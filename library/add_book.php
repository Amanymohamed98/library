<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Librarian') {
    header("Location: index.php");
    exit();
}

// جلب التصنيفات من قاعدة البيانات
$categories = $conn->query("SELECT * FROM Category");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // تنظيف وفلترة البيانات المدخلة
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $author = trim(filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING));
    $publisher = trim(filter_input(INPUT_POST, 'publisher', FILTER_SANITIZE_STRING));
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, 
              ['options' => ['min_range' => 1900, 'max_range' => date('Y')]]);
    $isbn = trim(filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_STRING));
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $available = isset($_POST['available']) ? 1 : 0; // حالة التوفر

    // التحقق من صحة البيانات
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Book title is required";
    }
    
    if (empty($author)) {
        $errors[] = "Author name is required";
    }
    
    if (empty($publisher)) {
        $errors[] = "Publisher is required";
    }
    
    if (!$year) {
        $errors[] = "Please enter a valid publication year (1900-" . date('Y') . ")";
    }
    
    if (empty($isbn)) {
        $errors[] = "ISBN number is required";
    } elseif (!preg_match('/^[0-9\-]+$/', $isbn)) {
        $errors[] = "ISBN should contain only numbers and hyphens";
    }
    
    if (!$category_id) {
        $errors[] = "Please select a valid category";
    }

    // إذا لم تكن هناك أخطاء، قم بإضافة الكتاب
    if (empty($errors)) {
        try {
            $conn->begin_transaction();
            
            // إعداد استعلام الإدراج مع جميع الحقول
            $stmt = $conn->prepare("INSERT INTO Book 
                                  (Title, Author, Publisher, Year, ISBN, CategoryID, Available) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("sssisii", $title, $author, $publisher, $year, $isbn, $category_id, $available);
            
            if ($stmt->execute()) {
                $conn->commit();
                $_SESSION['success'] = "Book added successfully!";
                header("Location: manage_books.php");
                exit();
            } else {
                throw new Exception("Error adding book: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book - Library System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input {
            margin-right: 10px;
        }
        .submit-btn, .cancel-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .submit-btn {
            background-color: #007bff;
            color: white;
        }
        .submit-btn:hover {
            background-color: #0069d9;
        }
        .cancel-btn {
            background-color: #6c757d;
            color: white;
            margin-left: 10px;
        }
        .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <?php include 'librarian_header.php'; ?>

    <div class="container">
        <h1>Add New Book</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Book Title:*</label>
                <input type="text" id="title" name="title" required 
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="author">Author:*</label>
                <input type="text" id="author" name="author" required
                       value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="publisher">Publisher:*</label>
                <input type="text" id="publisher" name="publisher" required
                       value="<?php echo isset($_POST['publisher']) ? htmlspecialchars($_POST['publisher']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="year">Publication Year:*</label>
                <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>" required
                       value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="isbn">ISBN Number:*</label>
                <input type="text" id="isbn" name="isbn" required
                       value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>">
                <small>Format: 978-1234567890</small>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category:*</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['CategoryID']; ?>"
                            <?php if (isset($_POST['category_id']) && $_POST['category_id'] == $cat['CategoryID']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['CategoryName']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="available" name="available" value="1" checked>
                <label for="available">Book is available for loan</label>
            </div>
            
            <button type="submit" class="submit-btn">Add Book</button>
            <a href="manage_books.php" class="cancel-btn">Cancel</a>
        </form>
    </div>
</body>
</html>