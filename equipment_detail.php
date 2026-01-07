<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/db_connect.php");

// ป้องกันการแคช
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้');
            window.location.href = 'index.php';
          </script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$fullname = $_SESSION["fullname"];

// รับ ID อุปกรณ์จาก URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('ไม่พบข้อมูลอุปกรณ์');
            window.location.href = 'equipment_available.php';
          </script>";
    exit();
}

$equipment_id = intval($_GET['id']);

// ดึงข้อมูลอุปกรณ์จากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM tb_equipment WHERE equipment_id = ? AND user_id = ?");
$stmt->bind_param("ii", $equipment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
            alert('ไม่พบอุปกรณ์นี้ในระบบของคุณ');
            window.location.href = 'equipment_available.php';
          </script>";
    exit();
}

$equipment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายละเอียดอุปกรณ์</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding-bottom: 60px;
        }

        .container {
            max-width: 500px;
            background: #fff;
            margin: 80px auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .image-box {
            text-align: center;
            margin-bottom: 20px;
        }

        .image-box img {
            width: 220px;
            height: 220px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 15px;
        }

        .info {
            font-size: 15px;
            line-height: 1.7;
            color: #333;
        }

        .info strong {
            color: #000;
        }

        .price {
            margin-top: 10px;
            background: #f1f8ff;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
        }

        .price p {
            margin: 5px 0;
            font-size: 16px;
            color: #2196F3;
        }

        .status {
            text-align: center;
            margin-top: 10px;
            color: #666;
        }

        .btn-edit {
            display: block;
            width: 100%;
            background: #2196F3;
            color: #fff;
            text-align: center;
            padding: 12px;
            border-radius: 25px;
            font-size: 16px;
            margin-top: 25px;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-edit:hover {
            background: #1976D2;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: transparent;
            color: #2196F3;
            border: 2px solid #2196F3;
            border-radius: 25px;
            padding: 6px 15px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: 0.2s;
            z-index: 10;
        }

        .back-btn:hover {
            background: #2196F3;
            color: white;
        }
    </style>
</head>

<body>
    <button class="back-btn" onclick="window.location.href='equipment_available.php'">กลับ</button>

    <div class="container">
        <div class="image-box">
            <img src="uploads/<?= htmlspecialchars($equipment['equipment_image'] ?: 'default.png'); ?>"
                alt="Equipment Image">
        </div>

        <h2><?= htmlspecialchars($equipment['equipment_name']); ?></h2>

        <div class="info">
            <p><strong>รายละเอียด:</strong> <?= nl2br(htmlspecialchars($equipment['description'] ?: 'ไม่มีข้อมูล')); ?>
            </p>
            <div class="price">
                <p>💰 <?= number_format($equipment['price_per_day'], 2); ?> บาท / วัน</p>
                <p>🕓 <?= number_format($equipment['price_per_hour'], 2); ?> บาท / ชั่วโมง</p>
            </div>
            <p class="status">📅 อัปเดตล่าสุด: <?= htmlspecialchars($equipment['updated_at']); ?></p>
            <p class="status">🔖 สถานะ: <?= htmlspecialchars($equipment['status']); ?></p>
        </div>


        <div style="display:flex; justify-content:space-between; gap:10px; margin-top:30px;">
            <a href="verify_pin.php?next=equipment_edit.php?id=<?= $equipment_id; ?>" class="btn-edit"
                style="flex:1; background:#FF9800;">แก้ไขข้อมูล</a>
            <a href="equipment_borrow.php?id=<?= $equipment_id; ?>" class="btn-edit"
                style="flex:1; background:#4CAF50;">ยืม</a>
        </div>

    </div>
</body>

</html>