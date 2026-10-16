<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$error = '';

$penghuni = $pdo->query("SELECT p.id_penghuni,p.nama,k.nomor_kamar,k.harga_sewa
    FROM penghuni p LEFT JOIN kamar k ON k.id_kamar=p.id_kamar
    ORDER BY p.nama ASC")->fetchAll();

$periodeOtomatis = date('Y-m-01');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $idPenghuni = filter_input(INPUT_POST, 'id_penghuni', FILTER_VALIDATE_INT);
    $tanggal = trim($_POST['tanggal_pembayaran'] ?? '');
    $periode = $periodeOtomatis;
    $jumlah = filter_var($_POST['jumlah_pembayaran'] ?? '', FILTER_VALIDATE_FLOAT);
    $status = $_POST['status_pembayaran'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } elseif (!$idPenghuni || $tanggal === '' || $periode === '' || $jumlah === false || $jumlah <= 0 || !in_array($status, ['lunas','belum_bayar'], true)) {
        $error = 'Semua data wajib diisi dengan benar.';
    } else {
        try {
            $cekPenghuni = $pdo->prepare("SELECT p.id_penghuni FROM penghuni p WHERE p.id_penghuni=:id");
            $cekPenghuni->execute(['id'=>$idPenghuni]);
            if (!$cekPenghuni->fetch()) throw new RuntimeException('Penghuni tidak ditemukan.');

            $cekPeriode = $pdo->prepare('SELECT COUNT(*) FROM pembayaran WHERE id_penghuni=:id AND periode_sewa=:periode');
            $cekPeriode->execute(['id'=>$idPenghuni,'periode'=>$periode]);
            if ((int)$cekPeriode->fetchColumn() > 0) throw new RuntimeException('Pembayaran untuk penghuni dan periode tersebut sudah tercatat. Gunakan menu Edit jika ingin memperbaruinya.');

            $s = $pdo->prepare('INSERT INTO pembayaran (id_penghuni,tanggal_pembayaran,periode_sewa,jumlah_pembayaran,status_pembayaran) VALUES (:id_penghuni,:tanggal_pembayaran,:periode_sewa,:jumlah_pembayaran,:status_pembayaran)');
            $s->execute(['id_penghuni'=>$idPenghuni,'tanggal_pembayaran'=>$tanggal,'periode_sewa'=>$periode,'jumlah_pembayaran'=>$jumlah,'status_pembayaran'=>$status]);
            require_once __DIR__ . '/../includes/pembayaran_helper.php';
            pastikanTagihanBulanIni($pdo);
            header('Location: pembayaran.php?pesan=tambah'); exit;
        } catch (RuntimeException $e) { $error = $e->getMessage(); }
        catch (PDOException $e) { $error = 'Pembayaran gagal disimpan. Periksa struktur tabel pembayaran.'; error_log($e->getMessage()); }
    }
}
function eForm($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tambah Pembayaran | KosSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="../assets/css/app.css" rel="stylesheet"></head><body><div class="app-shell d-flex"><?php include __DIR__.'/../includes/admin_sidebar.php';?><main class="app-main"><header class="topbar"><div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Tambah pembayaran</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?=eForm($_SESSION['nama']??'Pengelola')?></div><div class="user-role"><?= isPengelolaUtama() ? 'Pengelola utama' : 'Admin' ?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right"></i></a></div></header><section class="page-heading"><div><div class="eyebrow">Transaksi</div><h1>Catat Pembayaran</h1><p>Catat pembayaran sewa dari penghuni aktif.</p></div><a href="pembayaran.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a></section><?php if($error):?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?=eForm($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?=eForm($_SESSION['csrf_token'])?>"><div class="row g-3"><div class="col-lg-8"><section class="panel"><h2 class="panel-title">Informasi transaksi</h2><div class="panel-subtitle mb-4">Pilih penghuni aktif dan masukkan data pembayaran bulanannya.</div><div class="row g-3"><div class="col-12"><label class="form-label">Penghuni</label><select name="id_penghuni" class="form-select" required><option value="">Pilih penghuni aktif</option><?php foreach($penghuni as $p):?><option value="<?=$p['id_penghuni']?>" <?=((string)($_POST['id_penghuni']??'')===(string)$p['id_penghuni'])?'selected':''?>><?=eForm($p['nama'])?> · Kamar <?=eForm($p['nomor_kamar']??'-')?></option><?php endforeach;?></select><?php if(!$penghuni):?><div class="form-hint text-danger">Belum ada penghuni aktif. Tambahkan atau aktifkan penghuni terlebih dahulu.</div><?php endif;?></div><div class="col-md-6"><label class="form-label">Tanggal pembayaran</label><input type="date" name="tanggal_pembayaran" class="form-control" value="<?=eForm($_POST['tanggal_pembayaran']??date('Y-m-d'))?>" required></div><div class="col-md-6"><label class="form-label">Periode sewa</label><input class="form-control" value="<?=eForm($periodeOtomatis)?>" readonly><div class="form-hint">Periode dibuat otomatis berdasarkan bulan dan tahun saat ini.</div></div><div class="col-md-6"><label class="form-label">Jumlah pembayaran</label><input type="number" min="1" step="1" name="jumlah_pembayaran" class="form-control" placeholder="500000" value="<?=eForm($_POST['jumlah_pembayaran']??'')?>" required></div><div class="col-md-6"><label class="form-label">Status pembayaran</label><select name="status_pembayaran" class="form-select" required><option value="lunas" <?=($_POST['status_pembayaran']??'lunas')==='lunas'?'selected':''?>>Lunas</option><option value="belum_bayar" <?=($_POST['status_pembayaran']??'')==='belum_bayar'?'selected':''?>>Belum lunas</option></select></div></div></section></div><div class="col-lg-4"><section class="panel"><span class="stat-icon icon-blue mb-3"><i class="bi bi-wallet2"></i></span><h2 class="panel-title">Catatan</h2><div class="panel-subtitle mb-3">Pembayaran yang disimpan akan langsung muncul pada riwayat penghuni.</div><button class="btn btn-primary w-100" type="submit" <?=!$penghuni?'disabled':''?>><i class="bi bi-check2-circle me-2"></i>Simpan Pembayaran</button></section></div></div></form><footer class="app-footer">&copy; <?=date('Y')?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer></main></div></body></html>
