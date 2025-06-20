<?php
include('dbconnect.php');
$page_title = 'Our Services - GreenLife Wellness Center';
$services = [];
$sql = "SELECT * FROM services WHERE is_active = 1 ORDER BY name";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="services.css">
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Services Hero Section -->
    <section class="page-hero services-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Our Wellness Services</h1>
                <p>Holistic treatments tailored to your unique needs</p>
            </div>
        </div>
    </section>
    <!-- Services Categories -->
    <section class="services-categories">
        <div class="container">
            <div class="category-tabs">
                <button class="category-tab active" data-category="all">All Services</button>
                <button class="category-tab" data-category="ayurveda">Ayurveda</button>
                <button class="category-tab" data-category="yoga">Yoga & Meditation</button>
                <button class="category-tab" data-category="therapy">Therapies</button>
                <button class="category-tab" data-category="nutrition">Nutrition</button>
            </div>
        </div>
    </section>
    <!-- Search Bar -->
    <div class="search-container">
        <div class="search-bar">
            <input type="text" id="service-search" placeholder="Search services...">
            <button id="search-button"><i class="fas fa-search"></i></button>
        </div>
        <div class="search-results-count" id="results-count"></div>
    </div>
    <!-- Main Services Section -->
    <main class="services-main">
        <div class="services-container">
            <div class="service-cards" id="services-container">
                <?php foreach ($services as $service): ?>
                <div class="service-card" data-category="<?php echo htmlspecialchars(strtolower($service['category'] ?? 'all')); ?>" data-search="<?php echo htmlspecialchars($service['name'] . ' ' . $service['description']); ?>">
                    <div class="service-image">
                        <img src="<?php echo !empty($service['image_path']) ? htmlspecialchars($service['image_path']) : 'image/ser.webp'; ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                        <?php if (!empty($service['is_popular'])): ?>
                        <div class="service-badge">Most Popular</div>
                        <?php endif; ?>
                    </div>
                    <div class="service-content">
                        <h2><?php echo htmlspecialchars($service['name']); ?></h2>
                        <div class="service-meta">
                            <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($service['duration']); ?> mins</span>
                            <span><i class="fas fa-rupee-sign"></i> From ₹<?php echo number_format($service['price'], 2); ?></span>
                        </div>
                        <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                        <?php if (!empty($service['features'])): ?>
                        <ul class="service-features">
                            <?php foreach (explode("|", $service['features']) as $feature): ?>
                                <li><?php echo htmlspecialchars($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        <div class="service-price">Starting at ₹<?php echo number_format($service['price'], 2); ?></div>
                        <div class="service-actions">
                            <a href="<?php echo isset($_SESSION['user_id']) ? 'book_appointment.php' : 'login.php'; ?>" class="btn btn-primary">Book Now</a>
                            <a href="<?php echo !empty($service['details_link']) ? htmlspecialchars($service['details_link']) : '#'; ?>" class="btn btn-outline">Learn More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <!-- Consultation Banner -->
    <section class="consultation-banner">
        <div class="consultation-content">
            <h2>Not sure which service is right for you?</h2>
            <p>Schedule a free 15-minute consultation with our wellness advisor</p>
            <a href="contact.php" class="btn btn-large">Get a Consultation</a>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <img src="image/logo.png" alt="GreenLife"><span>GreenLife</span>
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
    <script>
    // Service category filtering
    const categoryTabs = document.querySelectorAll('.category-tab');
    const serviceCards = document.querySelectorAll('.service-card');

    categoryTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Update active tab
            categoryTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Filter services
            const category = tab.dataset.category;
            serviceCards.forEach(card => {
                const cardCategories = card.dataset.category.split(' ');
                if (category === 'all' || cardCategories.includes(category)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
            // Update search results count after category filter
            updateSearchResultsCount();
        });
    });

    // Mobile menu toggle
    if (document.querySelector('.mobile-menu-btn')) {
        document.querySelector('.mobile-menu-btn').addEventListener('click', function () {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    }
    // Search Functionality
    const searchInput = document.getElementById('service-search');
    const searchButton = document.getElementById('search-button');
    const servicesContainer = document.getElementById('services-container');
    const resultsCount = document.getElementById('results-count');

    function filterServices() {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.service-card').forEach(card => {
            const searchData = card.getAttribute('data-search').toLowerCase();
            const isVisible = searchData.includes(searchTerm) &&
                (card.style.display !== 'none' || searchTerm.length === 0);

            if (isVisible) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        updateSearchResultsCount(visibleCount);
    }

    function updateSearchResultsCount(count) {
        const totalServices = document.querySelectorAll('.service-card').length;
        const visibleServices = count !== undefined ? count :
            document.querySelectorAll('.service-card[style="display: flex;"]').length;

        if (searchInput.value.length > 0) {
            resultsCount.textContent = `Showing ${visibleServices} of ${totalServices} services matching "${searchInput.value}"`;
        } else {
            resultsCount.textContent = `Showing all ${totalServices} services`;
        }

        // Show no results message if needed
        const noResults = document.querySelector('.no-results');
        if (visibleServices === 0) {
            if (!noResults) {
                const noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results';
                noResultsDiv.textContent = 'No services found matching your search.';
                servicesContainer.appendChild(noResultsDiv);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }

    // Event listeners for search
    searchInput.addEventListener('input', filterServices);
    searchButton.addEventListener('click', filterServices);

    // Initialize results count
    updateSearchResultsCount();
    </script>
    <script src="auth_check.js"></script>
</body>
</html>
