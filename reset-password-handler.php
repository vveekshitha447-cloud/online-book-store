<?php
session_start();
require_once '../db_conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$token           = trim($_POST['token']           ?? '');
$email           = trim($_POST['email']           ?? '');
$password        = $_POST['password']             ?? '';
$confirmPassword = $_POST['confirm_password']     ?? '';

if (empty($token) || empty($email) || empty($password) || empty($confirmPassword)) {
    $_SESSION['rp_error'] = 'All fields are required.';
    header("Location: ../reset-password.php?token=" . urlencode($token));
    exit;
}

if ($password !== $confirmPassword) {
    $_SESSION['rp_error'] = 'Passwords do not match.';
    header("Location: ../reset-password.php?token=" . urlencode($token));
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['rp_error'] = 'Password must be at least 8 characters.';
    header("Location: ../reset-password.php?token=" . urlencode($token));
    exit;
}

// Verify token is still valid — PDO style
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND email = ? AND expires_at > NOW()");
$stmt->execute([$token, $email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $_SESSION['rp_error'] = 'Reset link is invalid or has expired.';
    header("Location: ../reset-password.php?token=" . urlencode($token));
    exit;
}

// Update password in admin table
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE admin SET password = ? WHERE email = ?");
$stmt->execute([$hashedPassword, $email]);

// Delete used token
$stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->execute([$email]);

$_SESSION['login_success'] = 'Password reset successfully! Please log in with your new password.';
header('Location: ../login.php');
exit;