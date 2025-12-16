<?php
// config/db.php
declare(strict_types=1);

// 1. Thông tin kết nối CŨ (sql305)
$host = 'sql305.infinityfree.com';
$db   = 'if0_40505405_db_123';
$user = 'if0_40505405';
$pass = 'L5H2SjYmpSMQ';

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     exit("Lỗi kết nối Database Cũ: " . $e->getMessage());
}
?>