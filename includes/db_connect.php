<?php
$host     = "dpg-d5eufv2li9vc73d7hadg-a"; // Hostname
$port     = "5432";                         // Port
$dbname   = "borrow_system_db_bl55";       // Database name
$user     = "borrow_system_db_bl55_user";  // Username
$password = "ejslbBGv4SpDpIeFdwuMQX51UPUWHMo8";              // Password

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว");
} else {
    echo "✅ เชื่อมต่อฐานข้อมูล PostgreSQL สำเร็จ!";
}
?>
