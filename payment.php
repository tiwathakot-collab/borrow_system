<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db_connect.php");

// ป้องกัน cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$fullname = $_SESSION["fullname"];

// ✅ ยอดรวมทั้งหมด + แยกเงินสด/โอน
$total_sql = "
    SELECT 
        SUM(amount) AS total_amount,
        SUM(CASE WHEN payment_method='สด' THEN amount ELSE 0 END) AS total_cash,
        SUM(CASE WHEN payment_method='โอน' THEN amount ELSE 0 END) AS total_transfer
    FROM tb_payment p
    JOIN tb_borrow b ON p.borrow_id = b.borrow_id
    JOIN tb_equipment e ON b.equipment_id = e.equipment_id
    WHERE e.user_id = ?
";

$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_amount = $total_result['total_amount'] ?? 0;
$total_cash = $total_result['total_cash'] ?? 0;
$total_transfer = $total_result['total_transfer'] ?? 0;

// ✅ ดึงประวัติการชำระเงิน
$payment_sql = "
    SELECT 
        p.payment_id,
        p.borrow_id,
        p.amount,
        p.payment_method,
        p.created_at,
        b.borrower_name,
        e.equipment_name,
        e.equipment_image
    FROM tb_payment p
    JOIN tb_borrow b ON p.borrow_id = b.borrow_id
    JOIN tb_equipment e ON b.equipment_id = e.equipment_id
    WHERE e.user_id = ?
    ORDER BY p.created_at DESC
";
$payment_stmt = $conn->prepare($payment_sql);
$payment_stmt->bind_param("i", $user_id);
$payment_stmt->execute();
$payments = $payment_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>การชำระเงิน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            margin: 0;
            background: #f7f7f7;
            padding-bottom: 80px;
        }

        .navbar {
            background: #fff;
            display: flex;
            justify-content: center;
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
        }

        .container {
            width: 95%;
            max-width: 900px;
            margin: 20px auto;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #fff;
            color: #2196F3;
            border: 2px solid #2196F3;
            border-radius: 25px;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: .2s;
            z-index: 10;
        }

        .back-btn:hover {
            background: #2196F3;
            color: #fff;
        }

        .total-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .total-box h3 {
            margin: 0;
            color: #333;
        }

        .total-box p {
            font-size: 18px;
            margin: 5px 0;
        }

        .payment-history {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .payment-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .payment-card:hover {
            transform: scale(1.01);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .payment-card img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .payment-info {
            flex: 1;
        }

        .payment-info p {
            margin: 3px 0;
            color: #333;
            font-size: 14px;
        }

        .payment-info p.method {
            font-weight: bold;
            color: #2196F3;
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

    <button class="back-btn" onclick="window.location.href='equipment_borrowing.php'">กลับ</button>

    <div class="navbar">
        <h1>การชำระเงิน</h1>
    </div>

    <div class="container">
        <!-- ยอดรวมทั้งหมด -->
        <div class="total-box">
            <h3>ยอดรวมการชำระเงิน</h3>
            <p>รวมทั้งหมด: <?= number_format($total_amount, 2); ?> บาท</p>
            <p>เงินสด: <?= number_format($total_cash, 2); ?> บาท | โอน: <?= number_format($total_transfer, 2); ?> บาท
            </p>
        </div>

        <!-- ประวัติการชำระเงิน -->
        <div class="payment-history">
            <?php if ($payments->num_rows > 0): ?>
                <?php while ($row = $payments->fetch_assoc()): ?>
                    <div class="payment-card" onclick="window.location.href='payment_info.php?id=<?= $row['payment_id']; ?>'">
                        <img src="uploads/<?= htmlspecialchars($row['equipment_image'] ?: 'default.png'); ?>" alt="อุปกรณ์">
                        <div class="payment-info">
                            <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($row['equipment_name']); ?></p>
                            <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($row['borrower_name']); ?></p>
                            <p class="method">วิธีชำระ: <?= htmlspecialchars($row['payment_method']); ?></p>
                            <p><strong>ราคารวม:</strong> <?= number_format($row['amount'], 2); ?> บาท</p>
                            <p style="font-size:12px;color:#666;">วันที่: <?= htmlspecialchars($row['created_at']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center;color:#888;">ยังไม่มีประวัติการชำระเงิน</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab Bar -->
    <div class="tabbar">
        <a href="equipment_available.php">ว่าง</a>
        <a href="equipment_borrowing.php">กำลังยืม</a>
        <a href="equipment_near_expire.php">ใกล้หมดเวลา</a>
        <a href="payment.php" class="active">การชำระเงิน</a>
    </div>

</body>

</html>