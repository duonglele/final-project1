<?php
// Bắt đầu session và kiểm tra đăng nhập
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'connection.php';

$message = '';

// ======= Thêm sản phẩm =======
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];

    // 1. Kiểm tra mã sản phẩm đã tồn tại (Dùng Prepared Statement)
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE code = ? LIMIT 1");
    $check_stmt->bind_param("s", $code);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Sản phẩm với mã '{$code}' đã tồn tại trong hệ thống.";
    } else {
        // 2. Xử lý ảnh upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $upload_dir = "uploads/" . basename($image_name);

            // Tạo thư mục uploads nếu chưa có
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);

            if (move_uploaded_file($image_tmp, $upload_dir)) {
                // 3. Insert vào CSDL (Dùng Prepared Statement)
                $sql = "INSERT INTO products (name, code, price, quantity, category_id, image)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                // Loại dữ liệu: s(string), s(string), d(double/decimal), i(integer), i(integer), s(string)
                $stmt->bind_param("ssdiss", $name, $code, $price, $quantity, $category_id, $upload_dir);
                
                if ($stmt->execute()) {
                    $message = "Thêm sản phẩm thành công!";
                } else {
                     $message = "Lỗi khi thêm sản phẩm: " . $stmt->error;
                }
                $stmt->close();
            } else {
                 $message = "Lỗi khi upload ảnh.";
            }
        } else {
            $message = "Vui lòng chọn ảnh cho sản phẩm.";
        }
    }
    $check_stmt->close();
}

// ======= Xóa sản phẩm (Dùng Prepared Statement) =======
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Xóa ảnh cũ trên server
    $img_stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $img_stmt->bind_param("i", $id);
    $img_stmt->execute();
    $result = $img_stmt->get_result();
    $img = $result->fetch_assoc()['image'];
    if ($img && file_exists($img)) unlink($img);
    $img_stmt->close();
    
    // Xóa sản phẩm khỏi CSDL
    $delete_stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    header("Location: home.php");
    exit;
}

// ======= Sửa sản phẩm (Dùng Prepared Statement) =======
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];

    if (!empty($_FILES['image']['name'])) {
        // Có ảnh mới -> Xóa ảnh cũ và thêm ảnh mới
        
        // 1. Xóa ảnh cũ
        $img_stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
        $img_stmt->bind_param("i", $id);
        $img_stmt->execute();
        $old_img = $img_stmt->get_result()->fetch_assoc()['image'];
        if ($old_img && file_exists($old_img)) unlink($old_img);
        $img_stmt->close();
        
        // 2. Upload ảnh mới
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $upload_dir = "uploads/" . basename($image_name);
        move_uploaded_file($image_tmp, $upload_dir);
        
        // 3. Update CSDL kèm ảnh
        $sql = "UPDATE products SET name=?, code=?, price=?, quantity=?, category_id=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdissi", $name, $code, $price, $quantity, $category_id, $upload_dir, $id);
        $stmt->execute();
        $stmt->close();
        
    } else {
        // Không có ảnh mới -> Chỉ update thông tin
        $sql = "UPDATE products SET name=?, code=?, price=?, quantity=?, category_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $name, $code, $price, $quantity, $category_id, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: home.php");
    exit;
}

// Lấy danh sách Categories
$categories_result = $conn->query("SELECT id, name FROM categories");

// Lấy danh sách sản phẩm (JOIN với categories để hiển thị tên danh mục)
$sql_select = "SELECT p.*, c.name as category_name 
               FROM products p 
               JOIN categories c ON p.category_id = c.id
               ORDER BY p.id DESC"; // Sắp xếp theo ID mới nhất
$result = $conn->query($sql_select);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý sản phẩm</title>
<style>
    /* CSS giữ nguyên, chỉ sửa màu để khớp với giao diện login */
    body {
        font-family: Arial, sans-serif;
        background-color: #fce4d4; /* Tone nền nhạt của ảnh du lịch */
        display: flex;
        justify-content: center;
        padding: 40px 0;
    }
    .container {
        width: 95%;
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #ff5117;
    }
    form {
        display: flex;
        align-items: center;
        flex-wrap: wrap; /* Cho phép xuống dòng nếu cần */
        gap: 10px;
        background: #fff3ec; /* Nền form nhạt hơn */
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #ff5117;
    }
    input[type="text"], input[type="number"], input[type="file"], select {
        flex: 1;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background: #f0f0f0;
        min-width: 150px; /* Giữ độ rộng tối thiểu */
    }
    .btn {
        background-color: #ff5117;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn:hover { background-color: #cc4113; }
    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }
    th, td {
        padding: 10px;
        border-bottom: 1px solid #ccc;
    }
    th {
        background-color: #ff5117;
        color: white;
    }
    img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
    }
    .actions a {
        color: #ff5117;
        text-decoration: none;
        margin: 0 5px;
        font-weight: bold;
    }
    .actions a:hover { text-decoration: underline; }
    .message {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        background-color: #dfd;
        color: #0a0;
        border: 1px solid #0a0;
        text-align: center;
    }
</style>
</head>
<body>

<div class="container">
    <h2>QUẢN LÝ SẢN PHẨM</h2>
    
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Tên sản phẩm" required>
        <input type="text" name="code" placeholder="Mã sản phẩm" required>
        
        <select name="category_id" required>
            <option value="" disabled selected>Chọn Danh mục</option>
            <?php $categories_result->data_seek(0); // Reset con trỏ ?>
            <?php while ($cat = $categories_result->fetch_assoc()) { ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php } ?>
        </select>
        
        <input type="number" name="price" placeholder="Giá" required>
        <input type="number" name="quantity" placeholder="Số lượng" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" name="add_product" class="btn">+ Thêm</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Mã sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Hành động</th>
        </tr>
        <?php if ($result->num_rows > 0) { ?>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?php if ($row['image'] && file_exists($row['image'])) { ?><img src="<?= $row['image'] ?>"><?php } else { echo "Không có ảnh"; } ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['code']) ?></td>
            <td><?= htmlspecialchars($row['category_name'] ?? 'Chưa xác định') ?></td>
            <td><?= number_format($row['price'], 0, ',', '.') ?>₫</td>
            <td><?= $row['quantity'] ?></td>
            <td class="actions">
                <a href="?edit=<?= $row['id'] ?>">Sửa</a> |
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
            </td>
        </tr>
        <?php } ?>
        <?php } else { ?>
        <tr><td colspan="8">Chưa có sản phẩm nào được thêm vào.</td></tr>
        <?php } ?>
    </table>

    <?php 
    // Logic form sửa sản phẩm
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $edit_stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
        $edit_stmt->bind_param("i", $id);
        $edit_stmt->execute();
        $product = $edit_stmt->get_result()->fetch_assoc();
        $edit_stmt->close();
        if ($product) {
    ?>
    <h3 style="margin-top: 30px;">Sửa sản phẩm</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        <input type="text" name="code" value="<?= htmlspecialchars($product['code']) ?>" required>
        
        <select name="category_id" required>
            <?php $categories_result->data_seek(0); // Reset con trỏ ?>
            <?php while ($cat = $categories_result->fetch_assoc()) { ?>
                <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php } ?>
        </select>
        
        <input type="number" name="price" value="<?= $product['price'] ?>" required>
        <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required>
        <input type="file" name="image" accept="image/*">
        <button type="submit" name="edit_product" class="btn">Cập nhật</button>
    </form>
    <?php 
        } // end if ($product)
    } // end if (isset($_GET['edit']))
    ?>
    
<div style="text-align: right; margin-top: 30px;">
    <a href="logout.php"
       style="background-color: #cc3333; 
              color: white; 
              padding: 12px 24px; 
              border-radius: 6px; 
              text-decoration: none; 
              font-weight: bold;
              transition: 0.3s;">
       Đăng xuất
    </a>
</div>

</div>
</body>
</html>
