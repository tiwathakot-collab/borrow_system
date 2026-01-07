<?php
$message = isset($_GET['message']) ? $_GET['message'] : 'ดำเนินการสำเร็จ';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'main.php';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>สำเร็จ</title>
    <style>
        body {
            margin: 0;
            background: #ffffff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Prompt', sans-serif;
        }

        img {
            width: 120px;
            height: 120px;
            margin-bottom: 25px;
        }

        h1 {
            color: #333;
            font-size: 22px;
            margin: 0;
        }

        p {
            color: #777;
            font-size: 16px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <!-- ✅ รูปติ๊กถูก -->
    <img src="assets/check.png" alt="success">

    <!-- ✅ ข้อความที่ส่งมา -->
    <h1><?php echo htmlspecialchars($message); ?></h1>

    <!-- ✅ ข้อความบอกกำลังพาไป -->
    <p>กำลังพาไปยังหน้าถัดไป...</p>

    <script>
        // ✅ พาไปหน้าที่กำหนดในพารามิเตอร์ redirect หลัง 2.5 วินาที
        setTimeout(() => {
            window.location.href = "<?php echo htmlspecialchars($redirect); ?>";
        }, 2500);
    </script>

</body>

</html>