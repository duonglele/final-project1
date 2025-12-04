<?php
$servername = "127.0.0.1";   // hoặc có thể dùng localhost
$username = "root";          // tài khoản mặc định của Laragon
$password = "";              // mật khẩu trống
$dbname = "final_project";         // tên database đã tạo trong MySQL (đổi tên: final_project)
$port = 3306;                // cổng của MySQL trong Laragon

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
