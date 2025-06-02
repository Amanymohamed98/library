<?php
session_start();
include 'db_connect.php'; // اتصال قاعدة البيانات

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $student_id = $_POST['student_id'];
    $librarian_id = 1; // ثابت مؤقت، لاحقًا يمكن ربطه من تسجيل دخول الموظف
    $loan_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days'));

    $stmt = $conn->prepare("INSERT INTO Loan (BookID, StudentID, LibrarianID, LoanDate, DueDate) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $book_id, $student_id, $librarian_id, $loan_date, $due_date);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?success=1");
    } else {
        echo "Error borrowing book: " . $conn->error;
    }
}
?>
