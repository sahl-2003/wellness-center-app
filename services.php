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
    <style>
        .service-details-more {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .service-description.truncated {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* number of lines to show */
            -webkit-box-orient: vertical;
        }
    </style>
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
                <button class="category-tab" data-category="ayurvedic">Ayurveda</button>
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
                            <span><i class="fas fa-tag"></i> From RS <?php echo number_format($service['price'], 2); ?></span>
                        </div>
                        <p class="service-description truncated"><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="service-details-more" id="details-<?php echo $service['service_id']; ?>">
                             <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                        </div>
                        <?php if (!empty($service['features'])): ?>
                        <ul class="service-features">
                            <?php foreach (explode("|", $service['features']) as $feature): ?>
                                <li><?php echo htmlspecialchars($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        <div class="service-price">Starting at RS <?php echo number_format($service['price'], 2); ?></div>
                        <div class="service-actions">
                            <a href="<?php echo isset($_SESSION['user_id']) ? 'book_appointment.php' : 'login.php'; ?>" class="btn btn-primary">Book Now</a>
                            <a href="#" class="read-more" data-service="<?php echo $service['service_id']; ?>">Learn More <i class="fas fa-arrow-right"></i></a>
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
                        <img src="image/logo.png" alt="GreenLife"><span>GreenLife Wellness Center</span>
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
    // Constants for DOM elements
    const categoryTabs = document.querySelectorAll('.category-tab');
    const serviceCards = document.querySelectorAll('.service-card');
    const searchInput = document.getElementById('service-search');
    const searchButton = document.getElementById('search-button');
    const servicesContainer = document.getElementById('services-container');
    const resultsCount = document.getElementById('results-count');

    /**
     * Filters and searches services based on the active category and search term.
     */
    function filterAndDisplayServices() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeCategory = document.querySelector('.category-tab.active').dataset.category;
        let visibleCount = 0;

        serviceCards.forEach(card => {
            const cardCategory = card.dataset.category.toLowerCase();
            const cardSearchData = card.dataset.search.toLowerCase();

            // Category match: checks if the card's category string includes the active category keyword.
            const categoryMatch = (activeCategory === 'all' || cardCategory.includes(activeCategory));
            
            // Search match: checks if the card's search data includes the search term.
            const searchMatch = cardSearchData.includes(searchTerm);

            if (categoryMatch && searchMatch) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        updateResultsCount(visibleCount);
    }

    /**
     * Updates the text indicating how many results are showing.
     * @param {number} count - The number of visible service cards.
     */
    function updateResultsCount(count) {
        const totalServices = serviceCards.length;
        const searchTerm = searchInput.value;

        const noResults = servicesContainer.querySelector('.no-results');
        if (count === 0) {
            resultsCount.textContent = `No services found matching your criteria.`;
            if (!noResults) {
                const noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results';
                noResultsDiv.textContent = 'No services found.';
                servicesContainer.appendChild(noResultsDiv);
            }
        } else {
            if (noResults) {
                noResults.remove();
            }
            if (searchTerm) {
                resultsCount.textContent = `Showing ${count} of ${totalServices} services matching "${searchTerm}".`;
            } else {
                resultsCount.textContent = `Showing ${count} of ${totalServices} services.`;
            }
        }
    }

    // Add event listeners
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            categoryTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            filterAndDisplayServices();
        });
    });

    searchInput.addEventListener('input', filterAndDisplayServices);
    searchButton.addEventListener('click', filterAndDisplayServices);
    
    // Initial filter on page load
    filterAndDisplayServices();

    // Learn More functionality
    document.querySelectorAll('.read-more').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const serviceId = this.getAttribute('data-service');
            const detailsDiv = document.getElementById('details-' + serviceId);
            const card = this.closest('.service-card');
            const description = card.querySelector('.service-description');
            
            if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
                detailsDiv.style.display = 'block';
                description.style.display = 'none';
                this.innerHTML = 'Show Less <i class="fas fa-arrow-up"></i>';
            } else {
                detailsDiv.style.display = 'none';
                description.style.display = 'block';
                this.innerHTML = 'Learn More <i class="fas fa-arrow-right"></i>';
            }
        });
    });

    // Mobile menu toggle
    if (document.querySelector('.mobile-menu-btn')) {
        document.querySelector('.mobile-menu-btn').addEventListener('click', function () {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    }
    </script>
    <script src="auth_check.js"></script>
</body>
</html>
