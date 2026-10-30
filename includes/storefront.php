<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function storefront_categories(): array
{
    try {
        return db()->query('SELECT category_id, category_name FROM categories WHERE status = 1 ORDER BY category_id LIMIT 6')->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

function render_store_header(string $pageTitle, string $active = ''): void
{
    $user = current_user();
    $categories = storefront_categories();
    $cssVersion = is_file(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time();
    $jsVersion = is_file(__DIR__ . '/../assets/js/main.js') ? filemtime(__DIR__ . '/../assets/js/main.js') : time();
    $nav = [
        ['Trang chủ', 'index.html', 'home'],
        ['Sản phẩm', 'products.html', 'products'],
        ['Giới thiệu', 'about.html', 'about'],
        ['Hỗ trợ', 'contact.html', 'contact'],
    ];
    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= app_url('assets/css/style.css?v=' . $cssVersion) ?>">
</head>
<body>
  <div class="topbar">Giao nhanh 2h miễn phí toàn quốc • Trả góp 0% • Thu cũ đổi mới trợ giá đến 3.000.000đ</div>

  <header class="site-header">
    <div class="header-main">
      <a class="brand" href="<?= app_url('index.html') ?>" aria-label="Văn Trường Luxury Mobile">
        <span class="brand-mark">VT</span>
        <span>Văn Trường <small>Luxury Mobile Flagship</small></span>
      </a>

      <nav class="primary-nav" aria-label="Điều hướng chính">
        <?php foreach ($nav as [$label, $href, $key]): ?>
          <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= app_url($href) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <form class="search-form" action="<?= app_url('search.html') ?>" role="search">
        <span class="material-symbols-outlined">search</span>
        <input name="q" type="search" placeholder="Tìm iPhone, AirPods...">
        <button type="submit" aria-label="Tìm kiếm"><span class="material-symbols-outlined">arrow_forward</span></button>
      </form>

      <nav class="account-nav" aria-label="Tài khoản">
        <a class="cart-link" href="<?= app_url('cart.php') ?>" aria-label="Giỏ hàng">
          <span class="material-symbols-outlined">shopping_bag</span>
          <strong id="cartCountBadge"><?= cart_count() ?></strong>
        </a>
        <a class="icon-link" href="<?= app_url($user ? 'profile.php' : 'login.php') ?>">
          <span class="material-symbols-outlined">account_circle</span>
          <span class="account-label"><?= e($user['full_name'] ?? 'Đăng nhập') ?></span>
        </a>
      </nav>

      <button class="mobile-menu-toggle" type="button" aria-label="Mở menu" aria-expanded="false">
        <span class="material-symbols-outlined">menu</span>
      </button>
    </div>

    <nav class="category-nav" aria-label="Danh mục nhanh">
      <a href="<?= app_url('products.html') ?>">Tất cả</a>
      <?php foreach ($categories as $category): ?>
        <a href="<?= app_url('products.html') ?>"><?= e($category['category_name']) ?></a>
      <?php endforeach; ?>
      <a href="<?= app_url('compare.html') ?>">So sánh</a>
      <a href="<?= app_url('warranty.html') ?>">Bảo hành</a>
    </nav>
  </header>

  <main>
    <?php if ($message = flash('success')): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="alert error"><?= e($message) ?></div><?php endif; ?>
    <?php
}

function render_store_footer(): void
{
    $jsVersion = is_file(__DIR__ . '/../assets/js/main.js') ? filemtime(__DIR__ . '/../assets/js/main.js') : time();
    ?>
  </main>
  <footer class="site-footer">
    <div>
      <a class="footer-brand" href="<?= app_url('index.html') ?>"><span class="brand-mark">VT</span><span>Văn Trường <small>Luxury Mobile Flagship</small></span></a>
      <p>Chuyên Flagship & iPhone chính hãng, máy tuyển chọn Like New và thu cũ đổi mới với quy trình minh bạch, ưu đãi tốt nhất.</p>
    </div>
    <div>
      <h4>Mua hàng</h4>
      <a href="<?= app_url('products.html') ?>">Sản phẩm</a>
      <a href="<?= app_url('cart.php') ?>">Giỏ hàng</a>
      <a href="<?= app_url('my-orders.html') ?>">Đơn hàng</a>
    </div>
    <div>
      <h4>Chính sách</h4>
      <a href="<?= app_url('warranty.html') ?>">Bảo hành VIP</a>
      <a href="<?= app_url('returns.html') ?>">Đổi trả 1-đổi-1</a>
      <a href="<?= app_url('contact.html') ?>">Liên hệ CSKH</a>
    </div>
    <div>
      <h4>Showroom</h4>
      <p>256 Cầu Giấy, Quan Hoa, Cầu Giấy, Hà Nội</p>
      <p>Hotline: 0988 123 456</p>
    </div>
  </footer>
  <script src="<?= app_url('assets/js/main.js?v=' . $jsVersion) ?>"></script>
</body>
</html>
    <?php
}

function active_coupon_for_subtotal(float $subtotal): ?array
{
    $stmt = db()->prepare("
        SELECT *
        FROM coupons
        WHERE status = 1
          AND start_date <= NOW()
          AND end_date >= NOW()
          AND min_order <= ?
          AND (usage_limit = 0 OR used_count < usage_limit)
        ORDER BY discount_value DESC
        LIMIT 1
    ");
    $stmt->execute([$subtotal]);
    return $stmt->fetch() ?: null;
}

function coupon_discount(?array $coupon, float $subtotal): float
{
    if (!$coupon) {
        return 0;
    }

    if (($coupon['discount_type'] ?? '') === 'percent') {
        return min($subtotal, $subtotal * ((float) $coupon['discount_value'] / 100));
    }

    return min($subtotal, (float) $coupon['discount_value']);
}

function suggested_products(int $limit = 4): array
{
    $stmt = db()->prepare("
        SELECT pv.*, p.product_name, p.description, p.image, p.warranty_months, c.category_name
        FROM product_variants pv
        JOIN products p ON p.product_id = pv.product_id
        JOIN categories c ON c.category_id = p.category_id
        WHERE p.status = 1 AND pv.stock_quantity > 0
        ORDER BY p.is_featured DESC, p.created_at DESC, pv.sale_price IS NULL, pv.sale_price ASC, pv.price ASC
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
