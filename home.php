<?php
include 'connection.php';

// Lấy danh sách categories cho dropdown
$categories = [];
$catRes = $conn->query("SELECT * FROM categories ORDER BY name");
while ($row = $catRes->fetch_assoc()) {
    $categories[] = $row;
}

// ======= Thêm sản phẩm =======
if (isset($_POST['add_product'])) {
    $name     = $_POST['name'];
    $code     = $_POST['code'];
    $price    = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    // Xử lý ảnh upload
    $image_name = $_FILES['image']['name'];
    $image_tmp  = $_FILES['image']['tmp_name'];
    $upload_dir = "uploads/" . basename($image_name);

    // Tạo thư mục uploads nếu chưa có
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (!empty($image_name) && move_uploaded_file($image_tmp, $upload_dir)) {

        // Insert vào bảng products
        if ($category_id === null) {
            $sql = "INSERT INTO products (name, code, price, quantity, category_id, image)
                    VALUES ('$name', '$code', '$price', '$quantity', NULL, '$upload_dir')";
        } else {
            $sql = "INSERT INTO products (name, code, price, quantity, category_id, image)
                    VALUES ('$name', '$code', '$price', '$quantity', $category_id, '$upload_dir')";
        }

        if ($conn->query($sql)) {
            // Lấy id sản phẩm vừa tạo
            $product_id = $conn->insert_id;

            // Lưu thêm vào bảng product_images (ảnh chi tiết)
            $conn->query("INSERT INTO product_images (product_id, url) 
                          VALUES ($product_id, '$upload_dir')");
        }
    }

    header("Location: home.php");
    exit;
}

// ======= Xóa sản phẩm =======
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Lấy ảnh chính trong products
    $imgRow = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
    if ($imgRow && $imgRow['image'] && file_exists($imgRow['image'])) {
        unlink($imgRow['image']);
    }

    // Lấy tất cả ảnh liên quan trong product_images để xóa file
    $imgsRes = $conn->query("SELECT url FROM product_images WHERE product_id=$id");
    while ($row = $imgsRes->fetch_assoc()) {
        if ($row['url'] && file_exists($row['url'])) {
            unlink($row['url']);
        }
    }
    // Các bản ghi product_images sẽ tự xóa theo ON DELETE CASCADE khi xóa product

    // Xóa sản phẩm (cart_items, order_items cũng sẽ xử lý theo FK nếu có)
    $conn->query("DELETE FROM products WHERE id=$id");

    header("Location: home.php");
    exit;
}

// ======= Sửa sản phẩm =======
if (isset($_POST['edit_product'])) {
    $id       = (int)$_POST['id'];
    $name     = $_POST['name'];
    $code     = $_POST['code'];
    $price    = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    // Có upload ảnh mới
    if (!empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $image_tmp  = $_FILES['image']['tmp_name'];
        $upload_dir = "uploads/" . basename($image_name);

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($image_tmp, $upload_dir)) {
            // (Tuỳ chọn) Xóa ảnh cũ trong products
            $oldImgRow = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
            if ($oldImgRow && $oldImgRow['image'] && file_exists($oldImgRow['image'])) {
                unlink($oldImgRow['image']);
            }

            // Update products
            if ($category_id === null) {
                $sql = "UPDATE products
                        SET name='$name', code='$code', price='$price', quantity='$quantity',
                            category_id=NULL, image='$upload_dir'
                        WHERE id=$id";
            } else {
                $sql = "UPDATE products
                        SET name='$name', code='$code', price='$price', quantity='$quantity',
                            category_id=$category_id, image='$upload_dir'
                        WHERE id=$id";
            }
            $conn->query($sql);

            // Lưu thêm 1 ảnh mới vào product_images
            $conn->query("INSERT INTO product_images (product_id, url)
                          VALUES ($id, '$upload_dir')");
        }
    } else {
        // Không đổi ảnh, chỉ update thông tin
        if ($category_id === null) {
            $sql = "UPDATE products
                    SET name='$name', code='$code', price='$price', quantity='$quantity',
                        category_id=NULL
                    WHERE id=$id";
        } else {
            $sql = "UPDATE products
                    SET name='$name', code='$code', price='$price', quantity='$quantity',
                        category_id=$category_id
                    WHERE id=$id";
        }
        $conn->query($sql);
    }

    header("Location: home.php");
    exit;
}

// ======= Lấy danh sách sản phẩm (JOIN category) =======
$result = $conn->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title class = "title">Quản lý sản phẩm</title>
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
        color: #ff5117;
    }
    form {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    input[type="text"], input[type="number"], input[type="file"], select {
        flex: 1;
        min-width: 150px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background: #f0f0f0;
    }
    .btn {
        background-color: #58bc39;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn:hover { 
        /* background-color: #777;  */
        opacity: .8;
    }
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

    .head-product {
        background-color: 
    }
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

        <select name="category_id">
            <option value="">-- Chọn danh mục --</option>
            <?php foreach ($categories as $cat) { ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php } ?>
        </select>

        <input type="file" name="image" accept="image/*" required>
        <button type="submit" name="add_product" class="btn">+ Thêm</button>
    </form>

    <!-- Bảng sản phẩm -->
    <table>
        <tr class = "head-product">
            <th>ID</th>
            <th>Ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Mã sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Hành động</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td>
                <?php if ($row['image']) { ?>
                    <img src="<?= $row['image'] ?>">
                <?php } else { echo "Không có ảnh"; } ?>
            </td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['code']) ?></td>
            <td><?= $row['category_name'] ? htmlspecialchars($row['category_name']) : 'Chưa gán' ?></td>
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
        $id = (int)$_GET['edit'];
        $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
    ?>
    <h3 style="margin-top: 30px;">Sửa sản phẩm</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        <input type="text" name="code" value="<?= htmlspecialchars($product['code']) ?>" required>
        <input type="number" name="price" value="<?= $product['price'] ?>" required>
        <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required>

        <select name="category_id">
            <option value="">-- Chọn danh mục --</option>
            <?php foreach ($categories as $cat) { ?>
                <option value="<?= $cat['id'] ?>"
                    <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php } ?>
        </select>

        <input type="file" name="image" accept="image/*">
        <button type="submit" name="edit_product" class="btn">Cập nhật</button>
    </form>
    <?php } ?>
</div>

</body>
</html>
