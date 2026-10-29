<?php
$pageTitle = "Leaderboard";
include __DIR__ . '/header.php';

include __DIR__ . '/db_conn.php';

$sql = "SELECT a.full_name, COUNT(rh.id) as books_read,
        (SELECT b.name FROM badges b 
         JOIN user_badges ub ON b.id = ub.badge_id 
         WHERE ub.user_id = a.id ORDER BY b.required_books DESC LIMIT 1) as top_badge
        FROM admin a
        JOIN reading_history rh ON a.id = rh.user_id
        WHERE a.role = 'user'
        GROUP BY a.id ORDER BY books_read DESC LIMIT 10";

$result = $conn->query($sql);
$rows = $result->fetchAll();
?>

<style>
  /* ── Leaderboard Page Styles ── */
  .lb-hero {
    background: var(--hero-bg);
    color: var(--hero-text);
    border-radius: 20px;
    padding: 3rem 2rem 2.5rem;
    text-align: center;
    margin: 1.5rem 0 2.5rem;
    position: relative;
    overflow: hidden;
  }
  .lb-hero::before {
    content: '🏆';
    position: absolute;
    font-size: 9rem;
    opacity: 0.07;
    top: -10px;
    right: 40px;
    pointer-events: none;
  }
  .lb-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.4rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
  }
  .lb-hero p {
    opacity: 0.8;
    font-size: 1rem;
    margin: 0;
  }

  /* ── Podium (top 3) ── */
  .podium-wrap {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2.5rem;
  }
  .podium-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    text-align: center;
    padding: 1.5rem 1rem 1.2rem;
    flex: 1;
    max-width: 200px;
    box-shadow: 0 4px 20px var(--shadow);
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .podium-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px var(--card-hover-shadow);
  }
  .podium-card.first {
    padding-top: 2rem;
    border-color: #f59e0b;
    box-shadow: 0 6px 30px rgba(245,158,11,0.2);
  }
  .podium-card.second { border-color: #94a3b8; }
  .podium-card.third  { border-color: #b45309; }

  .podium-medal {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 0.5rem;
    line-height: 1;
  }
  .podium-card.first .podium-medal  { font-size: 2.8rem; }

  .podium-name {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 1rem;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
    word-break: break-word;
  }
  .podium-card.first .podium-name { font-size: 1.15rem; }

  .podium-books {
    font-size: 0.82rem;
    color: var(--text-secondary);
    margin-bottom: 0.4rem;
  }
  .podium-badge-pill {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 999px;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
  }
  .podium-card.first .podium-badge-pill {
    background: #fef3c7;
    color: #92400e;
    border-color: #f59e0b;
  }

  .podium-rank-label {
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--hero-bg);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 999px;
    letter-spacing: 0.05em;
    white-space: nowrap;
  }
  .podium-card.first .podium-rank-label { background: linear-gradient(90deg,#f59e0b,#ef4444); }
  .podium-card.second .podium-rank-label { background: #64748b; }
  .podium-card.third  .podium-rank-label { background: #92400e; }

  /* ── Rest of leaderboard table ── */
  .lb-table-wrap {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 16px var(--shadow);
  }
  .lb-table-header {
    display: grid;
    grid-template-columns: 60px 1fr 110px 150px;
    padding: 0.75rem 1.5rem;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
  }
  .lb-row {
    display: grid;
    grid-template-columns: 60px 1fr 110px 150px;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    align-items: center;
    transition: background 0.15s ease;
  }
  .lb-row:last-child { border-bottom: none; }
  .lb-row:hover { background: var(--bg-secondary); }

  .lb-rank {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--text-muted);
  }
  .lb-name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95rem;
  }
  .lb-books {
    font-size: 0.88rem;
    color: var(--text-secondary);
  }
  .lb-books strong {
    color: var(--text-primary);
    font-size: 1rem;
  }
  .lb-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 999px;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
  }
  .lb-badge.has-badge {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
  }
  [data-theme="dark"] .lb-badge.has-badge {
    background: #1e3a5f;
    color: #93c5fd;
    border-color: #2563eb;
  }

  .lb-empty {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
    font-size: 0.95rem;
  }

  @media (max-width: 576px) {
    .lb-table-header,
    .lb-row { grid-template-columns: 45px 1fr 80px; }
    .lb-table-header > *:last-child,
    .lb-row > *:last-child { display: none; }
    .podium-wrap { gap: 0.5rem; }
    .podium-card { padding: 1.2rem 0.6rem 1rem; }
  }
</style>

<div class="container py-3">

  <!-- Hero -->
  <div class="lb-hero">
    <h1>📚 Reader Leaderboard</h1>
    <p>The most dedicated readers in our community</p>
  </div>

  <?php if (empty($rows)): ?>
    <div class="lb-empty">
      <div style="font-size:3rem;margin-bottom:1rem;">📖</div>
      No reading activity yet. Start reading to appear here!
    </div>
  <?php else: ?>

  <!-- Podium: Top 3 -->
  <?php
    $badgeIcons = [
      'Curious Reader' => '📗',
      'Bookworm'       => '📘',
      'Avid Reader'    => '📕',
      'Top Reader'     => '🏅',
      'Legend'         => '👑',
    ];
    $podiumOrder = [1, 0, 2]; // show 2nd, 1st, 3rd visually
    $podiumClasses = ['second', 'first', 'third'];
    $podiumMedals  = ['🥈', '🥇', '🥉'];
    $podiumLabels  = ['2nd Place', '1st Place', '3rd Place'];
  ?>
  <div class="podium-wrap">
    <?php foreach ($podiumOrder as $i => $idx):
      if (!isset($rows[$idx])) continue;
      $r = $rows[$idx];
      $badgeIcon = isset($badgeIcons[$r['top_badge']]) ? $badgeIcons[$r['top_badge']] : '🎖️';
    ?>
    <div class="podium-card <?= $podiumClasses[$i] ?>">
      <span class="podium-rank-label"><?= $podiumLabels[$i] ?></span>
      <span class="podium-medal"><?= $podiumMedals[$i] ?></span>
      <div class="podium-name"><?= htmlspecialchars($r['full_name']) ?></div>
      <div class="podium-books"><?= $r['books_read'] ?> books read</div>
      <span class="podium-badge-pill">
        <?= $r['top_badge'] ? $badgeIcon . ' ' . htmlspecialchars($r['top_badge']) : '— No badge yet' ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Ranks 4–10 -->
  <?php if (count($rows) > 3): ?>
  <div class="lb-table-wrap">
    <div class="lb-table-header">
      <span>Rank</span>
      <span>Reader</span>
      <span>Books Read</span>
      <span>Top Badge</span>
    </div>
    <?php foreach ($rows as $i => $row):
      if ($i < 3) continue;
      $badgeIcon = isset($badgeIcons[$row['top_badge']]) ? $badgeIcons[$row['top_badge']] : '🎖️';
    ?>
    <div class="lb-row">
      <span class="lb-rank">#<?= $i + 1 ?></span>
      <span class="lb-name"><?= htmlspecialchars($row['full_name']) ?></span>
      <span class="lb-books"><strong><?= $row['books_read'] ?></strong> books</span>
      <span class="lb-badge <?= $row['top_badge'] ? 'has-badge' : '' ?>">
        <?= $row['top_badge'] ? $badgeIcon . ' ' . htmlspecialchars($row['top_badge']) : '— None yet' ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php endif; ?>

</div>

<?php include __DIR__ . '/footer.php'; ?>