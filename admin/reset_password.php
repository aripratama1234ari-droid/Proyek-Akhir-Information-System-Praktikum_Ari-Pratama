<?php
require_once __DIR__ . '/../includes/auth.php';
wajibRole('pengelola');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/password_vault.php';
$idTarget = filter_input(INPUT_GET, 'id_user', FILTER_VALIDATE_INT);
$error=''; $success=''; $target=null;
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
if (!$idTarget || $idTarget < 1) { $error='Akun tidak ditemukan.'; }
else { $st=$pdo->prepare("SELECT id_user,nama,username,role,password_terbuka FROM users WHERE id_user=:id AND role IN ('admin','penghuni') LIMIT 1"); $st->execute(['id'=>$idTarget]); $target=$st->fetch(); if(!$target) $error='Akun admin atau penghuni tidak ditemukan.'; }
if ($_SERVER['REQUEST_METHOD']==='POST' && $target) {
    $token=$_POST['csrf_token']??'';
    $usernameBaru=trim((string)($_POST['username_baru']??''));
    $baru=(string)($_POST['password_baru']??'');
    $konf=(string)($_POST['konfirmasi_password']??'');
    if(!hash_equals($_SESSION['csrf_token'],$token)) $error='Permintaan tidak valid. Silakan muat ulang halaman.';
    elseif($usernameBaru==='') $error='Username tidak boleh kosong.';
    elseif(strlen($usernameBaru)<4 || strlen($usernameBaru)>50) $error='Username harus terdiri dari 4 sampai 50 karakter.';
    elseif(!preg_match('/^[A-Za-z0-9._-]+$/', $usernameBaru)) $error='Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.';
    elseif($baru!=='' && strlen($baru)<8) $error='Password baru minimal 8 karakter.';
    elseif($baru!==$konf) $error='Konfirmasi password baru tidak cocok.';
    else {
        $cek=$pdo->prepare('SELECT COUNT(*) FROM users WHERE username=:username AND id_user<>:id');
        $cek->execute(['username'=>$usernameBaru,'id'=>$idTarget]);
        if((int)$cek->fetchColumn()>0) $error='Username tersebut sudah digunakan oleh pengguna lain.';
        else {
            try {
                if($baru!=='') {
                    $hash=password_hash($baru,PASSWORD_DEFAULT);
                    $passwordTerenkripsi=enkripsiPasswordUntukPengelola($baru);
                    $up=$pdo->prepare('UPDATE users SET username=:username, password=:password, password_terbuka=:password_terbuka WHERE id_user=:id');
                    $up->execute(['username'=>$usernameBaru,'password'=>$hash,'password_terbuka'=>$passwordTerenkripsi,'id'=>$idTarget]);
                    $aksi='Mengganti username dan password pengguna';
                } else {
                    $up=$pdo->prepare('UPDATE users SET username=:username WHERE id_user=:id');
                    $up->execute(['username'=>$usernameBaru,'id'=>$idTarget]);
                    $aksi='Mengganti username pengguna';
                }
                try { $log=$pdo->prepare('INSERT INTO activity_logs (id_user, aktivitas, modul, data_diproses) VALUES (:id,:aktivitas,:modul,:data)'); $log->execute(['id'=>$_SESSION['id_user'],'aktivitas'=>$aksi,'modul'=>'Manajemen Pengguna','data'=>$target['username'].' → '.$usernameBaru.' ('.$target['role'].')']); } catch(PDOException $ignored) {}
                $success=($baru!==''?'Username dan password':'Username').' akun '.$target['nama'].' berhasil diperbarui.';
                $target['username']=$usernameBaru;
                if($baru!=='') $target['password_terbuka']=enkripsiPasswordUntukPengelola($baru);
                $_SESSION['csrf_token']=bin2hex(random_bytes(32));
            } catch(PDOException $e) { $error='Perubahan akun gagal disimpan. Pastikan username belum digunakan.'; }
        }
    }
}
function eReset($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kelola Akun Pengguna | KosSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="../assets/css/app.css" rel="stylesheet"></head>
<body><div class="app-shell d-flex"><?php include __DIR__.'/../includes/admin_sidebar.php'; ?><main class="app-main"><header class="topbar"><div><div class="topbar-kicker">Manajemen pengguna</div><div class="topbar-title">Kelola akun pengguna</div></div><div class="user-chip"><div class="avatar"><i class="bi bi-person-fill"></i></div><div class="d-none d-sm-block"><div class="user-name"><?=eReset($_SESSION['nama']??'Pengelola')?></div><div class="user-role">Pengelola utama</div></div></div></header>
<section class="page-heading"><div><div class="eyebrow">Keamanan akun</div><h1>Kelola Akun Pengguna</h1><p>Pengelola dapat melihat password tersimpan dan mengubah username atau password akun admin maupun penghuni.</p></div><a href="<?= $target && $target['role']==='admin' ? 'users.php' : 'penghuni.php' ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a></section>
<?php if($error):?><div class="alert alert-danger"><?=eReset($error)?></div><?php endif;?><?php if($success):?><div class="alert alert-success"><?=eReset($success)?></div><?php endif;?>
<?php if($target):?><section class="panel" style="max-width:760px"><div class="d-flex gap-3 align-items-center mb-4"><span class="stat-icon icon-blue"><i class="bi bi-person-gear"></i></span><div><h2 class="panel-title mb-1"><?=eReset($target['nama'])?></h2><div class="panel-subtitle">Peran: <?=eReset($target['role'])?> · Username saat ini: @<?=eReset($target['username'])?></div></div></div><?php $passwordSaatIni = dekripsiPasswordUntukPengelola($target['password_terbuka'] ?? null); ?><div class="alert alert-warning"><strong>Password saat ini:</strong> <?php if($passwordSaatIni !== null): ?><span id="passwordLamaTampil" data-password="<?=eReset($passwordSaatIni)?>">••••••••</span> <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="const p=document.getElementById('passwordLamaTampil');const visible=p.dataset.visible==='1';p.textContent=visible?'••••••••':p.dataset.password;p.dataset.visible=visible?'0':'1';">Tampilkan/Sembunyikan</button><?php else: ?><span>Belum tersedia untuk akun lama.</span><div class="small mt-1">Password dibuat sebelum fitur ini ditambahkan sehingga tidak bisa dipulihkan dari hash. Atur password baru untuk mulai menyimpan salinan terenkripsi.</div><?php endif; ?></div><form method="post"><input type="hidden" name="csrf_token" value="<?=eReset($_SESSION['csrf_token'])?>"><div class="mb-3"><label class="form-label" for="username_baru">Username baru</label><input id="username_baru" type="text" name="username_baru" class="form-control" minlength="4" maxlength="50" pattern="[A-Za-z0-9._-]+" value="<?=eReset($target['username'])?>" required autocomplete="off"><div class="form-hint">4–50 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.</div></div><hr class="my-4"><div class="mb-3"><label class="form-label" for="password_baru">Password baru <span class="text-secondary">(opsional)</span></label><input id="password_baru" type="password" name="password_baru" class="form-control" minlength="8" autocomplete="new-password"><div class="form-hint">Kosongkan jika hanya ingin mengganti username. Jika diisi, minimal 8 karakter.</div></div><div class="mb-4"><label class="form-label" for="konfirmasi_password">Konfirmasi password baru</label><input id="konfirmasi_password" type="password" name="konfirmasi_password" class="form-control" minlength="8" autocomplete="new-password"><div class="form-hint">Harus sama dengan password baru jika kamu menggantinya.</div></div><button class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Perubahan Akun</button></form></section><?php endif;?>
<footer class="app-footer">&copy; <?=date('Y')?> KosSpace · Sistem Informasi Pengelolaan Kos Mahasiswa</footer></main></div></body></html>
