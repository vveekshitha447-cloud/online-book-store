<?php
session_start();
include "db_conn.php";

# Only track if user is logged in
if (isset($_SESSION['user_id']) && isset($_GET['book_id'])) {

    $book_id = $_GET['book_id'];
    $user_id = $_SESSION['user_id'];
    $action  = $_GET['action'] ?? 'open';

    # Save to book_reads table
    $sql  = "INSERT INTO book_reads (book_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$book_id, $user_id]);

    # Get book file to redirect
    $sql  = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if ($book) {
        if ($action === 'download') {
            # Force download
            header("Location: uploads/files/" . $book['file']);
        } else {
            # Open file
            header("Location: uploads/files/" . $book['file']);
        }
        exit;
    }
}

header("Location: index.php");
exit;