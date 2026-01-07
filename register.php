<?php
session_start();
include("includes/db_connect.php");

$error = "";
$success = "";

// เก็บค่าที่กรอกไว้
$fullname_val = "";
$email_val = "";
$pin_val = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname_val = trim($_POST['fullname']);
    $email_val = trim($_POST['email']);
    $password = trim($_POST['password']);
    $pin_val = trim($_POST['pin']);

    if (!preg_match("/^\d{6}$/", $pin_val)) {
        $error = "❌ รหัส PIN ต้องเป็นตัวเลข 6 หลัก";
    } else {
        $stmt = $conn->prepare("SELECT * FROM tb_user WHERE email=?");
        $stmt->bind_param("s", $email_val);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "❌ อีเมลนี้ถูกใช้งานแล้ว";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO tb_user (fullname, email, password, pin_code) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname_val, $email_val, $hashed_password, $pin_val);
            if ($stmt->execute()) {
                $success = "✅ สมัครสมาชิกสำเร็จ! กำลังพาไปหน้าล็อกอิน...";
                echo "<script>
                        setTimeout(()=>{ window.location.href='index.php'; }, 1000);
                      </script>";
                // ล้างค่ารหัสผ่าน
                $password = "";
            } else {
                $error = "❌ เกิดข้อผิดพลาดในการสมัครสมาชิก";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #fafafa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 320px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        .success {
            color: green;
            font-size: 14px;
            margin-top: 10px;
        }

        .link-login {
            margin-top: 10px;
        }

        .link-login a {
            text-decoration: none;
            color: #333;
        }

        .link-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>สมัครสมาชิก</h2>
        <form method="POST" action="">
            <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" required autocomplete="off"
                value="<?php echo htmlspecialchars($fullname_val); ?>">
            <input type="email" name="email" placeholder="อีเมล" required autocomplete="off"
                value="<?php echo htmlspecialchars($email_val); ?>">
            <input type="password" name="password" placeholder="รหัสผ่าน" required autocomplete="new-password">
            <input type="password" name="pin" placeholder="รหัส PIN 6 หลัก" maxlength="6" required autocomplete="off"
                value="<?php echo htmlspecialchars($pin_val); ?>">
            <button type="submit">สมัครสมาชิก</button>
        </form>

        <?php if ($error)
            echo "<div class='error'>$error</div>"; ?>
        <?php if ($success)
            echo "<div class='success'>$success</div>"; ?>

        <div class="link-login"><a href="index.php">เข้าสู่ระบบ</a></div>
    </div>
</body>

</html>