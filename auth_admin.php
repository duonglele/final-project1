<?php
// auth_admin.php
// Dùng để include ở đầu các trang chỉ admin mới truy cập được

session_start();

// Nếu chưa login -> redirect về login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Nếu không có role trong session -> thử lấy từ cookie (fallback)
if (!isset($_SESSION['role']) && isset($_COOKIE['user_role'])) {
    $_SESSION['role'] = $_COOKIE['user_role'];
}

// Nếu không phải admin -> redirect về trang chính
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
