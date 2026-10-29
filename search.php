<?php 
session_start();

# If search key is not set or empty
if (!isset($_GET['key']) || empty($_GET['key'])) {
    header("Location: index.php");
    exit;
}
$key = $_GET['key'];

# Database Connection File
include "db_conn.php";

# Book helper function
include "php/func-book.php";
$books = search_books($conn, $key);

# author helper function
include "php/func-author.php";
$authors = get_all_author($conn);

# Category helper function
include "php/func-category.php";
$categories = get_all_categories($conn);

$pageTitle = "Search — Online Book Store";
include "header.php";
?>

<style>
    /* ── Search Header ── */
    .search-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 16px;
        padding: 36px 40px;
        margin: 24px 0 28px;
        position: relative;
        overflow: hidden;
    }
    .search-header::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .search-header h2 {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .search-header p {
        color: #a0b4cc;
        margin: 0;
        font-size: 0.95rem;
    }
    .search-header .keyword {
        color: #e2c97e;
        font-weight: 700;
    }
    .result-count-badge {
        display: inline-block;
        background: rgba(255,255,255,0.12);
        color: #fff;
        border-radius: 20px;
        padding: 3px 14px;
        font-size: 0.82rem;
        font-weight: 600;
        margin-top: 10px;
    }

    /* ── Search bar ── */
    .search-wrapper .form-control {
        border-radius: 10px 0 0 10px;
        padding: 12px 18px;
        font-size: 1rem;
    }
    .search-wrapper .btn {
        border-radius: 0 10px 10px 0;
        padding: 0 20px;
    }

    /* ── Book Cards ── */
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
    .img-wrapper {
        position: relative;
        overflow: hidden;
        cursor: pointer;
        height: 280px;
    }
    .cover-thumb {
        width: 100%; height: 100%;
        object-fit: cover; object-position: top;
        display: block;
        transition: opacity 0.3s ease;
    }
    .cover-full {
        position: absolute; top: 0; left: 0;
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

    .book-card .card-body {
        padding: 14px;
        background: #1e1e2e;
        color: #e0e0e0;
    }
    .book-card .card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .book-card .card-text {
        font-size: 0.82rem;
        color: #a0b4cc;
        margin-bottom: 8px;
    }
    .book-desc {
        font-size: 0.8rem;
        color: #6b7280;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .book-card .btn {
        font-size: 0.85rem;
        padding: 7px;
        border-radius: 8px;
        font-weight: 600;
    }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #a0b4cc;
    }
    .empty-state img { margin-bottom: 20px; opacity: 0.7; }
    .empty-state h4 { color: #fff; font-weight: 700; margin-bottom: 8px; }
    .empty-state p  { color: #6b7280; font-size: 0.95rem; }

    /* ── Card fade-in ── */
    @keyframes cardFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .book-col { animation: cardFadeUp 0.4s ease both; }
    .book-col:nth-child(1) { animation-delay: 0.05s; }
    .book-col:nth-child(2) { animation-delay: 0.10s; }
    .book-col:nth-child(3) { animation-delay: 0.15s; }
    .book-col:nth-child(4) { animation-delay: 0.20s; }
    .book-col:nth-child(5) { animation-delay: 0.25s; }
    .book-col:nth-child(6) { animation-delay: 0.30s; }

    /* ── Back to Top ── */
    #backToTop {
        position: fixed;
        bottom: 32px;
        right: 32px;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f3460, #e94560);
        color: #fff;
        border: none;
        font-size: 1.3rem;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        z-index: 999;
        transition: transform 0.2s, opacity 0.3s;
    }
    #backToTop:hover { transform: scale(1.12); }
    #backToTop.visible { display: flex; animation: fadeInBtn 0.3s ease; }
    @keyframes fadeInBtn { from { opacity:0; transform: scale(0.7); } to { opacity:1; transform: scale(1); } }

    /* ── Modal ── */
    #coverModal.show { display: flex !important; animation: fadeIn 0.2s ease; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
</style>

    <!-- SEARCH HEADER -->
    <div class="search-header">
        <h2>🔍 Search Results</h2>
        <p>Showing results for <span class="keyword">"<?= htmlspecialchars($key) ?>"</span></p>
        <?php if ($books != 0): ?>
            <span class="result-count-badge"><?= count($books) ?> book<?= count($books) > 1 ? 's' : '' ?> found</span>
        <?php endif; ?>
    </div>

    <!-- SEARCH AGAIN -->
    <form action="search.php" method="get" class="search-wrapper mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="key"
                   value="<?= htmlspecialchars($key) ?>"
                   placeholder="Search again...">
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

    <!-- RESULTS -->
    <?php if ($books == 0): ?>
        <div class="empty-state">
            <img src="img/empty-search.png" width="110">
            <h4>No results found</h4>
            <p>The keyword <strong style="color:#e2c97e;">"<?= htmlspecialchars($key) ?>"</strong> didn't match any book in the database.</p>
            <a href="index.php" class="btn btn-primary mt-3">← Back to Store</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($books as $book):
                $authorName = '';
                foreach ($authors as $author) { if ($author['id'] == $book['author_id']) { $authorName = $author['name']; break; } }
                $categoryName = '';
                foreach ($categories as $category) { if ($category['id'] == $book['category_id']) { $categoryName = $category['name']; break; } }
            ?>
            <div class="col book-col">
                <div class="card book-card h-100 shadow-sm">
                    <div class="img-wrapper"
                         onclick="openModal('uploads/cover/<?= $book['cover'] ?>', '<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>', 'By: <?= htmlspecialchars($authorName, ENT_QUOTES) ?>')">
                        <img src="uploads/cover/<?= $book['cover'] ?>" class="cover-thumb">
                        <img src="uploads/cover/<?= $book['cover'] ?>" class="cover-full">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title" title="<?= htmlspecialchars($book['title']) ?>"><?= $book['title'] ?></h5>
                        <p class="card-text">
                            <b>By:</b> <?= $authorName ?><br>
                            <b>Category:</b> <?= $categoryName ?>
                        </p>
                        <?php if (!empty($book['description'])): ?>
                            <p class="book-desc"><?= htmlspecialchars($book['description']) ?></p>
                        <?php endif; ?>
                        <div class="mt-auto d-flex flex-column gap-2">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="book-detail.php?id=<?= $book['id'] ?>" class="btn btn-success w-100">Open</a>
                                <a href="track.php?book_id=<?= $book['id'] ?>&action=download" class="btn btn-primary w-100" download="<?= $book['title'] ?>">Download</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-warning w-100">Login to Access</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- BACK TO TOP BUTTON -->
    <button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to Top">↑</button>

    <script>
        // Back to Top visibility
        window.addEventListener('scroll', function () {
            const btn = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        });

        // Cover modal
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
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>

<?php include "footer.php"; ?>