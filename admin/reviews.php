<?php
$pageTitle = 'Duyệt đánh giá';
require_once __DIR__ . '/../includes/admin-header.php';

function review_status_label(int $status): string
{
    return match ($status) {
        1 => 'Đã hiển thị',
        -1 => 'Đã ẩn',
        default => 'Chờ duyệt',
    };
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $status = (int) ($_POST['status'] ?? 0);

    if (!in_array($status, [-1, 0, 1], true)) {
        flash('error', 'Trạng thái đánh giá không hợp lệ.');
        redirect('admin/reviews.php');
    }

    $stmt = db()->prepare('UPDATE reviews SET status = ? WHERE review_id = ?');
    $stmt->execute([$status, $reviewId]);
    flash('success', 'Đã cập nhật trạng thái đánh giá.');
    redirect('admin/reviews.php');
}

$filter = $_GET['status'] ?? '';
$params = [];
$where = '';
if ($filter !== '' && in_array((int) $filter, [-1, 0, 1], true)) {
    $where = 'WHERE r.status = ?';
    $params[] = (int) $filter;
}

$stmt = db()->prepare("
    SELECT r.*, u.full_name, u.email, p.product_name
    FROM reviews r
    JOIN users u ON u.user_id = r.user_id
    JOIN products p ON p.product_id = r.product_id
    $where
    ORDER BY r.created_at DESC
");
$stmt->execute($params);
$reviews = $stmt->fetchAll();
?>
<div class="section-head">
    <div><h2>Đánh giá sản phẩm</h2><p>Duyệt nhận xét của khách trước khi hiển thị ngoài trang sản phẩm.</p></div>
    <form class="search-form" method="get">
        <select name="status">
            <option value="">Tất cả</option>
            <option value="0" <?= $filter === '0' ? 'selected' : '' ?>>Chờ duyệt</option>
            <option value="1" <?= $filter === '1' ? 'selected' : '' ?>>Đã hiển thị</option>
            <option value="-1" <?= $filter === '-1' ? 'selected' : '' ?>>Đã ẩn</option>
        </select>
        <button type="submit">Lọc</button>
    </form>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Sản phẩm</th><th>Khách hàng</th><th>Đánh giá</th><th>Nội dung</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead>
        <tbody>
            <?php foreach ($reviews as $review): ?>
                <tr>
                    <td><?= e($review['product_name']) ?><br><span class="meta"><?= e($review['created_at']) ?></span></td>
                    <td><?= e($review['full_name']) ?><br><span class="meta"><?= e($review['email']) ?></span></td>
                    <td><span class="stars"><?= str_repeat('★', (int) $review['rating']) ?><span><?= str_repeat('☆', 5 - (int) $review['rating']) ?></span></span></td>
                    <td><?= nl2br(e($review['comment'])) ?></td>
                    <td><span class="badge"><?= e(review_status_label((int) $review['status'])) ?></span></td>
                    <td>
                        <form method="post" class="actions">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="review_id" value="<?= (int) $review['review_id'] ?>">
                            <select name="status">
                                <option value="0" <?= (int) $review['status'] === 0 ? 'selected' : '' ?>>Chờ duyệt</option>
                                <option value="1" <?= (int) $review['status'] === 1 ? 'selected' : '' ?>>Hiển thị</option>
                                <option value="-1" <?= (int) $review['status'] === -1 ? 'selected' : '' ?>>Ẩn</option>
                            </select>
                            <button type="submit">Lưu</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$reviews): ?>
                <tr><td colspan="6">Chưa có đánh giá phù hợp.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
