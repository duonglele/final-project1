<?php
// Bắt đầu session
session_start();

// Nếu người dùng đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: home.php"); // Thay đổi thành trang chính của bạn
    exit;
}

include 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Lấy dữ liệu
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập Username và Mật khẩu.";
    } else {

        // 2. Truy vấn người dùng -- LẤY CẢ ROLE
        $sql = "SELECT id, username, password, role FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashedPassword = $user['password'];

            // 3. Kiểm tra mật khẩu
            if (
                // Nếu mật khẩu đã mã hóa -> dùng password_verify
                (substr($hashedPassword, 0, 4) === '$2y$' && password_verify($password, $hashedPassword)) ||
                // Nếu mật khẩu lưu dạng thường -> so sánh trực tiếp
                ($password === $hashedPassword)
            ) {

                // Đăng nhập thành công

                //Lưu thông tin cơ bản vào session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; //Lưu phân quyền tài khoản vào session

                //======Lưu role vào cookie 7 ngày=====
                setcookie(
                    "user_role",
                    $user['role'],
                    time() + (7 * 24 * 60 * 60), //7 ngày
                    "/",
                    "",
                    false,
                    true //httponly --> giúp bảo mật hơn
                );

                //=======Điều hướng theo quyền truy cập=======
                if ($user['role'] === 'admin') {
                    header("Location: home.php");   //Trang quản lý sản phẩm
                }
                else {
                    header("Location: index.php");   //Trang xem sản phẩm sản phẩm
                }
                exit;

            } else {
                $error = "Username hoặc Mật khẩu không đúng.";
            }
        } else {
            $error = "Username hoặc Mật khẩu không đúng.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Chính - Đăng Nhập</title>
    <style>
        /* CSS tối thiểu để mô phỏng giao diện */
        body { 
            font-family: sans-serif; 
            background-color: #d9d9d9; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        
        .container { 
            background-color: #fff; 
            padding: 40px; 
            border-radius: 5px; 
            width: 400px; 
            text-align: center;
        }

        input[type="text"], input[type="password"] { 
            width: 100%; 
            adding: 12px;
            margin: 8px 0; 
            display: inline-block; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
            background-color: #e6e6e6; 
        }

        .btn-login { 
            background-color: #ff5117; 
            color: white; 
            padding: 14px 20px; 
            margin: 8px 0; 
            border: 2px solid #ff5117; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%; 
            ont-size: 16px; 
            font-weight: bold; 
        }

        .btn-register { 
            background-color: #fff; 
            color: #ff5117; 
            padding: 14px 20px; 
            margin-top: 20px; 
            border: 2px solid #ff5117; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 16px; 
            font-weight: bold; 
        }

        .register-link {
            margin-top: 15px; 
            display: block; 
            text-align: left; 
        }

        .error {
            padding: 10px; 
            margin-bottom: 15px; 
            border-radius: 4px; 
            background-color: #fdd; 
            color: #a00; 
            border: 1px solid #a00; 
        }

        /* CSS -- ĐĂNG NHẬP HỆ THỐNG */
        h2 {
            font-size: 24px;
            font-weight: bold;
            color: #ff5117;
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
        }

        /* Button -- ĐĂNG NHẬP (Hover) */
        .btn-login:hover {
            /* transform: translateY(-4px);
            box-shadow: 0 4px 8px #0000001a; */
            background-color: #fff;
            color: #ff5117;
            border: 2px solid #ff5117;
        }

        /* Button -- ĐĂNG KÝ TÀI KHOẢN (Hover) */
        .btn-register:hover {
            background-color: #ff5117;
            color: #fff;
            border: 2px solid #ff5117;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ĐĂNG NHẬP HỆ THỐNG</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
        </form>
        
        <div class="register-link">
            Chưa có tài khoản
            <a href="register.php" style="text-decoration: none;">
                 <button type="button" class="btn-register">ĐĂNG KÝ</button>
            </a>
        </div>
    </div>
</body>
</html>