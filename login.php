<?php
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT id, username, password, avatar_color FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['avatar_color']  = $user['avatar_color'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — FileVault</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">

<div class="auth-split">
  <div class="auth-brand">
    <div class="brand-content">
      <div class="logo-mark">⬡</div>
      <h1 class="brand-name">FileVault</h1>
      <p class="brand-tagline">Secure file sharing<br>for everyone.</p>
      <div class="brand-features">
        <div class="feat"><span>🔒</span> Encrypted uploads</div>
        <div class="feat"><span>📊</span> Download tracking</div>
        <div class="feat"><span>🔗</span> Shareable links</div>
      </div>
    </div>
    <div class="brand-bg-shapes">
      <div class="shape s1"></div>
      <div class="shape s2"></div>
      <div class="shape s3"></div>
    </div>
  </div>

  <div class="auth-form-panel">
    <div class="auth-form-wrap">
      <h2 class="form-title">Welcome back</h2>
      <p class="form-sub">New here? <a href="register.php">Create an account</a></p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

        <div class="field-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="you@email.com" required>
        </div>

        <div class="field-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Your password" required>
        </div>

        <button type="submit" class="btn-primary btn-full">Sign In →</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
