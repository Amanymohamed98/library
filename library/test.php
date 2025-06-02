<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("sql313.infinityfree.com", "if0_38899302", "Am982001", "if0_38899302_library_system");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
} else {
    echo "اتصال ناجح بقاعدة البيانات!";
}
?>