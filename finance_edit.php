<?php
session_start();
include("includes/db_connect.php");

// 🔒 ป้องกัน cache หลัง logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 🚫 ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้');
            window.location.href = 'index.php';
          </script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

// ✅ ดึงข้อมูลการเงินปัจจุบัน
$stmt = $conn->prepare("SELECT account_name, bank_name, promptpay, qr_image FROM tb_finance WHERE user_id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$finance = $result->fetch_assoc();

// ✅ บันทึกข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_name = trim($_POST["account_name"]);
    $bank_name = trim($_POST["bank_name"]);
    $promptpay = trim($_POST["promptpay"]);

    // 📷 จัดการอัปโหลดรูป QR ใหม่ (ถ้ามี)
    $qr_file = $finance["qr_image"];
    if (!empty($_FILES["qr_image"]["name"])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);
        $file_name = "qr_" . time() . "_" . basename($_FILES["qr_image"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["qr_image"]["tmp_name"], $target_file)) {
            $qr_file = $file_name;
        } else {
            $error = "❌ ไม่สามารถอัปโหลดไฟล์ได้";
        }
    }

    // ✅ ตรวจว่ามีข้อมูลอยู่แล้วหรือยัง
    $check = $conn->prepare("SELECT COUNT(*) FROM tb_finance WHERE user_id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        $update = $conn->prepare("UPDATE tb_finance SET account_name=?, bank_name=?, promptpay=?, qr_image=? WHERE user_id=?");
        $update->bind_param("ssssi", $account_name, $bank_name, $promptpay, $qr_file, $user_id);
        if ($update->execute()) {
            $success = "✅ อัปเดตข้อมูลสำเร็จ! กำลังกลับไปยังหน้าข้อมูลการเงิน...";
            echo "<script>
                setTimeout(()=>{ window.location.href='finance.php'; }, 1000);
              </script>";
        } else {
            $error = "❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        }
    } else {
        $insert = $conn->prepare("INSERT INTO tb_finance (user_id, account_name, bank_name, promptpay, qr_image) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("issss", $user_id, $account_name, $bank_name, $promptpay, $qr_file);
        if ($insert->execute()) {
            $success = "✅ เพิ่มข้อมูลสำเร็จ! กำลังกลับไปยังหน้าข้อมูลการเงิน...";
            echo "<script>
                setTimeout(()=>{ window.location.href='finance.php'; }, 1000);
              </script>";
        } else {
            $error = "❌ เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    }


}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลการเงิน</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #fafafa;
            margin: 0;
            padding-bottom: 60px;
        }

        .container {
            width: 90%;
            max-width: 450px;
            margin: 80px auto;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 500;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        input[type="file"] {
            border: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #2196F3;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1976D2;
        }

        .qr-preview {
            display: block;
            margin: 15px auto;
            width: 200px;
            border-radius: 12px;
            border: 1px solid #ddd;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }

        .success {
            color: green;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <button class="back-btn" onclick="window.location.href='finance.php'">กลับ</button>

    <div class="container">
        <h2>แก้ไขข้อมูลการเงิน</h2>

        <div class="card">
            <form method="POST" enctype="multipart/form-data" autocomplete="off">
                <label>ชื่อบัญชี</label>
                <input type="text" name="account_name" value="<?= htmlspecialchars($finance['account_name'] ?? '') ?>"
                    required>

                <label>ธนาคาร</label>
                <input type="text" name="bank_name" value="<?= htmlspecialchars($finance['bank_name'] ?? '') ?>"
                    required>

                <label>หมายเลขพร้อมเพย์</label>
                <input type="text" name="promptpay" value="<?= htmlspecialchars($finance['promptpay'] ?? '') ?>">

                <label>QR Code สำหรับชำระเงิน</label>
                <input type="file" name="qr_image" accept="image/*">

                <?php if (!empty($finance['qr_image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($finance['qr_image']); ?>" class="qr-preview">
                <?php endif; ?>

                <button type="submit">บันทึกข้อมูล</button>
            </form>
            <?php if ($error)
                echo "<div class='error'>$error</div>"; ?>
            <?php if ($success)
                echo "<div class='success'>$success</div>"; ?>

        </div>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
    </div>
</body>

</html>