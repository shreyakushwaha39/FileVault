<?php
// ============================================
// config.php — Database connection settings
// Change DB_USER and DB_PASS to your values
// ============================================

define('DB_HOST', 'sql208.infinityfree.com');
define('DB_USER', 'if0_41503743');       // change this
define('DB_PASS', 'SHreya2006');           // change this
define('DB_NAME', 'if0_41503743_fileshare');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50 MB
define('ENCRYPTION_KEY', 'MySecretKey1234!'); // change in production!
define('BASE_URL', 'https://filevault.great-site.net/'); // change to your URL

// Allowed file types
define('ALLOWED_EXTENSIONS', ['jpg','jpeg','png','gif','pdf','txt','docx','xlsx','zip','mp3','mp4']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg', 'image/png', 'image/gif',
    'application/pdf', 'text/plain',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/zip', 'audio/mpeg', 'video/mp4'
]);

// Connect to database using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Helper: sanitize output to prevent XSS
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Helper: format file size nicely
function formatSize($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

// Helper: get file icon based on type
function getFileIcon($type) {
    if (str_contains($type, 'image')) return '🖼️';
    if (str_contains($type, 'pdf'))   return '📄';
    if (str_contains($type, 'video')) return '🎬';
    if (str_contains($type, 'audio')) return '🎵';
    if (str_contains($type, 'zip'))   return '📦';
    if (str_contains($type, 'word') || str_contains($type, 'document')) return '📝';
    if (str_contains($type, 'sheet') || str_contains($type, 'excel'))  return '📊';
    return '📁';
}

// CSRF token generation
function generateCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Encrypt file content using XOR + base64 (simple, explainable for students)
function encryptFile($data) {
    $key = ENCRYPTION_KEY;
    $keyLen = strlen($key);
    $encrypted = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $encrypted .= $data[$i] ^ $key[$i % $keyLen];
    }
    return base64_encode($encrypted);
}

// Decrypt file content
function decryptFile($data) {
    $key = ENCRYPTION_KEY;
    $keyLen = strlen($key);
    $decoded = base64_decode($data);
    $decrypted = '';
    for ($i = 0; $i < strlen($decoded); $i++) {
        $decrypted .= $decoded[$i] ^ $key[$i % $keyLen];
    }
    return $decrypted;
}
?>
