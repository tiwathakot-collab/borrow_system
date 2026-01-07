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

$borrow_id = intval($_GET['borrow_id']);
$user_id = $_SESSION['user_id'];

// ✅ ดึงข้อมูลอุปกรณ์
$sql = "SELECT b.*, e.equipment_name, e.price_per_day, e.price_per_hour
        FROM tb_borrow b
        JOIN tb_equipment e ON b.equipment_id = e.equipment_id
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
$change = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $given = floatval($_POST['given']);
    if ($given < $price) {
        $error = "❌ จำนวนเงินไม่เพียงพอ!";
    } else {
        $change = $given - $price;
        // อัปเดตสถานะเป็นชำระเงินแล้ว
        $conn->query("UPDATE tb_borrow SET status='ชำระเงินแล้ว' WHERE borrow_id=$borrow_id");
        $conn->query("UPDATE tb_equipment SET status='ว่าง', updated_at=NOW() WHERE equipment_id=" . intval($data['equipment_id']));
        $success = "✅ ชำระเงินสำเร็จ! เงินทอน " . number_format($change, 2) . " บาท";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ชำระเงินสด</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding-bottom: 80px;
        }

        .container {
            max-width: 450px;
            background: #fff;
            margin: 80px auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        p {
            margin: 6px 0;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            border: none;
            color: white;
            border-radius: 25px;
            font-size: 16px;
            margin-top: 20px;
        }

        .success {
            color: green;
            text-align: center;
            margin-top: 15px;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <button class="back-btn"
        onclick="window.location.href='payment_detail.php?borrow_id=<?= $borrow_id ?>'">กลับ</button>

    <div class="container">
        <h2>ชำระเงินสด</h2>

        <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($data['equipment_name']); ?></p>
        <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($data['borrower_name']); ?></p>
        <p><strong>ยอดชำระ:</strong> <?= number_format($price, 2); ?> บาท</p>

        <form method="POST">
            <label>จำนวนเงินที่ได้รับ:</label>
            <input type="number" name="given" step="0.01" placeholder="ระบุจำนวนเงินที่ลูกค้าให้มา" required>
            <button type="submit">คำนวณเงินทอน</button>
        </form>

        <?php if (isset($success)): ?>
            <div class="success"><?= $success; ?></div>
            <script>
                setTimeout(() => { window.location.href = 'payment.php'; }, 2000);
            </script>
        <?php elseif (isset($error)): ?>
            <div class="error"><?= $error; ?></div>
        <?php elseif ($change !== null): ?>
            <div class="success">เงินทอน: <?= number_format($change, 2); ?> บาท</div>
        <?php endif; ?>
    </div>
</body>

</html>
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

$borrow_id = intval($_GET['borrow_id']);
$user_id = $_SESSION['user_id'];

// ✅ ดึงข้อมูลอุปกรณ์
$sql = "SELECT b.*, e.equipment_name, e.price_per_day, e.price_per_hour
        FROM tb_borrow b
        JOIN tb_equipment e ON b.equipment_id = e.equipment_id
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
$change = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $given = floatval($_POST['given']);
    if ($given < $price) {
        $error = "❌ จำนวนเงินไม่เพียงพอ!";
    } else {
        $change = $given - $price;
        // อัปเดตสถานะเป็นชำระเงินแล้ว
        $conn->query("UPDATE tb_borrow SET status='ชำระเงินแล้ว' WHERE borrow_id=$borrow_id");
        $conn->query("UPDATE tb_equipment SET status='ว่าง', updated_at=NOW() WHERE equipment_id=" . intval($data['equipment_id']));
        $success = "✅ ชำระเงินสำเร็จ! เงินทอน " . number_format($change, 2) . " บาท";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ชำระเงินสด</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding-bottom: 80px;
        }

        .container {
            max-width: 450px;
            background: #fff;
            margin: 80px auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        p {
            margin: 6px 0;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            border: none;
            color: white;
            border-radius: 25px;
            font-size: 16px;
            margin-top: 20px;
        }

        .success {
            color: green;
            text-align: center;
            margin-top: 15px;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <button class="back-btn"
        onclick="window.location.href='payment_detail.php?borrow_id=<?= $borrow_id ?>'">กลับ</button>

    <div class="container">
        <h2>ชำระเงินสด</h2>

        <p><strong>ชื่ออุปกรณ์:</strong> <?= htmlspecialchars($data['equipment_name']); ?></p>
        <p><strong>ชื่อผู้ยืม:</strong> <?= htmlspecialchars($data['borrower_name']); ?></p>
        <p><strong>ยอดชำระ:</strong> <?= number_format($price, 2); ?> บาท</p>

        <form method="POST">
            <label>จำนวนเงินที่ได้รับ:</label>
            <input type="number" name="given" step="0.01" placeholder="ระบุจำนวนเงินที่ลูกค้าให้มา" required>
            <button type="submit">คำนวณเงินทอน</button>
        </form>

        <?php if (isset($success)): ?>
            <div class="success"><?= $success; ?></div>
            <script>
                setTimeout(() => { window.location.href = 'payment.php'; }, 2000);
            </script>
        <?php elseif (isset($error)): ?>
            <div class="error"><?= $error; ?></div>
        <?php elseif ($change !== null): ?>
            <div class="success">เงินทอน: <?= number_format($change, 2); ?> บาท</div>
        <?php endif; ?>
    </div>
</body>

</html>