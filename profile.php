<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, full_name, email, role, bio, phone, profile_picture FROM admin WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: logout.php");
    exit;
}

// Wishlist count
$wStmt = $conn->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
$wStmt->execute([$user_id]);
$wishlistCount = $wStmt->fetch()['total'];

// Books read count
$bStmt = $conn->prepare("SELECT COUNT(DISTINCT book_id) as total FROM reading_history WHERE user_id = ?");
$bStmt->execute([$user_id]);
$booksRead = $bStmt->fetch()['total'];

// Fetch all badges with earned status
$badgeStmt = $conn->prepare("
    SELECT b.*, IF(ub.id IS NOT NULL, 1, 0) as earned
    FROM badges b
    LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
    ORDER BY b.required_books ASC
");
$badgeStmt->execute([$user_id]);
$allBadges = $badgeStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Profile — Online Book Store";
include "header.php";
?>

<style>
.profile-wrapper { max-width: 860px; margin: 2rem auto; }

.profile-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 32px var(--shadow);
}

.profile-banner {
    height: 140px;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    position: relative;
}

.profile-avatar-wrap {
    position: absolute;
    bottom: -50px;
    left: 36px;
}

.profile-avatar {
    width: 100px; height: 100px;
    border-radius: 50%;
    border: 4px solid var(--bg-card);
    object-fit: cover;
    background: #2d3148;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem;
    overflow: hidden;
}

.profile-avatar img {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-body { padding: 64px 36px 36px; }

.profile-name {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 2px;
}

.profile-role {
    display: inline-block;
    background: <?= $user['role'] === 'admin' ? '#e94560' : '#0d6efd' ?>;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 12px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.profile-email {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 16px;
}

.profile-bio {
    color: var(--text-secondary);
    font-size: 0.92rem;
    line-height: 1.6;
    margin-bottom: 20px;
    font-style: italic;
}

.profile-stats {
    display: flex;
    gap: 24px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}

.stat-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 14px 24px;
    text-align: center;
    min-width: 110px;
}

.stat-box .stat-num {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-primary);
}

.stat-box .stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.profile-info-row {
    display: flex;
    gap: 12px;
    align-items: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 8px;
}

.profile-info-row span { color: var(--text-primary); font-weight: 500; }

.profile-actions { display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap; }

/* Badges Section */
.badges-section { margin-top: 28px; border-top: 1px solid var(--border-color); padding-top: 24px; }
.badges-section h5 { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
.badges-grid { display: flex; flex-wrap: wrap; gap: 12px; }
.badge-item { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 110px; padding: 14px 8px; border-radius: 14px; border: 1px solid var(--border-color); background: var(--bg-secondary); text-align: center; transition: transform 0.2s; }
.badge-item.earned { border-color: #D4AF37; background: #FAEEDA; }
.badge-item:hover { transform: translateY(-3px); }
.badge-icon-big { font-size: 2rem; margin-bottom: 6px; }
.badge-name-sm { font-size: 0.72rem; font-weight: 700; color: var(--text-primary); }
.badge-req-sm { font-size: 0.65rem; color: var(--text-secondary); margin-top: 2px; }
.badge-earned-label { font-size: 0.6rem; margin-top: 4px; background: #4a7c59; color: #fff; border-radius: 20px; padding: 1px 8px; }
.badge-locked-label { font-size: 0.6rem; margin-top: 4px; color: var(--text-muted); }
</style>

<div class="profile-wrapper">

    <?php if (isset($_GET['success'])) { ?>
    <div class="alert alert-success mb-3"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php } ?>

    <div class="profile-card">
        <div class="profile-banner">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_picture']) && file_exists("uploads/profile/" . $user['profile_picture'])): ?>
                        <img src="uploads/profile/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Avatar">
                    <?php else: ?>
                        <?= mb_strtoupper(mb_substr($user['full_name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-body">
            <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="profile-role"><?= htmlspecialchars($user['role']) ?></div>

            <div class="profile-email">✉️ <?= htmlspecialchars($user['email']) ?></div>

            <?php if (!empty($user['phone'])): ?>
            <div class="profile-info-row">📞 <span><?= htmlspecialchars($user['phone']) ?></span></div>
            <?php endif; ?>

            <?php if (!empty($user['bio'])): ?>
            <div class="profile-bio">"<?= htmlspecialchars($user['bio']) ?>"</div>
            <?php else: ?>
            <div class="profile-bio" style="opacity:0.5;">No bio added yet.</div>
            <?php endif; ?>

            <div class="profile-stats">
                <div class="stat-box">
                    <div class="stat-num">❤️ <?= $wishlistCount ?></div>
                    <div class="stat-label">Wishlist</div>
                </div>
                <div class="stat-box">
                    <div class="stat-num">📗 <?= $booksRead ?></div>
                    <div class="stat-label">Books Read</div>
                </div>
                <div class="stat-box">
                    <div class="stat-num">👤</div>
                    <div class="stat-label"><?= ucfirst($user['role']) ?></div>
                </div>
            </div>

            <!-- BADGES SECTION -->
            <?php if (!empty($allBadges)): ?>
            <div class="badges-section">
                <h5>🏅 My Badges</h5>
                <div class="badges-grid">
                    <?php foreach ($allBadges as $b): ?>
                    <div class="badge-item <?= $b['earned'] ? 'earned' : '' ?>">
                        <div class="badge-icon-big"><?= $b['icon'] ?></div>
                        <div class="badge-name-sm"><?= htmlspecialchars($b['name']) ?></div>
                        <div class="badge-req-sm"><?= $b['required_books'] ?> books</div>
                        <?php if ($b['earned']): ?>
                            <span class="badge-earned-label">✓ Earned</span>
                        <?php else: ?>
                            <span class="badge-locked-label"><?= $booksRead ?>/<?= $b['required_books'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="profile-actions">
                <a href="edit-profile.php" class="btn btn-primary">✏️ Edit Profile</a>
                <a href="wishlist.php" class="btn btn-outline-danger">❤️ My Wishlist</a>
                <a href="change-password.php" class="btn btn-outline-secondary">🔒 Change Password</a>
                <a href="logout.php" class="btn btn-outline-dark">🚪 Logout</a>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>