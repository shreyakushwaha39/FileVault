<?php
require_once 'config.php';

// If already logged in, go to dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check if email or username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Email or username already registered.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                // Random avatar color
                $colors = ['#6366f1','#ec4899','#f59e0b','#10b981','#3b82f6','#8b5cf6'];
                $color = $colors[array_rand($colors)];

                $ins = $conn->prepare("INSERT INTO users (username, email, password, avatar_color) VALUES (?, ?, ?, ?)");
                $ins->bind_param("ssss", $username, $email, $hashed, $color);

                if ($ins->execute()) {
                    $success = 'Account created! You can now log in.';
                } else {
                    $error = 'Registration failed. Try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — FileVault</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">

<div class="auth-split">
  <!-- Left: branding panel -->
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

  <!-- Right: form panel -->
  <div class="auth-form-panel">
    <div class="auth-form-wrap">
      <h2 class="form-title">Create account</h2>
      <p class="form-sub">Already have one? <a href="login.php">Sign in</a></p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?> <a href="login.php">Login now →</a></div>
      <?php endif; ?>

      <form method="POST" class="auth-form" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

        <div class="field-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="e.g. shreya123" required
                 value="<?= e($_POST['username'] ?? '') ?>">
        </div>

        <div class="field-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="you@email.com" required
                 value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div class="field-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min 6 characters" required>
        </div>

        <div class="field-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Repeat password" required>
        </div>

        <button type="submit" class="btn-primary btn-full">Create Account →</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
