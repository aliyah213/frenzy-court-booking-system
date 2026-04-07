<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get all courts
$courts_query = "SELECT * FROM courts ORDER BY type, name";
$courts_stmt = $db->prepare($courts_query);
$courts_stmt->execute();
$courts = $courts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected court
$selected_court = isset($_GET['court_id']) ? $_GET['court_id'] : ($courts[0]['id'] ?? 0);

// Get selected date
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get booked slots for selected court and date
$booked_query = "SELECT start_time, end_time, status, user_id 
                 FROM bookings 
                 WHERE court_id = :court_id 
                 AND booking_date = :date 
                 AND status != 'cancelled'
                 ORDER BY start_time";
$booked_stmt = $db->prepare($booked_query);
$booked_stmt->bindParam(':court_id', $selected_court);
$booked_stmt->bindParam(':date', $selected_date);
$booked_stmt->execute();
$booked_slots = $booked_stmt->fetchAll(PDO::FETCH_ASSOC);

// Create booked slots array for easy checking
$booked = [];
foreach($booked_slots as $slot) {
    $booked[$slot['start_time']] = $slot;
}

// Handle block/unblock slot
if(isset($_POST['action'])) {
    if($_POST['action'] == 'block') {
        $court_id = $_POST['court_id'];
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        // Check if slot is already booked
        $check_query = "SELECT id FROM bookings 
                       WHERE court_id = :court_id 
                       AND booking_date = :date 
                       AND start_time = :start_time
                       AND status != 'cancelled'";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':court_id', $court_id);
        $check_stmt->bindParam(':date', $date);
        $check_stmt->bindParam(':start_time', $start_time);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            $error = "Slot already booked!";
        } else {
            // Block slot by creating a booking with status 'blocked'
            $insert_query = "INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, total_price, status) 
                           VALUES (:user_id, :court_id, :date, :start_time, :end_time, 0, 'blocked')";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $insert_stmt->bindParam(':court_id', $court_id);
            $insert_stmt->bindParam(':date', $date);
            $insert_stmt->bindParam(':start_time', $start_time);
            $insert_stmt->bindParam(':end_time', $end_time);
            
            if($insert_stmt->execute()) {
                $success = "Slot blocked successfully!";
                // Refresh page
                header("Location: slots.php?court_id=$court_id&date=$date&success=1");
                exit();
            }
        }
    } elseif($_POST['action'] == 'unblock') {
        $booking_id = $_POST['booking_id'];
        
        $delete_query = "DELETE FROM bookings WHERE id = :id AND status = 'blocked'";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $booking_id);
        
        if($delete_stmt->execute()) {
            header("Location: slots.php?court_id=" . $_POST['court_id'] . "&date=" . $_POST['date'] . "&success=2");
            exit();
        }
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-clock"></i> Manage Time Slots</h4>
                </div>
                <div class="card-body">
                    
                    <?php if(isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                                if($_GET['success'] == 1) echo "Slot blocked successfully!";
                                if($_GET['success'] == 2) echo "Slot unblocked successfully!";
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Form -->
                    <form method="GET" class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Select Court</label>
                            <select name="court_id" class="form-select" onchange="this.form.submit()">
                                <?php foreach($courts as $court): ?>
                                <option value="<?php echo $court['id']; ?>" <?php echo $selected_court == $court['id'] ? 'selected' : ''; ?>>
                                    <?php echo $court['name']; ?> (<?php echo strtoupper($court['type']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Select Date</label>
                            <input type="date" name="date" class="form-control" 
                                   value="<?php echo $selected_date; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   onchange="this.form.submit()">
                        </div>
                    </form>

                    <!-- Time Slots Grid -->
                    <div class="row">
                        <?php
                        $slots = getTimeSlots();
                        foreach($slots as $slot):
                            $is_booked = isset($booked[$slot['start']]);
                            $booking = $is_booked ? $booked[$slot['start']] : null;
                        ?>
                        <div class="col-md-3 mb-3">
                            <div class="card <?php 
                                echo $is_booked ? 
                                    ($booking['status'] == 'blocked' ? 'border-warning' : 'border-danger') : 
                                    'border-success'; 
                            ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $slot['display']; ?></h5>
                                    
                                    <?php if($is_booked): ?>
                                        <?php if($booking['status'] == 'blocked'): ?>
                                            <span class="badge bg-warning">Blocked</span>
                                            <p class="text-muted small mt-2">Blocked by admin</p>
                                            <form method="POST" onsubmit="return confirm('Unblock this slot?')">
                                                <input type="hidden" name="action" value="unblock">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="court_id" value="<?php echo $selected_court; ?>">
                                                <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning w-100">
                                                    <i class="fas fa-unlock"></i> Unblock
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Booked</span>
                                            <p class="text-muted small mt-2">Booking ID: #<?php echo $booking['id']; ?></p>
                                            <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info w-100">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-success">Available</span>
                                        <form method="POST" class="mt-2" onsubmit="return confirm('Block this slot?')">
                                            <input type="hidden" name="action" value="block">
                                            <input type="hidden" name="court_id" value="<?php echo $selected_court; ?>">
                                            <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                                            <input type="hidden" name="start_time" value="<?php echo $slot['start']; ?>:00">
                                            <input type="hidden" name="end_time" value="<?php echo $slot['end']; ?>:00">
                                            <button type="submit" class="btn btn-sm btn-secondary w-100">
                                                <i class="fas fa-lock"></i> Block Slot
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Legend -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Legend:</h5>
                                    <span class="badge bg-success me-2">Available</span>
                                    <span class="badge bg-danger me-2">Booked</span>
                                    <span class="badge bg-warning me-2">Blocked by Admin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>