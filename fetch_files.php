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
$folder_path = "../uploads/$user_id/$folder_name";

if (is_dir($folder_path)) {
    $files = array_diff(scandir($folder_path), ['.', '..']);
    echo json_encode(['status' => 'success', 'files' => array_values($files)]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Folder not found', 'folder_path' => $folder_path]);
}
?>