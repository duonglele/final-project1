<?php
// htdocs/order_detail.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";

require_login();

$orderId = (int)($_GET['id'] ?? 0);
$currentUser = current_user();

// 1. Lấy thông tin đơn hàng
$st = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$st->execute([$orderId]);
$order = $st->fetch();

if (!$order) exit("Đơn hàng không tồn tại.");

// 2. BẢO MẬT QUAN TRỌNG: 
// Nếu không phải Admin VÀ đơn hàng này không phải của người dùng đang đăng nhập -> Chặn
if ($currentUser['role'] !== 'admin' && (int)$order['user_id'] !== (int)$currentUser['id']) {
    echo "<div class='alert error'>Bạn không có quyền xem đơn hàng này.</div>";
    require_once __DIR__ . "/includes/footer.php";
    exit;
}

// 3. Lấy chi tiết sản phẩm trong đơn
$stItems = $pdo->prepare("
    SELECT oi.*, p.name, p.image, p.sku 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stItems->execute([$orderId]);
$items = $stItems->fetchAll();
?>

<div class="container" style="max-width: 900px; margin-top: 30px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Chi tiết đơn hàng #<?= $orderId ?></h2>
        <?php if($currentUser['role'] === 'admin'): ?>
            <a href="/admin/orders.php" class="btn">← Quay lại Quản lý</a>
        <?php else: ?>
            <a href="/order_history.php" class="btn">← Quay lại Lịch sử</a>
        <?php endif; ?>
    </div>

    <div style="background:#fff; padding:20px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px;">
        <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></p>
        <p><strong>Trạng thái:</strong> <span style="font-weight:bold; text-transform:uppercase;"><?= htmlspecialchars($order['status']) ?></span></p>
        <p><strong>Tổng tiền:</strong> <span style="color:#e11d48; font-size:18px; font-weight:bold;"><?= number_format($order['total'], 0, ',', '.') ?> ₫</span></p>
    </div>

    <h3>Danh sách sản phẩm</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Giá lúc mua</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td style="display:flex; gap:10px; align-items:center;">
                    <?php if($item['image']): ?>
                        <img src="/uploads/<?= htmlspecialchars($item['image']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                    <?php endif; ?>
                    <div>
                        <b><?= htmlspecialchars($item['name']) ?></b><br>
                        <small>SKU: <?= htmlspecialchars($item['sku']) ?></small>
                    </div>
                </td>
                <td><?= number_format($item['price'], 0, ',', '.') ?> ₫</td>
                <td style="text-align:center;"><?= (int)$item['qty'] ?></td>
                <td style="font-weight:bold;"><?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?> ₫</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>