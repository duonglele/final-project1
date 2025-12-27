<?php
// htdocs/shop.php
// BẬT HIỂN THỊ LỖI (Debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";

// Logic tìm kiếm
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR sku LIKE ? ORDER BY created_at DESC");
  $like = "%$q%";
  $stmt->execute([$like, $like]);
} else {
  $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
}
$products = $stmt->fetchAll();
?>

<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
  <div style="background-color: #eff6ff; border: 1px solid #1d4ed8; color: #1e40af; padding: 15px; border-radius: 12px; margin-bottom: 24px; text-align: center;">
    <strong>Xin chào Admin!</strong>
    <a href="/admin/dashboard.php" class="btn primary" style="margin-left: 10px; display: inline-flex; align-items: center; gap: 5px;">
       ⚙️ Vào Dashboard Quản Lý
    </a>
  </div>
<?php endif; ?>

<div class="row">
  <form class="search" method="get" style="display:flex; gap:10px; margin-bottom:20px;">
    <input name="q" placeholder="Tìm linh kiện (RAM, SSD,...)" value="<?= htmlspecialchars($q) ?>" style="flex:1; padding:10px; border-radius:8px; border:1px solid #ccc;">
    <button class="btn primary" type="submit">Tìm</button>
  </form>
</div>

<div class="grid">
<?php if (count($products) === 0): ?>
    <div style="grid-column: 1/-1; text-align:center; padding: 20px;">
        <p>Không tìm thấy sản phẩm nào.</p>
    </div>
<?php endif; ?>

<?php foreach ($products as $p): ?>
  <div class="card">
    <div class="thumb">
      <?php if (!empty($p['image'])): ?>
        <img src="/uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
      <?php else: ?>
        <div class="thumb-placeholder" style="color:#999; display:flex; align-items:center; justify-content:center; height:100%;">No Image</div>
      <?php endif; ?>

      <?php if ((int)$p['stock'] <= 0): ?>
        <span class="badge soldout" style="position:absolute; top:10px; right:10px;">HẾT HÀNG</span>
      <?php endif; ?>
    </div>

    <div class="card-body">
<div class="title" style="font-weight:bold; height:auto; overflow:visible; margin-bottom: 5px;"><?= htmlspecialchars($p['name']) ?></div>
      <div class="muted">SKU: <?= htmlspecialchars($p['sku'] ?? '-') ?></div>
      <div class="price" style="color:#e11d48; font-weight:bold;"><?= number_format((float)$p['price'], 0, ',', '.') ?> ₫</div>
      <div class="muted">Kho: <?= (int)$p['stock'] ?></div>

      <div class="actions" style="margin-top:10px; display:flex; gap:5px;">
        <a class="btn" href="product.php?id=<?= (int)$p['id'] ?>" style="flex:1; justify-content:center; border:1px solid #ddd;">Chi tiết</a>

        <?php if ((int)$p['stock'] > 0): ?>
          <form method="post" action="add_to_cart.php" style="flex:1;">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="qty" value="1">
            <input type="hidden" name="redirect" value="back">
            <button class="btn primary" type="submit" style="width:100%;">Thêm giỏ</button>
          </form>
        <?php else: ?>
          <button class="btn" disabled style="flex:1;">Hết hàng</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
