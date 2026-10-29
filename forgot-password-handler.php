<?php
session_start();
require_once '../db_conn.php';
require_once '../phpmailer/src/PHPMailer.php';
require_once '../phpmailer/src/SMTP.php';
require_once '../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forgot-password.php');
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['fp_error'] = 'Please enter a valid email address.';
    header('Location: ../forgot-password.php');
    exit;
}

// Check if email exists — PDO style
$stmt = $conn->prepare("SELECT id, full_name FROM admin WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['fp_success'] = 'If that email is registered, a reset link has been sent.';
    header('Location: ../forgot-password.php');
    exit;
}

// Generate secure token
$token     = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Save token to DB
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
$stmt->execute([$email, $token, $expiresAt]);

// Build reset link
$resetLink = "http://localhost/onlinebookstore/reset-password.php?token=" . $token;

// Send email via PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your_email@gmail.com'; // ⚠️ Replace with your Gmail
    $mail->Password   = 'your_app_password';    // ⚠️ Replace with Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('your_email@gmail.com', 'Online Bookstore');
    $mail->addAddress($email, $user['full_name']);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request - Online Bookstore';

    $fullName = $user['full_name'];
    $year     = date('Y');
    $mail->Body = "
        <div style='font-family:Segoe UI,sans-serif;max-width:500px;margin:auto;padding:30px;border:1px solid #eee;border-radius:10px;'>
          <h2 style='color:#e74c3c;'>Online Bookstore</h2>
          <p>Hi <strong>$fullName</strong>,</p>
          <p>We received a request to reset your password. Click the button below to create a new password:</p>
          <div style='text-align:center;margin:30px 0;'>
            <a href='$resetLink' style='background:#e74c3c;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:1rem;'>
              Reset My Password
            </a>
          </div>
          <p style='color:#777;font-size:0.85rem;'>This link will expire in <strong>1 hour</strong>. If you didn't request a password reset, you can safely ignore this email.</p>
          <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'/>
          <p style='color:#aaa;font-size:0.8rem;'>Online Bookstore &copy; $year</p>
        </div>
    ";
    $mail->AltBody = "Reset your password: $resetLink (expires in 1 hour)";

    $mail->send();
    $_SESSION['fp_success'] = 'A password reset link has been sent to your email. Please check your inbox.';

} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
    $_SESSION['fp_error'] = 'Failed to send email. Please try again later.';
}

header('Location: ../forgot-password.php');
exit;