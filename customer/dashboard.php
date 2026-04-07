<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get user info
$user = $db->prepare("SELECT * FROM users WHERE id = :id");
$user->bindParam(':id', $_SESSION['user_id']);
$user->execute();
$user = $user->fetch(PDO::FETCH_ASSOC);

// Get all courts
$courts = $db->query("SELECT * FROM courts")->fetchAll(PDO::FETCH_ASSOC);

// Get unread notifications count
$notif = $db->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id AND is_read = FALSE");
$notif->bindParam(':user_id', $_SESSION['user_id']);
$notif->execute();
$notif_count = $notif->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FRENZY</title>
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
        
        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #e74c3c;
        }
        
        .notification-icon {
            position: relative;
        }
        
        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .welcome {
            background: #111;
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
            border-left: 4px solid #e74c3c;
        }
        
        .welcome h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .welcome h1 span {
            color: #e74c3c;
        }
        
        .section-title {
            font-size: 24px;
            margin: 30px 0 20px 0;
            color: #e74c3c;
        }
        
        .courts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        
        .court-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .court-card:hover {
            transform: translateY(-5px);
            border-color: #e74c3c;
        }
        
        .court-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .futsal-image {
            background-image: url('https://images.pexels.com/photos/29803795/pexels-photo-29803795.jpeg');
        }
        
        .badminton-image {
            background-image: url('https://images.pexels.com/photos/8007075/pexels-photo-8007075.jpeg');
        }
        
        .court-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            padding: 15px;
        }
        
        .court-title {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .court-desc {
            color: #ccc;
            font-size: 12px;
        }
        
        .court-details {
            padding: 20px;
        }
        
        .court-type {
            background: #e74c3c;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .court-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .court-price {
            font-size: 20px;
            color: #e74c3c;
            font-weight: bold;
            margin: 10px 0 15px 0;
        }
        
        .btn-book {
            display: block;
            width: 100%;
            padding: 12px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .btn-book:hover {
            background: #c0392b;
        }
        
        .footer {
            background: #111;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
            border-top: 1px solid #333;
        }
        
        @media (max-width: 768px) {
            .courts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">FRENZY <span>Booking</span></a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="notifications.php" class="notification-icon">
                    Notifications
                    <?php if($notif_count > 0): ?>
                        <span class="badge"><?php echo $notif_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h1>Welcome, <span><?php echo htmlspecialchars($user['name']); ?></span>!</h1>
            <p>Book your favorite court now.</p>
        </div>

        <h2 class="section-title">Available Courts</h2>
        
        <div class="courts-grid">
            <?php foreach($courts as $court): 
                $is_futsal = (strtolower($court['type']) == 'futsal');
            ?>
            <div class="court-card">
                <!-- Gambar -->
                <div class="court-image <?php echo $is_futsal ? 'futsal-image' : 'badminton-image'; ?>">
                    <div class="court-overlay">
                        <div class="court-title"><?php echo $is_futsal ? 'FUTSAL ARENA' : 'BADMINTON HALL'; ?></div>
                        <div class="court-desc">Professional indoor court</div>
                    </div>
                </div>
                
                <div class="court-details">
                    <span class="court-type"><?php echo strtoupper($court['type']); ?></span>
                    <div class="court-name"><?php echo htmlspecialchars($court['name']); ?></div>
                    <div class="court-price">RM <?php echo number_format($court['price_per_hour'], 2); ?> / hour</div>
                    <a href="book.php?court_id=<?php echo $court['id']; ?>" class="btn-book">BOOK NOW</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 FRENZY Court Booking System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>