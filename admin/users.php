<?php
require_once __DIR__ . '/../includes/auth.php';
wajibRole('pengelola');
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = '';
$jenisPesan = 'success';
$flash = $_GET['pesan'] ?? '';
if ($flash === 'tambah') $pesan = 'Akun admin berhasil dibuat.';
elseif ($flash === 'hapus') $pesan = 'Akun admin berhasil dihapus.';
elseif ($flash === 'tidak_ditemukan') { $pesan = 'Akun admin tidak ditemukan.'; $jenisPesan = 'warning'; }
elseif ($flash === 'gagal_hapus') { $pesan = 'Akun admin tidak dapat dihapus.'; $jenisPesan = 'warning'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $token = $_POST['csrf_token'] ?? '';
    $idUser = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
        $jenisPesan = 'danger';
    } elseif (!$idUser || $idUser < 1 || $idUser === (int)$_SESSION['id_user']) {
        $pesan = 'Akun admin yang sedang digunakan tidak dapat dihapus.';
        $jenisPesan = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_user, role FROM users WHERE id_user = :id LIMIT 1");
            $stmt->execute(['id' => $idUser]);
            $user = $stmt->fetch();

            if (!$user || $user['role'] !== 'admin') {
                header('Location: users.php?pesan=tidak_ditemukan');
                exit;
            }

           
            $cekPenghuni = $pdo->prepare('SELECT COUNT(*) FROM penghuni WHERE id_user = :id');
            $cekPenghuni->execute(['id' => $idUser]);
            if ((int)$cekPenghuni->fetchColumn() > 0) {
                header('Location: users.php?pesan=gagal_hapus');
                exit;
            }

            $hapus = $pdo->prepare("DELETE FROM users WHERE id_user = :id AND role = 'admin'");
            $hapus->execute(['id' => $idUser]);
            header('Location: users.php?pesan=' . ($hapus->rowCount() > 0 ? 'hapus' : 'tidak_ditemukan'));
            exit;
        } catch (PDOException $e) {
            $pesan = 'Akun admin gagal dihapus. Periksa relasi database.';
            $jenisPesan = 'danger';
        }
    }
}

$stmt = $pdo->query("SELECT id_user, nama, username FROM users WHERE role = 'admin' ORDER BY nama ASC");
$admins = $stmt->fetchAll();

function eUser($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manajemen Admin | KosSpace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell d-flex">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="app-main">
<header class="topbar">
<div><div class="topbar-kicker">Administrasi sistem</div><div class="topbar-title">Manajemen akun admin</div></div>
<div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?=eUser($_SESSION['nama'] ?? 'Pengelola')?></div><div class="user-role">Pengelola utama</div></div><a href="../logout.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Keluar</span></a></div>
</header>
<section class="page-heading"><div><div class="eyebrow">Manajemen pengguna</div><h1>Admin</h1><p>Tambahkan akun admin untuk membantu pemilik mengelola sistem kos.</p></div><a href="user_tambah.php" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Tambah Admin</a></section>
<?php if($pesan): ?><div class="alert alert-<?=eUser($jenisPesan)?> alert-dismissible fade show"><?=eUser($pesan)?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<section class="panel">
<div class="mb-4"><h2 class="panel-title">Daftar akun admin</h2><div class="panel-subtitle">Admin dapat menjalankan pengelolaan operasional seperti kamar, penghuni, pembayaran, dan keluhan.</div></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>No.</th><th>Nama</th><th>Username</th><th>Peran</th><th class="text-end">Aksi</th></tr></thead><tbody>
<?php if(!$admins): ?><tr><td colspan="5"><div class="empty-state"><i class="bi bi-person-badge"></i><strong class="d-block text-dark mb-1">Belum ada admin tambahan</strong><span>Buat akun admin yang membantu mengelola kos.</span></div></td></tr>
<?php else: foreach($admins as $i=>$admin): ?><tr><td><?=$i+1?></td><td class="fw-semibold"><?=eUser($admin['nama'])?></td><td><?=eUser($admin['username'])?></td><td><span class="soft-badge"><i class="bi bi-shield-check"></i>Admin</span></td><td class="text-end text-nowrap"><a href="reset_password.php?id_user=<?=(int)$admin['id_user']?>" class="btn btn-sm btn-outline-primary" title="Ganti username atau password"><i class="bi bi-person-gear"></i></a> <form method="post" class="d-inline" onsubmit="return confirm('Hapus akun admin ini? Akun tersebut tidak akan dapat login lagi.');"><input type="hidden" name="csrf_token" value="<?=eUser($_SESSION['csrf_token'])?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_user" value="<?=(int)$admin['id_user']?>"><button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash3"></i></button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></div>
</section>
<footer class="app-footer">&copy; <?=date('Y')?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer>
</main></div></body></html>
