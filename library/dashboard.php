<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if($_SESSION['role'] == 'Student') {
    include 'student_dashboard.php';
} else {
    include 'librarian_dashboard.php';
}
?>