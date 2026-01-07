<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/db_connect.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('โปรดเข้าสู่ระบบก่อนเพิ่มอุปกรณ์');
            window.location.href = 'index.php';
          </script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$fullname = $_SESSION["fullname"];

$error = "";
$success = "";

// ✅ เมื่อกดบันทึก
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["equipment_name"]);
    $desc = trim($_POST["description"]);
    $price_day = floatval($_POST["price_per_day"]);
    $price_hour = floatval($_POST["price_per_hour"]);

    if ($name == "") {
        $error = "❌ กรุณากรอกชื่ออุปกรณ์";
    } elseif ($price_day <= 0 && $price_hour <= 0) {
        $error = "❌ กรุณากรอกราคาอย่างน้อยหนึ่งช่อง";
    } else {
        // ✅ จัดการอัปโหลดรูปภาพ
        $image_name = null;
        if (!empty($_FILES["equipment_image"]["name"])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_ext = pathinfo($_FILES["equipment_image"]["name"], PATHINFO_EXTENSION);
            $image_name = "eq_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
            $target_file = $target_dir . $image_name;

            $check = getimagesize($_FILES["equipment_image"]["tmp_name"]);
            if ($check === false) {
                $error = "❌ กรุณาอัปโหลดเฉพาะไฟล์ภาพเท่านั้น";
            } elseif (!move_uploaded_file($_FILES["equipment_image"]["tmp_name"], $target_file)) {
                $error = "❌ ไม่สามารถอัปโหลดรูปภาพได้";
            }
        }

        // ✅ บันทึกข้อมูลลงฐานข้อมูล
        if ($error === "") {
            $stmt = $conn->prepare("INSERT INTO tb_equipment 
                (user_id, equipment_name, equipment_image, description, price_per_day, price_per_hour, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'ว่าง', NOW(), NOW())");
            $stmt->bind_param("isssdd", $user_id, $name, $image_name, $desc, $price_day, $price_hour);

            if ($stmt->execute()) {
                $success = "✅ เพิ่มอุปกรณ์เรียบร้อยแล้ว";
                echo "<script>
                        setTimeout(() => { window.location.href='equipment_available.php'; }, 1500);
                      </script>";
            } else {
                $error = "❌ เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มอุปกรณ์ใหม่</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding-bottom: 80px;
        }

        .container {
            max-width: 500px;
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

        label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            height: 80px;
        }

        input[type="file"] {
            margin-top: 8px;
        }

        .price-group {
            display: flex;
            gap: 10px;
        }

        .price-group div {
            flex: 1;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #2196F3;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #1976D2;
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
        <h2>เพิ่มอุปกรณ์ใหม่</h2>

        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <label>ชื่ออุปกรณ์</label>
            <input type="text" name="equipment_name" required>

            <label>รายละเอียด</label>
            <textarea name="description" placeholder="รายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>

            <label>ราคาอุปกรณ์</label>
            <div class="price-group">
                <div>
                    <label>รายวัน (บาท)</label>
                    <input type="number" name="price_per_day" step="0.01" placeholder="เช่น 150" min="0">
                </div>
                <div>
                    <label>รายชั่วโมง (บาท)</label>
                    <input type="number" name="price_per_hour" step="0.01" placeholder="เช่น 20" min="0">
                </div>
            </div>

            <label>รูปภาพอุปกรณ์</label>
            <input type="file" name="equipment_image" accept="image/*">

            <button type="submit">บันทึกข้อมูล</button>
        </form>

        <?php if ($success): ?>
            <div class="success"><?= $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= $error; ?></div>
        <?php endif; ?>
    </div>
</body>

</html>
