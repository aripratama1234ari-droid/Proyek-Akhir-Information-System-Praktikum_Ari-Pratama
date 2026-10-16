<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/pembayaran_helper.php';
pastikanTagihanBulanIni($pdo);

$totalKamar = (int) $pdo->query('SELECT COUNT(*) FROM kamar')->fetchColumn();
$kamarTerisi = (int) $pdo->query("SELECT COUNT(*) FROM kamar WHERE status_kamar = 'terisi'")->fetchColumn();
$totalPenghuni = (int) $pdo->query("SELECT COUNT(*) FROM penghuni WHERE status_penghuni = 'aktif'")->fetchColumn();
$totalKeluhan = (int) $pdo->query("SELECT COUNT(*) FROM keluhan WHERE status_penanganan != 'selesai'")->fetchColumn();
$persentaseTerisi = $totalKamar > 0 ? round(($kamarTerisi / $totalKamar) * 100) : 0;

function rupiahDashboard(float $angka): string { return 'Rp ' . number_format($angka, 0, ',', '.'); }
function eDashboard($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$isOwner = isPengelolaUtama();
$bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$bulanDipilih = trim($_GET['bulan'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $bulanDipilih)) $bulanDipilih = date('Y-m');
[$tahunDipilih, $nomorBulanDipilih] = array_map('intval', explode('-', $bulanDipilih));
if ($nomorBulanDipilih < 1 || $nomorBulanDipilih > 12) $bulanDipilih = date('Y-m');
$labelBulanDipilih = $bulanNama[$nomorBulanDipilih] . ' ' . $tahunDipilih;

$chartLabels = [];
$chartKeys = [];
$chartPemasukan = [];
$chartPengeluaran = [];
$chartSelisih = [];

$startChart = new DateTimeImmutable('first day of this month');
$startChart = $startChart->modify('-11 months');
$endChart = new DateTimeImmutable('first day of this month');

if ($isOwner) {
    $incomeStmt = $pdo->prepare("SELECT DATE_FORMAT(tanggal_pembayaran, '%Y-%m') AS periode, COALESCE(SUM(jumlah_pembayaran),0) AS total
        FROM pembayaran
        WHERE status_pembayaran = 'lunas' AND tanggal_pembayaran IS NOT NULL
          AND tanggal_pembayaran >= :mulai AND tanggal_pembayaran < :sampai
        GROUP BY DATE_FORMAT(tanggal_pembayaran, '%Y-%m')");
    $incomeStmt->execute([
        'mulai' => $startChart->format('Y-m-01'),
        'sampai' => $endChart->modify('+1 month')->format('Y-m-01')
    ]);
    $incomeMap = [];
    foreach ($incomeStmt->fetchAll() as $r) $incomeMap[$r['periode']] = (float)$r['total'];

    $expenseStmt = $pdo->prepare("SELECT DATE_FORMAT(tanggal_pengeluaran, '%Y-%m') AS periode, COALESCE(SUM(jumlah),0) AS total
        FROM pengeluaran
        WHERE tanggal_pengeluaran >= :mulai AND tanggal_pengeluaran < :sampai
        GROUP BY DATE_FORMAT(tanggal_pengeluaran, '%Y-%m')");
    $expenseStmt->execute([
        'mulai' => $startChart->format('Y-m-01'),
        'sampai' => $endChart->modify('+1 month')->format('Y-m-01')
    ]);
    $expenseMap = [];
    foreach ($expenseStmt->fetchAll() as $r) $expenseMap[$r['periode']] = (float)$r['total'];

    for ($d = $startChart; $d <= $endChart; $d = $d->modify('+1 month')) {
        $key = $d->format('Y-m');
        $chartKeys[] = $key;
        $chartLabels[] = $bulanNama[(int)$d->format('n')] . ' ' . $d->format('Y');
        $in = $incomeMap[$key] ?? 0;
        $out = $expenseMap[$key] ?? 0;
        $chartPemasukan[] = $in;
        $chartPengeluaran[] = $out;
        $chartSelisih[] = $in - $out;
    }

    $incomeSelectedStmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah_pembayaran),0) FROM pembayaran
        WHERE status_pembayaran='lunas' AND tanggal_pembayaran IS NOT NULL AND DATE_FORMAT(tanggal_pembayaran,'%Y-%m')=:bulan");
    $incomeSelectedStmt->execute(['bulan'=>$bulanDipilih]);
    $pemasukanDipilih = (float)$incomeSelectedStmt->fetchColumn();

    $expenseSelectedStmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran WHERE DATE_FORMAT(tanggal_pengeluaran,'%Y-%m')=:bulan");
    $expenseSelectedStmt->execute(['bulan'=>$bulanDipilih]);
    $pengeluaranDipilih = (float)$expenseSelectedStmt->fetchColumn();
    $selisihDipilih = $pemasukanDipilih - $pengeluaranDipilih;

    $expenseDetailStmt = $pdo->prepare("SELECT g.id_pengeluaran,g.tanggal_pengeluaran,g.jenis_pengeluaran,g.jumlah,g.catatan,u.nama AS pencatat
        FROM pengeluaran g LEFT JOIN users u ON u.id_user=g.id_user
        WHERE DATE_FORMAT(g.tanggal_pengeluaran,'%Y-%m')=:bulan
        ORDER BY g.tanggal_pengeluaran DESC,g.id_pengeluaran DESC");
    $expenseDetailStmt->execute(['bulan'=>$bulanDipilih]);
    $expenseDetails = $expenseDetailStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | KosSpace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/app.css" rel="stylesheet">
<style>
.finance-months{display:flex;gap:.5rem;flex-wrap:wrap}.finance-months a{padding:.45rem .7rem;border:1px solid #e1e7f0;border-radius:10px;text-decoration:none;color:#52617a;background:#fff;font-size:.85rem}.finance-months a:hover,.finance-months a.active{background:#edf3ff;color:#2f5ee8;border-color:#8aa6ff;font-weight:600}.finance-table td,.finance-table th{vertical-align:middle}.positive{color:#17834b}.negative{color:#c0392b}
</style>
</head>
<body>
<div class="app-shell d-flex">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="app-main">
<header class="topbar"><div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Ringkasan pengelolaan kos</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?=eDashboard($_SESSION['nama']??'Pengguna')?></div><div class="user-role"><?= $isOwner ? 'Pemilik / Pengelola' : 'Admin' ?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a></div></header>
<section class="page-heading"><div><div class="eyebrow">Dashboard utama</div><h1>Selamat datang, <?=eDashboard($_SESSION['nama']??'Pengguna')?>!</h1><p>Berikut ringkasan kondisi kos saat ini.</p></div><div class="soft-badge"><i class="bi bi-circle-fill" style="font-size:7px"></i> Sistem aktif</div></section>
<section class="row g-3 mb-4">
<div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Total kamar</span><span class="stat-icon icon-blue"><i class="bi bi-door-closed"></i></span></div><div class="stat-value"><?=number_format($totalKamar)?></div><div class="stat-foot">Jumlah seluruh kamar terdaftar</div></div></div>
<div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Kamar terisi</span><span class="stat-icon icon-green"><i class="bi bi-house-check"></i></span></div><div class="stat-value"><?=number_format($kamarTerisi)?></div><div class="stat-foot"><?=$persentaseTerisi?>% dari seluruh kamar</div></div></div>
<div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Penghuni aktif</span><span class="stat-icon icon-purple"><i class="bi bi-people"></i></span></div><div class="stat-value"><?=number_format($totalPenghuni)?></div><div class="stat-foot">Sudah membayar bulan berjalan</div></div></div>
<div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><span class="stat-label">Keluhan terbuka</span><span class="stat-icon icon-orange"><i class="bi bi-chat-square-dots"></i></span></div><div class="stat-value"><?=number_format($totalKeluhan)?></div><div class="stat-foot">Keluhan yang belum selesai</div></div></div>
</section>
<section class="row g-3 mb-4">
<div class="col-lg-7"><div class="panel h-100"><div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><h2 class="panel-title">Tingkat keterisian kamar</h2><div class="panel-subtitle">Perbandingan kamar terisi dengan seluruh kamar.</div></div><span class="stat-icon icon-blue"><i class="bi bi-bar-chart-line"></i></span></div><div class="d-flex align-items-end justify-content-between mb-2"><div class="stat-value"><?=$persentaseTerisi?>%</div><div class="text-secondary small"><?=$kamarTerisi?> dari <?=$totalKamar?> kamar</div></div><div class="progress" style="height:10px;border-radius:20px;background:#edf1f8"><div class="progress-bar" role="progressbar" style="width:<?=$persentaseTerisi?>%;background:linear-gradient(90deg,#3867e8,#7594ff);border-radius:20px"></div></div></div></div>
<div class="col-lg-5" id="modul-sistem"><div class="panel h-100"><h2 class="panel-title">Modul sistem</h2><div class="panel-subtitle mb-3">Bagian utama dalam pengelolaan kos.</div><div class="d-grid gap-2"><a class="quick-link" href="kamar.php"><i class="bi bi-door-closed"></i><span class="flex-grow-1">Pengelolaan data kamar</span><span class="soft-badge">Buka modul</span></a><a class="quick-link" href="penghuni.php"><i class="bi bi-people"></i><span class="flex-grow-1">Pengelolaan data penghuni</span><span class="soft-badge">Buka modul</span></a><a class="quick-link" href="pembayaran.php"><i class="bi bi-wallet2"></i><span class="flex-grow-1">Pencatatan pembayaran</span><i class="bi bi-chevron-right" style="font-size:12px;color:#8c9ab0"></i></a><a class="quick-link" href="keluhan.php"><i class="bi bi-chat-square-text"></i><span class="flex-grow-1">Pengelolaan keluhan</span><i class="bi bi-chevron-right" style="font-size:12px;color:#8c9ab0"></i></a><?php if($isOwner): ?><a class="quick-link" href="pengeluaran.php"><i class="bi bi-cash-stack"></i><span class="flex-grow-1">Catatan pengeluaran</span><i class="bi bi-chevron-right" style="font-size:12px;color:#8c9ab0"></i></a><?php endif; ?></div></div></div>
</section>
<?php if($isOwner): ?>
<section id="keuangan" class="mb-4"><div class="d-flex justify-content-between align-items-end mb-3"><div><div class="eyebrow">Keuangan</div><h2 class="panel-title mb-1">Ringkasan pemasukan & pengeluaran</h2><div class="panel-subtitle">Pemasukan dihitung dari pembayaran yang berstatus Lunas berdasarkan tanggal pembayaran. Pengeluaran dihitung berdasarkan tanggal pengeluaran.</div></div><a href="pengeluaran.php" class="btn btn-primary"><i class="bi bi-cash-stack me-1"></i>Kelola Pengeluaran</a></div>
<div class="row g-3 mb-3"><div class="col-md-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Pemasukan <?=$labelBulanDipilih?></span><span class="stat-icon icon-green"><i class="bi bi-arrow-down-left"></i></span></div><div class="stat-value" style="font-size:23px"><?=rupiahDashboard($pemasukanDipilih)?></div><div class="stat-foot">Pembayaran berstatus lunas</div></div></div><div class="col-md-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Pengeluaran <?=$labelBulanDipilih?></span><span class="stat-icon icon-orange"><i class="bi bi-arrow-up-right"></i></span></div><div class="stat-value" style="font-size:23px"><?=rupiahDashboard($pengeluaranDipilih)?></div><div class="stat-foot">Seluruh pengeluaran bulan terpilih</div></div></div><div class="col-md-4"><div class="stat-card"><div class="stat-top"><span class="stat-label">Selisih <?=$labelBulanDipilih?></span><span class="stat-icon icon-blue"><i class="bi bi-calculator"></i></span></div><div class="stat-value <?= $selisihDipilih >= 0 ? 'positive' : 'negative' ?>" style="font-size:23px"><?=rupiahDashboard($selisihDipilih)?></div><div class="stat-foot">Pemasukan dikurangi pengeluaran</div></div></div></div>
<div class="row g-3 mb-3">
<div class="col-xl-6"><div class="panel h-100"><div class="d-flex justify-content-between align-items-start mb-3"><div><h2 class="panel-title">Grafik Pemasukan</h2><div class="panel-subtitle">Total pembayaran berstatus lunas per bulan.</div></div><span class="stat-icon icon-green"><i class="bi bi-graph-up-arrow"></i></span></div><div style="height:320px"><canvas id="incomeChart"></canvas></div></div></div>
<div class="col-xl-6"><div class="panel h-100"><div class="d-flex justify-content-between align-items-start mb-3"><div><h2 class="panel-title">Grafik Pengeluaran</h2><div class="panel-subtitle">Total pengeluaran operasional per bulan.</div></div><span class="stat-icon icon-orange"><i class="bi bi-graph-down-arrow"></i></span></div><div style="height:320px"><canvas id="expenseChart"></canvas></div></div></div>
</div>
<div class="panel mb-3"><div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h2 class="panel-title">Pilih bulan</h2><div class="panel-subtitle">Klik bulan untuk menampilkan rincian pengeluaran pada tabel di bawah.</div></div></div><div class="finance-months"><?php foreach($chartKeys as $idx=>$key): ?><a class="<?= $key===$bulanDipilih ? 'active' : '' ?>" href="?bulan=<?=eDashboard($key)?>#pengeluaran-bulan"><?=eDashboard($chartLabels[$idx])?></a><?php endforeach; ?></div></div>
<div class="panel mb-3" id="rekap-keuangan"><div class="d-flex justify-content-between align-items-start mb-3"><div><h2 class="panel-title">Rekap keuangan per bulan</h2><div class="panel-subtitle">Selisih = pemasukan - pengeluaran.</div></div></div><div class="table-responsive"><table class="table table-hover align-middle finance-table mb-0"><thead><tr><th>Bulan</th><th class="text-end">Pemasukan</th><th class="text-end">Pengeluaran</th><th class="text-end">Selisih</th></tr></thead><tbody><?php foreach($chartKeys as $idx=>$key): $net=$chartSelisih[$idx]; ?><tr><td><a href="?bulan=<?=eDashboard($key)?>#pengeluaran-bulan" class="text-decoration-none fw-semibold"><?=eDashboard($chartLabels[$idx])?></a></td><td class="text-end"><?=rupiahDashboard($chartPemasukan[$idx])?></td><td class="text-end"><?=rupiahDashboard($chartPengeluaran[$idx])?></td><td class="text-end fw-semibold <?= $net>=0 ? 'positive':'negative' ?>"><?=rupiahDashboard($net)?></td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="panel" id="pengeluaran-bulan"><div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h2 class="panel-title">Pengeluaran <?=$labelBulanDipilih?></h2><div class="panel-subtitle">Rincian pengeluaran yang tercatat pada bulan terpilih.</div></div><span class="soft-badge">Total <?=rupiahDashboard($pengeluaranDipilih)?></span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>No.</th><th>Tanggal</th><th>Jenis</th><th>Catatan</th><th>Pencatat</th><th class="text-end">Jumlah</th></tr></thead><tbody><?php if(!$expenseDetails): ?><tr><td colspan="6"><div class="empty-state"><i class="bi bi-cash-stack"></i><strong class="d-block text-dark mb-1">Belum ada pengeluaran</strong><span>Tidak ada pengeluaran yang tercatat pada <?=$labelBulanDipilih?>.</span></div></td></tr><?php else: foreach($expenseDetails as $i=>$r): ?><tr><td><?=$i+1?></td><td><?=eDashboard(date('d M Y',strtotime($r['tanggal_pengeluaran'])))?></td><td class="fw-semibold"><?=eDashboard($r['jenis_pengeluaran'])?></td><td><?=eDashboard($r['catatan']??'-')?></td><td><?=eDashboard($r['pencatat']??'-')?></td><td class="text-end fw-semibold"><?=rupiahDashboard((float)$r['jumlah'])?></td></tr><?php endforeach; endif; ?></tbody></table></div></div>
</section>
<?php else: ?>
<section class="panel mb-4"><div class="d-flex align-items-start gap-3"><span class="stat-icon icon-blue"><i class="bi bi-lock"></i></span><div><h2 class="panel-title">Informasi keuangan</h2><div class="panel-subtitle">Data pemasukan, pengeluaran, total pengeluaran, dan rincian keuangan hanya dapat dilihat oleh pemilik/pengelola utama.</div></div></div></section>
<?php endif; ?>
<footer class="app-footer">&copy; <?=date('Y')?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
</main></div>
<?php if($isOwner): ?><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script><script>
const chartKeys = <?=json_encode($chartKeys, JSON_UNESCAPED_UNICODE)?>;
const chartLabels = <?=json_encode($chartLabels, JSON_UNESCAPED_UNICODE)?>;
const pemasukan = <?=json_encode($chartPemasukan)?>;
const pengeluaran = <?=json_encode($chartPengeluaran)?>;
const selisih = <?=json_encode($chartSelisih)?>;
const formatRupiah = value => 'Rp ' + new Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:1}).format(value || 0);
const commonOptions = {responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.dataset.label+': Rp '+new Intl.NumberFormat('id-ID').format(c.parsed.y||0);}}}},scales:{y:{beginAtZero:true,ticks:{callback:function(v){return formatRupiah(v);}}}}};
new Chart(document.getElementById('incomeChart'),{type:'bar',data:{labels:chartLabels,datasets:[{label:'Pemasukan',data:pemasukan,backgroundColor:'rgba(40,167,69,.72)',borderColor:'rgb(40,167,69)',borderWidth:1,borderRadius:7}]},options:{...commonOptions,onClick:function(evt,elements){if(!elements.length)return;const i=elements[0].index;window.location.href='dashboard.php?bulan='+encodeURIComponent(chartKeys[i])+'#rekap-keuangan';}}});
new Chart(document.getElementById('expenseChart'),{type:'bar',data:{labels:chartLabels,datasets:[{label:'Pengeluaran',data:pengeluaran,backgroundColor:'rgba(255,159,67,.72)',borderColor:'rgb(255,159,67)',borderWidth:1,borderRadius:7}]},options:{...commonOptions,onClick:function(evt,elements){if(!elements.length)return;const i=elements[0].index;window.location.href='dashboard.php?bulan='+encodeURIComponent(chartKeys[i])+'#pengeluaran-bulan';}}});
</script><?php endif; ?>
</body></html>
