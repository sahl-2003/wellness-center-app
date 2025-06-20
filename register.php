<?php
session_start();
session_regenerate_id(true);
include('dbconnect.php');
error_reporting(E_ALL);

$error = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm']);
    $user_type = 'client';
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, pwd, email, phone, role) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssss", $username, $hashed_password, $email, $phone, $user_type);
        if ($stmt->execute()) {
            echo '<script type="text/javascript">alert("Registration successful. Now you can login");window.location="login.php"; </script>';
            exit();
        } else {
            $error = "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0"   >
    <title>Register - GreenLife Wellness Center</title>
    <link rel="stylesheet" href="register.css" />
    <link rel="stylesheet" href="main.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>
    <?php include('header.php'); ?>
    <main class="register-page">
        <div class="form-wrapper">
            <div class="auth-container">
                <h1>Create Your Account</h1>
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; text-align:center;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form class="auth-form" action="register.php" method="POST">
                    <div class="form-group">
                        <label for="username">USER Name</label>
                        <input type="text" id="username" name="username" placeholder="USER name" required />
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="example@email.com" required />
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+94 xxxx xxxx" />
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Password" />
                    </div>
                    <div class="form-group">
                        <label for="confirm">Confirm Password</label>
                        <input type="password" id="confirm" name="confirm" required placeholder="Confirm password" />
                    </div>
                    <button type="submit" name="register" class="btn">Register</button>
                </form>
                <div class="auth-options">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
            <div class="info-box">
                <div class="overlay"></div>
                <div class="info-content">
                    <h2>Welcome to GreenLife</h2>
                    <p>Join us for a healthier, happier lifestyle. Sign up to begin your wellness journey.</p>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <img src="image/logo.png" alt="GreenLife " /><span>GreenLife</span>
                    </div>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="book_appointment.php">Book Appointment</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Wellness Street, Colombo</p>
                    <p><i class="fas fa-phone"></i> +94 11 234 5678</p>
                    <p><i class="fas fa-envelope"></i> info@greenlifewellness.lk</p>
                </div>
                <div class="footer-section">
                    <h3>Opening Hours</h3>
                    <p>Monday-Friday: 8am-8pm</p>
                    <p>Saturday: 9am-5pm</p>
                    <p>Sunday: 10am-4pm</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2024 GreenLife Wellness Center. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>