<?php
function encryptFile($source, $dest) {
    $key = openssl_random_pseudo_bytes(32);
    $iv = openssl_random_pseudo_bytes(16);
    $content = file_get_contents($source);
    $encrypted = openssl_encrypt($content, 'aes-256-cbc', $key, 0, $iv);
    file_put_contents($dest, $iv . $encrypted);
    return base64_encode($key);
}

function decryptFile($source, $keyBase64) {
    $key = base64_decode($keyBase64);
    $data = file_get_contents($source);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}
?>