<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
require_once '../includes/send_email.php';

$database = new Database();
$db = $database->getConnection();

$date = $_GET['date'] ?? '';
$court_id = $_GET['court_id'] ?? '';
$time = $_GET['time'] ?? '';
$price = $_GET['price'] ?? 0;

if(empty($date) || empty($court_id) || empty($time)) {
    header("Location: book.php");
    exit();
}

$court_query = "SELECT * FROM courts WHERE id = :id";
$court_stmt = $db->prepare($court_query);
$court_stmt->bindParam(':id', $court_id);
$court_stmt->execute();
$court = $court_stmt->fetch(PDO::FETCH_ASSOC);

$time_slots = [
    '14:00' => '14:00 - 15:00', '15:00' => '15:00 - 16:00',
    '16:00' => '16:00 - 17:00', '17:00' => '17:00 - 18:00',
    '18:00' => '18:00 - 19:00', '19:00' => '19:00 - 20:00',
    '20:00' => '20:00 - 21:00', '21:00' => '21:00 - 22:00',
    '22:00' => '22:00 - 23:00', '23:00' => '23:00 - 00:00'
];

$total_price = $price;
$transaction_fee = 1.00;
$total_payable = $total_price + $transaction_fee;

$message = '';
$payment_success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $start_time = $time;
    $end_time = date('H:i', strtotime($start_time) + 3600);
    
    $insert = "INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, total_price, status, payment_status) 
               VALUES (:user_id, :court_id, :booking_date, :start_time, :end_time, :price, 'pending', 'unpaid')";
    $stmt = $db->prepare($insert);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':court_id', $court_id);
    $stmt->bindParam(':booking_date', $date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':price', $price);
    
    if($stmt->execute()) {
        $court_name = $court['name'];
        $booking_date_display = date('d/m/Y', strtotime($date));
        $booking_time_display = $time_slots[$time] ?? $time;
        
        $notif_msg = "📝 Your booking at $court_name on $booking_date_display at $booking_time_display is pending confirmation.";
        $notif = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
        $notif_stmt = $db->prepare($notif);
        $notif_stmt->bindParam(':user_id', $_SESSION['user_id']);
        $notif_stmt->bindParam(':message', $notif_msg);
        $notif_stmt->execute();
        
        $user_query = "SELECT email, name FROM users WHERE id = :id";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->bindParam(':id', $_SESSION['user_id']);
        $user_stmt->execute();
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        $email_subject = "Booking Pending - FRENZY Court";
        $email_message = "Your booking at $court_name on $booking_date_display at $booking_time_display is pending confirmation. We will notify you once confirmed.";
        sendEmail($user['email'], $user['name'], $email_subject, $email_message);
        
        $message = '<div class="alert success">✅ Booking submitted! Waiting for admin confirmation.</div>';
        $payment_success = true;
    } else {
        $message = '<div class="alert error">❌ Payment failed. Please try again.</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment - FRENZY</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial; }
        body { background: #000; color: #fff; }
        .navbar { background: #111; padding: 15px; border-bottom: 3px solid #e74c3c; }
        .container { max-width: 500px; margin: 0 auto; padding: 20px; }
        .navbar .container { display: flex; justify-content: space-between; }
        .logo { color: white; font-size: 24px; text-decoration: none; }
        .logo span { color: #e74c3c; }
        .payment-card { background: #111; border: 1px solid #333; border-radius: 15px; padding: 30px; margin-top: 20px; }
        h2 { text-align: center; margin-bottom: 20px; color: #e74c3c; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #333; }
        .total { font-weight: bold; color: #e74c3c; }
        .payment-option {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }
        .payment-option input {
            transform: scale(1.2);
            margin: 0;
            cursor: pointer;
        }
        .payment-option label {
            color: white;
            font-size: 16px;
            cursor: pointer;
            flex: 1;
        }
        .btn { 
            width: 100%; 
            padding: 15px; 
            background: #e74c3c; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 18px; 
            cursor: pointer; 
            margin-top: 20px; 
        }
        .btn:hover {
            background: #c0392b;
        }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert.success { background: #27ae60; }
        .alert.error { background: #e74c3c; }
        .back-link { color: #e74c3c; text-decoration: none; display: block; margin-top: 20px; }
        .dashboard-btn { display: block; width: 100%; padding: 15px; background: #27ae60; color: white; text-align: center; text-decoration: none; border-radius: 8px; margin-top: 20px; }
        .footer { background: #111; text-align: center; padding: 20px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">FRENZY <span>Booking</span></a>
            <a href="../auth/logout.php" style="color:white;">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="payment-card">
            <h2>💳 Payment</h2>
            
            <?php if($message) echo $message; ?>
            
            <?php if(!$payment_success): ?>
                <div class="detail-row"><span>Court:</span><span><?php echo $court['name']; ?></span></div>
                <div class="detail-row"><span>Date:</span><span><?php echo date('d/m/Y', strtotime($date)); ?></span></div>
                <div class="detail-row"><span>Time:</span><span><?php echo $time_slots[$time] ?? $time; ?></span></div>
                <div class="detail-row"><span>Duration:</span><span>1 hour</span></div>
                <div class="detail-row"><span>Court Fee:</span><span>RM <?php echo $total_price; ?></span></div>
                <div class="detail-row"><span>Transaction Fee:</span><span>RM <?php echo $transaction_fee; ?></span></div>
                <div class="detail-row total"><span>Total:</span><span>RM <?php echo $total_payable; ?></span></div>
                
                <!-- RADIO BUTTON FPX -->
                <div class="payment-option" onclick="document.getElementById('fpx_radio').click()">
                    <input type="radio" name="payment_method" id="fpx_radio" value="fpx" checked>
                    <label for="fpx_radio">🏦 FPX Online Banking</label>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="payment_method" id="selected_payment" value="fpx">
                    <button type="submit" name="pay" class="btn">Pay Now (RM <?php echo $total_payable; ?>)</button>
                </form>
                <a href="book.php" class="back-link">← Back</a>
            <?php else: ?>
                <a href="dashboard.php" class="dashboard-btn">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2024 FRENZY Court Booking</p>
    </div>
    
    <script>
        document.getElementById('fpx_radio').addEventListener('change', function() {
            document.getElementById('selected_payment').value = this.value;
        });
    </script>
</body>
</html>