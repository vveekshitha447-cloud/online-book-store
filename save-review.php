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

// Check if user already reviewed this book
$check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ?");
$check->execute([$user_id, $book_id]);
$existing = $check->fetch();

if ($existing) {
    // Update existing review
    $stmt = $conn->prepare("UPDATE reviews SET rating = ?, review = ?, created_at = NOW() WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$rating, $review_text, $user_id, $book_id]);
} else {
    // Insert new review
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $book_id, $rating, $review_text]);
}

header("Location: ../book-detail.php?id=$book_id&success=Your review has been saved!");
exit;