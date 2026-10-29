<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!empty($_SESSION['csrf_token']) === false) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = "Change Password — Online Book Store";
include "header.php";
?>

<style>
.change-wrapper { max-width: 500px; margin: 2rem auto; }
.change-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 8px 32px var(--shadow);
}
.change-card h2 { font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1.5rem; }
</style>

<div class="change-wrapper">

    <?php if (isset($_GET['error'])) { ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php } ?>
    <?php if (isset($_GET['success'])) { ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php } ?>

    <div class="change-card">
        <h2>🔒 Change Password</h2>
        <form method="POST" action="php/change-password.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password"
                       minlength="8" required placeholder="Min 8 chars, 1 uppercase, 1 number"
                       oninput="checkStrength(this.value)">
                <div id="strengthMsg" style="font-size:0.8rem;margin-top:4px;"></div>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" minlength="8" required>
            </div>
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary w-100">🔒 Update Password</button>
                <a href="profile.php" class="btn btn-outline-secondary w-100">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function checkStrength(val) {
    const el = document.getElementById('strengthMsg');
    if (!val) { el.textContent = ''; return; }
    if (val.length < 8) { el.textContent = '⚠️ Too short'; el.style.color = '#e94560'; }
    else if (!/[A-Z]/.test(val) || !/[0-9]/.test(val)) { el.textContent = '🟡 Add uppercase & number'; el.style.color = '#f5a623'; }
    else { el.textContent = '✅ Strong'; el.style.color = '#28a745'; }
}
</script>

<?php include "footer.php"; ?>