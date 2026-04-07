<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$user_count = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'")->fetch(PDO::FETCH_ASSOC)['total'];
$court_count = $db->query("SELECT COUNT(*) as total FROM courts")->fetch(PDO::FETCH_ASSOC)['total'];
$booking_count = $db->query("SELECT COUNT(*) as total FROM bookings")->fetch(PDO::FETCH_ASSOC)['total'];
$pending_count = $db->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'")->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FRENZY</title>
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
        .nav-links a:hover {
            color: #e74c3c;
        }
        .welcome {
            background: #111;
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #e74c3c;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            color: #e74c3c;
            font-weight: bold;
        }
        .stat-label {
            color: #ccc;
            margin-top: 10px;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        .menu-item {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: 0.3s;
        }
        .menu-item:hover {
            border-color: #e74c3c;
            transform: translateY(-5px);
        }
        .menu-icon {
            font-size: 50px;
            margin-bottom: 15px;
            color: #e74c3c;
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
        <div class="welcome">
            <h1>Welcome, Admin!</h1>
            <p>Manage your court booking system here.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_count; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $court_count; ?></div>
                <div class="stat-label">Total Courts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $booking_count; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <div class="admin-menu">
            <a href="courts.php" class="menu-item">
                <div class="menu-icon">🏟️</div>
                <h3>Manage Courts</h3>
                <p>Add, edit, delete courts</p>
            </a>
            <a href="bookings.php" class="menu-item">
                <div class="menu-icon">📅</div>
                <h3>Manage Bookings</h3>
                <p>View and confirm bookings</p>
            </a>
            <a href="users.php" class="menu-item">
                <div class="menu-icon">👥</div>
                <h3>Manage Users</h3>
                <p>View registered users</p>
            </a>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 FRENZY Court Booking System - Admin Panel</p>
        </div>
    </div>
</body>
</html>