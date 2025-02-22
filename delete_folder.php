<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $folder_name = $_POST['folder_name'] ?? '';

    if (empty($folder_name)) {
        echo json_encode(["status" => "error", "message" => "Folder name is required"]);
        exit();
    }

    // Get folder path
    $folder_path = "../uploads/$user_id/$folder_name";

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM folders WHERE user_id = ? AND folder_name = ?");
    $stmt->bind_param("is", $user_id, $folder_name);

    if ($stmt->execute()) {
        // Remove folder from file system
        if (is_dir($folder_path)) {
            function deleteFolderRecursively($folder) {
                foreach (scandir($folder) as $file) {
                    if ($file === '.' || $file === '..') continue;
                    $filePath = "$folder/$file";
                    is_dir($filePath) ? deleteFolderRecursively($filePath) : unlink($filePath);
                }
                rmdir($folder);
            }
            deleteFolderRecursively($folder_path);
        }

        echo json_encode(["status" => "success", "message" => "Folder deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete folder"]);
    }

    $stmt->close();
}
?>
