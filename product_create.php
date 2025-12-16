<?php
// htdocs/admin/product_create.php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/header.php";

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name'] ?? '');
  $sku   = trim($_POST['sku'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $stock = (int)($_POST['stock'] ?? 0);
  $desc  = trim($_POST['description'] ?? '');

  $imageName = null;

  if (!empty($_FILES['image']['name']) && ($_FILES['image']['error'] ?? 1) === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['image']['tmp_name'];
    $orig = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $allow = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allow, true)) {
      $err = "Ảnh không hợp lệ (chỉ jpg/jpeg/png/webp/gif).";
    } else {
      $imageName = uniqid('p_', true) . "." . $ext;
      $dest = __DIR__ . "/../uploads/" . $imageName;   
      if (!move_uploaded_file($tmp, $dest)) {
        $err = "Không upload được ảnh. Kiểm tra quyền thư mục uploads.";
        $imageName = null;
      }
    }
  }

  if (empty($err)) {
    $st = $pdo->prepare("INSERT INTO products(name,sku,price,stock,description,image,created_at) VALUES(?,?,?,?,?,?,NOW())");
    $st->execute([$name, $sku, $price, $stock, $desc, $imageName]);
    header("Location: dashboard.php");
    exit;
  }
}
?>

<h2>Thêm sản phẩm</h2>
<?php if (!empty($err)): ?>
  <div class="alert" style="color:red; border:1px solid red; padding:10px; margin-bottom:10px;"><?= e($err) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form">
  <label>Tên sản phẩm:</label>
  <input name="name" placeholder="Ví dụ: iPhone 15 Pro Max" required style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Mã SKU:</label>
  <input name="sku" placeholder="Ví dụ: IP15PM-256" required style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Giá bán (VNĐ):</label>
  <input type="number" name="price" placeholder="Nhập giá tiền (chỉ nhập số)..." step="1" required style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Số lượng trong kho:</label>
  <input type="number" name="stock" placeholder="Số lượng" required style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Mô tả chi tiết:</label>
  <textarea name="description" placeholder="Mô tả sản phẩm..." rows="5" style="width:100%; padding:10px; margin-bottom:10px;"></textarea>

  <label class="muted" style="display:block; margin-top:10px;">Ảnh sản phẩm:</label>
  <input type="file" name="image" accept="image/*" style="margin-bottom:20px;">

  <button class="btn primary" type="submit">Lưu sản phẩm</button>
</form>

<a class="btn mt-16" href="dashboard.php">← Quay lại Dashboard</a>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>