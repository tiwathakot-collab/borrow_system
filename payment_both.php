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

// ✅ ดึงข้อมูลการยืม + การเงิน
$sql = "SELECT b.*, e.equipment_name, e.price_per_day, e.price_per_hour, e.equipment_id, f.account_name, f.bank_name, f.promptpay, f.qr_image 
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

$price = ($data['borrow_type'] === 'รายวัน') ? $data['price_per_day'] : $data['price_per_hour'];
$diff = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transfer = floatval($_POST['transfer']);
    $cash = floatval($_POST['cash']);
    $total = $transfer + $cash;
    $diff = $total - $price;

    if ($total < $price) {
        $message = "⚠️ ชำระไม่ครบ ขาด " . number_format(abs($diff), 2) . " บาท";
    } elseif ($total > $price) {
        $message = "💵 จ่ายเกิน " . number_format($diff, 2) . " บาท";
    } else {
        $message = "✅ ชำระครบพอดี";
    }

    echo "<script>
        if (confirm('ยืนยันการชำระเงินทั้งหมด $total บาท ?')) {
            alert('✅ ชำระเงินสำเร็จ!');
            window.location='equipment_borrowing.php';
        }
    </script>";

    $conn->query("UPDATE tb_borrow SET status='ชำระเงินแล้ว' WHERE borrow_id=$borrow_id");
    $conn->query("UPDATE tb_equipment SET status='ว่าง', updated_at=NOW() WHERE equipment_id=" . intval($data['equipment_id']));
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ชำระเงินแบบโอน + เงินสด</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f9f9f9;
            margin: 0;
        }

        .container {
            max-width: 480px;
            background: #fff;
            margin: 80px auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 { text-align: center; color: #333; }
        img {
            width: 200px; height: 200px;
            display: block; margin: 0 auto;
            border-radius: 12px; object-fit: cover;
        }

        p { color: #333; margin: 6px 0; font-size: 15px; }
        input {
            width: 100%; padding: 10px;
            border: 1px solid #ccc; border-radius: 8px;
            margin-top: 8px; font-size: 16px;
        }
        button {
            width: 100%; padding: 12px;
            background: #4CAF50; color: white;
            border: none; border-radius: 25px;
            margin-top: 20px; font-size: 16px;
        }
        .result {
            margin-top: 15px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <button class="back-btn" onclick="window.location.href='payment_detail.php?borrow_id=<?= $borrow_id ?>'">กลับ</button>

    <div class="container">
        <h2>ชำระเงินแบบโอน + เงินสด</h2>
        <img src="uploads/<?= htmlspecialchars($data['qr_image'] ?: 'default_qr.png'); ?>" alt="QR Code">

        <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($data['equipment_name']); ?></p>
        <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($data['borrower_name']); ?></p>
        <p><strong>ยอดรวม:</strong> <?= number_format($price, 2); ?> บาท</p>

        <form method="POST">
            <label>จำนวนเงินที่โอน:</label>
            <input type="number" name="transfer" step="0.01" placeholder="ระบุจำนวนเงินที่โอน" required>

            <label>จำนวนเงินสด:</label>
            <input type="number" name="cash" step="0.01" placeholder="ระบุจำนวนเงินสด" required>

            <button type="submit">ยืนยันการชำระเงิน</button>
        </form>

        <?php if (isset($message)): ?>
            <div class="result"><?= $message ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
