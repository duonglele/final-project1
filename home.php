<?php
include 'connection.php';

// ======= Thêm sản phẩm =======
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $code = $_POST['code'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Xử lý ảnh upload
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $upload_dir = "uploads/" . basename($image_name);

    // Tạo thư mục uploads nếu chưa có
    if (!is_dir('uploads')) mkdir('uploads', 0777, true);

    if (move_uploaded_file($image_tmp, $upload_dir)) {
        $sql = "INSERT INTO products (name, code, price, quantity, image)
                VALUES ('$name', '$code', '$price', '$quantity', '$upload_dir')";
        $conn->query($sql);
    }

    header("Location: home.php");
    exit;
}

// ======= Xóa sản phẩm =======
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $img = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc()['image'];
    if ($img && file_exists($img)) unlink($img);
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: home.php");
    exit;
}

// ======= Sửa sản phẩm =======
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $code = $_POST['code'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    if (!empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $upload_dir = "uploads/" . basename($image_name);
        move_uploaded_file($image_tmp, $upload_dir);
        $conn->query("UPDATE products 
                      SET name='$name', code='$code', price='$price', quantity='$quantity', image='$upload_dir' 
                      WHERE id=$id");
    } else {
        $conn->query("UPDATE products 
                      SET name='$name', code='$code', price='$price', quantity='$quantity' 
                      WHERE id=$id");
    }

    header("Location: home.php");
    exit;
}

// ======= Lấy danh sách sản phẩm =======
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý sản phẩm</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e6e6e6;
        display: flex;
        justify-content: center;
        padding: 40px 0;
    }
    .container {
        width: 95%;
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    form {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
    }
    input[type="text"], input[type="number"], input[type="file"] {
        flex: 1;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background: #f0f0f0;
    }
    .btn {
        background-color: #999;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn:hover { background-color: #777; }
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
        background-color: #999;
        color: white;
    }
    img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
    }
    .actions a {
        color: #0066cc;
        text-decoration: none;
        margin: 0 5px;
        font-weight: bold;
    }
    .actions a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="container">
    <h2>QUẢN LÝ SẢN PHẨM</h2>

    <!-- Form thêm sản phẩm -->
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Tên sản phẩm" required>
        <input type="text" name="code" placeholder="Mã sản phẩm" required>
        <input type="number" name="price" placeholder="Giá" required>
        <input type="number" name="quantity" placeholder="Số lượng" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" name="add_product" class="btn">+ Thêm</button>
    </form>

    <!-- Bảng sản phẩm -->
    <table>
        <tr>
            <th>ID</th>
            <th>Ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Mã sản phẩm</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Hành động</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?php if ($row['image']) { ?><img src="<?= $row['image'] ?>"><?php } else { echo "Không có ảnh"; } ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['code']) ?></td>
            <td><?= number_format($row['price'], 0, ',', '.') ?>₫</td>
            <td><?= $row['quantity'] ?></td>
            <td class="actions">
                <a href="?edit=<?= $row['id'] ?>">Sửa</a> |
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <?php if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
    ?>
    <h3 style="margin-top: 30px;">Sửa sản phẩm</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <input type="text" name="name" value="<?= $product['name'] ?>" required>
        <input type="text" name="code" value="<?= $product['code'] ?>" required>
        <input type="number" name="price" value="<?= $product['price'] ?>" required>
        <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required>
        <input type="file" name="image" accept="image/*">
        <button type="submit" name="edit_product" class="btn">Cập nhật</button>
    </form>
    <?php } ?>
</div>

</body>
</html>
