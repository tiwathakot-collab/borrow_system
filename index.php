<?php
session_start();
include("includes/db_connect.php");

$error = "";
$email_val = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_val = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM tb_user WHERE email=?");
    $stmt->bind_param("s", $email_val);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];

            // ถ้ารหัสถูก ให้ไปหน้า login_success.php
            header("Location: login_success.php?next=main.php");
            exit();
        } else {
            $error = "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = "❌ ไม่พบบัญชีผู้ใช้นี้";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
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

        .register-link {
            margin-top: 10px;
        }

        .register-link a {
            text-decoration: none;
            color: #333;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>เข้าสู่ระบบ</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="อีเมล" required autocomplete="off"
                value="<?php echo htmlspecialchars($email_val); ?>">
            <input type="password" name="password" placeholder="รหัสผ่าน" required autocomplete="new-password">
            <button type="submit">เข้าสู่ระบบ</button>
        </form>

        <?php if ($error)
            echo "<div class='error'>$error</div>"; ?>

        <div class="register-link"><a href="register.php">ยังไม่มีบัญชี? สมัครสมาชิก</a></div>
    </div>
</body>

</html>