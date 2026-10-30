<?php
$pageTitle = 'Báo cáo doanh thu';
require_once __DIR__ . '/../includes/admin-header.php';

$revenueByDay = db()->query("
    SELECT DATE(created_at) AS day, SUM(total_amount) AS revenue, COUNT(*) AS orders_count
    FROM orders
    WHERE status = 'Đã giao'
    GROUP BY DATE(created_at)
    ORDER BY day DESC
    LIMIT 30
")->fetchAll();

$topProducts = db()->query("
    SELECT od.product_name, SUM(od.quantity) AS sold, SUM(od.line_total) AS revenue
    FROM order_details od
    JOIN orders o ON o.order_id = od.order_id
    WHERE o.status = 'Đã giao'
    GROUP BY od.product_name
    ORDER BY sold DESC
    LIMIT 10
")->fetchAll();

$orderSummary = db()->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status')->fetchAll();
?>
<div class="grid">
    <section class="panel">
        <h2>Trạng thái đơn hàng</h2>
        <?php foreach ($orderSummary as $row): ?>
            <p><?= e($row['status']) ?>: <strong><?= (int) $row['total'] ?></strong></p>
        <?php endforeach; ?>
    </section>
    <section class="panel">
        <h2>Ghi chú báo cáo</h2>
        <p class="meta">Doanh thu chỉ tính các đơn có trạng thái Đã giao, đúng yêu cầu nghiệp vụ trong đặc tả.</p>
    </section>
</div>
<section class="panel" style="margin-top:18px">
    <h2>Doanh thu theo ngày</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Ngày</th><th>Số đơn đã giao</th><th>Doanh thu</th></tr></thead>
            <tbody>
                <?php foreach ($revenueByDay as $row): ?>
                    <tr><td><?= e($row['day']) ?></td><td><?= (int) $row['orders_count'] ?></td><td><?= format_price($row['revenue']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<section class="panel" style="margin-top:18px">
    <h2>Sản phẩm bán chạy</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Sản phẩm</th><th>Số lượng bán</th><th>Doanh thu</th></tr></thead>
            <tbody>
                <?php foreach ($topProducts as $row): ?>
                    <tr><td><?= e($row['product_name']) ?></td><td><?= (int) $row['sold'] ?></td><td><?= format_price($row['revenue']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
