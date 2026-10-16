<?php
require_once __DIR__ . '/../includes/auth.php';
wajibRole('pengelola');
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pesan = '';
$jenisPesan = 'success';
$flash = $_GET['pesan'] ?? '';
$map = [
    'tambah' => 'Catatan pengeluaran berhasil ditambahkan.',
    'ubah' => 'Catatan pengeluaran berhasil diperbarui.',
    'hapus' => 'Catatan pengeluaran berhasil dihapus.'
];
if (isset($map[$flash])) $pesan = $map[$flash];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $token = $_POST['csrf_token'] ?? '';
    $id = filter_input(INPUT_POST, 'id_pengeluaran', FILTER_VALIDATE_INT);
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $pesan = 'Permintaan tidak valid.';
        $jenisPesan = 'danger';
    } elseif (!$id) {
        $pesan = 'Data pengeluaran tidak valid.';
        $jenisPesan = 'danger';
    } else {
        $q = $pdo->prepare('DELETE FROM pengeluaran WHERE id_pengeluaran = :id');
        $q->execute(['id' => $id]);
        header('Location: pengeluaran.php?pesan=' . ($q->rowCount() ? 'hapus' : 'tidak_ditemukan'));
        exit;
    }
}

$cari = trim($_GET['cari'] ?? '');
$bulan = trim($_GET['bulan'] ?? '');
$params = [];
$sql = "SELECT g.id_pengeluaran, g.tanggal_pengeluaran, g.jenis_pengeluaran, g.jumlah, g.catatan, u.nama AS pencatat
        FROM pengeluaran g
        LEFT JOIN users u ON u.id_user = g.id_user
        WHERE 1=1";
if ($cari !== '') {
    $sql .= ' AND (g.jenis_pengeluaran LIKE :jenis OR g.catatan LIKE :catatan OR u.nama LIKE :pencatat)';
    $like = "%$cari%";
    $params += ['jenis' => $like, 'catatan' => $like, 'pencatat' => $like];
}
if (preg_match('/^\d{4}-\d{2}$/', $bulan)) {
    $sql .= " AND DATE_FORMAT(g.tanggal_pengeluaran, '%Y-%m') = :bulan";
    $params['bulan'] = $bulan;
}
$sql .= ' ORDER BY g.tanggal_pengeluaran DESC, g.id_pengeluaran DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$totalPengeluaran = (float) $pdo->query('SELECT COALESCE(SUM(jumlah), 0) FROM pengeluaran')->fetchColumn();
$totalBulanIni = (float) $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran WHERE DATE_FORMAT(tanggal_pengeluaran,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();
$totalCatatan = (int) $pdo->query('SELECT COUNT(*) FROM pengeluaran')->fetchColumn();

function ePengeluaran($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function rpPengeluaran($v): string { return 'Rp ' . number_format((float) $v, 0, ',', '.'); }
function tPengeluaran($v): string { return $v ? date('d M Y', strtotime($v)) : '-'; }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pengeluaran | KosSpace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/app.css" rel="stylesheet">
</head>
<body><div class="app-shell d-flex">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="app-main">
<header class="topbar">
    <div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Catatan pengeluaran kos</div></div>
    <div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?= ePengeluaran($_SESSION['nama'] ?? 'Pengelola') ?></div><div class="user-role"><?= isPengelolaUtama() ? 'Pengelola utama' : 'Admin' ?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a></div>
</header>
<section class="page-heading"><div><div class="eyebrow">Keuangan operasional</div><h1>Pengeluaran</h1><p>Catat biaya listrik, air, gas, perbaikan, penanganan keluhan, dan kebutuhan operasional lainnya.</p></div><a href="pengeluaran_tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Pengeluaran</a></section>
<?php if ($pesan): ?><div class="alert alert-<?= ePengeluaran($jenisPesan) ?> alert-dismissible fade show"><i class="bi bi-info-circle me-2"></i><?= ePengeluaran($pesan) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<section class="row g-3 mb-4">
<div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Total pengeluaran</span><span class="stat-icon icon-blue"><i class="bi bi-cash-stack"></i></span></div><div class="stat-value" style="font-size:22px"><?= rpPengeluaran($totalPengeluaran) ?></div><div class="stat-foot">Akumulasi seluruh catatan</div></div></div>
<div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Bulan ini</span><span class="stat-icon icon-orange"><i class="bi bi-calendar3"></i></span></div><div class="stat-value" style="font-size:22px"><?= rpPengeluaran($totalBulanIni) ?></div><div class="stat-foot">Pengeluaran pada bulan berjalan</div></div></div>
<div class="col-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Jumlah catatan</span><span class="stat-icon icon-green"><i class="bi bi-journal-text"></i></span></div><div class="stat-value"><?= number_format($totalCatatan) ?></div><div class="stat-foot">Seluruh catatan pengeluaran</div></div></div>
</section>
<section class="panel">
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4"><div><h2 class="panel-title">Riwayat pengeluaran</h2><div class="panel-subtitle">Jenis pengeluaran menggunakan teks bebas, sehingga bisa disesuaikan dengan kebutuhan kos.</div></div>
<form method="get" class="d-flex flex-column flex-sm-row gap-2"><input name="cari" class="form-control" placeholder="Cari jenis, catatan, pencatat..." value="<?= ePengeluaran($cari) ?>"><input type="month" name="bulan" class="form-control" value="<?= ePengeluaran($bulan) ?>"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button><?php if ($cari || $bulan): ?><a class="btn btn-outline-secondary" href="pengeluaran.php">Reset</a><?php endif; ?></form></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>No.</th><th>Tanggal</th><th>Jenis pengeluaran</th><th>Jumlah</th><th>Catatan</th><th>Pencatat</th><th class="text-end">Aksi</th></tr></thead><tbody>
<?php if (!$data): ?><tr><td colspan="7"><div class="empty-state"><i class="bi bi-cash-stack"></i><strong class="d-block text-dark mb-1">Belum ada catatan pengeluaran</strong><span>Tambahkan pengeluaran operasional untuk mulai membuat riwayat.</span></div></td></tr>
<?php else: foreach ($data as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= tPengeluaran($r['tanggal_pengeluaran']) ?></td><td><strong><?= ePengeluaran($r['jenis_pengeluaran']) ?></strong></td><td class="fw-semibold"><?= rpPengeluaran($r['jumlah']) ?></td><td style="min-width:240px;max-width:400px"><?= nl2br(ePengeluaran($r['catatan'] ?: '-')) ?></td><td><?= ePengeluaran($r['pencatat'] ?? '-') ?></td><td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="pengeluaran_edit.php?id=<?= (int) $r['id_pengeluaran'] ?>"><i class="bi bi-pencil-square"></i></a> <form method="post" class="d-inline" onsubmit="return confirm('Hapus catatan pengeluaran ini?');"><input type="hidden" name="csrf_token" value="<?= ePengeluaran($_SESSION['csrf_token']) ?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_pengeluaran" value="<?= (int) $r['id_pengeluaran'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button></form></td></tr><?php endforeach; endif; ?></tbody></table></div>
</section>
<footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
</main></div></body></html>
