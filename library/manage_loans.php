<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Librarian') {
    header("Location: index.php");
    exit();
}

// Process book return
if(isset($_GET['return'])) {
    $loan_id = $_GET['return'];
    $return_date = date('Y-m-d');
    
    // Using backticks for reserved table name
    $conn->query("INSERT INTO `Return` (LoanID, ReturnDate) VALUES ($loan_id, '$return_date')");
    header("Location: manage_loans.php");
    exit();
}

// Fetch all loans using backticks for reserved table name
$loans = $conn->query("
    SELECT l.*, s.FirstName, s.LastName, b.Title, r.ReturnDate 
    FROM Loan l 
    JOIN Student s ON l.StudentID = s.StudentID 
    JOIN Book b ON l.BookID = b.BookID 
    LEFT JOIN `Return` r ON l.LoanID = r.LoanID
    ORDER BY l.LoanDate DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Loans - Library System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'librarian_header.php'; ?>

    <div class="container">
        <h1>Manage Loans</h1>
        
        <a href="add_loan.php" class="add-btn">Add New Loan</a>
        
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Book Title</th>
                    <th>Loan Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($loans->num_rows > 0) {
                    while($loan = $loans->fetch_assoc()) {
                        $returned = !is_null($loan['ReturnDate']);
                        $status = $returned ? "Returned" : (strtotime($loan['DueDate']) < time() ? "Overdue" : "Borrowed");
                        
                        echo "<tr>";
                        echo "<td>{$loan['FirstName']} {$loan['LastName']}</td>";
                        echo "<td>{$loan['Title']}</td>";
                        echo "<td>{$loan['LoanDate']}</td>";
                        echo "<td>{$loan['DueDate']}</td>";
                        echo "<td>".($returned ? $loan['ReturnDate'] : '-')."</td>";
                        echo "<td>{$status}</td>";
                        echo "<td>";
                        if(!$returned) {
                            echo "<a href='manage_loans.php?return={$loan['LoanID']}' class='return-btn'>Record Return</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No loans found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>