<?php
require_once __DIR__ . '/../includes/functions.php';

if (current_admin()) {
    redirect('admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? AND status = 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = (int) $admin['admin_id'];
        flash('success', 'Đăng nhập quản trị thành công.');
        redirect('admin/index.php');
    }

    flash('error', 'Tài khoản hoặc mật khẩu admin không đúng.');
    redirect('admin/login.php');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập Quản trị - Văn Trường Luxury Mobile</title>
    <link rel="stylesheet" href="<?= app_url('assets/css/style.css') ?>">
</head>
<body>
<section class="auth-page">
    <form class="auth-card form-grid" method="post">
        <h1>Admin Văn Trường Luxury</h1>
        <?php if ($message = flash('error')): ?><div class="alert error"><?= e($message) ?></div><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <label>Tên đăng nhập
            <input name="username" required autofocus>
        </label>
        <label>Mật khẩu
            <input type="password" name="password" required>
        </label>
        <button type="submit">Đăng nhập</button>
        <p class="meta">Tài khoản mẫu: admin / admin123</p>
    </form>
</section>
</body>
</html>
