<?php
session_start();

if (isset($_POST['user_name']) &&
    isset($_POST['user_email']) &&
    isset($_POST['title']) &&
    isset($_POST['author']) &&
    isset($_POST['condition'])) {

    # Database Connection File
    include "../db_conn.php";

    # Validation helper function
    include "func-validation.php";

    # File upload helper (same one used by func-book-upload.php)
    include "func-file-upload.php";

    $user_name   = trim($_POST['user_name']);
    $user_email  = trim($_POST['user_email']);
    $title       = trim($_POST['title']);
    $author      = trim($_POST['author']);
    $isbn        = trim($_POST['isbn'] ?? '');
    $genre       = trim($_POST['genre'] ?? '');
    $condition   = $_POST['condition'];
    $description = trim($_POST['description'] ?? '');

    # Get user_id from session if logged in
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    $location = "../request-book.php";
    $ms = "error";

    # Validate required fields
    is_empty($user_name,  "Name",       $location, $ms, "");
    is_empty($user_email, "Email",      $location, $ms, "");
    is_empty($title,      "Book Title", $location, $ms, "");
    is_empty($author,     "Author",     $location, $ms, "");
    is_empty($condition,  "Condition",  $location, $ms, "");

    # Validate condition value
    $allowed = ['new', 'good', 'fair'];
    if (!in_array($condition, $allowed)) {
        $em = "Invalid book condition selected.";
        header("Location: ../request-book.php?error=" . urlencode($em));
        exit;
    }

    # ── Handle File Upload (using existing helper) ──────────────────────
    if (!isset($_FILES['book_file']) || $_FILES['book_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $em = "Please upload a PDF file of your book.";
        header("Location: ../request-book.php?error=" . urlencode($em));
        exit;
    }

    $result = upload_file($_FILES['book_file'], ['pdf'], 'files');

    if ($result['status'] !== 'success') {
        $em = $result['data'];
        header("Location: ../request-book.php?error=" . urlencode($em));
        exit;
    }

    $uploaded_file = $result['data'];
    # ───────────────────────────────────────────────────────────────────

    # Insert book request into database (with uploaded_file)
    $sql = "INSERT INTO book_requests 
            (user_id, user_name, user_email, title, author, isbn, genre, `condition`, description, status, uploaded_file) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $user_id,
        $user_name,
        $user_email,
        $title,
        $author,
        $isbn,
        $genre,
        $condition,
        $description,
        $uploaded_file
    ]);

    if ($stmt->rowCount() === 1) {
        $sm = "Your book has been uploaded and submitted for review! Admin will approve it shortly.";
        header("Location: ../request-book.php?success=" . urlencode($sm));
        exit;
    } else {
        $em = "Something went wrong. Please try again.";
        header("Location: ../request-book.php?error=" . urlencode($em));
        exit;
    }

} else {
    header("Location: ../request-book.php");
    exit;
}
?>