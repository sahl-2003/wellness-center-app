<?php
session_start();
include('dbconnect.php');

// Error message variable
$error = '';

if (isset($_POST["log"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row && password_verify($password, $row['pwd'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];

            // Redirect based on role
            if ($row['role'] === 'admin') {
                header("Location: admin/dashboard.php");
                exit();
            } elseif ($row['role'] === 'therapist') {
                header("Location: therapist/dashboard.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>

<html lang="en">
    <head>
    <meta charset="UTF-8" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0" >
    <title>Login - GreenLife Wellness Center</title>
    <link rel="stylesheet" href="login.css"/>
    <link rel="stylesheet" href="main.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
 </head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="login-wrapper">
            <div class="welcome-container">
                <div class="welcome-content">
                    <h2>Welcome Back!</h2>
                    <p>We're glad to see you again. Login to continue your wellness journey with us.</p>
                </div>
            </div>

            <div class="auth-container">
                <h1>Login to Your Account</h1>

                <?php if (!empty($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; text-align:center;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Email Address" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>

                    <button type="submit" name="log" class="btn">Login</button>
                </form>

                <div class="auth-options">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                    <p><a href="#">Forgot password?</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <img src="image/logo.png" alt="GreenLife">
                        <span>GreenLife</span>
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
