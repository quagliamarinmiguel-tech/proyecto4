<?php
// config/crypto.php
function encryptAES($data, $key, $iv) {
    return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv));
}

function decryptAES($encrypted, $key, $iv) {
    return openssl_decrypt(base64_decode($encrypted), 'AES-256-CBC', $key, 0, $iv);
}
?>