<?php

require_once __DIR__ . '/../config/password_vault_key.php';

function enkripsiPasswordUntukPengelola(string $password): string {
    $key = base64_decode(PASSWORD_VAULT_KEY, true);
    if ($key === false || strlen($key) !== 32) {
        throw new RuntimeException('Kunci penyimpanan password tidak valid.');
    }
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($password, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) throw new RuntimeException('Password gagal dienkripsi.');
    return base64_encode($iv . $tag . $cipher);
}

function dekripsiPasswordUntukPengelola(?string $encrypted): ?string {
    if (!$encrypted) return null;
    $key = base64_decode(PASSWORD_VAULT_KEY, true);
    $raw = base64_decode($encrypted, true);
    if ($key === false || strlen($key) !== 32 || $raw === false || strlen($raw) < 29) return null;
    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $cipher = substr($raw, 28);
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? null : $plain;
}
