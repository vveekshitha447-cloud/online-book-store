<?php
session_start();

# Only admin can do this
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['request_id']) &&
    isset($_POST['title']) &&
    isset($_POST['author_id']) &&
    isset($_POST['category_id']) &&
    isset($_FILES['cover'])) {

    include "../db_conn.php";

    $request_id    = $_POST['request_id'];
    $title         = trim($_POST['title']);
    $description   = trim($_POST['description']);
    $author_id     = $_POST['author_id'];
    $category_id   = $_POST['category_id'];
    $uploaded_file = trim($_POST['uploaded_file']);

    # Handle cover image upload
    $cover    = $_FILES['cover'];
    $coverExt = strtolower(pathinfo($cover['name'], PATHINFO_EXTENSION));

    $allowedExt = ['jpg', 'jpeg', 'png'];
    if (!in_array($coverExt, $allowedExt)) {
        $em = "Only JPG and PNG cover images are allowed.";
        header("Location: ../book-requests-admin.php?error=$em");
        exit;
    }

    $coverName = time() . '_cover.' . $coverExt;
    $coverPath = "../uploads/cover/" . $coverName;

    if (!move_uploaded_file($cover['tmp_name'], $coverPath)) {
        $em = "Cover image upload failed. Please try again.";
        header("Location: ../book-requests-admin.php?error=$em");
        exit;
    }

    # Insert book into books table — file is already in uploads/files/
    $sql  = "INSERT INTO books (title, author_id, category_id, description, cover, file) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $title,
        $author_id,
        $category_id,
        $description,
        $coverName,
        $uploaded_file
    ]);

    if ($stmt->rowCount() === 1) {
        # Mark request as added to store
        $sql  = "UPDATE book_requests SET status = 'added_to_store' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$request_id]);

        $sm = "Book has been added to the store successfully!";
        header("Location: ../book-requests-admin.php?success=$sm");
        exit;
    } else {
        $em = "Failed to add book to store. Please try again.";
        header("Location: ../book-requests-admin.php?error=$em");
        exit;
    }

} else {
    header("Location: ../book-requests-admin.php");
    exit;
}
?>