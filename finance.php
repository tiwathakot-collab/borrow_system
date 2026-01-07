<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/db_connect.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];

// ✅ ดึงข้อมูลบัญชีจากฐานข้อมูล
$sql = "SELECT account_name, bank_name, account_number, promptpay, qr_image 
        FROM tb_finance WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$finance = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลการเงิน</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f9f9f9;
            margin: 0;
            text-align: center;
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

        h1 {
            font-size: 20px;
            color: #000;
            flex: 1;
            margin: 0;
        }

        /* ปุ่มกลับ */
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

        /* กล่องข้อมูล */
        .container {
            margin: 40px auto;
            width: 90%;
            max-width: 400px;
            background: #fff;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 14px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        p {
            font-size: 16px;
            color: #333;
            margin: 8px 0;
        }

        /* ปุ่มแก้ไข */
        .edit-btn {
            background: #2196F3;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            margin-top: 20px;
            cursor: pointer;
            transition: 0.3s;
        }

        .edit-btn:hover {
            background: #1976D2;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <button class="back-btn" onclick="window.location.href='main.php'">กลับ</button>
        <h1>ข้อมูลการเงิน</h1>
        <div style="width:60px;"></div>
    </div>

    <!-- เนื้อหา -->
    <div class="container">
        <h2>บัญชีสำหรับรับเงิน</h2>

        <?php if (!empty($finance)): ?>
            <img src="uploads/<?= htmlspecialchars($finance['qr_image'] ?: 'default_qr.png'); ?>" alt="QR Code">
            <p><strong>ชื่อบัญชี:</strong> <?= htmlspecialchars($finance['account_name']); ?></p>
            <p><strong>ธนาคาร:</strong> <?= htmlspecialchars($finance['bank_name']); ?></p>
            <p><strong>เลขบัญชี:</strong> <?= htmlspecialchars($finance['account_number']); ?></p>
            <p><strong>พร้อมเพย์:</strong> <?= htmlspecialchars($finance['promptpay']); ?></p>
        <?php else: ?>
            <p style="color:red;">❌ ยังไม่มีข้อมูลบัญชี กรุณาเพิ่มข้อมูล</p>
        <?php endif; ?>

        <button class="edit-btn" onclick="window.location.href='verify_pin.php?next=finance_edit.php'">แก้ไขข้อมูล</button>
    </div>

</body>
</html>
