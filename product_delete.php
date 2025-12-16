<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/header.php";

require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  echo "<div class='alert'>Thiếu ID sản phẩm.</div>";
  echo "<a class='btn' href='dashboard.php'>← Quay lại</a>"; // ✅ dashboard.php
  require_once __DIR__ . "/../includes/footer.php";
  exit;
}

// Lấy sản phẩm để xóa ảnh
$st = $pdo->prepare("SELECT id, image FROM products WHERE id=?");
$st->execute([$id]);
$p = $st->fetch(PDO::FETCH_ASSOC);

if (!$p) {
  echo "<div class='alert'>Sản phẩm không tồn tại.</div>";
  echo "<a class='btn' href='dashboard.php'>← Quay lại</a>"; // ✅ dashboard.php
  require_once __DIR__ . "/../includes/footer.php";
  exit;
}

// Xóa record
$del = $pdo->prepare("DELETE FROM products WHERE id=?");
$del->execute([$id]);

// Xóa file ảnh trong thư mục uploads
if (!empty($p['image'])) {
  // ✅ FIX PATH: ../uploads/
  $path = __DIR__ . "/../uploads/" . $p['image']; 
  if (is_file($path)) @unlink($path);
}

// ✅ FIX REDIRECT: Về dashboard.php
header("Location: dashboard.php");
exit;