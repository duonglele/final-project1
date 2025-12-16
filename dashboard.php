<?php
// htdocs/admin/dashboard.php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/header.php";

require_admin();

// Lấy danh sách sản phẩm
$products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();
?>

<h2>Admin Dashboard - Quản lý Sản phẩm</h2>

<div style="display:flex; gap:10px; flex-wrap:wrap; margin: 20px 0; padding: 15px; background: #f9fafb; border: 1px solid #eaecf0; border-radius: 12px;">
  <a class="btn primary" href="product_create.php" style="background:#e11d48; color:white;">+ Thêm sản phẩm</a>
  <a class="btn" href="users.php" style="background:white; border:1px solid #ccc;">Quản lý Users</a>
  
  <a class="btn" href="/shop.php" style="margin-left: auto; background:#101828; color:white;">
    🛍️ Về Cửa Hàng
  </a>
</div>

<table class="table" style="width:100%; border-collapse:collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
  <tr style="background:#f3f4f6; text-align: left;">
    <th style="padding:12px;">STT</th>
    <th style="padding:12px;">Ảnh</th>
    <th style="padding:12px;">Tên Sản Phẩm</th>
    <th style="padding:12px;">Giá</th>
    <th style="padding:12px; text-align:center;">Kho</th>
    <th style="padding:12px; text-align:right;">Hành động</th>
  </tr>

  <?php $stt = 0; foreach ($products as $p): $stt++; ?>
    <tr style="border-bottom: 1px solid #eee;">
      <td style="padding:12px;"><?= $stt ?></td>
      <td style="padding:12px; width: 60px;">
        <?php if($p['image']): ?>
            <img src="/uploads/<?= htmlspecialchars($p['image']) ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
        <?php else: ?>
            <span style="font-size:10px; color:#999;">No img</span>
        <?php endif; ?>
      </td>
      <td style="padding:12px;">
        <b><?= htmlspecialchars($p['name']) ?></b>
      </td>
      <td style="padding:12px;"><?= number_format((float)$p['price'], 0, ',', '.') ?> ₫</td>
      <td style="padding:12px; text-align:center;">
        <?= (int)$p['stock'] ?>
      </td>
      <td style="padding:12px; text-align:right;">
        <a class="btn" href="product_edit.php?id=<?= (int)$p['id'] ?>" style="padding: 4px 10px; font-size: 13px;">Sửa</a>
        <a class="btn" href="product_delete.php?id=<?= (int)$p['id'] ?>" 
           onclick="return confirm('Xoá sản phẩm này?')" style="padding: 4px 10px; font-size: 13px; color: red; border-color: red;">Xoá</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>