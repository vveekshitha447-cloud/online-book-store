<?php
include $_SERVER['DOCUMENT_ROOT'] . '/onlinebookstore/db_conn.php';

function awardBadges($conn, $user_id) {
    // Count unique books the user has opened (fixes duplicate visit counting)
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT book_id) as total FROM reading_history WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $books_read = $stmt->fetch()['total'];

    // Get all badges the user qualifies for but hasn't received yet
    $stmt = $conn->prepare("
        SELECT b.id, b.name FROM badges b
        WHERE b.required_books <= ?
        AND b.id NOT IN (
            SELECT badge_id FROM user_badges WHERE user_id = ?
        )
    ");
    $stmt->execute([$books_read, $user_id]);
    $new_badges = $stmt->fetchAll();

    $awarded_names = [];

    // Award each new badge
    foreach ($new_badges as $badge) {
        $stmt = $conn->prepare("
            INSERT INTO user_badges (user_id, badge_id, awarded_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$user_id, $badge['id']]);
        $awarded_names[] = $badge['name'];
    }

    return $awarded_names; // returns array of newly awarded badge names
}

function checkAndAwardBadges($conn, $user_id) {
    return awardBadges($conn, $user_id);
}