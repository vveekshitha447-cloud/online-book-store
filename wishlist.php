<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT w.book_id, w.added_at, b.title, b.cover,
           a.name AS author_name, c.name AS category_name
    FROM wishlist w
    JOIN books b ON w.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->execute([$user_id]);
$wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Wishlist — Online Book Store";
include "header.php";
?>

<style>
    .wishlist-heading { font-size: 1.7rem; font-weight: 800; color: var(--text-primary); margin-bottom: 24px; }
    .wish-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .wish-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px var(--shadow); }
    .wish-thumb { width: 100%; height: 220px; object-fit: cover; object-position: top; display: block; }
    .wish-body  { padding: 14px; }
    .wish-title { font-weight: 700; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wish-meta  { font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 12px; }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
    .empty-state .icon { font-size: 4rem; margin-bottom: 16px; }
</style>

<div class="container py-4">
    <h2 class="wishlist-heading">❤️ My Wishlist</h2>

    <?php if (empty($wishlist)): ?>
        <div class="empty-state">
            <div class="icon">💔</div>
            <p>Your wishlist is empty.</p>
            <a href="index.php" class="btn btn-primary mt-2">Browse Books</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-4 g-4">
            <?php foreach ($wishlist as $item): ?>
            <div class="col" id="wish-<?= $item['book_id'] ?>">
                <div class="wish-card">
                    <img src="uploads/cover/<?= htmlspecialchars($item['cover']) ?>" class="wish-thumb" alt="<?= htmlspecialchars($item['title']) ?>">
                    <div class="wish-body">
                        <div class="wish-title" title="<?= htmlspecialchars($item['title']) ?>"><?= htmlspecialchars($item['title']) ?></div>
                        <div class="wish-meta">
                            By <?= htmlspecialchars($item['author_name']) ?><br>
                            <?= htmlspecialchars($item['category_name']) ?>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="book-detail.php?id=<?= $item['book_id'] ?>" class="btn btn-success btn-sm w-100">Open</a>
                            <button class="btn btn-outline-danger btn-sm w-100"
                                    onclick="removeWishlist(<?= $item['book_id'] ?>)">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function removeWishlist(bookId) {
        fetch('ajax-wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'book_id=' + bookId
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'removed') {
                var el = document.getElementById('wish-' + bookId);
                if (el) el.remove();
            }
        });
    }

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