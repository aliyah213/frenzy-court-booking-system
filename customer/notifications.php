<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Mark all as read
if(isset($_GET['read_all'])) {
    $update = $db->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id");
    $update->bindParam(':user_id', $_SESSION['user_id']);
    $update->execute();
    header("Location: notifications.php");
    exit();
}

// Get all notifications
$notif = $db->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
$notif->bindParam(':user_id', $_SESSION['user_id']);
$notif->execute();
$notifications = $notif->fetchAll(PDO::FETCH_ASSOC);

// Count unread
$unread_count = 0;
foreach($notifications as $n) {
    if(!$n['is_read']) $unread_count++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - FRENZY</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            max-width: 800px;
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
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0 20px 0;
        }
        
        h2 {
            color: #e74c3c;
        }
        
        .btn {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #c0392b;
        }
        
        .notification-item {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .notification-item.unread {
            border-left: 4px solid #e74c3c;
            background: #1a1a1a;
        }
        
        .notification-message {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .notification-time {
            color: #666;
            font-size: 12px;
        }
        
        .empty-message {
            text-align: center;
            color: #666;
            padding: 50px;
            background: #111;
            border-radius: 10px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #e74c3c;
            text-decoration: none;
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
            <a href="dashboard.php" class="logo">FRENZY <span>Booking</span></a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h2>🔔 Notifications <?php echo $unread_count > 0 ? "($unread_count new)" : ''; ?></h2>
            <?php if($unread_count > 0): ?>
                <a href="?read_all=1" class="btn">Mark All as Read</a>
            <?php endif; ?>
        </div>

        <?php if(empty($notifications)): ?>
            <div class="empty-message">
                <p>No notifications yet</p>
            </div>
        <?php else: ?>
            <?php foreach($notifications as $notif): ?>
            <div class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
                <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                <div class="notification-time"><?php echo date('d/m/Y h:i A', strtotime($notif['created_at'])); ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 FRENZY Court Booking System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>