<?php
$pageTitle = 'Quản lý danh mục';
require_once __DIR__ . '/../includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['category_id'] ?? 0);
    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = (int) ($_POST['status'] ?? 1);

    if ($name === '') {
        flash('error', 'Tên danh mục không được để trống.');
        redirect('admin/categories.php');
    }

    if ($id) {
        $stmt = db()->prepare('UPDATE categories SET category_name = ?, description = ?, status = ? WHERE category_id = ?');
        $stmt->execute([$name, $description, $status, $id]);
        flash('success', 'Đã cập nhật danh mục.');
    } else {
        $stmt = db()->prepare('INSERT INTO categories (category_name, description, status) VALUES (?, ?, ?)');
        $stmt->execute([$name, $description, $status]);
        flash('success', 'Đã thêm danh mục.');
    }
    redirect('admin/categories.php');
}

$categoriesList = db()->query('SELECT * FROM categories ORDER BY category_id DESC')->fetchAll();
?>
<div class="catalog-layout">
    <form class="panel form-grid" method="post">
        <h2>Thêm danh mục</h2>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <label>Tên danh mục <input name="category_name" required></label>
        <label>Mô tả <textarea name="description"></textarea></label>
        <label>Trạng thái
            <select name="status"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select>
        </label>
        <button type="submit">Lưu danh mục</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tên</th><th>Mô tả</th><th>Trạng thái</th><th>Cập nhật nhanh</th></tr></thead>
            <tbody>
                <?php foreach ($categoriesList as $category): ?>
                    <tr>
                        <form method="post">
                            <td>
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="category_id" value="<?= (int) $category['category_id'] ?>">
                                <input name="category_name" value="<?= e($category['category_name']) ?>">
                            </td>
                            <td><input name="description" value="<?= e($category['description']) ?>"></td>
                            <td>
                                <select name="status">
                                    <option value="1" <?= $category['status'] ? 'selected' : '' ?>>Hiển thị</option>
                                    <option value="0" <?= !$category['status'] ? 'selected' : '' ?>>Ẩn</option>
                                </select>
                            </td>
                            <td><button type="submit" class="secondary">Lưu</button></td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
