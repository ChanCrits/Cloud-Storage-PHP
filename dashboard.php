<?php
session_start();

require_once 'db_config.php';




if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();



// Fetch user data
$stmt = $conn->prepare("SELECT username, email, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
// Handle folder creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_folder'])) {
    $folder_name = $_POST['folder_name'];
    $folder_path = "../uploads/$user_id/$folder_name";

    if (!is_dir($folder_path)) {
        mkdir($folder_path, 0777, true);

        // Insert folder into the database
        $stmt = $conn->prepare("INSERT INTO folders (user_id, folder_name, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $folder_name);
        if ($stmt->execute()) {
            // echo "Folder created successfully!";
        } else {
            echo "Failed to record folder in the database.";
        }
        $stmt->close();
    } else {
        // echo "Folder already exists.";
    }
}


// Handle folder sharing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['share_folder'])) {
    $folder_name = $_POST['folder_name'];
    $shared_with_email = $_POST['shared_with_email'];

    // Get the folder ID
    $stmt = $conn->prepare("SELECT id FROM folders WHERE user_id = ? AND folder_name = ?");
    $stmt->bind_param("is", $user_id, $folder_name);
    $stmt->execute();
    $folder = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($folder) {
        $folder_id = $folder['id'];

        // Get the user ID of the person to share with
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $shared_with_email);
        $stmt->execute();
        $shared_with_user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($shared_with_user) {
            $shared_with_user_id = $shared_with_user['id'];

            // Insert shared folder into the database
            $stmt = $conn->prepare("INSERT INTO shared_folders (folder_id, shared_by, shared_with) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $folder_id, $user_id, $shared_with_user_id);
            if ($stmt->execute()) {
                include 'alerts/shared_success.php';
            } else {
                include 'alerts/shared_failed.php';
            }
            $stmt->close();
        } else {
            echo "User with email $shared_with_email not found.";
        }
    } else {
        echo "Folder not found.";
    }
}


// Fetch folders
$stmt = $conn->prepare("SELECT folder_name FROM folders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$folders = [];
while ($row = $result->fetch_assoc()) {
    $folders[] = $row['folder_name'];
}
$stmt->close();

// Fetch shared folders
$stmt = $conn->prepare("SELECT folders.folder_name FROM shared_folders JOIN folders ON shared_folders.folder_id = folders.id WHERE shared_folders.shared_with = ? ORDER BY folders.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$shared_folders = [];
while ($row = $result->fetch_assoc()) {
    $shared_folders[] = $row['folder_name'];
}
$stmt->close();


// Fetch all users
$stmt = $conn->prepare("SELECT username, email, profile_pic FROM users");
$stmt->execute();
$all_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css2/dash1.css">
    <link rel="icon" href="icons/512logo.png" type="image/icon type">

    <title>SDrive</title>
    <style>
    .shared-folders ul {
        list-style-type: none;
        padding: 0;
    }

    .shared-folders li {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        cursor: pointer;
        /* Ensure the cursor changes to pointer */
    }

    .shared-folders img {
        width: 20px;
        height: 20px;
        margin-right: 10px;
    }

    /* .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
    } */

    .file-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .file-modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }




    .user-list ul {
        list-style-type: none;
        padding: 0;
    }

    .user-list li {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .user-profile-pic {
        width: 30px;
        height: 30px;
        margin-right: 10px;
        border-radius: 50%;
    }

    .user-email {
        font-weight: bold;
        margin-right: 10px;
    }

    .user-username {
        color: gray;
    }
    </style>

</head>

<body>



    <div class="left-container">
        <div class="logo">
            <img src="icons/logo.png" alt="Logo">
        </div>
        <div class="create-folder">
            <button onclick="openModal()" style="    background-color:rgb(11, 51, 120);
 color: white; border: none; padding: 5px 30px; border-radius: 50px;">
                <img src="icons/Newfolder.png" alt="New Folder Icon"
                    style="width: 30px; height: 30px; vertical-align: middle; margin-right: 5px;">
                New Folder
            </button>
        </div>

        <div class="shared-folders">
            <h3>Shared Folders</h3>
            <ul>
                <?php foreach ($shared_folders as $shared_folder_name): ?>
                <li onclick="handleSharedFolderClick('<?= htmlspecialchars($shared_folder_name) ?>')">
                    <img src="icons/shared_folders.png" alt="Shared Folder Icon">
                    <?= htmlspecialchars($shared_folder_name) ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <!-- Folder Management -->
    <div class="main-container">
        <div class="folder-container">
            <!-- Fixed Header -->
            <div class="file-container">
                <h3>Files</h3>
                <div id="fileList" class="file-list">
                    <!-- Files will be displayed here -->
                </div>
            </div>

            <div class="header-container">
                <div class="search-container">
                    <span class="search-icon">
                        <img src="icons/search.png" alt="Search Icon">
                    </span>
                    <input type="text" id="search" class="search-bar" oninput="liveSearch()" />
                </div>
                <div class="profile-menu" onclick="toggleDropdown()">
                    <span class="profile-name"><?php echo htmlspecialchars($user['username']); ?></span>
                    <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'icons/default_user.png'; ?>"
                        alt="Profile" class="profile-img">
                    <div class="dropdown" id="dropdownMenu">
                        <a href="account.php">Account</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>


            <div class="folder-header">

                <!-- Folder Actions Buttons -->
                <div class="button-container">
                    <p class="label">My Drive</p>
                    <span id="selectedCount">Selected: 0</span>
                    <button id="deleteBtn" onclick="deleteFolder()">
                        <img src="icons/delete.png" alt="Delete Icon">
                    </button>
                    <button id="downloadBtn" onclick="downloadFolder()">
                        <img src="icons/download.png" alt="Download Icon">
                    </button>
                    <button id="shareBtn" onclick="shareFolder()">
                        <img src="icons/share.png" alt="Share Icon">
                    </button>

                </div>
            </div>

            <!-- Scrollable Folder List -->
            <div class="folder-list-container">
                <div class="folder-grid" id="folderList">
                    <?php foreach ($folders as $folder_name): ?>
                    <div class="folder-item" onclick="handleClick(event, this)">
                        <img src="icons/folder3.png" alt="Folder Icon" class="folder-icon">
                        <p class="folder-name"><?= htmlspecialchars($folder_name) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal for Creating Folder -->
    <div id="folderModal" class="modal">
        <div class="modal-content">
            <form method="POST" action="dashboard.php">
                <h2>Create Folder</h2>
                <input type="text" name="folder_name" required placeholder="Folder Name">
                <br><br>
                <button type="button" onclick="closeModal()">Cancel</button>
                <button type="submit" name="create_folder">Create</button>
            </form>
        </div>
    </div>

    <!-- Modal for Sharing Folder -->
    <div id="shareModal" class="modal">
        <div class="modal-content">
            <form method="POST" action="dashboard.php">
                <h2>Share Folder</h2>
                <input type="hidden" name="folder_name" id="shareFolderName" required>
                <input type="email" name="shared_with_email" required placeholder="User Email">
                <br><br>
                <button type="button" onclick="closeShareModal()">Cancel</button>
                <button type="submit" name="share_folder">Share</button>
            </form>
            <div class="user-list">
                <h3>All Users</h3>
                <ul>
                    <?php foreach ($all_users as $user): ?>
                    <li>
                        <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture"
                            class="user-profile-pic">
                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                        <span class="user-username"><?= htmlspecialchars($user['username']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>



    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Are you sure you want to delete this folder?</h2>
            <p id="folderToDelete"></p>
            <button onclick="closeDeleteModal()">Cancel</button>
            <button onclick="confirmDelete()">Delete</button>
        </div>
    </div>
    <script>
    function handleSharedFolderClick(folderName) {
        fetchFiles(folderName, true); // Pass true to indicate it's a shared folder
    }

    function fetchFiles(folderName, isShared = false) {
        const url = isShared ? 'fetch_shared_files.php' : 'fetch_files.php';
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `folder_name=${encodeURIComponent(folderName)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Response data:", data); // Debugging information
                if (data.status === "success") {
                    if (Array.isArray(data.files)) {
                        displayFiles(data.files);
                    } else {
                        console.error("Error: files is not an array", data.files);
                    }
                } else {
                    alert(data.message);
                    console.error("Error details:", data);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
    }

    function displayFiles(files) {
        const fileListContainer = document.getElementById('fileList');
        fileListContainer.innerHTML = ''; // Clear previous files

        if (Array.isArray(files)) {
            files.forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.textContent = file;
                fileListContainer.appendChild(fileItem);
            });
        } else {
            console.error("Error: files is not an array", files);
        }
    }
    </script>
    <script>
    function handleClick(event, folder) {
        if (clickTimeout) {
            // Double-click detected
            handleDoubleClick(event, folder);
        } else {
            // Single-click selection
            clickTimeout = setTimeout(() => {
                toggleSelection(event, folder); // Perform single click action after a small delay
                clickTimeout = null; // Reset timeout after processing
                fetchFiles(folder.querySelector('.folder-name').textContent); // Fetch files inside the folder
            }, 300); // 300ms delay for distinguishing single and double clicks
        }
    }
    </script>

    <script>
    function toggleDropdown() {
        let dropdown = document.getElementById("dropdownMenu");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function(event) {
        let profileMenu = document.querySelector(".profile-menu");
        let dropdown = document.getElementById("dropdownMenu");

        if (!profileMenu.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
    </script>
    <script>
    let selectedFolders = []; // Array to hold selected folders
    let clickTimeout = null;
    let folderToDelete = null; // Global variable to store folder name to delete

    // Open Modal
    function openModal() {
        document.getElementById('folderModal').style.display = 'flex';
    }

    // Close Modal
    function closeModal() {
        document.getElementById('folderModal').style.display = 'none';
    }

    // Close Share Modal
    function closeShareModal() {
        document.getElementById('shareModal').style.display = 'none';
    }

    function toggleSelection(event, folder) {
        if (event.ctrlKey || event.metaKey) { // Multi-select with Ctrl or Cmd
            folder.classList.toggle('selected:');
            const folderName = folder.querySelector('.folder-name').textContent;

            if (folder.classList.contains('selected')) {
                selectedFolders.push(folderName);
            } else {
                selectedFolders = selectedFolders.filter(f => f !== folderName);
            }
        } else {
            // Handle single-click for selection (deselect others)
            document.querySelectorAll('.folder-item').forEach(item => item.classList.remove('selected'));
            folder.classList.add('selected');
            selectedFolders = [folder.querySelector('.folder-name').textContent];
        }

        updateSelectedCount();
    }

    function handleDoubleClick(event, folder) {
        clearTimeout(clickTimeout); // Clear previous single click timeout

        // Now navigate to the folder on double click
        window.location.href =
            `file_manager.php?folder=${encodeURIComponent(folder.querySelector('.folder-name').textContent)}`;
    }

    function handleClick(event, folder) {
        if (clickTimeout) {
            // Double-click detected
            handleDoubleClick(event, folder);
        } else {
            // Single-click selection
            clickTimeout = setTimeout(() => {
                toggleSelection(event, folder); // Perform single click action after a small delay
                clickTimeout = null; // Reset timeout after processing
            }, 300); // 300ms delay for distinguishing single and double clicks
        }
    }

    function updateSelectedCount() {
        document.getElementById('selectedCount').textContent = `Selected: ${selectedFolders.length}`;
    }
    document.addEventListener('click', function(event) {
        const folderItems = document.querySelectorAll('.folder-item');
        if (!event.target.closest('.folder-list')) { // Check if the click is outside the folder list
            folderItems.forEach(item => item.classList.remove('selected'));
            selectedFolders = [];
            updateSelectedCount();
        }
    });

    function deleteFolder() {
        if (selectedFolders.length === 0) {
            alert("No folders selected.");
            return;
        }

        const folderToDelete = selectedFolders[0]; // Get the first selected folder

        fetch('delete_folder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `folder_name=${encodeURIComponent(folderToDelete)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // Remove the folder from the UI
                    document.querySelectorAll('.folder-item').forEach(item => {
                        if (item.querySelector('.folder-name').textContent === folderToDelete) {
                            item.remove();
                        }
                    });
                    selectedFolders = [];
                    updateSelectedCount();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
    }

    function openDeleteModal() {
        document.getElementById('deleteModal').style.display = 'flex'; // Show the modal
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none'; // Close the modal
    }

    function confirmDelete() {
        if (folderToDelete) {
            fetch('delete_folder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `folder_name=${encodeURIComponent(folderToDelete)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert(data.message);
                        // Remove the folder from the UI
                        document.querySelectorAll('.folder-item').forEach(item => {
                            if (item.textContent === folderToDelete) {
                                item.remove();
                            }
                        });
                        selectedFolders = [];
                        updateSelectedCount();
                    } else {
                        alert(data.message);
                    }
                    closeDeleteModal(); // Close the modal after the action
                })
                .catch(error => {
                    console.error("Error:", error);
                    closeDeleteModal(); // Close the modal on error
                });
        }
    }

    function downloadFolder() {
        if (selectedFolders.length === 0) {
            alert("No folder selected for download.");
            return;
        }

        const folderName = selectedFolders[0];

        // Show loading animation by loading content from loading.php
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'loadingContainer';
        // Use AJAX to load the content from loading.php
        fetch('loading.php')
            .then(response => response.text())
            .then(data => {
                loadingDiv.innerHTML = data; // Add the loading HTML to the div
                document.body.appendChild(loadingDiv); // Append it to the body
            });

        // Start downloading the folder after the loading animation appears
        fetch('download_folder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `folder_name=${encodeURIComponent(folderName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    window.location.href = data.download_url; // Redirect to trigger download
                    // Remove the loading animation once download starts
                    document.getElementById('loadingContainer').remove();
                } else {
                    alert(data.message);
                    document.getElementById('loadingContainer').remove(); // Remove loading on error
                }
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById('loadingContainer').remove(); // Remove loading on error
            });
    }

    function shareFolder() {
        if (selectedFolders.length === 0) {
            alert("No folders selected.");
            return;
        }

        const folderName = selectedFolders[0]; // Get the first selected folder
        document.getElementById('shareFolderName').value = folderName; // Set the folder name in the modal

        // Show the modal
        document.getElementById("shareModal").style.display = "flex";
    }


    function liveSearch() {
        const searchTerm = document.getElementById('search').value.toLowerCase();
        const folderItems = document.querySelectorAll('.folder-item');

        folderItems.forEach(item => {
            const folderName = item.textContent.toLowerCase();
            if (folderName.includes(searchTerm)) {
                item.style.display = 'block'; // Show matching folder
            } else {
                item.style.display = 'none'; // Hide non-matching folder
            }
        });
    }
    </script>
</body>

</html>