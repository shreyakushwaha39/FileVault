<?php
require_once 'config.php';

// Get file ID from URL
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$file_id) {
    die("Invalid file.");
}

// Fetch file record
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    die("File not found.");
}

// Permission check
if ($file['permission'] === 'private') {
    // Only the owner can download private files
    if (!isLoggedIn() || $_SESSION['user_id'] !== $file['user_id']) {
        die("Access denied. This file is private.");
    }
}

// Build file path
$file_path = UPLOAD_DIR . $file['stored_name'];

// Security: make sure path is inside uploads dir
$real_path    = realpath($file_path);
$real_uploads = realpath(UPLOAD_DIR);

if (!$real_path || strpos($real_path, $real_uploads) !== 0) {
    die("Security error.");
}

if (!file_exists($real_path)) {
    die("File no longer exists on server.");
}

// Log the download
$downloader_id = isLoggedIn() ? $_SESSION['user_id'] : null;
$ip = $_SERVER['REMOTE_ADDR'];
$log = $conn->prepare("INSERT INTO download_logs (file_id, downloader_id, ip_address) VALUES (?, ?, ?)");
$log->bind_param("iis", $file_id, $downloader_id, $ip);
$log->execute();

// Update download count
$conn->query("UPDATE files SET download_count = download_count + 1 WHERE id = $file_id");

// Read file content
$content = file_get_contents($real_path);

// Decrypt if encrypted
if ($file['is_encrypted']) {
    $content = decryptFile($content);
}

// Send file to browser
header('Content-Type: ' . $file['file_type']);
header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
header('Content-Length: ' . strlen($content));
header('Cache-Control: no-cache');
echo $content;
exit;
?>
