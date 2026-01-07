<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 ป้องกัน cache หน้าหลัง logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("includes/db_connect.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/includes/mail_config.php';

// 🚫 ถ้าไม่ได้เข้าสู่ระบบ
if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้');
            window.location.href = 'index.php';
          </script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$type = $_GET['type'] ?? 'password';
$display_type = ($type === 'pin') ? "รหัสผ่าน 6 หลัก (PIN)" : "รหัสผ่านบัญชี";

$error = "";
$success = "";
$cooldown = 60;

// 🕒 คำนวณเวลาคงเหลือ
$remaining_time = isset($_SESSION['otp_time'])
    ? max(0, $cooldown - (time() - $_SESSION['otp_time']))
    : 0;

// ✅ ถ้ายังไม่ verify
if (empty($_SESSION['otp_verified'])) {

    // กำหนดกรณีต้องส่งรหัสใหม่
    if (
        !isset($_SESSION['otp']) ||
        $remaining_time <= 0 ||
        isset($_POST["send_otp"])
    ) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_type'] = $type;
        $_SESSION['otp_time'] = time();

        // ดึงอีเมลจากฐานข้อมูล
        $stmt = $conn->prepare("SELECT email FROM tb_user WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $email = $user['email'];

        // ส่งอีเมล OTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = MAIL_PORT;
            $mail->CharSet = "UTF-8";

            $mail->setFrom(MAIL_USERNAME, 'ระบบยืนยันตัวตน');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "รหัส OTP สำหรับยืนยันการเปลี่ยน{$display_type}";
            $mail->Body = "
                <h2>รหัส OTP สำหรับเปลี่ยน{$display_type}</h2>
                <p style='font-size:24px;letter-spacing:4px;'><b>$otp</b></p>
                <p>รหัสนี้มีอายุ 60 วินาที กรุณาใช้ก่อนหมดเวลา</p>";

            $mail->send();
            $success = "📩 ส่งรหัส OTP ไปยังอีเมลของคุณแล้ว เพื่อยืนยันการเปลี่ยน{$display_type}";
            $remaining_time = $cooldown;
        } catch (Exception $e) {
            $error = "❌ ส่งอีเมลไม่สำเร็จ: " . $mail->ErrorInfo;
        }
    }

    // ✅ ตรวจสอบ OTP ที่กรอก
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["verify_otp"])) {
        $input_otp = trim($_POST["otp"]);

        // 🧩 แปลงเป็น string และตัดช่องว่าง เพื่อให้เทียบได้ตรง
        $session_otp = isset($_SESSION['otp']) ? trim(strval($_SESSION['otp'])) : '';
        $input_otp = trim(strval($input_otp));

        // 🧠 Debug (ใช้ดูใน error.log)
        error_log("DEBUG - SESSION OTP: " . $session_otp);
        error_log("DEBUG - INPUT OTP: " . $input_otp);

        if (
            $input_otp === $session_otp &&
            (time() - $_SESSION['otp_time']) <= $cooldown
        ) {
            $_SESSION['otp_verified'] = true;
            $success = "✅ ยืนยันรหัส OTP สำเร็จ";

            // ไปหน้าตามประเภท
            if ($type === 'pin') {
                header("refresh:1; url=change_pin.php");
            } else {
                header("refresh:1; url=change_password.php");
            }
            exit();
        } else {
            $error = "❌ รหัส OTP ไม่ถูกต้องหรือหมดอายุ";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยันรหัส OTP</title>
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
            font-size: 18px;
        }

        button {
            background: #2196F3;
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #1976D2;
        }

        button.disabled {
            background: #b0c4de;
            cursor: not-allowed;
        }

        .success {
            color: green;
            margin-top: 10px;
        }

        .error {
            color: red;
            margin-top: 10px;
        }
    </style>

    <script>
        let countdown = <?= $remaining_time ?>;
        function startCountdown() {
            const btn = document.getElementById("otp-btn");
            if (!btn) return;
            if (countdown > 0) {
                btn.classList.add('disabled');
                btn.disabled = true;
                const interval = setInterval(() => {
                    countdown--;
                    btn.innerText = `ขอรหัสใหม่ (${countdown} วิ)`;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        btn.innerText = "ขอรหัสใหม่";
                        btn.disabled = false;
                        btn.classList.remove('disabled');
                    }
                }, 1000);
            }
        }
    </script>
</head>

<body onload="startCountdown()">
    <button class="back-btn" onclick="window.location.href='edit_profile.php'">กลับ</button>

    <div class="container">
        <h2>ยืนยันรหัส OTP</h2>
        <p>เพื่อเปลี่ยน<?= $display_type ?></p>

        <form method="POST">
            <input type="text" name="otp" maxlength="6" placeholder="กรอกรหัส OTP" required>
            <button type="submit" name="verify_otp">ยืนยันรหัส</button>
        </form>

        <?php if (empty($_SESSION['otp_verified'])): ?>
            <form method="POST">
                <button type="submit" name="send_otp" id="otp-btn">ขอรหัสใหม่</button>
            </form>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
