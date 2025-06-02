<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Librarian') {
    header("Location: index.php");
    exit();
}

// متغيرات للتحكم في حالة التعديل
$edit_mode = false;
$current_book = null;
$errors = [];

// جلب جميع التصنيفات
$categories = $conn->query("SELECT * FROM Category");

// حذف كتاب
if(isset($_GET['delete'])) {
    $book_id = $_GET['delete'];
    $conn->query("DELETE FROM Book WHERE BookID = $book_id");
    header("Location: manage_books.php");
    exit();
}

// بدء التعديل - جلب بيانات الكتاب
if(isset($_GET['edit'])) {
    $book_id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM Book WHERE BookID = $book_id");
    if($result->num_rows > 0) {
        $edit_mode = true;
        $current_book = $result->fetch_assoc();
    }
}

// معالجة تحديث الكتاب
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_book'])) {
    $book_id = $_POST['book_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $publisher = trim($_POST['publisher']);
    $year = $_POST['year'];
    $isbn = trim($_POST['isbn']);
    $category_id = $_POST['category_id'];
    $available = isset($_POST['available']) ? 1 : 0;

    // التحقق من صحة البيانات
    if(empty($title)) $errors[] = "Book title is required";
    if(empty($author)) $errors[] = "Author is required";
    if(empty($publisher)) $errors[] = "Publisher is required";
    if(empty($year) || $year < 1900 || $year > date('Y')) $errors[] = "Invalid publication year";
    if(empty($isbn)) $errors[] = "ISBN is required";

    if(empty($errors)) {
        $stmt = $conn->prepare("UPDATE Book SET 
                              Title = ?, 
                              Author = ?, 
                              Publisher = ?, 
                              Year = ?, 
                              ISBN = ?, 
                              CategoryID = ?, 
                              Available = ? 
                              WHERE BookID = ?");
        $stmt->bind_param("sssisisi", $title, $author, $publisher, $year, $isbn, $category_id, $available, $book_id);
        
        if($stmt->execute()) {
            header("Location: manage_books.php");
            exit();
        } else {
            $errors[] = "Error updating book: " . $conn->error;
        }
    }
}

// جلب جميع الكتب
$books = $conn->query("SELECT b.*, c.CategoryName FROM Book b JOIN Category c ON b.CategoryID = c.CategoryID");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Library System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .edit-form {
            background: #f9f9f9;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input {
            margin-right: 10px;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .edit-btn, .delete-btn, .update-btn, .cancel-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        .edit-btn { background-color: #ffc107; }
        .delete-btn { background-color: #dc3545; }
        .update-btn { background-color: #28a745; }
        .cancel-btn { background-color: #6c757d; }
        .add-btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <?php include 'librarian_header.php'; ?>

    <div class="container">
        <h1>Manage Books</h1>
        
        <a href="add_book.php" class="add-btn">Add New Book</a>
        
        <?php if(!empty($errors)): ?>
            <div class="error-message">
                <?php foreach($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if($edit_mode): ?>
            <!-- نموذج التعديل -->
            <div class="edit-form">
                <h2>Edit Book: <?php echo htmlspecialchars($current_book['Title']); ?></h2>
                <form method="POST">
                    <input type="hidden" name="book_id" value="<?php echo $current_book['BookID']; ?>">
                    
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo htmlspecialchars($current_book['Title']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author:</label>
                        <input type="text" id="author" name="author" required
                               value="<?php echo htmlspecialchars($current_book['Author']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="publisher">Publisher:</label>
                        <input type="text" id="publisher" name="publisher" required
                               value="<?php echo htmlspecialchars($current_book['Publisher']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Publication Year:</label>
                        <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>" required
                               value="<?php echo htmlspecialchars($current_book['Year']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="isbn">ISBN:</label>
                        <input type="text" id="isbn" name="isbn" required
                               value="<?php echo htmlspecialchars($current_book['ISBN']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id" required>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['CategoryID']; ?>"
                                    <?php if($cat['CategoryID'] == $current_book['CategoryID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cat['CategoryName']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="available" name="available" value="1"
                            <?php if($current_book['Available'] == 1) echo 'checked'; ?>>
                        <label for="available">Available for loan</label>
                    </div>
                    
                    <button type="submit" name="update_book" class="update-btn">Update Book</button>
                    <a href="manage_books.php" class="cancel-btn">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <!-- جدول عرض الكتب -->
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Publisher</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($books->num_rows > 0): ?>
                    <?php while($book = $books->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['Title']); ?></td>
                            <td><?php echo htmlspecialchars($book['Author']); ?></td>
                            <td><?php echo htmlspecialchars($book['CategoryName']); ?></td>
                            <td><?php echo htmlspecialchars($book['Publisher']); ?></td>
                            <td><?php echo htmlspecialchars($book['Year']); ?></td>
                            <td><?php echo $book['Available'] ? 'Available' : 'Not Available'; ?></td>
                            <td class="actions">
                                <a href="manage_books.php?edit=<?php echo $book['BookID']; ?>" class="edit-btn">Edit</a>
                                <a href="manage_books.php?delete=<?php echo $book['BookID']; ?>" class="delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No books found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>