<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get all users
$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - FRENZY Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #000;
            color: #fff;
        }
        .navbar {
            background: #111;
            padding: 15px 0;
            border-bottom: 3px solid #e74c3c;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        .logo span {
            color: #e74c3c;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        h1 {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #111;
        }
        th {
            background: #e74c3c;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #333;
        }
        .badge-admin {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .badge-customer {
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .footer {
            background: #111;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="dashboards.php" class="logo">FRENZY <span>Admin</span></a>
            <div class="nav-links">
                <a href="dashboards.php">Dashboard</a>
                <a href="courts.php">Courts</a>
                <a href="bookings.php">Bookings</a>
                <a href="users.php">Users</a>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h1>Manage Users</h1>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Username</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone'] ?? '-'; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td>
                        <span class="badge-<?php echo $user['role']; ?>">
                            <?php echo $user['role']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 FRENZY Court Booking System - Admin Panel</p>
        </div>
    </div>
</body>
</html>