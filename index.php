<?php
// htdocs/index.php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/header.php";
?>

<div style="max-width: 800px; margin: 60px auto; text-align: center; padding: 20px;">
    <h1 style="font-size: 36px; color: #111; margin-bottom: 15px; font-weight: 800;">Chào mừng đến với Electronics Shop</h1>
    <p style="color: #666; margin-bottom: 50px; font-size: 18px;">Vui lòng chọn khu vực bạn muốn truy cập:</p>

    <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap;">
        
        <a href="shop.php" style="
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            width: 260px; height: 200px;
            background: #fff;
            border: 2px solid #eaecf0;
            border-radius: 20px;
            text-decoration: none;
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        " onmouseover="this.style.borderColor='#e11d48'; this.style.transform='translateY(-8px)'" 
           onmouseout="this.style.borderColor='#eaecf0'; this.style.transform='translateY(0)'">
            
            <div style="font-size: 48px; margin-bottom: 15px;">🛍️</div>
            <h3 style="margin: 0; color: #101828; font-size: 20px;">Cửa Hàng</h3>
            <p style="margin: 10px 0 0; color: #666; font-size: 14px;">Xem và mua linh kiện</p>
        </a>

        <a href="admin/dashboard.php" style="
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            width: 260px; height: 200px;
            background: #fff;
            border: 2px solid #eaecf0;
            border-radius: 20px;
            text-decoration: none;
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        " onmouseover="this.style.borderColor='#1d4ed8'; this.style.transform='translateY(-8px)'" 
           onmouseout="this.style.borderColor='#eaecf0'; this.style.transform='translateY(0)'">
            
            <div style="font-size: 48px; margin-bottom: 15px;">⚙️</div>
            <h3 style="margin: 0; color: #101828; font-size: 20px;">Quản Trị Viên</h3>
            <p style="margin: 10px 0 0; color: #666; font-size: 14px;">Quản lý kho và đơn hàng</p>
        </a>

    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>