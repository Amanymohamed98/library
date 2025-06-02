<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Librarian') {
    header("Location: index.php");
    exit();
}

// Get students and available books
$students = $conn->query("SELECT * FROM Student");
$books = $conn->query("SELECT * FROM Book WHERE Available = 1");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = (int)$_POST['student_id'];
    $book_id = (int)$_POST['book_id'];
    $loan_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days'));

    try {
        $conn->begin_transaction();

        // Option 1: Don't specify LoanID (let auto-increment handle it)
        $stmt = $conn->prepare("INSERT INTO Loan (StudentID, BookID, LoanDate, DueDate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $student_id, $book_id, $loan_date, $due_date);
        
        // Option 2: If you must specify LoanID, get the next available ID
        // $next_id = $conn->query("SELECT IFNULL(MAX(LoanID), 0) + 1 FROM Loan")->fetch_row()[0];
        // $stmt = $conn->prepare("INSERT INTO Loan (LoanID, StudentID, BookID, LoanDate, DueDate) VALUES (?, ?, ?, ?, ?)");
        // $stmt->bind_param("iiiss", $next_id, $student_id, $book_id, $loan_date, $due_date);

        if (!$stmt->execute()) {
            throw new Exception("Failed to create loan: " . $stmt->error);
        }

        if (!$conn->query("UPDATE Book SET Available = 0 WHERE BookID = $book_id")) {
            throw new Exception("Failed to update book status");
        }

        $conn->commit();
        header("Location: manage_loans.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Loan</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'librarian_header.php'; ?>

    <div class="container">
        <h1>Add New Loan</h1>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="student_id">Student:</label>
                <select name="student_id" id="student_id" required class="form-control">
                    <option value="">Select Student</option>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <option value="<?= $student['StudentID'] ?>">
                            <?= htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="book_id">Book:</label>
                <select name="book_id" id="book_id" required class="form-control">
                    <option value="">Select Book</option>
                    <?php while ($book = $books->fetch_assoc()): ?>
                        <option value="<?= $book['BookID'] ?>">
                            <?= htmlspecialchars($book['Title']) ?> (<?= htmlspecialchars($book['Author']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Loan</button>
        </form>
    </div>
</body>
</html>