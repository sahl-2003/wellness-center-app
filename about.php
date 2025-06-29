<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) session_start();
include('dbconnect.php');

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// Fetch all therapists
$therapists = [];
$sql = "SELECT t.therapist_id, u.username, u.email, t.specialization, t.qualifications, t.bio, t.profile_picture FROM therapists t JOIN users u ON t.user_id = u.user_id WHERE u.role = 'therapist'";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $therapists[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - GreenLife Wellness Center</title>
    <link rel="stylesheet" href="about.css">
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="about-hero">
        <div class="container">
            <h1>Our Story & Philosophy</h1>
            <p>Discover the journey of GreenLife Wellness Center and our commitment to holistic healing</p>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Your Trusted Partner in Holistic Wellness</h2>
                    <p>Founded in 2010, GreenLife Wellness Center has been at the forefront of providing comprehensive holistic health solutions to the Colombo community. What began as a small clinic with two therapists has now grown into a premier wellness destination with over 20 certified practitioners.</p>
                    <p>Our center combines ancient healing traditions with modern therapeutic techniques to deliver personalized care that addresses mind, body, and spirit. We believe in treating the whole person, not just symptoms, to promote lasting wellbeing.</p>
                    <div class="about-values">
                        <h3>Our Core Values</h3>
                        <div class="value-cards">
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h3>Compassion</h3>
                                <p>We treat every client with empathy, respect, and genuine care for their wellbeing.</p>
                            </div>
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-leaf"></i>
                                </div>
                                <h3>Natural Healing</h3>
                                <p>We prioritize natural, non-invasive methods that work with the body's innate wisdom.</p>
                            </div>
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <h3>Excellence</h3>
                                <p>We maintain the highest standards of care through continuous learning and improvement.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="image/f1.jpeg" alt="GreenLife Wellness Center Facility">
                </div>
            </div>
        </div>
    </section>
    <section class="about-mission">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="image/m1.png" alt="Our Mission">
                </div>
                <div class="about-text">
                    <h2>Our Mission & Vision</h2>
                    <p><strong>Mission:</strong> To empower individuals to take control of their health through education, personalized care, and natural healing modalities that honor the body's innate wisdom.</p>
                    <p><strong>Vision:</strong> To create a healthier community by making holistic wellness accessible to all, while maintaining the highest standards of care and professionalism.</p>
                    <p>We envision a world where preventive care and holistic approaches are the foundation of health, reducing reliance on medications and invasive procedures.</p>
                    <a href="services.php" class="btn btn-primary" style="margin-top: 20px;">Explore Our Services</a>
                </div>
            </div>
        </div>
    </section>
    <section class="wellness-practices">
        <div class="container">
            <div class="section-header">
                <h2>Our Holistic Wellness Practices</h2>
                <p>Rooted in tradition, powered by science</p>
            </div>
            <div class="practices-grid">
                <!-- Ayurveda -->
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Ayurveda</h3>
                    <p>5,000-year-old healing system balancing body, mind, and spirit through personalized diets, herbal remedies, and detox therapies like <strong>Panchakarma</strong>.</p>
                    <ul class="practice-benefits">
                        <li><i class="fas fa-check"></i> Dosha-specific treatments</li>
                        <li><i class="fas fa-check"></i> Natural detoxification</li>
                        <li><i class="fas fa-check"></i> Long-term wellness</li>
                    </ul>
                </div>
                <!-- Yoga & Meditation -->
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3>Yoga & Meditation</h3>
                    <p>Ancient practices combining physical postures (<strong>asanas</strong>), breath control (<strong>pranayama</strong>), and mindfulness for holistic health.</p>
                    <ul class="practice-benefits">
                        <li><i class="fas fa-check"></i> Stress reduction</li>
                        <li><i class="fas fa-check"></i> Improved flexibility</li>
                        <li><i class="fas fa-check"></i> Mental clarity</li>
                    </ul>
                </div>
                <!-- Integrative Therapies -->
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3>Integrative Therapies</h3>
                    <p>Blending Eastern and Western modalities like acupuncture, cupping, and physiotherapy for targeted healing.</p>
                    <ul class="practice-benefits">
                        <li><i class="fas fa-check"></i> Pain management</li>
                        <li><i class="fas fa-check"></i> Enhanced circulation</li>
                        <li><i class="fas fa-check"></i> Faster recovery</li>
                    </ul>
                </div>
                <!-- Nutrition & Detox -->
                <div class="practice-card">
                    <div class="practice-icon">
                        <i class="fas fa-apple-alt"></i>
                    </div>
                    <h3>Nutrition & Detox</h3>
                    <p>Science-backed dietary plans and cleansing protocols to restore gut health and vitality.</p>
                    <ul class="practice-benefits">
                        <li><i class="fas fa-check"></i> Personalized meal plans</li>
                        <li><i class="fas fa-check"></i> Herbal supplements</li>
                        <li><i class="fas fa-check"></i> Sustainable weight management</li>
                    </ul>
                </div>
            </div>
            <!-- Wellness Philosophy -->
            <div class="wellness-philosophy">
                <h3>Our Philosophy</h3>
                <p>At GreenLife, we believe in <strong>preventive care</strong> over reactive treatment. Our practices:</p>
                <div class="philosophy-points">
                    <div class="point">
                        <i class="fas fa-seedling"></i>
                        <span>Use 100% natural ingredients</span>
                    </div>
                    <div class="point">
                        <i class="fas fa-user-md"></i>
                        <span>Are delivered by certified experts</span>
                    </div>
                    <div class="point">
                        <i class="fas fa-heartbeat"></i>
                        <span>Address root causes, not just symptoms</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Meet Our Expert Team</h2>
            <p class="section-subtitle">
                Our team of certified therapists and wellness experts are dedicated to helping you achieve optimal health
            </p>
            <div class="team-slider-container">
                <button class="slider-arrow prev-arrow"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-arrow next-arrow"><i class="fas fa-chevron-right"></i></button>
                <div class="team-slider-track">
                    <?php foreach ($therapists as $therapist): ?>
                        <div class="team-slide">
                            <img src="<?php echo htmlspecialchars($therapist['profile_picture'] ?: 'assets/images/default-profile.jpg'); ?>" alt="<?php echo htmlspecialchars($therapist['username']); ?>" class="team-photo">
                            <div class="team-info">
                                <h3><?php echo htmlspecialchars($therapist['username']); ?></h3>
                                <?php if (!empty($therapist['specialization'])): ?>
                                    <p class="team-specialization"><?php echo htmlspecialchars($therapist['specialization']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($therapist['qualifications'])): ?>
                                    <div class="team-qualifications">
                                        <strong>Qualifications:</strong> <?php echo nl2br(htmlspecialchars($therapist['qualifications'])); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="social-links">
                                    <?php if (!empty($therapist['facebook'])): ?><a href="<?php echo htmlspecialchars($therapist['facebook']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                                    <?php if (!empty($therapist['instagram'])): ?><a href="<?php echo htmlspecialchars($therapist['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
                                    <?php if (!empty($therapist['linkedin'])): ?><a href="<?php echo htmlspecialchars($therapist['linkedin']); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                                    <?php if (!empty($therapist['twitter'])): ?><a href="<?php echo htmlspecialchars($therapist['twitter']); ?>" target="_blank"><i class="fab fa-twitter"></i></a><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <section class="section" style="background-color: var(--light-color);">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Why Choose GreenLife?</h2>
                    <ul style="list-style-type: none;">
                        <li style="margin-bottom: 15px; position: relative; padding-left: 30px;">
                            <i class="fas fa-check" style="color: var(--primary-color); position: absolute; left: 0; top: 5px;"></i>
                            <strong>Holistic Approach:</strong> We address root causes, not just symptoms
                        </li>
                        <li style="margin-bottom: 15px; position: relative; padding-left: 30px;">
                            <i class="fas fa-check" style="color: var(--primary-color); position: absolute; left: 0; top: 5px;"></i>
                            <strong>Certified Practitioners:</strong> All therapists are fully qualified and experienced
                        </li>
                        <li style="margin-bottom: 15px; position: relative; padding-left: 30px;">
                            <i class="fas fa-check" style="color: var(--primary-color); position: absolute; left: 0; top: 5px;"></i>
                            <strong>Personalized Care:</strong> Treatment plans tailored to your unique needs
                        </li>
                        <li style="margin-bottom: 15px; position: relative; padding-left: 30px;">
                            <i class="fas fa-check" style="color: var(--primary-color); position: absolute; left: 0; top: 5px;"></i>
                            <strong>Natural Methods:</strong> Minimally invasive techniques with no side effects
                        </li>
                        <li style="margin-bottom: 15px; position: relative; padding-left: 30px;">
                            <i class="fas fa-check" style="color: var(--primary-color); position: absolute; left: 0; top: 5px;"></i>
                            <strong>Proven Results:</strong> Thousands of satisfied clients since 2010
                        </li>
                    </ul>
                
                </div>
                <div class="about-image">
                    <img src="image/w1.jpg" alt="Why Choose GreenLife">
                </div>
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
        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('.team-slide');
            const prevArrow = document.querySelector('.prev-arrow');
            const nextArrow = document.querySelector('.next-arrow');
            const sliderContainer = document.querySelector('.team-slider-container');
            let currentIndex = 0;
            const totalSlides = slides.length;
            // Create dots container
            const dotsContainer = document.createElement('div');
            dotsContainer.className = 'slider-dots';
            sliderContainer.appendChild(dotsContainer);
            // Create dots
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('span');
                dot.className = 'dot';
                dot.dataset.index = i;
                dotsContainer.appendChild(dot);
            }
            const dots = document.querySelectorAll('.dot');
            function initSlider() {
                slides.forEach((slide, index) => {
                    slide.classList.remove('active', 'prev', 'next');
                    slide.style.transform = '';
                    slide.style.filter = '';
                    slide.style.zIndex = '';
                    slide.style.opacity = '';
                    const position = (index - currentIndex + totalSlides) % totalSlides;
                    if (index === currentIndex) {
                        slide.classList.add('active');
                        slide.style.zIndex = '10';
                        slide.style.transform = 'translateX(0) scale(1)';
                        slide.style.filter = 'none';
                        slide.style.opacity = '1';
                    } else if (position === totalSlides - 1 || index === currentIndex - 1) {
                        slide.classList.add('prev');
                        slide.style.transform = 'translateX(-80%) scale(0.85)';
                        slide.style.filter = 'blur(3px)';
                        slide.style.zIndex = '5';
                        slide.style.opacity = '0.9';
                    } else if (position === 1 || index === currentIndex + 1) {
                        slide.classList.add('next');
                        slide.style.transform = 'translateX(80%) scale(0.85)';
                        slide.style.filter = 'blur(3px)';
                        slide.style.zIndex = '5';
                        slide.style.opacity = '0.9';
                    } else {
                        slide.style.transform = 'translateX(0) scale(0.7)';
                        slide.style.filter = 'blur(6px)';
                        slide.style.zIndex = '1';
                        slide.style.opacity = '0.6';
                    }
                });
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }
            function goToSlide(index) {
                currentIndex = index;
                initSlider();
            }
            function goToPrev() {
                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                initSlider();
            }
            function goToNext() {
                currentIndex = (currentIndex + 1) % totalSlides;
                initSlider();
            }
            prevArrow.addEventListener('click', goToPrev);
            nextArrow.addEventListener('click', goToNext);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') {
                    goToPrev();
                } else if (e.key === 'ArrowRight') {
                    goToNext();
                }
            });
            initSlider();
        });
    </script>
    <script src="auth_check.js"></script>
</body>
</html> 