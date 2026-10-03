<?php
$host = "sql307.infinityfree.com";
$username = "if0_43081047";
$password = "aliabdeen123";
$dbname = "if0_43081047_aliabdeen";

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
?>