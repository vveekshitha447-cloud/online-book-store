<?php
session_start();

// Only admins can see this - check FIRST before anything else
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit;
}

include "db_conn.php";
include "php/func-stats.php";

$reads_per_book = get_reads_per_book($conn);
$reads_per_day  = get_reads_per_day($conn);
$top_readers    = get_top_readers($conn);

// Encode for JS
$book_labels = json_encode(array_column($reads_per_book, 'title'));
$book_data   = json_encode(array_column($reads_per_book, 'total_reads'));
$day_labels  = json_encode(array_column($reads_per_day,  'day'));
$day_data    = json_encode(array_column($reads_per_day,  'total'));

// Summary counts
$total_reads  = array_sum(array_column($reads_per_book, 'total_reads'));
$total_books  = count($reads_per_book);
$active_today = 0;
foreach ($reads_per_day as $d) {
    if ($d['day'] === date('Y-m-d')) $active_today = $d['total'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reading Stats — Admin</title>
  <script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="css/style.css">
  <style>
    :root{--bg-primary:#ffffff;--bg-secondary:#f8f9fa;--bg-card:#ffffff;--bg-navbar:#f8f9fa;--text-primary:#212529;--text-secondary:#6c757d;--text-muted:#6c757d;--border-color:#dee2e6;--shadow:rgba(0,0,0,0.08);--toggle-bg:#e2e8f0;--input-bg:#ffffff;--accent:#0d6efd;--bg-surface:#ffffff;--border:#dee2e6;--shadow-sm:0 2px 8px rgba(0,0,0,0.06);--bg-surface-2:#f8f9fa;--accent-soft:#e0eaff;}
    [data-theme="dark"]{--bg-primary:#0f1117;--bg-secondary:#1a1d27;--bg-card:#1e2130;--bg-navbar:#13161f;--text-primary:#e8eaf0;--text-secondary:#9aa3b2;--text-muted:#6b7280;--border-color:#2d3148;--shadow:rgba(0,0,0,0.4);--toggle-bg:#3b4263;--input-bg:#1e2130;--accent:#3b82f6;--bg-surface:#1e2130;--border:#2d3148;--shadow-sm:0 2px 8px rgba(0,0,0,0.3);--bg-surface-2:#13161f;--accent-soft:#1e3a5f;}
    *,*::before,*::after{box-sizing:border-box;transition:background-color 0.3s ease,color 0.3s ease,border-color 0.3s ease;}
    body{background-color:var(--bg-primary)!important;color:var(--text-primary)!important;}
    .navbar{background-color:var(--bg-navbar)!important;border-bottom:1px solid var(--border-color);}
    .navbar-brand,.navbar-brand span{color:var(--text-primary)!important;font-weight:700;}
    .nav-link{color:var(--text-secondary)!important;}
    .nav-link:hover,.nav-link.active{color:var(--text-primary)!important;}
    .page-header h2{color:var(--text-primary);}
    .page-header p{color:var(--text-secondary);}
    .stats-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-top: 2rem;
      margin-bottom: 3rem;
    }
    .stat-card {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 1.6rem;
      box-shadow: var(--shadow-sm);
    }
    .stat-card.full-width { grid-column: 1 / -1; }
    .stat-card h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.1rem;
      color: var(--text-primary);
      margin-bottom: 1.2rem;
      padding-bottom: .7rem;
      border-bottom: 1px solid var(--border);
    }

    .summary-tiles {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
      margin-top: 1.5rem;
    }
    .tile {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 1.4rem 1.6rem;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: .3rem;
    }
    .tile-value {
      font-family: 'Playfair Display', serif;
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--accent);
      line-height: 1;
    }
    .tile-label {
      font-size: .82rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: .07em;
    }

    .readers-table { width: 100%; border-collapse: collapse; }
    .readers-table th {
      font-size: .78rem;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: var(--text-muted);
      padding: .5rem .8rem;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }
    .readers-table td {
      padding: .7rem .8rem;
      font-size: .92rem;
      color: var(--text-primary);
      border-bottom: 1px solid var(--border);
    }
    .readers-table tr:last-child td { border-bottom: none; }
    .rank-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 24px; height: 24px;
      border-radius: 50%;
      background: var(--accent-soft);
      color: var(--accent);
      font-size: .78rem;
      font-weight: 600;
    }
    .rank-badge.gold   { background: #fff8e1; color: #f59e0b; }
    .rank-badge.silver { background: #f0f0f0; color: #6b7280; }
    .rank-badge.bronze { background: #fdf0e6; color: #b45309; }

    .read-bar-wrap { display: flex; align-items: center; gap: .7rem; }
    .read-bar-bg { flex: 1; height: 6px; background: var(--bg-surface-2); border-radius: 99px; overflow: hidden; }
    .read-bar-fill { height: 100%; background: var(--accent); border-radius: 99px; }

    .empty-chart {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 150px;
      color: var(--text-muted);
      font-size: .9rem;
      border: 2px dashed var(--border);
      border-radius: 10px;
    }

    @media (max-width: 768px) {
      .stats-grid { grid-template-columns: 1fr; }
      .stat-card.full-width { grid-column: 1; }
      .summary-tiles { grid-template-columns: 1fr 1fr; }
    }
  .theme-toggle-btn{display:flex;align-items:center;gap:8px;background:var(--toggle-bg,#e2e8f0);border:none;border-radius:50px;padding:6px 14px 6px 10px;cursor:pointer;font-size:0.82rem;font-weight:600;color:var(--text-primary,#212529);transition:all 0.3s ease;white-space:nowrap;}
  .theme-toggle-btn:hover{transform:scale(1.04);}
  </style>
</head>
<body>
<div class="container">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="admin.php"><span>Admin</span> Panel</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navStats" aria-controls="navStats" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navStats">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="index.php">Store</a></li>
          <li class="nav-item"><a class="nav-link" href="admin.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="stats.php">Reading Stats</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
        <button class="theme-toggle-btn ms-3" id="themeToggle" onclick="toggleTheme()" title="Toggle dark/light mode">
          <span id="toggleIcon">🌙</span>
          <span id="themeLabel">Dark Mode</span>
        </button>
      </div>
    </div>
  </nav>

  <!-- PAGE HEADER -->
  <div class="page-header mt-4">
    <h2>Reading Statistics</h2>
    <p>Live insights into how readers are engaging with your books.</p>
  </div>

  <!-- SUMMARY TILES -->
  <div class="summary-tiles">
    <div class="tile">
      <span class="tile-value"><?= number_format($total_reads) ?></span>
      <span class="tile-label">Total Reads</span>
    </div>
    <div class="tile">
      <span class="tile-value"><?= $total_books ?></span>
      <span class="tile-label">Books Read</span>
    </div>
    <div class="tile">
      <span class="tile-value"><?= $active_today ?></span>
      <span class="tile-label">Reads Today</span>
    </div>
  </div>

  <!-- CHARTS GRID -->
  <div class="stats-grid">

    <!-- Line chart -->
    <div class="stat-card full-width">
      <h3>📈 Daily Reading Activity — Last 30 Days</h3>
      <?php if (empty($reads_per_day)) { ?>
        <div class="empty-chart">No reading activity in the last 30 days yet.</div>
      <?php } else { ?>
        <canvas id="lineChart"></canvas>
      <?php } ?>
    </div>

    <!-- Bar chart -->
    <div class="stat-card">
      <h3>📚 Most Read Books</h3>
      <?php if (empty($reads_per_book)) { ?>
        <div class="empty-chart">No book reads recorded yet.</div>
      <?php } else { ?>
        <canvas id="barChart"></canvas>
      <?php } ?>
    </div>

    <!-- Top readers -->
    <div class="stat-card">
      <h3>🏆 Top Readers</h3>
      <?php if (empty($top_readers)) { ?>
        <div class="empty-chart">No reader data yet.</div>
      <?php } else {
        $max = $top_readers[0]['total_reads'];
      ?>
      <table class="readers-table">
        <thead>
          <tr><th>#</th><th>Email</th><th>Books Read</th></tr>
        </thead>
        <tbody>
          <?php foreach ($top_readers as $i => $reader) {
            $pct = $max > 0 ? round(($reader['total_reads'] / $max) * 100) : 0;
            $badge_class = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
          ?>
          <tr>
            <td><span class="rank-badge <?= $badge_class ?>"><?= $i + 1 ?></span></td>
            <td><?= htmlspecialchars($reader['email']) ?></td>
            <td>
              <div class="read-bar-wrap">
                <div class="read-bar-bg">
                  <div class="read-bar-fill" style="width:<?= $pct ?>%"></div>
                </div>
                <span style="font-size:.82rem;color:var(--text-muted);min-width:2rem;text-align:right">
                  <?= $reader['total_reads'] ?>
                </span>
              </div>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } ?>
    </div>

  </div>
</div>

<script>
  function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    localStorage.setItem('theme', t);
    var icon  = document.getElementById('toggleIcon');
    var label = document.getElementById('themeLabel');
    if (t === 'dark') { icon.textContent = '☀️'; label.textContent = 'Light Mode'; }
    else { icon.textContent = '🌙'; label.textContent = 'Dark Mode'; }
    updateChartColors();
  }
  function toggleTheme() {
    applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  }

  var bookLabels = <?= $book_labels ?>;
  var bookData   = <?= $book_data ?>;
  var dayLabels  = <?= $day_labels ?>;
  var dayData    = <?= $day_data ?>;

  function cssVar(name) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  }

  var lineChart, barChart;

  function buildCharts() {
    var accent      = '#3b82f6';
    var textPrimary = '#1a1a2e';
    var textMuted   = '#6b7280';
    var border      = '#e5e7eb';
    var surface     = '#ffffff';

    // LINE CHART
    var lineEl = document.getElementById('lineChart');
    if (lineEl && dayLabels.length > 0) {
      if (lineChart) lineChart.destroy();
      var lineCtx = lineEl.getContext('2d');
      var gradient = lineCtx.createLinearGradient(0, 0, 0, 280);
      gradient.addColorStop(0, accent + '55');
      gradient.addColorStop(1, accent + '00');
      lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
          labels: dayLabels,
          datasets: [{
            label: 'Reads',
            data: dayData,
            borderColor: accent,
            backgroundColor: gradient,
            borderWidth: 2.5,
            pointBackgroundColor: accent,
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: surface, titleColor: textPrimary, bodyColor: textMuted, borderColor: border, borderWidth: 1, padding: 10 }
          },
          scales: {
            x: { grid: { color: border }, ticks: { color: textMuted, font: { family: 'DM Sans' } } },
            y: { beginAtZero: true, grid: { color: border }, ticks: { color: textMuted, font: { family: 'DM Sans' }, precision: 0 } }
          }
        }
      });
    }

    // BAR CHART
    var barEl = document.getElementById('barChart');
    if (barEl && bookLabels.length > 0) {
      if (barChart) barChart.destroy();
      var barCtx = barEl.getContext('2d');
      barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
          labels: bookLabels,
          datasets: [{
            label: 'Total Reads',
            data: bookData,
            backgroundColor: accent + 'cc',
            hoverBackgroundColor: accent,
            borderRadius: 6,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          indexAxis: 'y',
          plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: surface, titleColor: textPrimary, bodyColor: textMuted, borderColor: border, borderWidth: 1, padding: 10 }
          },
          scales: {
            x: { beginAtZero: true, grid: { color: border }, ticks: { color: textMuted, font: { family: 'DM Sans' }, precision: 0 } },
            y: { grid: { display: false }, ticks: { color: textMuted, font: { family: 'DM Sans' } } }
          }
        }
      });
    }
  }

  function updateChartColors() {
    buildCharts();
  }

  applyTheme(localStorage.getItem('theme') || 'light');
  buildCharts();
</script>
</body>
</html>