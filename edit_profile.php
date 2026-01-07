<?php


// ✅ เปิด session แบบปลอดภัย
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 ป้องกัน cache หน้า (เพื่อกันย้อนกลับหลัง logout)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("includes/db_connect.php");

// 🚫 ถ้าไม่มี session ให้กลับหน้า login
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

// ✅ ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT fullname, email, phone FROM tb_user WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ อัปเดตข้อมูลทั่วไป
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    if ($fullname === "" || $email === "") {
        $error = "❌ กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        $update = $conn->prepare("UPDATE tb_user SET fullname=?, email=?, phone=? WHERE user_id=?");
        $update->bind_param("sssi", $fullname, $email, $phone, $user_id);

        if ($update->execute()) {
            $success = "✅ บันทึกข้อมูลสำเร็จ";
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
    <title>แก้ไขโปรไฟล์</title>

    <!-- ปุ่มกลับใช้ร่วมกัน -->
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
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .submit-btn {
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

        .submit-btn:hover {
            background: #1976D2;
        }

        .setting-links {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .setting-links a {
            display: block;
            color: #2196F3;
            text-decoration: none;
            margin: 6px 0;
        }

        .setting-links a:hover {
            text-decoration: underline;
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

    <button class="back-btn" onclick="window.location.href='main.php'">กลับ</button>

    <div class="container">
        <h2>แก้ไขโปรไฟล์</h2>

        <div class="card">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="update_profile" value="1">

                <label>ชื่อ - นามสกุล</label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>

                <label>อีเมล</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <label>เบอร์โทรศัพท์</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

                <div class="setting-links">
                    <a href="otp_request.php?type=password">เปลี่ยนรหัสผ่านบัญชี</a>
                    <a href="otp_request.php?type=pin">เปลี่ยนรหัสผ่าน 6 หลัก (PIN)</a>
                </div>

                <button type="submit" class="submit-btn">บันทึกข้อมูล</button>
            </form>
        </div>

        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    </div>

</body>
</html>
