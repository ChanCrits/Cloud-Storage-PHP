<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$folder_name = $_GET['folder'] ?? '';

// Fetch folder ID from the database
$stmt = $conn->prepare("SELECT id FROM folders WHERE user_id = ? AND folder_name = ?");
$stmt->bind_param("is", $user_id, $folder_name);
$stmt->execute();
$stmt->bind_result($folder_id);
$stmt->fetch();
$stmt->close();

if (!$folder_id) {
    die("Folder not found.");
}
// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_to_upload'])) {
    // Loop through all uploaded files
    foreach ($_FILES['file_to_upload']['name'] as $index => $file_name) {
        $file_tmp_name = $_FILES['file_to_upload']['tmp_name'][$index];
        $file_name_basename = basename($file_name);
        $target_path = "../uploads/$user_id/$folder_name/$file_name_basename";

        // Check for duplicate file in the database
        $stmt = $conn->prepare("SELECT COUNT(*) FROM files WHERE user_id = ? AND folder_id = ? AND file_name = ?");
        $stmt->bind_param("iis", $user_id, $folder_id, $file_name_basename);
        $stmt->execute();
        $stmt->bind_result($file_exists);
        $stmt->fetch();
        $stmt->close();

        if ($file_exists > 0) {
            // File exists, trigger JavaScript pop-up for each duplicate file
            echo "<script>
                document.getElementById('duplicatePopup').style.display = 'flex';
                document.getElementById('duplicateFileName').innerText = '$file_name_basename';
            </script>";
        } else {
            // Proceed with file upload for each file
            if (move_uploaded_file($file_tmp_name, $target_path)) {
                $stmt = $conn->prepare("INSERT INTO files (user_id, folder_id, file_name) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $user_id, $folder_id, $file_name_basename);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Failed to upload file: $file_name_basename";
            }
        }
    }

    // Redirect to file manager page after all files are processed
    header("Location: file_manager.php?folder=" . urlencode($folder_name));
    exit();
}


// Handle multiple file deletions
// Handle multiple file deletions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_files'])) {
    $files_to_delete = json_decode($_POST['delete_files'], true);

    foreach ($files_to_delete as $file_to_delete) {
        $target_path = "../uploads/$user_id/$folder_name/$file_to_delete";

        if (unlink($target_path)) {
            $stmt = $conn->prepare("DELETE FROM files WHERE user_id = ? AND folder_id = ? AND file_name = ?");
            $stmt->bind_param("iis", $user_id, $folder_id, $file_to_delete);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Return an empty response to indicate success
    echo json_encode(['status' => 'success']);
    exit();
}

// Fetch files from the database
$stmt = $conn->prepare("SELECT file_name FROM files WHERE folder_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $folder_id);
$stmt->execute();
$result = $stmt->get_result();
$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row['file_name'];
}
$stmt->close();



$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT email, profile_pic, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/512logo.png" type="image/icon type">
    <link rel="stylesheet" href="css2/filesmanager.css">
    <title>SDrive</title>
  

</head>

<body>

    <div class="container">

    <div id="loadingContainer" style="display: none;"></div>
        <div class="upload-container">
            <a href="dashboard.php" style="display: inline-block; text-decoration: none;">
                <img src="icons/back.png" alt="Back" style="width: 24px; height: 24px;" />
            </a>

            <div class="profile" style="text-align: center; margin: 1px 0;">
                <img src="<?= !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'default-profile.png'; ?>"
                    alt="Profile Picture" style="
            width: 90px; 
            height: 90px; 
            border: 1px solid#e3e3e3; 
            border-radius: 50%; 
            display: block; 
            margin: 0 auto; 
            object-fit: cover; 
            object-position: center;">
                <h3 style="margin-bottom: 1px;"><?= $user['username'] ?></h3>
                <p style="font-size: 14px; color: rgb(183, 183, 183); margin-top: 0;"><?= $user['email'] ?></p>
            </div>

            <div style="text-align: center; margin-bottom: 20px;">
                <h2>Upload Files</h2>
            </div>

            <!-- Drop Area with Overlay Icon -->
            <div id="drop-area" style="
        position: relative;
        border: 2px dashed #ccc; 
        height: 48.5%;
        border-radius: 10px; 
        padding: 20px; 
        display: flex; 
        align-items: start; 
        justify-content: center; 
        cursor: pointer; 
        color: #555;">
                <!-- Overlay Upload Icon -->
                <img src="icons/upload.png" alt="Upload Icon" style="
            position: absolute;
            width: 100px; 
            height: 100px; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%);
            opacity: 0.8;
            animation: float 3s ease-in-out infinite;
            ">
                <style>
                @keyframes float {
                    0% {
                        transform: translate(-50%, -50%) translateY(0);
                    }

                    50% {
                        transform: translate(-50%, -50%) translateY(-10px);
                    }

                    100% {
                        transform: translate(-50%, -50%) translateY(0);
                    }
                }
                </style>


                <p style="margin: 0;">Drag and drop your files here or click to upload</p>
                <input type="file" name="files_to_upload[]" id="file-input" style="display: none;" multiple />
            </div>

            <button id="upload-button" style="
        margin-top: 15px; 
        background-color: #007BFF; 
        color: #fff; 
        width: 99.9%;
        border: none; 
        padding: 10px 20px; 
        border-radius: 5px; 
        cursor: pointer;">Upload</button>
        </div>


        <div class="files-container">

            <div>
                <!-- Search Bar -->

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <!-- Search Bar -->
                    <div class="search-container">
                        <span class="search-icon">
                            <img src="icons/search.png" alt="Search Icon">
                        </span>
                       <input type="text" id="searchBar" class="search-bar" oninput="filterFiles()" />
                    </div>

                    <!-- Logo -->
                    <img src="icons/logo.png" alt="Logo" style="width: 86px; height: 45px; margin-left: 10px; ">
                </div>



                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                    <h3 style="margin: 0;  font-size: 27px;">Files in <?= htmlspecialchars($folder_name) ?></h3>
                    <span id="selectedCount">Selected: 0 files</span>

                    <button id="deleteButton" disabled style="background: none; border: none; cursor: not-allowed;">
                        <img src="icons/delete.png" alt="Delete" style="width: 24px; height: 24px; margin-bottom: 6px">
                    </button>
                    <button id="downloadButton" disabled style="background: none; border: none; cursor: not-allowed;">
                        <img src="icons/download.png" alt="Download" style="width: 24px; height: 24px;">
                    </button>
                    <button id="shareButton" disabled style="background: none; border: none; cursor: not-allowed;"
                        onclick="openSharePopup('<?= "http://localhost/file_manager.php?folder=" . urlencode($folder_name) ?>')">
                        <img src="icons/share.png" alt="Share" style="width: 24px; height: 24px;">
                    </button>

                </div>

                <div class="file-grid">

                    <div id="noResultsMessage" style="display: none; text-align: center; color: #ccc;">
                        No files found.
                    </div>

                    <?php foreach ($files as $file_name): ?>
                    <div class="file-item" onclick="toggleSelectFile(this, '<?= htmlspecialchars($file_name) ?>')">
                        <div style="display: flex; align-items: center; justify-content: start; gap: 8px; width: 100%;">
                            <?php
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $icon_path = "icons/file.png"; // Default icon

            // Determine the correct icon based on file extension
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', ])) {
                $icon_path = "icons/image.png";
            } elseif (in_array($file_ext, ['mp4', 'webm', 'ogg'])) {
                $icon_path = "icons/video.png";
            } elseif ($file_ext === 'pdf') {
                $icon_path = "icons/pdf.png";
            } elseif ($file_ext === 'psd') {
                $icon_path = "icons/psd.png";
            } elseif ($file_ext === 'docx') {
                $icon_path = "icons/docs.png";
            } elseif ($file_ext === 'pptx') {
                $icon_path = "icons/ppt.png";
            } elseif (in_array($file_ext, ['xls', 'xlsx'])) {
                $icon_path = "icons/xls.png";
            } elseif ($file_ext === 'ai') {
                $icon_path = "icons/illustrator.png";
            } elseif (in_array($file_ext, ['mp3', 'wav', 'ogg'])) {
                $icon_path = "icons/music.png";
            }

            // Output the icon
            ?>
                            <img src="<?= htmlspecialchars($icon_path) ?>" alt="File Icon"
                                style="width: 14px; height: 14px;">
                            <p class="file-name"><?= htmlspecialchars($file_name)?></p>
                        </div>
                        <?php
        $file_path = "../uploads/$user_id/$folder_name/$file_name";

        // Display file previews or default icon based on file type
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <img src="<?= htmlspecialchars($file_path) ?>" alt="<?= htmlspecialchars($file_name) ?>"
                            class="file-preview">
                        <?php elseif (in_array($file_ext, ['mp4', 'webm', 'ogg'])): ?>
                        <video class="file-preview" controls>
                            <source src="<?= htmlspecialchars($file_path) ?>"
                                type="video/<?= htmlspecialchars($file_ext) ?>">
                            Your browser does not support the video tag.
                        </video>
                        <?php elseif ($file_ext == 'pdf'): ?>
                        <img src="icons/pdf.png" alt="PDF Icon" class="file-preview">
                        <?php elseif ($file_ext == 'psd'): ?>
                        <img src="icons/psd.png" alt="PSD Icon" class="file-preview">
                        <?php elseif ($file_ext == 'docx'): ?>
                        <img src="icons/docs.png" alt="DOCX Icon" class="file-preview">
                        <?php elseif ($file_ext == 'pptx'): ?>
                        <img src="icons/ppt.png" alt="PPTX Icon" class="file-preview">
                        <?php elseif ($file_ext == 'xls' || $file_ext == 'xlsx'): ?>
                        <img src="icons/xls.png" alt="XLS Icon" class="file-preview">
                        <?php elseif ($file_ext == 'ai'): ?>
                        <img src="icons/illustrator.png" alt="AI Icon" class="file-preview">
                        <?php elseif (in_array($file_ext, ['mp3', 'wav', 'ogg'])): ?>
                        <audio class="file-preview" controls>
                            <source src="<?= htmlspecialchars($file_path) ?>"
                                type="audio/<?= htmlspecialchars($file_ext) ?>">
                            Your browser does not support the audio element.
                        </audio>
                        <?php else: ?>
                        <img src="icons/file.png" alt="File Icon" class="file-preview">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>


            </div>

        </div>
    </div>

    <div id="duplicatePopup"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 100;">
        <div style="background-color: #161c27; padding: 20px; border-radius: 8px; text-align: center; width: 300px;">
            <p>A file named <strong id="duplicateFileName"></strong> already exists in this folder.</p>
            <p>Do you want to replace it?</p>
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button class="confirm" onclick="confirmReplace()">Yes, Replace</button>
                <button class="cancel" onclick="cancelUpload()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Pop-Up Confirmation -->
    <div id="deletePopup">
        <div id="popupContent">
            <img src="icons/deleteanimated.gif" alt="delete.png" />
            <p>Are you sure you want to delete?</p>
            <button class="confirm" onclick="confirmDeletion()">Yes</button>
            <button class="cancel" onclick="closePopup()">No</button>
        </div>
    </div>

    <!-- Success Pop-Up -->
    <div id="successPopup" style="display: none;">
        <div id="successPopupContent">
            <p>File Deleted Successfully!</p>
            <button onclick="closeSuccessPopup()">OK</button>
        </div>
    </div>

    <div id="sharePopup"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center;">
        <div style="background-color: #161c27; padding: 20px; border-radius: 10px; text-align: center; width: 250px;">
            <p style="color: white; margin-bottom: 15px;">Share this link:</p>
            <input type="text" id="shareLink" style="width: 91%; padding: 10px; border-radius: 5px; border: none;"
                readonly>
            <button onclick="copyToClipboard()"
                style="margin-top: 15px; margin-right: 20px; padding: 10px 40px; border-radius: 5px; background-color: #2c7bf7; color: white; border: none; cursor: pointer;">Copy</button>
            <button onclick="closeSharePopup()"
                style="margin-top: 15px; padding: 10px 40px; border-radius: 5px; background-color: gray; color: white; border: none; cursor: pointer;">Close</button>
        </div>
    </div>


    <script>
    function confirmReplace() {
        document.getElementById('duplicatePopup').style.display = 'none';
        // Submit the form to replace the file
        document.querySelector('form').submit();
    }

    function cancelUpload() {
        document.getElementById('duplicatePopup').style.display = 'none';
        // Reset the file input field
        document.querySelector('input[name="file_to_upload"]').value = '';
    }
    </script>

    <script>
    let selectedFiles = [];

    function toggleSelectFile(fileItem, fileName) {
        if (selectedFiles.includes(fileName)) {
            selectedFiles = selectedFiles.filter(file => file !== fileName);
            fileItem.classList.remove('selected');
        } else {
            selectedFiles.push(fileName);
            fileItem.classList.add('selected');
        }

        const deleteButton = document.getElementById('deleteButton');
        const downloadButton = document.getElementById('downloadButton');
        const shareButton = document.getElementById('shareButton');
        const selectedCount = document.getElementById('selectedCount');

        // Update buttons' state
        const buttons = [deleteButton, downloadButton, shareButton];
        if (selectedFiles.length > 0) {
            buttons.forEach(button => {
                button.disabled = false;
                button.style.cursor = 'pointer'; // Change cursor to pointer
                button.classList.add('active');
            });
        } else {
            buttons.forEach(button => {
                button.disabled = true;
                button.style.cursor = 'not-allowed'; // Change cursor to not-allowed
                button.classList.remove('active');
            });
        }

        // Update the selected files count
        selectedCount.textContent = `Selected: ${selectedFiles.length} file${selectedFiles.length !== 1 ? 's' : ''}`;
    }

    document.getElementById('deleteButton').addEventListener('click', () => {
        if (selectedFiles.length > 0) {
            document.getElementById('deletePopup').style.display = 'flex';
        }
    });

    document.getElementById('downloadButton').addEventListener('click', () => {
        selectedFiles.forEach(fileName => {
            const link = document.createElement('a');
            link.href = '../uploads/<?= $user_id ?>/<?= urlencode($folder_name) ?>/' + fileName;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });

    document.getElementById('shareButton').addEventListener('click', () => {
        selectedFiles.forEach(fileName => {
            const shareLink = `../uploads/<?= $user_id ?>/<?= urlencode($folder_name) ?>/${fileName}`;
            // prompt("Share this link:", shareLink);
        });
    });

    function confirmDeletion() {
        const formData = new FormData();
        formData.append('delete_files', JSON.stringify(selectedFiles));

        fetch('file_manager.php?folder=<?= urlencode($folder_name) ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                // alert(result);
                location.reload();
            })
            .catch(error => console.error('Error:', error));
    }

    function closePopup() {
        document.getElementById('deletePopup').style.display = 'none';
    }

    function filterFiles() {
        const searchQuery = document.getElementById("searchBar").value.toLowerCase();
        const fileItems = document.querySelectorAll(".file-item");
        let hasResults = false;

        fileItems.forEach(item => {
            const fileName = item.querySelector(".file-name").textContent.toLowerCase();
            if (fileName.includes(searchQuery)) {
                item.style.display = "flex";
                hasResults = true;
            } else {
                item.style.display = "none";
            }
        });

        const noResultsMessage = document.getElementById("noResultsMessage");
        if (!hasResults) {
            noResultsMessage.style.display = "block";
        } else {
            noResultsMessage.style.display = "none";
        }
    }



    function openSharePopup(link) {
        document.getElementById('shareLink').value = link;
        document.getElementById('sharePopup').style.display = 'flex';
    }

    function closeSharePopup() {
        document.getElementById('sharePopup').style.display = 'none';
    }

    function copyToClipboard() {
        const shareLink = document.getElementById('shareLink');
        shareLink.select();
        shareLink.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(shareLink.value).then(() => {
            // alert('Link copied to clipboard!');
        }).catch(err => {
            console.error('Could not copy link: ', err);
        });
    }







    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file-input');
    const uploadButton = document.getElementById('upload-button');
    const loadingContainer = document.getElementById('loadingContainer');

    // Open file dialog on click
    dropArea.addEventListener('click', () => {
        fileInput.click();
    });

    // Highlight drag area on dragover
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#007BFF';
    });

    // Reset border color on dragleave
    dropArea.addEventListener('dragleave', () => {
        dropArea.style.borderColor = '#ccc';
    });

    // Handle dropped files
    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#ccc';

        const files = e.dataTransfer.files;
        fileInput.files = files; // Assign dropped files to input

        // Optionally, display the file name(s)
        if (files.length > 0) {
            dropArea.querySelector('p').textContent = `${files.length} files selected`;
        }
    });

    // Handle file selection through input
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            dropArea.querySelector('p').textContent = `${fileInput.files.length} files selected`;
        }
    });

    // Add upload functionality
    uploadButton.addEventListener('click', () => {
        if (fileInput.files.length === 0) {
            alert('Please select files to upload.');
            return;
        }

          // Show loading animation
    loadingContainer.style.display = 'flex';
    fetch('loading.php')
        .then(response => response.text())
        .then(data => {
            loadingContainer.innerHTML = data;
        });


        // Create a FormData object for the file upload
        const formData = new FormData();

        // Append each file to the FormData object
        Array.from(fileInput.files).forEach((file) => {
            formData.append('file_to_upload[]', file);
        });

        // Send the files to the server via Fetch API
        fetch('file_manager.php?folder=<?= urlencode($folder_name) ?>', {
            method: 'POST',
            body: formData,
        })
        .then((response) => response.text())
        .then((data) => {
            console.log(data); // Log server response
            // Automatically refresh the page
            window.location.reload();
        })
        .catch((error) => {
            console.error('Error uploading files:', error);
            alert('An error occurred while uploading the files.');
        })
        .finally(() => {
            // Hide loading animation
            loadingContainer.style.display = 'none';
        });
});
    </script>
</body>

</html>