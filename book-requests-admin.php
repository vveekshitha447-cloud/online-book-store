<?php
session_start();

# Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

# Database Connection
include "db_conn.php";

# Fetch all book requests (newest first)
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($filter === 'all') {
    $sql = "SELECT * FROM book_requests ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} else {
    $sql = "SELECT * FROM book_requests WHERE status = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$filter]);
}

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

# Get all authors and categories for the Add to Store form
$authors    = $conn->query("SELECT * FROM authors ORDER BY name ASC")->fetchAll();
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Requests — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Apply saved theme BEFORE paint to avoid flash -->
    <script>
        (function () {
            var t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    <style>
        /* ── LIGHT MODE (default) ── */
        :root {
            --cream:      #fdf8f2;
            --cream-dark: #f5ede0;
            --gold:       #c9a84c;
            --gold-light: #e8c97a;
            --gold-dark:  #a07830;
            --text:       #2c2416;
            --text-muted: #8a7560;
            --border:     #e4d5bc;
            --white:      #ffffff;
            --green:      #4a7c59;
            --red:        #9e3a3a;
            --blue:       #2d5986;
        }

        /* ── DARK MODE ── */
        [data-theme="dark"] {
            --cream:      #1a1812;
            --cream-dark: #22201a;
            --gold:       #c9a84c;
            --gold-light: #e8c97a;
            --gold-dark:  #d4a84c;
            --text:       #e8e0d0;
            --text-muted: #9a8a70;
            --border:     #3a3020;
            --white:      #1e1c16;
            --green:      #5a9a6a;
            --red:        #c05050;
            --blue:       #4a7ab5;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body {
            background-color: var(--cream);
            font-family: 'Lato', sans-serif;
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── NAVBAR ── */
        .admin-nav {
            background: var(--white);
            border-bottom: 2px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }
        .admin-nav .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gold-dark);
            text-decoration: none;
            letter-spacing: .02em;
        }
        .admin-nav .nav-links { display: flex; align-items: center; gap: 1.5rem; list-style: none; margin: 0; padding: 0; }
        .admin-nav .nav-links a {
            font-size: .85rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--text-muted);
            text-decoration: none;
            padding: .25rem 0;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
        }
        .admin-nav .nav-links a:hover,
        .admin-nav .nav-links a.active {
            color: var(--gold-dark);
            border-bottom-color: var(--gold);
        }

        /* ── THEME TOGGLE BUTTON ── */
        .theme-toggle-btn {
            background: none;
            border: 1.5px solid var(--border);
            border-radius: 50px;
            padding: 5px 14px;
            cursor: pointer;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .06em;
            color: var(--text-muted);
            transition: all .2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .theme-toggle-btn:hover {
            border-color: var(--gold);
            color: var(--gold-dark);
        }

        /* ── PAGE HEADER ── */
        .page-header { padding: 2.5rem 2rem 1.5rem; }
        .page-header .diamonds { color: var(--gold); font-size: 1rem; letter-spacing: .4rem; margin-bottom: .3rem; }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--text);
        }

        /* ── ALERTS ── */
        .alert-custom {
            margin: 0 2rem 1.25rem;
            padding: .85rem 1.25rem;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 600;
            border-left: 4px solid;
        }
        .alert-custom.success { background: #eef7f1; border-color: var(--green); color: var(--green); }
        .alert-custom.danger  { background: #fdf0f0; border-color: var(--red);   color: var(--red);   }

        /* ── FILTER TABS ── */
        .filter-bar { padding: 0 2rem 1.5rem; display: flex; gap: .6rem; flex-wrap: wrap; }
        .filter-btn {
            font-family: 'Lato', sans-serif;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            padding: .45rem 1.1rem;
            border-radius: 50px;
            border: 2px solid var(--border);
            color: var(--text-muted);
            background: var(--white);
            transition: all .2s;
        }
        .filter-btn:hover { border-color: var(--gold); color: var(--gold-dark); }
        .filter-btn.active-all      { background: var(--text);  border-color: var(--text);  color: var(--white); }
        .filter-btn.active-pending  { background: #f5a623;       border-color: #f5a623;       color: var(--white); }
        .filter-btn.active-approved { background: var(--green);  border-color: var(--green);  color: var(--white); }
        .filter-btn.active-rejected { background: var(--red);    border-color: var(--red);    color: var(--white); }

        /* ── TABLE ── */
        .table-wrap { padding: 0 2rem 3rem; overflow-x: auto; }

        .req-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
        }
        .req-table thead tr { background: var(--cream-dark); }
        .req-table thead th {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: .9rem 1rem;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }
        .req-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }
        .req-table tbody tr:last-child { border-bottom: none; }
        .req-table tbody tr:hover { background: var(--cream); }
        .req-table td {
            padding: .9rem 1rem;
            vertical-align: middle;
            font-size: .88rem;
        }
        .req-table td strong { font-weight: 700; }
        .req-table td .sub { font-size: .78rem; color: var(--text-muted); margin-top: 2px; }

        /* ── BADGES ── */
        .badge-status {
            display: inline-block;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            padding: .28rem .7rem;
            border-radius: 50px;
        }
        .badge-status.pending       { background: #fff3dc; color: #b07800; border: 1px solid #f5c84a; }
        .badge-status.approved      { background: #e9f7ee; color: var(--green); border: 1px solid #a3d4b0; }
        .badge-status.rejected      { background: #fdf0f0; color: var(--red);   border: 1px solid #e8aaaa; }
        .badge-status.added_to_store{ background: #e8f0fb; color: var(--blue);  border: 1px solid #a3bce8; }

        [data-theme="dark"] .badge-status.pending       { background: #2a1f00; color: #f5c84a; border-color: #6a4f00; }
        [data-theme="dark"] .badge-status.approved      { background: #0a2015; color: #6ac48a; border-color: #2a6040; }
        [data-theme="dark"] .badge-status.rejected      { background: #2a0a0a; color: #e08080; border-color: #6a2020; }
        [data-theme="dark"] .badge-status.added_to_store{ background: #0a1525; color: #6a9ad4; border-color: #1a3a6a; }

        /* ── ACTION BUTTONS ── */
        .btn-act {
            display: inline-block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: .32rem .75rem;
            border-radius: 6px;
            border: 1.5px solid;
            cursor: pointer;
            text-decoration: none;
            margin: 2px 1px;
            transition: all .18s;
            white-space: nowrap;
            background: transparent;
        }
        .btn-act.view    { border-color: var(--gold);  color: var(--gold-dark); }
        .btn-act.view:hover { background: var(--gold); color: var(--white); }
        .btn-act.approve { border-color: var(--green); color: var(--green); }
        .btn-act.approve:hover { background: var(--green); color: var(--white); }
        .btn-act.reject  { border-color: var(--red);   color: var(--red); }
        .btn-act.reject:hover { background: var(--red); color: var(--white); }
        .btn-act.store   { border-color: var(--blue);  color: var(--blue); }
        .btn-act.store:hover { background: var(--blue); color: var(--white); }

        /* ── EMPTY STATE ── */
        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--text-muted); }
        .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; }
        .empty-state p { font-family: 'Playfair Display', serif; font-size: 1.1rem; }

        /* ══════════════════════════════════════════
           SIDE PANEL
        ══════════════════════════════════════════ */
        .side-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 300;
            animation: fadeIn .2s ease;
        }
        .side-overlay.open { display: block; }

        @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }
        @keyframes slideOut{ from { transform: translateX(0); } to { transform: translateX(100%); } }

        .side-panel {
            position: fixed;
            top: 0; right: 0;
            width: 55vw;
            max-width: 860px;
            min-width: 360px;
            height: 100vh;
            background: var(--white);
            z-index: 400;
            display: flex;
            flex-direction: column;
            box-shadow: -8px 0 40px rgba(0,0,0,.15);
            transform: translateX(100%);
            transition: transform .3s cubic-bezier(.4,0,.2,1);
        }
        .side-panel.open { transform: translateX(0); }

        .side-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.5rem;
            background: var(--cream-dark);
            border-bottom: 2px solid var(--border);
            flex-shrink: 0;
        }
        .side-panel-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: var(--text);
        }
        .side-panel-close {
            background: none; border: none; cursor: pointer;
            font-size: 1.4rem; color: var(--text-muted);
            line-height: 1; padding: 0;
            transition: color .2s;
        }
        .side-panel-close:hover { color: var(--text); }

        .side-panel-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }

        /* PDF viewer takes most of the space */
        .pdf-viewer-wrap {
            flex: 1;
            background: #525659;
            overflow: hidden;
            position: relative;
        }
        .pdf-viewer-wrap iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        .pdf-no-file {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #aaa;
            font-size: .95rem;
            gap: .5rem;
        }
        .pdf-no-file span { font-size: 3rem; }

        /* Book info strip below PDF */
        .side-info-strip {
            background: var(--cream);
            border-top: 1px solid var(--border);
            padding: 1rem 1.5rem;
            flex-shrink: 0;
            display: flex;
            flex-wrap: wrap;
            gap: .5rem 1.5rem;
            font-size: .83rem;
        }
        .side-info-strip .info-item { display: flex; gap: .35rem; align-items: baseline; }
        .side-info-strip .info-label {
            font-size: .68rem; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: var(--text-muted);
        }

        /* Action footer */
        .side-panel-footer {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1rem 1.5rem;
            background: var(--white);
            border-top: 2px solid var(--border);
            flex-shrink: 0;
            flex-wrap: wrap;
        }
        .btn-side {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            padding: .55rem 1.3rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: opacity .18s, transform .12s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-side:hover { opacity: .85; transform: translateY(-1px); }
        .btn-side.approve { background: var(--green);    color: var(--white); }
        .btn-side.reject  { background: var(--red);      color: var(--white); }
        .btn-side.store   { background: var(--blue);     color: var(--white); }
        .btn-side.cancel  { background: var(--cream-dark); color: var(--text-muted); }
        .btn-side.pdf-link{ background: var(--gold-dark); color: var(--white); }

        /* reject note inline */
        .reject-note-wrap {
            display: none;
            width: 100%;
            margin-top: .5rem;
        }
        .reject-note-wrap textarea {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: .6rem .9rem;
            font-family: 'Lato', sans-serif;
            font-size: .88rem;
            resize: vertical;
            min-height: 70px;
            outline: none;
            background: var(--cream);
            color: var(--text);
        }
        .reject-note-wrap textarea:focus { border-color: var(--gold); }
        .reject-confirm-btns { display: flex; gap: .5rem; margin-top: .5rem; }

        /* ── MODALS ── */
        .modal-content {
            border: none; border-radius: 14px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.18);
            background: var(--white);
        }
        .modal-header {
            background: var(--cream-dark);
            border-bottom: 2px solid var(--border);
            padding: 1.2rem 1.5rem;
        }
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700; font-size: 1.15rem; color: var(--text);
        }
        .modal-body  { padding: 1.5rem; background: var(--white); }
        .modal-footer { background: var(--cream); border-top: 1px solid var(--border); padding: 1rem 1.5rem; }

        .form-label-custom {
            font-size: .72rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: var(--text-muted);
            margin-bottom: .4rem; display: block;
        }
        .form-control-custom, .form-select-custom {
            width: 100%; padding: .65rem .9rem;
            border: 1.5px solid var(--border); border-radius: 8px;
            background: var(--cream); color: var(--text);
            font-family: 'Lato', sans-serif; font-size: .9rem;
            outline: none; transition: border-color .2s;
        }
        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,.12);
        }
        .form-group-custom { margin-bottom: 1.1rem; }
        .btn-modal {
            font-size: .8rem; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; padding: .55rem 1.3rem;
            border-radius: 8px; border: none; cursor: pointer;
            transition: opacity .18s, transform .12s;
        }
        .btn-modal:hover { opacity: .88; transform: translateY(-1px); }
        .btn-modal.cancel  { background: var(--cream-dark); color: var(--text-muted); }
        .btn-modal.success { background: var(--green);      color: var(--white); }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="admin-nav">
    <a class="brand" href="admin.php">✦ Admin Panel</a>
    <ul class="nav-links">
        <li><a href="index.php">Store</a></li>
        <li><a href="book-requests-admin.php" class="active">Book Requests</a></li>
        <li><a href="logout.php">Logout</a></li>
        <li>
            <button class="theme-toggle-btn" onclick="toggleTheme()" id="themeToggle">
                <span id="toggleIcon">🌙</span>
                <span id="toggleLabel">Dark Mode</span>
            </button>
        </li>
    </ul>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="diamonds">✦ ✦ ✦</div>
    <h1>Book Requests</h1>
</div>

<!-- ALERTS -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert-custom success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert-custom danger">✕ <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- FILTER TABS -->
<div class="filter-bar">
    <a href="book-requests-admin.php?status=all"
       class="filter-btn <?= $filter === 'all'      ? 'active-all'      : '' ?>">All</a>
    <a href="book-requests-admin.php?status=pending"
       class="filter-btn <?= $filter === 'pending'  ? 'active-pending'  : '' ?>">Pending</a>
    <a href="book-requests-admin.php?status=approved"
       class="filter-btn <?= $filter === 'approved' ? 'active-approved' : '' ?>">Approved</a>
    <a href="book-requests-admin.php?status=rejected"
       class="filter-btn <?= $filter === 'rejected' ? 'active-rejected' : '' ?>">Rejected</a>
</div>

<!-- TABLE -->
<div class="table-wrap">
<?php if (count($requests) === 0): ?>
    <div class="empty-state">
        <div class="icon">📚</div>
        <p>No book requests found.</p>
    </div>
<?php else: ?>
    <table class="req-table">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Condition</th>
                <th>Status</th>
                <th>File</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td><?= $req['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($req['user_name']) ?></strong>
                    <div class="sub"><?= htmlspecialchars($req['user_email']) ?></div>
                </td>
                <td>
                    <?= htmlspecialchars($req['title']) ?>
                    <?php if ($req['isbn']): ?>
                        <div class="sub">ISBN: <?= htmlspecialchars($req['isbn']) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($req['author']) ?></td>
                <td><?= ucfirst($req['condition']) ?></td>
                <td>
                    <span class="badge-status <?= $req['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $req['status'])) ?>
                    </span>
                </td>
                <td>
                    <?= !empty($req['uploaded_file'])
                        ? '✅'
                        : '<span style="color:var(--text-muted)">—</span>' ?>
                </td>
                <td><?= date('d M Y', strtotime($req['created_at'])) ?></td>
                <td>
                    <!-- VIEW triggers the side panel -->
                    <button class="btn-act view" onclick="openPanel(<?= htmlspecialchars(json_encode($req)) ?>)">View</button>

                    <?php if ($req['status'] === 'approved' && !empty($req['uploaded_file'])): ?>
                        <button class="btn-act store" data-bs-toggle="modal"
                                data-bs-target="#addToStoreModal<?= $req['id'] ?>">Add to Store</button>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- ADD TO STORE MODAL -->
            <?php if ($req['status'] === 'approved' && !empty($req['uploaded_file'])): ?>
            <div class="modal fade" id="addToStoreModal<?= $req['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">✦ Add to Store</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="php/func-add-to-store.php" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="request_id"    value="<?= $req['id'] ?>">
                                <input type="hidden" name="uploaded_file" value="<?= htmlspecialchars($req['uploaded_file']) ?>">

                                <div class="form-group-custom">
                                    <label class="form-label-custom">Book Title <span style="color:var(--gold)">**</span></label>
                                    <input type="text" class="form-control-custom" name="title"
                                           value="<?= htmlspecialchars($req['title']) ?>" required>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Description</label>
                                    <textarea class="form-control-custom" name="description"
                                              rows="3"><?= htmlspecialchars($req['description'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Author <span style="color:var(--gold)">**</span></label>
                                    <select class="form-select-custom" name="author_id" required>
                                        <option value="">— Select Author —</option>
                                        <?php foreach ($authors as $author): ?>
                                            <option value="<?= $author['id'] ?>"><?= htmlspecialchars($author['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Category <span style="color:var(--gold)">**</span></label>
                                    <select class="form-select-custom" name="category_id" required>
                                        <option value="">— Select Category —</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Cover Image <span style="color:var(--gold)">**</span></label>
                                    <input type="file" class="form-control-custom" name="cover"
                                           accept=".jpg,.jpeg,.png" required>
                                </div>
                            </div>
                            <div class="modal-footer" style="gap:.6rem">
                                <button type="button" class="btn-modal cancel" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn-modal success">Add to Store</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<!-- ══════════════════════════════════════════
     SIDE PANEL
══════════════════════════════════════════ -->
<div class="side-overlay" id="sideOverlay" onclick="closePanel()"></div>

<div class="side-panel" id="sidePanel">
    <div class="side-panel-header">
        <h2 id="panelTitle">Book Details</h2>
        <button class="side-panel-close" onclick="closePanel()">✕</button>
    </div>

    <div class="side-panel-body">
        <!-- PDF viewer -->
        <div class="pdf-viewer-wrap" id="pdfViewerWrap">
            <div class="pdf-no-file" id="pdfNoFile">
                <span>📄</span>
                <p>No file uploaded yet</p>
            </div>
            <iframe id="pdfFrame" src="" style="display:none"></iframe>
        </div>

        <!-- Info strip -->
        <div class="side-info-strip" id="sideInfoStrip"></div>
    </div>

    <!-- Footer actions -->
    <div class="side-panel-footer" id="sidePanelFooter"></div>
</div>

<script>
    // ── THEME ────────────────────────────────────────────────
    function applyTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        var icon  = document.getElementById('toggleIcon');
        var label = document.getElementById('toggleLabel');
        if (t === 'dark') {
            if (icon)  icon.textContent  = '☀️';
            if (label) label.textContent = 'Light Mode';
        } else {
            if (icon)  icon.textContent  = '🌙';
            if (label) label.textContent = 'Dark Mode';
        }
    }
    function toggleTheme() {
        applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    }
    // Sync button label with saved theme on load
    applyTheme(localStorage.getItem('theme') || 'light');

    // ── SIDE PANEL ───────────────────────────────────────────
    let currentReq = null;

    function openPanel(req) {
        currentReq = req;

        document.getElementById('panelTitle').textContent = req.title + ' — ' + req.author;

        const frame  = document.getElementById('pdfFrame');
        const noFile = document.getElementById('pdfNoFile');
        if (req.uploaded_file) {
            frame.src = 'uploads/files/' + req.uploaded_file;
            frame.style.display = 'block';
            noFile.style.display = 'none';
        } else {
            frame.src = '';
            frame.style.display = 'none';
            noFile.style.display = 'flex';
        }

        const strip = document.getElementById('sideInfoStrip');
        strip.innerHTML = `
            <div class="info-item"><span class="info-label">From</span> ${esc(req.user_name)} &lt;${esc(req.user_email)}&gt;</div>
            <div class="info-item"><span class="info-label">Condition</span> ${cap(req.condition)}</div>
            <div class="info-item"><span class="info-label">Genre</span> ${req.genre || 'N/A'}</div>
            <div class="info-item"><span class="info-label">ISBN</span> ${req.isbn || 'N/A'}</div>
            <div class="info-item"><span class="info-label">Status</span> <span class="badge-status ${req.status}">${cap(req.status.replace('_',' '))}</span></div>
            ${req.description ? `<div class="info-item" style="width:100%"><span class="info-label">Note</span> ${esc(req.description)}</div>` : ''}
        `;

        const footer = document.getElementById('sidePanelFooter');
        let html = '';

        if (req.uploaded_file) {
            html += `<a class="btn-side pdf-link" href="uploads/files/${req.uploaded_file}" target="_blank">Open PDF ↗</a>`;
        }

        if (req.status === 'pending') {
            html += `
                <form method="POST" action="php/func-book-request-action.php" style="display:inline">
                    <input type="hidden" name="id" value="${req.id}">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn-side approve">✓ Approve</button>
                </form>
                <button class="btn-side reject" onclick="showRejectForm()">✕ Reject</button>
            `;
        }

        html += `<button class="btn-side cancel" onclick="closePanel()">Close</button>`;

        html += `
            <div class="reject-note-wrap" id="rejectNoteWrap">
                <form method="POST" action="php/func-book-request-action.php">
                    <input type="hidden" name="id" value="${req.id}">
                    <input type="hidden" name="action" value="reject">
                    <textarea name="admin_note" placeholder="Reason for rejection (optional)..."></textarea>
                    <div class="reject-confirm-btns">
                        <button type="submit" class="btn-side reject">Confirm Reject</button>
                        <button type="button" class="btn-side cancel" onclick="hideRejectForm()">Cancel</button>
                    </div>
                </form>
            </div>
        `;

        footer.innerHTML = html;

        document.getElementById('sideOverlay').classList.add('open');
        document.getElementById('sidePanel').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closePanel() {
        document.getElementById('sideOverlay').classList.remove('open');
        document.getElementById('sidePanel').classList.remove('open');
        document.body.style.overflow = '';
        document.getElementById('pdfFrame').src = '';
    }

    function showRejectForm() {
        document.getElementById('rejectNoteWrap').style.display = 'block';
    }
    function hideRejectForm() {
        document.getElementById('rejectNoteWrap').style.display = 'none';
    }

    function esc(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function cap(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Close on Escape key
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });
</script>

</body>
</html>