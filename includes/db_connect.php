<?php
$servername = "localhost";
$username = "root";
$password = ""; // สำหรับ XAMPP ปกติจะเว้นว่าง
$dbname = "db_equipment_borrow";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$conn->set_charset("utf8");

?>