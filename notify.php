<?php
function addNotification($conn, $user_id, $message, $type = 'info') {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $message, $type]);
}
?>