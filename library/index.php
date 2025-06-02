<?php
session_start();
require 'db_connect.php';

// Fix default librarian account credentials
$check_user = $conn->query("SELECT * FROM UserAccount WHERE Username = 'admin'");
if($check_user->num_rows == 1) {
    $user = $check_user->fetch_assoc();
    if(password_verify('password', $user['Password'])) {
        // If password is 'password' (incorrect), update it
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("UPDATE UserAccount SET Password = '$hashed_password' WHERE Username = 'admin'");
    }
}

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $stmt = $conn->prepare("SELECT * FROM UserAccount WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Special fix: if old password exists
        if($user['Password'] === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') {
            $correct_password = 'password';
        } else {
            $correct_password = $user['Password'];
        }
        
        if(password_verify($password, $correct_password)) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];
            
            if($user['Role'] == 'Student') {
                $_SESSION['student_id'] = $user['StudentID'];
            } else {
                $_SESSION['librarian_id'] = $user['LibrarianID'];
            }
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Username does not exist";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Login to Library System</h1>
        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required value="admin">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required value="admin123">
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <div class="text-center" style="margin-top: 1rem;">
            <a href="register.php" class="login-link">Create new account</a>
        </div>
    </div>
</body>
</html>