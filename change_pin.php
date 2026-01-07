<?php


session_start();
include("includes/db_connect.php");

// ถ้ายังไม่เข้าสู่ระบบ หรือยังไม่ได้ยืนยัน OTP ให้กลับหน้าแรก
if (!isset($_SESSION["user_id"]) || empty($_SESSION["otp_verified"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin1 = trim($_POST["new_pin"]);
    $pin2 = trim($_POST["confirm_pin"]);

    if (!preg_match("/^[0-9]{6}$/", $pin1)) {
        $error = "❌ รหัส PIN ต้องเป็นตัวเลข 6 หลัก";
    } elseif ($pin1 !== $pin2) {
        $error = "❌ รหัส PIN ไม่ตรงกัน";
    } else {
        $stmt = $conn->prepare("UPDATE tb_user SET pin_code=? WHERE user_id=?");
        $stmt->bind_param("si", $pin1, $user_id);
        if ($stmt->execute()) {
            $success = "✅ เปลี่ยนรหัสผ่าน 6 หลักสำเร็จ";
            unset($_SESSION['otp_verified']); // เคลียร์การยืนยัน OTP เพื่อความปลอดภัย

            // ดีเลย์ 1 วินาทีก่อนกลับไปหน้าโปรไฟล์
            echo "<script>
                setTimeout(() => {
                    window.location.href = 'edit_profile.php';
                }, 1000);
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
<title>เปลี่ยนรหัสผ่าน 6 หลัก (PIN)</title>
<link rel="stylesheet" href="includes/style_backbtn.css">
<style>
body {
    font-family: 'Prompt', sans-serif;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.container {
    width: 90%;
    max-width: 320px;
    text-align: center;
}
h2 {
    color: #333;
    margin-bottom: 20px;
}
input, button {
    width: 100%;
    max-width: 280px;
    padding: 10px;
    margin: 10px auto;
    border-radius: 25px;
    font-size: 16px;
    display: block;
    box-sizing: border-box;
}
input {
    border: 1px solid #ccc;
    text-align: center;
    letter-spacing: 4px;
}
button {
    background: #2196F3;
    color: white;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background: #1976D2;
}
.success { color: green; margin-top: 10px; }
.error { color: red; margin-top: 10px; }
</style>
</head>
<body>

<button class="back-btn" onclick="window.location.href='edit_profile.php'">กลับ</button>

<div class="container">
    <h2>เปลี่ยนรหัสผ่าน 6 หลัก (PIN)</h2>
    <form method="POST">
        <input type="password" name="new_pin" maxlength="6" placeholder="PIN ใหม่" required>
        <input type="password" name="confirm_pin" maxlength="6" placeholder="ยืนยัน PIN" required>
        <button type="submit">บันทึก</button>
    </form>

    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
</div>

</body>
</html>
