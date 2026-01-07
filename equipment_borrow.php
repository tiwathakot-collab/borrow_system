<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db_connect.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('โปรดเข้าสู่ระบบก่อนเข้าหน้านี้'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$fullname = $_SESSION["fullname"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo "<script>alert('ไม่พบอุปกรณ์'); window.location.href='equipment_available.php';</script>";
    exit();
}

$equipment_id = intval($_GET["id"]);
$stmt = $conn->prepare("SELECT * FROM tb_equipment WHERE equipment_id=? AND user_id=?");
$stmt->bind_param("ii", $equipment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('ไม่พบอุปกรณ์นี้'); window.location.href='equipment_available.php';</script>";
    exit();
}
$equipment = $result->fetch_assoc();

$error = "";
$success = "";

// ✅ เมื่อกดยืม
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $borrower_name = trim($_POST["borrower_name"]);
    $borrow_type = trim($_POST["borrow_type"]);
    $borrow_duration = intval($_POST["borrow_duration"] ?? 0);
    $contact_info = trim($_POST["contact"]);
    $note = trim($_POST["note"]);

    if ($borrower_name === "" || $borrow_type === "" || $contact_info === "" || $borrow_duration <= 0) {
        $error = "❌ กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        $price_per_unit = floatval($equipment["price_per_unit"]);

        // ถ้าเกิน 24 ชม. ให้คิดเป็น 1 วัน
        if ($borrow_type === "รายชั่วโมง" && $borrow_duration > 24) {
            $borrow_type = "รายวัน";
            $borrow_duration = ceil($borrow_duration / 24);
        }

        // คำนวณราคารวม
        $total_price = $price_per_unit * $borrow_duration;

        // เวลาเริ่ม/สิ้นสุด
        $borrow_start = date("Y-m-d H:i:s");
        $borrow_end = ($borrow_type === "รายวัน")
            ? date("Y-m-d H:i:s", strtotime("+{$borrow_duration} days"))
            : date("Y-m-d H:i:s", strtotime("+{$borrow_duration} hours"));

        // ✅ INSERT (ฟิลด์ตรง 100%)
        $sql = "INSERT INTO tb_borrow 
            (equipment_id, borrower_name, contact_info, borrow_type, borrow_duration, borrow_start, borrow_end, total_price, note, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'กำลังยืม', NOW())";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('❌ SQL Prepare Error: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param(
            "isssissds",
            $equipment_id,
            $borrower_name,
            $contact_info,
            $borrow_type,
            $borrow_duration,
            $borrow_start,
            $borrow_end,
            $total_price,
            $note
        );

        if ($stmt->execute()) {
            $conn->query("UPDATE tb_equipment SET status='กำลังยืม' WHERE equipment_id=$equipment_id");
            $success = "✅ ยืมอุปกรณ์สำเร็จแล้ว! (รวมราคา {$total_price} บาท)";
            echo "<script>setTimeout(()=>{ window.location.href='equipment_borrowing.php'; },1500);</script>";
        } else {
            $error = "❌ ไม่สามารถบันทึกข้อมูลได้: " . htmlspecialchars($stmt->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ยืมอุปกรณ์</title>
<style>
body {
    font-family: 'Prompt', sans-serif;
    background: #f7f7f7;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
input, select, textarea, button {
    width: 100%;
    margin-top: 10px;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
}
button {
    background: #2196F3;
    color: white;
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
<div class="container">
    <h2>ยืมอุปกรณ์: <?= htmlspecialchars($equipment["equipment_name"]); ?></h2>
    <form method="POST">
        <label>ชื่อผู้ยืม *</label>
        <input type="text" name="borrower_name" required>

        <label>ประเภทการยืม *</label>
        <select name="borrow_type" required>
            <option value="">-- เลือกประเภท --</option>
            <option value="รายวัน">รายวัน</option>
            <option value="รายชั่วโมง">รายชั่วโมง</option>
        </select>

        <label>จำนวน (วัน/ชั่วโมง) *</label>
        <input type="number" name="borrow_duration" min="1" required>

        <label>ข้อมูลการติดต่อ *</label>
        <input type="text" name="contact" required placeholder="เบอร์โทร / ไลน์ / อีเมล">

        <label>หมายเหตุ</label>
        <textarea name="note" rows="2"></textarea>

        <button type="submit">ยืนยันการยืม</button>
    </form>

    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
</div>
</body>
</html>
