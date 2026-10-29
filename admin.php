<?php
session_start();

# Session protection - only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

# Database connection
include "db_conn.php";

# Get book read counts for bar chart
$sql = "SELECT b.title, COUNT(br.id) as read_count 
        FROM books b 
        LEFT JOIN book_reads br ON b.id = br.book_id 
        GROUP BY b.id, b.title 
        ORDER BY read_count DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$book_stats = $stmt->fetchAll();

$book_titles = [];
$book_counts = [];
foreach ($book_stats as $stat) {
    $book_titles[] = $stat['title'];
    $book_counts[] = $stat['read_count'];
}

# Get daily reads for last 30 days (User Trends)
$sql = "SELECT DATE(created_at) as day, COUNT(*) as total
        FROM book_reads
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY day ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$daily_reads = $stmt->fetchAll();

$trend_labels = [];
$trend_data   = [];
foreach ($daily_reads as $row) {
    $trend_labels[] = date('M d', strtotime($row['day']));
    $trend_data[]   = $row['total'];
}

# Get total users count
$sql = "SELECT COUNT(*) as total FROM admin WHERE role = 'user'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$total_users = $stmt->fetch()['total'];

# Get total books count
$sql = "SELECT COUNT(*) as total FROM books";
$stmt = $conn->prepare($sql);
$stmt->execute();
$total_books = $stmt->fetch()['total'];

# Get total reads count
$sql = "SELECT COUNT(*) as total FROM book_reads";
$stmt = $conn->prepare($sql);
$stmt->execute();
$total_reads = $stmt->fetch()['total'];

# Get all books with author and category for the table
$sql = "SELECT b.*, a.name AS author_name, c.name AS category_name
        FROM books b
        LEFT JOIN authors a    ON b.author_id   = a.id
        LEFT JOIN categories c ON b.category_id = c.id
        ORDER BY b.id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$all_books = $stmt->fetchAll();

# Get all categories
$sql = "SELECT * FROM categories ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$all_categories = $stmt->fetchAll();

# Get all authors
$sql = "SELECT * FROM authors ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$all_authors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>

  <script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="css/style.css">

  <style>
    :root {
      --bg-primary: #ffffff; --bg-secondary: #f8f9fa; --bg-card: #ffffff;
      --bg-navbar: #f8f9fa; --text-primary: #212529; --text-secondary: #6c757d;
      --text-muted: #6c757d; --border-color: #dee2e6; --shadow: rgba(0,0,0,0.08);
      --toggle-bg: #e2e8f0; --input-bg: #ffffff; --input-border: #ced4da;
      --category-active-bg: #0d6efd; --bg-surface: #ffffff; --border: #dee2e6;
    }
    [data-theme="dark"] {
      --bg-primary: #0f1117; --bg-secondary: #1a1d27; --bg-card: #1e2130;
      --bg-navbar: #13161f; --text-primary: #e8eaf0; --text-secondary: #9aa3b2;
      --text-muted: #6b7280; --border-color: #2d3148; --shadow: rgba(0,0,0,0.4);
      --toggle-bg: #3b4263; --input-bg: #1e2130; --input-border: #2d3148;
      --category-active-bg: #2563eb; --bg-surface: #1e2130; --border: #2d3148;
    }
    *, *::before, *::after { box-sizing: border-box; transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
    body { background-color: var(--bg-primary) !important; color: var(--text-primary) !important; }
    .navbar { background-color: var(--bg-navbar) !important; border-bottom: 1px solid var(--border-color); }
    .navbar-brand, .nav-link { color: var(--text-secondary) !important; }
    .navbar-brand { color: var(--text-primary) !important; font-weight: 700; }
    .nav-link:hover { color: var(--text-primary) !important; }
    .theme-toggle-btn {
      display: flex; align-items: center; gap: 8px;
      background: var(--toggle-bg); border: none; border-radius: 50px;
      padding: 6px 14px 6px 10px; cursor: pointer; font-size: 0.82rem;
      font-weight: 600; color: var(--text-primary); transition: all 0.3s ease; white-space: nowrap;
    }
    .theme-toggle-btn:hover { transform: scale(1.04); }
    .table { color: var(--text-primary) !important; }
    .table-bordered { border-color: var(--border-color) !important; }
    .table-bordered td, .table-bordered th { border-color: var(--border-color) !important; }
    [data-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: var(--bg-secondary) !important; color: var(--text-primary) !important; }
    [data-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) > * { background-color: var(--bg-card) !important; color: var(--text-primary) !important; }

    .stat-card-admin {
      border-radius: 14px;
      padding: 1.8rem;
      text-align: center;
      color: #fff;
      box-shadow: var(--shadow-md);
    }
    .stat-card-admin h2 {
      font-family: 'Playfair Display', serif;
      font-size: 2.8rem;
      font-weight: 700;
      margin: 0;
    }
    .stat-card-admin p { margin: .3rem 0 0; font-size: .9rem; opacity: .9; }
    .bg-blue   { background: #3b82f6; }
    .bg-green  { background: #10b981; }
    .bg-yellow { background: #f59e0b; }

    .chart-card {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 1.8rem;
      box-shadow: var(--shadow-sm);
      margin-bottom: 2rem;
    }
    .chart-card h4 {
      font-family: 'Playfair Display', serif;
      color: var(--text-primary);
      margin-bottom: .3rem;
      font-size: 1.3rem;
    }
    .chart-subtitle {
      font-size: .82rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: .08em;
      margin-bottom: 1.5rem;
      padding-bottom: .8rem;
      border-bottom: 1px solid var(--border);
    }

    /* Trend chart header like the screenshot */
    .trend-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 1.5rem;
      padding-bottom: .8rem;
      border-bottom: 1px solid var(--border);
    }
    .trend-header-left { display: flex; flex-direction: column; gap: .25rem; }
    .trend-icon {
      font-size: 1.6rem;
      line-height: 1;
    }
    .trend-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem;
      color: var(--text-primary);
      margin: 0;
    }
    .trend-badge {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      font-size: .75rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: .07em;
      cursor: pointer;
    }
    .trend-badge svg { width: 12px; height: 12px; }
  </style>
</head>
<body>
  <div class="container">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg">
      <div class="container-fluid">
        <a class="navbar-brand" href="admin.php"><span>Admin</span> Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin" aria-controls="navAdmin" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navAdmin">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="index.php">Store</a></li>
	<li class="nav-item"><a class="nav-link" href="add-book.php">Add Book</a></li>
	<li class="nav-item"><a class="nav-link" href="add-category.php">Add Category</a></li>
	<li class="nav-item"><a class="nav-link" href="add-author.php">Add Author</a></li>
	<li class="nav-item"><a class="nav-link" href="stats.php">Reading Stats</a></li>
	<li class="nav-item"><a class="nav-link" href="book-requests-admin.php">Book Requests</a></li>
	<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
          </ul>
          <button class="theme-toggle-btn ms-3" id="themeToggle" onclick="toggleTheme()" title="Toggle dark/light mode">
            <span id="toggleIcon">🌙</span>
            <span id="themeLabel">Dark Mode</span>
          </button>
        </div>
      </div>
    </nav>

    <!-- Welcome -->
    <div class="mt-4 mb-4">
      <h3 style="font-family:'Playfair Display',serif;color:var(--text-primary);">
        Welcome, <?= htmlspecialchars($_SESSION['user_email']); ?> 👋
      </h3>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-5">
      <div class="col-md-4 mb-3">
        <div class="stat-card-admin bg-blue">
          <h2><?= $total_users ?></h2>
          <p>Total Users</p>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="stat-card-admin bg-green">
          <h2><?= $total_books ?></h2>
          <p>Total Books</p>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="stat-card-admin bg-yellow">
          <h2><?= $total_reads ?></h2>
          <p>Total Reads</p>
        </div>
      </div>
    </div>

    <!-- USER TRENDS LINE CHART -->
    <div class="chart-card">
      <div class="trend-header">
        <div class="trend-header-left">
          <span class="trend-icon">📈</span>
          <h4 class="trend-title">User Trends</h4>
          <span class="trend-badge">
            Daily Actives
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </span>
        </div>
      </div>
      <canvas id="trendChart" height="80"></canvas>
    </div>

    <!-- MOST READ BOOKS BAR CHART -->
    <div class="chart-card">
      <h4>📊 Books Read by Users</h4>
      <p class="chart-subtitle">Most read books — highest to lowest</p>
      <canvas id="bookChart" height="<?= max(60, count($book_titles) * 40) ?>"></canvas>
    </div>


    <!-- ALL BOOKS TABLE -->
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <h4 class="mt-5 mb-3" style="font-family:'Playfair Display',serif;color:var(--text-primary);">All Books</h4>
    <table class="table table-bordered table-striped align-middle" style="table-layout:fixed;width:100%;">
      <thead class="table-dark">
        <tr>
          <th style="width:4%">#</th>
          <th style="width:18%">Title</th>
          <th style="width:12%">Author</th>
          <th style="width:40%">Description</th>
          <th style="width:12%">Category</th>
          <th style="width:14%">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($all_books)): ?>
          <tr><td colspan="6" class="text-center">No books found.</td></tr>
        <?php else: ?>
          <?php foreach ($all_books as $i => $book): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <?php if (!empty($book['cover'])): ?>
                <img src="uploads/cover/<?= htmlspecialchars($book['cover']) ?>"
                     alt="cover" height="80" class="d-block mb-1 rounded"><br>
              <?php endif; ?>
              <a href="track.php?book_id=<?= $book['id'] ?>" target="_blank" style="color:var(--accent);">
                <?= htmlspecialchars($book['title']) ?>
              </a>
            </td>
            <td><?= htmlspecialchars($book['author_name'] ?? '—') ?></td>
            <td style="word-wrap:break-word;"><?= htmlspecialchars($book['description'] ?? '—') ?></td>
            <td><?= htmlspecialchars($book['category_name'] ?? '—') ?></td>
            <td>
              <a href="edit-book.php?id=<?= $book['id'] ?>"
                 class="btn btn-sm btn-warning mb-1">Edit</a><br>
              <a href="php/delete-book.php?id=<?= $book['id'] ?>"
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this book?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>


    <!-- ALL CATEGORIES TABLE -->
    <h4 class="mt-5 mb-3" style="font-family:'Playfair Display',serif;color:var(--text-primary);">All Categories</h4>
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:5%">#</th>
          <th>Category Name</th>
          <th style="width:20%">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($all_categories)): ?>
          <tr><td colspan="3" class="text-center">No categories found.</td></tr>
        <?php else: ?>
          <?php foreach ($all_categories as $i => $cat): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($cat['name']) ?></td>
            <td>
              <a href="edit-category.php?id=<?= $cat['id'] ?>"
                 class="btn btn-sm btn-warning mb-1">Edit</a>
              <a href="php/delete-category.php?id=<?= $cat['id'] ?>"
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this category?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- ALL AUTHORS TABLE -->
    <h4 class="mt-5 mb-3" style="font-family:'Playfair Display',serif;color:var(--text-primary);">All Author</h4>
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:5%">#</th>
          <th>Author Name</th>
          <th style="width:20%">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($all_authors)): ?>
          <tr><td colspan="3" class="text-center">No authors found.</td></tr>
        <?php else: ?>
          <?php foreach ($all_authors as $i => $author): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($author['name']) ?></td>
            <td>
              <a href="edit-author.php?id=<?= $author['id'] ?>"
                 class="btn btn-sm btn-warning mb-1">Edit</a>
              <a href="php/delete-author.php?id=<?= $author['id'] ?>"
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this author?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

  </div>

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
      rebuildCharts();
    }
    function toggleTheme() {
      applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    }

    // ── Data from PHP ─────────────────────────────────────
    const bookTitles  = <?= json_encode($book_titles) ?>;
    const bookCounts  = <?= json_encode($book_counts) ?>;
    const trendLabels = <?= json_encode($trend_labels) ?>;
    const trendData   = <?= json_encode($trend_data) ?>;

    function cssVar(name) {
      return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    var trendChart   = null;
    var bookChart    = null;

    function rebuildCharts() {
      if (trendChart) trendChart.destroy();
      if (bookChart)  bookChart.destroy();

      var textMuted   = cssVar('--text-muted');
      var textPrimary = cssVar('--text-primary');
      var border      = cssVar('--border');
      var surface     = cssVar('--bg-surface');
      var max         = Math.max(...bookCounts, 1);

      // ── TREND LINE CHART ──────────────────────────────
      var tCtx = document.getElementById('trendChart').getContext('2d');
      var gradient = tCtx.createLinearGradient(0, 0, 0, 300);
      gradient.addColorStop(0, 'rgba(59,130,246,0.18)');
      gradient.addColorStop(1, 'rgba(59,130,246,0.00)');

      trendChart = new Chart(tCtx, {
        type: 'line',
        data: {
          labels: trendLabels,
          datasets: [{
            label: 'Daily Reads',
            data: trendData,
            borderColor: '#3b82f6',
            backgroundColor: gradient,
            borderWidth: 2.5,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            tension: 0.4,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: surface,
              titleColor: textPrimary,
              bodyColor: textMuted,
              borderColor: border,
              borderWidth: 1,
              padding: 10,
              callbacks: {
                label: ctx => ` ${ctx.raw} read${ctx.raw !== 1 ? 's' : ''}`
              }
            }
          },
          scales: {
            x: {
              grid: { color: border, drawBorder: false },
              ticks: { color: textMuted, font: { family: 'DM Sans', size: 11 } }
            },
            y: {
              beginAtZero: true,
              grid: { color: border, drawBorder: false },
              ticks: {
                stepSize: 1,
                color: textMuted,
                font: { family: 'DM Sans', size: 11 },
                precision: 0
              }
            }
          }
        }
      });

      // ── BAR CHART ─────────────────────────────────────
      var bgColors = bookCounts.map(val => {
        var opacity = 0.4 + (val / max) * 0.6;
        return `rgba(192, 98, 47, ${opacity})`;
      });

      var bCtx = document.getElementById('bookChart').getContext('2d');
      bookChart = new Chart(bCtx, {
        type: 'bar',
        data: {
          labels: bookTitles,
          datasets: [{
            label: 'Number of Reads',
            data: bookCounts,
            backgroundColor: bgColors,
            borderColor: 'rgba(192, 98, 47, 1)',
            borderWidth: 2,
            borderRadius: 6,
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: surface,
              titleColor: textPrimary,
              bodyColor: textMuted,
              borderColor: border,
              borderWidth: 1,
              padding: 10,
              callbacks: {
                label: ctx => ` ${ctx.raw} read${ctx.raw !== 1 ? 's' : ''}`
              }
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: border },
              ticks: { stepSize: 1, color: textMuted, font: { family: 'DM Sans' } },
              title: { display: true, text: 'Number of Reads', color: textMuted, font: { family: 'DM Sans' } }
            },
            y: {
              grid: { display: false },
              ticks: { color: textPrimary, font: { family: 'DM Sans', weight: '500' } }
            }
          }
        }
      });
    }

    // Init
    applyTheme(localStorage.getItem('theme') || 'light');
    rebuildCharts();
  </script>
</body>
</html>