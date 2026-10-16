<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$nomorKamar = '';
$hargaSewa = '';
$fasilitas = '';
$statusKamar = 'tersedia';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $nomorKamar = trim($_POST['nomor_kamar'] ?? '');
    $hargaSewa = trim($_POST['harga_sewa'] ?? '');
    $fasilitas = trim($_POST['fasilitas'] ?? '');
    $statusKamar = trim($_POST['status_kamar'] ?? 'tersedia');

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    }
    if ($nomorKamar === '') {
        $errors[] = 'Nomor kamar wajib diisi.';
    } elseif (mb_strlen($nomorKamar) > 30) {
        $errors[] = 'Nomor kamar maksimal 30 karakter.';
    }
    if ($hargaSewa === '' || !is_numeric($hargaSewa) || (float) $hargaSewa < 0) {
        $errors[] = 'Harga sewa harus berupa angka nol atau lebih.';
    }
    if (mb_strlen($fasilitas) > 1000) {
        $errors[] = 'Fasilitas terlalu panjang (maksimal 1000 karakter).';
    }
    if (!in_array($statusKamar, ['tersedia', 'terisi'], true)) {
        $errors[] = 'Status kamar tidak valid.';
    }

    if (!$errors) {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM kamar WHERE nomor_kamar = :nomor_kamar');
        $cek->execute(['nomor_kamar' => $nomorKamar]);
        if ((int) $cek->fetchColumn() > 0) {
            $errors[] = 'Nomor kamar tersebut sudah digunakan. Gunakan nomor yang berbeda.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO kamar (nomor_kamar, harga_sewa, fasilitas, status_kamar) VALUES (:nomor_kamar, :harga_sewa, :fasilitas, :status_kamar)');
        $stmt->execute([
            'nomor_kamar' => $nomorKamar,
            'harga_sewa' => (float) $hargaSewa,
            'fasilitas' => $fasilitas,
            'status_kamar' => $statusKamar,
        ]);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: kamar.php?pesan=tambah');
        exit;
    }
}
function eTambah($nilai): string { return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kamar | KosSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell d-flex">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="app-main">
        <header class="topbar">
            <div><div class="topbar-kicker">Workspace / Data kamar</div><div class="topbar-title">Tambah kamar baru</div></div>
            <div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?= eTambah($_SESSION['nama'] ?? 'Pengelola') ?></div><div class="user-role"><?= isPengelolaUtama() ? 'Pengelola utama' : 'Admin' ?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a></div>
        </header>
        <section class="page-heading"><div><div class="eyebrow">Data master / Kamar</div><h1>Tambah Kamar</h1><p>Isi informasi kamar yang akan didaftarkan ke sistem.</p></div><a href="kamar.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a></section>
        <?php if ($errors): ?><div class="alert alert-danger"><strong>Data belum dapat disimpan:</strong><ul class="mb-0 mt-2"><?php foreach ($errors as $error): ?><li><?= eTambah($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <section class="panel" style="max-width:850px">
            <div class="d-flex align-items-center gap-3 mb-4"><span class="stat-icon icon-blue"><i class="bi bi-door-open"></i></span><div><h2 class="panel-title">Informasi kamar</h2><div class="panel-subtitle">Kolom bertanda * wajib diisi.</div></div></div>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= eTambah($_SESSION['csrf_token']) ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="nomor_kamar">Nomor kamar *</label><input class="form-control" id="nomor_kamar" name="nomor_kamar" maxlength="30" required value="<?= eTambah($nomorKamar) ?>" placeholder="Contoh: A01"><div class="form-hint">Gunakan nomor kamar yang unik.</div></div>
                    <div class="col-md-6"><label class="form-label" for="harga_sewa">Harga sewa per bulan (Rp) *</label><input class="form-control" type="number" id="harga_sewa" name="harga_sewa" min="0" step="1000" required value="<?= eTambah($hargaSewa) ?>" placeholder="Contoh: 500000"></div>
                    <div class="col-12"><label class="form-label" for="fasilitas">Fasilitas kamar</label><textarea class="form-control" id="fasilitas" name="fasilitas" rows="3" maxlength="1000" placeholder="Contoh: Kasur, lemari, meja, kipas angin"><?= eTambah($fasilitas) ?></textarea><div class="form-hint">Tuliskan fasilitas yang tersedia, pisahkan dengan koma.</div></div>
                    <div class="col-md-6"><label class="form-label" for="status_kamar">Status kamar *</label><select class="form-select" id="status_kamar" name="status_kamar" required><option value="tersedia" <?= $statusKamar === 'tersedia' ? 'selected' : '' ?>>Tersedia</option><option value="terisi" <?= $statusKamar === 'terisi' ? 'selected' : '' ?>>Terisi</option></select><div class="form-hint">Jika kamar sudah ditempati, pilih status terisi.</div></div>
                </div>
                <div class="divider"></div>
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2"><a href="kamar.php" class="btn btn-outline-secondary">Batal</a><button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-2"></i>Simpan Kamar</button></div>
            </form>
        </section>
        <footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
    </main>
</div>
</body>
</html>
