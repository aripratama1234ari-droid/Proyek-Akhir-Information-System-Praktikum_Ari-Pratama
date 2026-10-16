<?php
$adminPage = basename($_SERVER['PHP_SELF'] ?? '');
$activeDash = $adminPage === 'dashboard.php' ? 'active' : '';
$activeKamar = $adminPage === 'kamar.php' || str_starts_with($adminPage, 'kamar_') ? 'active' : '';
$activePenghuni = $adminPage === 'penghuni.php' || str_starts_with($adminPage, 'penghuni_') ? 'active' : '';
$activePembayaran = $adminPage === 'pembayaran.php' || str_starts_with($adminPage, 'pembayaran_') ? 'active' : '';
$activeKeluhan = $adminPage === 'keluhan.php' ? 'active' : '';
$activePengeluaran = $adminPage === 'pengeluaran.php' || str_starts_with($adminPage, 'pengeluaran_') ? 'active' : '';
?>
<aside class="sidebar">
<a class="brand" href="dashboard.php"><span class="brand-mark"><i class="bi bi-house-heart-fill"></i></span><span>KosSpace</span></a>
<div class="sidebar-label">Menu utama</div>
<nav class="side-nav">
<a class="<?= $activeDash ?>" href="dashboard.php"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a>
<a class="<?= $activeKamar ?>" href="kamar.php"><i class="bi bi-door-closed"></i>Data kamar</a>
<a class="<?= $activePenghuni ?>" href="penghuni.php"><i class="bi bi-people"></i>Data penghuni</a>
<a class="<?= $activePembayaran ?>" href="pembayaran.php"><i class="bi bi-wallet2"></i>Pembayaran</a>
<a class="<?= $activeKeluhan ?>" href="keluhan.php"><i class="bi bi-chat-square-text"></i>Keluhan</a>
<a class="<?= $adminPage === 'laporan.php' ? 'active' : '' ?>" href="laporan.php"><i class="bi bi-file-earmark-bar-graph"></i>Laporan</a>
<?php if (isPengelolaUtama()): ?>
<a class="<?= $activePengeluaran ?>" href="pengeluaran.php"><i class="bi bi-cash-stack"></i>Pengeluaran</a>
<?php $activeUsers = in_array($adminPage, ['users.php','user_tambah.php','reset_password.php'], true) ? 'active' : ''; ?>
<a class="<?= $activeUsers ?>" href="users.php"><i class="bi bi-person-badge"></i>Admin</a>
<a class="<?= $adminPage === 'riwayat_aktivitas.php' ? 'active' : '' ?>" href="riwayat_aktivitas.php"><i class="bi bi-clock-history"></i>Riwayat aktivitas</a>
<?php endif; ?>
<?php $activePassword = $adminPage === 'ganti_password.php' ? 'active' : ''; ?>
<a class="<?= $activePassword ?>" href="ganti_password.php"><i class="bi bi-shield-lock"></i>Ganti username & password</a>
</nav>
<div class="sidebar-footer"><i class="bi bi-shield-check me-1"></i> Portal Pengelola & Admin<br>Sistem Informasi Pengelolaan Kos Mahasiswa</div>
</aside>
