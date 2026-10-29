<?php
session_start();

# If the user is NOT logged in, show login form
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_email'])) {

# Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = "Login — Online Book Store";
include "header.php";
?>

<style>
.login-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 2rem 0;
    border-radius: 16px;
    margin: 1.5rem 0;
}

[data-theme="light"] .login-wrapper {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

[data-theme="dark"] .login-wrapper {
    background: linear-gradient(135deg, #1a1d27, #0f1117);
}

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}

.login-form {
    animation: fadeSlideIn 0.8s ease-out;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2.5rem;
    width: 100%;
    max-width: 30rem;
    box-shadow: 0 8px 32px var(--shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-form:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px var(--card-hover-shadow);
}

.login-form h1 {
    color: var(--text-primary);
    font-size: 2rem;
    font-weight: 800;
    text-align: center;
    margin-bottom: 2rem;
}

.login-form .form-label {
    color: var(--text-secondary);
    font-weight: 500;
}

.btn-primary {
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: scale(1.05);
}

.back-link {
    color: var(--text-secondary);
    text-decoration: none;
    margin-left: 1rem;
    font-size: 0.9rem;
}

.back-link:hover {
    color: var(--text-primary);
}
</style>

<div class="login-wrapper">
    <div class="login-form">
        <h1>🔐 Login</h1>

        <?php if (isset($_GET['error'])) { ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($_GET['error']); ?>
        </div>
        <?php } ?>

        <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($_GET['success']); ?>
        </div>
        <?php } ?>

        <!-- Rate limit warning -->
        <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 3) { ?>
        <div class="alert alert-warning" role="alert">
            ⚠️ Multiple failed attempts detected. Please wait before trying again.
        </div>
        <?php } ?>

        <form method="POST" action="php/author.php">

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" class="form-control" name="email"
                       maxlength="150" required autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password"
                       maxlength="255" required autocomplete="current-password">
            </div>
            <div class="d-flex align-items-center mt-4">
                <button type="submit" class="btn btn-primary px-4">Login</button>
                <a href="index.php" class="back-link">← Back</a>
            </div>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>

<?php } else {
    header("Location: index.php");
    exit;
} ?>