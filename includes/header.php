<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Văn Trường Luxury Mobile - Thế Giới Flagship Chính Hãng';
$categories = db()->query('SELECT * FROM categories WHERE status = 1 ORDER BY category_name')->fetchAll();
$user = current_user();
$cssVersion = filemtime(__DIR__ . '/../assets/css/style.css');
$jsVersion = filemtime(__DIR__ . '/../assets/js/main.js');
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$navItems = [
    ['label' => 'Trang chủ', 'href' => 'index.php', 'pages' => ['index.php']],
    ['label' => 'Danh mục', 'href' => 'categories.php', 'pages' => ['categories.php']],
    ['label' => 'Sản phẩm', 'href' => 'products.php', 'pages' => ['products.php', 'product-detail.php']],
    ['label' => 'So sánh', 'href' => 'compare.php', 'pages' => ['compare.php']],
    ['label' => 'Bảo hành', 'href' => 'warranty.php', 'pages' => ['warranty.php', 'returns.php']],
    ['label' => 'Hỗ trợ', 'href' => 'contact.php', 'pages' => ['contact.php']],
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
<body data-page="<?= e($currentPage) ?>">
<header class="site-header">
    <div class="topbar">
        <span class="material-symbols-outlined">verified</span>
        <span>Giao nhanh 2h miễn phí toàn quốc · Trả góp 0% · Thu cũ đổi mới trợ giá đến 4.000.000đ · Văn Trường Luxury</span>
    </div>
    <div class="header-main">
        <a class="brand" href="<?= app_url('index.php') ?>" aria-label="Văn Trường Luxury Mobile">
            <span class="brand-mark">VT</span>
            <span>
                Văn Trường
                <small>Luxury Mobile Flagship</small>
            </span>
        </a>

        <button class="mobile-menu-toggle" type="button" aria-label="Mở menu" aria-expanded="false">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <nav class="primary-nav" aria-label="Điều hướng chính">
            <?php foreach ($navItems as $item): ?>
                <a class="<?= in_array($currentPage, $item['pages'], true) ? 'active' : '' ?>" href="<?= app_url($item['href']) ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <form class="search-form" action="<?= app_url('products.php') ?>" method="get" role="search">
            <span class="material-symbols-outlined">search</span>
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm iPhone 16 Pro, AirPods...">
            <button type="submit" aria-label="Tìm kiếm">
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <nav class="account-nav" aria-label="Tài khoản">
            <?php if ($user): ?>
                <a class="icon-link" href="<?= app_url('profile.php') ?>" title="<?= e($user['full_name']) ?>">
                    <span class="material-symbols-outlined">person</span>
                    <span class="account-label"><?= e($user['full_name']) ?></span>
                </a>
                <a class="icon-link" href="<?= app_url('logout.php') ?>" title="Đăng xuất">
                    <span class="material-symbols-outlined">logout</span>
                </a>
            <?php else: ?>
                <a class="icon-link" href="<?= app_url('login.php') ?>">
                    <span class="material-symbols-outlined">person</span>
                    <span class="account-label">Đăng nhập</span>
                </a>
            <?php endif; ?>
            <a class="cart-link" href="<?= app_url('cart.php') ?>" aria-label="Giỏ hàng">
                <span class="material-symbols-outlined">shopping_bag</span>
                <strong id="cartCountBadge"><?= cart_count() ?></strong>
            </a>
        </nav>
    </div>
    <nav class="category-nav" aria-label="Danh mục nhanh">
        <a class="<?= $currentPage === 'products.php' && empty($_GET['category_id']) ? 'active' : '' ?>" href="<?= app_url('products.php') ?>">Tất cả</a>
        <?php foreach ($categories as $category): ?>
            <a class="<?= (string) ($_GET['category_id'] ?? '') === (string) $category['category_id'] ? 'active' : '' ?>" href="<?= app_url('products.php?category_id=' . (int) $category['category_id']) ?>">
                <?= e($category['category_name']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>
<main>
<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>
