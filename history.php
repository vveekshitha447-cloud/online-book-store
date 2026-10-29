<?php
session_start();
include "db_conn.php";
include "php/func-badge.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Award badges based on current reading count
checkAndAwardBadges($conn, $user_id);

$stmt = $conn->prepare("
    SELECT rh.opened_at, b.id AS book_id, b.title, b.cover,
           a.name AS author_name, c.name AS category_name
    FROM reading_history rh
    JOIN books b ON rh.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE rh.user_id = ?
    ORDER BY rh.opened_at DESC
    LIMIT 30
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Reading History — Online Book Store";
include "header.php";
?>

<style>
    .history-wrap { max-width: 820px; margin: 0 auto; padding: 32px 0 60px; }
    .page-heading { font-size: 1.7rem; font-weight: 800; color: var(--text-primary); margin-bottom: 24px; }
    .history-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 14px;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .history-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px var(--shadow); }
    .history-thumb { width: 72px; height: 100px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
    .history-info { flex: 1; }
    .history-title { font-weight: 700; font-size: 1rem; color: var(--text-primary); margin-bottom: 4px; }
    .history-meta  { font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 6px; }
    .history-time  { font-size: 0.76rem; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 20px; padding: 2px 12px; color: var(--text-muted); display: inline-block; }
    .empty-state   { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
    .empty-state .icon { font-size: 4rem; margin-bottom: 16px; }
</style>

<div class="container">
    <div class="history-wrap">
        <h2 class="page-heading">📖 Reading History</h2>

        <?php if (empty($history)): ?>
            <div class="empty-state">
                <div class="icon">📚</div>
                <p>You haven't opened any books yet.</p>
                <a href="index.php" class="btn btn-primary mt-2">Browse Books</a>
            </div>
        <?php else: ?>
            <?php foreach ($history as $item): ?>
            <div class="history-card">
                <img src="uploads/cover/<?= htmlspecialchars($item['cover']) ?>" class="history-thumb" alt="<?= htmlspecialchars($item['title']) ?>">
                <div class="history-info">
                    <div class="history-title"><?= htmlspecialchars($item['title']) ?></div>
                    <div class="history-meta">
                        By <?= htmlspecialchars($item['author_name']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($item['category_name']) ?>
                    </div>
                    <span class="history-time">🕐 <?= date('d M Y, h:i A', strtotime($item['opened_at'])) ?></span>
                </div>
                <a href="book-detail.php?id=<?= $item['book_id'] ?>" class="btn btn-success btn-sm">Open</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function applyTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        var icon  = document.getElementById('toggleIcon');
        var label = document.getElementById('toggleLabel');
        if (t === 'dark') { if(icon) icon.textContent='☀️'; if(label) label.textContent='Light Mode'; }
        else              { if(icon) icon.textContent='🌙'; if(label) label.textContent='Dark Mode'; }
    }
    function toggleTheme() {
        applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    }
    applyTheme(localStorage.getItem('theme') || 'light');
</script>

<?php include "footer.php"; ?>