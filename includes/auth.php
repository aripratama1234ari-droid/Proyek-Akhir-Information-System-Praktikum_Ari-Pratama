<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function wajibLogin(): void
{
    if (!isset($_SESSION['id_user'])) {
        header('Location: ../login.php');
        exit;
    }
}

function wajibRole(string $role): void
{
    wajibLogin();

    if (($_SESSION['role'] ?? '') !== $role) {
        if (in_array($_SESSION['role'] ?? '', ['pengelola', 'admin'], true)) {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../penghuni/dashboard.php');
        }
        exit;
    }
}


function wajibAdminArea(): void
{
    wajibLogin();

    if (!in_array($_SESSION['role'] ?? '', ['pengelola', 'admin'], true)) {
        header('Location: ../penghuni/dashboard.php');
        exit;
    }
}

function isPengelolaUtama(): bool
{
    return ($_SESSION['role'] ?? '') === 'pengelola';
}
?>
