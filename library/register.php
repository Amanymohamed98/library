<?php
session_start();
require 'db_connect.php';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Get categories and student classes
$categories = $conn->query("SELECT * FROM Category");
$classes = ['10A', '10B', '11A', '11B', '12A', '12B']; // Can be replaced with database list if needed

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    
    // Password validation
    if($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check for existing username
        $stmt = $conn->prepare("SELECT * FROM UserAccount WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Begin database transaction
            $conn->begin_transaction();
            
            try {
                // Create user account
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                if($role == 'Student') {
                    // Register new student
                    $first_name = trim($_POST['first_name']);
                    $last_name = trim($_POST['last_name']);
                    $class = $_POST['class'];
                    
                    // Insert student data
                    $stmt = $conn->prepare("INSERT INTO Student (FirstName, LastName, Class, Email) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $first_name, $last_name, $class, $email);
                    $stmt->execute();
                    $student_id = $conn->insert_id;
                    
                    // Create user account
                    $stmt = $conn->prepare("INSERT INTO UserAccount (Username, Password, Role, StudentID) VALUES (?, ?, 'Student', ?)");
                    $stmt->bind_param("ssi", $username, $hashed_password, $student_id);
                    $stmt->execute();
                    
                    $success = "Student registered successfully! You can now login.";
                } else {
                    // Register librarian (must be pre-registered by admin)
                    $first_name = trim($_POST['first_name']);
                    $last_name = trim($_POST['last_name']);
                    
                    // Check if librarian exists
                    $stmt = $conn->prepare("SELECT * FROM Librarian WHERE Email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if($result->num_rows == 0) {
                        $error = "You must be pre-registered as a librarian by the administrator first";
                        $conn->rollback();
                    } else {
                        $librarian = $result->fetch_assoc();
                        $librarian_id = $librarian['LibrarianID'];
                        
                        // Create user account
                        $stmt = $conn->prepare("INSERT INTO UserAccount (Username, Password, Role, LibrarianID) VALUES (?, ?, 'Librarian', ?)");
                        $stmt->bind_param("ssi", $username, $hashed_password, $librarian_id);
                        $stmt->execute();
                        
                        $success = "Librarian registered successfully! You can now login.";
                    }
                }
                
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Registration - Library System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-container">
        <h1>New User Registration</h1>
        
        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
            <div class="text-center">
                <a href="index.php" class="login-link">Return to login page</a>
            </div>
        <?php else: ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="role">Account Type:</label>
                <select id="role" name="role" required onchange="toggleFields()">
                    <option value="Student">Student</option>
                    <option value="Librarian">Librarian</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            
            <div class="form-group" id="class-field">
                <label for="class">Class:</label>
                <select id="class" name="class">
                    <?php foreach($classes as $class): ?>
                        <option value="<?php echo $class; ?>"><?php echo $class; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="register-btn">Register Account</button>
            <div class="text-center">
                <a href="index.php" class="login-link">Already have an account? Login here</a>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            const classField = document.getElementById('class-field');
            
            if(role === 'Student') {
                classField.style.display = 'block';
            } else {
                classField.style.display = 'none';
            }
        }
        
        // Run function when page loads
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</body>
</html>