<?php
// htdocs/add_to_cart.php
require_once __DIR__ . "/config/db.php";

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$pid = (int)($_POST['product_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);
if ($qty < 1) $qty = 1;

$redirect = $_POST['redirect'] ?? 'back';

if ($pid > 0) {
  $st = $pdo->prepare("SELECT stock FROM products WHERE id=?");
  $st->execute([$pid]);
  $p = $st->fetch(PDO::FETCH_ASSOC);

  if ($p) {
    $stock = (int)$p['stock'];
    if ($stock <= 0) {
      $_SESSION['flash'] = "Sản phẩm đã hết hàng.";
    } else {
      $current = (int)($_SESSION['cart'][$pid] ?? 0);
      $_SESSION['cart'][$pid] = min($stock, $current + $qty);
      $_SESSION['flash'] = "Đã thêm vào giỏ hàng.";
    }
  }
}

if ($redirect === 'cart') {
  header("Location: cart.php");
  exit;
}

// Quay lại trang trước (thường là shop.php hoặc product.php)
$back = $_SERVER['HTTP_REFERER'] ?? 'shop.php';
header("Location: " . $back);
exit;