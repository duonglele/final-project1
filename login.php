<?php
// Bắt đầu session
session_start();

/*
 * 1. TỰ ĐĂNG NHẬP BẰNG COOKIE (REMEMBER ME)
 * - Nếu chưa có session nhưng trình duyệt còn các cookie user_id, username, role
 *   thì tạo lại session và chuyển hướng luôn.
 */
if (
    !isset($_SESSION['user_id']) &&
    isset($_COOKIE['user_id'], $_COOKIE['username'], $_COOKIE['user_role'])
) {
    $_SESSION['user_id']  = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['role']     = $_COOKIE['user_role'];

    if ($_SESSION['role'] === 'admin') {
        header("Location: home.php");   // Trang admin
    } else {
        header("Location: index.php");  // Trang khách xem sản phẩm
    }
    exit;
}

/*
 * 2. Nếu đã có session → chuyển hướng luôn theo role
 */
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: home.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

include 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Lấy dữ liệu
    $username = trim($_POST['username']);  // cho phép nhập username hoặc email
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập Username/Email và Mật khẩu.";
    } else {

        // 2. Truy vấn người dùng theo bảng users trong ERD (có cột role)
        $sql = "SELECT id, username, email, password, role 
                FROM users 
                WHERE username = ? OR email = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashedPassword = $user['password'];

            // 3. Kiểm tra mật khẩu
            if (
                // Nếu mật khẩu đã mã hóa bcrypt
                (substr($hashedPassword, 0, 4) === '$2y$' && password_verify($password, $hashedPassword))
                // Nếu đang lưu mật khẩu thường (dev/test) thì so sánh trực tiếp
                || ($password === $hashedPassword)
            ) {
                // ===== ĐĂNG NHẬP THÀNH CÔNG =====

                // Lưu thông tin vào session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                /*
                 * 4. GHI NHỚ ĐĂNG NHẬP BẰNG COOKIE (NẾU TICK)
                 *    - Lưu user_id, username, role vào cookie ~30 ngày
                 */
                if (!empty($_POST['remember'])) {
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 ngày

                    // Cookie chỉ dùng để server đọc (httponly)
                    setcookie("user_id",   $user['id'],       $expiry, "/", "", false, true);
                    setcookie("username",  $user['username'], $expiry, "/", "", false, true);
                    setcookie("user_role", $user['role'],     $expiry, "/", "", false, true);
                } else {
                    // Không chọn "Ghi nhớ đăng nhập" → xóa cookie (nếu có)
                    setcookie("user_id",   "", time() - 3600, "/", "", false, true);
                    setcookie("username",  "", time() - 3600, "/", "", false, true);
                    setcookie("user_role", "", time() - 3600, "/", "", false, true);
                }

                // 5. Chuyển hướng theo phân quyền
                if ($user['role'] === 'admin') {
                    header("Location: home.php");   // Trang quản lý sản phẩm
                } else {
                    header("Location: index.php");  // Trang khách
                }
                exit;

            } else {
                $error = "Username/Email hoặc Mật khẩu không đúng.";
            }
        } else {
            $error = "Username/Email hoặc Mật khẩu không đúng.";
        }
        if (isset($stmt)) {
            $stmt->close();
        }
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
        /* CSS đơn giản – bạn có thể giữ nguyên hoặc chỉnh lại */
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
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            margin-bottom: 10px;
            color: #333;
        }
        .subtitle {
            margin-bottom: 20px;
            color: #777;
        }
        input[type="text"], input[type="password"] {
            width: 100%; 
            padding: 12px 20px; 
            margin: 8px 0; 
            border: 1px solid #ccc; 
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-login { 
            background-color: #ff5117; 
            color: #fff; 
            padding: 14px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 16px; 
            font-weight: bold; 
        }
        .btn-login:hover {
            background-color: #e04815;
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
            background-color: #ffe7df;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .remember {
            text-align: left;
            margin: 8px 0 16px;
            font-size: 14px;
            color: #555;
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
        <h1>Đăng Nhập</h1>
        <div class="subtitle">Final Project Shop</div>

        <?php if (!empty($error)) : ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Username hoặc Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <!-- Ghi nhớ đăng nhập -->
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
