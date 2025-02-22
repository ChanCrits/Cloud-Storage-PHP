<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['path'])) {
    $path = $_POST['path'];

    // Check if the file or folder belongs to the logged-in user
    $user_id = $_SESSION['user_id'];
    $user_folder = "../uploads/$user_id/";

    if (strpos(realpath($path), realpath($user_folder)) === 0) {
        if (is_dir($path)) {
            // Delete folder and its contents
            array_map('unlink', glob("$path/*.*"));
            rmdir($path);
            echo "Folder deleted successfully.";
        } elseif (is_file($path)) {
            // Delete file
            unlink($path);
            echo "File deleted successfully.";
        } else {
            echo "Invalid item.";
        }
    } else {
        echo "Unauthorized access.";
    }
}
?>
