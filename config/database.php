<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'vantruong_mobile';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        http_response_code(500);
        exit('Không thể kết nối cơ sở dữ liệu. Hãy tạo database vantruong_mobile và import file database/schema.sql (hoặc database/schema_vantruong.sql).');
    }

    return $pdo;
}
