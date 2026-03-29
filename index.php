<?php 
session_start();
if(!isset($_SESSION['user_id'])) header("Location: register.php");
include 'includes/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>FileVault</title>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">✦ FileVault</div>
        <nav>
            <a href="upload.php" class="btn-primary">+ Upload</a>
            <a href="logout.php" style="color:white; margin-left:20px;">Logout</a>
        </nav>
    </div>

    <h1>Welcome, <?php echo $_SESSION['user_name']; ?></h1>
    
    <div class="files-grid">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        while($f = $stmt->fetch()): ?>
            <div class="file-card glass-panel">
                <div style="font-size: 40px; margin-bottom:10px;">📄</div>
                <div style="font-weight:bold;"><?php echo $f['file_name']; ?></div>
                <div style="color:#94a3b8; font-size:12px; margin:10px 0;">
                    Downloads: <?php echo $f['download_count']; ?>
                </div>
                <a href="download.php?id=<?php echo $f['id']; ?>" class="btn-primary" style="font-size:12px; padding:8px 15px;">Download Securely</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>