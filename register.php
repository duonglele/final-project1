<?php
// htdocs/register.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';

  if ($name && $email && $pass) {
    try {
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $st = $pdo->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?, 'user')");
      $st->execute([$name,$email,$hash]);
      
      echo "<div class='alert' style='background:#dcfce7; color:#166534; text-align:center;'>✅ Đăng ký thành công! Đang chuyển hướng...</div>";
      echo "<meta http-equiv='refresh' content='2;url=login.php'>";
      require_once __DIR__ . "/includes/footer.php";
      exit;

    } catch (Throwable $e) {
      $err = "Email này đã được sử dụng.";
    }
  } else $err = "Vui lòng nhập đủ thông tin.";
}
?>

<div class="auth-page">
  <div class="auth-card">
    <h2>Đăng ký tài khoản</h2>
    
    <?php if (!empty($err)) echo "<div class='alert' style='background:#fee2e2; color:#b91c1c;'>".htmlspecialchars($err)."</div>"; ?>
    
    <form method="post" class="form">
      <label>Họ và tên:</label>
      <input name="name" placeholder="Ví dụ: Nguyễn Văn A" required>
      
      <label>Email:</label>
      <input name="email" type="email" placeholder="email@example.com" required>
      
      <label>Mật khẩu:</label>
      <input type="password" name="password" placeholder="Tự đặt mật khẩu..." required>
      
      <button class="btn primary" style="width:100%; margin-top:10px;">Tạo tài khoản</button>
    </form>
    
    <div class="mt-16 center">
        Đã có tài khoản? <a href="login.php" style="color:#e11d48;">Đăng nhập</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
