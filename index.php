<?php
session_start();

# Database Connection File
include "db_conn.php";

# Book helper function
include "php/func-book.php";
$allBooks = get_all_books($conn);
$allBooksCount = count($allBooks);

# ── Pagination ─────────────────────────────────────────────────
$booksPerPage = 8;
$totalPages   = ceil($allBooksCount / $booksPerPage);
$currentPage  = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset       = ($currentPage - 1) * $booksPerPage;
$books        = array_slice($allBooks, $offset, $booksPerPage);

# author helper function
include "php/func-author.php";
$authors = get_all_author($conn);

# Category helper function
include "php/func-category.php";
$categories = get_all_categories($conn);

$pageTitle = "Home — Online Book Store";
include "header.php";
?>

<style>
    /* ── Hero ── */
    .hero-section {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 16px;
        padding: 52px 40px;
        margin-bottom: 10px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 260px; height: 260px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -40px;
        width: 320px; height: 320px;
        background: rgba(255,255,255,0.03);
        border-radius: 50%;
    }
    .hero-section h1 {
        font-size: 2.4rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 10px;
    }
    .hero-section p {
        color: #a0b4cc;
        font-size: 1.05rem;
        margin-bottom: 24px;
    }
    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 32px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    .hero-stat {
        background: rgba(255,255,255,0.08);
        border-radius: 12px;
        padding: 12px 28px;
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .hero-stat span {
        display: block;
        font-size: 0.78rem;
        font-weight: 400;
        color: #a0b4cc;
        margin-top: 2px;
    }

    /* ── Search ── */
    .search-wrapper .form-control {
        border-radius: 10px 0 0 10px;
        padding: 12px 18px;
        font-size: 1rem;
    }
    .search-wrapper .btn {
        border-radius: 0 10px 10px 0;
        padding: 0 20px;
    }

    /* ── Cards ── */
    .book-card {
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        background: #1e1e2e;
        border: 1px solid rgba(255,255,255,0.07);
    }
    .book-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.5) !important;
    }

    /* ── Image wrapper ── */
    .img-wrapper {
        position: relative;
        overflow: hidden;
        cursor: pointer;
        height: 300px;
    }
    .cover-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top;
        display: block;
        transition: opacity 0.3s ease;
    }
    .cover-full {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: contain;
        background: rgba(0,0,0,0.93);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        padding: 8px;
    }
    .img-wrapper:hover .cover-full  { opacity: 1; }
    .img-wrapper:hover .cover-thumb { opacity: 0.2; }

    /* ── Badge ── */
    .book-badge {
        position: absolute;
        top: 10px; left: 10px;
        background: linear-gradient(135deg, #e94560, #c0392b);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 2;
    }

    /* ── Wishlist ── */
    .wishlist-btn {
        position: absolute;
        top: 10px; right: 10px;
        background: rgba(0,0,0,0.55);
        border: none;
        border-radius: 50%;
        width: 34px; height: 34px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        font-size: 1.1rem;
        z-index: 3;
        transition: background 0.2s, transform 0.2s;
        color: #fff;
    }
    .wishlist-btn:hover { background: rgba(233,69,96,0.85); transform: scale(1.15); }
    .wishlist-btn.active { background: #e94560; }

    /* ── Card body ── */
    .book-card .card-body {
        padding: 14px;
        background: #1e1e2e;
        color: #e0e0e0;
    }
    .book-card .card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .book-card .card-text {
        font-size: 0.82rem;
        color: #a0b4cc;
        margin-bottom: 8px;
    }

    /* ── Star rating ── */
    .star-rating { color: #f5a623; font-size: 0.85rem; margin-bottom: 8px; }
    .star-rating span { color: #888; font-size: 0.75rem; margin-left: 4px; }

    /* ── Buttons ── */
    .book-card .btn { font-size: 0.85rem; padding: 7px; border-radius: 8px; font-weight: 600; }

    /* ── Sidebar ── */
    .list-group-item { font-size: 0.9rem; }

    /* ── Card fade-in animation ── */
    @keyframes cardFadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .book-col { animation: cardFadeUp 0.4s ease both; }
    .book-col:nth-child(1) { animation-delay: 0.05s; }
    .book-col:nth-child(2) { animation-delay: 0.12s; }
    .book-col:nth-child(3) { animation-delay: 0.19s; }
    .book-col:nth-child(4) { animation-delay: 0.26s; }
    .book-col:nth-child(5) { animation-delay: 0.33s; }
    .book-col:nth-child(6) { animation-delay: 0.40s; }
    .book-col:nth-child(7) { animation-delay: 0.47s; }
    .book-col:nth-child(8) { animation-delay: 0.54s; }

    /* ── Modal ── */
    #coverModal.show {
        display: flex !important;
        animation: fadeIn 0.2s ease;
    }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
</style>

    <?php if (!isset($_SESSION['user_id'])) { ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center mt-3" role="alert">
        <span>📚 New here? Create an account to get started!</span>
        <a href="register.php" class="btn btn-success">Register Now</a>
    </div>
    <?php } ?>

    <!-- HERO -->
    <div class="hero-section">
        <h1>📚 Online Book Store</h1>
        <p>Your digital library — discover, read, and explore books across every genre, all in one place.</p>
        <div class="hero-stats">
            <div class="hero-stat"><?= $allBooksCount ?><span>Total Books</span></div>
            <div class="hero-stat"><?= count($categories) ?><span>Categories</span></div>
            <div class="hero-stat"><?= count($authors) ?><span>Authors</span></div>
        </div>
    </div>

    <!-- SEARCH -->
    <form action="search.php" method="get" class="search-wrapper">
        <div class="input-group my-4">
            <input type="text" class="form-control" name="key" placeholder="Search books by title, author..." aria-label="Search">
            <button class="input-group-text btn btn-primary" type="submit">
                <img src="img/search.png" width="20">
            </button>
        </div>
    </form>

    <!-- FULLSCREEN MODAL -->
    <div id="coverModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.93);justify-content:center;align-items:center;flex-direction:column;" onclick="closeModal()">
        <span style="position:absolute;top:20px;right:32px;font-size:2.5rem;color:#fff;cursor:pointer;font-weight:bold;">&times;</span>
        <img id="modalImage" src="" style="max-height:78vh;max-width:88vw;border-radius:12px;box-shadow:0 8px 48px rgba(0,0,0,0.9);object-fit:contain;">
        <div id="modalTitle" style="color:#fff;font-size:1.4rem;font-weight:700;margin-top:18px;text-align:center;max-width:80vw;"></div>
        <div id="modalAuthor" style="color:#aaa;font-size:1rem;margin-top:6px;text-align:center;"></div>
    </div>

    <!-- BOOKS + SIDEBAR -->
    <div class="row pt-3">
        <?php if ($books == 0) { ?>
            <div class="alert alert-warning text-center p-5 w-100" role="alert">
                <img src="img/empty.png" width="100"><br>
                There is no book in the database
            </div>
        <?php } else { ?>

        <!-- BOOKS: 9 columns -->
        <div class="col-md-9">
            <div class="row row-cols-1 row-cols-md-4 g-4">
                <?php foreach ($books as $index => $book) {
                    $authorName = '';
                    foreach ($authors as $author) { if ($author['id'] == $book['author_id']) { $authorName = $author['name']; break; } }
                    $categoryName = '';
                    foreach ($categories as $category) { if ($category['id'] == $book['category_id']) { $categoryName = $category['name']; break; } }
                    $isNew = $index < 2;
                    $stars = rand(3, 5);
                    $reviews = rand(12, 180);
                ?>
                <div class="col book-col">
                    <div class="card book-card h-100 shadow-sm">
                        <div class="img-wrapper"
                             onclick="openModal('uploads/cover/<?= $book['cover'] ?>', '<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>', 'By: <?= htmlspecialchars($authorName, ENT_QUOTES) ?>')">
                            <?php if ($isNew) { ?><div class="book-badge">New</div><?php } ?>
                            <button class="wishlist-btn" data-book-id="<?= $book['id'] ?>" onclick="event.stopPropagation();toggleWishlist(this)" title="Add to Wishlist">♡</button>
                            <img src="uploads/cover/<?= $book['cover'] ?>" class="cover-thumb">
                            <img src="uploads/cover/<?= $book['cover'] ?>" class="cover-full">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title" title="<?= htmlspecialchars($book['title']) ?>"><?= $book['title'] ?></h5>
                            <div class="star-rating">
                                <?= str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) ?>
                                <span>(<?= $reviews ?>)</span>
                            </div>
                            <p class="card-text">
                                <b>By:</b> <?= $authorName ?><br>
                                <b>Category:</b> <?= $categoryName ?>
                            </p>
                            <div class="mt-auto d-flex flex-column gap-2">
                                <?php if (isset($_SESSION['user_id'])) { ?>
                                    <a href="book-detail.php?id=<?= $book['id'] ?>" class="btn btn-success w-100">Open</a>
                                    <a href="track.php?book_id=<?= $book['id'] ?>&action=download" class="btn btn-primary w-100" download="<?= $book['title'] ?>">Download</a>
                                <?php } else { ?>
                                    <a href="login.php" class="btn btn-warning w-100">Login to Access</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4 mb-2">
                <ul class="pagination justify-content-center flex-wrap gap-1" style="--bs-pagination-active-bg:#e94560;--bs-pagination-active-border-color:#e94560;">
                    <!-- Previous -->
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>" style="border-radius:8px;">‹ Prev</a>
                    </li>

                    <?php
                    // Show smart page range
                    $start = max(1, $currentPage - 2);
                    $end   = min($totalPages, $currentPage + 2);
                    if ($start > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=1" style="border-radius:8px;">1</a></li>
                        <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>" style="border-radius:8px;"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>" style="border-radius:8px;"><?= $totalPages ?></a></li>
                    <?php endif; ?>

                    <!-- Next -->
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>" style="border-radius:8px;">Next ›</a>
                    </li>
                </ul>
                <p class="text-center text-muted" style="font-size:.82rem;">
                    Showing <?= $offset + 1 ?>–<?= min($offset + $booksPerPage, $allBooksCount) ?> of <?= $allBooksCount ?> books
                </p>
            </nav>
            <?php endif; ?>

            <!-- VIEW ALL BUTTON -->
        </div>

        <!-- SIDEBAR: 3 columns -->
        <div class="col-md-3">
            <div class="list-group mb-4">
                <?php if ($categories != 0) { ?>
                <a href="#" class="list-group-item list-group-item-action active">📂 Category</a>
                <?php foreach ($categories as $category) { ?>
                    <a href="category.php?id=<?= $category['id'] ?>" class="list-group-item list-group-item-action"><?= $category['name'] ?></a>
                <?php } } ?>
            </div>
            <div class="list-group">
                <?php if ($authors != 0) { ?>
                <a href="#" class="list-group-item list-group-item-action active">✍️ Author</a>
                <?php foreach ($authors as $author) { ?>
                    <a href="author.php?id=<?= $author['id'] ?>" class="list-group-item list-group-item-action"><?= $author['name'] ?></a>
                <?php } } ?>
            </div>
        </div>

        <?php } ?>
    </div>

    <script>
        function openModal(src, title, author) {
            document.getElementById('modalImage').src = src;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalAuthor').innerText = author;
            document.getElementById('coverModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('coverModal').classList.remove('show');
            document.body.style.overflow = '';
        }

        // FIX: Send book_id to ajax-wishlist.php and save to database
        function toggleWishlist(btn) {
            const bookId = btn.getAttribute('data-book-id');
            if (!bookId) return;

            fetch('ajax-wishlist.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'book_id=' + bookId
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'added') {
                    btn.classList.add('active');
                    btn.innerText = '♥';
                } else if (data.status === 'removed') {
                    btn.classList.remove('active');
                    btn.innerText = '♡';
                } else if (data.status === 'error' && data.message === 'Not logged in') {
                    window.location.href = 'login.php';
                }
            })
            .catch(err => console.error('Wishlist error:', err));
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>

<?php include "footer.php"; ?>