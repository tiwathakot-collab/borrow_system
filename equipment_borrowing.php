<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/db_connect.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้');
            window.location.href = 'index.php';
          </script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$fullname = $_SESSION["fullname"];

// ✅ ดึงข้อมูลการยืมและข้อมูลอุปกรณ์
$sql = "
SELECT
    b.borrow_id,
    b.borrower_name,
    b.borrow_type,
    b.contact_info,
    b.note,
    b.borrow_start,
    b.borrow_end,
    b.borrow_duration,
    b.total_price,
    e.equipment_name,
    e.equipment_image,
    e.price_per_day,
    e.price_per_hour
FROM tb_borrow b
INNER JOIN tb_equipment e ON b.equipment_id = e.equipment_id
WHERE e.user_id = ? AND b.status = 'กำลังยืม'
ORDER BY b.created_at DESC
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
    <title>อุปกรณ์ที่กำลังยืมอยู่</title>
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

        .menu-btn {
            background: none;
            border: none;
            cursor: pointer;
        }

        .menu-btn img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        h1 {
            font-size: 20px;
            text-align: center;
            flex: 1;
            color: #000;
        }

        .container {
            width: 95%;
            max-width: 900px;
            margin: 20px auto;
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
        <h1>รายการอุปกรณ์ที่กำลังยืมอยู่</h1>
        <div style="width:36px;"></div>
    </div>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $rate = ($row["borrow_type"] === "รายวัน") ? $row["price_per_day"] : $row["price_per_hour"];
                $total_price = $rate * $row["borrow_duration"];
                ?>
                <div class="borrow-card">
                    <img src="uploads/<?= htmlspecialchars($row['equipment_image'] ?: 'default.png'); ?>" alt="อุปกรณ์">
                    <div class="borrow-info">
                        <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($row['equipment_name']); ?></p>
                        <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($row['borrower_name']); ?></p>
                        <p><strong>ประเภทเวลา:</strong> <?= htmlspecialchars($row['borrow_type']); ?></p>
                        <p><strong>ระยะเวลา:</strong> <?= htmlspecialchars($row['borrow_duration']); ?>
                            <?= $row['borrow_type'] === 'รายวัน' ? 'วัน' : 'ชั่วโมง'; ?></p>
                        <p><strong>ราคาต่อหน่วย:</strong> <?= number_format($rate, 2); ?> บาท</p>
                        <p><strong>ราคารวม:</strong> <?= number_format($total_price, 2); ?> บาท</p>
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
                    <div class="no-data">ยังไม่มีอุปกรณ์ที่ถูกยืมอยู่
            </div>
        <?php endif; ?>
    </div>

    <div class="tabbar">
        <a href="equipment_available.php">ว่าง</a>
        <a href="equipment_borrowing.php" class="active">กำลังยืม</a>
        <a href="equipment_near_expire.php">ใกล้หมดเวลา</a>
        <a href="payment.php">การชำระเงิน</a>
    </div>
</body>

</html>