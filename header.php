<?php
// includes/header.php

// 1. Ép buộc sử dụng HTTPS (Giữ nguyên logic cũ của bạn)
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $location);
    exit;
}

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/helpers.php";
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/assets/style.css">
  <title>Electronics Shop</title>
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    
    <a class="brand" href="/shop.php">ElectronicsShop</a>

    <nav class="nav">
      <a href="/cart.php">Giỏ hàng</a>

      <?php if (current_user()): ?>
        <span class="chip"><?= e(current_user()['name']) ?> (<?= e(current_user()['role']) ?>)</span>

        <a href="/order_history.php">Lịch sử đơn hàng</a>

        <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
          <a href="/admin/dashboard.php">Admin</a>
        <?php endif; ?>

        <a href="/logout.php">Đăng xuất</a>
      <?php else: ?>
        <a href="/login.php">Đăng nhập</a>
        <a href="/register.php">Đăng ký</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
