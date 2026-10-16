<?php
require_once __DIR__ . '/../includes/auth.php';
wajibRole('penghuni');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/password_vault.php';

$success = '';
$error = '';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi_akun'] ?? '') !== 'ganti_username') {
    $token = $_POST['csrf_token'] ?? '';
    $passwordLama = $_POST['password_lama'] ?? '';
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman dan coba lagi.';
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
            $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = :password, password_terbuka = :password_terbuka WHERE id_user = :id');
            $stmt->execute(['password' => $hashBaru, 'password_terbuka' => enkripsiPasswordUntukPengelola($passwordBaru), 'id' => $_SESSION['id_user']]);

            try {
                $log = $pdo->prepare('INSERT INTO activity_logs (id_user, aktivitas, modul, data_diproses) VALUES (:id, :aktivitas, :modul, :data)');
                $log->execute([
                    'id' => $_SESSION['id_user'],
                    'aktivitas' => 'Mengubah password',
                    'modul' => 'Pengaturan Akun',
                    'data' => 'Password akun diperbarui'
                ]);
            } catch (PDOException $e) {
                error_log('Gagal mencatat log perubahan password: ' . $e->getMessage());
            }

            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $success = 'Password berhasil diubah. Gunakan password baru untuk login berikutnya.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Akun | KosSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell d-flex">
    <aside class="sidebar">
        <a class="brand" href="dashboard.php"><span class="brand-mark"><i class="bi bi-house-heart-fill"></i></span><span>KosSpace</span></a>
        <div class="sidebar-label">Menu penghuni</div>
        <nav class="side-nav">
            <a href="dashboard.php"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a>
            <a href="dashboard.php#informasi-pembayaran"><i class="bi bi-wallet2"></i>Ringkasan pembayaran</a>
            <a class="active" href="ganti_password.php"><i class="bi bi-shield-lock"></i>Pengaturan akun</a>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-shield-check me-1"></i> Portal Penghuni<br>Kelola keamanan akunmu di sini.</div>
    </aside>

    <main class="app-main">
        <header class="topbar">
            <div><div class="topbar-kicker">Workspace / Pengaturan akun</div><div class="topbar-title">Keamanan akun</div></div>
            <div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Penghuni', ENT_QUOTES, 'UTF-8') ?></div><div class="user-role">Penghuni kos</div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a></div>
        </header>

        <section class="page-heading"><div><div class="eyebrow">Pengaturan akun</div><h1>Ganti password</h1><p>Perbarui password secara berkala untuk menjaga keamanan akun.</p></div></section>

        <div class="row g-3 mb-3"><div class="col-lg-7 col-xl-8"><section class="panel"><h2 class="panel-title">Ganti username</h2><div class="panel-subtitle mb-4">Username harus unik. Masukkan password saat ini sebagai verifikasi.</div><?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><form method="post" action="ganti_password.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="aksi_akun" value="ganti_username"><div class="mb-3"><label class="form-label">Username baru</label><input type="text" name="username_baru" class="form-control" minlength="4" maxlength="30" pattern="[A-Za-z0-9._-]{4,30}" value="<?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div><div class="mb-3"><label class="form-label">Password saat ini</label><input type="password" name="password_verifikasi" class="form-control" required autocomplete="current-password"></div><button class="btn btn-outline-primary"><i class="bi bi-person-gear me-2"></i>Simpan Username</button></form></section></div></div>
<div class="row g-3">
            <div class="col-lg-7 col-xl-8">
                <section class="panel">
                    <div class="d-flex align-items-center gap-3 mb-4"><span class="stat-icon icon-blue" style="width:48px;height:48px;font-size:20px"><i class="bi bi-shield-lock"></i></span><div><h2 class="panel-title">Perbarui password akun</h2><div class="panel-subtitle">Isi password lama lalu tentukan password baru.</div></div></div>
                    <?php if ($success): ?><div class="alert alert-success d-flex gap-2 align-items-start"><i class="bi bi-check-circle-fill mt-1"></i><div><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div></div><?php endif; ?>
                    <?php if ($error): ?><div class="alert alert-danger d-flex gap-2 align-items-start"><i class="bi bi-exclamation-circle-fill mt-1"></i><div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div></div><?php endif; ?>
                    <form method="POST" action="ganti_password.php" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3"><label for="password_lama" class="form-label">Password lama</label><div class="password-wrap"><input type="password" class="form-control" id="password_lama" name="password_lama" placeholder="Masukkan password saat ini" required autocomplete="current-password"><button class="password-toggle" type="button" data-toggle-password="password_lama" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div></div>
                        <div class="mb-3"><label for="password_baru" class="form-label">Password baru</label><div class="password-wrap"><input type="password" class="form-control" id="password_baru" name="password_baru" placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password"><button class="password-toggle" type="button" data-toggle-password="password_baru" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div><div class="form-hint">Gunakan minimal 8 karakter. Kombinasikan huruf, angka, dan simbol.</div></div>
                        <div class="mb-4"><label for="konfirmasi_password" class="form-label">Konfirmasi password baru</label><div class="password-wrap"><input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password baru" required minlength="8" autocomplete="new-password"><button class="password-toggle" type="button" data-toggle-password="konfirmasi_password" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div></div>
                        <div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-2"></i>Simpan password baru</button><a href="dashboard.php" class="btn btn-outline-secondary">Kembali ke dashboard</a></div>
                    </form>
                </section>
            </div>
            <div class="col-lg-5 col-xl-4">
                <section class="panel h-100">
                    <span class="stat-icon icon-green mb-3" style="width:48px;height:48px;font-size:20px"><i class="bi bi-shield-check"></i></span>
                    <h2 class="panel-title">Tips keamanan</h2><div class="panel-subtitle mb-3">Beberapa hal sederhana untuk melindungi akunmu.</div>
                    <div class="d-flex gap-3 mb-3"><i class="bi bi-check-circle-fill" style="color:#138a63;font-size:17px"></i><div><div class="fw-bold">Gunakan password unik</div><div class="text-secondary small mt-1">Hindari menggunakan password yang sama untuk akun lain.</div></div></div>
                    <div class="d-flex gap-3 mb-3"><i class="bi bi-check-circle-fill" style="color:#138a63;font-size:17px"></i><div><div class="fw-bold">Jangan bagikan password</div><div class="text-secondary small mt-1">Pengelola tidak perlu mengetahui password pribadimu.</div></div></div>
                    <div class="d-flex gap-3"><i class="bi bi-check-circle-fill" style="color:#138a63;font-size:17px"></i><div><div class="fw-bold">Keluar setelah selesai</div><div class="text-secondary small mt-1">Terutama jika kamu menggunakan komputer bersama.</div></div></div>
                </section>
            </div>
        </div>
        <footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
    </main>
</div>
<script>
document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
    button.addEventListener('click', function () {
        const field = document.getElementById(this.dataset.togglePassword);
        const icon = this.querySelector('i');
        const visible = field.type === 'password';
        field.type = visible ? 'text' : 'password';
        icon.className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
        this.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
    });
});
</script>
</body>
</html>
