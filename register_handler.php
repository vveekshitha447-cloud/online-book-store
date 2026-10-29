<?php
session_start();

if (isset($_POST['full_name']) && isset($_POST['email']) &&
    isset($_POST['password']) && isset($_POST['confirm_password'])) {

    # Database Connection File
    include "../db_conn.php";

    # ── 1. CSRF Protection ──────────────────────────────────────────
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        error_log("CSRF attack detected on register from IP: " . $_SERVER['REMOTE_ADDR']);
        header("Location: ../register.php?error=" . urlencode("Invalid request. Please try again."));
        exit;
    }

    # ── 2. Input Sanitization ───────────────────────────────────────
    $full_name        = trim(htmlspecialchars($_POST['full_name'], ENT_QUOTES, 'UTF-8'));
    $email            = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    # ── 3. Validation ───────────────────────────────────────────────
    $redirect_base = "../register.php?full_name=" . urlencode($full_name) . "&email=" . urlencode($email);

    if (empty($full_name) || empty($email) || empty($password)) {
        header("Location: $redirect_base&error=" . urlencode("All fields are required."));
        exit;
    }

    if (strlen($full_name) > 100) {
        header("Location: $redirect_base&error=" . urlencode("Full name is too long."));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        header("Location: $redirect_base&error=" . urlencode("Please enter a valid email address."));
        exit;
    }

    if (strlen($password) < 8) {
        header("Location: $redirect_base&error=" . urlencode("Password must be at least 8 characters."));
        exit;
    }

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        header("Location: $redirect_base&error=" . urlencode("Password must contain at least one uppercase letter and one number."));
        exit;
    }

    if ($password !== $confirm_password) {
        header("Location: $redirect_base&error=" . urlencode("Passwords do not match."));
        exit;
    }

    # ── 4. Check if email already exists ───────────────────────────
    $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        header("Location: $redirect_base&error=" . urlencode("An account with this email already exists."));
        exit;
    }

    # ── 5. Hash password with bcrypt ───────────────────────────────
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    # ── 6. Insert new user ─────────────────────────────────────────
    $stmt = $conn->prepare("INSERT INTO admin (full_name, email, password, role) VALUES (?, ?, ?, 'user')");


    if ($stmt->execute([$full_name, $email, $hashed_password])) {
        unset($_SESSION['csrf_token']);

        # ── 7. Send welcome notifications ──────────────────────────
        $new_user_id = $conn->lastInsertId();
        include "notify.php";
        addNotification($conn, $new_user_id, "Welcome to Online Book Store, {$full_name}! Start exploring books.", "success");
        addNotification($conn, $new_user_id, "Tip: Add books to your wishlist and download them anytime!", "info");

        header("Location: ../login.php?success=" . urlencode("Registration successful! Please log in.") . "&toast=" . urlencode("Account created! Welcome aboard.") . "&type=success");
        exit;
    } else {
        header("Location: ../register.php?error=" . urlencode("Registration failed. Please try again."));
        exit;
    }

} else {
    header("Location: ../register.php");
    exit;
}