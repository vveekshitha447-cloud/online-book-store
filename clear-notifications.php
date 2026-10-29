<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../db_conn.php";
$conn->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$_SESSION['user_id']]);
header("Location: ../notifications.php");
exit;
?>