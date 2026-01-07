<?php
$servername = "dpg-d5eufv2li9vc73d7hadg-a"; // Host ของ Render
$username = "borrow_system_db_bl55_user";                   // Username ของ Render
$password = "ejslbBGv4SpDpIeFdwuMQX51UPUWHMo8";                   // Password ของ Render
$dbname = "borrow_system_db";               // ชื่อฐานข้อมูลบน Render

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
