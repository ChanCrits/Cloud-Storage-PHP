<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$folder_name = $_POST['folder_name'];

// Get the folder path for the shared folder
$stmt = $conn->prepare("SELECT folders.user_id FROM folders JOIN shared_folders ON folders.id = shared_folders.folder_id WHERE folders.folder_name = ? AND shared_folders.shared_with = ?");
$stmt->bind_param("si", $folder_name, $user_id);
$stmt->execute();
$folder = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($folder) {
    $owner_id = $folder['user_id'];
    $folder_path = "../uploads/$owner_id/$folder_name";

    if (is_dir($folder_path)) {
        $files = array_diff(scandir($folder_path), ['.', '..']);
        echo json_encode(['status' => 'success', 'files' => array_values($files)]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Folder not found', 'folder_path' => $folder_path]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Folder not found']);
}
?>