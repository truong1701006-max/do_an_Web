<?php
$pageTitle = 'Tổng quan';
require_once __DIR__ . '/../includes/admin-header.php';

$stats = [
    'products' => db()->query('SELECT COUNT(*) FROM products WHERE status = 1')->fetchColumn(),
    'orders' => db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'users' => db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'reviews' => db()->query('SELECT COUNT(*) FROM reviews WHERE status = 0')->fetchColumn(),
    'revenue' => db()->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status = 'Đã giao'")->fetchColumn(),
];
$recentOrders = db()->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();
?>
<div class="stat-grid">
    <div class="stat-card">Sản phẩm đang bán<strong><?= (int) $stats['products'] ?></strong></div>
    <div class="stat-card">Tổng đơn hàng<strong><?= (int) $stats['orders'] ?></strong></div>
    <div class="stat-card">Khách hàng<strong><?= (int) $stats['users'] ?></strong></div>
    <div class="stat-card">Đánh giá chờ duyệt<strong><?= (int) $stats['reviews'] ?></strong></div>
    <div class="stat-card">Doanh thu đã giao<strong><?= format_price($stats['revenue']) ?></strong></div>
</div>
<section class="panel" style="margin-top:18px">
    <div class="section-head">
        <div><h2>Đơn hàng mới</h2><p>Theo dõi nhanh các đơn gần nhất.</p></div>
        <a class="btn light" href="<?= app_url('admin/orders.php') ?>">Xem tất cả</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Mã đơn</th><th>Người nhận</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th></tr></thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><?= e($order['order_code']) ?></td>
                        <td><?= e($order['recipient_name']) ?></td>
                        <td><?= format_price($order['total_amount']) ?></td>
                        <td><span class="badge"><?= e($order['status']) ?></span></td>
                        <td><?= e($order['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
