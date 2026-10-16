
<?php
session_start();

if (isset($_SESSION['id_user'])) {
    require_once __DIR__ . '/config/database.php';

    $stmt = $pdo->prepare(
        'INSERT INTO activity_logs
         (id_user, aktivitas, modul, data_diproses)
         VALUES (:id_user, :aktivitas, :modul, :data)'
    );

    try {
        $stmt->execute([
            'id_user' => $_SESSION['id_user'],
            'aktivitas' => 'Logout',
            'modul' => 'Autentikasi',
            'data' => $_SESSION['username'] ?? ''
        ]);
    } catch (PDOException $e) {
        error_log('Gagal mencatat logout: ' . $e->getMessage());
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: login.php');
exit;
?>
