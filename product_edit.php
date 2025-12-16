<?php
// htdocs/admin/product_edit.php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/header.php";
require_admin();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM products WHERE id=?");
$st->execute([$id]);
$p = $st->fetch();
if (!$p) exit("Sản phẩm không tồn tại");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $sku = trim($_POST['sku'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $stock = (int)($_POST['stock'] ?? 0);
  $desc = trim($_POST['description'] ?? '');

  $imageName = $p['image'];
  if (!empty($_FILES['image']['name'])) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = uniqid("p_", true) . "." . strtolower($ext);
    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . "/../uploads/" . $imageName);
  }

  $up = $pdo->prepare("UPDATE products SET sku=?, name=?, price=?, stock=?, description=?, image=? WHERE id=?");
  $up->execute([$sku,$name,$price,$stock,$desc,$imageName,$id]);
  
  header("Location: dashboard.php"); exit;
}
?>

<h2>Sửa sản phẩm #<?= $id ?></h2>
<form method="post" enctype="multipart/form-data" class="form">
  <label>SKU:</label>
  <input name="sku" value="<?= e($p['sku'] ?? '') ?>" placeholder="SKU" style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Tên sản phẩm:</label>
  <input name="name" value="<?= e($p['name']) ?>" placeholder="Tên" required style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Giá bán:</label>
  <input name="price" type="number" step="1" value="<?= e((string)$p['price']) ?>" placeholder="Giá" style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Tồn kho:</label>
  <input name="stock" type="number" value="<?= (int)$p['stock'] ?>" placeholder="Số lượng" style="width:100%; padding:10px; margin-bottom:10px;">
  
  <label>Mô tả chi tiết:</label>
  <textarea name="description" rows="6" style="width:100%; padding:10px; margin-bottom:10px;"><?= e($p['description'] ?? '') ?></textarea>
  
  <label>Ảnh hiện tại:</label>
  <?php if($p['image']): ?>
    <img src="/uploads/<?= e($p['image']) ?>" style="height:80px; display:block; margin:5px 0 10px;">
  <?php else: ?>
    <div class="muted">Chưa có ảnh</div>
  <?php endif; ?>
  
  <label>Thay ảnh mới (nếu muốn):</label>
  <input type="file" name="image" style="margin-bottom:20px;">
  
  <button class="btn primary">Cập nhật</button>
</form>

<a class="btn mt-16" href="dashboard.php">← Quay lại Dashboard</a>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>