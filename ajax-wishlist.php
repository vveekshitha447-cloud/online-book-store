<?php
session_start();
include "db_conn.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = intval($_POST['book_id']);

if ($book_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid book']);
    exit;
}

// Check if already in wishlist
$check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
$check->execute([$user_id, $book_id]);
$exists = $check->fetch();

if ($exists) {
    // Remove from wishlist
    $del = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
    $del->execute([$user_id, $book_id]);
    echo json_encode(['status' => 'removed']);
} else {
    // Add to wishlist
    $ins = $conn->prepare("INSERT INTO wishlist (user_id, book_id, added_at) VALUES (?, ?, NOW())");
    $ins->execute([$user_id, $book_id]);
    echo json_encode(['status' => 'added']);
}