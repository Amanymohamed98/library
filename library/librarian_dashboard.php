<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require 'db_connect.php';

// Check permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: index.php");
    exit();
}

// Fetch librarian data with error handling
try {
    $stmt = $conn->prepare("
        SELECT l.* 
        FROM Librarian l
        JOIN UserAccount u ON l.LibrarianID = u.LibrarianID
        WHERE u.UserID = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $librarian = $stmt->get_result()->fetch_assoc();
    
    if (!$librarian) {
        throw new Exception("No associated librarian data found");
    }
} catch (Exception $e) {
    die("
        <div style='text-align:center; padding:50px; font-family:Arial;'>
            <h2 style='color:#d32f2f'>System Error</h2>
            <p style='font-size:18px'>".$e->getMessage()."</p>
            <p>Please contact the administrator and provide this information:</p>
            <p><strong>User ID:</strong> ".$_SESSION['user_id']."</p>
            <div style='margin-top:30px'>
                <a href='logout.php' style='
                    padding:10px 20px;
                    background:#f44336;
                    color:white;
                    text-decoration:none;
                    border-radius:5px;
                '>Logout</a>
            </div>
        </div>
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard - Library System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Welcome <?php echo htmlspecialchars($librarian['FirstName'] . ' ' . $librarian['LastName']); ?></h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="manage_books.php">Manage Books</a></li>
                <li><a href="manage_loans.php">Manage Loans</a></li>
                <li><a href="manage_students.php">Manage Students</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <section class="stats-section">
            <h2>Library Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <?php
                    $total_books = $conn->query("SELECT COUNT(*) as count FROM Book")->fetch_assoc()['count'];
                    ?>
                    <h3>Total Books</h3>
                    <p><?php echo $total_books; ?></p>
                </div>
                <div class="stat-card">
                    <?php
                    $active_loans = $conn->query("SELECT COUNT(*) as count FROM Loan WHERE LoanID NOT IN (SELECT LoanID FROM `Return`)")->fetch_assoc()['count'];
                    ?>
                    <h3>Active Loans</h3>
                    <p><?php echo $active_loans; ?></p>
                </div>
                <div class="stat-card">
                    <?php
                    $total_students = $conn->query("SELECT COUNT(*) as count FROM Student")->fetch_assoc()['count'];
                    ?>
                    <h3>Total Students</h3>
                    <p><?php echo $total_students; ?></p>
                </div>
                <div class="stat-card">
                    <?php
                    $total_fines = $conn->query("SELECT SUM(Amount) as total FROM Fine")->fetch_assoc()['total'];
                    ?>
                    <h3>Total Fines</h3>
                    <p><?php echo $total_fines ? $total_fines . ' SAR' : '0 SAR'; ?></p>
                </div>
            </div>
        </section>

        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="add_book.php" class="action-card">
                    <h3>Add New Book</h3>
                </a>
               
                <a href="manage_loans.php" class="action-card">
                    <h3>Record Book Return</h3>
                </a>
                
            </div>
        </section>

        <section class="recent-loans">
            <h2>Recent Loans</h2>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Book Title</th>
                        <th>Loan Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_loans = $conn->query("
                        SELECT l.*, s.FirstName, s.LastName, b.Title 
                        FROM Loan l 
                        JOIN Student s ON l.StudentID = s.StudentID 
                        JOIN Book b ON l.BookID = b.BookID 
                        ORDER BY l.LoanDate DESC LIMIT 5
                    ");
                    
                    if($recent_loans->num_rows > 0) {
                        while($loan = $recent_loans->fetch_assoc()) {
                            $returned = $conn->query("SELECT * FROM `Return` WHERE LoanID = {$loan['LoanID']}")->num_rows > 0;
                            $status = $returned ? "Returned" : (strtotime($loan['DueDate']) < time() ? "Overdue" : "Borrowed");
                            
                            echo "<tr>";
                            echo "<td>".htmlspecialchars($loan['FirstName'])." ".htmlspecialchars($loan['LastName'])."</td>";
                            echo "<td>".htmlspecialchars($loan['Title'])."</td>";
                            echo "<td>".htmlspecialchars($loan['LoanDate'])."</td>";
                            echo "<td>".htmlspecialchars($loan['DueDate'])."</td>";
                            echo "<td>".htmlspecialchars($status)."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No recent loans</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>