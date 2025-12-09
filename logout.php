<?php
session_start();

// Hủy tất cả biến session
$_SESSION = array();

// XÓA COOKIE GHI NHỚ ĐĂNG NHẬP (nếu có)
setcookie('user_id',   '', time() - 3600, "/", "", false, true);
setcookie('username',  '', time() - 3600, "/", "", false, true);
setcookie('user_role', '', time() - 3600, "/", "", false, true);

// Xóa session cookie (PHPSESSID)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Quay về trang đăng nhập
header("Location: login.php");
exit;
?>
