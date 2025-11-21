<?php
session_start();
include 'connection.php';

$error = '';
$success = '';
$username = '';
$email = '';
$phone = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ tất cả các trường.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username hoặc Email đã được sử dụng.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $username, $email, $phone, $hashed_password);

            if ($insert_stmt->execute()) {
                //  THÊM PHẦN NÀY: Chuyển hướng về trang đăng nhập sau khi đăng ký thành công
                echo "<script>
                        alert('Đăng ký thành công! Hãy đăng nhập để tiếp tục.');
                        window.location.href = 'login.php';
                      </script>";
                exit;
            } else {
                $error = "Đã xảy ra lỗi khi đăng ký: " . $insert_stmt->error;
            }
            $insert_stmt->close();
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
    <title>Trang Đăng Ký</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #d9d9d9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .register-container {
            background-color: #f2f2f2;
            padding: 30px 40px;
            border-radius: 5px;
            width: 380px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 24px);
            padding: 12px;
            margin-bottom: 15px;
            display: block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #e6e6e6;
            font-size: 16px;
            color: #555;
        }
        input::placeholder {
            color: #888;
        }
        button {
            background-color: #b3b3b3;
            color: white;
            padding: 14px 20px;
            margin-top: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        button:hover {
            background-color: #a0a0a0;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .error {
            background-color: #fdd;
            color: #a00;
            border: 1px solid #a00;
        }
        .success {
            background-color: #dfd;
            color: #0a0;
            border: 1px solid #0a0;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Đăng Ký Tài Khoản Mới</h2>

        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post" autocomplete="off">
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            <input type="text" name="phone" placeholder="Số điện thoại" value="<?php echo htmlspecialchars($phone); ?>">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="password" name="confirm_password" placeholder="Xác nhận lại mật khẩu" required>
            <button type="submit">ĐĂNG KÝ</button>
        </form>

        <div class="login-link">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
        </div>
    </div>
</body>
</html>
