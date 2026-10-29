<?php
session_start();
include '../php/db_conn.php';
include '../php/func-badge.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['book_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = intval($_POST['book_id']);

// Insert or update reading history
$stmt = $conn->prepare("
    INSERT INTO reading_history (user_id, book_id, opened_at)
    VALUES (?, ?, NOW())
    ON DUPLICATE KEY UPDATE opened_at = NOW()
");
$stmt->execute([$user_id, $book_id]);

// Award badges and get names of newly earned ones
$new_badges = awardBadges($conn, $user_id);

echo json_encode([
    'status'    => 'success',
    'new_badge' => !empty($new_badges) ? implode(', ', $new_badges) : null
]);