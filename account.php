<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT firstname, middlename, lastname, email, profile_pic, qr_code FROM users WHERE id = ?");
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
    <title>Account</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

        .account-container {
            background-color: #1c1f26;
            padding: 20px;
            border-radius: 10px;
            width: 350px;
            text-align: center;
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e3e3e3;
            margin-bottom: 10px;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            margin-top: 20px;
        }

        .info {
            margin-bottom: 10px;
        }

        .info label {
            font-weight: bold;
        }

        .info span {
            display: block;
            margin-top: 5px;
        }

        .btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #11151c;
        }
    </style>
</head>
<body>
    <div class="account-container">
        <a href="dashboard.php" class="btn" style="margin-bottom: 20px;">Back to Dashboard</a>
        <img src="<?= !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'default-profile.png'; ?>" alt="Profile Picture" class="profile-pic">
        <div class="info">
            <label>First Name:</label>
            <span><?= htmlspecialchars($user['firstname']); ?></span>
        </div>
        <div class="info">
            <label>Middle Name:</label>
            <span><?= htmlspecialchars($user['middlename']); ?></span>
        </div>
        <div class="info">
            <label>Last Name:</label>
            <span><?= htmlspecialchars($user['lastname']); ?></span>
        </div>
        <div class="info">
            <label>Email:</label>
            <span><?= htmlspecialchars($user['email']); ?></span>
        </div>
        <div class="info">
            <label>QR Code:</label>
            <img src="uploads/qr_codes/<?= htmlspecialchars($user['qr_code']); ?>.png" alt="QR Code" class="qr-code">
        </div>
        <a href="update_account.php" class="btn" style="margin-top: 20px;">Update Profile</a>
    </div>
</body>
</html>