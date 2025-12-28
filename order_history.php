<?php
// htdocs/order_history.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";

require_login(); // Bắt buộc đăng nhập

$userId = current_user()['id'];

// Lấy danh sách đơn hàng của user này
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<div class="container" style="max-width: 900px; margin-top: 30px;">
    <h2>Lịch sử đơn hàng của bạn</h2>

    <?php if (count($orders) === 0): ?>
        <div class="alert">Bạn chưa có đơn hàng nào. <a href="shop.php">Mua sắm ngay</a></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Mã Đơn (#)</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int)$o['id'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td style="color:#e11d48; font-weight:bold;"><?= number_format($o['total'], 0, ',', '.') ?> ₫</td>
                    <td>
                        <?php 
                            $st = $o['status'];
                            $color = 'black';
                            if($st == 'paid') $color = 'green';
                            if($st == 'pending') $color = 'orange';
                            if($st == 'cancelled') $color = 'red';
                        ?>
                        <span style="color:<?= $color ?>; font-weight:bold; text-transform:uppercase;"><?= htmlspecialchars($st) ?></span>
                    </td>
                    <td>
                        <a href="order_detail.php?id=<?= $o['id'] ?>" class="btn" style="padding: 5px 10px; font-size: 13px;">Xem</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>