<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = '';
$jenisPesan = 'success';
if (($_GET['pesan'] ?? '') === 'tambah') { $pesan = 'Data kamar berhasil ditambahkan.'; }
elseif (($_GET['pesan'] ?? '') === 'ubah') { $pesan = 'Data kamar berhasil diperbarui.'; }
elseif (($_GET['pesan'] ?? '') === 'tidak_ditemukan') { $pesan = 'Data kamar tidak ditemukan.'; $jenisPesan = 'warning'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $token = $_POST['csrf_token'] ?? '';
    $idKamar = filter_input(INPUT_POST, 'id_kamar', FILTER_VALIDATE_INT);

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
        $jenisPesan = 'danger';
    } elseif (!$idKamar || $idKamar < 1) {
        $pesan = 'Data kamar yang dipilih tidak valid.';
        $jenisPesan = 'danger';
    } else {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM penghuni WHERE id_kamar = :id_kamar');
        $cek->execute(['id_kamar' => $idKamar]);
        $jumlahPenghuniTerkait = (int) $cek->fetchColumn();

        if ($jumlahPenghuniTerkait > 0) {
            $pesan = 'Kamar tidak dapat dihapus karena masih memiliki data penghuni terkait. Ubah status atau data penghuni terlebih dahulu.';
            $jenisPesan = 'warning';
        } else {
            $hapus = $pdo->prepare('DELETE FROM kamar WHERE id_kamar = :id_kamar');
            $hapus->execute(['id_kamar' => $idKamar]);
            if ($hapus->rowCount() > 0) {
                $pesan = 'Data kamar berhasil dihapus.';
            } else {
                $pesan = 'Data kamar tidak ditemukan atau sudah dihapus.';
                $jenisPesan = 'warning';
            
            }
        }
    }
}

$kataKunci = trim($_GET['cari'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$sql = 'SELECT k.id_kamar, k.nomor_kamar, k.harga_sewa, k.fasilitas, k.status_kamar,
        (SELECT COUNT(*) FROM penghuni p WHERE p.id_kamar = k.id_kamar AND p.status_penghuni = \'aktif\') AS jumlah_penghuni
        FROM kamar k WHERE 1=1';
$params = [];

if ($kataKunci !== '') {
    $sql .= ' AND (k.nomor_kamar LIKE :cari_nomor OR k.fasilitas LIKE :cari_fasilitas)';
    $likeCari = '%' . $kataKunci . '%';
    $params['cari_nomor'] = $likeCari;
    $params['cari_fasilitas'] = $likeCari;
}
if (in_array($statusFilter, ['tersedia', 'terisi'], true)) {
    $sql .= ' AND k.status_kamar = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY k.nomor_kamar ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarKamar = $stmt->fetchAll();

$totalKamar = (int) $pdo->query('SELECT COUNT(*) FROM kamar')->fetchColumn();
$totalTersedia = (int) $pdo->query("SELECT COUNT(*) FROM kamar WHERE status_kamar = 'tersedia'")->fetchColumn();
$totalTerisi = (int) $pdo->query("SELECT COUNT(*) FROM kamar WHERE status_kamar = 'terisi'")->fetchColumn();
function rupiahKamar(float $angka): string { return 'Rp ' . number_format($angka, 0, ',', '.'); }
function eKamar($nilai): string { return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Kamar | KosSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell d-flex">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="app-main">
        <header class="topbar">
            <div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Manajemen data kamar</div></div>
            <div class="user-chip">
                <div class="avatar"><i class="bi bi-person-fill"></i></div>
                <div class="d-none d-sm-block"><div class="user-name"><?= eKamar($_SESSION['nama'] ?? 'Pengelola') ?></div><div class="user-role"><?= isPengelolaUtama() ? 'Pengelola utama' : 'Admin' ?></div></div>
                <a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a>
            </div>
        </header>

        <section class="page-heading">
            <div><div class="eyebrow">Data master</div><h1>Data Kamar</h1><p>Kelola informasi kamar, harga sewa, fasilitas, dan status ketersediaan.</p></div>
            <a href="kamar_tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Kamar</a>
        </section>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-<?= eKamar($jenisPesan) ?> alert-dismissible fade show" role="alert">
                <?= eKamar($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <section class="row g-3 mb-4">
            <div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Total kamar</span><span class="stat-icon icon-blue"><i class="bi bi-door-closed"></i></span></div><div class="stat-value"><?= number_format($totalKamar) ?></div><div class="stat-foot">Seluruh kamar terdaftar</div></div></div>
            <div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Tersedia</span><span class="stat-icon icon-green"><i class="bi bi-check-circle"></i></span></div><div class="stat-value"><?= number_format($totalTersedia) ?></div><div class="stat-foot">Siap ditempati</div></div></div>
            <div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Terisi</span><span class="stat-icon icon-orange"><i class="bi bi-house-check"></i></span></div><div class="stat-value"><?= number_format($totalTerisi) ?></div><div class="stat-foot">Sedang ditempati</div></div></div>
        </section>

        <section class="panel">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div><h2 class="panel-title">Daftar kamar</h2><div class="panel-subtitle">Data kamar yang tersimpan di database.</div></div>
                <form method="get" class="d-flex flex-column flex-sm-row gap-2" role="search">
                    <input type="search" name="cari" class="form-control" placeholder="Cari nomor atau fasilitas..." value="<?= eKamar($kataKunci) ?>" aria-label="Cari kamar">
                    <select name="status" class="form-select" aria-label="Filter status" style="min-width:135px">
                        <option value="">Semua status</option>
                        <option value="tersedia" <?= $statusFilter === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="terisi" <?= $statusFilter === 'terisi' ? 'selected' : '' ?>>Terisi</option>
                    </select>
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if ($kataKunci !== '' || $statusFilter !== ''): ?><a href="kamar.php" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th style="width:60px">No.</th><th>Nomor kamar</th><th>Harga sewa / bulan</th><th>Fasilitas</th><th>Penghuni</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php if (!$daftarKamar): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-door-closed"></i><strong class="d-block text-dark mb-1">Belum ada data kamar</strong><span>Tambahkan kamar baru atau ubah kata kunci pencarian.</span></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($daftarKamar as $index => $kamar): ?>
                            <tr>
                                <td class="text-secondary"><?= $index + 1 ?></td>
                                <td><div class="fw-bold text-dark"><?= eKamar($kamar['nomor_kamar']) ?></div><div class="text-secondary small">ID: <?= (int) $kamar['id_kamar'] ?></div></td>
                                <td class="fw-semibold"><?= rupiahKamar((float) $kamar['harga_sewa']) ?></td>
                                <td style="min-width:180px;max-width:300px"><?= $kamar['fasilitas'] !== '' ? eKamar($kamar['fasilitas']) : '<span class="text-secondary">Belum diisi</span>' ?></td>
                                <td>
                                    <?php $jumlahPenghuniKamar = (int)$kamar['jumlah_penghuni']; ?>
                                    <span class="fw-semibold"><?= $jumlahPenghuniKamar ?></span>
                                    <div class="text-secondary small"><?= $jumlahPenghuniKamar === 1 ? 'orang' : 'orang aktif' ?></div>
                                </td>
                                <td>
                                    <?php if ($kamar['status_kamar'] === 'terisi'): ?><span class="soft-badge pending"><i class="bi bi-person-check"></i> Terisi</span>
                                    <?php else: ?><span class="soft-badge"><i class="bi bi-check-circle"></i> Tersedia</span><?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="kamar_edit.php?id=<?= (int) $kamar['id_kamar'] ?>" class="btn btn-sm btn-outline-primary" title="Edit kamar" aria-label="Edit kamar <?= eKamar($kamar['nomor_kamar']) ?>"><i class="bi bi-pencil-square"></i></a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kamar <?= eKamar($kamar['nomor_kamar']) ?>? Data yang sudah dihapus tidak dapat dikembalikan.');">
                                        <input type="hidden" name="csrf_token" value="<?= eKamar($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id_kamar" value="<?= (int) $kamar['id_kamar'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus kamar" aria-label="Hapus kamar <?= eKamar($kamar['nomor_kamar']) ?>"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"><span class="text-secondary small">Menampilkan <?= count($daftarKamar) ?> kamar</span><span class="text-secondary small">Data diperbarui langsung dari database</span></div>
        </section>
        <footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
