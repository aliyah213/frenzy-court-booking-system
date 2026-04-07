<?php
class Database {
    private $host = "localhost";
    private $db_name = "frenzy_booking";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }

    public function testConnection() {
        try {
            $this->getConnection();
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function displayError($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> ' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
}

function displaySuccess($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> ' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
}

function displayWarning($message) {
    return '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> ' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

function formatCurrency($amount) {
    return 'RM ' . number_format($amount, 2);
}

function getTimeSlots() {
    $slots = [];
    for($i = 8; $i <= 23; $i++) {
        $start = sprintf("%02d:00", $i);
        $end = sprintf("%02d:00", $i + 1);
        $slots[] = [
            'start' => $start,
            'end' => $end,
            'display' => date('h:i A', strtotime($start)) . ' - ' . date('h:i A', strtotime($end))
        ];
    }
    return $slots;
}
?>