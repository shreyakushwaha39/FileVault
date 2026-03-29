<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid link.");
}

// Fetch file by share token
$stmt = $conn->prepare("
    SELECT f.*, u.username
    FROM files f
    JOIN users u ON u.id = f.user_id
    WHERE f.share_token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    die("File not found or link is invalid.");
}

// If private, only owner can access
if ($file['permission'] === 'private') {
    if (!isLoggedIn() || $_SESSION['user_id'] !== $file['user_id']) {
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($file['original_name']) ?> — FileVault</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="share-page">

<div class="share-container">
  <div class="share-logo">
    <span class="logo-mark">⬡</span>
    <span class="logo-text">FileVault</span>
  </div>

  <div class="share-card">
    <div class="share-file-icon"><?= getFileIcon($file['file_type']) ?></div>

    <h1 class="share-filename"><?= e($file['original_name']) ?></h1>

    <div class="share-meta">
      <div class="share-meta-item">
        <span class="meta-label">Size</span>
        <span class="meta-value"><?= formatSize($file['file_size']) ?></span>
      </div>
      <div class="share-meta-item">
        <span class="meta-label">Uploaded by</span>
        <span class="meta-value"><?= e($file['username']) ?></span>
      </div>
      <div class="share-meta-item">
        <span class="meta-label">Date</span>
        <span class="meta-value"><?= date('d M Y', strtotime($file['upload_date'])) ?></span>
      </div>
      <div class="share-meta-item">
        <span class="meta-label">Downloads</span>
        <span class="meta-value"><?= $file['download_count'] ?></span>
      </div>
    </div>

    <?php if ($file['is_encrypted']): ?>
      <div class="share-enc-badge">🔒 This file is encrypted — it will be decrypted automatically on download.</div>
    <?php endif; ?>

    <a href="download.php?id=<?= $file['id'] ?>" class="btn-primary btn-full btn-download-big">
      ⬇️ Download File
    </a>

    <p class="share-footer">Shared via <strong>FileVault</strong> · <a href="register.php">Create your own account</a></p>
  </div>
</div>

<script src="assets/page-transition.js"></script>
</body>
</html>
