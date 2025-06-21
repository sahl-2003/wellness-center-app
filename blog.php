<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Blog - GreenLife Wellness Center</title>
    <link rel="stylesheet" href="blog.css">
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Blog Hero Section -->
    <section class="blog-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Wellness Blog</h1>
                <p>Evidence-based articles on holistic health, nutrition, and mindful living</p>
                <div class="blog-search">
                    <input type="text" placeholder="Search articles..." id="blogSearchInput">
                    <button id="blogSearchBtn"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog Main Content -->
    <main class="blog-main">
        <div class="container">
            <div class="blog-container">
                <!-- Featured Post -->
                <section class="featured-post">
                    <div class="featured-image">
                        <img src="image/fb.jpeg" alt="Featured Blog Post">
                        <div class="featured-badge">Featured</div>
                    </div>
                    <div class="featured-content">
                        <div class="post-meta">
                            <span class="category">Ayurveda</span>
                            <span class="date">June 15, 2024</span>
                            <span class="read-time">5 min read</span>
                        </div>
                        <h2>The Ancient Wisdom of Ayurveda in Modern Life</h2>
                        <p class="excerpt">Discover how these 5,000-year-old healing principles can transform your daily routine and bring balance to your hectic modern lifestyle...</p>
                        <a href="#" class="read-more" data-article="0">Read Full Article <i class="fas fa-arrow-right"></i></a>
                        <div class="full-article" id="full-article-0" style="display:none;margin-top:12px;">
                            <p><strong>Ayurveda</strong> is an ancient system of medicine from India, focusing on balance in body, mind, and spirit. In modern life, you can apply its wisdom by:<br>
                            <strong>1. Daily Routines (Dinacharya):</strong> Start your day with self-care rituals like tongue scraping, oil massage, and meditation.<br>
                            <strong>2. Eating for Your Dosha:</strong> Choose foods that balance your unique constitution.<br>
                            <strong>3. Mindful Living:</strong> Practice gratitude, mindful eating, and regular movement.<br>
                            <strong>4. Herbal Support:</strong> Use herbs like ashwagandha and turmeric for resilience.<br>
                            <strong>5. Restorative Sleep:</strong> Prioritize sleep and relaxation for true wellness.<br>
                            Embracing Ayurveda can help you thrive in a fast-paced world!</p>
                        </div>
                    </div>
                </section>
                <!-- Blog Posts Grid -->
                <section class="blog-posts">
                    <h2>Latest Articles</h2>
                    <div class="posts-grid" id="postsGrid">
                        <!-- Blog Post 1 -->
                        <article class="blog-post">
                            <div class="post-image">
                                <img src="image/b1.jpg" alt="Ayurvedic Principles">
                                <div class="blog-category">Ayurveda</div>
                            </div>
                            <div class="post-content">
                                <div class="post-meta">
                                    <span class="date">June 10, 2024</span>
                                    <span class="read-time">4 min read</span>
                                </div>
                                <h3>5 Ayurvedic Principles for Daily Wellness</h3>
                                <p>Discover how ancient Ayurvedic practices can transform your modern lifestyle...</p>
                                <a href="#" class="read-more" data-article="1">Continue Reading <i class="fas fa-arrow-right"></i></a>
                                <div class="full-article" id="full-article-1" style="display:none;margin-top:12px;">
                                    <p><strong>1. Wake Up Early:</strong> Start your day before sunrise to align with nature's rhythms.<br>
                                    <strong>2. Oil Pulling:</strong> Cleanse your mouth with oil for oral and overall health.<br>
                                    <strong>3. Mindful Eating:</strong> Eat slowly, savoring each bite, and avoid distractions.<br>
                                    <strong>4. Daily Movement:</strong> Incorporate yoga or walking into your routine.<br>
                                    <strong>5. Evening Wind Down:</strong> Unplug and relax before bed for restorative sleep.<br>
                                    Embrace these principles for a balanced, vibrant life!</p>
                                </div>
                            </div>
                        </article>
                        <!-- Blog Post 2 -->
                        <article class="blog-post">
                            <div class="post-image">
                                <img src="image/b3.jpg" alt="Post Image">
                                <div class="post-category">Mindfulness</div>
                            </div>
                            <div class="post-content">
                                <div class="post-meta">
                                    <span class="date">June 5, 2024</span>
                                    <span class="read-time">6 min read</span>
                                </div>
                                <h3>5-Minute Meditation Techniques for Busy People</h3>
                                <p class="excerpt">Quick and effective mindfulness practices you can do anywhere, even during your busiest days...</p>
                                <a href="#" class="read-more" data-article="2">Continue Reading <i class="fas fa-arrow-right"></i></a>
                                <div class="full-article" id="full-article-2" style="display:none;margin-top:12px;">
                                    <p><strong>1. Breath Awareness:</strong> Focus on your breath for 1 minute.<br>
                                    <strong>2. Body Scan:</strong> Notice sensations from head to toe.<br>
                                    <strong>3. Gratitude Pause:</strong> Think of one thing you're grateful for.<br>
                                    <strong>4. Mini Visualization:</strong> Picture a peaceful place.<br>
                                    <strong>5. Reset Intention:</strong> Set a positive intention for your next task.<br>
                                    These quick techniques can help you reset and recharge anytime!</p>
                                </div>
                            </div>
                        </article>
                        <!-- More blog posts would go here -->
                    </div>
                </section>
                <!-- Newsletter Subscription -->
                <section class="blog-newsletter">
                    <div class="newsletter-content">
                        <h2>Join Our Wellness Community</h2>
                        <p>Get the latest articles, health tips, and special offers delivered to your inbox</p>
                    </div>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </section>
            </div>
            <!-- About Widget -->
            <div class="sidebar-widget">
                <h3>About This Blog</h3>
                <p>Our wellness blog provides evidence-based articles written by our team of certified practitioners, covering topics from Ayurveda to Zen meditation.</p>
            </div>
    </main>
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
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function () {
            document.querySelector('.nav-links').classList.toggle('active');
        });
        // Blog search functionality
        function filterBlogPosts() {
            const input = document.getElementById('blogSearchInput');
            const filter = input.value.toLowerCase();
            const posts = document.querySelectorAll('.blog-post');
            posts.forEach(post => {
                const title = post.querySelector('h3').textContent.toLowerCase();
                const excerpt = post.querySelector('p, .excerpt') ? post.querySelector('p, .excerpt').textContent.toLowerCase() : '';
                if (title.includes(filter) || excerpt.includes(filter)) {
                    post.style.display = '';
                } else {
                    post.style.display = 'none';
                }
            });
        }
        document.getElementById('blogSearchInput').addEventListener('input', filterBlogPosts);
        document.getElementById('blogSearchBtn').addEventListener('click', function(e) {
            e.preventDefault();
            filterBlogPosts();
        });
        // Inline article expansion for blog posts
        document.querySelectorAll('.read-more').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var articleId = this.getAttribute('data-article');
                var fullArticle = document.getElementById('full-article-' + articleId);
                if (fullArticle.style.display === 'none' || fullArticle.style.display === '') {
                    fullArticle.style.display = 'block';
                    this.innerHTML = 'Show Less <i class="fas fa-arrow-up"></i>';
                } else {
                    fullArticle.style.display = 'none';
                    this.innerHTML = 'Read Full Article <i class="fas fa-arrow-right"></i>';
                }
            });
        });
    </script>
    <script src="auth_check.js"></script>
</body>
</html> 