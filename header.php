<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="container">
        <nav>
            <div class="logo">
                <img src="image/logo.png" alt="GreenLife"><span>GreenLife</span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>>Home</a></li>
                <li><a href="about.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'class="active"' : ''; ?>>About</a></li>
                <li><a href="services.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'services.php') ? 'class="active"' : ''; ?>>Services</a></li>
                <li><a href="<?php echo isset($_SESSION['user_id']) ? 'book_appointment.php' : 'login.php'; ?>" <?php echo (basename($_SERVER['PHP_SELF']) == 'book_appointment.php') ? 'class="active"' : ''; ?>>Book Now</a></li>
                <li><a href="blog.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'blog.php') ? 'class="active"' : ''; ?>>Blog</a></li>
                <li><a href="contact.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'class="active"' : ''; ?>>Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    $dashboard_url = '';
                    switch($_SESSION['role']) {
                        case 'admin':
                            $dashboard_url = 'admin/dashboard.php';
                            break;
                        case 'therapist':
                            $dashboard_url = 'therapist/dashboard.php';
                            break;
                        case 'client':
                            $dashboard_url = 'client_dashboard.php';
                            break;
                    }
                    ?>
                    <li><a href="logout.php" class="btn btn-primary">Logout</a></li>
                    <li><a href="<?php echo $dashboard_url; ?>" class="btn">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn btn-primary">Login</a></li>
                    <li><a href="register.php" class="btn">Register</a></li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu-btn">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </div>
</header> 

<script>
// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    mobileMenuBtn.addEventListener('click', function() {
        mobileMenuBtn.classList.toggle('active');
        navLinks.classList.toggle('active');
    });
    
    // Close menu when clicking on a link
    const navLinksItems = document.querySelectorAll('.nav-links a');
    navLinksItems.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenuBtn.classList.remove('active');
            navLinks.classList.remove('active');
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!mobileMenuBtn.contains(event.target) && !navLinks.contains(event.target)) {
            mobileMenuBtn.classList.remove('active');
            navLinks.classList.remove('active');
        }
    });
});
</script> 