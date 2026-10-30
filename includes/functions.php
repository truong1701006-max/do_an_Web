<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = __DIR__ . '/../storage/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);
    session_start();
}

function app_base(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = preg_replace('#/admin$#', '', $dir) ?: '';
    return rtrim($dir, '/');
}

function app_url(string $path = ''): string
{
    return app_base() . '/' . ltrim($path, '/');
}

function query_url(string $path, array $overrides = []): string
{
    $query = $_GET;
    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }

    $queryString = http_build_query($query);
    return app_url($path . ($queryString ? '?' . $queryString : ''));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function format_price(float|int|string|null $price): string
{
    return number_format((float) $price, 0, ',', '.') . 'đ';
}

function media_url(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return app_url('assets/images/iphone-dark.svg');
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return app_url($path);
}

function product_image_map(): array
{
    return [
        'iphone 13' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB23Nyn-jm3DeSgjHMc8AG-MmR4PtgJtvyT526FXLp0rH5UkGeCDUOuIxFdOWOy2-6YV_xd7C-G9lNJ8JmyxOPvU1Tr9y7RD9WffGsMJRLWFiuU1GDC44CouMWnfmc1wmhX6T13LfAqNTFUUuRsu-oeXOw5zLyUaMol49eFQz2Nt1dWsXAypLZEE2i6fAbUundN6svFK0lpZmamYl5czaxcBpih28ij4DFcHv867ZM',
        'iphone 14 pro' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCeGP95H38ZQn757vdUzX8TiAPqMQU2WqWN9sDfimRn88oA68pTlUBLxmy4Trm932AKo0ehbethEKEcNUIWumM5CXdOM5Z4Ip6Hg0TvBfu6WUBVpNhhoi4STceZ2_ZBi56zgSvRfM_6wB0XSoDItSMfjTNTVn9AjzhqaC6PYjyMWOaZ7nRzPqicazrKgjDpDFDzH4c8yZ3QeiEUml8UFppIiYoOOQ2UPYGF76YsPoA',
        'iphone 14' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB6tpe2WMmYcnUGlmhOnii5pyQRr6Me_dvq-ogFw3bTxOH8zEJqver-01DbfgMxadn6VwdjTgZYV3NLiGjXbpgoCEAg5rAJDlM5eYhA0gxk-484upwFnkUc_SLltdS5UCE11PHNnZAa88HU-AhmucZlZwF_WPlS9ofAGnEG5GhnnZrswe5Npg7ZkfRiGWNeXmX1lc_NRhqkn0UvEuzH9_60Qa7N3xxsuVEH7P6wUts',
        'iphone 15 pro max' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDszWEybAcrOFRnLqVGFy8BcjDQwNbhdSPRE-VGqh6MKQwy7xrNJSN1QC_rXSmFV7SUB5MkPF52lxP8pUvkRvPi-33sNX6fPNah3Z49o8NWIkG76PdPOQvWsZ2uWgVAlYt7-krUmOrIjM7d8MyzFxPgmvYP0tWRO-1gdb2jCz0rMXYcyTebv5kbkU6wHs1UDyMGg4c1fb1aje6JF7aGK8n4Boivy_b2nfXR_v9Jd2Y',
        'iphone 15' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD6lShy95e8bVCxoZh-DIdqzuRUHnX2vQwUeN6HlxZxXoqr_VCIzirqbv5XSLQsePswh2Adi-JHBay_8VP_0eTvA16Mh2mnjbp52XpngRuqCVh2SSjTz3Ptyo-20skci6PMuAMO00uy3SBhqgh5TuaZPyOQMQC9-aHGkNlRqr6STV1u98co5EdOWyV9gwcO35-olAnctJECtfD-Egxb1RfY4Yls8O6m4HglsGL62Rc',
        'iphone 16' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCeGP95H38ZQn757vdUzX8TiAPqMQU2WqWN9sDfimRn88oA68pTlUBLxmy4Trm932AKo0ehbethEKEcNUIWumM5CXdOM5Z4Ip6Hg0TvBfu6WUBVpNhhoi4STceZ2_ZBi56zgSvRfM_6wB0XSoDItSMfjTNTVn9AjzhqaC6PYjyMWOaZ7nRzPqicazrKgjDpDFDzH4c8yZ3QeiEUml8UFppIiYoOOQ2UPYGF76YsPoA',
    ];
}

function product_image_url(array $product): string
{
    $name = strtolower((string) ($product['product_name'] ?? ''));
    foreach (product_image_map() as $needle => $url) {
        if (str_contains($name, $needle)) {
            return $url;
        }
    }

    return media_url($product['image'] ?? '');
}

function category_image_url(array $category): string
{
    $name = strtolower((string) ($category['category_name'] ?? ''));
    foreach (product_image_map() as $needle => $url) {
        if (str_contains($name, $needle)) {
            return $url;
        }
    }

    return product_image_map()['iphone 16'];
}

function product_gallery_images(array $product): array
{
    return array_values(array_unique([
        product_image_url($product),
        'https://lh3.googleusercontent.com/aida-public/AB6AXuCD9Yl_DGFsNW0Fexx1F-kvucUUxIV8Go67HxUlmUyAuDBjUkTrcXp-UZy6ma0u_BJKezf3mB6fx8yZUbLeTu6NYOkR4lZc0e39En90zhWZRsVm3WmQpASPHNM8hZXCjGL4hC9EpGc8ah3e6FKV_F0eHF0MS54gfz-y2Pwxb5yP1eseYaxMn0eIHCzr33jcJw45SuTFRoCO-HHNvBI20AFc5kWdO4pXRUFd2502Jro',
        'https://lh3.googleusercontent.com/aida-public/AB6AXuBwie2YtC9FfaAnEKkf7fVo5PFZAnV2vebIj9ALHIqGFsDmn3-YPUpRj-0kXWsIGuLVH0lXV-xl_pR7mY5hx2IoHmByR46GmFB4EhcaYX50zSp7s1TW5VdFk8Ue4WPPd50vfmgcxKzQTMEh4cKoJ6tSTd5BCvBvUMCPOgfwGoeJl9wlwYLTir3_44q7mnqtlzhpNU6DbddVMwKf41x8isJwUW8GPFwLLsKnhScPYy4',
        'https://lh3.googleusercontent.com/aida-public/AB6AXuCCeoN2_4vER1oMueyuXs-u_C-RPYHvxa-14nbYYS091efNDUJG6kksTR0AD_kx444cgtqPsQBGP0SbIScUTZHeq3rNh_7oOvthmC2l31AEwz9ct5DzCYdCGEkhmgqRBW9XcTw1dTBCW8O8xfwryWBOE7mYNerdZI6TXLznJbBBgmQPgKZ0K5dsJmotwD6_G_ChFwZdgYm6mL1ddxB7Ltzx8BTMvASYb6vq-ADEacE',
    ]));
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Phiên làm việc không hợp lệ. Vui lòng quay lại và thử lại.');
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE user_id = ? AND status = 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM admins WHERE admin_id = ? AND status = 1');
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('login.php');
    }

    return $user;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        flash('error', 'Vui lòng đăng nhập quản trị.');
        redirect('admin/login.php');
    }

    return $admin;
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart_items()));
}

function cart_lines(): array
{
    $cart = cart_items();
    if (!$cart) {
        return [];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("
        SELECT pv.*, p.product_name, p.image, p.status AS product_status, c.category_name
        FROM product_variants pv
        JOIN products p ON p.product_id = pv.product_id
        JOIN categories c ON c.category_id = p.category_id
        WHERE pv.variant_id IN ($placeholders)
    ");
    $stmt->execute($ids);

    $lines = [];
    foreach ($stmt->fetchAll() as $row) {
        $qty = max(1, (int) ($cart[$row['variant_id']] ?? 1));
        $price = (float) ($row['sale_price'] ?: $row['price']);
        $row['quantity'] = $qty;
        $row['unit_price'] = $price;
        $row['line_total'] = $price * $qty;
        $lines[] = $row;
    }

    return $lines;
}

function cart_total(): float
{
    return array_sum(array_column(cart_lines(), 'line_total'));
}

function product_badge(array $product): string
{
    if (!empty($product['sale_price']) && (float) $product['sale_price'] < (float) $product['price']) {
        return 'Đang giảm';
    }

    if (!empty($product['created_at']) && strtotime($product['created_at']) > strtotime('-14 days')) {
        return 'Mới';
    }

    return 'Nổi bật';
}

function order_statuses(): array
{
    return ['Chờ xác nhận', 'Đã xác nhận', 'Đang chuẩn bị', 'Đang giao', 'Đã giao', 'Đã hủy'];
}
