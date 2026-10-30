<?php
$pageTitle = 'Thêm sản phẩm';
require_once __DIR__ . '/../includes/admin-header.php';
$categories = db()->query('SELECT * FROM categories WHERE status = 1 ORDER BY category_name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $pdo = db();
    $productName = trim($_POST['product_name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $brand = trim($_POST['brand'] ?? 'Apple') ?: 'Apple';
    $warranty = max(0, (int) ($_POST['warranty_months'] ?? 12));
    $image = trim($_POST['image'] ?? 'assets/images/iphone-dark.svg');
    $storage = trim($_POST['storage'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $condition = trim($_POST['condition'] ?? 'Mới');
    $price = (float) ($_POST['price'] ?? 0);
    $salePrice = $_POST['sale_price'] === '' ? null : (float) $_POST['sale_price'];
    $stock = max(0, (int) ($_POST['stock_quantity'] ?? 0));
    $sku = trim($_POST['sku'] ?? '');

    if ($productName === '' || !$categoryId || $storage === '' || $color === '' || $price <= 0 || $sku === '') {
        flash('error', 'Vui lòng nhập đầy đủ thông tin sản phẩm và biến thể đầu tiên.');
        redirect('admin/product-add.php');
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('
            INSERT INTO products (category_id, product_name, description, brand, warranty_months, image, is_featured)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$categoryId, $productName, $description, $brand, $warranty, $image, (int) isset($_POST['is_featured'])]);
        $productId = (int) $pdo->lastInsertId();

        $variantStmt = $pdo->prepare('
            INSERT INTO product_variants (product_id, storage, color, `condition`, price, sale_price, stock_quantity, sku)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $variantStmt->execute([$productId, $storage, $color, $condition, $price, $salePrice, $stock, $sku]);
        $pdo->commit();
        flash('success', 'Đã thêm sản phẩm mới.');
        redirect('admin/products.php');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Không thể thêm sản phẩm. Kiểm tra SKU có bị trùng không.');
        redirect('admin/product-add.php');
    }
}
?>
<form class="panel form-grid" method="post">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <h2>Thông tin sản phẩm</h2>
    <label>Tên sản phẩm <input name="product_name" required></label>
    <label>Danh mục
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?><option value="<?= (int) $category['category_id'] ?>"><?= e($category['category_name']) ?></option><?php endforeach; ?>
        </select>
    </label>
    <label>Mô tả <textarea name="description"></textarea></label>
    <label>Thương hiệu <input name="brand" value="Apple"></label>
    <label>Bảo hành tháng <input type="number" name="warranty_months" value="12" min="0"></label>
    <label>Ảnh sản phẩm <input name="image" value="assets/images/iphone-dark.svg"></label>
    <label><input type="checkbox" name="is_featured" value="1"> Sản phẩm nổi bật</label>
    <h2>Biến thể đầu tiên</h2>
    <label>Dung lượng <input name="storage" placeholder="128GB" required></label>
    <label>Màu sắc <input name="color" placeholder="Đen" required></label>
    <label>Tình trạng <input name="condition" value="Mới" required></label>
    <label>Giá bán <input type="number" name="price" min="0" step="1000" required></label>
    <label>Giá khuyến mãi <input type="number" name="sale_price" min="0" step="1000"></label>
    <label>Tồn kho <input type="number" name="stock_quantity" min="0" value="0"></label>
    <label>SKU <input name="sku" required></label>
    <button type="submit">Lưu sản phẩm</button>
</form>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
