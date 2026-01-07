<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/db_connect.php");

// ป้องกัน cache หลัง logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// ตรวจสอบล็อกอิน
if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$fullname = $_SESSION["fullname"];

// ดึงอุปกรณ์ใกล้หมดเวลา
$sql = "
SELECT 
    b.borrow_id,
    b.borrower_name,
    b.borrow_type,
    b.contact_info,
    b.note,
    b.borrow_end,
    e.equipment_name,
    e.equipment_image,
    e.status
FROM tb_borrow b
JOIN tb_equipment e ON b.equipment_id = e.equipment_id
WHERE e.user_id = ? 
  AND b.status = 'กำลังยืม'
  AND (
      (b.borrow_type='รายวัน' AND TIMESTAMPDIFF(HOUR, NOW(), b.borrow_end) BETWEEN 0 AND 24)
   OR (b.borrow_type='รายชั่วโมง' AND TIMESTAMPDIFF(HOUR, NOW(), b.borrow_end) BETWEEN 0 AND 6)
)
ORDER BY b.borrow_end ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>อุปกรณ์ใกล้หมดเวลา</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            margin: 0;
            background: #f9f9f9;
            padding-bottom: 80px;
        }

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
            font-size: 18px;
            color: #333;
            text-align: center;
            flex: 1;
        }

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

        .container {
            width: 95%;
            max-width: 900px;
            margin: 0 auto;
        }

        .borrow-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
            position: relative;
        }

        .borrow-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }

        .borrow-info {
            flex: 1;
        }

        .borrow-info p {
            margin: 6px 0;
            color: #333;
        }

        .borrow-info p strong {
            color: #000;
        }

        .btn-finish {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-finish:hover {
            background: #388E3C;
        }

        .no-data {
            text-align: center;
            color: #888;
            font-size: 18px;
            margin-top: 50px;
        }

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

    <div class="navbar">
        <button class="menu-btn" id="menuBtn">
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="account">
        </button>
        <h1>อุปกรณ์ใกล้หมดเวลา</h1>
        <div style="width:36px;"></div>
    </div>

    <div class="sidebar" id="sidebar">
        <h3>👤 <?= htmlspecialchars($fullname) ?></h3>
        <ul>
            <li><a href="verify_pin.php?next=edit_profile.php">แก้ไขโปรไฟล์</a></li>
            <li><a href="finance.php">ข้อมูลการเงิน</a></li>
            <li><a href="index.php">เปลี่ยนบัญชี</a></li>
            <li><a href="index.php?logout=1">ออกจากระบบ</a></li>
        </ul>
    </div>

    <div class="overlay" id="overlay"></div>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()):
                $now = time();
                $end = strtotime($row['borrow_end']);
                $diff = $end - $now;

                if ($diff <= 0) {
                    $hours_left_text = "หมดเวลาแล้ว";
                } else {
                    $hours = floor($diff / 3600);
                    $minutes = floor(($diff % 3600) / 60);
                    $hours_left_text = "{$hours} ชม. {$minutes} นาที";
                }

                ?>
                <div class="borrow-card">
                    <img src="uploads/<?= htmlspecialchars($row['equipment_image'] ?: 'default.png'); ?>" alt="อุปกรณ์">
                    <div class="borrow-info">
                        <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($row['equipment_name']); ?></p>
                        <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($row['borrower_name']); ?></p>
                        <p><strong>ประเภทเวลา:</strong> <?= htmlspecialchars($row['borrow_type']); ?></p>
                        <p><strong>เวลาที่เหลือ:</strong> <?= $hours_left_text ?></p>
                        <p><strong>การติดต่อ:</strong> <?= htmlspecialchars($row['contact_info']); ?></p>
                        <p><strong>หมายเหตุ:</strong> <?= htmlspecialchars($row['note'] ?: "-"); ?></p>
                    </div>
                    <button class="btn-finish"
                        onclick="window.location.href='payment_detail.php?borrow_id=<?= $row['borrow_id']; ?>'">
                        เสร็จสิ้นการยืม
                    </button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">ยังไม่มีอุปกรณ์ที่ใกล้หมดเวลา</div>
        <?php endif; ?>
    </div>

    <div class="tabbar">
        <a href="equipment_available.php">ว่าง</a>
        <a href="equipment_borrowing.php">กำลังยืม</a>
        <a href="equipment_near_expire.php" class="active">ใกล้หมดเวลา</a>
        <a href="payment.php">การชำระเงิน</a>
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