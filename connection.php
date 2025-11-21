<?php
$servername = "127.0.0.1";   // hoặc có thể dùng localhost
$username = "root";          // tài khoản mặc định của Laragon
$password = "";              // mật khẩu trống
$dbname = "user_db";         // tên database đã tạo trong MySQL
$port = 3307;                // cổng của MySQL trong Laragon

// Kết nối đến MySQL
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
} else {
    // echo "Kết nối thành công!"; // có thể bật để kiểm tra
}
$conn->set_charset("utf8mb4");
?>
