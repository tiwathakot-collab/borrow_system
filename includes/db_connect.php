<?php
// ข้อมูลเชื่อมต่อฐานข้อมูล PostgreSQL
$host     = "dpg-d5eufv2li9vc73d7hadg-a"; // Hostname (Render Database)
$port     = "5432";                         // Port
$dbname   = "borrow_system_db_bl55";       // Database name
$user     = "borrow_system_db_bl55_user";  // Username
$password = "ejslbBGv4SpDpIeFdwuMQX51UPUWHMo8"; // Password

try {
    // สร้าง PDO connection
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);

    // ตั้งค่าให้ PDO แสดง error แบบ exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Debug เชื่อมต่อสำเร็จ (สามารถลบหรือ comment ใน production)
    echo "✅ เชื่อมต่อฐานข้อมูล PostgreSQL ผ่าน PDO สำเร็จ!";
} catch (PDOException $e) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}
?>
