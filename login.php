<?php
// htdocs/login.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";

// Nếu đã login rồi thì về Shop
if (current_user()) {
  header("Location: shop.php");
  exit;
}

// Xử lý khi bấm nút Đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $remember = !empty($_POST['remember']);

  $st = $pdo->prepare("SELECT id,name,email,role,password_hash FROM users WHERE email=?");
  $st->execute([$email]);
  $u = $st->fetch(PDO::FETCH_ASSOC);

  if ($u && password_verify($pass, $u['password_hash'])) {
    $_SESSION['user'] = [
      'id'    => (int)$u['id'],
      'name'  => $u['name'],
      'email' => $u['email'],
      'role'  => $u['role'],
    ];

    if ($remember) {
      require_once __DIR__ . "/includes/remember.php";
      remember_me_set($pdo, (int)$u['id'], 30);
    }

    header("Location: shop.php");
    exit;
  } else {
    $err = "Sai email hoặc mật khẩu.";
  }
}
?>

<div class="auth-page">
  <div class="auth-card">
    <h2>Đăng nhập</h2>
    
    <?php if (!empty($err)) echo "<div class='alert' style='background:#fee2e2; color:#b91c1c;'>".htmlspecialchars($err)."</div>"; ?>
    
    <form method="post" class="form">
      <label>Email:</label>
      <input name="email" type="email" placeholder="Nhập email..." required>
      
      <label>Mật khẩu:</label>
      <input type="password" name="password" placeholder="Nhập mật khẩu..." required>
      
      <div class="auth-row">
        <label>
          <input type="checkbox" name="remember" value="1">
          Ghi nhớ đăng nhập
        </label>
      </div>
      
      <button class="btn primary" style="width:100%; margin-top:10px;">Đăng nhập</button>
    </form>
    
    <div class="mt-16 center">
        Chưa có tài khoản? <a href="register.php" style="color:#e11d48;">Đăng ký ngay</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>