<?php
require_once __DIR__ . '/../includes/auth.php';
wajibAdminArea();
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$idPenghuni = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPenghuni) { header('Location: penghuni.php?pesan=tidak_ditemukan'); exit; }

$ambil = $pdo->prepare('SELECT p.*, u.username FROM penghuni p LEFT JOIN users u ON p.id_user = u.id_user WHERE p.id_penghuni = :id LIMIT 1');
$ambil->execute(['id'=>$idPenghuni]);
$penghuni = $ambil->fetch();
if (!$penghuni) { header('Location: penghuni.php?pesan=tidak_ditemukan'); exit; }

$kamarStmt = $pdo->query("SELECT k.id_kamar, k.nomor_kamar, k.harga_sewa, k.status_kamar,
    (SELECT COUNT(*) FROM penghuni p2 WHERE p2.id_kamar = k.id_kamar AND p2.status_penghuni = 'aktif') AS jumlah_penghuni
    FROM kamar k ORDER BY k.nomor_kamar ASC");
$semuaKamar = $kamarStmt->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nomor_identitas_mahasiswa'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $tanggal = trim($_POST['tanggal_mulai_tinggal'] ?? '');
    $idKamarBaru = filter_input(INPUT_POST, 'id_kamar', FILTER_VALIDATE_INT);
    $status = $penghuni['status_penghuni']; 

    if (!hash_equals($_SESSION['csrf_token'], $token)) $error='Permintaan tidak valid. Silakan muat ulang halaman.';
    elseif ($nama==='' || $nim==='' || $telepon==='' || $tanggal==='' || !$idKamarBaru) $error='Semua data wajib diisi dengan benar.';
    else {
        try {
            $pdo->beginTransaction();
            $lama = $pdo->prepare('SELECT id_kamar FROM penghuni WHERE id_penghuni = :id FOR UPDATE');
            $lama->execute(['id'=>$idPenghuni]);
            $dataLama=$lama->fetch();
            if (!$dataLama) throw new RuntimeException('Data penghuni tidak ditemukan.');

            $cekKamar = $pdo->prepare('SELECT id_kamar FROM kamar WHERE id_kamar = :id FOR UPDATE');
            $cekKamar->execute(['id'=>$idKamarBaru]);
            if (!$cekKamar->fetch()) throw new RuntimeException('Kamar yang dipilih tidak ditemukan.');

            
            $ubah = $pdo->prepare('UPDATE penghuni SET id_kamar=:id_kamar, nama=:nama, nomor_identitas_mahasiswa=:nim, no_telepon=:telepon, tanggal_mulai_tinggal=:tanggal, status_penghuni=:status WHERE id_penghuni=:id');
            $ubah->execute(['id_kamar'=>$idKamarBaru,'nama'=>$nama,'nim'=>$nim,'telepon'=>$telepon,'tanggal'=>$tanggal,'status'=>$status,'id'=>$idPenghuni]);

            $ubahUser = $pdo->prepare('UPDATE users SET nama=:nama WHERE id_user=:id_user');
            $ubahUser->execute(['nama'=>$nama,'id_user'=>$penghuni['id_user']]);

            
            $kamarYangDisinkronkan = array_unique([(int)$dataLama['id_kamar'], (int)$idKamarBaru]);
            foreach ($kamarYangDisinkronkan as $idKamarSync) {
                if ($idKamarSync < 1) continue;
                $cekAktif = $pdo->prepare("SELECT COUNT(*) FROM penghuni WHERE id_kamar=:id AND status_penghuni='aktif'");
                $cekAktif->execute(['id'=>$idKamarSync]);
                $adaAktif = (int)$cekAktif->fetchColumn() > 0;
                $statusKamar = $adaAktif ? 'terisi' : 'tersedia';
                $s = $pdo->prepare('UPDATE kamar SET status_kamar=:status WHERE id_kamar=:id');
                $s->execute(['status'=>$statusKamar,'id'=>$idKamarSync]);
            }

            $pdo->commit();
            require_once __DIR__ . '/../includes/pembayaran_helper.php';
            pastikanTagihanBulanIni($pdo);
            header('Location: penghuni.php?pesan=ubah'); exit;
        } catch (RuntimeException $e) { if($pdo->inTransaction())$pdo->rollBack(); $error=$e->getMessage(); }
        catch(PDOException $e){ if($pdo->inTransaction())$pdo->rollBack(); $error='Data gagal diperbarui. Periksa struktur database dan data yang dimasukkan.'; }
    }
}
function eEdit($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Penghuni | KosSpace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/app.css" rel="stylesheet">
</head>
<body><div class="app-shell d-flex">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="app-main"><header class="topbar"><div><div class="topbar-kicker">Workspace / Operasional</div><div class="topbar-title">Edit data penghuni</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?= eEdit($_SESSION['nama'] ?? 'Pengelola') ?></div><div class="user-role"><?= isPengelolaUtama() ? 'Pengelola utama' : 'Admin' ?></div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right"></i></a></div></header>
<section class="page-heading"><div><div class="eyebrow">Data penghuni</div><h1>Edit Penghuni</h1><p>Perbarui data penghuni dan penempatan kamar. Satu kamar dapat ditempati beberapa mahasiswa.</p></div><a href="penghuni.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a></section>
<?php if($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= eEdit($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= eEdit($_SESSION['csrf_token']) ?>"><div class="row g-3">
<div class="col-lg-7"><div class="panel"><h2 class="panel-title">Informasi penghuni</h2><div class="panel-subtitle mb-4">Data identitas dan kamar yang ditempati.</div><div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama lengkap</label><input class="form-control" name="nama" value="<?= eEdit($_POST['nama'] ?? $penghuni['nama']) ?>" required></div>
<div class="col-md-6"><label class="form-label">NIM / nomor identitas mahasiswa</label><input class="form-control" name="nomor_identitas_mahasiswa" value="<?= eEdit($_POST['nomor_identitas_mahasiswa'] ?? $penghuni['nomor_identitas_mahasiswa']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Nomor telepon</label><input class="form-control" name="no_telepon" value="<?= eEdit($_POST['no_telepon'] ?? $penghuni['no_telepon']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Tanggal mulai tinggal</label><input type="date" class="form-control" name="tanggal_mulai_tinggal" value="<?= eEdit($_POST['tanggal_mulai_tinggal'] ?? $penghuni['tanggal_mulai_tinggal']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Status penghuni</label><input class="form-control" value="<?= eEdit($penghuni['status_penghuni'] === 'aktif' ? 'Aktif' : 'Nonaktif') ?>" disabled><div class="form-hint">Status otomatis mengikuti pembayaran bulan berjalan: aktif jika lunas, nonaktif jika belum lunas.</div></div>
<div class="col-md-6"><label class="form-label">Kamar</label><select class="form-select" name="id_kamar" required><?php foreach($semuaKamar as $k): $selected=(string)($_POST['id_kamar'] ?? $penghuni['id_kamar'])===(string)$k['id_kamar']; $jumlah=(int)$k['jumlah_penghuni']; ?><option value="<?= (int)$k['id_kamar'] ?>" <?= $selected?'selected':'' ?>>Kamar <?= eEdit($k['nomor_kamar']) ?> · <?= $jumlah > 0 ? $jumlah . ' penghuni aktif' : 'Belum ada penghuni' ?> · Rp <?= number_format((float)$k['harga_sewa'],0,',','.') ?>/bulan</option><?php endforeach; ?></select><div class="form-hint">Kamar yang sudah terisi tetap dapat dipilih. Tidak ada batas jumlah penghuni pada sistem.</div></div>
</div></div></div>
<div class="col-lg-5"><div class="panel"><h2 class="panel-title">Akun penghuni</h2><div class="panel-subtitle mb-4">Informasi akun login yang terhubung.</div><div class="mb-3"><label class="form-label">Username</label><input class="form-control" value="<?= eEdit($penghuni['username'] ?? '-') ?>" disabled></div><div class="form-hint mb-4">Password dapat diubah sendiri oleh penghuni melalui menu Pengaturan Akun.</div><div class="quick-link"><i class="bi bi-person-badge"></i><span class="flex-grow-1">ID penghuni</span><strong>#<?= (int)$penghuni['id_penghuni'] ?></strong></div><div class="alert alert-info py-2 small mt-3"><i class="bi bi-people me-1"></i>Kamar yang sama dapat menampung beberapa mahasiswa aktif.</div><button class="btn btn-primary w-100 mt-2" type="submit"><i class="bi bi-check2-circle me-2"></i>Simpan Perubahan</button></div></div>
</div></form><footer class="app-footer">&copy; <?= date('Y') ?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer></main></div>
</body></html>
