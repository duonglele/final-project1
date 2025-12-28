<?php
// htdocs/admin/users.php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/header.php";

require_admin();

// Xử lý Form gửi lên
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $uid    = (int)($_POST['user_id'] ?? 0);
  $action = $_POST['action'] ?? ''; // Lấy hành động (update hoặc delete)
  
  $currentUserId = (int)current_user()['id'];

  // --- CASE 1: XÓA USER ---
  if ($action === 'delete') {
    // 1. Bảo vệ: Không cho tự xóa chính mình
    if ($uid === $currentUserId) {
        $_SESSION['flash'] = "Lỗi: Bạn không thể tự xóa tài khoản của mình.";
    } else {
        try {
            // 2. Thực hiện xóa
            $st = $pdo->prepare("DELETE FROM users WHERE id=?");
            $st->execute([$uid]);
            $_SESSION['flash'] = "Đã xóa thành công User #$uid.";
        } catch (PDOException $e) {
            // 3. Xử lý lỗi nếu User đã có đơn hàng (Ràng buộc khóa ngoại)
            if ($e->getCode() == '23000') {
                $_SESSION['flash'] = "Không thể xóa: User này đã có dữ liệu đơn hàng cũ.";
            } else {
                $_SESSION['flash'] = "Lỗi Database: " . $e->getMessage();
            }
        }
    }
    header("Location: users.php"); exit;
  }

  // --- CASE 2: CẬP NHẬT QUYỀN (Code cũ của bạn) ---
  if ($action === 'update') {
      $role = $_POST['role'] ?? 'user';
      if (!in_array($role, ['user','admin'], true)) $role = 'user';

      // Không cho tự hạ quyền chính mình
      if ($uid === $currentUserId && $role !== 'admin') {
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
  <div class="alert" style="padding:10px; background:#e0f2fe; color:#0369a1; border-radius:5px; margin-bottom:15px;">
      <?= e($flash) ?>
  </div>
<?php endif; ?>

<table class="table">
  <tr>
    <th>STT</th>
    <th>Họ tên</th>
    <th>Email</th>
    <th>Role</th>
    <th style="width: 200px;">Hành động</th>
  </tr>

  <?php $stt = 0; foreach ($users as $u): $stt++; ?>
    <tr>
      <td><?= $stt ?></td>
      <td><?= e($u['name']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td>
        <?php if($u['role'] === 'admin'): ?>
            <strong style="color:red;">admin</strong>
        <?php else: ?>
            <span style="color:green;">user</span>
        <?php endif; ?>
      </td>
      <td>
        <form method="post" style="display:flex; gap:5px; align-items:center;">
          <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
          
          <select name="role" style="padding:5px; border-radius:5px; border:1px solid #ccc;">
            <option value="user"  <?= $u['role']==='user'?'selected':'' ?>>user</option>
            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
          </select>
          
          <button class="btn primary" type="submit" name="action" value="update" style="padding:5px 10px; font-size:12px;">Lưu</button>

          <button class="btn" type="submit" name="action" value="delete"
                  style="padding:5px 10px; font-size:12px; background-color:#ef4444; color:white; border:none;"
                  onclick="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa User: <?= e($u['name']) ?>?');">
            Xóa
          </button>

        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
