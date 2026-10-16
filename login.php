<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['id_user'])) {
    $tujuan = in_array($_SESSION['role'] ?? '', ['pengelola', 'admin'], true)
        ? 'admin/dashboard.php'
        : 'penghuni/dashboard.php';
    header('Location: ' . $tujuan);
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | KosSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-wrap">
        <section class="login-intro">
            <div class="brand">
                <span class="brand-mark"><i class="bi bi-house-heart-fill"></i></span>
                <span>KosSpace</span>
            </div>
            <div>
                <div class="eyebrow" style="color:#a9beff">Sistem Informasi Kos</div>
                <h1>Kelola kos jadi lebih mudah.</h1>
                <p>Semua informasi penting tentang hunian, pembayaran, dan kebutuhan penghuni dalam satu tempat.</p>
                <div class="feature-row"><i class="bi bi-house-check"></i><span>Informasi kamar lebih terorganisir</span></div>
                <div class="feature-row"><i class="bi bi-wallet2"></i><span>Pemantauan pembayaran lebih praktis</span></div>
                <div class="feature-row"><i class="bi bi-chat-square-text"></i><span>Komunikasi kebutuhan penghuni</span></div>
            </div>
            <div style="color:#8fa0c2;font-size:11px">Sistem Informasi Pengelolaan Kos Mahasiswa</div>
        </section>

        <section class="login-form-wrap">
            <div class="eyebrow">Selamat datang kembali</div>
            <h2>Masuk ke akunmu</h2>
            <p class="lead">Masukkan username dan password untuk melanjutkan.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                    <i class="bi bi-exclamation-circle-fill mt-1"></i>
                    <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST" autocomplete="on">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color:#dfe5ef;border-radius:10px 0 0 10px;color:#8a97aa"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" id="username" class="form-control" style="border-radius:0 10px 10px 0" placeholder="Masukkan username" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
                        <button class="password-toggle" type="button" id="togglePassword" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary login-submit">Masuk ke aplikasi <i class="bi bi-arrow-right ms-2"></i></button>
            </form>
            <div class="login-bottom">&copy; <?= date('Y') ?> KosSpace. Sistem Informasi Pengelolaan Kos.</div>
        </section>
    </div>
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const field = document.getElementById('password');
    const icon = this.querySelector('i');
    const visible = field.type === 'password';
    field.type = visible ? 'text' : 'password';
    icon.className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
    this.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
});
</script>
</body>
</html>
