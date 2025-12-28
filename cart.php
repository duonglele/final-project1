<?php
// htdocs/cart.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // 1. XỬ LÝ ADD (THÊM) / BUY NOW (MUA NGAY)
  if ($action === 'add' || $action === 'buy_now') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);
    if ($qty < 1) $qty = 1;

    if ($pid > 0) {
      $st = $pdo->prepare("SELECT stock FROM products WHERE id=?");
      $st->execute([$pid]);
      $row = $st->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        $stock = (int)$row['stock'];
        if ($stock <= 0) {
          $_SESSION['flash'] = "Sản phẩm đã hết hàng.";
        } else {
          $current = $_SESSION['cart'][$pid] ?? 0;
          $_SESSION['cart'][$pid] = min($stock, $current + $qty);
          $_SESSION['flash'] = "Đã thêm vào giỏ hàng.";
        }
      }
    }
    
    if ($action === 'buy_now') {
        header("Location: checkout.php");
        exit;
    }
    header("Location: cart.php"); exit;
  }

  // 2. XỬ LÝ UPDATE (CẬP NHẬT/XÓA) HOẶC CHECKOUT (THANH TOÁN)
  if ($action === 'update' || $action === 'go_checkout') {
    // Nếu bấm xóa từng sản phẩm
    if (!empty($_POST['remove_id'])) {
      unset($_SESSION['cart'][(int)$_POST['remove_id']]);
      header("Location: cart.php"); exit;
    }

    // Cập nhật số lượng mới cho toàn bộ giỏ
    foreach (($_POST['qtys'] ?? []) as $k => $v) {
      $pid = (int)$k; $want = (int)$v;
      if ($want <= 0) { unset($_SESSION['cart'][$pid]); continue; }
      
      // Kiểm tra tồn kho lần nữa
      $st = $pdo->prepare("SELECT stock FROM products WHERE id=?");
      $st->execute([$pid]);
      $row = $st->fetch(PDO::FETCH_ASSOC);
      if (!$row) { unset($_SESSION['cart'][$pid]); continue; }
      
      $_SESSION['cart'][$pid] = min((int)$row['stock'], $want);
    }
    
    // Nếu nút bấm là "go_checkout" -> Chuyển sang trang thanh toán
    if ($action === 'go_checkout') {
        header("Location: checkout.php");
        exit;
    }

    // Mặc định tải lại trang giỏ hàng
    header("Location: cart.php"); exit;
  }
}

// LẤY DỮ LIỆU ĐỂ HIỂN THỊ
$cart = $_SESSION['cart'];
$items = [];
$total = 0.0;

if ($cart) {
  $ids = array_keys($cart);
  if (count($ids) > 0) {
      $in  = implode(',', array_fill(0, count($ids), '?'));
      $st  = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
      $st->execute($ids);
      $products = $st->fetchAll(PDO::FETCH_ASSOC);
    
      $map = [];
      foreach ($products as $p) $map[(int)$p['id']] = $p;
    
      foreach ($cart as $pid => $qty) {
        if (isset($map[$pid])) {
          $p = $map[$pid];
          $line = (float)$p['price'] * (int)$qty;
          $total += $line;
          $items[] = ['p' => $p, 'qty' => (int)$qty, 'line' => $line];
        }
      }
  }
}
?>

<h2>Giỏ hàng của bạn</h2>
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (!$items): ?>
  <p>Giỏ hàng trống.</p>
  <a class="btn" href="shop.php">← Quay lại mua sắm</a>
<?php else: ?>

<form method="post">
  <input type="hidden" name="action" value="update">
  
  <table class="table">
    <tr>
      <th>Sản phẩm</th>
      <th>Đơn giá</th>
      <th>Số lượng</th>
      <th>Thành tiền</th>
      <th>Xóa</th>
    </tr>
    <?php foreach ($items as $it): $p = $it['p']; ?>
      <tr>
        <td>
            <a href="product.php?id=<?= (int)$p['id'] ?>"><b><?= htmlspecialchars($p['name']) ?></b></a><br>
            <small>Kho còn: <?= (int)$p['stock'] ?></small>
        </td>
        <td><?= number_format((float)$p['price'], 0, ',', '.') ?> ₫</td>
        <td>
          <input type="number" name="qtys[<?= (int)$p['id'] ?>]" value="<?= (int)$it['qty'] ?>" min="1" max="<?= (int)$p['stock'] ?>" style="width:60px; padding:5px;">
        </td>
        <td><?= number_format((float)$it['line'], 0, ',', '.') ?> ₫</td>
        <td>
           <button class="btn" type="submit" name="remove_id" value="<?= (int)$p['id'] ?>" style="color:red; border-color:red;" onclick="return confirm('Xóa khỏi giỏ?')">Xóa</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <div class="summary" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;">
    <button class="btn" type="submit">Cập nhật số lượng</button>
    
    <div style="text-align:right;">
        <div class="total" style="font-size:20px; font-weight:bold; margin-bottom:10px;">Tổng cộng: <?= number_format($total, 0, ',', '.') ?> ₫</div>
        <a class="btn" href="shop.php" style="margin-right:10px;">Mua thêm</a>
        
        <button class="btn primary" type="submit" name="action" value="go_checkout">Tiến hành Thanh toán</button>
    </div>
  </div>
</form>
<?php endif; ?>

<?php require_once __DIR__ . "/includes/footer.php"; ?>