<?php
$pageTitle = 'Quản lý sản phẩm';
require_once __DIR__ . '/../includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $productId = (int) ($_POST['product_id'] ?? 0);
    $status = (int) ($_POST['status'] ?? 0);
    $stmt = db()->prepare('UPDATE products SET status = ? WHERE product_id = ?');
    $stmt->execute([$status, $productId]);
    flash('success', 'Đã cập nhật trạng thái sản phẩm.');
    redirect('admin/products.php');
}

$stmt = db()->query('
    SELECT p.*, c.category_name,
           MIN(COALESCE(pv.sale_price, pv.price)) AS display_price,
           SUM(pv.stock_quantity) AS stock,
           COUNT(pv.variant_id) AS variants_count
    FROM products p
    JOIN categories c ON c.category_id = p.category_id
    LEFT JOIN product_variants pv ON pv.product_id = p.product_id
    GROUP BY p.product_id
    ORDER BY p.created_at DESC
');
$products = $stmt->fetchAll();
?>
<div class="section-head">
    <div><h2>Danh sách sản phẩm</h2><p>Quản lý thông tin điện thoại, giá và tồn kho theo biến thể.</p></div>
    <a class="btn" href="<?= app_url('admin/product-add.php') ?>">Thêm sản phẩm</a>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Giá từ</th><th>Biến thể</th><th>Tồn kho</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <div class="admin-product-cell">
                            <img src="<?= e(product_image_url($product)) ?>" alt="<?= e($product['product_name']) ?>">
                            <div>
                                <strong><?= e($product['product_name']) ?></strong>
                                <span class="meta">#<?= (int) $product['product_id'] ?></span>
                            </div>
                        </div>
                    </td>
                    <td><?= e($product['category_name']) ?></td>
                    <td><?= format_price($product['display_price']) ?></td>
                    <td><?= (int) $product['variants_count'] ?></td>
                    <td><?= (int) $product['stock'] ?></td>
                    <td><?= $product['status'] ? 'Đang bán' : 'Đã ẩn' ?></td>
                    <td class="actions">
                        <a class="btn light" href="<?= app_url('admin/product-edit.php?id=' . (int) $product['product_id']) ?>">Sửa</a>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                            <input type="hidden" name="status" value="<?= $product['status'] ? 0 : 1 ?>">
                            <button class="secondary" type="submit"><?= $product['status'] ? 'Ẩn' : 'Hiện' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
