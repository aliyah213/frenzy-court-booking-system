<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Add court
if(isset($_POST['add'])) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    
    $insert = "INSERT INTO courts (name, type, price_per_hour) VALUES (:name, :type, :price)";
    $stmt = $db->prepare($insert);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':price', $price);
    $stmt->execute();
}

// Delete court
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->prepare("DELETE FROM courts WHERE id = :id")->execute([':id' => $id]);
}

// Get all courts
$courts = $db->query("SELECT * FROM courts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courts - FRENZY Admin</title>
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
        .card {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }
        input, select {
            width: 100%;
            padding: 10px;
            background: #000;
            border: 1px solid #333;
            color: white;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        .btn-edit {
            padding: 5px 10px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 3px;
        }
        .btn-delete {
            padding: 5px 10px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
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
        <h1>Manage Courts</h1>
        
        <div class="card">
            <h2>Add New Court</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Court Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Court Type</label>
                    <select name="type">
                        <option value="futsal">Futsal</option>
                        <option value="badminton">Badminton</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price per Hour (RM)</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <button type="submit" name="add">Add Court</button>
            </form>
        </div>

        <div class="card">
            <h2>Courts List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courts as $court): ?>
                    <tr>
                        <td><?php echo $court['id']; ?></td>
                        <td><?php echo $court['name']; ?></td>
                        <td><?php echo $court['type']; ?></td>
                        <td>RM <?php echo $court['price_per_hour']; ?></td>
                        <td>
                            <a href="?delete=<?php echo $court['id']; ?>" class="btn-delete" onclick="return confirm('Delete this court?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 FRENZY Court Booking System - Admin Panel</p>
        </div>
    </div>
</body>
</html>