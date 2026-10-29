<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php include "global-extras.php"; ?>
<?php if (!isset($conn)) { include "db_conn.php"; } ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Online Book Store' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&family=Abril+Fatface&family=Yeseva+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        :root {
            --bg-primary: #ffffff; --bg-secondary: #f8f9fa; --bg-card: #ffffff;
            --bg-navbar: #f8f9fa; --text-primary: #212529; --text-secondary: #6c757d;
            --text-muted: #6c757d; --border-color: #dee2e6; --shadow: rgba(0,0,0,0.08);
            --hero-bg: linear-gradient(135deg,#0f2044 0%,#1a3a6b 60%,#0d4a6b 100%);
            --hero-text: #ffffff; --card-hover-shadow: rgba(0,0,0,0.15);
            --navbar-border: rgba(0,0,0,0.08); --toggle-bg: #e2e8f0;
            --input-bg: #ffffff; --input-border: #ced4da;
            --category-active-bg: #0d6efd; --category-active-text: #ffffff;
        }
        [data-theme="dark"] {
            --bg-primary: #0f1117; --bg-secondary: #1a1d27; --bg-card: #1e2130;
            --bg-navbar: #13161f; --text-primary: #e8eaf0; --text-secondary: #9aa3b2;
            --text-muted: #6b7280; --border-color: #2d3148; --shadow: rgba(0,0,0,0.4);
            --hero-bg: linear-gradient(135deg,#060d1f 0%,#0d1f3c 60%,#071a30 100%);
            --hero-text: #e8eaf0; --card-hover-shadow: rgba(0,0,0,0.6);
            --navbar-border: rgba(255,255,255,0.05); --toggle-bg: #3b4263;
            --input-bg: #1e2130; --input-border: #2d3148;
            --category-active-bg: #2563eb; --category-active-text: #ffffff;
        }
        *,*::before,*::after { box-sizing:border-box; transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        body { background-color: var(--bg-primary) !important; color: var(--text-primary) !important; min-height: 100vh; }
        .navbar { background-color: var(--bg-navbar) !important; border-bottom: 1px solid var(--navbar-border); box-shadow: 0 2px 12px var(--shadow); }
        .navbar-brand { color: var(--text-primary) !important; font-weight: 700; }
        .nav-link { color: var(--text-secondary) !important; }
        .nav-link:hover, .nav-link.active { color: var(--text-primary) !important; }
        .theme-toggle-btn {
            display: flex; align-items: center; gap: 8px;
            background: var(--toggle-bg); border: none; border-radius: 50px;
            padding: 6px 14px 6px 10px; cursor: pointer; font-size: 0.82rem;
            font-weight: 600; color: var(--text-primary); transition: all 0.3s ease; white-space: nowrap;
        }
        .theme-toggle-btn:hover { transform: scale(1.04); }

        /* Profile avatar in navbar */
        .nav-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color);
            background: var(--bg-secondary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
            overflow: hidden;
            vertical-align: middle;
            margin-right: 6px;
        }
        .nav-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        .hero-section {
            background: var(--hero-bg); color: var(--hero-text);
            border-radius: 16px; padding: 3.5rem 2rem; text-align: center;
            margin: 1.5rem 0 2.5rem;
        }
        .hero-section h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.75rem; }
        .hero-section p { font-size: 1.1rem; opacity: 0.85; max-width: 520px; margin: 0 auto; line-height: 1.6; }
        .card { background-color: var(--bg-card) !important; border: 1px solid var(--border-color) !important; border-radius: 12px !important; overflow: hidden; }
        .card-body { padding: 0.75rem !important; }
        .card-title { font-size: 0.95rem !important; margin-bottom: 0.3rem !important; }
        .card-text { font-size: 0.82rem !important; margin-bottom: 0.3rem !important; }
        .card-title { color: var(--text-primary) !important; white-space: normal !important; word-wrap: break-word; overflow: visible !important; display: block; }
        .card-text { color: var(--text-secondary) !important; }
        .list-group-item { background-color: var(--bg-card) !important; border-color: var(--border-color) !important; color: var(--text-primary) !important; }
        .list-group-item.active { background-color: var(--category-active-bg) !important; border-color: var(--category-active-bg) !important; color: var(--category-active-text) !important; }
        .form-control { background-color: var(--input-bg) !important; border-color: var(--input-border) !important; color: var(--text-primary) !important; }
        .form-control::placeholder { color: var(--text-muted) !important; }
        .alert-info { background-color: var(--bg-secondary) !important; border-color: var(--border-color) !important; color: var(--text-primary) !important; }
        .page-footer { text-align: center; padding: 2rem 0 1rem; color: var(--text-muted); font-size: 0.85rem; border-top: 1px solid var(--border-color); margin-top: 3rem; }
        .section-divider { border: none; border-top: 1px solid var(--border-color); margin: 2rem 0; }
        .category { width: 200px; margin-left: 1.5rem; flex-shrink: 0; }
        .pdf-list { flex: 1; }
        .about-hero { background: var(--hero-bg); color: var(--hero-text); padding: 80px 0 60px; text-align: center; border-radius: 0 0 30px 30px; margin-bottom: 60px; }
        .about-hero h1 { font-size: 3rem; font-weight: 700; margin-bottom: 1rem; }
        .about-hero p { font-size: 1.1rem; opacity: .85; max-width: 600px; margin: 0 auto; }
        .section-title { font-size: 2rem; font-weight: 700; color: var(--text-primary); margin-bottom: .5rem; }
        .section-sub { color: var(--text-secondary); font-size: .95rem; margin-bottom: 2.5rem; }
        .stat-box { text-align: center; padding: 2rem; background: var(--bg-secondary); border-radius: 16px; border: 1px solid var(--border-color); }
        .stat-box h2 { font-size: 2.8rem; font-weight: 700; color: var(--category-active-bg); margin: 0; }
        .stat-box p { color: var(--text-secondary); font-size: .85rem; text-transform: uppercase; letter-spacing: .07em; margin: .3rem 0 0; }
        .mission-section { background: var(--bg-secondary); border-radius: 20px; padding: 50px 40px; margin: 60px 0; border: 1px solid var(--border-color); }
        .feature-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 2rem 1.5rem; text-align: center; transition: box-shadow 0.3s ease, transform 0.3s ease; }
        .feature-card h5 { font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; }
        .feature-card p { color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6; margin: 0; }
        .feature-icon { font-size: 2.5rem; margin-bottom: 1rem; }

        /* ── Bell Icon ── */
        .notif-bell {
            position: relative; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--bg-secondary); border: 1px solid var(--border-color);
            font-size: 1.1rem; transition: all .2s; text-decoration: none;
        }
        .notif-bell:hover { background: var(--bg-card); transform: scale(1.08); }
        .notif-badge {
            position: absolute; top: -4px; right: -4px;
            background: #ef4444; color: #fff; font-size: .6rem; font-weight: 700;
            min-width: 18px; height: 18px; border-radius: 999px;
            display: none; align-items: center; justify-content: center; padding: 0 4px;
        }
        .notif-dropdown {
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 320px; background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 9999; display: none; overflow: hidden; animation: dropIn .2s ease;
        }
        @keyframes dropIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        .notif-dropdown.open { display: block; }
        .notif-dropdown-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: .9rem 1rem; border-bottom: 1px solid var(--border-color);
            font-weight: 600; font-size: .9rem;
        }
        .notif-dropdown-header a { font-size: .75rem; color: #6366f1; text-decoration: none; }
        .notif-item {
            display: flex; gap: .75rem; align-items: flex-start;
            padding: .8rem 1rem; border-bottom: 1px solid var(--border-color); transition: background .15s;
        }
        .notif-item:hover { background: var(--bg-secondary); }
        .notif-item:last-child { border-bottom: none; }
        .notif-item-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: 2px; }
        .notif-item-text { font-size: .82rem; color: var(--text-primary); line-height: 1.4; }
        .notif-item-time { font-size: .7rem; color: var(--text-muted); margin-top: .2rem; }
        .notif-empty { padding: 2rem; text-align: center; color: var(--text-muted); font-size: .85rem; }

        /* ── Toast ── */
        .toast-container {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            z-index: 99999; display: flex; flex-direction: column; gap: .5rem;
        }
        .toast-notif {
            display: flex; align-items: center; gap: .75rem;
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-left: 4px solid #6366f1; border-radius: 10px;
            padding: .9rem 1.2rem; min-width: 280px; max-width: 360px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2); animation: toastIn .3s ease; cursor: pointer;
        }
        .toast-notif.removing { animation: toastOut .3s ease forwards; }
        @keyframes toastIn  { from { opacity:0; transform:translateX(60px); } to { opacity:1; transform:translateX(0); } }
        @keyframes toastOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(60px); } }
        .toast-notif.success { border-left-color: #4ade80; }
        .toast-notif.error   { border-left-color: #f87171; }
        .toast-notif.warning { border-left-color: #facc15; }
        .toast-notif.info    { border-left-color: #60a5fa; }
        .toast-icon { font-size: 1.2rem; flex-shrink: 0; }
        .toast-msg  { font-size: .85rem; color: var(--text-primary); flex: 1; line-height: 1.4; }
        .toast-close{ font-size: .9rem; color: var(--text-muted); flex-shrink: 0; }

        /* ── Brand Logo ── */
        .brand-logo {
            display: flex !important;
            align-items: center;
            gap: 13px;
            text-decoration: none !important;
            padding: 4px 0;
            position: relative;
        }

        /* Icon Box */
        .brand-icon-wrap {
            position: relative;
            width: 56px;
            height: 56px;
            background: linear-gradient(145deg, #0b1a38 0%, #1a3a6b 50%, #1c5fa8 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow:
                0 8px 24px rgba(11,26,56,0.55),
                0 2px 6px rgba(28,95,168,0.4),
                inset 0 1px 0 rgba(255,255,255,0.15);
            transition: transform 0.28s cubic-bezier(.34,1.56,.64,1), box-shadow 0.28s ease;
        }
        .brand-logo:hover .brand-icon-wrap {
            transform: translateY(-3px) rotate(-3deg) scale(1.07);
            box-shadow:
                0 14px 32px rgba(11,26,56,0.6),
                0 4px 12px rgba(28,95,168,0.5),
                inset 0 1px 0 rgba(255,255,255,0.18);
        }
        .brand-icon-wrap::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 55%;
            border-radius: 16px 16px 40% 40%;
            background: rgba(255,255,255,0.09);
            pointer-events: none;
        }
        .brand-icon-wrap::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 16px;
            border: 1.5px solid rgba(255,255,255,0.2);
            pointer-events: none;
        }
        .brand-icon {
            font-size: 2.1rem;
            line-height: 1;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.55));
            transition: transform 0.28s cubic-bezier(.34,1.56,.64,1);
        }
        .brand-logo:hover .brand-icon {
            transform: scale(1.12) rotate(6deg);
        }

        /* Text Block */
        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
            gap: 4px;
        }
        .brand-main {
            font-family: 'Abril Fatface', 'Yeseva One', serif;
            font-size: 1.55rem;
            font-weight: 400;
            letter-spacing: 0.01em;
            line-height: 1;
            background: linear-gradient(135deg, #0b1a38 20%, #1565c0 70%, #2196f3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: background 0.3s ease;
        }
        .brand-sub {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.58rem;
            font-weight: 600;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #7a8fac;
            -webkit-text-fill-color: #7a8fac;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .brand-sub::before,
        .brand-sub::after {
            content: '';
            display: inline-block;
            width: 18px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #a0b4cc);
        }
        .brand-sub::after {
            background: linear-gradient(90deg, #a0b4cc, transparent);
        }

        /* Dark mode */
        [data-theme="dark"] .brand-icon-wrap {
            background: linear-gradient(145deg, #0d1f3c 0%, #1a3560 50%, #1565c0 100%);
            box-shadow:
                0 8px 28px rgba(21,101,192,0.5),
                0 2px 8px rgba(96,165,250,0.3),
                inset 0 1px 0 rgba(255,255,255,0.12);
        }
        [data-theme="dark"] .brand-logo:hover .brand-icon-wrap {
            box-shadow:
                0 14px 36px rgba(21,101,192,0.6),
                0 4px 14px rgba(96,165,250,0.4),
                inset 0 1px 0 rgba(255,255,255,0.15);
        }
        [data-theme="dark"] .brand-main {
            background: linear-gradient(135deg, #93c5fd 10%, #60a5fa 55%, #e0f0ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        [data-theme="dark"] .brand-sub {
            color: #3a4f6a;
            -webkit-text-fill-color: #3a4f6a;
        }
        [data-theme="dark"] .brand-sub::before {
            background: linear-gradient(90deg, transparent, #2d3f5a);
        }
        [data-theme="dark"] .brand-sub::after {
            background: linear-gradient(90deg, #2d3f5a, transparent);
        }
    </style>

    <script>
        (function () {
            const saved = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>

    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
<div class="container">

    <!-- ========== NAVBAR ========== -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand brand-logo" href="index.php">
                <span class="brand-icon-wrap">
                    <span class="brand-icon">📚</span>
                </span>
                <span class="brand-text">
                    <span class="brand-main">Online Book Store</span>
                    <span class="brand-sub">Your Digital Library</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : '' ?>" href="about.php">About</a>
                    </li>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <?php if (empty($simpleNav)): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : '' ?>" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : '' ?>" href="register.php">Register</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <?php endif; ?>

                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <?php if (empty($simpleNav)): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : '' ?>" href="admin.php">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'wishlist.php') ? 'active' : '' ?>" href="wishlist.php">❤️ Wishlist</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'leaderboard.php') ? 'active' : '' ?>" href="leaderboard.php">🏆 Leaderboard</a>
                        </li>
                        <!-- Profile dropdown for admin -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php
                                    $picPath = '';
                                    if (isset($_SESSION['user_id'])) {
                                        $pStmt = $conn->prepare("SELECT profile_picture, full_name FROM admin WHERE id = ? LIMIT 1");
                                        $pStmt->execute([$_SESSION['user_id']]);
                                        $pUser = $pStmt->fetch();
                                        $picPath = $pUser['profile_picture'] ?? '';
                                        $pName   = $pUser['full_name'] ?? 'U';
                                    }
                                ?>
                                <span class="nav-avatar">
                                    <?php if ($picPath && file_exists("uploads/profile/" . $picPath)): ?>
                                        <img src="uploads/profile/<?= htmlspecialchars($picPath) ?>" alt="avatar">
                                    <?php else: ?>
                                        <?= mb_strtoupper(mb_substr($pName, 0, 1)) ?>
                                    <?php endif; ?>
                                </span>
                                <?= htmlspecialchars($pName) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="profile.php">👤 My Profile</a></li>
                                <li><a class="dropdown-item" href="edit-profile.php">✏️ Edit Profile</a></li>
                                <li><a class="dropdown-item" href="change-password.php">🔒 Change Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">🚪 Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if (empty($simpleNav)): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="request-book.php">Add a Book</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'wishlist.php') ? 'active' : '' ?>" href="wishlist.php">❤️ Wishlist</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'leaderboard.php') ? 'active' : '' ?>" href="leaderboard.php">🏆 Leaderboard</a>
                        </li>
                        <!-- Profile dropdown for regular user -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php
                                    $picPath2 = '';
                                    if (isset($_SESSION['user_id'])) {
                                        $pStmt2 = $conn->prepare("SELECT profile_picture, full_name FROM admin WHERE id = ? LIMIT 1");
                                        $pStmt2->execute([$_SESSION['user_id']]);
                                        $pUser2  = $pStmt2->fetch();
                                        $picPath2 = $pUser2['profile_picture'] ?? '';
                                        $pName2   = $pUser2['full_name'] ?? 'U';
                                    }
                                ?>
                                <span class="nav-avatar">
                                    <?php if ($picPath2 && file_exists("uploads/profile/" . $picPath2)): ?>
                                        <img src="uploads/profile/<?= htmlspecialchars($picPath2) ?>" alt="avatar">
                                    <?php else: ?>
                                        <?= mb_strtoupper(mb_substr($pName2, 0, 1)) ?>
                                    <?php endif; ?>
                                </span>
                                <?= htmlspecialchars($pName2) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown2">
                                <li><a class="dropdown-item" href="profile.php">👤 My Profile</a></li>
                                <li><a class="dropdown-item" href="edit-profile.php">✏️ Edit Profile</a></li>
                                <li><a class="dropdown-item" href="change-password.php">🔒 Change Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">🚪 Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <!-- NOTIFICATION BELL — hidden on simple nav -->
                <?php if (isset($_SESSION['user_id']) && empty($simpleNav)): ?>
                <div style="position:relative; margin-left: 12px;">
                    <a class="notif-bell" onclick="toggleNotifDropdown(event)" title="Notifications">
                        🔔
                        <span class="notif-badge" id="notifBadge">0</span>
                    </a>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-dropdown-header">
                            <span>🔔 Notifications</span>
                            <a href="notifications.php">View All</a>
                        </div>
                        <div id="notifList">
                            <div class="notif-empty">Loading...</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- DARK / LIGHT MODE TOGGLE -->
                <button class="theme-toggle-btn ms-3" id="themeToggle" onclick="toggleTheme()" title="Toggle dark/light mode">
                    <span class="toggle-icon" id="toggleIcon">🌙</span>
                    <span id="toggleLabel">Dark Mode</span>
                </button>
            </div>
        </div>
    </nav>
    <!-- ========== END NAVBAR ========== -->

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
// ── TOAST SYSTEM ──────────────────────────────────────────────
function showToast(message, type = 'info', duration = 4000) {
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: '💬' };
    const toast = document.createElement('div');
    toast.className = `toast-notif ${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type] || '💬'}</span>
        <span class="toast-msg">${message}</span>
        <span class="toast-close">✕</span>
    `;
    toast.onclick = () => removeToast(toast);
    document.getElementById('toastContainer').appendChild(toast);
    setTimeout(() => removeToast(toast), duration);
}
function removeToast(toast) {
    toast.classList.add('removing');
    setTimeout(() => toast.remove(), 300);
}

// ── BELL DROPDOWN ─────────────────────────────────────────────
function toggleNotifDropdown(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    if (!dropdown) return;
    dropdown.classList.toggle('open');
    if (dropdown.classList.contains('open')) loadNotifications();
}
document.addEventListener('click', () => {
    const d = document.getElementById('notifDropdown');
    if (d) d.classList.remove('open');
});
function loadNotifications() {
    fetch('get-notifications.php')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list  = document.getElementById('notifList');
            const icons = { info:'💬', success:'✅', warning:'⚠️', error:'❌' };
            if (badge) {
                if (data.count > 0) { badge.style.display='flex'; badge.textContent = data.count > 9 ? '9+' : data.count; }
                else badge.style.display = 'none';
            }
            if (!list) return;
            if (data.notifications.length === 0) {
                list.innerHTML = '<div class="notif-empty">🔕 No new notifications</div>'; return;
            }
            list.innerHTML = data.notifications.map(n => `
                <div class="notif-item">
                    <span class="notif-item-icon">${icons[n.type]||'💬'}</span>
                    <div>
                        <div class="notif-item-text">${n.message}</div>
                        <div class="notif-item-time">${timeAgo(n.created_at)}</div>
                    </div>
                </div>
            `).join('');
        }).catch(() => {});
}
function timeAgo(dateStr) {
    const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff/60) + ' min ago';
    if (diff < 86400) return Math.floor(diff/3600) + ' hr ago';
    return Math.floor(diff/86400) + ' days ago';
}
function toggleTheme() {
    const html  = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    const next   = isDark ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    document.getElementById('toggleIcon').textContent  = next === 'dark' ? '☀️' : '🌙';
    document.getElementById('toggleLabel').textContent = next === 'dark' ? 'Light Mode' : 'Dark Mode';
}

// Fix button label on page load to match saved theme
(function() {
    const saved = localStorage.getItem('theme') || 'light';
    const icon  = document.getElementById('toggleIcon');
    const label = document.getElementById('toggleLabel');
    if (icon && label) {
        icon.textContent  = saved === 'dark' ? '☀️' : '🌙';
        label.textContent = saved === 'dark' ? 'Light Mode' : 'Dark Mode';
    }
})();
function fetchBadge() {
    fetch('get-notifications.php')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (!badge) return;
            if (data.count > 0) { badge.style.display='flex'; badge.textContent = data.count > 9 ? '9+' : data.count; }
            else badge.style.display = 'none';
        }).catch(() => {});
}
<?php if (isset($_SESSION['user_id'])): ?>
fetchBadge();
setInterval(fetchBadge, 30000);
<?php endif; ?>

// ── Show toast if URL has ?toast= param ───────────────────────
(function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('toast')) showToast(decodeURIComponent(params.get('toast')), params.get('type') || 'info');
})();
</script>