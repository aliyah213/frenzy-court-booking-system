<?php
session_start();
require_once '../config/database.php';

// Kalau dah login, redirect ikut role
if(isset($_SESSION['user_id'])) {
    if($_SESSION['user_role'] == 'admin') {
        header("Location: ../admin/dashboards.php");
    } else {
        header("Location: ../customer/dashboard.php");
    }
    exit();
}

$database = new Database();
$db = $database->getConnection();

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if(empty($email) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if($user['role'] == 'admin') {
                    header("Location: ../admin/dashboards.php");
                } else {
                    header("Location: ../customer/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Email not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FRENZY Booking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #000;
            color: white;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .auth-section {
            background: #111;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }
        input {
            width: 100%;
            padding: 12px;
            background: #000;
            border: 1px solid #333;
            color: white;
            border-radius: 5px;
        }
        input:focus {
            outline: none;
            border-color: #e74c3c;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #c0392b;
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #e74c3c;
            color: white;
        }
        a {
            color: #e74c3c;
            text-decoration: none;
        }
        p {
            text-align: center;
            margin-top: 20px;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-section">
            <h2>Login to Frenzy</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" name="login" class="btn">LOGIN</button>
                
                <p>
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>