<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GreenLife Wellness Center</title>
    <link rel="stylesheet" href="contact.css">
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Contact Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="hero-content">
                <h1>We'd Love to Hear From You</h1>
                <p>Get in touch with our wellness team for any questions or inquiries</p>
            </div>
        </div>
    </section>
    <!-- Contact Main Content -->
    <main class="contact-main">
        <div class="container">
            <div class="contact-container">
                <!-- Contact Information -->
                <div class="contact-info">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Our Location</h3>
                            <p>123 Wellness Street<br>Colombo 05, Sri Lanka</p>
                            <a href="#map" class="map-link">View on map <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Contact Us</h3>
                            <p><a href="tel:+94112345678">+94 11 234 5678</a></p>
                            <p><a href="mailto:info@greenlifewellness.lk">info@greenlifewellness.lk</a></p>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h3>Opening Hours</h3>
                            <p><strong>Monday-Friday:</strong> 8:00 AM - 8:00 PM</p>
                            <p><strong>Saturday:</strong> 9:00 AM - 5:00 PM</p>
                            <p><strong>Sunday:</strong> 10:00 AM - 4:00 PM</p>
                        </div>
                    </div>
                    <div class="social-card">
                        <h3>Connect With Us</h3>
                        <div class="social-links">
                            <a href="#" class="social-link facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link youtube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <!-- Contact Form -->
                <div class="contact-form-container">
                    <div class="form-header">
                        <h2>Send Us a Message</h2>
                        <p>Fill out the form below and we'll get back to you within 24 hours</p>
                    </div>
                    <form class="contact-form">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" placeholder="fullname" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" placeholder="+94 xxxx xxxx" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" aria-placeholder="subject" required>
                                <option value="" disabled selected>Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="booking">Booking Question</option>
                                <option value="feedback">Feedback/Suggestion</option>
                                <option value="partnership">Partnership Opportunity</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                            <p class="form-note">* Required fields</p>
                        </div>
                    </form>
                </div>
            </div>
            <!-- FAQ Section -->
            <section class="faq-section">
                <div class="section-header">
                    <h2>Frequently Asked Questions</h2>
                    <p>Quick answers to common questions</p>
                </div>
                <div class="faq-container">
                    <div class="faq-item">
                        <button class="faq-question">
                            What should I bring to my first appointment?
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>For your first appointment, please bring any medical reports, a list of current medications, and comfortable clothing. If you're coming for Ayurvedic therapy, we recommend bringing loose cotton clothing.</p>
                        </div>
                    </div>
                    <!-- More FAQ items would go here -->
                </div>
            </section>
            <!-- Map Section -->
            <section class="map-section" id="map">
                <h2>Find Us on the Map</h2>
                <div class="map-container" id="mapContainer"></div>
                <div class="map-actions">
                    <a href="https://maps.google.com" class="btn btn-outline" target="_blank">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </section>
        </div>
    </main>
    <!-- Emergency Contact Banner -->
    <section class="emergency-banner">
        <div class="container">
            <div class="banner-content">
                <div class="emergency-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="emergency-info">
                    <h3>Medical Emergency?</h3>
                    <p>For immediate medical assistance, please call our emergency line: <strong>+94 11 234 9999</strong></p>
                </div>
            </div>
        </div>
    </section>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <img src="image/logo.png" alt="GreenLife "><span>GreenLife</span>
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
    <!-- JavaScript Libraries -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
        // Initialize map
        const map = L.map('mapContainer').setView([6.9271, 79.8612], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        L.marker([6.9271, 79.8612]).addTo(map)
            .bindPopup('GreenLife Wellness Center<br>123 Wellness Street, Colombo')
            .openPopup();
    </script>
</body>
</html> 