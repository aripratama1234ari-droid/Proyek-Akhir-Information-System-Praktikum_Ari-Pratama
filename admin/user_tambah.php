<?php
require_once __DIR__ . '/../includes/auth.php';
wajibRole('pengelola');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/password_vault.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    elseif ($nama === '' || $username === '' || $password === '' || $konfirmasi === '') $error = 'Semua kolom wajib diisi.';
    elseif (strlen($username) < 3) $error = 'Username minimal 3 karakter.';
    elseif (strlen($password) < 8) $error = 'Password minimal 8 karakter.';
    elseif ($password !== $konfirmasi) $error = 'Konfirmasi password tidak cocok.';
    else {
        try {
            $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
            $cek->execute(['username' => $username]);
            if ((int)$cek->fetchColumn() > 0) throw new RuntimeException('Username sudah digunakan. Pilih username lain.');

            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, password_terbuka, role) VALUES (:nama, :username, :password, :password_terbuka, 'admin')");
            $stmt->execute(['nama'=>$nama, 'username'=>$username, 'password'=>password_hash($password, PASSWORD_DEFAULT), 'password_terbuka'=>enkripsiPasswordUntukPengelola($password)]);
            $idUser = (int)$pdo->lastInsertId();

            try {
                $log = $pdo->prepare('INSERT INTO activity_logs (id_user, aktivitas, modul, data_diproses) VALUES (:id,:aktivitas,:modul,:data)');
                $log->execute(['id'=>$_SESSION['id_user'], 'aktivitas'=>'Menambahkan akun admin', 'modul'=>'Manajemen Pengguna', 'data'=>$username]);
            } catch (PDOException $ignored) {}

            header('Location: users.php?pesan=tambah'); exit;
        } catch (RuntimeException $e) { $error = $e->getMessage(); }
          catch (PDOException $e) { $error = 'Akun admin gagal dibuat. Pastikan struktur tabel users sesuai.'; }
    }
}
function eUserForm($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tambah Admin | KosSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="../assets/css/app.css" rel="stylesheet"></head>
<body><div class="app-shell d-flex"><?php include __DIR__.'/../includes/admin_sidebar.php'; ?><main class="app-main"><header class="topbar"><div><div class="topbar-kicker">Administrasi sistem</div><div class="topbar-title">Tambah akun admin</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?=eUserForm($_SESSION['nama']??'Pengelola')?></div><div class="user-role">Pengelola utama</div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right"></i></a></div></header>
<section class="page-heading"><div><div class="eyebrow">Manajemen pengguna</div><h1>Tambah Admin</h1><p>Buat akun admin yang diberi tugas membantu pengelolaan website kos.</p></div><a href="users.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a></section>
<?php if($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?=eUserForm($error)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?=eUserForm($_SESSION['csrf_token'])?>"><div class="row g-3"><div class="col-lg-8"><section class="panel"><h2 class="panel-title">Informasi akun</h2><div class="panel-subtitle mb-4">Akun ini akan memiliki hak akses operasional, bukan hak mengelola akun admin.</div><div class="row g-3"><div class="col-12"><label class="form-label">Nama lengkap</label><input class="form-control" name="nama" value="<?=eUserForm($_POST['nama']??'')?>" required></div><div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" value="<?=eUserForm($_POST['username']??'')?>" minlength="3" required autocomplete="username"></div><div class="col-md-6"><label class="form-label">Peran</label><input class="form-control" value="Admin" disabled></div><div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password" minlength="8" required autocomplete="new-password"></div><div class="col-md-6"><label class="form-label">Konfirmasi password</label><input type="password" class="form-control" name="konfirmasi_password" minlength="8" required autocomplete="new-password"></div></div></section></div><div class="col-lg-4"><section class="panel"><span class="stat-icon icon-blue mb-3"><i class="bi bi-person-badge"></i></span><h2 class="panel-title">Hak akses Admin</h2><ul class="text-secondary small ps-3"><li>Mengelola kamar</li><li>Mengelola penghuni</li><li>Mengelola pembayaran</li><li>Menangani keluhan</li></ul><div class="alert alert-warning small">Admin tidak dapat membuat atau menghapus akun admin. Fitur tersebut hanya tersedia untuk pengelola utama/pemilik.</div><button class="btn btn-primary w-100"><i class="bi bi-person-plus me-2"></i>Buat Akun Admin</button></section></div></div></form>
<footer class="app-footer">&copy; <?=date('Y')?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer></main></div></body></html>
