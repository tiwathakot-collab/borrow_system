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

// ✅ ตรวจสอบการรับค่า id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('ไม่พบอุปกรณ์ที่ต้องการแก้ไข');
            window.location.href = 'equipment_available.php';
          </script>";
    exit();
}

$equipment_id = intval($_GET['id']);
$error = "";
$success = "";

// ✅ ดึงข้อมูลเดิม
$stmt = $conn->prepare("SELECT * FROM tb_equipment WHERE equipment_id=? AND user_id=?");
$stmt->bind_param("ii", $equipment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
            alert('ไม่พบอุปกรณ์นี้ในระบบของคุณ');
            window.location.href = 'equipment_available.php';
          </script>";
    exit();
}

$equipment = $result->fetch_assoc();

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
        $image_name = $equipment['equipment_image']; // ใช้รูปเดิมก่อน

        // ✅ อัปโหลดรูปใหม่ถ้ามี
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

        if ($error === "") {
            $stmt = $conn->prepare("UPDATE tb_equipment 
                SET equipment_name=?, description=?, price_per_day=?, price_per_hour=?, equipment_image=?, updated_at=NOW()
                WHERE equipment_id=? AND user_id=?");
            $stmt->bind_param("ssddsii", $name, $desc, $price_day, $price_hour, $image_name, $equipment_id, $user_id);

            if ($stmt->execute()) {
                $success = "✅ บันทึกข้อมูลเรียบร้อยแล้ว";
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
    <title>แก้ไขอุปกรณ์</title>
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

        .preview {
            text-align: center;
            margin-top: 10px;
        }

        .preview img {
            width: 180px;
            height: 180px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <button class="back-btn" onclick="window.location.href='equipment_detail.php?id=<?= $equipment_id ?>'">กลับ</button>

    <div class="container">
        <h2>แก้ไขข้อมูลอุปกรณ์</h2>

        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <label>ชื่ออุปกรณ์</label>
            <input type="text" name="equipment_name" value="<?= htmlspecialchars($equipment['equipment_name']); ?>" required>

            <label>รายละเอียด</label>
            <textarea name="description"><?= htmlspecialchars($equipment['description']); ?></textarea>

            <label>ราคาอุปกรณ์</label>
            <div class="price-group">
                <div>
                    <label>รายวัน (บาท)</label>
                    <input type="number" name="price_per_day" step="0.01" value="<?= htmlspecialchars($equipment['price_per_day']); ?>" min="0">
                </div>
                <div>
                    <label>รายชั่วโมง (บาท)</label>
                    <input type="number" name="price_per_hour" step="0.01" value="<?= htmlspecialchars($equipment['price_per_hour']); ?>" min="0">
                </div>
            </div>

            <label>รูปภาพอุปกรณ์</label>
            <input type="file" name="equipment_image" accept="image/*">
            <?php if (!empty($equipment['equipment_image'])): ?>
                <div class="preview">
                    <img src="uploads/<?= htmlspecialchars($equipment['equipment_image']); ?>" alt="ภาพเดิม">
                    <p style="color:#666; font-size:14px;">(หากไม่อัปโหลดใหม่ จะใช้ภาพเดิม)</p>
                </div>
            <?php endif; ?>

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
