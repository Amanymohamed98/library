<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Librarian') {
    header("Location: index.php");
    exit();
}

// Delete student
if(isset($_GET['delete'])) {
    $student_id = $_GET['delete'];
    $conn->query("DELETE FROM Student WHERE StudentID = $student_id");
    header("Location: manage_students.php");
    exit();
}

// Update student
if(isset($_POST['update_student'])) {
    $student_id = $_POST['student_id'];
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $class = $conn->real_escape_string($_POST['class']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $conn->query("UPDATE Student SET 
                 FirstName = '$firstName',
                 LastName = '$lastName',
                 Class = '$class',
                 Email = '$email'
                 WHERE StudentID = $student_id");
    
    header("Location: manage_students.php");
    exit();
}

// Fetch all students
$students = $conn->query("SELECT * FROM Student");

// Fetch student data for editing if edit_id is set
$edit_student = null;
if(isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_student = $conn->query("SELECT * FROM Student WHERE StudentID = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Library System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9f7fe;
        }
        
        .edit-btn, .delete-btn {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 5px;
            transition: all 0.3s;
        }
        
        .edit-btn {
            background-color: #3498db;
            color: white;
            border: 1px solid #2980b9;
        }
        
        .edit-btn:hover {
            background-color: #2980b9;
        }
        
        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: 1px solid #c0392b;
        }
        
        .delete-btn:hover {
            background-color: #c0392b;
        }
        
        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 3px solid #f1f1f1;
            z-index: 9;
            background: white;
            padding: 25px;
            width: 450px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .form-container {
            max-width: 100%;
        }
        
        .form-container h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-container input[type=text],
        .form-container input[type=email] {
            width: 100%;
            padding: 12px 15px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border 0.3s;
        }
        
        .form-container input[type=text]:focus,
        .form-container input[type=email]:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .form-container .btn {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        
        .form-container .btn:hover {
            background-color: #2980b9;
        }
        
        .form-container .cancel {
            background-color: #95a5a6;
        }
        
        .form-container .cancel:hover {
            background-color: #7f8c8d;
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 8;
        }
        
        .no-students {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include 'librarian_header.php'; ?>

    <div class="container">
        <h1>Manage Students</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Class</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($students->num_rows > 0) {
                    while($student = $students->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$student['StudentID']}</td>";
                        echo "<td>{$student['FirstName']}</td>";
                        echo "<td>{$student['LastName']}</td>";
                        echo "<td>{$student['Class']}</td>";
                        echo "<td>{$student['Email']}</td>";
                        echo "<td>
                                <a href='manage_students.php?edit_id={$student['StudentID']}' class='edit-btn'>Edit</a>
                                <a href='manage_students.php?delete={$student['StudentID']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this student?\")'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='no-students'>No students found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Student Form -->
    <?php if($edit_student): ?>
    <div class="overlay" id="editOverlay" style="display: block;"></div>
    
    <div class="form-popup" id="editForm" style="display: block;">
        <form action="manage_students.php" method="post" class="form-container">
            <h2>Edit Student</h2>
            
            <input type="hidden" name="student_id" value="<?php echo $edit_student['StudentID']; ?>">
            
            <label for="first_name"><b>First Name</b></label>
            <input type="text" placeholder="Enter First Name" name="first_name" 
                   value="<?php echo $edit_student['FirstName']; ?>" required>
            
            <label for="last_name"><b>Last Name</b></label>
            <input type="text" placeholder="Enter Last Name" name="last_name" 
                   value="<?php echo $edit_student['LastName']; ?>" required>
            
            <label for="class"><b>Class</b></label>
            <input type="text" placeholder="Enter Class" name="class" 
                   value="<?php echo $edit_student['Class']; ?>" required>
            
            <label for="email"><b>Email</b></label>
            <input type="email" placeholder="Enter Email" name="email" 
                   value="<?php echo $edit_student['Email']; ?>" required>
            
            <button type="submit" name="update_student" class="btn">Update Student</button>
            <button type="button" class="btn cancel" onclick="closeEditForm()">Cancel</button>
        </form>
    </div>
    <?php endif; ?>

    <script>
        function closeEditForm() {
            window.location.href = "manage_students.php";
        }
        
        // Close form when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById("editOverlay")) {
                closeEditForm();
            }
        }
    </script>
</body>
</html>