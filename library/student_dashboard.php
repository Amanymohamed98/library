<?php
$student_id = $_SESSION['student_id'];

// Query to get student information
$stmt = $conn->prepare("SELECT * FROM Student WHERE StudentID = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Query to get available books
$available_books = $conn->query("SELECT b.*, c.CategoryName FROM Book b JOIN Category c ON b.CategoryID = c.CategoryID");

// Query to get student loans
$loans_stmt = $conn->prepare("SELECT l.*, b.Title, b.Author FROM Loan l JOIN Book b ON l.BookID = b.BookID WHERE l.StudentID = ?");
$loans_stmt->bind_param("i", $student_id);
$loans_stmt->execute();
$loans = $loans_stmt->get_result();

// Query to get fines
$fines_stmt = $conn->prepare("SELECT * FROM Fine WHERE StudentID = ?");
$fines_stmt->bind_param("i", $student_id);
$fines_stmt->execute();
$fines = $fines_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Library System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Welcome <?php echo $student['FirstName'] . ' ' . $student['LastName']; ?></h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <section class="search-section">
            <h2>Search Books</h2>
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search for a book...">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php
                    $categories = $conn->query("SELECT * FROM Category");
                    while($cat = $categories->fetch_assoc()) {
                        echo "<option value='{$cat['CategoryID']}'>{$cat['CategoryName']}</option>";
                    }
                    ?>
                </select>
                <button type="submit">Search</button>
            </form>
        </section>

        <div class="books-grid">
<?php
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT b.*, c.CategoryName FROM Book b JOIN Category c ON b.CategoryID = c.CategoryID WHERE 1";

if(!empty($search)) {
    $query .= " AND (b.Title LIKE '%$search%' OR b.Author LIKE '%$search%')";
}

if(!empty($category)) {
    $query .= " AND b.CategoryID = $category";
}

$filtered_books = $conn->query($query);

if($filtered_books->num_rows > 0) {
    while($book = $filtered_books->fetch_assoc()) {
        echo "<div class='book-card'>";
        echo "<h3>{$book['Title']}</h3>";
        echo "<p><strong>Author:</strong> {$book['Author']}</p>";
        echo "<p><strong>Publisher:</strong> {$book['Publisher']}</p>";
        echo "<p><strong>Year:</strong> {$book['Year']}</p>";
        echo "<p><strong>Category:</strong> {$book['CategoryName']}</p>";

        // Borrow Button Form
        echo "<form method='POST' action='borrow.php'>";
        echo "<input type='hidden' name='book_id' value='{$book['BookID']}'>";
        echo "<input type='hidden' name='student_id' value='{$student_id}'>";
        echo "<button type='submit'>Borrow</button>";
        echo "</form>";

        echo "</div>";
    }
} else {
    echo "<p>No books match your search.</p>";
}
?>
</div>


        <section class="loans-section">
            <h2>My Loans</h2>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Loan Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($loans->num_rows > 0) {
                        while($loan = $loans->fetch_assoc()) {
                            $returned = $conn->query("SELECT * FROM `Return` WHERE LoanID = {$loan['LoanID']}")->num_rows > 0;
                            $status = $returned ? "Returned" : (strtotime($loan['DueDate']) < time() ? "Overdue" : "Borrowed");
                            
                            echo "<tr>";
                            echo "<td>{$loan['Title']}</td>";
                            echo "<td>{$loan['Author']}</td>";
                            echo "<td>{$loan['LoanDate']}</td>";
                            echo "<td>{$loan['DueDate']}</td>";
                            echo "<td>{$status}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No loan records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <section class="fines-section">
            <h2>Fines</h2>
            <table>
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($fines->num_rows > 0) {
                        while($fine = $fines->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$fine['Amount']} OMR</td>";
                            echo "<td>{$fine['Reason']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No fines found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
