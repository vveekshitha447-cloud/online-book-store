<?php 
session_start();

# If not author ID is set
if (!isset($_GET['id'])) {
	header("Location: index.php");
	exit;
}

# Get author ID from GET request
$id = $_GET['id'];

# Database Connection File
include "db_conn.php";

# Book helper function
include "php/func-book.php";
$books = get_books_by_author($conn, $id);

# author helper function
include "php/func-author.php";
$authors = get_all_author($conn);
$current_author = get_author($conn, $id);

# Category helper function
include "php/func-category.php";
$categories = get_all_categories($conn);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($current_author['name']) ?></title>

	<!-- Flash prevention: read localStorage before paint -->
	<script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&family=Abril+Fatface&display=swap" rel="stylesheet">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>

	<link rel="stylesheet" href="css/style.css">

	<style>
		/* ── CSS Variables ─────────────────────────────── */
		:root {
			--bg-primary:     #ffffff;
			--bg-secondary:   #f8f9fa;
			--bg-card:        #ffffff;
			--bg-navbar:      #f8f9fa;
			--bg-surface:     #ffffff;
			--text-primary:   #212529;
			--text-secondary: #6c757d;
			--text-muted:     #6c757d;
			--border-color:   #dee2e6;
			--shadow:         rgba(0,0,0,0.08);
			--toggle-bg:      #e2e8f0;
			--input-bg:       #ffffff;
			--input-border:   #ced4da;
			--accent:         #0d6efd;
			--sidebar-active-bg: #0d6efd;
			--sidebar-active-color: #ffffff;
			--card-shadow:    0 2px 12px rgba(0,0,0,0.07);
		}
		[data-theme="dark"] {
			--bg-primary:     #0f1117;
			--bg-secondary:   #1a1d27;
			--bg-card:        #1e2130;
			--bg-navbar:      #13161f;
			--bg-surface:     #1e2130;
			--text-primary:   #e8eaf0;
			--text-secondary: #9aa3b2;
			--text-muted:     #6b7280;
			--border-color:   #2d3148;
			--shadow:         rgba(0,0,0,0.4);
			--toggle-bg:      #3b4263;
			--input-bg:       #1e2130;
			--input-border:   #2d3148;
			--accent:         #3b82f6;
			--sidebar-active-bg: #2563eb;
			--sidebar-active-color: #ffffff;
			--card-shadow:    0 2px 12px rgba(0,0,0,0.35);
		}

		/* ── Brand Logo ────────────────────────────────── */
		.brand-logo {
			display: flex !important; align-items: center;
			gap: 13px; text-decoration: none !important; padding: 4px 0;
		}
		.brand-icon-wrap {
			width: 56px; height: 56px;
			background: linear-gradient(145deg, #0b1a38 0%, #1a3a6b 50%, #1c5fa8 100%);
			border-radius: 16px; display: flex; align-items: center; justify-content: center;
			flex-shrink: 0;
			box-shadow: 0 8px 24px rgba(11,26,56,0.55), 0 2px 6px rgba(28,95,168,0.4), inset 0 1px 0 rgba(255,255,255,0.15);
			transition: transform 0.28s cubic-bezier(.34,1.56,.64,1), box-shadow 0.28s ease;
			position: relative;
		}
		.brand-logo:hover .brand-icon-wrap { transform: translateY(-3px) rotate(-3deg) scale(1.07); }
		.brand-icon-wrap::after {
			content: ''; position: absolute; inset: 0;
			border-radius: 16px; border: 1.5px solid rgba(255,255,255,0.2); pointer-events: none;
		}
		.brand-icon {
			font-size: 2.1rem; line-height: 1; position: relative; z-index: 1;
			filter: drop-shadow(0 2px 5px rgba(0,0,0,0.55));
			transition: transform 0.28s cubic-bezier(.34,1.56,.64,1);
		}
		.brand-logo:hover .brand-icon { transform: scale(1.12) rotate(6deg); }
		.brand-text { display: flex; flex-direction: column; line-height: 1; gap: 4px; }
		.brand-main {
			font-family: 'Abril Fatface', serif; font-size: 1.55rem; font-weight: 400;
			background: linear-gradient(135deg, #0b1a38 20%, #1565c0 70%, #2196f3 100%);
			-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
		}
		.brand-sub {
			font-family: 'DM Sans', sans-serif; font-size: 0.58rem; font-weight: 600;
			letter-spacing: 0.25em; text-transform: uppercase; color: #7a8fac;
			-webkit-text-fill-color: #7a8fac; display: flex; align-items: center; gap: 6px;
		}
		.brand-sub::before, .brand-sub::after { content: ''; display: inline-block; width: 18px; height: 1px; }
		.brand-sub::before { background: linear-gradient(90deg, transparent, #a0b4cc); }
		.brand-sub::after  { background: linear-gradient(90deg, #a0b4cc, transparent); }

		/* ── Smooth transitions ────────────────────────── */
		*, *::before, *::after {
			box-sizing: border-box;
			transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
		}

		/* ── Base ──────────────────────────────────────── */
		body {
			background-color: var(--bg-primary) !important;
			color: var(--text-primary) !important;
			font-family: 'DM Sans', sans-serif;
		}

		/* ── Navbar ────────────────────────────────────── */
		.navbar {
			background-color: var(--bg-navbar) !important;
			border-bottom: 1px solid var(--border-color);
		}
		.navbar-brand {
			color: var(--text-primary) !important;
			font-family: 'Playfair Display', serif;
			font-weight: 700;
		}
		.nav-link {
			color: var(--text-secondary) !important;
		}
		.nav-link:hover, .nav-link.active {
			color: var(--text-primary) !important;
		}
		[data-theme="dark"] .navbar-toggler-icon {
			filter: invert(1);
		}
		[data-theme="dark"] .navbar-toggler {
			border-color: var(--border-color);
		}

		/* ── Theme toggle pill ─────────────────────────── */
		.theme-toggle-btn {
			display: flex;
			align-items: center;
			gap: 8px;
			background: var(--toggle-bg);
			border: none;
			border-radius: 50px;
			padding: 6px 14px 6px 10px;
			cursor: pointer;
			font-size: 0.82rem;
			font-weight: 600;
			color: var(--text-primary);
			transition: all 0.3s ease;
			white-space: nowrap;
		}
		.theme-toggle-btn:hover { transform: scale(1.04); }

		/* ── Page heading ──────────────────────────────── */
		.page-heading {
			font-family: 'Playfair Display', serif;
			color: var(--text-primary);
			margin: 1.5rem 0 1rem;
		}
		.back-link { text-decoration: none; }
		.back-link img { filter: none; }
		[data-theme="dark"] .back-link img { filter: invert(1); }

		/* ── Book cards ────────────────────────────────── */
		.pdf-list { display: flex; flex-wrap: wrap; }

		.card {
			background-color: var(--bg-card) !important;
			border: 1px solid var(--border-color) !important;
			color: var(--text-primary) !important;
			box-shadow: var(--card-shadow);
			border-radius: 12px;
			overflow: hidden;
		}
		.card-title { color: var(--text-primary) !important; font-family: 'Playfair Display', serif; }
		.card-text  { color: var(--text-secondary) !important; font-size: 0.9rem; }

		/* ── Empty state alert ─────────────────────────── */
		.alert-warning {
			background-color: #fff8e1;
			border-color: #ffe082;
			color: #7a5c00;
		}
		[data-theme="dark"] .alert-warning {
			background-color: #2a2005;
			border-color: #5c4500;
			color: #fcd34d;
		}

		/* ── Sidebar list-group ────────────────────────── */
		.category { min-width: 180px; }

		.list-group-item {
			background-color: var(--bg-surface) !important;
			color: var(--text-primary) !important;
			border-color: var(--border-color) !important;
		}
		.list-group-item:hover {
			background-color: var(--bg-secondary) !important;
			color: var(--text-primary) !important;
		}
		.list-group-item.active {
			background-color: var(--sidebar-active-bg) !important;
			color: var(--sidebar-active-color) !important;
			border-color: var(--sidebar-active-bg) !important;
			font-family: 'Playfair Display', serif;
			font-weight: 600;
			pointer-events: none;
		}
	</style>
</head>
<body>
	<div class="container">

		<!-- NAVBAR -->
		<nav class="navbar navbar-expand-lg">
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
				<button class="navbar-toggler" type="button"
				        data-bs-toggle="collapse"
				        data-bs-target="#navbarSupportedContent"
				        aria-controls="navbarSupportedContent"
				        aria-expanded="false"
				        aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<li class="nav-item">
							<a class="nav-link active" aria-current="page" href="index.php">Store</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="about.php">About</a>
						</li>
						<li class="nav-item">
							<?php if (isset($_SESSION['user_id'])): ?>
								<a class="nav-link" href="admin.php">Admin</a>
							<?php else: ?>
								<a class="nav-link" href="login.php">Login</a>
							<?php endif; ?>
						</li>
					</ul>

					<!-- Theme toggle pill (matches admin.php) -->
					<button class="theme-toggle-btn ms-3"
					        id="themeToggle"
					        onclick="toggleTheme()"
					        title="Toggle dark/light mode">
						<span id="toggleIcon">🌙</span>
						<span id="themeLabel">Dark Mode</span>
					</button>
				</div>
			</div>
		</nav>

		<!-- Page heading -->
		<h1 class="display-4 p-3 fs-3 page-heading">
			<a href="index.php" class="back-link">
				<img src="img/back-arrow.PNG" width="35" alt="Back">
			</a>
			<?= htmlspecialchars($current_author['name']) ?>
		</h1>

		<div class="d-flex pt-3">

			<!-- Books grid -->
			<?php if ($books == 0): ?>
				<div class="alert alert-warning text-center p-5 flex-grow-1" role="alert">
					<img src="img/empty.png" width="100" alt="Empty"><br>
					There is no book by this author in the database.
				</div>
			<?php else: ?>
				<div class="pdf-list d-flex flex-wrap flex-grow-1">
					<?php foreach ($books as $book): ?>
					<div class="card m-1" style="width: 200px;">
						<img src="uploads/cover/<?= htmlspecialchars($book['cover']) ?>"
						     class="card-img-top" alt="Cover">
						<div class="card-body">
							<h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
							<p class="card-text">
								<i><b>By:
									<?php foreach ($authors as $author):
										if ($author['id'] == $book['author_id']):
											echo htmlspecialchars($author['name']);
											break;
										endif;
									endforeach; ?>
								<br></b></i>
								<?= htmlspecialchars($book['description']) ?>
								<br><i><b>Category:
									<?php foreach ($categories as $category):
										if ($category['id'] == $book['category_id']):
											echo htmlspecialchars($category['name']);
											break;
										endif;
									endforeach; ?>
								<br></b></i>
							</p>
							<a href="uploads/files/<?= htmlspecialchars($book['file']) ?>"
							   class="btn btn-success btn-sm">Open</a>
							<a href="uploads/files/<?= htmlspecialchars($book['file']) ?>"
							   class="btn btn-primary btn-sm"
							   download="<?= htmlspecialchars($book['title']) ?>">Download</a>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Sidebar -->
			<div class="category ms-3">

				<!-- Categories -->
				<div class="list-group">
					<?php if ($categories != 0): ?>
						<a href="#" class="list-group-item list-group-item-action active">Category</a>
						<?php foreach ($categories as $category): ?>
							<a href="category.php?id=<?= $category['id'] ?>"
							   class="list-group-item list-group-item-action">
								<?= htmlspecialchars($category['name']) ?>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<!-- Authors -->
				<div class="list-group mt-5">
					<?php if ($authors != 0): ?>
						<a href="#" class="list-group-item list-group-item-action active">Author</a>
						<?php foreach ($authors as $author): ?>
							<a href="author.php?id=<?= $author['id'] ?>"
							   class="list-group-item list-group-item-action <?= ($author['id'] == $id) ? 'fw-bold' : '' ?>">
								<?= htmlspecialchars($author['name']) ?>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

		</div><!-- /.d-flex -->
	</div><!-- /.container -->

	<script>
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

		// Init from localStorage (already set on <html> by the inline script,
		// this just syncs the button label/icon)
		applyTheme(localStorage.getItem('theme') || 'light');
	</script>
</body>
</html>