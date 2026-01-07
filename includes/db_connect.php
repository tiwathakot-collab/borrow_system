<?php
$host     = "dpg-d5eufv2li9vc73d7hadg-a"; 
$port     = "5432";                         
$dbname   = "borrow_system_db_bl55";       
$user     = "borrow_system_db_bl55_user";  
$password = "รหัสผ่านของคุณ";              

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // ❌ ไม่ต้อง echo อะไร
} catch (PDOException $e) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}
?>
