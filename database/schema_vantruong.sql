CREATE DATABASE IF NOT EXISTS vantruong_mobile CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vantruong_mobile;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_status_logs;
DROP TABLE IF EXISTS inventory_logs;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    status TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    status TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    status TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    brand VARCHAR(100) NOT NULL DEFAULT 'Apple',
    warranty_months INT NOT NULL DEFAULT 12,
    image VARCHAR(255),
    is_featured TINYINT NOT NULL DEFAULT 0,
    status TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;

CREATE TABLE product_variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    storage VARCHAR(30) NOT NULL,
    color VARCHAR(50) NOT NULL,
    `condition` VARCHAR(30) NOT NULL DEFAULT 'Mới',
    price DECIMAL(12,2) NOT NULL,
    sale_price DECIMAL(12,2),
    stock_quantity INT NOT NULL DEFAULT 0,
    sku VARCHAR(50) NOT NULL UNIQUE,
    CONSTRAINT fk_variants_product FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    note TEXT,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'Chờ xác nhận',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE order_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    variant_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    line_total DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_order_details_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_order_details_variant FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
) ENGINE=InnoDB;

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method VARCHAR(30) NOT NULL DEFAULT 'COD',
    amount DECIMAL(12,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Chưa thanh toán',
    paid_at DATETIME NULL,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB;

CREATE TABLE coupons (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type VARCHAR(20) NOT NULL,
    discount_value DECIMAL(12,2) NOT NULL,
    min_order DECIMAL(12,2) NOT NULL DEFAULT 0,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    usage_limit INT NOT NULL DEFAULT 0,
    used_count INT NOT NULL DEFAULT 0,
    status TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    status TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

CREATE TABLE inventory_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    variant_id INT NOT NULL,
    admin_id INT NULL,
    change_type VARCHAR(20) NOT NULL,
    quantity INT NOT NULL,
    note TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_variant FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id),
    CONSTRAINT fk_inventory_admin FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
) ENGINE=InnoDB;

CREATE TABLE order_status_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    admin_id INT NULL,
    old_status VARCHAR(30),
    new_status VARCHAR(30) NOT NULL,
    note TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_logs_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_order_logs_admin FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
) ENGINE=InnoDB;

INSERT INTO admins (username, password, full_name, role) VALUES
('admin', '$2y$10$782Q5q82Cj7mzcqSMNg2ne8p2b6OqXjCXcgpD4eWHLDEHRLgZgO92', 'Quản trị viên Văn Trường', 'admin');

INSERT INTO users (full_name, email, password, phone, address) VALUES
('Lê Văn Trường', 'truong@example.com', '$2y$10$782Q5q82Cj7mzcqSMNg2ne8p2b6OqXjCXcgpD4eWHLDEHRLgZgO92', '0988123456', '256 Cầu Giấy, Hà Nội');

INSERT INTO categories (category_name, description) VALUES
('iPhone 13 Series', 'Các mẫu iPhone 13 chính hãng và đã qua sử dụng.'),
('iPhone 14 Series', 'Các mẫu iPhone 14 nhiều dung lượng, nhiều màu.'),
('iPhone 15 Series', 'Dòng iPhone 15 mới, hiệu năng cao.'),
('iPhone 16 Series', 'Dòng iPhone 16 mới nhất tại cửa hàng.'),
('Máy đổi trả', 'Sản phẩm đổi trả đã kiểm tra kỹ thuật.');

INSERT INTO products (category_id, product_name, description, brand, warranty_months, image, is_featured, status, created_at) VALUES
(1, 'iPhone 13 128GB', 'iPhone 13 màn hình Super Retina XDR, chip A15 Bionic, camera kép sắc nét.', 'Apple', 12, 'assets/images/iphone-blue.svg', 1, 1, NOW() - INTERVAL 26 DAY),
(2, 'iPhone 14 Pro 256GB', 'iPhone 14 Pro với Dynamic Island, màn hình ProMotion và camera 48MP.', 'Apple', 12, 'assets/images/iphone-dark.svg', 1, 1, NOW() - INTERVAL 18 DAY),
(3, 'iPhone 15 128GB', 'iPhone 15 thiết kế mới, cổng USB-C, camera 48MP và hiệu năng ổn định.', 'Apple', 12, 'assets/images/iphone-silver.svg', 1, 1, NOW() - INTERVAL 9 DAY),
(3, 'iPhone 15 Pro Max 256GB', 'iPhone 15 Pro Max khung titan, chip A17 Pro, camera telephoto mạnh mẽ.', 'Apple', 12, 'assets/images/iphone-dark.svg', 1, 1, NOW() - INTERVAL 5 DAY),
(4, 'iPhone 16 128GB', 'iPhone 16 thế hệ mới với hiệu năng cao, pin tốt và cụm camera nâng cấp.', 'Apple', 12, 'assets/images/iphone-blue.svg', 1, 1, NOW() - INTERVAL 3 DAY),
(5, 'iPhone 14 128GB đổi trả', 'Máy đổi trả ngoại hình đẹp, đã kiểm tra kỹ thuật, bảo hành 6 tháng.', 'Apple', 6, 'assets/images/iphone-silver.svg', 0, 1, NOW() - INTERVAL 31 DAY);

INSERT INTO product_variants (product_id, storage, color, `condition`, price, sale_price, stock_quantity, sku) VALUES
(1, '128GB', 'Xanh dương', 'Đã qua sử dụng', 11990000, 10990000, 8, 'IP13-128-BLUE-LN'),
(1, '256GB', 'Đen', 'Đã qua sử dụng', 13990000, NULL, 5, 'IP13-256-BLK-LN'),
(2, '256GB', 'Đen', 'Mới', 22990000, 21490000, 6, 'IP14P-256-BLK-NEW'),
(2, '512GB', 'Bạc', 'Mới', 25990000, NULL, 3, 'IP14P-512-SLV-NEW'),
(3, '128GB', 'Bạc', 'Mới', 18990000, 17990000, 12, 'IP15-128-SLV-NEW'),
(3, '256GB', 'Xanh dương', 'Mới', 21990000, NULL, 7, 'IP15-256-BLU-NEW'),
(4, '256GB', 'Đen', 'Mới', 31990000, 30490000, 4, 'IP15PM-256-BLK-NEW'),
(4, '512GB', 'Bạc', 'Mới', 36990000, NULL, 2, 'IP15PM-512-SLV-NEW'),
(5, '128GB', 'Xanh dương', 'Mới', 22990000, 21990000, 10, 'IP16-128-BLU-NEW'),
(6, '128GB', 'Bạc', 'Máy đổi trả', 14990000, 13590000, 4, 'IP14-128-SLV-RTN');

INSERT INTO coupons (code, discount_type, discount_value, min_order, start_date, end_date, usage_limit, status) VALUES
('TRUONGVIP', 'amount', 500000, 15000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY, 100, 1),
('VTLUXURY10', 'percent', 10, 5000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY, 100, 1),
('WELCOMEVT', 'amount', 200000, 5000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY, 200, 1);
