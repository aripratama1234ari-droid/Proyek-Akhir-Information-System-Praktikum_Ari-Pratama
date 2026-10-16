<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = '';
$jenisPesan = 'success';
$flash = $_GET['pesan'] ?? '';
$flashMap = [
    'tambah' => 'Data penghuni dan akun berhasil ditambahkan.',
    'ubah' => 'Data penghuni berhasil diperbarui.',
    'hapus' => 'Data penghuni berhasil dihapus.',
];
if (isset($flashMap[$flash])) {
    $pesan = $flashMap[$flash];
} elseif ($flash === 'tidak_ditemukan') {
    $pesan = 'Data penghuni tidak ditemukan.';
    $jenisPesan = 'warning';
} elseif ($flash === 'gagal_hapus') {
    $pesan = 'Data penghuni tidak dapat dihapus karena masih memiliki keluhan yang belum selesai. Selesaikan keluhan terlebih dahulu.';
    $jenisPesan = 'warning';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $token = $_POST['csrf_token'] ?? '';
    $idPenghuni = filter_input(INPUT_POST, 'id_penghuni', FILTER_VALIDATE_INT);

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
        $jenisPesan = 'danger';
    } elseif (!$idPenghuni || $idPenghuni < 1) {
        $pesan = 'Data penghuni yang dipilih tidak valid.';
        $jenisPesan = 'danger';
    } else {
        try {
            $pdo->beginTransaction();
            $ambil = $pdo->prepare('SELECT id_user, id_kamar FROM penghuni WHERE id_penghuni = :id FOR UPDATE');
            $ambil->execute(['id' => $idPenghuni]);
            $data = $ambil->fetch();

            if (!$data) {
                $pdo->rollBack();
                $pesan = 'Data penghuni tidak ditemukan.';
                $jenisPesan = 'warning';
            } else {
             
                $cekKeluhanAktif = $pdo->prepare("SELECT COUNT(*) FROM keluhan WHERE id_penghuni = :id AND status_penanganan IN ('menunggu', 'diproses')");
                $cekKeluhanAktif->execute(['id' => $idPenghuni]);

                if ((int)$cekKeluhanAktif->fetchColumn() > 0) {
                    $pdo->rollBack();
                    header('Location: penghuni.php?pesan=gagal_hapus');
                    exit;
                }

                
                $hapusPembayaran = $pdo->prepare('DELETE FROM pembayaran WHERE id_penghuni = :id');
                $hapusPembayaran->execute(['id' => $idPenghuni]);

              
                $hapusKeluhan = $pdo->prepare("DELETE FROM keluhan WHERE id_penghuni = :id AND status_penanganan = 'selesai'");
                $hapusKeluhan->execute(['id' => $idPenghuni]);

                $hapus = $pdo->prepare('DELETE FROM penghuni WHERE id_penghuni = :id');
                $hapus->execute(['id' => $idPenghuni]);

                
                if (!empty($data['id_user'])) {
                    $idUser = (int)$data['id_user'];
                    $cekRelasiPenghuni = $pdo->prepare('SELECT COUNT(*) FROM penghuni WHERE id_user = :id_user');
                    $cekRelasiPenghuni->execute(['id_user' => $idUser]);
                    if ((int)$cekRelasiPenghuni->fetchColumn() === 0) {
                       
                        try {
                            $lepasLog = $pdo->prepare('UPDATE activity_logs SET id_user = NULL WHERE id_user = :id_user');
                            $lepasLog->execute(['id_user' => $idUser]);
                        } catch (PDOException $ignored) {
                           
                        }
                        try {
                            $lepasPengeluaran = $pdo->prepare('UPDATE pengeluaran SET id_user = NULL WHERE id_user = :id_user');
                            $lepasPengeluaran->execute(['id_user' => $idUser]);
                        } catch (PDOException $ignored) {
                            
                        }
                        $hapusUser = $pdo->prepare("DELETE FROM users WHERE id_user = :id_user AND role = 'penghuni'");
                        $hapusUser->execute(['id_user' => $idUser]);
                    }
                }

                if (!empty($data['id_kamar'])) {
                    $cekAktif = $pdo->prepare("SELECT COUNT(*) FROM penghuni WHERE id_kamar = :id_kamar AND status_penghuni = 'aktif'");
                    $cekAktif->execute(['id_kamar' => $data['id_kamar']]);
                    if ((int)$cekAktif->fetchColumn() === 0) {
                        $ubahKamar = $pdo->prepare("UPDATE kamar SET status_kamar = 'tersedia' WHERE id_kamar = :id_kamar");
                        $ubahKamar->execute(['id_kamar' => $data['id_kamar']]);
                    }
                }

                $pdo->commit();
                header('Location: penghuni.php?pesan=hapus');
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $pesan = 'Data penghuni gagal dihapus. Periksa relasi database.';
            $jenisPesan = 'danger';
        }
    }
}

$kataKunci = trim($_GET['cari'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$sql = "SELECT p.id_penghuni, p.id_user, p.id_kamar, p.nama, p.nomor_identitas_mahasiswa, p.no_telepon,
               p.tanggal_mulai_tinggal, p.status_penghuni, k.nomor_kamar, u.username
        FROM penghuni p
        LEFT JOIN kamar k ON p.id_kamar = k.id_kamar
        LEFT JOIN users u ON p.id_user = u.id_user
        WHERE 1=1";
$params = [];

if ($kataKunci !== '') {
    $sql .= ' AND (p.nama LIKE :cari_nama OR p.nomor_identitas_mahasiswa LIKE :cari_nim OR p.no_telepon LIKE :cari_telepon OR k.nomor_kamar LIKE :cari_kamar)';
    $like = '%' . $kataKunci . '%';
    $params['cari_nama'] = $like;
    $params['cari_nim'] = $like;
    $params['cari_telepon'] = $like;
    $params['cari_kamar'] = $like;
}
if (in_array($statusFilter, ['aktif', 'nonaktif'], true)) {
    $sql .= ' AND p.status_penghuni = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY p.status_penghuni = \'aktif\' DESC, p.nama ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarPenghuni = $stmt->fetchAll();

$totalPenghuni = (int)$pdo->query('SELECT COUNT(*) FROM penghuni')->fetchColumn();
$totalAktif = (int)$pdo->query("SELECT COUNT(*) FROM penghuni WHERE status_penghuni = 'aktif'")->fetchColumn();
$totalNonaktif = $totalPenghuni - $totalAktif;

function ePenghuni($nilai): string { return htmlspecialchars((string)$nilai, ENT_QUOTES, 'UTF-8'); }
function tanggalIndonesia(?string $tanggal): string {
    if (!$tanggal) return '-';
    return date('d M Y', strtotime($tanggal));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Penghuni | KosSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell d-flex">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="app-main">
        <header class="topbar">
            <div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Manajemen data penghuni</div></div>
            <div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?= ePenghuni($_SESSION['nama'] ?? 'Pengelola') ?></div><div class="user-role"><?= isPengelolaUtama() ? 'Pengelola utama' : 'Admin' ?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a></div>
        </header>

        <section class="page-heading">
            <div><div class="eyebrow">Data master</div><h1>Data Penghuni</h1><p>Kelola identitas penghuni, kamar, status tinggal, dan akun akses.</p></div>
            <a href="penghuni_tambah.php" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Tambah Penghuni</a>
        </section>

        <?php if ($pesan !== ''): ?>
        <div class="alert alert-<?= ePenghuni($jenisPesan) ?> alert-dismissible fade show" role="alert"><i class="bi bi-info-circle me-2"></i><?= ePenghuni($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
        <?php endif; ?>

        <section class="row g-3 mb-4">
            <div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Total penghuni</span><span class="stat-icon icon-blue"><i class="bi bi-people"></i></span></div><div class="stat-value"><?= number_format($totalPenghuni) ?></div><div class="stat-foot">Seluruh data penghuni</div></div></div>
            <div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Aktif</span><span class="stat-icon icon-green"><i class="bi bi-person-check"></i></span></div><div class="stat-value"><?= number_format($totalAktif) ?></div><div class="stat-foot">Sedang tinggal di kos</div></div></div>
            <div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Nonaktif</span><span class="stat-icon icon-orange"><i class="bi bi-person-dash"></i></span></div><div class="stat-value"><?= number_format($totalNonaktif) ?></div><div class="stat-foot">Tidak sedang tinggal</div></div></div>
        </section>
    

        <section class="panel">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div><h2 class="panel-title">Daftar penghuni</h2><div class="panel-subtitle">Data penghuni yang tersimpan di database.</div></div>
                <form method="get" class="d-flex flex-column flex-sm-row gap-2" role="search">
                    <input type="search" name="cari" class="form-control" placeholder="Cari nama, NIM, telepon, kamar..." value="<?= ePenghuni($kataKunci) ?>" aria-label="Cari penghuni">
                    <select name="status" class="form-select" aria-label="Filter status" style="min-width:135px"><option value="">Semua status</option><option value="aktif" <?= $statusFilter === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= $statusFilter === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select>
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if ($kataKunci !== '' || $statusFilter !== ''): ?><a href="penghuni.php" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th style="width:55px">No.</th><th>Penghuni</th><th>Kamar</th><th>Kontak</th><th>Mulai tinggal</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php if (!$daftarPenghuni): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i><strong class="d-block text-dark mb-1">Belum ada data penghuni</strong><span>Tambahkan penghuni baru atau ubah kata kunci pencarian.</span></div></td></tr>
                    <?php else: foreach ($daftarPenghuni as $index => $p): ?>
                        <tr>
                            <td class="text-secondary"><?= $index + 1 ?></td>
                            <td><div class="fw-bold text-dark"><?= ePenghuni($p['nama']) ?></div><div class="text-secondary small">NIM: <?= ePenghuni($p['nomor_identitas_mahasiswa']) ?></div><div class="text-secondary small">@<?= ePenghuni($p['username'] ?? '-') ?></div></td>
                            <td><?php if (!empty($p['nomor_kamar'])): ?><span class="fw-bold">Kamar <?= ePenghuni($p['nomor_kamar']) ?></span><?php else: ?><span class="text-secondary">Belum ditentukan</span><?php endif; ?></td>
                            <td><?= ePenghuni($p['no_telepon'] ?? '-') ?></td>
                            <td><?= tanggalIndonesia($p['tanggal_mulai_tinggal']) ?></td>
                            <td><?php if ($p['status_penghuni'] === 'aktif'): ?><span class="soft-badge"><i class="bi bi-check-circle"></i> Aktif</span><?php else: ?><span class="soft-badge pending"><i class="bi bi-dash-circle"></i> Nonaktif</span><?php endif; ?></td>
                            <td class="text-end text-nowrap"><a href="penghuni_edit.php?id=<?= (int)$p['id_penghuni'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a> <?php if (isPengelolaUtama() && !empty($p['id_user'])): ?><a href="reset_password.php?id_user=<?= (int)$p['id_user'] ?>" class="btn btn-sm btn-outline-secondary" title="Ganti password"><i class="bi bi-key"></i></a><?php endif; ?> <form method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data <?= ePenghuni($p['nama']) ?>?');"><input type="hidden" name="csrf_token" value="<?= ePenghuni($_SESSION['csrf_token']) ?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_penghuni" value="<?= (int)$p['id_penghuni'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash3"></i></button></form></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"><span class="text-secondary small">Menampilkan <?= count($daftarPenghuni) ?> penghuni</span><span class="text-secondary small">Data diperbarui langsung dari database</span></div>
        </section>
        <footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
