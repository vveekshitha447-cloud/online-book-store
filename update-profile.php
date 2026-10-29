<?php
session_start();
include "../db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// CSRF
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header("Location: ../edit-profile.php?error=" . urlencode("Invalid request."));
    exit;
}

// Sanitize inputs
$full_name = trim(htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8'));
$email     = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
$phone     = trim(htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'));
$bio       = trim(htmlspecialchars($_POST['bio'] ?? '', ENT_QUOTES, 'UTF-8'));

if (empty($full_name) || empty($email)) {
    header("Location: ../edit-profile.php?error=" . urlencode("Name and email are required."));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../edit-profile.php?error=" . urlencode("Invalid email format."));
    exit;
}

// Check email not taken by another user
$check = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ? LIMIT 1");
$check->execute([$email, $user_id]);
if ($check->rowCount() > 0) {
    header("Location: ../edit-profile.php?error=" . urlencode("This email is already in use."));
    exit;
}

// Handle profile picture upload
$profile_picture = null;
if (!empty($_FILES['profile_picture']['name'])) {
    $file     = $_FILES['profile_picture'];
    $maxSize  = 2 * 1024 * 1024; // 2MB
    $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($file['size'] > $maxSize) {
        header("Location: ../edit-profile.php?error=" . urlencode("Image must be under 2MB."));
        exit;
    }

    if (!in_array($mimeType, $allowed)) {
        header("Location: ../edit-profile.php?error=" . urlencode("Only JPG, PNG, and WEBP images allowed."));
        exit;
    }

    $uploadDir = "../uploads/profile/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Delete old picture
    $old = $conn->prepare("SELECT profile_picture FROM admin WHERE id = ? LIMIT 1");
    $old->execute([$user_id]);
    $oldPic = $old->fetch()['profile_picture'];
    if ($oldPic && file_exists($uploadDir . $oldPic)) {
        unlink($uploadDir . $oldPic);
    }

    $ext             = pathinfo($file['name'], PATHINFO_EXTENSION);
    $profile_picture = "user_{$user_id}_" . time() . "." . strtolower($ext);

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $profile_picture)) {
        header("Location: ../edit-profile.php?error=" . urlencode("Failed to upload image."));
        exit;
    }
}

// Update DB
if ($profile_picture) {
    $stmt = $conn->prepare("UPDATE admin SET full_name=?, email=?, phone=?, bio=?, profile_picture=? WHERE id=?");
    $stmt->execute([$full_name, $email, $phone, $bio, $profile_picture, $user_id]);
} else {
    $stmt = $conn->prepare("UPDATE admin SET full_name=?, email=?, phone=?, bio=? WHERE id=?");
    $stmt->execute([$full_name, $email, $phone, $bio, $user_id]);
}

// Refresh CSRF token
unset($_SESSION['csrf_token']);

header("Location: ../profile.php?success=" . urlencode("Profile updated successfully!"));
exit;