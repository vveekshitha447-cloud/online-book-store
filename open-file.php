<?php
session_start();

# Only admin can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    header("Location: ../admin.php");
    exit;
}

$file_name = basename($_GET['file']); // basename prevents directory traversal
$file_path = "../uploads/files/" . $file_name;

if (!file_exists($file_path)) {
    die("File not found.");
}

$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

$mime_types = [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'doc'  => 'application/msword',
    'ppt'  => 'application/vnd.ms-powerpoint',
];

$mime = $mime_types[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>