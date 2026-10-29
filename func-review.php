<?php
session_start();
include "../db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id     = $_SESSION['user_id'];
$book_id     = intval($_POST['book_id']);
$rating      = intval($_POST['rating']);
$review_text = htmlspecialchars(strip_tags(trim($_POST['review'])));

if ($book_id <= 0) {
    header("Location: ../index.php");
    exit;
}

if ($rating < 1 || $rating > 5) {
    header("Location: ../book-detail.php?id=$book_id&error=Please select a star rating first.");
    exit;
}

// Insert or update (one review per user per book)
$stmt = $conn->prepare("
    INSERT INTO reviews (user_id, book_id, rating, review, created_at)
    VALUES (?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE
        rating      = VALUES(rating),
        review      = VALUES(review),
        created_at  = NOW()
");
$stmt->execute([$user_id, $book_id, $rating, $review_text]);

header("Location: ../book-detail.php?id=$book_id&success=Your review has been saved!");
exit;