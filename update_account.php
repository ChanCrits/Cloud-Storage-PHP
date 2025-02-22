<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT email, profile_pic, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $profile_pic_url = trim($_POST['profile_pic_url']); // URL of the cropped image
    $errors = [];

    // Validate email
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Handle cropped profile picture
    if (!empty($profile_pic_url)) {
        $upload_dir = 'uploads/profile_pics/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Download and save the cropped image locally
        $image_data = file_get_contents($profile_pic_url);
        $image_name = "user_" . $user_id . "_profile.jpg";
        $local_image_path = $upload_dir . $image_name;
        file_put_contents($local_image_path, $image_data);

        // Update profile picture path in the database
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $local_image_path, $user_id);
        $stmt->execute();
        $stmt->close();
    }

   // Update username and email
if (empty($errors)) {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
    if ($stmt->execute()) {
        // Pass success message to JavaScript for modal display
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showModal('successModal', 'Profile updated successfully.');
                });
              </script>";
    } else {
        // Pass error message to JavaScript for modal display
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showModal('errorModal', 'Failed to update profile.');
                });
              </script>";
    }
    $stmt->close();
} else {
    // If there are validation errors, pass them to JavaScript
    $error_message = implode(', ', $errors);
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showModal('errorModal', '$error_message');
            });
          </script>";
}

}

// Refresh user data after update
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
    <title>SDrive</title>
    <link rel="icon" href="icons/512logo.png" type="image/icon type">
    <script>
    UPLOADCARE_PUBLIC_KEY = "demopublickey"; // Replace with your actual Uploadcare public key
    </script>
    <script src="https://ucarecdn.com/libs/widget/3.x/uploadcare.full.min.js" charset="utf-8"></script>
    <style>
    body {
        color: #e3e3e3;
        font-family: Arial, sans-serif;
        background-color: #11151c;
        margin: 0;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-update-container {
        background-color: #1c1f26;
        padding: 20px;
        border-radius: 10px;
        width: 350px;
        height: 460px;

        margin: auto;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .success {
        color: green;
    }

    .error {
        color: red;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 20px;
    }

    label {
        margin-left: -75%;
        /* Indent label slightly to align with input */
    }

    #preview {
        margin-bottom: 10px;
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #e3e3e3;
    }

    button {
        background-color: #007BFF;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        width: 93%;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #11151c;
    }

    a.btn {
        display: block;
        margin-bottom: 20px;
        color: #11151c;
        text-decoration: none;
    }

    a.btn:hover {
        text-decoration: underline;
    }


    label {
        font-size: 16px;
        font-weight: light;
        color: #e3e3e3;
        margin-bottom: 5px;
    }

    input[type="text"],
    input[type="email"] {
        width: 86%;
        padding: 10px;
        font-size: 16px;
        border: 2px solid #e3e3e3;
        border-radius: 5px;
        background-color: #1c1f26;
        color: #e3e3e3;
        outline: none;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="email"]:focus {
        border-color: #007BFF;
        
    }

    input[type="text"]::placeholder,
    input[type="email"]::placeholder {
        color: #a3a3a3;
    }

    input[type="text"]:disabled,
    input[type="email"]:disabled {
        background-color: #333;
        cursor: not-allowed;
    }

    button:hover {
        background-color: #11151c;
    }


    /* Modal styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: #1c1f26;
        color: #e3e3e3;
        margin: auto;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        max-width: 400px;
        font-size: 18px;
    }

    .modal-content.success {
        border: 3px solid green;
    }

    .modal-content.error {
        border: 3px solid red;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: white;
        text-decoration: none;
    }




    .uploadcare--jcrop-holder>div>div, #preview {
            border-radius: 50%;
        }
        





    
    </style>
</head>

<body>
    <div class="profile-update-container">
        <a href="dashboard.php"
            style="display: inline-block; text-decoration: none; position: absolute; left: 20px; top: 20px;">
            <img src="icons/back.png" alt="Back" style="width: 24px; height: 24px;" />
        </a>

        <h2>Update Profile</h2>


        <?php if (!empty($success_message)): ?>
        <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
        <ul class="error">
            <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <form action="update_profile.php" method="POST">
            <div class="form-group">
                <!-- Profile Picture Preview -->
                <img src="<?= !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'default-profile.png'; ?>"
                    alt="Profile Picture" id="preview">

                <!-- Label for Upload -->
                <!-- <label for="profile_pic">Choose an Image</label> -->

                <!-- Uploadcare Widget with Circular Crop -->
                <input type="hidden" role="uploadcare-uploader" data-crop="1:1" data-images-only name="profile_pic_url"
                    required>
                    
            </div>

            <div class="form-group">
                <label for="username">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']); ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>

            <button type="submit">Update Profile</button>
        </form>


        <div id="successModal" class="modal">
            <div class="modal-content success">
                <span class="close" onclick="closeModal('successModal')">&times;</span>
                <p id="successMessage"></p>
            </div>
        </div>

        <div id="errorModal" class="modal">
            <div class="modal-content error">
                <span class="close" onclick="closeModal('errorModal')">&times;</span>
                <p id="errorMessage"></p>
            </div>
        </div>
    </div>

    <script>
    // Get the Uploadcare widget instance
    const widget = uploadcare.Widget('[role=uploadcare-uploader]');
    const preview = document.getElementById('preview');

    // Update the hidden input value and show the image preview
    widget.onUploadComplete(fileInfo => {
        preview.src = fileInfo.cdnUrl; // Show the image preview
        document.querySelector('input[name="profile_pic_url"]').value = fileInfo
            .cdnUrl; // Set the hidden input value
    });



    // Function to display the modal
    function showModal(modalId, message) {
        const modal = document.getElementById(modalId);
        const messageContainer = modal.querySelector("p");
        messageContainer.textContent = message;
        modal.style.display = "flex";
    }

    // Function to close the modal
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    // Show success or error messages dynamically
    <?php if (!empty($success_message)): ?>
    showModal("successModal", "<?= $success_message; ?>");
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    showModal("errorModal", "<?= implode(', ', $errors); ?>");
    <?php endif; ?>
    </script>
</body>

</html>