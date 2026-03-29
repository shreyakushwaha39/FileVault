<?php
require_once 'config.php';
requireLogin();

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's files with download counts
$stmt = $conn->prepare("
    SELECT f.*, 
           COUNT(d.id) as total_downloads
    FROM files f
    LEFT JOIN download_logs d ON d.file_id = f.id
    WHERE f.user_id = ?
    GROUP BY f.id
    ORDER BY f.upload_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Stats
$total_files     = count($files);
$total_downloads = array_sum(array_column($files, 'total_downloads'));
$total_size      = array_sum(array_column($files, 'file_size'));

// Delete file action
if (isset($_GET['delete']) && verifyCSRF($_GET['token'] ?? '')) {
    $fid = (int)$_GET['delete'];
    $chk = $conn->prepare("SELECT stored_name FROM files WHERE id = ? AND user_id = ?");
    $chk->bind_param("ii", $fid, $user_id);
    $chk->execute();
    $row = $chk->get_result()->fetch_assoc();

    if ($row) {
        $path = UPLOAD_DIR . $row['stored_name'];
        if (file_exists($path)) unlink($path);
        $del = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $del->bind_param("ii", $fid, $user_id);
        $del->execute();
        header("Location: dashboard.php?msg=deleted");
        exit;
    }
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — FileVault</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="app-page">

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <span class="logo-mark">⬡</span>
    <span class="logo-text">FileVault</span>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item active">
      <span class="nav-icon">🗂️</span> My Files
    </a>
    <a href="upload.php" class="nav-item">
      <span class="nav-icon">⬆️</span> Upload
    </a>
    <a href="logs.php" class="nav-item">
      <span class="nav-icon">📊</span> Activity
    </a>
  </nav>
  <div class="sidebar-user">
    <div class="user-avatar" style="background:<?= e($_SESSION['avatar_color']) ?>">
      <?= strtoupper(substr($username, 0, 1)) ?>
    </div>
    <div class="user-info">
      <span class="user-name"><?= e($username) ?></span>
      <a href="logout.php" class="user-logout">Log out</a>
    </div>
  </div>
</aside>

<!-- Main content -->
<main class="main-content">

  <div class="page-header">
    <div>
      <h1 class="page-title">My Files</h1>
      <p class="page-sub">Manage and share your uploaded files</p>
    </div>
    <a href="upload.php" class="btn-primary">+ Upload File</a>
  </div>

  <?php if ($msg === 'deleted'): ?>
    <div class="alert alert-success">File deleted successfully.</div>
  <?php elseif ($msg === 'uploaded'): ?>
    <div class="alert alert-success">File uploaded successfully! 🎉</div>
  <?php endif; ?>

  <!-- Stats cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">📁</div>
      <div class="stat-value"><?= $total_files ?></div>
      <div class="stat-label">Total Files</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⬇️</div>
      <div class="stat-value"><?= $total_downloads ?></div>
      <div class="stat-label">Downloads</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💾</div>
      <div class="stat-value"><?= formatSize($total_size) ?></div>
      <div class="stat-label">Storage Used</div>
    </div>
  </div>

  <!-- Files table -->
  <div class="files-section">
    <h2 class="section-title">All Files</h2>

    <?php if (empty($files)): ?>
      <div class="empty-state">
        <div class="empty-icon">📂</div>
        <p>No files uploaded yet.</p>
        <a href="upload.php" class="btn-primary">Upload your first file</a>
      </div>
    <?php else: ?>
      <div class="files-grid">
        <?php foreach ($files as $f): ?>
          <div class="file-card">
            <div class="file-card-top">
              <div class="file-type-icon"><?= getFileIcon($f['file_type']) ?></div>
              <div class="file-badges">
                <?php if ($f['is_encrypted']): ?>
                  <span class="badge badge-enc">🔒 Encrypted</span>
                <?php endif; ?>
                <span class="badge badge-perm badge-<?= e($f['permission']) ?>">
                  <?= $f['permission'] === 'public' ? '🌐 Public' : '🔐 Private' ?>
                </span>
              </div>
            </div>

            <div class="file-name" title="<?= e($f['original_name']) ?>">
              <?= e($f['original_name']) ?>
            </div>

            <div class="file-meta">
              <span><?= formatSize($f['file_size']) ?></span>
              <span><?= date('d M Y', strtotime($f['upload_date'])) ?></span>
            </div>

            <div class="file-stats">
              <span class="download-count">⬇️ <?= $f['total_downloads'] ?> downloads</span>
            </div>

            <!-- Share link -->
            <?php if ($f['share_token']): ?>
            <div class="share-row">
              <input type="text" class="share-input" readonly
                     value="<?= BASE_URL ?>/share.php?token=<?= e($f['share_token']) ?>"
                     id="link-<?= $f['id'] ?>">
              <button class="copy-btn" onclick="copyLink('link-<?= $f['id'] ?>')">Copy</button>
            </div>
            <?php endif; ?>

            <div class="file-actions">
              <a href="download.php?id=<?= $f['id'] ?>" class="btn-sm btn-download">⬇ Download</a>
              <a href="dashboard.php?delete=<?= $f['id'] ?>&token=<?= generateCSRF() ?>"
                 class="btn-sm btn-delete"
                 onclick="return confirm('Delete this file?')">🗑 Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</main>

<script src="assets/page-transition.js"></script>
<script>
function copyLink(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        // Show copied feedback
        const btn = input.nextElementSibling;
        btn.textContent = 'Copied!';
        btn.style.background = '#10b981';
        setTimeout(() => { btn.textContent = 'Copy'; btn.style.background = ''; }, 2000);
    });
}
</script>
</body>
</html>
