<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิกสำเร็จ</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Prompt', sans-serif;
        }

        body {
            margin: 0;
            background: #ffffff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .success-container {
            background: #fff;
            border-radius: 16px;
            padding: 40px 50px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            width: 90%;
            max-width: 420px;
            transition: 0.3s;
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #4CAF50;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 45px;
            margin: 0 auto 20px;
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        }

        h1 {
            margin: 10px 0;
            color: #333;
            font-weight: 600;
        }

        p {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background: #4CAF50;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 15px;
            transition: 0.2s;
            cursor: pointer;
        }

        .btn:hover {
            background: #43A047;
            box-shadow: 0 4px 8px rgba(67, 160, 71, 0.3);
        }
    </style>
</head>

<body>

    <div class="success-container">
        <div class="success-icon">✔</div>
        <h1>สมัครสมาชิกสำเร็จ</h1>
        <button class="btn" onclick="window.location.href='index.php'">กลับไปหน้าเข้าสู่ระบบ</button>
    </div>

</body>

</html>