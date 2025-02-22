<?php
session_start();
require_once 'db_config.php';
require_once 'phpqrcode/qrlib.php'; // Ensure this path is correct

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_firstname = trim($_POST['firstname']);
    $new_lastname = trim($_POST['lastname']);
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $profile_pic_url = trim($_POST['profile_pic_url']); // URL of the uploaded image
    $errors = [];

    // Validate email
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate profile picture URL
    if (empty($profile_pic_url)) {
        $errors[] = "Profile picture is required.";
    }

    if (empty($errors)) {
        // Hash the password before storing it
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // OPTIONAL: Save the uploaded image locally
        $upload_dir = 'uploads/profile_pics/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Get the image from the Uploadcare URL and save it locally
        $image_data = file_get_contents($profile_pic_url);
        $image_name = uniqid('user_') . '.jpg';
        file_put_contents($upload_dir . $image_name, $image_data);

        // Update the profile_pic_url to the local path
        $local_image_path = $upload_dir . $image_name;

        // Generate a unique QR code token
        $qr_code_token = bin2hex(random_bytes(16));

        // Generate the QR code image
        $qr_code_dir = 'uploads/qr_codes/';
        if (!is_dir($qr_code_dir)) {
            mkdir($qr_code_dir, 0755, true);
        }
        $qr_code_path = $qr_code_dir . $qr_code_token . '.png';
        QRcode::png($qr_code_token, $qr_code_path);

        // Store user data in the database
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, email, password, profile_pic, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssss", $new_firstname, $new_lastname, $new_username, $new_email, $hashed_password, $local_image_path, $qr_code_token);
            if (!$stmt->execute()) {
                die("Database error: " . $stmt->error);
            }
            $stmt->close();

            // Set session and redirect to login page with QR code path
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['qr_code_path'] = $qr_code_path;
            header("Location: login.php");
            exit();
        } else {
            die("Database prepare error: " . $conn->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script>
        UPLOADCARE_PUBLIC_KEY = "demopublickey"; // Replace with your actual Uploadcare public key
    </script>
    <script src="https://ucarecdn.com/libs/widget/3.x/uploadcare.full.min.js" charset="utf-8"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" />
    <link rel="stylesheet" href="css/mdb.min.css" />
    <style>

        

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }
        .form-container img {
            display: block;
            margin: 0 auto 20px;
            border-radius: 50%;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php if (!empty($errors)): ?>
        <ul class="error">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="form-container">
        <h1>Register</h1>
        <img src="uploads/profile_pics/default-profile.png" alt="Preview" id="preview" width="100" height="100">
        <form action="register.php" method="POST">
            <label for="firstname">First Name</label>
            <input type="text" name="firstname" id="firstname" required>

            <label for="middlename">Middle Name</label>
            <input type="text" name="middlename" id="middlename">

            <label for="lastname">Last Name</label>
            <input type="text" name="lastname" id="lastname" required>

            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <label for="profile_pic">Profile Picture</label>
            <input type="hidden" role="uploadcare-uploader" data-crop="1:1" data-images-only name="profile_pic_url" required>

            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        // Get the Uploadcare widget instance
        const widget = uploadcare.Widget('[role=uploadcare-uploader]');
        const preview = document.getElementById('preview');

        // Set the hidden input value with the image URL and show preview
        widget.onUploadComplete(fileInfo => {
            preview.src = fileInfo.cdnUrl; // Show the image preview
            document.querySelector('input[name="profile_pic_url"]').value = fileInfo.cdnUrl; // Set the hidden input value
        });
    </script>
    <!-- MDB -->
    <script type="text/javascript" src="js/mdb.umd.min.js"></script>
</body>
</html>