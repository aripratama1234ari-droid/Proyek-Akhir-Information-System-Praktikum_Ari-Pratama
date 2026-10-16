<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/password_vault.php';

$success = '';
$error = '';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi_akun'] ?? '') === 'ganti_username') {
    $token = $_POST['csrf_token'] ?? '';
    $usernameBaru = trim($_POST['username_baru'] ?? '');
    $passwordVerifikasi = $_POST['password_verifikasi'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{4,30}$/', $usernameBaru)) {
        $error = 'Username harus 4–30 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif ($passwordVerifikasi === '') {
        $error = 'Masukkan password saat ini untuk mengganti username.';
    } else {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id_user = :id LIMIT 1');
        $stmt->execute(['id' => $_SESSION['id_user']]);
        $akun = $stmt->fetch();
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username AND id_user <> :id');
        $cek->execute(['username' => $usernameBaru, 'id' => $_SESSION['id_user']]);
        if (!$akun || !password_verify($passwordVerifikasi, $akun['password'])) {
            $error = 'Password saat ini salah.';
        } elseif ((int)$cek->fetchColumn() > 0) {
            $error = 'Username tersebut sudah digunakan. Pilih username lain.';
        } else {
            $lama = $_SESSION['username'] ?? '';
            $up = $pdo->prepare('UPDATE users SET username = :username WHERE id_user = :id');
            $up->execute(['username' => $usernameBaru, 'id' => $_SESSION['id_user']]);
            $_SESSION['username'] = $usernameBaru;
            try {
                $log = $pdo->prepare('INSERT INTO activity_logs (id_user, aktivitas, modul, data_diproses) VALUES (:id,:aktivitas,:modul,:data)');
                $log->execute(['id'=>$_SESSION['id_user'],'aktivitas'=>'Mengubah username sendiri','modul'=>'Pengaturan Akun','data'=>'Username: '.$lama.' -> '.$usernameBaru]);
            } catch (PDOException $ignored) {}
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $success = 'Username berhasil diubah.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $passwordLama = $_POST['password_lama'] ?? '';
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } elseif ($passwordLama === '' || $passwordBaru === '' || $konfirmasi === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (strlen($passwordBaru) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($passwordBaru !== $konfirmasi) {
        $error = 'Konfirmasi password baru tidak cocok.';
    } elseif ($passwordLama === $passwordBaru) {
        $error = 'Password baru harus berbeda dari password lama.';
    } else {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id_user = :id LIMIT 1');
        $stmt->execute(['id' => $_SESSION['id_user']]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($passwordLama, $user['password'])) {
            $error = 'Password lama yang kamu masukkan salah.';
        } else {
            $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
            $up = $pdo->prepare('UPDATE users SET password = :password, password_terbuka = :password_terbuka WHERE id_user = :id');
            $up->execute(['password' => $hash, 'password_terbuka' => enkripsiPasswordUntukPengelola($passwordBaru), 'id' => $_SESSION['id_user']]);
            try {
                $log = $pdo->prepare('INSERT INTO activity_logs (id_user, aktivitas, modul, data_diproses) VALUES (:id,:aktivitas,:modul,:data)');
                $log->execute(['id'=>$_SESSION['id_user'],'aktivitas'=>'Mengubah password sendiri','modul'=>'Pengaturan Akun','data'=>'Password akun diperbarui']);
            } catch (PDOException $ignored) {}
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $success = 'Password berhasil diubah.';
        }
    }
}
function ePwd($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Ganti Password | KosSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="../assets/css/app.css" rel="stylesheet"></head>
<body><div class="app-shell d-flex"><?php include __DIR__ . '/../includes/admin_sidebar.php'; ?><main class="app-main"><header class="topbar"><div><div class="topbar-kicker">Pengaturan akun</div><div class="topbar-title">Keamanan akun</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?=ePwd($_SESSION['nama']??'Pengguna')?></div><div class="user-role"><?=($_SESSION['role']??'')==='pengelola'?'Pengelola utama':'Admin'?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right"></i></a></div></header>
<section class="page-heading"><div><div class="eyebrow">Pengaturan akun</div><h1>Ganti Password</h1><p>Ubah password akun yang sedang digunakan.</p></div></section>
<div class="row g-3 mb-3"><div class="col-lg-8"><section class="panel"><h2 class="panel-title">Ganti username</h2><div class="panel-subtitle mb-4">Username harus unik. Masukkan password saat ini sebagai verifikasi.</div><?php if($success):?><div class="alert alert-success"><?=ePwd($success)?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=ePwd($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?=ePwd($_SESSION['csrf_token'])?>"><input type="hidden" name="aksi_akun" value="ganti_username"><div class="mb-3"><label class="form-label">Username baru</label><input type="text" name="username_baru" class="form-control" minlength="4" maxlength="30" pattern="[A-Za-z0-9._-]{4,30}" value="<?=ePwd($_SESSION['username']??'')?>" required></div><div class="mb-3"><label class="form-label">Password saat ini</label><input type="password" name="password_verifikasi" class="form-control" required autocomplete="current-password"></div><button class="btn btn-outline-primary"><i class="bi bi-person-gear me-2"></i>Simpan Username</button></form></section></div></div>
<div class="row g-3"><div class="col-lg-8"><section class="panel"><h2 class="panel-title">Perbarui password</h2><div class="panel-subtitle mb-4">Masukkan password lama untuk memverifikasi identitasmu.</div><?php if($success):?><div class="alert alert-success"><?=ePwd($success)?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=ePwd($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?=ePwd($_SESSION['csrf_token'])?>"><div class="mb-3"><label class="form-label">Password lama</label><input type="password" name="password_lama" class="form-control" required autocomplete="current-password"></div><div class="mb-3"><label class="form-label">Password baru</label><input type="password" name="password_baru" class="form-control" minlength="8" required autocomplete="new-password"></div><div class="mb-4"><label class="form-label">Konfirmasi password baru</label><input type="password" name="konfirmasi_password" class="form-control" minlength="8" required autocomplete="new-password"></div><button class="btn btn-primary"><i class="bi bi-check2-circle me-2"></i>Simpan Password</button></form></section></div></div>
<footer class="app-footer">&copy; <?=date('Y')?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer></main></div></body></html>
