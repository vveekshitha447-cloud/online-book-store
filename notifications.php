<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include "db_conn.php";

$user_id = $_SESSION['user_id'];

// Mark all as read when page opens
$conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);

// Fetch all notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

include "header.php";
?>

<style>
.notif-page { max-width: 700px; margin: 2rem auto; padding: 0 1rem 4rem; }
.notif-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem;
}
.notif-header h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; }
.clear-btn {
    background: transparent; border: 1px solid var(--border-color);
    color: var(--text-secondary); padding: .4rem 1rem;
    border-radius: 8px; font-size: .82rem; cursor: pointer;
    transition: all .2s;
}
.clear-btn:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

.notif-card {
    display: flex; align-items: flex-start; gap: 1rem;
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 12px; padding: 1rem 1.2rem;
    margin-bottom: .75rem; transition: all .2s;
    animation: slideIn .3s ease;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.notif-card:hover { border-color: #6366f1; transform: translateX(4px); }
.notif-icon {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.icon-info    { background: rgba(99,102,241,.15); }
.icon-success { background: rgba(74,222,128,.15); }
.icon-warning { background: rgba(250,204,21,.15); }
.icon-error   { background: rgba(248,113,113,.15); }

.notif-body { flex: 1; }
.notif-body p { margin: 0; font-size: .9rem; color: var(--text-primary); }
.notif-time { font-size: .75rem; color: var(--text-muted); margin-top: .3rem; }

.empty-state {
    text-align: center; padding: 4rem 2rem;
    color: var(--text-muted);
}
.empty-state .emoji { font-size: 3rem; margin-bottom: 1rem; display: block; }
.empty-state p { font-size: .95rem; }
</style>

<div class="notif-page">
    <div class="notif-header">
        <h2>🔔 Notifications</h2>
        <?php if (!empty($notifications)): ?>
        <form method="POST" action="php/clear-notifications.php">
            <button class="clear-btn" onclick="return confirm('Clear all notifications?')">Clear All</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
    <div class="empty-state">
        <span class="emoji">🔕</span>
        <p>No notifications yet.<br>We'll notify you when something happens!</p>
    </div>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
        <?php
            $icons = ['info' => '💬', 'success' => '✅', 'warning' => '⚠️', 'error' => '❌'];
            $icon  = $icons[$n['type']] ?? '💬';
            $time  = date('M d, Y • h:i A', strtotime($n['created_at']));
        ?>
        <div class="notif-card">
            <div class="notif-icon icon-<?= $n['type'] ?>"><?= $icon ?></div>
            <div class="notif-body">
                <p><?= htmlspecialchars($n['message']) ?></p>
                <div class="notif-time">🕐 <?= $time ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>