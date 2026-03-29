<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get all download logs for this user's files
$stmt = $conn->prepare("
    SELECT 
        d.downloaded_at,
        d.ip_address,
        f.original_name,
        f.file_type,
        f.is_encrypted,
        u.username as downloader
    FROM download_logs d
    JOIN files f ON f.id = d.file_id
    LEFT JOIN users u ON u.id = d.downloader_id
    WHERE f.user_id = ?
    ORDER BY d.downloaded_at DESC
    LIMIT 100
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Per-file download stats
$stats_stmt = $conn->prepare("
    SELECT f.original_name, f.file_type, COUNT(d.id) as downloads
    FROM files f
    LEFT JOIN download_logs d ON d.file_id = f.id
    WHERE f.user_id = ?
    GROUP BY f.id
    ORDER BY downloads DESC
    LIMIT 5
");
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$top_files = $stats_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$max_dl = $top_files[0]['downloads'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activity — FileVault</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="app-page">

<aside class="sidebar">
  <div class="sidebar-logo">
    <span class="logo-mark">⬡</span>
    <span class="logo-text">FileVault</span>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">🗂️</span> My Files</a>
    <a href="upload.php" class="nav-item"><span class="nav-icon">⬆️</span> Upload</a>
    <a href="logs.php" class="nav-item active"><span class="nav-icon">📊</span> Activity</a>
  </nav>
  <div class="sidebar-user">
    <div class="user-avatar" style="background:<?= e($_SESSION['avatar_color']) ?>">
      <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
    </div>
    <div class="user-info">
      <span class="user-name"><?= e($_SESSION['username']) ?></span>
      <a href="logout.php" class="user-logout">Log out</a>
    </div>
  </div>
</aside>

<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">Download Activity</h1>
      <p class="page-sub">Track who downloaded your files</p>
    </div>
  </div>

  <!-- Top files chart -->
  <?php if (!empty($top_files)): ?>
  <div class="chart-card">
    <h2 class="section-title">Most Downloaded Files</h2>
    <div class="bar-chart">
      <?php foreach ($top_files as $tf): ?>
        <div class="bar-row">
          <div class="bar-label" title="<?= e($tf['original_name']) ?>">
            <?= getFileIcon($tf['file_type']) ?> <?= e(substr($tf['original_name'], 0, 30)) ?>
          </div>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?= $max_dl > 0 ? round(($tf['downloads']/$max_dl)*100) : 0 ?>%"></div>
          </div>
          <div class="bar-count"><?= $tf['downloads'] ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Download log table -->
  <div class="logs-card">
    <h2 class="section-title">Download Log</h2>

    <?php if (empty($logs)): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <p>No downloads recorded yet.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="log-table">
          <thead>
            <tr>
              <th>File</th>
              <th>Downloaded By</th>
              <th>IP Address</th>
              <th>Date & Time</th>
              <th>Encrypted</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td class="td-file">
                  <?= getFileIcon($log['file_type']) ?>
                  <?= e(substr($log['original_name'], 0, 30)) ?>
                </td>
                <td><?= $log['downloader'] ? e($log['downloader']) : '<span class="guest-label">Guest</span>' ?></td>
                <td class="td-ip"><?= e($log['ip_address']) ?></td>
                <td><?= date('d M Y, H:i', strtotime($log['downloaded_at'])) ?></td>
                <td><?= $log['is_encrypted'] ? '🔒 Yes' : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>
<script src="assets/page-transition.js"></script>
</body>
</html>
