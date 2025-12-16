<?php
// htdocs/checkout.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/header.php";

require_login(); // Phải đăng nhập mới được mua

// Xử lý thông báo thành công (PRG)
$successOrder = (int)($_GET['order'] ?? 0);
if (isset($_GET['success']) && $successOrder > 0) {
  echo "<div class='alert' style='background:#dcfce7; color:#166534; border-color:#bbf7d0;'>Thanh toán thành công! Mã đơn hàng: #{$successOrder}</div>";
  echo "<a class='btn primary' href='shop.php'>← Tiếp tục mua sắm</a>";
  require_once __DIR__ . "/includes/footer.php";
  exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
  echo "<div class='alert'>Giỏ hàng trống.</div>";
  echo "<a class='btn' href='shop.php'>← Quay lại Cửa hàng</a>";
  require_once __DIR__ . "/includes/footer.php";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $pdo->beginTransaction();

    $ids = array_keys($cart);
    $in = implode(',', array_fill(0, count($ids), '?'));
    // Khóa dòng để tránh xung đột kho
    $st = $pdo->prepare("SELECT * FROM products WHERE id IN ($in) FOR UPDATE");
    $st->execute($ids);
    $products = $st->fetchAll();
    
    $map = [];
    foreach ($products as $p) $map[(int)$p['id']] = $p;

    $total = 0.0;
    foreach ($cart as $pid => $qty) {
      if (!isset($map[$pid])) throw new Exception("Sản phẩm ID $pid không tồn tại.");
      $stock = (int)$map[$pid]['stock'];
      if ($stock < $qty) throw new Exception("Sản phẩm '{$map[$pid]['name']}' không đủ hàng (còn $stock).");
      $total += (float)$map[$pid]['price'] * $qty;
    }

    // Tạo đơn
    $ins = $pdo->prepare("INSERT INTO orders(user_id, total, status) VALUES(?, ?, 'paid')");
    $ins->execute([current_user()['id'], $total]);
    $orderId = $pdo->lastInsertId();

    // Tạo chi tiết + Trừ kho
    $insItem = $pdo->prepare("INSERT INTO order_items(order_id, product_id, qty, price) VALUES(?,?,?,?)");
    $updStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id=?");

    foreach ($cart as $pid => $qty) {
      $price = (float)$map[$pid]['price'];
      $insItem->execute([$orderId, $pid, $qty, $price]);
      $updStock->execute([$qty, $pid]);
    }

    $pdo->commit();
    $_SESSION['cart'] = []; // Xóa giỏ

    header("Location: checkout.php?success=1&order=" . $orderId);
    exit;

  } catch (Throwable $e) {
    $pdo->rollBack();
    $err = "Lỗi thanh toán: " . $e->getMessage();
  }
}
?>

<h2>Xác nhận thanh toán</h2>
<?php if (!empty($err)): ?>
    <div class="alert"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<p>Bạn đang thanh toán cho đơn hàng gồm <b><?= count($cart) ?></b> sản phẩm.</p>
<p>Hình thức: <b>Thanh toán khi nhận hàng (COD)</b> (Demo)</p>

<form method="post" style="margin-top:20px; display:flex; gap:10px;">
  <button class="btn primary" type="submit">XÁC NHẬN ĐẶT HÀNG</button>
  <a class="btn" href="cart.php">Xem lại giỏ</a>
</form>

<?php require_once __DIR__ . "/includes/footer.php"; ?>