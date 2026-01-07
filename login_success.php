<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบสำเร็จ</title>
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
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin: 0;
        }

        p {
            color: #666;
            font-size: 16px;
            margin-top: 8px;
        }
    </style>
</head>

<body>

    <img src="/borrow_system/assets/check.png" alt="success">
    <h1>เข้าสู่ระบบสำเร็จ</h1>
    <p>กำลังพาคุณไปยังหน้าหลัก...</p>

    <script>
        // รอ 2.5 วินาที แล้วพาไป main.php
        setTimeout(() => {
            window.location.href = "main.php";
        }, 2500);
    </script>

</body>

</html>