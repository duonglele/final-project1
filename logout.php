<?php
session_start();

// 1. Hủy bỏ tất cả các biến session
$_SESSION = array();

// 2. Xóa Session ID Cookie (Cookie mặc định của PHP)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. XÓA COOKIE "GHI NHỚ ĐĂNG NHẬP" (Quan trọng nhất)
// Cần set thời gian hết hạn (time() - 3600) và PATH (/)
setcookie("user_id",  "", time() - 3600, "/", "", false, true);
setcookie("username", "", time() - 3600, "/", "", false, true);

// 4. Hủy session
session_destroy();

// 5. Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit;
?>
