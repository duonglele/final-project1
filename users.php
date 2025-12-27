<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/header.php";

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  // --- ĐOẠN MỚI THÊM: XỬ LÝ XÓA USER BẮT ĐẦU TỪ ĐÂY ---
  if (isset($_POST['delete_id'])) {
      $delId = (int)$_POST['delete_id'];
      
      // KIỂM TRA KHÔNG ĐƯỢC XÓA CHÍNH MÌNH
      if ($delId === (int)current_user()['id']) {
          $_SESSION['flash'] = "Không thể tự xóa tài khoản của chính mình.";
      } else {
          // THỰC HIỆN LỆNH XÓA TRONG DATABASE
          $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
          $stmt->execute([$delId]);
          $_SESSION['flash'] = "Đã xóa thành công user ID: $delId";
      }
      header("Location: users.php"); exit;
  }


  $uid  = (int)($_POST['user_id'] ?? 0);
  // ... (code cũ xử lý update role) ...
  if ($uid > 0) { // Thêm điều kiện check $uid > 0 để tránh xung đột với lệnh xóa bên trên
      $role = $_POST['role'] ?? 'user';
      if (!in_array($role, ['user','admin'], true)) $role = 'user';

      if ($uid === (int)current_user()['id'] && $role !== 'admin') {
        $_SESSION['flash'] = "Không thể tự hạ quyền chính mình.";
        header("Location: users.php"); exit;
      }

      $st = $pdo->prepare("UPDATE users SET role=? WHERE id=?");
      $st->execute([$role, $uid]);

      $_SESSION['flash'] = "Đã cập nhật quyền.";
      header("Location: users.php"); exit;
  }
}
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

$users = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY (role='admin') DESC, id ASC")->fetchAll();
?>

<h2>Quản lý Users</h2>

<div style="display:flex; gap:10px; flex-wrap:wrap; margin: 12px 0;">
  <a class="btn" href="/admin/dashboard.php">← Về Dashboard</a>
  <a class="btn" href="/shop.php">🛍️ Về Cửa hàng</a>
</div>

<?php if ($flash): ?>
  <div class="alert"><?= e($flash) ?></div>
<?php endif; ?>

<table class="table">
  <tr>
    <th>STT</th>
    <th>Họ tên</th>
    <th>Email</th>
    <th>Role</th>
    <th>Hành động</th>
  </tr>

  <?php $stt = 0; foreach ($users as $u): $stt++; ?>
    <tr>
      <td><?= $stt ?></td>
      <td><?= e($u['name']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td><b><?= e($u['role']) ?></b></td>
      <td>
        <form method="post" style="display:inline-block; margin-right:5px;">
          <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
          <select name="role" style="padding:5px; border-radius:5px;">
            <option value="user"  <?= $u['role']==='user'?'selected':'' ?>>user</option>
            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
          </select>
          <button class="btn" type="submit" style="padding:5px 10px;">Lưu</button>
        </form>

        <form method="post" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa user này không? Hành động này không thể hoàn tác!');">
            <input type="hidden" name="delete_id" value="<?= (int)$u['id'] ?>">
            <button type="submit" class="btn" style="background-color:red; color:white; border:none; padding:6px 10px;">Xóa</button>
        </form>
        </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
