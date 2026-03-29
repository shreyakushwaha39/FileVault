<?php
// We start the session here so we don't have to write it on every page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileVault — Secure Glass Sharing</title>
    
    <!-- Google Fonts: Syne for Headings, DM Sans for Body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Link to your Modern Glassmorphism CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php if(isset($_SESSION['user_id'])): ?>
<!-- This navigation bar only shows if the user is logged in -->
<nav style="padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div style="font-family: 'Syne'; font-size: 1.5rem; font-weight: 800; color: #10b981;">
        ✦ FileVault
    </div>
    <div style="display: flex; gap: 25px; align-items: center;">
        <a href="index.php" style="color: white; text-decoration: none; font-size: 0.9rem;">Dashboard</a>
        <a href="upload.php" style="color: white; text-decoration: none; font-size: 0.9rem;">Upload</a>
        <a href="logout.php" class="btn-primary" style="padding: 8px 20px; font-size: 0.8rem;">Logout</a>
    </div>
</nav>
<?php endif; ?>