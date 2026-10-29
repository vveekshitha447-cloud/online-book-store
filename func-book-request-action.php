<?php
session_start();

# Only admin can perform this action
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['id']) && isset($_POST['action'])) {

    # Database Connection File
    include "../db_conn.php";

    $id         = $_POST['id'];
    $action     = $_POST['action'];
    $admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';

    # Validate action
    $allowed_actions = ['approve', 'reject'];
    if (!in_array($action, $allowed_actions)) {
        $em = "Invalid action.";
        header("Location: ../book-requests-admin.php?error=" . urlencode($em));
        exit;
    }

    # Map action to status
    $status = ($action === 'approve') ? 'approved' : 'rejected';

    # Make sure the request is still pending
    $check = $conn->prepare("SELECT * FROM book_requests WHERE id = ? AND status = 'pending'");
    $check->execute([$id]);

    if ($check->rowCount() === 0) {
        $em = "Request not found or already reviewed.";
        header("Location: ../book-requests-admin.php?error=" . urlencode($em));
        exit;
    }

    $book_request = $check->fetch();

    # Update the request status
    $sql = "UPDATE book_requests SET status = ?, admin_note = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$status, $admin_note, $id]);

    if ($stmt->rowCount() === 1) {

        # ── Send Notification to User ──────────────────────────────────
        if (!is_null($book_request['user_id'])) {

            $book_title = $book_request['title'];

            if ($status === 'approved') {
                $message = "✅ Your book \"$book_title\" has been approved and will be added to the store soon!";
            } else {
                $note_part = !empty($admin_note) ? " Reason: $admin_note" : "";
                $message = "❌ Your book \"$book_title\" was not approved.$note_part";
            }

            $notif_sql  = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $notif_stmt = $conn->prepare($notif_sql);
            $notif_stmt->execute([$book_request['user_id'], $message]);
        }
        # ───────────────────────────────────────────────────────────────

        $sm = "Book request has been " . $status . " successfully.";
        header("Location: ../book-requests-admin.php?success=" . urlencode($sm));
        exit;

    } else {
        $em = "Something went wrong. Please try again.";
        header("Location: ../book-requests-admin.php?error=" . urlencode($em));
        exit;
    }

} else {
    header("Location: ../book-requests-admin.php");
    exit;
}
?>