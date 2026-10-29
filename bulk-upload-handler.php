<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include "../db_conn.php";

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../bulk-upload.php?error=No file uploaded");
    exit;
}

$file = $_FILES['csv_file'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'csv') {
    header("Location: ../bulk-upload.php?error=Only CSV files allowed");
    exit;
}

// Read CSV
$handle  = fopen($file['tmp_name'], 'r');
$results = [];
$row     = 0;

while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    $row++;

    // Skip header row
    if ($row === 1 && strtolower(trim($data[0])) === 'title') continue;

    // Check columns
    if (count($data) < 4) {
        $results[] = ['row' => $row, 'status' => 'error', 'message' => 'Not enough columns. Need: title, author_id, category_id, description'];
        continue;
    }

    $title       = trim($data[0]);
    $author_id   = (int) trim($data[1]);
    $category_id = (int) trim($data[2]);
    $description = trim($data[3]);

    // Validate
    if (empty($title)) {
        $results[] = ['row' => $row, 'status' => 'error', 'message' => 'Title is empty'];
        continue;
    }
    if ($author_id <= 0) {
        $results[] = ['row' => $row, 'status' => 'error', 'message' => "Invalid author_id for '$title'"];
        continue;
    }
    if ($category_id <= 0) {
        $results[] = ['row' => $row, 'status' => 'error', 'message' => "Invalid category_id for '$title'"];
        continue;
    }

    // Check author exists
    $s = $conn->prepare("SELECT id FROM authors WHERE id = ?");
    $s->execute([$author_id]);
    if (!$s->fetch()) {
        $results[] = ['row' => $row, 'status' => 'error', 'message' => "Author ID $author_id not found for '$title'"];
        continue;
    }

    // Check category exists
    $s = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $s->execute([$category_id]);
    if (!$s->fetch()) {
        $results[] = ['row' => $row, 'status' => 'error', 'message' => "Category ID $category_id not found for '$title'"];
        continue;
    }

    // Insert book (no cover/file for bulk — can be added later)
    $stmt = $conn->prepare("INSERT INTO books (title, author_id, category_id, description, cover, file) VALUES (?, ?, ?, ?, '', '')");
    if ($stmt->execute([$title, $author_id, $category_id, $description])) {
        $results[] = ['row' => $row, 'status' => 'success', 'message' => "'$title' added successfully"];
    } else {
        $results[] = ['row' => $row, 'status' => 'error', 'message' => "Database error for '$title'"];
    }
}

fclose($handle);

$encoded = base64_encode(json_encode($results));
header("Location: ../bulk-upload.php?results=$encoded");
exit;
?>