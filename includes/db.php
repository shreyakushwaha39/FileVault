<?php
$host = 'sql208.infinityfree.com'; // <--- CHECK THIS HOST!
$db   = 'if0_41503743_fileshare'; // <--- CHECK THIS NAME! 
$user = 'if0_41503743'; 
$pass = 'SHreya2006'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>