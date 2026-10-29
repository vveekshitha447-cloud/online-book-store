<?php
session_start();

include "db_conn.php";

# Fetch this user's requests if logged in
$my_requests = [];
if (isset($_SESSION['user_id'])) {
    $sql  = "SELECT * FROM book_requests WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $my_requests = $stmt->fetchAll();
}

# Fetch unread notifications for this user
$notifications = [];
if (isset($_SESSION['user_id'])) {
    $notif_sql  = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->execute([$_SESSION['user_id']]);
    $notifications = $notif_stmt->fetchAll();

    # Mark them as read immediately after fetching
    if (count($notifications) > 0) {
        $mark_sql  = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $mark_stmt = $conn->prepare($mark_sql);
        $mark_stmt->execute([$_SESSION['user_id']]);
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Book</title>

    <!-- Flash prevention: apply saved theme before paint -->
    <script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ── Light mode variables ──────────────────────── */
        :root {
            --cream:          #faf7f2;
            --cream-dark:     #f3ede3;
            --gold:           #c9a84c;
            --gold-light:     #e2c97e;
            --text-dark:      #2b2118;
            --text-mid:       #6b5b4e;
            --text-light:     #a89080;
            --border:         #e4d9cc;
            --input-bg:       #f7f2eb;
            --shadow:         rgba(43,33,24,.08);
            --card-bg:        #ffffff;
            --notif-bg:       #ffffff;
            --section-bg:     #ffffff;
            --toggle-bg:      #e2e8f0;
            --toggle-color:   #2b2118;
            --table-head-color: #6b5b4e;
            --uploaded-yes:   #2e7d52;
        }

        /* ── Dark mode variables ───────────────────────── */
        [data-theme="dark"] {
            --cream:          #0f1117;
            --cream-dark:     #1a1d27;
            --gold:           #e2c97e;
            --gold-light:     #c9a84c;
            --text-dark:      #e8eaf0;
            --text-mid:       #9aa3b2;
            --text-light:     #6b7280;
            --border:         #2d3148;
            --input-bg:       #1e2130;
            --shadow:         rgba(0,0,0,.4);
            --card-bg:        #1e2130;
            --notif-bg:       #1e2130;
            --section-bg:     #1e2130;
            --toggle-bg:      #3b4263;
            --toggle-color:   #e8eaf0;
            --table-head-color: #9aa3b2;
            --uploaded-yes:   #4ade80;
        }

        /* ── Smooth transitions ────────────────────────── */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0; padding: 0;
            transition: background-color 0.3s ease, color 0.3s ease,
                        border-color 0.3s ease, box-shadow 0.3s ease;
        }

        body {
            background: var(--cream);
            font-family: 'Jost', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            padding: 48px 16px 80px;
        }

        /* ── Top bar (back link + toggle) ──────────────── */
        .top-bar {
            max-width: 720px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .top-bar a {
            color: var(--text-light);
            font-size: .88rem;
            text-decoration: none;
            transition: color .2s;
        }
        .top-bar a:hover { color: var(--text-dark); }

        /* ── Theme toggle pill ─────────────────────────── */
        .theme-toggle-btn {
            display: flex; align-items: center; gap: 8px;
            background: var(--toggle-bg);
            border: none; border-radius: 50px;
            padding: 6px 14px 6px 10px;
            cursor: pointer;
            font-size: 0.82rem; font-weight: 600;
            font-family: 'Jost', sans-serif;
            color: var(--toggle-color);
            white-space: nowrap;
        }
        .theme-toggle-btn:hover { transform: scale(1.04); }

        /* ── Alerts ────────────────────────────────────── */
        .alert {
            max-width: 720px;
            margin: 0 auto 24px;
            border-radius: 10px;
            font-family: 'Jost', sans-serif;
            font-weight: 400;
        }
        [data-theme="dark"] .alert-success {
            background-color: #0f2e1a; color: #4ade80; border-color: #1a5c35;
        }
        [data-theme="dark"] .alert-danger {
            background-color: #2e0f0f; color: #f87171; border-color: #5c1a1a;
        }

        /* ── Notification Banner ────────────────────────── */
        .notif-banner {
            max-width: 720px;
            margin: 0 auto 16px;
            background: var(--notif-bg);
            border: 1.5px solid var(--gold);
            border-left: 5px solid var(--gold);
            border-radius: 10px;
            padding: 14px 18px;
            font-size: .93rem;
            color: var(--text-dark);
            display: flex;
            align-items: flex-start;
            gap: 10px;
            box-shadow: 0 2px 12px var(--shadow);
        }
        .notif-banner .notif-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: 1px; }
        .notif-banner .notif-text { flex: 1; line-height: 1.5; }
        .notif-banner .notif-close {
            background: none; border: none; cursor: pointer;
            color: var(--text-light); font-size: 1.1rem; flex-shrink: 0;
            padding: 0; line-height: 1;
        }
        .notif-banner .notif-close:hover { color: var(--text-dark); }

        /* ── Request Card ───────────────────────────────── */
        .request-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            max-width: 720px;
            margin: 0 auto;
            padding: 52px 56px 48px;
            box-shadow: 0 8px 40px var(--shadow);
        }

        /* ── Header diamonds ────────────────────────────── */
        .header-diamonds {
            display: flex; justify-content: center;
            gap: 14px; margin-bottom: 20px;
        }
        .header-diamonds span {
            display: inline-block; width: 10px; height: 10px;
            background: var(--gold); transform: rotate(45deg); opacity: .85;
        }
        .header-diamonds span:nth-child(2) { opacity: .5; }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem; font-weight: 700;
            text-align: center; color: var(--text-dark);
            letter-spacing: -.5px; margin-bottom: 6px;
        }
        .card-subtitle {
            text-align: center; color: var(--text-mid);
            font-size: .95rem; font-weight: 300; margin-bottom: 36px;
        }

        /* ── Required notice ────────────────────────────── */
        .required-note { font-size: .82rem; color: var(--text-light); margin-bottom: 28px; }
        .required-note strong { color: var(--gold); font-weight: 600; }

        /* ── Two-column grid ────────────────────────────── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 28px; }
        .form-grid .full { grid-column: 1 / -1; }

        /* ── Field label ────────────────────────────────── */
        .field-label {
            display: block; font-size: .72rem; font-weight: 600;
            letter-spacing: .12em; text-transform: uppercase;
            color: var(--text-dark); margin-bottom: 8px; margin-top: 20px;
        }
        .field-label em {
            font-style: italic; font-weight: 400;
            color: var(--text-light); text-transform: none;
            letter-spacing: 0; font-size: .82rem;
        }
        .field-label .req { color: var(--gold); margin-left: 3px; }

        /* ── Inputs ─────────────────────────────────────── */
        input[type="text"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            background: var(--input-bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 13px 16px;
            font-family: 'Jost', sans-serif;
            font-size: .95rem;
            color: var(--text-dark);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background-color .3s, color .3s;
            -webkit-appearance: none;
        }
        input::placeholder,
        textarea::placeholder { color: var(--text-light); font-weight: 300; }

        input:focus, select:focus, textarea:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,.12);
            background: var(--card-bg);
        }

        /* Custom select arrow */
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='8' viewBox='0 0 14 8'%3E%3Cpath d='M1 1l6 6 6-6' stroke='%23c9a84c' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 44px;
            cursor: pointer;
        }
        select option {
            color: var(--text-dark);
            background: var(--card-bg);
        }
        textarea { resize: vertical; min-height: 100px; }

        /* ── File Upload Field ──────────────────────────── */
        .file-upload-wrapper {
            width: 100%;
            background: var(--input-bg);
            border: 1.5px dashed var(--border);
            border-radius: 10px;
            padding: 20px 16px;
            text-align: center;
            transition: border-color .2s, background .2s;
            cursor: pointer;
            position: relative;
        }
        .file-upload-wrapper:hover,
        .file-upload-wrapper.dragover {
            border-color: var(--gold);
            background: var(--card-bg);
        }
        .file-upload-wrapper input[type="file"] {
            position: absolute; inset: 0; opacity: 0;
            cursor: pointer; width: 100%; height: 100%;
            padding: 0; border: none; background: transparent;
        }
        .file-upload-wrapper input[type="file"]:focus { box-shadow: none; }
        .file-upload-icon { font-size: 1.8rem; margin-bottom: 6px; }
        .file-upload-text { font-size: .88rem; color: var(--text-mid); font-weight: 400; }
        .file-upload-text strong { color: var(--gold); }
        .file-name-display {
            margin-top: 10px; font-size: .82rem;
            color: var(--uploaded-yes); font-weight: 500; display: none;
        }

        /* ── Buttons ─────────────────────────────────────── */
        .btn-submit {
            display: block; width: 100%; margin-top: 32px; padding: 15px;
            background: var(--text-dark); color: var(--cream);
            border: none; border-radius: 10px;
            font-family: 'Jost', sans-serif; font-size: .9rem;
            font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
            cursor: pointer; transition: background .2s, transform .15s, color .3s;
        }
        .btn-submit:hover { opacity: .85; transform: translateY(-1px); }

        .btn-back {
            display: block; text-align: center; margin-top: 16px;
            color: var(--text-light); font-size: .85rem;
            text-decoration: none; transition: color .2s;
        }
        .btn-back:hover { color: var(--text-dark); }

        /* ── My Requests Section ────────────────────────── */
        .requests-section {
            max-width: 960px;
            margin: 48px auto 0;
            background: var(--section-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 40px;
            box-shadow: 0 4px 24px var(--shadow);
        }
        .requests-section h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; margin-bottom: 24px;
            color: var(--text-dark);
        }

        /* ── Table ──────────────────────────────────────── */
        .table { color: var(--text-dark) !important; }
        .table thead th {
            font-size: .72rem; font-weight: 600;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--table-head-color);
            border-bottom: 2px solid var(--border);
            padding-bottom: 12px;
        }
        .table td {
            font-size: .9rem; color: var(--text-dark);
            vertical-align: middle; border-color: var(--border);
            padding: 14px 12px;
        }
        [data-theme="dark"] .table-hover > tbody > tr:hover > * {
            background-color: rgba(255,255,255,.04) !important;
            color: var(--text-dark);
        }

        .badge {
            font-family: 'Jost', sans-serif; font-weight: 500;
            font-size: .78rem; padding: 5px 12px; border-radius: 20px;
        }

        /* ── Responsive ─────────────────────────────────── */
        @media (max-width: 600px) {
            .request-card { padding: 36px 24px; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .full { grid-column: 1; }
        }
    </style>
</head>
<body>

<div class="container-fluid px-0">

    <!-- Top bar: back link + theme toggle -->
    <div class="top-bar">
        <a href="index.php">← Back to Store</a>
        <button class="theme-toggle-btn" id="themeToggle" onclick="toggleTheme()" title="Toggle dark/light mode">
            <span id="toggleIcon">🌙</span>
            <span id="themeLabel">Dark Mode</span>
        </button>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- NOTIFICATIONS -->
    <?php foreach ($notifications as $notif): ?>
    <div class="notif-banner" id="notif-<?= $notif['id'] ?>">
        <span class="notif-icon">🔔</span>
        <span class="notif-text"><?= htmlspecialchars($notif['message']) ?></span>
        <button class="notif-close"
                onclick="document.getElementById('notif-<?= $notif['id'] ?>').style.display='none'">✕</button>
    </div>
    <?php endforeach; ?>

    <!-- REQUEST FORM CARD -->
    <div class="request-card">

        <div class="header-diamonds">
            <span></span><span></span><span></span>
        </div>

        <h1 class="card-title">Request a Book</h1>
        <p class="card-subtitle">Fill in the details and upload your book PDF for review</p>

        <p class="required-note"><strong>**</strong> Required fields</p>

        <form method="POST" action="php/func-book-request.php" enctype="multipart/form-data">

            <div class="form-grid">

                <!-- Name -->
                <div>
                    <label class="field-label">Your Name <span class="req">**</span></label>
                    <input type="text" name="user_name" placeholder="Jane Doe"
                           value="<?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="field-label">Your Email <span class="req">**</span></label>
                    <input type="email" name="user_email" placeholder="you@example.com"
                           value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>" required>
                </div>

                <!-- Book Title -->
                <div>
                    <label class="field-label">Book Title <span class="req">**</span></label>
                    <input type="text" name="title" placeholder="Title of the book" required>
                </div>

                <!-- Author -->
                <div>
                    <label class="field-label">Author <span class="req">**</span></label>
                    <input type="text" name="author" placeholder="Author name" required>
                </div>

                <!-- ISBN -->
                <div>
                    <label class="field-label">ISBN <em>(optional)</em></label>
                    <input type="text" name="isbn" placeholder="978-3-16-148410-0">
                </div>

                <!-- Genre -->
                <div>
                    <label class="field-label">Genre <em>(optional)</em></label>
                    <input type="text" name="genre" placeholder="e.g. Fiction, Science">
                </div>

                <!-- Condition -->
                <div class="full">
                    <label class="field-label">Book Condition <span class="req">**</span></label>
                    <select name="condition" required>
                        <option value="">-- Select Condition --</option>
                        <option value="new">New</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="full">
                    <label class="field-label">Description <em>(optional)</em></label>
                    <textarea name="description" placeholder="Any additional notes about the book..."></textarea>
                </div>

                <!-- File Upload -->
                <div class="full">
                    <label class="field-label">Upload Book PDF <span class="req">**</span></label>
                    <div class="file-upload-wrapper" id="fileWrapper">
                        <input type="file" name="book_file" accept=".pdf" required id="bookFileInput">
                        <div class="file-upload-icon">📄</div>
                        <div class="file-upload-text">
                            <strong>Click to upload</strong> or drag &amp; drop your PDF here
                        </div>
                        <div class="file-name-display" id="fileNameDisplay"></div>
                    </div>
                </div>

            </div><!-- /.form-grid -->

            <button type="submit" class="btn-submit">Submit &amp; Upload Book</button>

        </form>
    </div><!-- /.request-card -->


    <!-- MY REQUESTS TABLE -->
    <?php if (isset($_SESSION['user_id']) && count($my_requests) > 0): ?>
    <div class="requests-section">
        <h4>📋 My Requests</h4>
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Condition</th>
                    <th>Uploaded</th>
                    <th>Status</th>
                    <th>Admin Note</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($my_requests as $i => $req): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($req['title']) ?></td>
                    <td><?= htmlspecialchars($req['author']) ?></td>
                    <td><?= ucfirst($req['condition']) ?></td>
                    <td>
                        <?php if (!empty($req['uploaded_file'])): ?>
                            <span style="color:var(--uploaded-yes);font-weight:500;">✅ Yes</span>
                        <?php else: ?>
                            <span style="color:var(--text-light);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($req['status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php elseif ($req['status'] === 'approved'): ?>
                            <span class="badge bg-success">Approved</span>
                        <?php elseif ($req['status'] === 'added_to_store'): ?>
                            <span class="badge" style="background:var(--gold);color:#fff;">In Store</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Rejected</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $req['admin_note'] ? htmlspecialchars($req['admin_note']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div><!-- /.container-fluid -->

<script>
    // ── Theme toggle ──────────────────────────────────────
    function applyTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        var icon  = document.getElementById('toggleIcon');
        var label = document.getElementById('themeLabel');
        if (t === 'dark') {
            icon.textContent  = '☀️';
            label.textContent = 'Light Mode';
        } else {
            icon.textContent  = '🌙';
            label.textContent = 'Dark Mode';
        }
    }
    function toggleTheme() {
        applyTheme(
            document.documentElement.getAttribute('data-theme') === 'dark'
                ? 'light' : 'dark'
        );
    }
    // Sync icon/label on load (theme already set by inline script above)
    applyTheme(localStorage.getItem('theme') || 'light');

    // ── File upload display ───────────────────────────────
    const input   = document.getElementById('bookFileInput');
    const display = document.getElementById('fileNameDisplay');
    const wrapper = document.getElementById('fileWrapper');

    input.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            display.textContent = '📎 ' + this.files[0].name;
            display.style.display = 'block';
        }
    });

    wrapper.addEventListener('dragover',  () => wrapper.classList.add('dragover'));
    wrapper.addEventListener('dragleave', () => wrapper.classList.remove('dragover'));
    wrapper.addEventListener('drop',      () => wrapper.classList.remove('dragover'));
</script>

</body>
</html>