<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenLife Wellness Center - Holistic Health in Colombo</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
<?php if (isset($_SESSION['username'])): ?>
    <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
<?php endif; ?>
                <h1>Rediscover Your Natural Balance</h1>
                <p class="hero-subtitle">Holistic wellness therapies in the heart of Colombo</p>
                <div class="hero-buttons">
                    <a href="#" id="bookSessionBtn" class="btn btn-primary">Book a Session</a>
                    <a href="services.php" class="btn btn-outline">Our Services</a>
                </div>
            </div>
        </div>
        <div class="hero-scroll">
            <a href="#features" class="scroll-down">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3>Holistic Therapies</h3>
                    <p>Traditional and modern wellness treatments for complete healing</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>Expert Practitioners</h3>
                    <p>Certified therapists with years of experience</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Natural Approach</h3>
                    <p>Chemical-free treatments using organic ingredients</p>
                </div>
            </div>
        </div>
    </section>
    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <h2>About GreenLife Wellness</h2>
                <p>Founded in 2010, GreenLife Wellness Center has been at the forefront of holistic health in Colombo. Our mission is to provide natural, effective wellness solutions that harmonize mind, body, and spirit.</p>
                <p>We combine ancient healing traditions with modern wellness techniques to create personalized programs for each client. Our team of certified practitioners brings years of experience and genuine care to every session.</p>
                <a href="about.php" class="btn btn-outline">Learn More About Us</a>
            </div>
            <div class="about-image">
                <img src="image/h2.jpg" alt="GreenLife Wellness Center">
                <div class="experience-badge">
                    <span>12+</span>
                    <p>Years of Experience</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Services Preview -->
    <section class="services-preview">
        <div class="container">
            <div class="section-header">
                <h2>Our Signature Services</h2>
                <p>Experience our most popular wellness treatments</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-image">
                        <img src="image/t2.webp" alt="Ayurvedic Therapy">
                    </div>
                    <div class="service-content">
                        <h3>Ayurvedic Therapy</h3>
                        <p>Traditional healing system using herbal treatments and massage techniques.</p>
                        <div class="service-meta">
                            <span><i class="fas fa-clock"></i> 60-90 mins</span>
                            <span><i class="fas fa-rupee-sign"></i> From RS 2,500</span>
                        </div>
                        <a href="services.php#ayurveda" class="btn btn-small">Learn More</a>
                    </div>
                </div>
                <!-- More service cards would go here -->
            </div>
            <div class="text-center">
                <a href="services.php" class="btn btn-primary">View All Services</a>
            </div>
        </div>
    </section>
    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>What Our Clients Say</h2>
                <p>Real experiences from our wellness community</p>
            </div>
            <div class="testimonial-slider-container">
                <div class="testimonial-slider">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-card active">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p>"The Ayurvedic treatments completely transformed my chronic back pain. After just 3 sessions, I felt 10 years younger!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="image/c1.jpg" alt="Sarah J." class="author-img">
                            <div class="author-info">
                                <h4>Sarah J.</h4>
                                <p>Corporate Executive</p>
                            </div>
                        </div>
                    </div>
                    <!-- Testimonial 2 -->
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p>"Panchakarma therapy detoxified my body like nothing else. My digestion improved and I lost 8kg naturally!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="image/c2.jpg" alt="Rajesh K." class="author-img">
                            <div class="author-info">
                                <h4>Rajesh K.</h4>
                                <p>Diabetes Patient</p>
                            </div>
                        </div>
                    </div>
                    <!-- Testimonial 3 -->
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p>"The daily yoga sessions cured my insomnia completely. I now sleep like a baby every night!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="image/c3.webp" alt="David L." class="author-img">
                            <div class="author-info">
                                <h4>David L.</h4>
                                <p>IT Professional</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-dots">
                    <!-- Dots will be added dynamically by JavaScript -->
                </div>
                <!-- Navigation Arrows -->
                <button class="slider-arrow prev-arrow">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-arrow next-arrow">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2>Ready to Begin Your Wellness Journey?</h2>
                    <p>Book your consultation today and take the first step toward balanced health</p>
                </div>
                <div class="cta-action">
                    <a href="book_appointment.php" id="bookNowBtn" class="btns btn-large">Book Now</a>
                    <a href="tel:+94112345678" class="cta-phone">
                        <i class="fas fa-phone-alt"></i> +94 11 234 5678
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog Preview -->
    <section class="blog-preview">
        <div class="container">
            <div class="section-header">
                <h2>Wellness Insights</h2>
                <p>Latest articles on holistic health and living</p>
            </div>
            <div class="blog-grid">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="image/b1.jpg" alt="Ayurvedic Principles">
                        <div class="blog-category">Ayurveda</div>
                    </div>
                    <div class="blog-content">
                        <h3>5 Ayurvedic Principles for Daily Wellness</h3>
                        <p>Discover how ancient Ayurvedic practices can transform your modern lifestyle...</p>
                        <a href="blog.php" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
                <!-- More blog cards would go here -->
            </div>
        </div>
    </section>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <img src="image/logo.png" alt="GreenLife "><span>GreenLife Wellness Center</span>
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
    <script>
    // Book buttons logic
    function handleBookButtonClick() {
        fetch('auth_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.loggedIn) {
                    // Redirect to booking page if logged in
                    window.location.href = 'book_appointment.php';
                } else {
                    // Redirect to login if not logged in
                    window.location.href = 'login.php';
                }
            })
            .catch(() => window.location.href = 'login.php');
    }
    document.getElementById('bookSessionBtn').addEventListener('click', function(e) {
        e.preventDefault();
        handleBookButtonClick();
    });
    document.getElementById('bookNowBtn').addEventListener('click', function(e) {
        e.preventDefault();
        handleBookButtonClick();
    });
    // Mobile menu toggle
    document.querySelector('.mobile-menu-btn').addEventListener('click', function () {
        document.querySelector('.nav-links').classList.toggle('active');
    });
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.querySelector('.testimonial-slider');
        const cards = document.querySelectorAll('.testimonial-card');
        const prevBtn = document.querySelector('.prev-arrow');
        const nextBtn = document.querySelector('.next-arrow');
        const dotsContainer = document.querySelector('.testimonial-dots');
        let currentIndex = 0;
        // Create dots
        cards.forEach((_, index) => {
            const dot = document.createElement('span');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => {
                currentIndex = index;
                updateSlider();
            });
            dotsContainer.appendChild(dot);
        });
        const dots = document.querySelectorAll('.dot');
        function updateSlider() {
            cards.forEach((card, i) => {
                card.classList.remove('active', 'prev', 'next');
                if (i === currentIndex) {
                    card.classList.add('active');
                } else if (i === (currentIndex - 1 + cards.length) % cards.length) {
                    card.classList.add('prev');
                } else if (i === (currentIndex + 1) % cards.length) {
                    card.classList.add('next');
                }
            });
            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }
        prevBtn.addEventListener('click', function () {
            currentIndex = (currentIndex - 1 + cards.length) % cards.length;
            updateSlider();
        });
        nextBtn.addEventListener('click', function () {
            currentIndex = (currentIndex + 1) % cards.length;
            updateSlider();
        });
        // Initialize
        updateSlider();
    });
    </script>
    <script src="auth_check.js"></script>
</body>
</html> 