<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db_connect.php");

// ป้องกัน cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้'); window.location.href='index.php';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];

// ตรวจสอบ borrow_id
if (!isset($_GET['borrow_id']) || !is_numeric($_GET['borrow_id'])) {
    echo "<script>alert('ไม่พบรายการยืม'); window.location.href='equipment_borrowing.php';</script>";
    exit();
}
$borrow_id = intval($_GET['borrow_id']);

// ดึงข้อมูล borrow + equipment
$sql = "SELECT b.borrow_id, b.borrower_name, b.borrow_type, b.borrow_duration,
        e.equipment_name, e.equipment_image, e.price_per_day, e.price_per_hour
        FROM tb_borrow b
        JOIN tb_equipment e ON b.equipment_id = e.equipment_id
        WHERE b.borrow_id = ? AND e.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $borrow_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('ไม่พบข้อมูลอุปกรณ์นี้'); window.location.href='equipment_borrowing.php';</script>";
    exit();
}
$data = $result->fetch_assoc();

// คำนวณราคา
$rate = ($data['borrow_type'] === 'รายวัน') ? $data['price_per_day'] : $data['price_per_hour'];
$total_price = $rate * $data['borrow_duration'];

// เมื่อกดชำระเงิน
if (isset($_POST['submit_payment'])) {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method']; // 'สด' หรือ 'โอน'
    $created_at = date("Y-m-d H:i:s");

    // บันทึกการชำระเงิน
    $insert = $conn->prepare("INSERT INTO tb_payment (borrow_id, user_id, amount, payment_method, created_at) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("iidss", $borrow_id, $user_id, $amount, $method, $created_at);
    $exec = $insert->execute();

    if ($exec) {
        // อัปเดตสถานะ borrow เป็น "ชำระแล้ว"
        $update_borrow = $conn->prepare("UPDATE tb_borrow SET status='ชำระแล้ว' WHERE borrow_id=?");
        $update_borrow->bind_param("i", $borrow_id);
        $update_borrow->execute();

        // อัปเดตสถานะอุปกรณ์เป็น "ว่าง"
        $update_equip = $conn->prepare("UPDATE tb_equipment e
                                        JOIN tb_borrow b ON e.equipment_id = b.equipment_id
                                        SET e.status = 'ว่าง'
                                        WHERE b.borrow_id = ?");
        $update_equip->bind_param("i", $borrow_id);
        $update_equip->execute();

        echo "<script>
                alert('บันทึกการชำระเงินเรียบร้อย');
                window.location.href='payment.php';
              </script>";
        exit();
    } else {
        die('เกิดข้อผิดพลาดในการบันทึกการชำระเงิน: (' . $insert->errno . ') ' . $insert->error);
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดการชำระเงิน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background: #f4f6f8; margin: 0; padding-bottom: 80px; }
        .container { width: 95%; max-width: 500px; margin: 50px auto; background: #fff; border-radius: 14px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); padding: 25px; }
        img.equipment { width: 220px; height: 220px; border-radius: 12px; object-fit: cover; margin-bottom: 15px; display: block; margin-left: auto; margin-right: auto; }
        h2 { text-align: center; color: #333; margin-bottom: 15px; }
        p { margin: 6px 0; color: #333; }
        form { margin-top: 20px; }
        label { display: block; margin: 10px 0 5px; }
        input[type=number], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 10px; }
        button.submit-btn { width: 100%; padding: 12px; background: #4CAF50; color: #fff; border: none; border-radius: 25px; font-size: 16px; cursor: pointer; transition: .2s; }
        button.submit-btn:hover { background: #388E3C; }
        button.back-btn { position: fixed; top: 20px; left: 20px; background: #fff; color: #2196F3; border: 2px solid #2196F3; border-radius: 25px; padding: 6px 12px; font-size: 14px; font-weight: 500; cursor: pointer; transition: .2s; z-index: 10; }
        button.back-btn:hover { background: #2196F3; color: white; }
    </style>
</head>

<body>

    <button class="back-btn" onclick="window.location.href='equipment_borrowing.php'">กลับ</button>

    <div class="container">
        <img class="equipment" src="uploads/<?= htmlspecialchars($data['equipment_image'] ?: 'default.png'); ?>" alt="Equipment">
        <h2><?= htmlspecialchars($data['equipment_name']); ?></h2>
        <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($data['borrower_name']); ?></p>
        <p><strong>ประเภทเวลา:</strong> <?= htmlspecialchars($data['borrow_type']); ?> |
            <?= $data['borrow_duration'] ?> <?= $data['borrow_type'] === 'รายวัน' ? 'วัน' : 'ชั่วโมง' ?></p>
        <p><strong>ราคาต่อหน่วย:</strong> <?= number_format($rate, 2); ?> บาท</p>
        <p><strong>ราคารวม:</strong> <?= number_format($total_price, 2); ?> บาท</p>

        <form method="post">
            <label>จำนวนเงินที่จ่าย</label>
            <input type="number" name="amount" value="<?= number_format($total_price, 2, '.', ''); ?>" step="0.01" required>

            <label>วิธีชำระเงิน</label>
            <select name="method" required>
                <option value="สด">เงินสด</option>
                <option value="โอน">โอน</option>
            </select>

            <button type="submit" name="submit_payment" class="submit-btn">บันทึกการชำระเงิน</button>
        </form>
    </div>

</body>
</html>
