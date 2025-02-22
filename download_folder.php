<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(["status" => "error", "message" => "Unauthorized access."]));
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_name'])) {
    $folder_name = basename($_POST['folder_name']);
    $folder_path = "../uploads/$user_id/$folder_name";

    if (!is_dir($folder_path)) {
        exit(json_encode(["status" => "error", "message" => "Folder not found."]));
    }

    // Create ZIP file
    $zip_file = "../uploads/$user_id/{$folder_name}.zip";
    $zip = new ZipArchive();

    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folder_path) + 1);
            $zip->addFile($filePath, $relativePath);
        }

        $zip->close();

        echo json_encode(["status" => "success", "download_url" => "download_folder.php?file=" . urlencode($folder_name . ".zip")]);
        exit;
    } else {
        exit(json_encode(["status" => "error", "message" => "Failed to create ZIP file."]));
    }
}

// Handle ZIP file download
if (isset($_GET['file'])) {
    $file_name = basename($_GET['file']);
    $file_path = "../uploads/{$_SESSION['user_id']}/" . $file_name;

    if (file_exists($file_path)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        unlink($file_path); // Delete ZIP after download
        exit;
    } else {
        die("YOU CAN'T DOWNLOAD NO FILE UPLOADED.");
    }
}
?>