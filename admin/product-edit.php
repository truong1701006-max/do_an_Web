<?php
$pageTitle = 'Sửa sản phẩm';
require_once __DIR__ . '/../includes/admin-header.php';
$id = (int) ($_GET['id'] ?? 0);
$categories = db()->query('SELECT * FROM categories WHERE status = 1 ORDER BY category_name')->fetchAll();

$stmt = db()->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $pdo = db();

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('
            UPDATE products
            SET category_id = ?, product_name = ?, description = ?, brand = ?, warranty_months = ?, image = ?, is_featured = ?
            WHERE product_id = ?
        ');
        $stmt->execute([
            (int) $_POST['category_id'],
            trim($_POST['product_name']),
            trim($_POST['description'] ?? ''),
            trim($_POST['brand'] ?? 'Apple') ?: 'Apple',
            max(0, (int) $_POST['warranty_months']),
            trim($_POST['image'] ?? ''),
            (int) isset($_POST['is_featured']),
            $id,
        ]);

        $variantStmt = $pdo->prepare('
            UPDATE product_variants
            SET storage = ?, color = ?, `condition` = ?, price = ?, sale_price = ?, stock_quantity = ?, sku = ?
            WHERE variant_id = ? AND product_id = ?
        ');
        foreach (($_POST['variants'] ?? []) as $variantId => $variant) {
            $variantStmt->execute([
                trim($variant['storage']),
                trim($variant['color']),
                trim($variant['condition']),
                (float) $variant['price'],
                $variant['sale_price'] === '' ? null : (float) $variant['sale_price'],
                max(0, (int) $variant['stock_quantity']),
                trim($variant['sku']),
                (int) $variantId,
                $id,
            ]);
        }

        if (trim($_POST['new_sku'] ?? '') !== '') {
            $insert = $pdo->prepare('
                INSERT INTO product_variants (product_id, storage, color, `condition`, price, sale_price, stock_quantity, sku)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $insert->execute([
                $id,
                trim($_POST['new_storage']),
                trim($_POST['new_color']),
                trim($_POST['new_condition'] ?: 'Mới'),
                (float) $_POST['new_price'],
                $_POST['new_sale_price'] === '' ? null : (float) $_POST['new_sale_price'],
                max(0, (int) $_POST['new_stock_quantity']),
                trim($_POST['new_sku']),
            ]);
        }

        $pdo->commit();
        flash('success', 'Đã cập nhật sản phẩm.');
        redirect('admin/product-edit.php?id=' . $id);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Không thể cập nhật. Kiểm tra dữ liệu hoặc SKU trùng.');
        redirect('admin/product-edit.php?id=' . $id);
    }
}

$variantStmt = db()->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_id');
$variantStmt->execute([$id]);
$variants = $variantStmt->fetchAll();
?>
<form class="panel form-grid" method="post">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <h2>Thông tin sản phẩm</h2>
    <label>Tên sản phẩm <input name="product_name" required value="<?= e($product['product_name']) ?>"></label>
    <label>Danh mục
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['category_id'] ?>" <?= $category['category_id'] == $product['category_id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Mô tả <textarea name="description"><?= e($product['description']) ?></textarea></label>
    <label>Thương hiệu <input name="brand" value="<?= e($product['brand']) ?>"></label>
    <label>Bảo hành tháng <input type="number" name="warranty_months" min="0" value="<?= (int) $product['warranty_months'] ?>"></label>
    <label>Ảnh sản phẩm <input name="image" value="<?= e($product['image']) ?>"></label>
    <label><input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>> Sản phẩm nổi bật</label>

    <h2>Biến thể hiện có</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Dung lượng</th><th>Màu</th><th>Tình trạng</th><th>Giá</th><th>Giá KM</th><th>Tồn kho</th><th>SKU</th></tr></thead>
            <tbody>
                <?php foreach ($variants as $variant): ?>
                    <tr>
                        <td><input name="variants[<?= (int) $variant['variant_id'] ?>][storage]" value="<?= e($variant['storage']) ?>"></td>
                        <td><input name="variants[<?= (int) $variant['variant_id'] ?>][color]" value="<?= e($variant['color']) ?>"></td>
                        <td><input name="variants[<?= (int) $variant['variant_id'] ?>][condition]" value="<?= e($variant['condition']) ?>"></td>
                        <td><input type="number" name="variants[<?= (int) $variant['variant_id'] ?>][price]" value="<?= e($variant['price']) ?>"></td>
                        <td><input type="number" name="variants[<?= (int) $variant['variant_id'] ?>][sale_price]" value="<?= e($variant['sale_price']) ?>"></td>
                        <td><input type="number" name="variants[<?= (int) $variant['variant_id'] ?>][stock_quantity]" value="<?= (int) $variant['stock_quantity'] ?>"></td>
                        <td><input name="variants[<?= (int) $variant['variant_id'] ?>][sku]" value="<?= e($variant['sku']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2>Thêm biến thể mới</h2>
    <label>SKU mới <input name="new_sku" placeholder="Bỏ trống nếu không thêm"></label>
    <label>Dung lượng <input name="new_storage"></label>
    <label>Màu sắc <input name="new_color"></label>
    <label>Tình trạng <input name="new_condition" value="Mới"></label>
    <label>Giá bán <input type="number" name="new_price" min="0" step="1000" value="0"></label>
    <label>Giá khuyến mãi <input type="number" name="new_sale_price" min="0" step="1000"></label>
    <label>Tồn kho <input type="number" name="new_stock_quantity" min="0" value="0"></label>
    <button type="submit">Cập nhật sản phẩm</button>
</form>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
