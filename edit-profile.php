<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, full_name, email, bio, phone, profile_picture FROM admin WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$pageTitle = "Edit Profile — Online Book Store";
include "header.php";
?>

<style>
.edit-wrapper {
    max-width: 600px;
    margin: 2rem auto;
}
.edit-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 8px 32px var(--shadow);
}
.edit-card h2 {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 1.5rem;
}
.avatar-preview {
    width: 90px; height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--border-color);
    margin-bottom: 12px;
    font-size: 2.2rem;
    display: flex; align-items: center; justify-content: center;
    background: var(--bg-secondary);
    color: var(--text-primary);
    overflow: hidden;
}
.avatar-preview img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
</style>

<div class="edit-wrapper">

    <?php if (isset($_GET['error'])) { ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php } ?>

    <div class="edit-card">
        <h2>✏️ Edit Profile</h2>

        <!-- Avatar preview -->
        <div class="avatar-preview" id="avatarPreview">
            <?php if (!empty($user['profile_picture']) && file_exists("uploads/profile/" . $user['profile_picture'])): ?>
                <img src="uploads/profile/<?= htmlspecialchars($user['profile_picture']) ?>" id="previewImg" alt="Avatar">
            <?php else: ?>
                <span id="avatarInitial"><?= mb_strtoupper(mb_substr($user['full_name'], 0, 1)) ?></span>
            <?php endif; ?>
        </div>

        <form method="POST" action="php/update-profile.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)) ?>">

            <div class="mb-3">
                <label class="form-label fw-600">Profile Picture</label>
                <input type="file" class="form-control" name="profile_picture"
                       accept="image/jpeg,image/png,image/webp"
                       onchange="previewAvatar(this)">
                <small class="text-muted">JPG, PNG or WEBP — max 2MB</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">Full Name</label>
                <input type="text" class="form-control" name="full_name"
                       value="<?= htmlspecialchars($user['full_name']) ?>"
                       maxlength="100" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">Email Address</label>
                <input type="email" class="form-control" name="email"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       maxlength="150" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">Phone</label>
                <input type="text" class="form-control" name="phone"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                       maxlength="20" placeholder="Optional">
            </div>

            <div class="mb-4">
                <label class="form-label fw-600">Bio</label>
                <textarea class="form-control" name="bio" rows="3"
                          maxlength="300" placeholder="Tell something about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary w-100">💾 Save Changes</button>
                <a href="profile.php" class="btn btn-outline-secondary w-100">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include "footer.php"; ?>