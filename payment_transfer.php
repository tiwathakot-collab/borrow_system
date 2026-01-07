<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db_connect.php");

if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบ'); window.location='index.php';</script>";
    exit();
}

if (!isset($_GET['borrow_id'])) {
    echo "<script>alert('ไม่พบข้อมูลการยืม'); window.location='payment.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$borrow_id = intval($_GET['borrow_id']);

// ✅ ดึงข้อมูลการยืม + ข้อมูลการเงิน
$sql = "SELECT b.*, e.equipment_name, e.price_per_day, e.price_per_hour, 
               f.account_name, f.bank_name, f.account_number, f.promptpay, f.qr_image 
        FROM tb_borrow b
        JOIN tb_equipment e ON b.equipment_id = e.equipment_id
        JOIN tb_finance f ON e.user_id = f.user_id
        WHERE b.borrow_id=? AND e.user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $borrow_id, $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('ไม่พบข้อมูล'); window.location='payment.php';</script>";
    exit();
}

// ✅ คำนวณราคา
$price = ($data['borrow_type'] === 'รายวัน') ? $data['price_per_day'] : $data['price_per_hour'];

// ✅ ยืนยันการชำระเงิน
if (isset($_POST['confirm'])) {
    $conn->query("UPDATE tb_borrow SET status='ชำระเงินแล้ว' WHERE borrow_id=$borrow_id");
    $conn->query("UPDATE tb_equipment SET status='ว่าง', updated_at=NOW() WHERE equipment_id=" . intval($data['equipment_id']));
    echo "<script>alert('✅ ชำระเงินสำเร็จ!'); window.location='equipment_borrowing.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ชำระเงินโดยการโอน</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f8f8f8;
            margin: 0;
            padding-bottom: 80px;
        }

        .container {
            max-width: 480px;
            background: #fff;
            margin: 80px auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 15px;
        }

        img {
            width: 220px;
            height: 220px;
            border-radius: 16px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .bank-info {
            text-align: left;
            margin-top: 10px;
            padding: 10px 15px;
            background: #f9f9f9;
            border-radius: 12px;
            border: 1px solid #eee;
        }

        .bank-info p {
            margin: 6px 0;
            font-size: 15px;
            color: #333;
        }

        .equipment-info {
            text-align: left;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .equipment-info p {
            margin: 5px 0;
            font-size: 15px;
        }

        .btn-confirm {
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background: #4CAF50;
            border: none;
            border-radius: 25px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-confirm:hover {
            background: #388E3C;
        }

        .note {
            color: #777;
            font-size: 13px;
            margin-top: 10px;
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
    <button class="back-btn" onclick="window.location.href='payment_detail.php?borrow_id=<?= $borrow_id ?>'">กลับ</button>

    <div class="container">
        <h2>ชำระเงินโดยการโอน</h2>
        <img src="uploads/<?= htmlspecialchars($data['qr_image'] ?: 'default_qr.png'); ?>" alt="QR Code">

        <div class="bank-info">
            <p><strong>ธนาคาร:</strong> <?= htmlspecialchars($data['bank_name'] ?: '-'); ?></p>
            <p><strong>ชื่อบัญชี:</strong> <?= htmlspecialchars($data['account_name'] ?: '-'); ?></p>
            <p><strong>เลขบัญชี:</strong> <?= htmlspecialchars($data['account_number'] ?: '-'); ?></p>
            <p><strong>พร้อมเพย์:</strong> <?= htmlspecialchars($data['promptpay'] ?: '-'); ?></p>
        </div>

        <div class="equipment-info">
            <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($data['equipment_name']); ?></p>
            <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($data['borrower_name']); ?></p>
            <p><strong>ยอดรวมที่ต้องชำระ:</strong> <?= number_format($price, 2); ?> บาท</p>
        </div>

        <form method="POST">
            <button type="submit" name="confirm" class="btn-confirm">ยืนยันการชำระเงิน</button>
        </form>

        <div class="note">โปรดตรวจสอบยอดโอนและข้อมูลบัญชีก่อนกดยืนยัน</div>
    </div>
</body>
</html>
