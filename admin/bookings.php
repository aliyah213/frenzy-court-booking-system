<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Update booking status
if(isset($_POST['update'])) {
    $id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $update = "UPDATE bookings SET status = :status WHERE id = :id";
    $stmt = $db->prepare($update);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    // Create notification for user
    $booking = $db->prepare("SELECT user_id, court_id, booking_date, start_time FROM bookings WHERE id = :id");
    $booking->bindParam(':id', $id);
    $booking->execute();
    $book = $booking->fetch(PDO::FETCH_ASSOC);
    
    // Get court name
    $court = $db->prepare("SELECT name FROM courts WHERE id = :id");
    $court->bindParam(':id', $book['court_id']);
    $court->execute();
    $court_name = $court->fetch(PDO::FETCH_ASSOC)['name'];
    
    $notif_msg = "Your booking at $court_name on " . date('d/m/Y', strtotime($book['booking_date'])) . " at " . $book['start_time'] . " has been " . $status . ".";
    $notif = $db->prepare("INSERT INTO notifications (user_id, message, type) VALUES (:user_id, :message, :type)");
    $notif->bindParam(':user_id', $book['user_id']);
    $notif->bindParam(':message', $notif_msg);
    $type = ($status == 'confirmed') ? 'booking_confirmation' : 'booking_cancelled';
    $notif->bindParam(':type', $type);
    $notif->execute();
}

// Get all bookings
$bookings = $db->query("SELECT b.*, u.name as user_name, u.email, c.name as court_name 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.id 
                        JOIN courts c ON b.court_id = c.id 
                        ORDER BY b.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - FRENZY Admin</title>
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
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #f39c12;
            color: #000;
        }
        .status-confirmed {
            background: #27ae60;
            color: #fff;
        }
        select {
            padding: 5px;
            background: #000;
            color: white;
            border: 1px solid #333;
        }
        button {
            padding: 5px 10px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
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
        <h1>Manage Bookings</h1>
        
         <table>
            <thead>
                 <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Court</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                 </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $booking): ?>
                 <tr>
                     <td>#<?php echo $booking['id']; ?></td>
                     <td><?php echo $booking['user_name']; ?><br><small><?php echo $booking['email']; ?></small></td>
                     <td><?php echo $booking['court_name']; ?></td>
                     <td><?php echo $booking['booking_date']; ?></td>
                     <td><?php echo $booking['start_time']; ?></td>
                     <td>RM <?php echo $booking['total_price']; ?></td>
                     <td>
                         <span class="status status-<?php echo $booking['status']; ?>">
                             <?php echo ucfirst($booking['status']); ?>
                         </span>
                     </td>
                     <td>
                         <form method="POST" style="display: flex; gap: 5px;">
                             <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                             <select name="status">
                                 <option value="pending" <?php echo $booking['status']=='pending'?'selected':''; ?>>Pending</option>
                                 <option value="confirmed" <?php echo $booking['status']=='confirmed'?'selected':''; ?>>Confirmed</option>
                             </select>
                             <button type="submit" name="update">Update</button>
                         </form>
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