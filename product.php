<?php
// htdocs/product.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) { http_response_code(404); exit("Sản phẩm không tồn tại"); }

$stock = (int)($p['stock'] ?? 0);
$img = !empty($p['image']) ? "/uploads/" . $p['image'] : "";
?>

<div class="breadcrumb">
  <a href="/shop.php">Cửa hàng</a> <span class="sep">/</span>
  <span>Chi tiết sản phẩm</span>
  <span class="sep">/</span>
  <span><?= htmlspecialchars($p['name']) ?></span>
</div>

<div class="pd-wrap">
  <div class="pd-gallery">
    <div class="pd-main-img">
      <?php if ($img): ?>
        <img id="mainImg" src="<?= htmlspecialchars($img) ?>" alt="">
      <?php else: ?>
        <div class="muted" style="height:300px; display:flex; align-items:center; justify-content:center;">No Image</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="pd-info">
    <h1 class="pd-title"><?= htmlspecialchars($p['name']) ?></h1>
    <div class="pd-sku">SKU: <strong><?= htmlspecialchars($p['sku'] ?? '-') ?></strong></div>

    <div class="pd-pricebox">
      <div class="label">Giá sản phẩm</div>
      <div class="price"><?= number_format((float)$p['price'], 0, ',', '.') ?> ₫</div>
      <div class="pd-stock">
        Tình trạng:
        <?php if ($stock > 0): ?>
          <strong style="color:green;">Còn hàng (<?= $stock ?>)</strong>
        <?php else: ?>
          <strong style="color:red;">Hết hàng</strong>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($stock > 0): ?>
      <form method="post" action="cart.php" class="pd-cta">
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="qty" value="1">

        <button class="btn primary" type="submit" name="action" value="buy_now">MUA NGAY</button>
        <button class="btn outline" type="submit" name="action" value="add">Thêm vào giỏ</button>
      </form>
    <?php else: ?>
      <div class="pd-cta">
        <button class="btn" disabled>Hết hàng</button>
        <a class="btn outline" href="/shop.php">Xem sản phẩm khác</a>
      </div>
    <?php endif; ?>

  </div>
</div>

<div class="pd-sections">
  <div class="pd-section">
    <h3>Mô tả sản phẩm</h3>
    <div class="content"><?= nl2br(htmlspecialchars($p['description'] ?? '')) ?></div>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>