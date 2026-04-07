<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get all courts
$courts_query = "SELECT * FROM courts";
$courts_stmt = $db->prepare($courts_query);
$courts_stmt->execute();
$courts = $courts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Time slots
$time_slots = [
    '14:00' => '14:00 - 15:00',
    '15:00' => '15:00 - 16:00',
    '16:00' => '16:00 - 17:00',
    '17:00' => '17:00 - 18:00',
    '18:00' => '18:00 - 19:00',
    '19:00' => '19:00 - 20:00',
    '20:00' => '20:00 - 21:00',
    '21:00' => '21:00 - 22:00',
    '22:00' => '22:00 - 23:00',
    '23:00' => '23:00 - 00:00'
];

// Generate next 5 days
$dates = [];
for($i = 0; $i < 5; $i++) {
    $dates[] = date('Y-m-d', strtotime("+$i days"));
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_date = $_POST['date'] ?? '';
    $selected_court = $_POST['court_id'] ?? '';
    $selected_time = $_POST['time'] ?? '';
    
    if(empty($selected_date) || empty($selected_court) || empty($selected_time)) {
        $error = "Please select date, court and time";
    } else {
        $court_query = "SELECT * FROM courts WHERE id = :id";
        $court_stmt = $db->prepare($court_query);
        $court_stmt->bindParam(':id', $selected_court);
        $court_stmt->execute();
        $court = $court_stmt->fetch(PDO::FETCH_ASSOC);
        
        header("Location: payment.php?date=$selected_date&court_id=$selected_court&time=$selected_time&price=" . $court['price_per_hour']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Court - FRENZY</title>
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
            max-width: 1200px;
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
        
        .booking-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 30px;
        }
        
        h2, h3 {
            color: white;
            margin-bottom: 20px;
        }
        
        h3 {
            margin-top: 30px;
        }
        
        .date-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .date-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 12px 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            color: #ccc;
        }
        
        .date-card:hover {
            border-color: #e74c3c;
        }
        
        .date-card.selected {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }
        
        .date-day {
            font-size: 14px;
            font-weight: bold;
        }
        
        .court-section {
            margin-bottom: 30px;
        }
        
        .court-header {
            background: #1a1a1a;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            color: white;
        }
        
        .time-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .time-slot {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            font-size: 14px;
            color: #ccc;
        }
        
        .time-slot:hover {
            border-color: #e74c3c;
        }
        
        .time-slot.selected {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }
        
        .btn-next {
            width: 100%;
            padding: 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .btn-next:hover {
            background: #c0392b;
        }
        
        .alert {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #e74c3c;
            text-decoration: none;
        }
        
        .footer {
            background: #111;
            color: #ccc;
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
        <div class="booking-card">
            <h2>📅 Select Date & Time</h2>
            
            <?php if($error): ?>
                <div class="alert"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="bookingForm">
                <h3>Select Date</h3>
                <div class="date-grid">
                    <?php foreach($dates as $index => $date): 
                        $day_name = date('D', strtotime($date));
                        $day_date = date('d M', strtotime($date));
                    ?>
                    <div class="date-card" onclick="selectDate('<?php echo $date; ?>', this)">
                        <div class="date-day"><?php echo $day_name; ?></div>
                        <div><?php echo $day_date; ?></div>
                    </div>
                    <?php endforeach; ?>
                    <input type="hidden" name="date" id="selected_date" required>
                </div>

                <h3>Select Court & Time</h3>
                <?php foreach($courts as $court): ?>
                <div class="court-section">
                    <div class="court-header">
                        <span><?php echo $court['name']; ?></span>
                        <span>RM <?php echo $court['price_per_hour']; ?>/hour</span>
                    </div>
                    
                    <div class="time-grid">
                        <?php foreach($time_slots as $time_value => $time_label): ?>
                        <div class="time-slot" 
                             onclick="selectTime('<?php echo $court['id']; ?>', '<?php echo $time_value; ?>', this)">
                            <?php echo $time_label; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <input type="hidden" name="court_id" id="selected_court" required>
                <input type="hidden" name="time" id="selected_time" required>
                
                <button type="submit" class="btn-next">Next → Payment</button>
            </form>
            
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 FRENZY Court Booking System</p>
        </div>
    </div>

    <script>
        function selectDate(date, element) {
            document.querySelectorAll('.date-card').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('selected_date').value = date;
        }
        
        function selectTime(courtId, time, element) {
            document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('selected_court').value = courtId;
            document.getElementById('selected_time').value = time;
        }
    </script>
</body>
</html>