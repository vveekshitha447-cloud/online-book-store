<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password - Online Bookstore</title>
  <link rel="stylesheet" href="css/style.css"/>
  <style>
    body {
      background: #f4f4f4;
      font-family: 'Segoe UI', sans-serif;
    }
    .fp-container {
      max-width: 420px;
      margin: 80px auto;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      padding: 40px 35px;
    }
    .fp-container h2 {
      text-align: center;
      margin-bottom: 8px;
      color: #2c3e50;
      font-size: 1.8rem;
    }
    .fp-container p.subtitle {
      text-align: center;
      color: #777;
      font-size: 0.9rem;
      margin-bottom: 28px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #333;
      font-size: 0.9rem;
    }
    .form-group input {
      width: 100%;
      padding: 11px 14px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 0.95rem;
      box-sizing: border-box;
      transition: border-color 0.2s;
    }
    .form-group input:focus {
      outline: none;
      border-color: #e74c3c;
    }
    .btn-reset {
      width: 100%;
      padding: 12px;
      background: #e74c3c;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-reset:hover {
      background: #c0392b;
    }
    .back-link {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
    }
    .back-link a {
      color: #e74c3c;
      text-decoration: none;
      font-weight: 600;
    }
    .back-link a:hover {
      text-decoration: underline;
    }
    .alert {
      padding: 12px 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }
    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .alert-danger {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .icon-lock {
      text-align: center;
      font-size: 3rem;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="fp-container">
  <div class="icon-lock">🔒</div>
  <h2>Forgot Password?</h2>
  <p class="subtitle">Enter your registered email address and we'll send you a link to reset your password.</p>

  <?php if (isset($_SESSION['fp_success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['fp_success']); unset($_SESSION['fp_success']); ?></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['fp_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['fp_error']); unset($_SESSION['fp_error']); ?></div>
  <?php endif; ?>

  <form action="php/forgot-password-handler.php" method="POST">
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" placeholder="Enter your email" required />
    </div>
    <button type="submit" class="btn-reset">Send Reset Link</button>
  </form>

  <div class="back-link">
    <a href="login.php">← Back to Login</a>
  </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>