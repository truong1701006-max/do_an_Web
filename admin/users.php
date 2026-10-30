<?php
$pageTitle = 'Quản lý khách hàng';
require_once __DIR__ . '/../includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('UPDATE users SET status = ? WHERE user_id = ?');
    $stmt->execute([(int) $_POST['status'], (int) $_POST['user_id']]);
    flash('success', 'Đã cập nhật trạng thái khách hàng.');
    redirect('admin/users.php');
}

$query = trim($_GET['q'] ?? '');
$params = [];
$where = '';
if ($query !== '') {
    $where = 'WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?';
    $term = '%' . $query . '%';
    $params = [$term, $term, $term];
}
$stmt = db()->prepare("SELECT * FROM users $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<div class="section-head">
    <div><h2>Khách hàng</h2><p>Xem, tìm kiếm và khóa/mở tài khoản khi cần.</p></div>
    <form class="search-form" method="get">
        <input name="q" value="<?= e($query) ?>" placeholder="Tên, email, số điện thoại">
        <button type="submit">Tìm</button>
    </form>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Khách hàng</th><th>Liên hệ</th><th>Địa chỉ</th><th>Ngày tạo</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($users as $customer): ?>
                <tr>
                    <td><?= e($customer['full_name']) ?></td>
                    <td><?= e($customer['email']) ?><br><?= e($customer['phone']) ?></td>
                    <td><?= e($customer['address']) ?></td>
                    <td><?= e($customer['created_at']) ?></td>
                    <td><?= $customer['status'] ? 'Hoạt động' : 'Đã khóa' ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="user_id" value="<?= (int) $customer['user_id'] ?>">
                            <input type="hidden" name="status" value="<?= $customer['status'] ? 0 : 1 ?>">
                            <button class="<?= $customer['status'] ? 'danger' : 'secondary' ?>" type="submit"><?= $customer['status'] ? 'Khóa' : 'Mở khóa' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
