<?php
// htdocs/logout.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/remember.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

remember_me_clear($pdo);
$_SESSION = [];
session_destroy();

// Logout xong về Shop (để khách xem hàng tiếp)
header("Location: shop.php");
exit;
?>