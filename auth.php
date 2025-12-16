<?php
// includes/auth.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . "/remember.php";

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

// Auto login bằng cookie
if (!current_user()) {
  try {
    // Biến $pdo được gọi từ file bên ngoài (global scope)
    global $pdo; 
    if (isset($pdo) && $pdo instanceof PDO) {
      remember_me_try_login($pdo);
    }
  } catch (Throwable $e) {
    // bỏ qua lỗi
  }
}

function require_login(): void {
  if (!current_user()) {
    header("Location: /login.php");
    exit;
  }
}

function require_admin(): void {
  require_login();
  if ((current_user()['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Bạn không có quyền truy cập trang này.");
  }
}