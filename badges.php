<?php
include 'php/db_conn.php';
session_start();
$user_id = $_SESSION['user_id'];

$sql = "SELECT b.*, IF(ub.id IS NOT NULL,1,0) as earned
        FROM badges b
        LEFT JOIN user_badges ub ON b.id=ub.badge_id AND ub.user_id=?
        ORDER BY b.required_books";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$badges = $stmt->get_result();

while ($b = $badges->fetch_assoc()):
?>
  <div class="badge <?= $b['earned'] ? 'earned' : 'locked' ?>">
    <span><?= $b['icon'] ?></span>
    <p><?= $b['name'] ?></p>
    <small><?= $b['required_books'] ?> books</small>
  </div>
<?php endwhile; ?>