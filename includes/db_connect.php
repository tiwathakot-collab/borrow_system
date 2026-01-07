<?php
$host     = "dpg-d5eufv2li9vc73d7hadg-a"; 
$port     = "5432";                         
$dbname   = "borrow_system_db_bl55";       
$user     = "borrow_system_db_bl55_user";  
$password = "ejslbBGv4SpDpIeFdwuMQX51UPUWHMo8";              

// include wrapper
require_once __DIR__ . '/PDOWrapper.php';

// สร้าง instance
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
$conn = new PDOWrapper($dsn, $user, $password);
?>
