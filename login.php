<?php
// Bắt đầu session
session_start();

// Thiết lập thời gian hết hạn cookie (30 ngày)
$cookie_expiry = time() + (30 * 24 * 60 * 60); 

/*
 * 1. TỰ ĐĂNG NHẬP BẰNG COOKIE (REMEMBER ME)
 * - Nếu chưa có session nhưng trình duyệt còn các cookie user_id, username
 * thì tạo lại session và chuyển hướng luôn.
 */
if (
    !isset($_SESSION['user_id']) &&
    isset($_COOKIE['user_id'], $_COOKIE['username'])
) {
    $_SESSION['user_id']  = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    
    header("Location: home.php");
    exit;
}

// 2. Nếu đã có session → chuyển hướng luôn
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

include 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Lấy dữ liệu
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập Username/Email và Mật khẩu.";
    } else {
        // 2. Truy vấn người dùng
        $sql = "SELECT id, username, password FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashedPassword = $user['password'];

            // 3. Kiểm tra mật khẩu
            if (
                (substr($hashedPassword, 0, 4) === '$2y$' && password_verify($password, $hashedPassword)) ||
                ($password === $hashedPassword)
            ) {
                // ===== ĐĂNG NHẬP THÀNH CÔNG =====

                // Lưu thông tin vào session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                /*
                 * 4. GHI NHỚ ĐĂNG NHẬP BẰNG COOKIE (NẾU TICK)
                 * - Lưu user_id, username vào cookie ~30 ngày
                 */
                if (!empty($_POST['remember'])) {
                    // Cookie chỉ dùng để server đọc (httponly: true)
                    setcookie("user_id",  $user['id'],      $cookie_expiry, "/", "", false, true);
                    setcookie("username", $user['username'], $cookie_expiry, "/", "", false, true);
                } else {
                    // Không chọn "Ghi nhớ đăng nhập" → xóa cookie (nếu có)
                    setcookie("user_id",  "", time() - 3600, "/", "", false, true);
                    setcookie("username", "", time() - 3600, "/", "", false, true);
                }

                header("Location: home.php");
                exit;

            } else {
                $error = "Username/Email hoặc Mật khẩu không đúng.";
            }
        } else {
            $error = "Username/Email hoặc Mật khẩu không đúng.";
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
        /* CSS đã được tối ưu cho hình nền Du lịch (tone Cam/Vàng) */
        body { 
            font-family: sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0;
            background-image: url('image_d0075e.png'); /* Hình nền Du lịch */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .container { 
            background-color: rgba(255, 255, 255, 0.9); /* Trắng trong suốt */
            padding: 40px; 
            border-radius: 10px; /* Bo góc */
            width: 400px; 
            text-align: center; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        h2 {
            font-size: 28px;
            font-weight: bold;
            color: #ff5117; /* Màu cam chính */
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
        }
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 12px; 
            margin: 8px 0; 
            border: 1px solid #ff5117; /* Viền cam */
            border-radius: 4px; 
            box-sizing: border-box; 
            background-color: #fce4d4; /* Nền nhạt */
        }
        .btn-login { 
            background-color: #ff5117; 
            color: white; 
            padding: 14px 20px; 
            margin: 8px 0; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 16px; 
            font-weight: bold; 
        }
        .btn-login:hover {
            background-color: #cc4113;
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
        .btn-register:hover {
            background-color: #ff5117;
            color: #fff;
        }
        .error {
            padding: 10px; margin-bottom: 15px; border-radius: 4px; background-color: #fdd; color: #a00; border: 1px solid #a00;
        }
        .register-link { margin-top: 15px; display: block; text-align: center; }
        .remember {
            text-align: left;
            margin: 8px 0 16px;
            font-size: 14px;
            color: #555;
            display: block;
        }
        .remember label {
            display: flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ĐĂNG NHẬP HỆ THỐNG</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Username hoặc Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <div class="remember">
                <label>
                    <input type="checkbox" name="remember" value="1">
                    Ghi nhớ đăng nhập
                </label>
            </div>

            <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
        </form>
        
        <div class="register-link">
            Chưa có tài khoản?
            <a href="register.php" style="text-decoration: none;">
                 <button type="button" class="btn-register">ĐĂNG KÝ</button>
            </a>
        </div>
    </div>
</body>
</html>
