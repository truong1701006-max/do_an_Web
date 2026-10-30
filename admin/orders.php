<?php
$pageTitle = 'Quản lý đơn hàng';
require_once __DIR__ . '/../includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? 'Chờ xác nhận';
    $note = trim($_POST['note'] ?? '');

    if (!in_array($newStatus, order_statuses(), true)) {
        flash('error', 'Trạng thái đơn hàng không hợp lệ.');
        redirect('admin/orders.php');
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            throw new RuntimeException('Không tìm thấy đơn hàng.');
        }

        $oldStatus = $order['status'];
        $update = $pdo->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
        $update->execute([$newStatus, $orderId]);

        $log = $pdo->prepare('INSERT INTO order_status_logs (order_id, admin_id, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)');
        $log->execute([$orderId, $admin['admin_id'], $oldStatus, $newStatus, $note]);

        if ($oldStatus !== 'Đã hủy' && $newStatus === 'Đã hủy') {
            $details = $pdo->prepare('SELECT variant_id, quantity FROM order_details WHERE order_id = ?');
            $details->execute([$orderId]);
            $restore = $pdo->prepare('UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE variant_id = ?');
            $inventoryLog = $pdo->prepare('INSERT INTO inventory_logs (variant_id, admin_id, change_type, quantity, note) VALUES (?, ?, ?, ?, ?)');
            foreach ($details->fetchAll() as $detail) {
                $restore->execute([$detail['quantity'], $detail['variant_id']]);
                $inventoryLog->execute([$detail['variant_id'], $admin['admin_id'], 'Nhập', $detail['quantity'], 'Hoàn kho do hủy đơn ' . $order['order_code']]);
            }
        }

        if ($newStatus === 'Đã giao') {
            $payment = $pdo->prepare("UPDATE payments SET status = 'Đã thanh toán', paid_at = COALESCE(paid_at, NOW()) WHERE order_id = ?");
            $payment->execute([$orderId]);
        }

        $pdo->commit();
        flash('success', 'Đã cập nhật trạng thái đơn hàng.');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', $exception->getMessage());
    }
    redirect('admin/orders.php');
}

$query = trim($_GET['q'] ?? '');
$params = [];
$where = '';
if ($query !== '') {
    $where = 'WHERE o.order_code LIKE ? OR o.recipient_name LIKE ? OR o.phone LIKE ?';
    $term = '%' . $query . '%';
    $params = [$term, $term, $term];
}
$stmt = db()->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON u.user_id = o.user_id $where ORDER BY o.created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<div class="section-head">
    <div><h2>Đơn hàng</h2><p>Tìm kiếm, xem nhanh và cập nhật trạng thái đơn.</p></div>
    <form class="search-form" method="get">
        <input name="q" value="<?= e($query) ?>" placeholder="Mã đơn, tên khách, SĐT">
        <button type="submit">Tìm</button>
    </form>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Người nhận</th><th>Tổng tiền</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order['order_code']) ?><br><span class="meta"><?= e($order['created_at']) ?></span></td>
                    <td><?= e($order['email']) ?></td>
                    <td><?= e($order['recipient_name']) ?><br><span class="meta"><?= e($order['phone']) ?></span></td>
                    <td><?= format_price($order['total_amount']) ?></td>
                    <td><span class="badge"><?= e($order['status']) ?></span></td>
                    <td>
                        <form method="post" class="actions">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                            <select name="status">
                                <?php foreach (order_statuses() as $status): ?>
                                    <option <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input name="note" placeholder="Ghi chú" style="min-width:160px">
                            <button type="submit">Lưu</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
