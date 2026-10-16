<?php
require_once __DIR__ . '/../includes/auth.php';
wajibRole('pengelola');
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: pengeluaran.php?pesan=tidak_ditemukan'); exit; }
$q = $pdo->prepare('SELECT * FROM pengeluaran WHERE id_pengeluaran=:id');
$q->execute(['id'=>$id]);
$data = $q->fetch();
if (!$data) { header('Location: pengeluaran.php?pesan=tidak_ditemukan'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $tanggal = trim($_POST['tanggal_pengeluaran'] ?? '');
    $jenis = trim($_POST['jenis_pengeluaran'] ?? '');
    $jumlah = filter_var($_POST['jumlah'] ?? '', FILTER_VALIDATE_FLOAT);
    $catatan = trim($_POST['catatan'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $token)) $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    elseif ($tanggal === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || $jenis === '' || mb_strlen($jenis) > 100 || $jumlah === false || $jumlah <= 0) $error = 'Tanggal, jenis pengeluaran, dan jumlah wajib diisi dengan benar.';
    else {
        try {
            $q = $pdo->prepare('UPDATE pengeluaran SET tanggal_pengeluaran=:tanggal, jenis_pengeluaran=:jenis, jumlah=:jumlah, catatan=:catatan WHERE id_pengeluaran=:id');
            $q->execute(['tanggal'=>$tanggal,'jenis'=>$jenis,'jumlah'=>$jumlah,'catatan'=>$catatan !== '' ? $catatan : null,'id'=>$id]);
            header('Location: pengeluaran.php?pesan=ubah'); exit;
        } catch (PDOException $e) { error_log($e->getMessage()); $error = 'Pengeluaran gagal diperbarui.'; }
    }
    $data['tanggal_pengeluaran'] = $tanggal; $data['jenis_pengeluaran'] = $jenis; $data['jumlah'] = $_POST['jumlah'] ?? ''; $data['catatan'] = $catatan;
}
function ePengeluaranForm($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Pengeluaran | KosSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="../assets/css/app.css" rel="stylesheet"></head><body><div class="app-shell d-flex"><?php include __DIR__.'/../includes/admin_sidebar.php'; ?><main class="app-main"><header class="topbar"><div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Edit pengeluaran</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?=ePengeluaranForm($_SESSION['nama']??'Pengelola')?></div><div class="user-role"><?=isPengelolaUtama()?'Pengelola utama':'Admin'?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right"></i></a></div></header><section class="page-heading"><div><div class="eyebrow">Keuangan operasional</div><h1>Edit Pengeluaran</h1><p>Perbarui catatan pengeluaran yang tersimpan.</p></div><a href="pengeluaran.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a></section><?php if($error):?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?=ePengeluaranForm($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?=ePengeluaranForm($_SESSION['csrf_token'])?>"><div class="row g-3"><div class="col-lg-8"><section class="panel"><h2 class="panel-title">Informasi pengeluaran</h2><div class="panel-subtitle mb-4">Perbarui tanggal, jenis, jumlah, atau catatan.</div><div class="row g-3"><div class="col-md-6"><label class="form-label">Tanggal pengeluaran</label><input type="date" name="tanggal_pengeluaran" class="form-control" value="<?=ePengeluaranForm($data['tanggal_pengeluaran'])?>" required></div><div class="col-md-6"><label class="form-label">Jenis pengeluaran</label><input type="text" name="jenis_pengeluaran" maxlength="100" class="form-control" value="<?=ePengeluaranForm($data['jenis_pengeluaran'])?>" required></div><div class="col-md-6"><label class="form-label">Jumlah</label><input type="number" min="1" step="1" name="jumlah" class="form-control" value="<?=ePengeluaranForm($data['jumlah'])?>" required></div><div class="col-12"><label class="form-label">Catatan</label><textarea name="catatan" class="form-control" rows="5" placeholder="Catatan perbaikan, penanganan keluhan, atau kebutuhan lainnya."><?=ePengeluaranForm($data['catatan']??'')?></textarea></div></div></section></div><div class="col-lg-4"><section class="panel"><span class="stat-icon icon-blue mb-3"><i class="bi bi-pencil-square"></i></span><h2 class="panel-title">Simpan perubahan</h2><div class="panel-subtitle mb-3">Data akan diperbarui di tabel pengeluaran.</div><button class="btn btn-primary w-100" type="submit"><i class="bi bi-check2-circle me-2"></i>Simpan Perubahan</button></section></div></div></form><footer class="app-footer">&copy; <?=date('Y')?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer></main></div></body></html>
