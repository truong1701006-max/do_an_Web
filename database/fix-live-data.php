<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$pdo = db();

$categories = [
    1 => ['iPhone 13 Series', 'Các mẫu iPhone 13 chính hãng và đã qua sử dụng.'],
    2 => ['iPhone 14 Series', 'Các mẫu iPhone 14 nhiều dung lượng, nhiều màu.'],
    3 => ['iPhone 15 Series', 'Dòng iPhone 15 mới, hiệu năng cao.'],
    4 => ['iPhone 16 Series', 'Dòng iPhone 16 mới nhất tại cửa hàng.'],
    5 => ['Máy đổi trả', 'Sản phẩm đổi trả đã kiểm tra kỹ thuật.'],
];

$stmt = $pdo->prepare('UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?');
foreach ($categories as $id => $row) {
    $stmt->execute([$row[0], $row[1], $id]);
}

$products = [
    1 => ['iPhone 13 128GB', 'iPhone 13 màn hình Super Retina XDR, chip A15 Bionic, camera kép sắc nét.'],
    2 => ['iPhone 14 Pro 256GB', 'iPhone 14 Pro với Dynamic Island, màn hình ProMotion và camera 48MP.'],
    3 => ['iPhone 15 128GB', 'iPhone 15 thiết kế mới, cổng USB-C, camera 48MP và hiệu năng ổn định.'],
    4 => ['iPhone 15 Pro Max 256GB', 'iPhone 15 Pro Max khung titan, chip A17 Pro, camera telephoto mạnh mẽ.'],
    5 => ['iPhone 16 128GB', 'iPhone 16 thế hệ mới với hiệu năng cao, pin tốt và cụm camera nâng cấp.'],
    6 => ['iPhone 14 128GB đổi trả', 'Máy đổi trả ngoại hình đẹp, đã kiểm tra kỹ thuật, bảo hành 6 tháng.'],
];

$stmt = $pdo->prepare('UPDATE products SET product_name = ?, description = ? WHERE product_id = ?');
foreach ($products as $id => $row) {
    $stmt->execute([$row[0], $row[1], $id]);
}

$variants = [
    1 => ['128GB', 'Xanh dương', 'Đã qua sử dụng'],
    2 => ['256GB', 'Đen', 'Đã qua sử dụng'],
    3 => ['256GB', 'Đen', 'Mới'],
    4 => ['512GB', 'Bạc', 'Mới'],
    5 => ['128GB', 'Bạc', 'Mới'],
    6 => ['256GB', 'Xanh dương', 'Mới'],
    7 => ['256GB', 'Đen', 'Mới'],
    8 => ['512GB', 'Bạc', 'Mới'],
    9 => ['128GB', 'Xanh dương', 'Mới'],
    10 => ['128GB', 'Bạc', 'Máy đổi trả'],
];

$stmt = $pdo->prepare('UPDATE product_variants SET storage = ?, color = ?, `condition` = ? WHERE variant_id = ?');
foreach ($variants as $id => $row) {
    $stmt->execute([$row[0], $row[1], $row[2], $id]);
}

$pdo->exec("UPDATE orders SET status = 'Chờ xác nhận' WHERE status NOT IN ('Chờ xác nhận','Đã xác nhận','Đang chuẩn bị','Đang giao','Đã giao','Đã hủy')");
$pdo->exec("UPDATE payments SET status = 'Chờ thanh toán' WHERE status NOT IN ('Chưa thanh toán','Chờ thanh toán','Đã thanh toán','Đã hủy')");

echo "Live data fixed.\n";
