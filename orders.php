<?php
// htdocs/admin/orders.php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/header.php";

require_admin(); // Chỉ Admin mới được vào

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $oid = (int)$_POST['order_id'];
    $st  = $_POST['status'];
    // Cập nhật
    $up = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $up->execute([$st, $oid]);
    $_SESSION['flash'] = "Cập nhật trạng thái đơn #$oid thành công.";
    header("Location: orders.php"); exit;
}

// Lấy tất cả đơn hàng (Kèm tên người mua)
$sql = "SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$orders = $pdo->query($sql)->fetchAll();
?>

<div class="container" style="margin-top:20px;">
    <h2>Admin - Quản lý Đơn Hàng</h2>
    
    <div style="margin-bottom:15px;">
        <a href="dashboard.php" class="btn">← Về Dashboard</a>
    </div>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert success"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái (Cập nhật)</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td>
                    <b><?= htmlspecialchars($o['user_name']) ?></b><br>
                    <small><?= htmlspecialchars($o['user_email']) ?></small>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                <td style="color:#e11d48; font-weight:bold;"><?= number_format($o['total'], 0, ',', '.') ?> ₫</td>
                <td>
                    <form method="post" style="display:flex; gap:5px;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status" style="padding:5px; border-radius:4px;">
                            <option value="pending"   <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                            <option value="paid"      <?= $o['status']=='paid'?'selected':'' ?>>Paid</option>
                            <option value="shipping"  <?= $o['status']=='shipping'?'selected':'' ?>>Shipping</option>
                            <option value="completed" <?= $o['status']=='completed'?'selected':'' ?>>Completed</option>
                            <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                        <button class="btn primary" style="padding:5px 10px; height:auto; font-size:12px;">Lưu</button>
                    </form>
                </td>
                <td>
                    <a href="/order_detail.php?id=<?= $o['id'] ?>" class="btn outline" style="padding:5px 10px; font-size:12px;">Xem chi tiết</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>