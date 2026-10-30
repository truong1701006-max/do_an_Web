<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Quản trị Văn Trường Luxury Mobile';
$admin = require_admin();
$cssVersion = filemtime(__DIR__ . '/../assets/css/style.css');
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$adminNav = [
    ['Tổng quan', 'admin/index.php', 'index.php', 'dashboard'],
    ['Sản phẩm', 'admin/products.php', 'products.php', 'inventory_2'],
    ['Danh mục', 'admin/categories.php', 'categories.php', 'category'],
    ['Đơn hàng', 'admin/orders.php', 'orders.php', 'receipt_long'],
    ['Khách hàng', 'admin/users.php', 'users.php', 'group'],
    ['Đánh giá', 'admin/reviews.php', 'reviews.php', 'reviews'],
    ['Kho hàng', 'admin/inventory.php', 'inventory.php', 'warehouse'],
    ['Báo cáo', 'admin/reports.php', 'reports.php', 'monitoring'],
];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@100..900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="<?= app_url('assets/css/style.css?v=' . $cssVersion) ?>">
</head>
<body class="admin-body">
<aside class="admin-sidebar">
    <a class="brand" href="<?= app_url('admin/index.php') ?>">
        <span class="brand-mark">VT</span>
        <span>Admin<small>Văn Trường Luxury</small></span>
    </a>
    <nav class="admin-menu" aria-label="Quản trị">
        <?php foreach ($adminNav as [$label, $href, $page, $icon]): ?>
            <a class="<?= $currentPage === $page ? 'active' : '' ?>" href="<?= app_url($href) ?>">
                <span class="material-symbols-outlined"><?= e($icon) ?></span>
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <a class="admin-store-link" href="<?= app_url('index.html') ?>">
        <span class="material-symbols-outlined">storefront</span>
        Về website
    </a>
</aside>
<section class="admin-shell">
    <header class="admin-topbar">
        <div>
            <p class="eyebrow">Bảng điều khiển</p>
            <h1><?= e($pageTitle) ?></h1>
        </div>
        <div class="admin-user">
            <span class="material-symbols-outlined">admin_panel_settings</span>
            <div>
                <strong><?= e($admin['full_name']) ?></strong>
                <a href="<?= app_url('admin/logout.php') ?>">Đăng xuất</a>
            </div>
        </div>
    </header>
    <?php if ($message = flash('success')): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert error"><?= e($message) ?></div>
    <?php endif; ?>
