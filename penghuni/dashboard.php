<?php
require_once __DIR__ . '/../includes/auth.php';
wajibRole('penghuni');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/pembayaran_helper.php';
pastikanTagihanBulanIni($pdo);

$stmt = $pdo->prepare(
    'SELECT p.*, k.nomor_kamar, k.harga_sewa, k.fasilitas
     FROM penghuni p LEFT JOIN kamar k ON p.id_kamar = k.id_kamar
     WHERE p.id_user = :id_user LIMIT 1'
);
$stmt->execute(['id_user' => $_SESSION['id_user']]);
$penghuni = $stmt->fetch();
$jumlahPembayaran = 0;
$jumlahKeluhan = 0;
$pembayaranTerakhir = null;

if ($penghuni) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pembayaran WHERE id_penghuni = :id AND status_pembayaran = 'lunas'");
    $stmt->execute(['id' => $penghuni['id_penghuni']]);
    $jumlahPembayaran = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM keluhan WHERE id_penghuni = :id AND status_penanganan != 'selesai'");
    $stmt->execute(['id' => $penghuni['id_penghuni']]);
    $jumlahKeluhan = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT * FROM pembayaran WHERE id_penghuni = :id ORDER BY id_pembayaran DESC LIMIT 1');
    $stmt->execute(['id' => $penghuni['id_penghuni']]);
    $pembayaranTerakhir = $stmt->fetch();

    $temanSekamar = [];
    if (!empty($penghuni['id_kamar']) && ($penghuni['status_penghuni'] ?? '') === 'aktif') {
        $stmt = $pdo->prepare("SELECT id_penghuni, nama, nomor_identitas_mahasiswa FROM penghuni WHERE id_kamar = :id_kamar AND status_penghuni = 'aktif' AND id_penghuni <> :id_penghuni ORDER BY nama ASC");
        $stmt->execute(['id_kamar' => $penghuni['id_kamar'], 'id_penghuni' => $penghuni['id_penghuni']]);
        $temanSekamar = $stmt->fetchAll();
    }

    $periodeBulanDashboard = date('Y-m-01');
    $cekBulan = $pdo->prepare('SELECT status_pembayaran, jumlah_pembayaran FROM pembayaran WHERE id_penghuni=:id AND (periode_sewa=:periode OR periode_sewa=:iso OR periode_sewa=:nama) ORDER BY id_pembayaran DESC LIMIT 1');
    $cekBulan->execute(['id'=>$penghuni['id_penghuni'], 'periode'=>$periodeBulanDashboard, 'iso'=>date('Y-m'), 'nama'=>date('F Y')]);
    $pembayaranBulanIni = $cekBulan->fetch();
}
function rupiahPenghuni(float $angka): string { return 'Rp ' . number_format($angka, 0, ',', '.'); }
function aman(?string $teks): string { return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Penghuni | KosSpace</title>
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
            <a class="active" href="dashboard.php"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a>
            <a href="#informasi-pembayaran"><i class="bi bi-wallet2"></i>Ringkasan pembayaran</a>
            <a href="ganti_password.php"><i class="bi bi-shield-lock"></i>Ganti username dan password</a>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-house-check me-1"></i> Portal Penghuni<br>Informasi hunian dalam satu tempat.</div>
    </aside>

    <main class="app-main">
        <header class="topbar">
            <div><div class="topbar-kicker">Workspace / Penghuni</div><div class="topbar-title">Portal informasi hunian</div></div>
            <div class="user-chip">
                <div class="avatar"><i class="bi bi-person-fill"></i></div>
                <div class="d-none d-sm-block"><div class="user-name"><?= aman($_SESSION['nama'] ?? 'Penghuni') ?></div><div class="user-role">Penghuni kos</div></div>
                <a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a>
            </div>
        </header>

        <section class="page-heading">
            <div><div class="eyebrow">Portal penghuni</div><h1>Halo, <?= aman($_SESSION['nama'] ?? 'Penghuni') ?>!</h1><p>Berikut ringkasan informasi kamar dan aktivitas kosmu.</p></div>
            <?php if ($penghuni && ($penghuni['status_penghuni'] ?? '') === 'aktif'): ?><span class="soft-badge"><i class="bi bi-check-circle-fill"></i> Penghuni aktif</span><?php endif; ?>
        </section>

        <?php if (!$penghuni): ?>
            <div class="panel py-5 mb-4"><div class="empty-state"><i class="bi bi-person-exclamation"></i><h5 class="fw-bold text-dark">Data penghuni belum tersedia</h5><p class="mb-0">Akunmu belum dihubungkan dengan data penghuni. Silakan hubungi pengelola kos.</p></div></div>
        <?php else: ?>
            <section class="room-banner mb-4">
                <div class="row align-items-center g-4 room-banner-content">
                    <div class="col-md-7">
                        <div class="small mb-2" style="color:#dbe5ff;font-weight:700;letter-spacing:.12em;font-size:10px">KAMAR YANG KAMU TEMPATI</div>
                        <h2>Kamar <?= aman($penghuni['nomor_kamar'] ?? 'Belum ditentukan') ?></h2>
                        <div class="room-meta"><i class="bi bi-calendar-check me-2"></i>Mulai tinggal: <?= !empty($penghuni['tanggal_mulai_tinggal']) ? date('d M Y', strtotime($penghuni['tanggal_mulai_tinggal'])) : 'Belum diatur' ?></div>
                        <?php if (!empty($penghuni['fasilitas'])): ?><div class="room-meta mt-2"><i class="bi bi-stars me-2"></i><?= aman($penghuni['fasilitas']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <div class="room-meta mb-1">Biaya sewa per bulan</div>
                        <div class="room-price"><?= rupiahPenghuni((float)($penghuni['harga_sewa'] ?? 0)) ?></div>
                    </div>
                </div>
            </section>

            <section class="row g-3 mb-4">
                <div class="col-6 col-xl-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Status penghuni</span><span class="stat-icon icon-green"><i class="bi bi-person-check"></i></span></div><div class="stat-value" style="font-size:22px"><?= aman(ucfirst($penghuni['status_penghuni'] ?? 'Belum diatur')) ?></div><div class="stat-foot">Status data hunianmu</div></div></div>
                <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Tagihan bulan ini</span><span class="stat-icon icon-orange"><i class="bi bi-calendar-month"></i></span></div><div class="stat-value" style="font-size:21px"><?= rupiahPenghuni((float)($penghuni['harga_sewa'] ?? 0)) ?></div><div class="stat-foot"><?= aman($periodeBulanDashboard) ?></div></div></div>
                <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Pembayaran lunas</span><span class="stat-icon icon-blue"><i class="bi bi-receipt"></i></span></div><div class="stat-value"><?= number_format($jumlahPembayaran) ?></div><div class="stat-foot">Jumlah transaksi tercatat lunas</div></div></div>
                <div class="col-12 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Status bulan ini</span><span class="stat-icon icon-green"><i class="bi bi-wallet2"></i></span></div><div class="stat-value" style="font-size:18px"><?= aman($pembayaranBulanIni ? ($pembayaranBulanIni['status_pembayaran']==='lunas' ? 'Lunas' : 'Menunggu') : 'Belum bayar') ?></div><div class="stat-foot"><a href="pembayaran.php" class="text-decoration-none">Kelola pembayaran →</a></div></div></div>
                <div class="col-12 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Keluhan berjalan</span><span class="stat-icon icon-orange"><i class="bi bi-chat-square-dots"></i></span></div><div class="stat-value"><?= number_format($jumlahKeluhan) ?></div><div class="stat-foot"><a href="keluhan.php" class="text-decoration-none">Buka keluhan →</a></div></div></div>
            </section>

            <section class="row g-3 mb-3">
                <div class="col-lg-7">
                    <div class="panel h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div><h2 class="panel-title">Teman sekamar</h2><div class="panel-subtitle">Penghuni aktif lain yang tercatat di kamar yang sama.</div></div>
                            <span class="stat-icon icon-blue"><i class="bi bi-people"></i></span>
                        </div>
                        <?php if ($temanSekamar): ?>
                            <?php foreach ($temanSekamar as $teman): ?>
                                <div class="quick-link mt-2">
                                    <i class="bi bi-person-circle"></i>
                                    <span class="flex-grow-1"><strong><?= aman($teman['nama']) ?></strong><small class="d-block text-secondary"><?= aman($teman['nomor_identitas_mahasiswa']) ?></small></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state py-3"><i class="bi bi-person-plus"></i><div class="fw-bold text-dark mb-1">Belum ada teman sekamar</div><p class="mb-0">Kamu tercatat sebagai satu-satunya penghuni aktif di kamar ini.</p></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="panel h-100">
                        <h2 class="panel-title">Ringkasan kamar</h2>
                        <div class="panel-subtitle mb-3">Informasi penghuni aktif di kamar ini.</div>
                        <div class="quick-link"><i class="bi bi-door-closed"></i><span class="flex-grow-1">Kamar</span><strong><?= aman($penghuni['nomor_kamar'] ?? '-') ?></strong></div>
                        <div class="quick-link mt-2"><i class="bi bi-people"></i><span class="flex-grow-1">Total penghuni aktif</span><strong><?= count($temanSekamar) + 1 ?> orang</strong></div>
                    </div>
                </div>
            </section>

            <section class="row g-3">
                <div class="col-lg-7" id="informasi-pembayaran">
                    <div class="panel h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h2 class="panel-title">Pembayaran terakhir</h2><div class="panel-subtitle">Ringkasan transaksi terbaru yang tercatat.</div></div><span class="stat-icon icon-blue"><i class="bi bi-wallet2"></i></span></div>
                        <?php if ($pembayaranTerakhir): ?>
                            <div class="divider"></div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div><div class="stat-label mb-1">Periode sewa</div><div class="fw-bold"><?= !empty($pembayaranTerakhir['periode_sewa']) ? date('F Y', strtotime($pembayaranTerakhir['periode_sewa'])) : '-' ?></div></div>
                                <div><div class="stat-label mb-1">Nominal</div><div class="fw-bold"><?= rupiahPenghuni((float)($pembayaranTerakhir['jumlah_pembayaran'] ?? 0)) ?></div></div>
                                <?php $statusBayar = strtolower((string)($pembayaranTerakhir['status_pembayaran'] ?? '')); ?>
                                <span class="soft-badge <?= $statusBayar === 'lunas' ? '' : 'pending' ?>"><?= aman(ucfirst($statusBayar ?: 'Belum diatur')) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="empty-state"><i class="bi bi-receipt"></i><div class="fw-bold text-dark mb-1">Belum ada riwayat pembayaran</div><p class="mb-0">Informasi pembayaran akan tampil di sini setelah data dicatat.</p></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="panel h-100">
                        <h2 class="panel-title">Pengaturan akun</h2><div class="panel-subtitle mb-3">Jaga keamanan akunmu.</div>
                        <a href="pembayaran.php" class="quick-link"><i class="bi bi-wallet2"></i><span class="flex-grow-1">Lihat pembayaran</span><i class="bi bi-chevron-right" style="font-size:12px;color:#8c9ab0"></i></a>
                        <a href="keluhan.php" class="quick-link mt-2"><i class="bi bi-chat-square-text"></i><span class="flex-grow-1">Kirim keluhan</span><i class="bi bi-chevron-right" style="font-size:12px;color:#8c9ab0"></i></a>
                        <a href="ganti_password.php" class="quick-link mt-2"><i class="bi bi-shield-lock"></i><span class="flex-grow-1">Ganti password</span><i class="bi bi-chevron-right" style="font-size:12px;color:#8c9ab0"></i></a>
                        <div class="d-flex align-items-start gap-2 mt-3" style="color:var(--ks-muted);font-size:11px;line-height:1.7"><i class="bi bi-info-circle mt-1"></i><span>Gunakan password yang kuat dan jangan membagikannya kepada orang lain.</span></div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        <footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
    </main>
</div>
</body>
</html>
