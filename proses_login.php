
<?php
session_start();
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id_user, nama, username, password, role
     FROM users
     WHERE username = :username
     LIMIT 1'
);

$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Catat aktivitas login
    $log = $pdo->prepare(
        'INSERT INTO activity_logs
         (id_user, aktivitas, modul, data_diproses)
         VALUES (:id_user, :aktivitas, :modul, :data)'
    );

    $log->execute([
        'id_user' => $user['id_user'],
        'aktivitas' => 'Login',
        'modul' => 'Autentikasi',
        'data' => $user['username']
    ]);

    if (in_array($user['role'], ['pengelola', 'admin'], true)) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: penghuni/dashboard.php');
    }
    exit;
}

$_SESSION['login_error'] = 'Username atau password salah.';
header('Location: login.php');
exit;
?>
