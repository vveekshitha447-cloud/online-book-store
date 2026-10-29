<?php
session_start();
require_once 'db_conn.php';

$token = trim($_GET['token'] ?? '');
$valid = false;
$email = '';

if (!empty($token)) {
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row   = $result->fetch_assoc();
        $email = $row['email'];
        $valid = true;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password - Online Bookstore</title>
  <link rel="stylesheet" href="css/style.css"/>
  <style>
    body { background:#f4f4f4; font-family:'Segoe UI',sans-serif; }
    .rp-container {
      max-width:420px; margin:80px auto;
      background:#fff; border-radius:10px;
      box-shadow:0 4px 20px rgba(0,0,0,0.1); padding:40px 35px;
    }
    .rp-container h2 { text-align:center; color:#2c3e50; margin-bottom:8px; font-size:1.8rem; }
    .rp-container p.subtitle { text-align:center; color:#777; font-size:0.9rem; margin-bottom:28px; }
    .form-group { margin-bottom:20px; }
    .form-group label { display:block; font-weight:600; margin-bottom:6px; color:#333; font-size:0.9rem; }
    .form-group input {
      width:100%; padding:11px 14px; border:1px solid #ddd;
      border-radius:6px; font-size:0.95rem; box-sizing:border-box; transition:border-color 0.2s;
    }
    .form-group input:focus { outline:none; border-color:#e74c3c; }
    .btn-submit {
      width:100%; padding:12px; background:#e74c3c; color:#fff;
      border:none; border-radius:6px; font-size:1rem; font-weight:600;
      cursor:pointer; transition:background 0.2s;
    }
    .btn-submit:hover { background:#c0392b; }
    .back-link { text-align:center; margin-top:20px; font-size:0.9rem; }
    .back-link a { color:#e74c3c; text-decoration:none; font-weight:600; }
    .back-link a:hover { text-decoration:underline; }
    .alert { padding:12px 15px; border-radius:6px; margin-bottom:20px; font-size:0.9rem; }
    .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    .alert-danger  { background:#f8d7da; color:#721c24;  border:1px solid #f5c6cb; }
    .icon { text-align:center; font-size:3rem; margin-bottom:15px; }
    .strength-bar { height:5px; border-radius:3px; margin-top:6px; transition:all 0.3s; background:#eee; }
    .strength-text { font-size:0.75rem; margin-top:4px; }
    .invalid-box { text-align:center; }
    .invalid-box p { color:#777; margin-bottom:20px; }
    .invalid-box a { background:#e74c3c; color:#fff; padding:10px 24px; border-radius:6px; text-decoration:none; font-weight:600; }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="rp-container">

  <?php if (!$valid): ?>
    <div class="icon">❌</div>
    <h2>Link Expired</h2>
    <div class="invalid-box">
      <p>This password reset link is invalid or has expired (links are valid for 1 hour).</p>
      <a href="forgot-password.php">Request a New Link</a>
    </div>

  <?php else: ?>
    <div class="icon">🔑</div>
    <h2>Reset Password</h2>
    <p class="subtitle">Enter your new password below.</p>

    <?php if (isset($_SESSION['rp_error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['rp_error']); unset($_SESSION['rp_error']); ?></div>
    <?php endif; ?>

    <form action="php/reset-password-handler.php" method="POST">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>"/>
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>"/>

      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" placeholder="Min. 8 characters" required oninput="checkStrength(this.value)"/>
        <div class="strength-bar" id="strengthBar"></div>
        <div class="strength-text" id="strengthText"></div>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required/>
      </div>

      <button type="submit" class="btn-submit">Update Password</button>
    </form>

    <div class="back-link"><a href="login.php">← Back to Login</a></div>
  <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

<script>
function checkStrength(val) {
  const bar  = document.getElementById('strengthBar');
  const text = document.getElementById('strengthText');
  let strength = 0;
  if (val.length >= 8)            strength++;
  if (/[A-Z]/.test(val))         strength++;
  if (/[0-9]/.test(val))         strength++;
  if (/[^A-Za-z0-9]/.test(val))  strength++;

  const colors = ['#e74c3c','#e67e22','#f1c40f','#2ecc71'];
  const labels = ['Weak','Fair','Good','Strong'];
  if (val.length === 0) { bar.style.width='0'; text.textContent=''; return; }
  bar.style.background = colors[strength-1];
  bar.style.width = (strength * 25) + '%';
  text.textContent = 'Strength: ' + labels[strength-1];
  text.style.color = colors[strength-1];
}
</script>
</body>
</html>