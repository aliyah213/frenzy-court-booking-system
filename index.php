<?php
session_start();

try {
    $db = new PDO("mysql:host=localhost;dbname=frenzy_booking", "root", "");
} catch(PDOException $e) {
    // Abaikan dulu
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRENZY Court Booking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #000000;
            color: #ffffff;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Navbar */
        .navbar {
            background: #111111;
            padding: 1rem 0;
            border-bottom: 2px solid #e74c3c;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
        }

        .logo span {
            color: #e74c3c;
        }

        /* Hero Section */
        .hero {
            background: #111111;
            padding: 3rem 2rem;
            border-radius: 20px;
            margin: 2rem 0 1rem 0;
            text-align: center;
            border: 1px solid #333;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
        }

        .hero p {
            font-size: 1.2rem;
            color: #ccc;
        }

        /* Auth Section */
        .auth-section {
            max-width: 500px;
            margin: 0 auto 3rem auto;
            background: #111;
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid #333;
        }

        .auth-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #333;
            padding-bottom: 1rem;
        }

        .tab-btn {
            flex: 1;
            padding: 0.8rem;
            border: none;
            background: none;
            color: white;
            font-size: 1rem;
            cursor: pointer;
        }

        .tab-btn.active {
            color: #e74c3c;
            border-bottom: 2px solid #e74c3c;
        }

        .auth-form {
            display: none;
        }

        .auth-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ccc;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            background: #000;
            border: 1px solid #333;
            color: white;
            border-radius: 5px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #e74c3c;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
        }

        .btn:hover {
            background: #c0392b;
        }

        /* Courts Grid */
        .courts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 50px 0;
        }

        .court-card {
            background: #111;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #333;
        }

        .court-icon {
            height: 200px;
            background: linear-gradient(135deg, #222, #000);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            color: #e74c3c;
        }

        .court-content {
            padding: 30px;
        }

        .court-title {
            color: white;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .court-features {
            list-style: none;
            color: #ccc;
            margin-bottom: 20px;
        }

        .court-features li {
            margin-bottom: 10px;
        }

        .court-tags {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tag {
            background: #e74c3c;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .court-price {
            font-size: 24px;
            color: white;
            margin-bottom: 20px;
        }

        .court-price strong {
            color: #e74c3c;
            font-size: 32px;
        }

        .court-btn {
            background: #e74c3c;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .court-btn:hover {
            background: #c0392b;
        }

        /* Features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 50px 0;
        }

        .feature-box {
            background: #111;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #333;
        }

        .feature-icon {
            font-size: 40px;
            color: #e74c3c;
            margin-bottom: 15px;
        }

        .feature-box h3 {
            color: white;
            margin-bottom: 10px;
        }

        .feature-box p {
            color: #ccc;
        }

        /* Footer */
        .footer {
            background: #111;
            color: #ccc;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
            border-top: 1px solid #333;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .courts-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">FRENZY <span>Booking</span></a>
        </div>
    </nav>

    <main>
        <div class="container">
            <!-- Hero Section - TANPA BUTTON GET STARTED -->
            <section class="hero">
                <h1>FRENZY Court Booking</h1>
                <p>Book your favorite futsal and badminton courts easily, anytime, anywhere!</p>
            </section>

            <!-- Auth Section -->
            <div class="auth-section">
                <div class="auth-tabs">
                    <button class="tab-btn active" onclick="showLogin()">Login</button>
                    <button class="tab-btn" onclick="showRegister()">Register</button>
                </div>

                <!-- Login Form -->
                <div id="loginForm" class="auth-form active">
                    <form action="auth/login.php" method="POST">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" name="login" class="btn">Login</button>
                    </form>
                </div>

                <!-- Register Form -->
                <div id="registerForm" class="auth-form">
                    <form action="auth/register.php" method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" placeholder="Enter your phone number" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" placeholder="Choose a username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Create password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                        <button type="submit" name="register" class="btn">Register</button>
                    </form>
                </div>
            </div>

            <!-- Courts Section -->
            <div class="courts-grid">
                <!-- Futsal Court -->
                <div class="court-card">
                    <div class="court-icon">⚽</div>
                    <div class="court-content">
                        <h2 class="court-title">FUTSAL COURT</h2>
                        <ul class="court-features">
                            <li>✓ Futsal Sports Court</li>
                            <li>✓ Available for rental hourly at super rates!</li>
                        </ul>
                        <div class="court-tags">
                        </div>
                        <div class="court-price">
                            <strong>RM 80</strong> /hour
                        </div>
                        <a href="auth/login.php" class="court-btn">Login to Book</a>
                    </div>
                </div>

                <!-- Badminton Court -->
                <div class="court-card">
                    <div class="court-icon">🏸</div>
                    <div class="court-content">
                        <h2 class="court-title">BADMINTON COURT</h2>
                        <ul class="court-features">
                            <li>✓ Badminton court available</li>
                            <li>✓ For hourly rental at super rates!</li>
                        </ul>
                        <div class="court-tags">
                        </div>
                        <div class="court-price">
                            <strong>RM 40</strong> /hour
                        </div>
                        <a href="auth/login.php" class="court-btn">Login to Book</a>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon">⏰</div>
                    <h3>24/7 Online Booking</h3>
                    <p>Book anytime, anywhere</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">✓</div>
                    <h3>Real-time Availability</h3>
                    <p>Check court availability instantly</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">⚡</div>
                    <h3>Instant Confirmation</h3>
                    <p>Get confirmation immediately</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">🛡️</div>
                    <h3>Secure Booking</h3>
                    <p>Safe and reliable system</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 FRENZY Court Booking System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function showLogin() {
            document.getElementById('loginForm').classList.add('active');
            document.getElementById('registerForm').classList.remove('active');
            document.querySelectorAll('.tab-btn')[0].classList.add('active');
            document.querySelectorAll('.tab-btn')[1].classList.remove('active');
        }

        function showRegister() {
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('registerForm').classList.add('active');
            document.querySelectorAll('.tab-btn')[0].classList.remove('active');
            document.querySelectorAll('.tab-btn')[1].classList.add('active');
        }
    </script>
</body>
</html>