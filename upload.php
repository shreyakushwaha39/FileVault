<?php
require_once 'config.php';
requireLogin();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed.';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'No file selected or upload error occurred.';
    } else {
        $file      = $_FILES['file'];
        $orig_name = basename($file['name']);
        $size      = $file['size'];
        $tmp       = $file['tmp_name'];

        // Validate size
        if ($size > MAX_FILE_SIZE) {
            $error = 'File too large. Maximum size is 50MB.';
        } else {
            // Validate extension
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                $error = 'File type not allowed. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS);
            } else {
                // Validate MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $tmp);
                finfo_close($finfo);

                if (!in_array($mime, ALLOWED_MIME_TYPES)) {
                    $error = 'Invalid file type detected.';
                } else {
                    // All checks passed — generate a random stored filename
                    $stored_name = bin2hex(random_bytes(16)) . '.' . $ext;
                    $target      = UPLOAD_DIR . $stored_name;
                    $encrypt     = isset($_POST['encrypt']) ? 1 : 0;
                    $permission  = in_array($_POST['permission'], ['public', 'private']) ? $_POST['permission'] : 'public';
                    $share_token = bin2hex(random_bytes(32)); // unique shareable link token

                    // Read file content
                    $content = file_get_contents($tmp);

                    if ($encrypt) {
                        // Encrypt and save
                        file_put_contents($target, encryptFile($content));
                    } else {
                        move_uploaded_file($tmp, $target);
                    }

                    // Save to database
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("
                        INSERT INTO files (user_id, original_name, stored_name, file_size, file_type, share_token, is_encrypted, permission)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("isssisss", $user_id, $orig_name, $stored_name, $size, $mime, $share_token, $encrypt, $permission);

                    if ($stmt->execute()) {
                        header("Location: dashboard.php?msg=uploaded");
                        exit;
                    } else {
                        $error = 'Database error. Please try again.';
                    }
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
<title>Upload — FileVault</title>
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
    <a href="dashboard.php" class="nav-item">
      <span class="nav-icon">🗂️</span> My Files
    </a>
    <a href="upload.php" class="nav-item active">
      <span class="nav-icon">⬆️</span> Upload
    </a>
    <a href="logs.php" class="nav-item">
      <span class="nav-icon">📊</span> Activity
    </a>
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
      <h1 class="page-title">Upload File</h1>
      <p class="page-sub">Max 50MB · Supported: images, pdf, docx, xlsx, zip, mp3, mp4</p>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="upload-card">
    <form method="POST" enctype="multipart/form-data" id="upload-form">
      <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

      <!-- Drag & drop zone -->
      <div class="drop-zone" id="drop-zone">
        <div class="drop-icon">☁️</div>
        <p class="drop-title">Drag & drop your file here</p>
        <p class="drop-sub">or click to browse</p>
        <input type="file" name="file" id="file-input" class="file-input-hidden" required
               onchange="handleFileSelect(this)">
        <div id="file-preview" class="file-preview hidden"></div>
      </div>

      <!-- Options -->
      <div class="upload-options">
        <div class="option-group">
          <label class="option-label">Permission</label>
          <div class="radio-group">
            <label class="radio-opt">
              <input type="radio" name="permission" value="public" checked>
              <span>🌐 Public — anyone with link can download</span>
            </label>
            <label class="radio-opt">
              <input type="radio" name="permission" value="private">
              <span>🔐 Private — only you can download</span>
            </label>
          </div>
        </div>

        <div class="option-group">
          <label class="toggle-label">
            <input type="checkbox" name="encrypt" id="encrypt-toggle">
            <span class="toggle-switch"></span>
            <span class="toggle-text">
              <strong>Encrypt this file</strong>
              <small>File content will be XOR-encrypted before storage</small>
            </span>
          </label>
        </div>
      </div>

      <button type="submit" class="btn-primary btn-full btn-upload" id="upload-btn">
        ⬆️ Upload File
      </button>
    </form>
  </div>

  <!-- How encryption works (great for presentation!) -->
  <div class="info-card">
    <h3>How encryption works 🔒</h3>
    <p>When you enable encryption, your file is encrypted using <strong>XOR cipher</strong> with a secret key before being stored on the server. When you download it, it's automatically decrypted. This means even if someone gets access to the raw file on disk, they can't read it without the key.</p>
    <p style="margin-top:8px;color:var(--text-muted);font-size:0.85rem;">Note: For real production apps, AES-256 is the standard. XOR is used here for educational purposes as it's easy to understand and explain.</p>
  </div>
</main>

<script src="assets/page-transition.js"></script>
<script>
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('file-input');
const preview  = document.getElementById('file-preview');

// Click to open file dialog
dropZone.addEventListener('click', (e) => {
    if (e.target !== fileInput) fileInput.click();
});

// Drag over styling
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
});
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));

// Drop
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const dt = e.dataTransfer;
    if (dt.files.length) {
        fileInput.files = dt.files;
        showPreview(dt.files[0]);
    }
});

function handleFileSelect(input) {
    if (input.files.length) showPreview(input.files[0]);
}

function formatBytes(b) {
    if (b > 1048576) return (b/1048576).toFixed(1) + ' MB';
    if (b > 1024)    return (b/1024).toFixed(1) + ' KB';
    return b + ' B';
}

function showPreview(file) {
    preview.innerHTML = `
        <div class="preview-info">
            <span class="preview-name">📄 ${file.name}</span>
            <span class="preview-size">${formatBytes(file.size)}</span>
        </div>`;
    preview.classList.remove('hidden');
}

// Show progress on submit
document.getElementById('upload-form').addEventListener('submit', () => {
    const btn = document.getElementById('upload-btn');
    btn.textContent = 'Uploading...';
    btn.disabled = true;
});
</script>
</body>
</html>
