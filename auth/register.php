<?php
session_start();
require_once '../config/database.php';

if(isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Senarai email yang akan jadi admin
$admin_emails = ['admin@frenzy.com', 'aisyah@frenzy.com'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form - GUNA 'fullname' (ikut name dalam form)
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if(empty($fullname) || empty($email) || empty($phone) || empty($username) || empty($password)) {
        $error = "All fields are required!";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email or username exists
        $check_query = "SELECT id FROM users WHERE email = :email OR username = :username";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            $error = "Email or username already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Check if email is in admin list
            if(in_array($email, $admin_emails)) {
                $role = 'admin';
            } else {
                $role = 'customer';
            }
            
            // Insert user - GUNA $fullname UNTUK COLUMN 'name'
            $query = "INSERT INTO users (username, email, password, name, phone, role) 
                      VALUES (:username, :email, :password, :name, :phone, :role)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':name', $fullname);  // <-- PENTING: Guna $fullname untuk 'name'
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':role', $role);
            
            if($stmt->execute()) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Registration failed!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - FRENZY</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial; }
        body { background: #000; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: #111; padding: 40px; border-radius: 10px; border: 1px solid #333; width: 400px; }
        h2 { text-align: center; color: #e74c3c; margin-bottom: 30px; }
        input { width: 100%; padding: 12px; margin: 8px 0; background: #000; border: 1px solid #333; color: white; border-radius: 5px; }
        button { width: 100%; padding: 12px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .error { background: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 10px; text-align: center; }
        .success { background: #27ae60; padding: 10px; border-radius: 5px; margin-bottom: 10px; text-align: center; }
        a { color: #e74c3c; text-decoration: none; }
        p { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <p><a href="login.php">Login here</a></p>
        <?php endif; ?>
        
        <?php if(!$success): ?>
        <form method="POST">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have account? <a href="login.php">Login</a></p>
        <?php endif; ?>
    </div>
</body>
</html>