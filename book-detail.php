<?php
session_start();
include "db_conn.php";


# Get book id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$book_id = intval($_GET['id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

# ✅ Track reading history when logged-in user opens book
if ($user_id) {
    try {
        $hist = $conn->prepare("
            INSERT INTO reading_history (user_id, book_id, opened_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE opened_at = NOW()
        ");
        $hist->execute([$user_id, $book_id]);
    } catch (Exception $e) {
        // Silently continue if reading_history table doesn't exist yet
    }
}

# Fetch book
$stmt = $conn->prepare("SELECT b.*, a.name AS author_name, c.name AS category_name
                         FROM books b
                         LEFT JOIN authors a ON a.id = b.author_id
                         LEFT JOIN categories c ON c.id = b.category_id
                         WHERE b.id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: index.php");
    exit;
}

# ✅ Fixed: Join with admin table using email column
$rev_stmt = $conn->prepare("SELECT r.*, u.email AS user_name
                             FROM reviews r
                             LEFT JOIN admin u ON u.id = r.user_id
                             WHERE r.book_id = ?
                             ORDER BY r.created_at DESC");
$rev_stmt->execute([$book_id]);
$reviews = $rev_stmt->fetchAll(PDO::FETCH_ASSOC);

# Average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $avg_rating = array_sum(array_column($reviews, 'rating')) / count($reviews);
}

# Check if current user already reviewed
$user_review = null;
if ($user_id) {
    $ur_stmt = $conn->prepare("SELECT * FROM reviews WHERE book_id = ? AND user_id = ?");
    $ur_stmt->execute([$book_id, $user_id]);
    $user_review = $ur_stmt->fetch(PDO::FETCH_ASSOC);
}

# Related books (same category, exclude current)
$rel_stmt = $conn->prepare("SELECT b.*, a.name AS author_name FROM books b
                             LEFT JOIN authors a ON a.id = b.author_id
                             WHERE b.category_id = ? AND b.id != ?
                             LIMIT 4");
$rel_stmt->execute([$book['category_id'], $book_id]);
$related_books = $rel_stmt->fetchAll(PDO::FETCH_ASSOC);

# Authors & categories for nav
include "php/func-author.php";
include "php/func-category.php";
$authors    = get_all_author($conn);
$categories = get_all_categories($conn);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['title']) ?> — Book Store</title>

    <script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        :root {
            --cream:        #faf7f2;
            --cream-dark:   #f3ede3;
            --gold:         #c9a84c;
            --gold-dark:    #a07830;
            --text-dark:    #2b2118;
            --text-mid:     #6b5b4e;
            --text-light:   #a89080;
            --border:       #e4d9cc;
            --green:        #4a7c59;
            --red:          #9e3a3a;
            --shadow:       rgba(43,33,24,.08);
            --bg-navbar:    #f8f9fa;
            --bg-card:      #ffffff;
            --bg-hero:      #ffffff;
            --toggle-bg:    #e2e8f0;
            --toggle-color: #2b2118;
            --input-bg:     #faf7f2;
            --category-bg:  #faf7f2;
        }
        [data-theme="dark"] {
            --cream:        #0f1117;
            --cream-dark:   #1a1d27;
            --gold:         #e2c97e;
            --gold-dark:    #e2c97e;
            --text-dark:    #e8eaf0;
            --text-mid:     #9aa3b2;
            --text-light:   #6b7280;
            --border:       #2d3148;
            --green:        #4a7c59;
            --red:          #9e3a3a;
            --shadow:       rgba(0,0,0,.4);
            --bg-navbar:    #13161f;
            --bg-card:      #1e2130;
            --bg-hero:      #1a1d27;
            --toggle-bg:    #3b4263;
            --toggle-color: #e8eaf0;
            --input-bg:     #1e2130;
            --category-bg:  #2d3148;
        }
        *, *::before, *::after { box-sizing: border-box; transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease; }
        body { background: var(--cream); font-family: 'Jost', sans-serif; color: var(--text-dark); }

        /* ── Reading Progress Bar ── */
        #reading-progress { position: fixed; top: 0; left: 0; z-index: 9999; height: 4px; width: 0%; background: linear-gradient(90deg, #c9a84c, #e94560); transition: width 0.1s linear; border-radius: 0 2px 2px 0; }

        /* ── Navbar ── */
        .navbar { background-color: var(--bg-navbar) !important; border-bottom: 1px solid var(--border); }
        .navbar-brand { color: var(--text-dark) !important; font-family: 'Playfair Display', serif; font-weight: 700; }
        .nav-link { color: var(--text-mid) !important; }
        .nav-link:hover { color: var(--text-dark) !important; }
        [data-theme="dark"] .navbar-toggler-icon { filter: invert(1); }
        [data-theme="dark"] .navbar-toggler { border-color: var(--border); }
        .theme-toggle-btn { display: flex; align-items: center; gap: 8px; background: var(--toggle-bg); border: none; border-radius: 50px; padding: 6px 14px 6px 10px; cursor: pointer; font-size: 0.82rem; font-weight: 600; font-family: 'Jost', sans-serif; color: var(--toggle-color); white-space: nowrap; }
        .theme-toggle-btn:hover { transform: scale(1.04); }

        /* ── Book Hero ── */
        .book-hero { background: var(--bg-hero); border-bottom: 1px solid var(--border); padding: 48px 0 40px; }
        .book-cover-wrap { position: relative; width: 200px; flex-shrink: 0; border-radius: 10px; overflow: hidden; box-shadow: 0 8px 32px var(--shadow); }
        .book-cover { width: 200px; height: 280px; object-fit: cover; display: block; transition: transform 0.4s ease; }
        .book-cover-wrap:hover .book-cover { transform: scale(1.07); }
        .book-title { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; margin-bottom: 6px; color: var(--text-dark); }
        .book-author { font-size: 1rem; color: var(--text-mid); margin-bottom: 4px; }
        .book-category { display: inline-block; background: var(--category-bg); border: 1px solid var(--border); border-radius: 20px; padding: 3px 14px; font-size: .78rem; font-weight: 600; color: var(--gold-dark); letter-spacing: .05em; margin-bottom: 16px; }
        .book-description { color: var(--text-mid); font-size: .95rem; line-height: 1.7; }

        /* ── Avg rating ── */
        .avg-rating-wrap { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
        .avg-stars { font-size: 1.3rem; letter-spacing: 2px; }
        .avg-number { font-size: 1.5rem; font-weight: 700; color: var(--gold-dark); }
        .avg-count { font-size: .85rem; color: var(--text-light); }

        /* ── Half star support ── */
        .star-display { display: inline-flex; gap: 2px; font-size: 1.3rem; }
        .star-display .s-full { color: var(--gold); }
        .star-display .s-half {
            position: relative; display: inline-block;
            color: var(--border);
        }
        .star-display .s-half::before {
            content: '★';
            position: absolute; left: 0; top: 0;
            width: 50%; overflow: hidden;
            color: var(--gold);
        }
        .star-display .s-empty { color: var(--border); }

        /* ── Rating breakdown bars ── */
        .rating-breakdown { margin-bottom: 20px; }
        .rating-row { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; font-size: 0.82rem; color: var(--text-mid); }
        .rating-bar-bg { flex: 1; height: 7px; background: var(--border); border-radius: 4px; overflow: hidden; }
        .rating-bar-fill { height: 100%; background: linear-gradient(90deg, var(--gold), #e2c97e); border-radius: 4px; transition: width 1s ease; }
        .rating-row .star-label { width: 14px; text-align: right; font-weight: 600; }
        .rating-row .bar-count { width: 22px; color: var(--text-light); }

        /* ── Action buttons ── */
        .btn-open { background: var(--green); color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-family: 'Jost', sans-serif; font-weight: 600; font-size: .9rem; text-decoration: none; display: inline-block; transition: opacity .2s; }
        .btn-open:hover { opacity: .85; color: #fff; }
        .btn-download { background: #2d5986; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-family: 'Jost', sans-serif; font-weight: 600; font-size: .9rem; text-decoration: none; display: inline-block; transition: opacity .2s; margin-left: 8px; }
        .btn-download:hover { opacity: .85; color: #fff; }

        /* ── Reviews Section ── */
        .reviews-section { padding: 48px 0 40px; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; margin-bottom: 28px; padding-bottom: 12px; border-bottom: 2px solid var(--border); color: var(--text-dark); }

        /* ── Write Review Card ── */
        .write-review-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 28px 32px; margin-bottom: 36px; box-shadow: 0 4px 20px var(--shadow); }
        .write-review-card h5 { font-family: 'Playfair Display', serif; font-size: 1.1rem; margin-bottom: 16px; color: var(--text-dark); }

        /* ── Star selector ── */
        .star-selector { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; margin-bottom: 16px; }
        .star-selector input { display: none; }
        .star-selector label { font-size: 2rem; color: var(--border); cursor: pointer; transition: color .15s; }
        .star-selector input:checked ~ label,
        .star-selector label:hover,
        .star-selector label:hover ~ label { color: var(--gold); }

        .review-textarea { width: 100%; border: 1.5px solid var(--border); border-radius: 10px; padding: 12px 16px; font-family: 'Jost', sans-serif; font-size: .92rem; resize: vertical; min-height: 90px; outline: none; background: var(--input-bg); color: var(--text-dark); transition: border-color .2s, background-color .3s, color .3s; }
        .review-textarea::placeholder { color: var(--text-light); }
        .review-textarea:focus { border-color: var(--gold); background: var(--bg-card); }
        .btn-submit-review { margin-top: 12px; background: var(--text-dark); color: var(--cream); border: none; padding: 11px 28px; border-radius: 8px; font-family: 'Jost', sans-serif; font-weight: 600; font-size: .88rem; letter-spacing: .06em; text-transform: uppercase; cursor: pointer; transition: opacity .2s, background-color .3s, color .3s; }
        .btn-submit-review:hover { opacity: .85; }

        .login-prompt { background: var(--bg-card); border: 1.5px dashed var(--border); border-radius: 16px; padding: 24px; text-align: center; color: var(--text-mid); margin-bottom: 36px; font-size: .95rem; }
        .login-prompt a { color: var(--gold-dark); font-weight: 600; }

        /* ── Review Cards ── */
        .review-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 20px 24px; margin-bottom: 16px; box-shadow: 0 2px 12px var(--shadow); display: flex; gap: 16px; align-items: flex-start; }
        .reviewer-avatar { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; color: #fff; flex-shrink: 0; text-transform: uppercase; }
        .review-body { flex: 1; }
        .review-header { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
        .reviewer-name { font-weight: 700; font-size: .95rem; color: var(--text-dark); }
        .review-date { font-size: .76rem; color: var(--text-light); background: var(--category-bg); padding: 2px 8px; border-radius: 20px; }
        .review-stars { font-size: 1rem; color: var(--gold); margin-bottom: 7px; }
        .review-text { font-size: .92rem; color: var(--text-mid); line-height: 1.6; }
        .no-reviews { text-align: center; color: var(--text-light); padding: 40px; font-size: .95rem; }

        /* ── Related Books ── */
        .related-section { padding: 0 0 60px; }
        .related-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: transform 0.25s, box-shadow 0.25s; text-decoration: none; display: block; }
        .related-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px var(--shadow); }
        .related-card img { width: 100%; height: 200px; object-fit: cover; object-position: top; display: block; transition: transform 0.3s; }
        .related-card:hover img { transform: scale(1.04); }
        .related-card-body { padding: 12px 14px; }
        .related-card-title { font-weight: 700; font-size: .88rem; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
        .related-card-author { font-size: .78rem; color: var(--text-light); }

        /* ── Alerts ── */
        [data-theme="dark"] .alert-success { background-color: #0f2e1a; color: #4ade80; border-color: #1a5c35; }
        [data-theme="dark"] .alert-danger  { background-color: #2e0f0f; color: #f87171; border-color: #5c1a1a; }

        /* ── Avatar colors ── */
        .av-0 { background: #e94560; } .av-1 { background: #0f3460; }
        .av-2 { background: #4a7c59; } .av-3 { background: #c9a84c; }
        .av-4 { background: #7c4dff; } .av-5 { background: #2d5986; }
    </style>
</head>
<body>

<!-- Reading Progress Bar -->
<div id="reading-progress"></div>

<!-- NAVBAR -->
<div class="container">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">📚 Online Book Store</a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navDetail"
                    aria-controls="navDetail" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navDetail">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="request-book.php">Add a Book</a></li>
                        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='history.php'?'active':'' ?>" href="history.php">📖 History</a></li>
                        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='wishlist.php'?'active':'' ?>" href="wishlist.php">❤️ Wishlist</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php endif; ?>
                </ul>
                <button class="theme-toggle-btn ms-3" id="themeToggle" onclick="toggleTheme()" title="Toggle dark/light mode">
                    <span id="toggleIcon">🌙</span>
                    <span id="themeLabel">Dark Mode</span>
                </button>
            </div>
        </div>
    </nav>
</div>

<!-- BOOK HERO -->
<div class="book-hero">
    <div class="container">
        <div class="d-flex gap-4 flex-wrap">
            <div class="book-cover-wrap">
                <img src="uploads/cover/<?= htmlspecialchars($book['cover']) ?>"
                     class="book-cover" alt="<?= htmlspecialchars($book['title']) ?>">
            </div>
            <div style="flex:1; min-width:260px;">
                <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>
                <p class="book-author">By <strong><?= htmlspecialchars($book['author_name']) ?></strong></p>
                <span class="book-category"><?= htmlspecialchars($book['category_name']) ?></span>

                <?php if (count($reviews) > 0):
                    $ratingCounts = [5=>0,4=>0,3=>0,2=>0,1=>0];
                    foreach ($reviews as $r) $ratingCounts[(int)$r['rating']]++;
                    $total = count($reviews);
                ?>
                <div class="avg-rating-wrap">
                    <span class="avg-number"><?= number_format($avg_rating, 1) ?></span>
                    <span class="star-display">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($avg_rating >= $i): ?>
                                <span class="s-full">★</span>
                            <?php elseif ($avg_rating >= $i - 0.5): ?>
                                <span class="s-half">★</span>
                            <?php else: ?>
                                <span class="s-empty">★</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </span>
                    <span class="avg-count">(<?= $total ?> review<?= $total > 1 ? 's' : '' ?>)</span>
                </div>
                <div class="rating-breakdown">
                    <?php for ($s = 5; $s >= 1; $s--):
                        $pct = $total > 0 ? ($ratingCounts[$s] / $total * 100) : 0;
                    ?>
                    <div class="rating-row">
                        <span class="star-label"><?= $s ?></span>
                        <span style="font-size:0.75rem;color:var(--gold);">★</span>
                        <div class="rating-bar-bg">
                            <div class="rating-bar-fill" style="width:<?= round($pct) ?>%"></div>
                        </div>
                        <span class="bar-count"><?= $ratingCounts[$s] ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <?php if ($book['description']): ?>
                    <p class="book-description"><?= htmlspecialchars($book['description']) ?></p>
                <?php endif; ?>

                <div class="mt-3">
                    <?php if ($user_id): ?>
                        <a href="track.php?book_id=<?= $book['id'] ?>&action=open" class="btn-open" target="_blank">📖 Open Book</a>
                        <a href="track.php?book_id=<?= $book['id'] ?>&action=download" class="btn-download">⬇ Download</a>

                        <?php
                        $readStmt = $conn->prepare("SELECT id FROM reading_history WHERE user_id = ? AND book_id = ?");
                        $readStmt->execute([$user_id, $book_id]);
                        $alreadyRead = $readStmt->fetch();
                        ?>

                    <?php else: ?>
                        <a href="login.php" class="btn-open">Login to Read</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- REVIEWS SECTION -->
<div class="reviews-section">
    <div class="container">
        <h2 class="section-title">⭐ Reviews &amp; Ratings</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if ($user_id): ?>
            <div class="write-review-card">
                <h5><?= $user_review ? '✏️ Edit Your Review' : '✍️ Write a Review' ?></h5>
                <form method="POST" action="php/save-review.php">
                    <input type="hidden" name="book_id" value="<?= $book_id ?>">
                    <div class="star-selector">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>"
                                <?= ($user_review && $user_review['rating'] == $i) ? 'checked' : '' ?> required>
                            <label for="star<?= $i ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <textarea class="review-textarea" name="review"
                              placeholder="Share your thoughts about this book..."><?= $user_review ? htmlspecialchars($user_review['review']) : '' ?></textarea>
                    <button type="submit" class="btn-submit-review">
                        <?= $user_review ? 'Update Review' : 'Submit Review' ?>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <a href="login.php">Login</a> to leave a review and rating.
            </div>
        <?php endif; ?>

        <!-- Reviews list -->
        <?php if (count($reviews) === 0): ?>
            <div class="no-reviews">📭 No reviews yet. Be the first to review this book!</div>
        <?php else: ?>
            <?php foreach ($reviews as $idx => $review):
                $initials  = strtoupper(substr($review['user_name'] ?? 'U', 0, 1));
                $colorClass = 'av-' . ($idx % 6);
            ?>
                <div class="review-card">
                    <div class="reviewer-avatar <?= $colorClass ?>"><?= $initials ?></div>
                    <div class="review-body">
                        <div class="review-header">
                            <span class="reviewer-name"><?= htmlspecialchars($review['user_name'] ?? 'Anonymous') ?></span>
                            <span class="review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                        </div>
                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $review['rating'] ? '★' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($review['review'])): ?>
                            <p class="review-text"><?= htmlspecialchars($review['review']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- RELATED BOOKS -->
<?php if (count($related_books) > 0): ?>
<div class="related-section">
    <div class="container">
        <h2 class="section-title">📚 You May Also Like</h2>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php foreach ($related_books as $rb): ?>
            <div class="col">
                <a href="book-detail.php?id=<?= $rb['id'] ?>" class="related-card">
                    <img src="uploads/cover/<?= htmlspecialchars($rb['cover']) ?>"
                         alt="<?= htmlspecialchars($rb['title']) ?>">
                    <div class="related-card-body">
                        <div class="related-card-title" title="<?= htmlspecialchars($rb['title']) ?>"><?= htmlspecialchars($rb['title']) ?></div>
                        <div class="related-card-author">By <?= htmlspecialchars($rb['author_name']) ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // ── Mark as Read ──
    function markAsRead(bookId) {
        const btn = document.getElementById('markReadBtn');
        btn.disabled = true;
        btn.textContent = 'Marking...';
        fetch('php/mark-read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'book_id=' + bookId
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                btn.textContent = '✅ Marked as Read';
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                if (data.new_badge) {
                    alert('🏆 Congratulations! You earned the "' + data.new_badge + '" badge!');
                }
            } else {
                btn.disabled = false;
                btn.textContent = '📗 Mark as Read';
            }
        })
        .catch(() => { btn.disabled = false; btn.textContent = '📗 Mark as Read'; });
    }

    // ── Reading progress bar ──
    window.addEventListener('scroll', function () {
        var scrollTop  = window.scrollY;
        var docHeight  = document.documentElement.scrollHeight - window.innerHeight;
        var pct        = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        document.getElementById('reading-progress').style.width = pct + '%';
    });

    // ── Theme toggle ──
    function applyTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        var icon  = document.getElementById('toggleIcon');
        var label = document.getElementById('themeLabel');
        if (t === 'dark') { icon.textContent = '☀️'; label.textContent = 'Light Mode'; }
        else              { icon.textContent = '🌙'; label.textContent = 'Dark Mode';  }
    }
    function toggleTheme() {
        applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    }
    applyTheme(localStorage.getItem('theme') || 'light');
</script>

</body>
</html>