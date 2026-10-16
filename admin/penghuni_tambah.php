<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/password_vault.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$error = '';


$kamarStmt = $pdo->query("SELECT k.id_kamar, k.nomor_kamar, k.harga_sewa, k.status_kamar,
    (SELECT COUNT(*) FROM penghuni p WHERE p.id_kamar = k.id_kamar AND p.status_penghuni = 'aktif') AS jumlah_penghuni
    FROM kamar k ORDER BY k.nomor_kamar ASC");
$semuaKamar = $kamarStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nomor_identitas_mahasiswa'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $tanggal = trim($_POST['tanggal_mulai_tinggal'] ?? '');
    $idKamar = filter_input(INPUT_POST, 'id_kamar', FILTER_VALIDATE_INT);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    elseif ($nama === '' || $nim === '' || $telepon === '' || $tanggal === '' || !$idKamar || $username === '' || $password === '') $error = 'Semua kolom wajib diisi.';
    elseif (strlen($password) < 8) $error = 'Password akun minimal 8 karakter.';
    else {
        try {
            $pdo->beginTransaction();

            $cekUser = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
            $cekUser->execute(['username' => $username]);
            if ((int)$cekUser->fetchColumn() > 0) throw new RuntimeException('Username sudah digunakan. Pilih username lain.');

            $cekKamar = $pdo->prepare('SELECT id_kamar FROM kamar WHERE id_kamar = :id FOR UPDATE');
            $cekKamar->execute(['id' => $idKamar]);
            if (!$cekKamar->fetch()) throw new RuntimeException('Kamar yang dipilih tidak ditemukan.');

            
            $buatUser = $pdo->prepare("INSERT INTO users (nama, username, password, password_terbuka, role) VALUES (:nama, :username, :password, :password_terbuka, 'penghuni')");
            $buatUser->execute([
                'nama' => $nama,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'password_terbuka' => enkripsiPasswordUntukPengelola($password)
            ]);
            $idUser = (int)$pdo->lastInsertId();

            $buatPenghuni = $pdo->prepare("INSERT INTO penghuni
                (id_user, id_kamar, nama, nomor_identitas_mahasiswa, no_telepon, tanggal_mulai_tinggal, status_penghuni)
                VALUES (:id_user, :id_kamar, :nama, :nim, :telepon, :tanggal, 'nonaktif')");
            $buatPenghuni->execute([
                'id_user' => $idUser,
                'id_kamar' => $idKamar,
                'nama' => $nama,
                'nim' => $nim,
                'telepon' => $telepon,
                'tanggal' => $tanggal
            ]);

            
            $sinkron = $pdo->prepare("UPDATE kamar SET status_kamar = CASE
                WHEN EXISTS (SELECT 1 FROM penghuni WHERE id_kamar = :id AND status_penghuni = 'aktif') THEN 'terisi'
                ELSE 'tersedia' END
                WHERE id_kamar = :id_update");
            $sinkron->execute(['id' => $idKamar, 'id_update' => $idKamar]);

            $pdo->commit();
            require_once __DIR__ . '/../includes/pembayaran_helper.php';
            pastikanTagihanBulanIni($pdo);
            header('Location: penghuni.php?pesan=tambah'); exit;
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack(); $error = $e->getMessage();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack(); $error = 'Data gagal disimpan. Pastikan data tidak duplikat dan struktur database sesuai.';
        }
    }
}
function eTambah($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Penghuni | KosSpace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/app.css" rel="stylesheet">
</head>
<body><div class="app-shell d-flex">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="app-main"><header class="topbar"><div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Tambah data penghuni</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?= eTambah($_SESSION['nama'] ?? 'Pengelola') ?></div><div class="user-role"><?= isPengelolaUtama() ? 'Pengelola utama' : 'Admin' ?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right"></i></a></div></header>
<section class="page-heading"><div><div class="eyebrow">Data penghuni</div><h1>Tambah Penghuni</h1><p>Tambahkan penghuni baru dan tentukan kamar yang akan ditempati.</p></div><a href="penghuni.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a></section>
<?php if($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= eTambah($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= eTambah($_SESSION['csrf_token']) ?>"><div class="row g-3">
<div class="col-lg-7"><div class="panel"><h2 class="panel-title">Informasi penghuni</h2><div class="panel-subtitle mb-4">Satu kamar dapat ditempati beberapa mahasiswa.</div><div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama lengkap</label><input class="form-control" name="nama" value="<?= eTambah($_POST['nama'] ?? '') ?>" required></div>
<div class="col-md-6"><label class="form-label">NIM / NIK</label><input class="form-control" name="nomor_identitas_mahasiswa" value="<?= eTambah($_POST['nomor_identitas_mahasiswa'] ?? '') ?>" required></div>
<div class="col-md-6"><label class="form-label">Nomor telepon</label><input class="form-control" name="no_telepon" value="<?= eTambah($_POST['no_telepon'] ?? '') ?>" required></div>
<div class="col-md-6"><label class="form-label">Tanggal mulai tinggal</label><input type="date" class="form-control" name="tanggal_mulai_tinggal" value="<?= eTambah($_POST['tanggal_mulai_tinggal'] ?? date('Y-m-d')) ?>" required></div>
<div class="col-12"><label class="form-label">Kamar</label><select class="form-select" name="id_kamar" required><option value="">Pilih kamar</option><?php foreach($semuaKamar as $k): $jumlah=(int)$k['jumlah_penghuni']; ?><option value="<?= (int)$k['id_kamar'] ?>" <?= (string)($_POST['id_kamar'] ?? '') === (string)$k['id_kamar'] ? 'selected' : '' ?>>Kamar <?= eTambah($k['nomor_kamar']) ?> · <?= $jumlah > 0 ? $jumlah . ' penghuni aktif' : 'Belum ada penghuni' ?> · Rp <?= number_format((float)$k['harga_sewa'],0,',','.') ?>/bulan</option><?php endforeach; ?></select><div class="form-hint">Kamar yang sudah terisi tetap dapat dipilih untuk penghuni tambahan.</div><?php if(!$semuaKamar): ?><div class="form-hint text-danger">Belum ada kamar. Tambahkan kamar terlebih dahulu.</div><?php endif; ?></div>
</div></div></div>
//
<div class="col-lg-5"><div class="panel"><h2 class="panel-title">Akun penghuni</h2><div class="panel-subtitle mb-4">Akun ini digunakan penghuni untuk login.</div><div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" value="<?= eTambah($_POST['username'] ?? '') ?>" required autocomplete="username"><div class="form-hint">Harus unik dan digunakan saat login.</div></div><div class="mb-4"><label class="form-label">Password awal</label><input type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password"><div class="form-hint">Minimal 8 karakter.</div></div><div class="alert alert-info py-2 small"><i class="bi bi-info-circle me-1"></i>Contoh: Kamar A01 dapat ditempati Ari, Budi, dan Citra secara bersamaan.</div><button class="btn btn-primary w-100" type="submit" <?= !$semuaKamar ? 'disabled' : '' ?>><i class="bi bi-person-plus me-2"></i>Simpan Penghuni</button></div></div>
</div></form><footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer></main></div>
</body></html>
