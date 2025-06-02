<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_system";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// تعيين الترميز لضبط اللغة العربية
$conn->set_charset("utf8");
?>