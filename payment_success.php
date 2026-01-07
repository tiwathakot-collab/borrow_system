<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ชำระเงินสำเร็จ</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            text-align: center;
            background: #f8f9fa;
            margin: 0;
            padding-top: 100px;
        }
        .box {
            display: inline-block;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        h2 { color: #4CAF50; }
        button {
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            cursor: pointer;
            font-size: 15px;
        }
        button:hover { background: #1976D2; }
    </style>
</head>
<body>
    <div class="box">
        <h2>✅ ชำระเงินสำเร็จ!</h2>
        <p>รายการของคุณถูกบันทึกเรียบร้อยแล้ว</p>
        <button onclick="window.location.href='payment.php'">กลับไปหน้าการชำระเงิน</button>
    </div>
</body>
</html>
