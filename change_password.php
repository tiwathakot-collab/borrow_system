<?php

session_start();
include("includes/db_connect.php");

if (!isset($_SESSION["user_id"]) || empty($_SESSION["otp_verified"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_pass = trim($_POST["new_password"]);
    $confirm = trim($_POST["confirm_password"]);

    if ($new_pass === "" || $confirm === "") {
        $error = "❌ กรุณากรอกรหัสให้ครบ";
    } elseif ($new_pass !== $confirm) {
        $error = "❌ รหัสผ่านไม่ตรงกัน";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE tb_user SET password=? WHERE user_id=?");
        $stmt->bind_param("si", $hashed, $user_id);
        if ($stmt->execute()) {
            $success = "✅ เปลี่ยนรหัสผ่านบัญชีสำเร็จ";
            unset($_SESSION['otp_verified']);

            echo "<script>
                setTimeout(() => {
                    window.location.href = 'edit_profile.php';
                }, 1000);
            </script>";
        } else {
            $error = "❌ เกิดข้อผิดพลาดในการบันทึก";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เปลี่ยนรหัสผ่านบัญชี</title>
<link rel="stylesheet" href="includes/style_backbtn.css">
<style>
body {
    font-family: 'Prompt', sans-serif;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    width: 90%;
    max-width: 320px;
    text-align: center;
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
}
button {
    background: #2196F3;
    color: white;
    border: none;
    cursor: pointer;
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
    <h2>เปลี่ยนรหัสผ่านบัญชี</h2>
    <form method="POST">
        <input type="password" name="new_password" placeholder="รหัสผ่านใหม่" required>
        <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
        <button type="submit">บันทึก</button>
    </form>

    <?php if($success) echo "<div class='success'>$success</div>"; ?>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>
</div>
</body>
</html>
