<?php
// ✅ ป้องกัน session_start() ซ้ำ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ ป้องกันการแคชหน้า (เพื่อกันย้อนกลับหลัง logout)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("includes/db_connect.php");

// 🚫 ถ้าไม่ได้ล็อกอิน ให้เด้งกลับหน้า index
if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้');
            window.location.href = 'index.php';
          </script>";
    exit();
}

// ✅ ดึงชื่อผู้ใช้จาก session
$fullname = $_SESSION["fullname"];
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>หน้าหลัก | ระบบยืม-คืนอุปกรณ์</title>
    <style>
        body {
            margin: 0;
            font-family: 'Prompt', sans-serif;
            background: #f9f9f9;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .navbar .menu-btn {
            background: none;
            border: none;
            cursor: pointer;
        }

        .navbar .menu-btn img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        .navbar h1 {
            font-size: 20px;
            color: #333;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: -260px;
            top: 0;
            height: 100%;
            width: 260px;
            background: #fff;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            padding-top: 60px;
            z-index: 3;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar h3 {
            text-align: center;
            color: #333;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        .sidebar li {
            padding: 14px 20px;
            border-bottom: 1px solid #eee;
        }

        .sidebar li a {
            text-decoration: none;
            color: #333;
            display: block;
        }

        .sidebar li a:hover {
            color: #2196F3;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 2;
        }

        .overlay.active {
            display: block;
        }

        /* Content */
        .content {
            flex: 1;
            padding: 20px;
            text-align: center;
        }

        .content h2 {
            color: #333;
            margin-bottom: 20px;
        }

        /* Equipment list */
        .equipment-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }

        .equipment-card {
            background: #fff;
            display: flex;
            align-items: center;
            width: 95%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            padding: 10px;
            transition: 0.2s;
        }

        .equipment-card:hover {
            transform: translateY(-3px);
        }

        .equipment-image {
            width: 90px;
            height: 90px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 15px;
        }

        .equipment-info {
            text-align: left;
            flex: 1;
        }

        .equipment-info p {
            margin: 3px 0;
            color: #333;
            font-size: 14px;
        }

        .no-data {
            color: #999;
            font-size: 18px;
            padding: 50px 0;
        }

        /* Bottom Tab Bar */
        .tabbar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #fff;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 60px;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.05);
        }

        .tabbar a {
            text-decoration: none;
            color: #666;
            font-size: 14px;
            text-align: center;
            flex: 1;
        }

        .tabbar a.active {
            color: #2196F3;
            font-weight: bold;
        }

        .tabbar a:hover {
            color: #1976D2;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <button class="menu-btn" id="menuBtn">
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="account">
        </button>
        <h1>ระบบยืม-คืนอุปกรณ์</h1>
        <div style="width:36px;"></div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h3>👤 <?= htmlspecialchars($fullname); ?></h3>
        <ul>
            <li><a href="verify_pin.php?next=edit_profile.php">แก้ไขโปรไฟล์</a></li>
            <li><a href="finance.php">ข้อมูลการเงิน</a></li>
            <li><a href="index.php">เปลี่ยนบัญชี</a></li>
            <li><a href="index.php?logout=1">ออกจากระบบ</a></li>
        </ul>
    </div>

    <div class="overlay" id="overlay"></div>

    <!-- Main Content -->
    <div class="content">
        <?php
        $sql = "SELECT * FROM tb_equipment ORDER BY updated_at DESC LIMIT 10";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0): ?>
            <div class="equipment-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="equipment-card">
                        <img src="uploads/<?= htmlspecialchars($row['equipment_image'] ?: 'default.png'); ?>" class="equipment-image" alt="Equipment">
                        <div class="equipment-info">
                            <p><strong><?= htmlspecialchars($row['equipment_name']); ?></strong></p>
                            <p>💰 <?= number_format($row['price_per_unit'], 2); ?> บาท / <?= htmlspecialchars($row['time_type']); ?></p>
                            <p>📅 <?= htmlspecialchars($row['updated_at']); ?></p>
                            <p>สถานะ: <b><?= htmlspecialchars($row['status']); ?></b></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-data">ยังไม่มีอุปกรณ์ในระบบ</div>
        <?php endif; ?>
    </div>

    <!-- Bottom Tab Bar -->
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <div class="tabbar">
        <a href="equipment_available.php" class="<?= ($current_page == 'equipment_available.php') ? 'active' : ''; ?>">ว่าง</a>
        <a href="equipment_borrowing.php" class="<?= ($current_page == 'equipment_borrowing.php') ? 'active' : ''; ?>">กำลังยืม</a>
        <a href="equipment_near_expire.php" class="<?= ($current_page == 'equipment_near_expire.php') ? 'active' : ''; ?>">ใกล้หมดเวลา</a>
        <a href="payment.php" class="<?= ($current_page == 'payment.php') ? 'active' : ''; ?>">การชำระเงิน</a>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    </script>
</body>

</html>
