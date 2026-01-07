<?php
// ✅ เปิด session แบบปลอดภัย (กันซ้ำ)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 ป้องกัน cache หลัง logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("includes/db_connect.php");

// 🚫 ถ้าไม่ได้เข้าสู่ระบบให้กลับไปหน้า login
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
$next_page = isset($_GET['next']) ? $_GET['next'] : "edit_profile.php";

// ✅ ใช้ชื่อ field คงที่
$field_name = "pin_input";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pin = trim($_POST[$field_name] ?? '');

    $stmt = $conn->prepare("SELECT pin_code FROM tb_user WHERE user_id=?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $db_pin = trim(preg_replace('/\s+/', '', $user['pin_code']));
            $input_pin = trim(preg_replace('/\s+/', '', $pin));

            if ($db_pin === $input_pin) {
                $success = "✅ รหัสถูกต้อง";
                echo "<script>
                        setTimeout(()=>{ window.location.href='$next_page'; }, 1000);
                      </script>";
            } else {
                $error = "❌ รหัสไม่ถูกต้อง";
            }
        } else {
            $error = "❌ ไม่พบบัญชีผู้ใช้นี้";
        }
    } else {
        $error = "❌ เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยันรหัส 6 หลัก</title>
    <link rel="stylesheet" href="includes/style_backbtn.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #fff;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
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

        input {
            width: 80%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
            font-size: 20px;
            letter-spacing: 5px;
            outline: none;
        }

        button.submit-btn {
            display: inline-block;
            padding: 10px 30px;
            margin-top: 20px;
            background: #2196F3;
            color: #fff;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.2s;
        }

        button.submit-btn:hover {
            background: #1976D2;
        }

        .error {
            color: red;
            margin-top: 10px;
            font-size: 14px;
        }

        .success {
            color: green;
            margin-top: 10px;
            font-size: 14px;
        }

        .forgot-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .forgot-link a {
            color: #2196F3;
            text-decoration: none;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <button class="back-btn" onclick="window.location.href='main.php'">กลับ</button>

    <div class="container">
        <h2>กรอกรหัสผ่าน 6 หลัก</h2>

        <form method="POST" action="" autocomplete="off">
            <input type="text" name="fakeuser" style="display:none">
            <input type="password" name="fakepass" style="display:none">

            <input 
                type="password" 
                name="<?= $field_name ?>" 
                maxlength="6" 
                pattern="\d{6}" 
                placeholder="******"
                required 
                autocomplete="new-password" 
                autocorrect="off" 
                autocapitalize="off" 
                inputmode="numeric"
            >
            <br>
            <button type="submit" class="submit-btn">ยืนยัน</button>
        </form>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <div class="forgot-link">
            <a href="otp_request.php?next=<?= urlencode($next_page) ?>">ลืมรหัส?</a>
        </div>
    </div>

</body>
</html>
