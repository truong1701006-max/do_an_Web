<?php
$pageTitle = 'Quản lý tồn kho';
require_once __DIR__ . '/../includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $variantId = (int) ($_POST['variant_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($quantity === 0) {
        flash('error', 'Số lượng điều chỉnh phải khác 0.');
        redirect('admin/inventory.php');
    }

    $pdo = db();
    $stmt = $pdo->prepare('UPDATE product_variants SET stock_quantity = GREATEST(stock_quantity + ?, 0) WHERE variant_id = ?');
    $stmt->execute([$quantity, $variantId]);
    $log = $pdo->prepare('INSERT INTO inventory_logs (variant_id, admin_id, change_type, quantity, note) VALUES (?, ?, ?, ?, ?)');
    $log->execute([$variantId, $admin['admin_id'], $quantity > 0 ? 'Nhập' : 'Điều chỉnh', $quantity, $note]);
    flash('success', 'Đã cập nhật tồn kho.');
    redirect('admin/inventory.php');
}

$variants = db()->query('
    SELECT pv.*, p.product_name
    FROM product_variants pv
    JOIN products p ON p.product_id = pv.product_id
    ORDER BY pv.stock_quantity ASC, p.product_name
')->fetchAll();
?>
<div class="section-head">
    <div><h2>Tồn kho</h2><p>Cảnh báo sản phẩm sắp hết và điều chỉnh số lượng nhập/xuất.</p></div>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Sản phẩm</th><th>Biến thể</th><th>SKU</th><th>Tồn kho</th><th>Điều chỉnh</th></tr></thead>
        <tbody>
            <?php foreach ($variants as $variant): ?>
                <tr>
                    <td><?= e($variant['product_name']) ?></td>
                    <td><?= e($variant['storage']) ?> · <?= e($variant['color']) ?> · <?= e($variant['condition']) ?></td>
                    <td><?= e($variant['sku']) ?></td>
                    <td><?= (int) $variant['stock_quantity'] ?> <?= $variant['stock_quantity'] <= 3 ? '<span class="badge">Sắp hết</span>' : '' ?></td>
                    <td>
                        <form method="post" class="actions">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="variant_id" value="<?= (int) $variant['variant_id'] ?>">
                            <input type="number" name="quantity" placeholder="+5 hoặc -2" style="width:120px">
                            <input name="note" placeholder="Ghi chú">
                            <button type="submit">Lưu</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
