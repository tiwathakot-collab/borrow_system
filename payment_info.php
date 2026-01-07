<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/db_connect.php");

// ป้องกันการเข้าถึงโดยไม่ล็อกอิน
if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$payment_id = $_GET['id'] ?? null;

if (!$payment_id) {
    echo "<script>alert('ไม่พบข้อมูลการชำระเงิน'); window.location.href='payment.php';</script>";
    exit();
}

// ✅ ดึงรายละเอียดการชำระเงิน
$sql = "
    SELECT 
        p.payment_id,
        p.amount,
        p.payment_method,
        p.created_at,
        b.borrower_name,
        b.borrow_type,
        e.equipment_name,
        e.equipment_image,
        e.price_per_unit
    FROM tb_payment p
    LEFT JOIN tb_borrow b ON p.borrow_id = b.borrow_id
    LEFT JOIN tb_equipment e ON b.equipment_id = e.equipment_id
    WHERE p.payment_id = ? AND e.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $payment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    echo "<script>alert('ไม่พบข้อมูลการชำระเงินนี้'); window.location.href='payment.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดการชำระเงิน</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f7f7f7;
            margin: 0;
            text-align: center;
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

        h1 {
            font-size: 20px;
            color: #000;
            flex: 1;
        }

        .back-btn {
            background: transparent;
            color: #2196F3;
            border: 2px solid #2196F3;
            border-radius: 25px;
            padding: 6px 15px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: 0.2s;
        }

        .back-btn:hover {
            background: #2196F3;
            color: white;
        }

        .container {
            width: 90%;
            max-width: 450px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            text-align: center;
        }

        .equipment-image {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 14px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .info-box {
            text-align: left;
            margin-top: 10px;
        }

        .info-box p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }

        .info-box strong {
            color: #000;
        }

        .amount {
            font-size: 22px;
            color: #4CAF50;
            font-weight: bold;
            margin-top: 15px;
        }

        .method {
            display: inline-block;
            background: #E3F2FD;
            color: #1976D2;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
        }

        .date {
            font-size: 13px;
            color: #777;
            margin-top: 8px;
        }

        .footer-btn {
            margin-top: 25px;
        }

        .footer-btn button {
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            cursor: pointer;
            font-size: 15px;
            transition: 0.3s;
        }

        .footer-btn button:hover {
            background: #1976D2;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <button class="back-btn" onclick="window.location.href='payment.php'">กลับ</button>
        <h1>รายละเอียดการชำระเงิน</h1>
        <div style="width:60px;"></div>
    </div>

    <div class="container">
        <img src="uploads/<?= htmlspecialchars($payment['equipment_image'] ?: 'default.png'); ?>" 
             alt="อุปกรณ์" class="equipment-image">

        <div class="info-box">
            <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($payment['equipment_name']); ?></p>
            <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($payment['borrower_name']); ?></p>
            <p><strong>ประเภทเวลา:</strong> <?= htmlspecialchars($payment['borrow_type']); ?></p>
            <p><strong>ราคาฐาน:</strong> <?= number_format($payment['price_per_unit'], 2); ?> บาท</p>
            <p class="amount">ยอดชำระทั้งหมด: <?= number_format($payment['amount'], 2); ?> บาท</p>
            <p class="method"><?= htmlspecialchars($payment['payment_method']); ?></p>
            <p class="date">วันที่ชำระ: <?= htmlspecialchars($payment['created_at']); ?></p>
        </div>

        <div class="footer-btn">
            <button onclick="window.location.href='payment.php'">กลับไปหน้าการชำระเงิน</button>
        </div>
    </div>
</body>
</html>
