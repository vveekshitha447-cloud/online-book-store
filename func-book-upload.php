<?php
session_start();
# Only logged in users can upload
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
if (isset($_POST['request_id']) && isset($_FILES['book_file'])) {
    include "../db_conn.php";
    # Include your existing file upload helper
    include "func-file-upload.php";
    $request_id = $_POST['request_id'];
    $user_id    = $_SESSION['user_id'];
    # Check if request is approved (removed user_id check for old requests)
    $sql  = "SELECT * FROM book_requests WHERE id = ? AND status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$request_id]);
    if ($stmt->rowCount() === 0) {
        $em = "Invalid request or not approved yet.";
        header("Location: ../request-book.php?error=$em");
        exit;
    }
    # Use your existing upload_file() function
    $allowed = ['pdf'];
    $result  = upload_file($_FILES['book_file'], $allowed, 'files');
    if ($result['status'] === 'success') {
        $newFileName = $result['data'];
        # Save filename and user_id to book_requests table
        $sql  = "UPDATE book_requests SET uploaded_file = ?, user_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$newFileName, $user_id, $request_id]);
        $sm = "Book uploaded successfully! Admin will add it to the store soon.";
        header("Location: ../request-book.php?success=$sm");
        exit;
    } else {
        $em = $result['data'];
        header("Location: ../request-book.php?error=$em");
        exit;
    }
} else {
    header("Location: ../request-book.php");
    exit;
}
?>