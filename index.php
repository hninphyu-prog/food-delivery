<?php
session_start();
// $_SESSION['test'] = 'Session is working!';
?>
<?php include("includes/header.php")?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food&Me - Delicious Food Delivery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Enhanced How It Works Section with Animations */
        .how-it-works {
            
            background: linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%);
            position: relative;
            overflow: hidden;
        }
        
        .how-it-works::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="%23ff6b6b" opacity="0.03"/></svg>');
            background-size: cover;
            z-index: 0;
        }
        
        .steps {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            position: relative;
            z-index: 1;
        ;
        }
        
        .step {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            background: white;
            border-radius: 16px;
            padding: 2.5rem 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgb(255,102,0);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s ease;
        }
        
        .step:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }
        
        .step:hover::before {
            transform: scaleX(1);
        }
        
        .step__number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgb(255,102,0),white,rgb(255,102,0));
            color: black;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0 auto 1.5rem;
            position: relative;
            z-index: 1;
            transition: all 0.4s ease;
        }
        
        .step:hover .step__number {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
        }
        
        .step__title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
            position: relative;
        }
        
        .step p {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .step__icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: rgb(255,102,0);
            transition: all 0.5s ease;
            display: inline-block;
        }
        
        .step:hover .step__icon {
            transform: scale(1.2) rotate(10deg);
            color: rgb(255,102,0);
        }
        
        
        
        /* Animation for step appearance */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .step.animate {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        /* Pulse animation for step numbers */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .step:hover .step__number {
            animation: pulse 1s infinite;
        }
        
        /* Decorative elements */
        .step::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 107, 107, 0.05), transparent);
            transform: rotate(45deg);
            transition: all 0.5s;
            opacity: 0;
        }
        
        .step:hover::after {
            opacity: 1;
            transform: rotate(45deg) translate(10%, 10%);
        }
        
        /* Connector lines for steps */
        .steps::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: linear-gradient(to right, #ff6b6b, #4ecdc4);
            z-index: 0;
            opacity: 0.3;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .steps {
                flex-direction: column;
                align-items: center;
            }
            
            .step {
                max-width: 100%;
            }
            
            .steps::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    
    <main>
        <section class="hero color-bg">
            <div class="container hero-inner">
                <div class="hero-content">
                    <h1>Need to order food? <span>Just Click</span></h1>
                    <p class="lead">Fast delivery from restaurants and stores you love. Order now and get it delivered hot & fresh.</p>
                    <p>
                        <a class="btn--primary" href="views/customer/restaurants.php">
                            <i class="fas fa-utensils"></i> Order now
                        </a>
                    </p>
                </div>
                <div class="hero-visual">
                    <div class="hero-image-container">
                        <img src="assets/images/jojo.jpg"
                             alt="Delicious chicken burger from Food&Me" class="hero-image active" data-index="0">
                        <img src="assets/images/kfc.jpg" 
                             alt="Spicy chicken burger from Food&Me" class="hero-image" data-index="1">
                        <img src="assets/images/ykkobg.jpg"
                             alt="Crispy chicken burger from Food&Me" class="hero-image" data-index="2">
                        <img src="assets/images/chapayom.jpg"
                             alt="Gourmet chicken burger from Food&Me" class="hero-image" data-index="3">
                        <img src="assets/images/sp.jpg"
                             alt="Premium chicken burger from Food&Me" class="hero-image" data-index="4">
                        
                        <div class="image-dots">
                            <div class="dot active" data-index="0"></div>
                            <div class="dot" data-index="1"></div>
                            <div class="dot" data-index="2"></div>
                            <div class="dot" data-index="3"></div>
                            <div class="dot" data-index="4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="features container">
            <div class="feature">
                <i class="fas fa-store"></i>
                <h3>Restaurants near you</h3>
                <p>Handpicked restaurants and popular menus.</p>
            </div>
            <div class="feature">
                <i class="fas fa-shipping-fast"></i>
                <h3>Fast delivery</h3>
                <p>Quick, reliable delivery to your door.</p>
            </div>
            <div class="feature">
                <i class="fas fa-shopping-basket"></i>
                <h3>Groceries & essentials</h3>
                <p>Get everyday items delivered fast.</p>
            </div>
        </section>

        <!-- Popular Categories Section -->
        <section class="categories">
            <div class="container">
                <h2 class="section-title">Popular Categories</h2>
                <div class="categories-grid">
                    <div class="category-card">
                        <div class="category-card__image">
                            <img src="assets/images/burger.jpg" alt="Burgers">
                        </div>
                        <div class="category-card__content">
                            <h3 class="category-card__title">Burgers</h3>
                            <p class="category-card__count">24 restaurants</p>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-card__image">
                            <img src="assets/images/ham&cheese.jpg" alt="Pizza">
                        </div>
                        <div class="category-card__content">
                            <h3 class="category-card__title">Pizza</h3>
                            <p class="category-card__count">18 restaurants</p>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-card__image">
                            <img src="assets/images/chicken.jpg" alt="Asian">
                        </div>
                        <div class="category-card__content">
                            <h3 class="category-card__title">Chicken</h3>
                            <p class="category-card__count">32 restaurants</p>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-card__image">
                            <img src="assets/images/noodle.jpg" alt="Desserts">
                        </div>
                        <div class="category-card__content">
                            <h3 class="category-card__title">Noodle</h3>
                            <p class="category-card__count">15 restaurants</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <div class="container">
                <h2 class="section-title">How It Works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step__number">1</div>
                        <div class="step__icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3 class="step__title">Choose your food</h3>
                        <p>Browse hundreds of restaurants and menus to find what you crave.</p>
                    </div>
                    
                    <div class="step">
                        <div class="step__number">2</div>
                        <div class="step__icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="step__title">Place your order</h3>
                        <p>Add items to your cart and complete your order with a few clicks.</p>
                    </div>
                    
                    <div class="step">
                        <div class="step__number">3</div>
                        <div class="step__icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="step__title">Track delivery</h3>
                        <p>Follow your order in real-time as we prepare and deliver it.</p>
                    </div>
                    
               
                </div>
            </div>
        </section>

        <!-- Mobile App Section -->
        <section class="mobile-app">
            <div class="container">
                <div class="mobile-app__content">
                    <div class="mobile-app__text">
                        <h2>Get the Food&Me App</h2>
                        <p>Our Team is working hard to bring you the best experience possible.So stay tuned for mobile app version.</p>
                        <div class="app-buttons">
                            <a href="#" class="app-button">
                                <i class="fab fa-apple"></i>
                                <div>
                                    <span>Download on the</span>
                                    <strong>App Store</strong>
                                </div>
                            </a>
                            <a href="#" class="app-button">
                                <i class="fab fa-google-play"></i>
                                <div>
                                    <span>GET IT ON</span>
                                    <strong>Google Play</strong>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mobile-app__image">
                        <img src="assets/images/app-screen.png" alt="Food&Me Mobile App">
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="container">
                <h2 class="section-title">What Our Customers Say</h2>
                <div class="testimonials-grid">
                    <?php
                    try {
                        require_once "config/db.php";
                        
                        $reviews = [];
                        $error = '';
                        
                        if (isset($pdo)) {
                            $sql = "
                                SELECT r.comment, r.rating, u.name AS user_name, res.name AS restaurant_name
                                FROM reviews r
                                JOIN users u ON r.user_id = u.user_id
                                JOIN restaurants res ON r.restaurant_id = res.restaurant_id
                                WHERE r.status = 'visible'
                                ORDER BY r.review_id DESC
                                LIMIT 6
                            ";
                            
                            $stmt = $pdo->prepare($sql);
                            if ($stmt->execute()) {
                                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } else {
                                $error = 'Unable to fetch reviews at this time.';
                                error_log('Review query failed: ' . implode(' ', $stmt->errorInfo()));
                            }
                        } else {
                            $error = 'Database connection error.';
                            error_log('Database connection not available in reviews section');
                        }
                        
                        if (!empty($reviews)) {
                        foreach ($reviews as $row) {
                            // Generate stars
                            $stars = "";
                            for ($i = 1; $i <= 5; $i++) {
                                $stars .= ($i <= $row['rating'])
                                    ? '<i class="fas fa-star"></i>'
                                    : '<i class="far fa-star"></i>';
                            }

                            echo '
                            <div class="testimonial-card">
                                <div class="testimonial-card__content">
                                    <p>' . htmlspecialchars($row['comment']) . '</p>
                                </div>
                                <div class="testimonial-card__author">
                                    <div class="testimonial-card__info">
                                        <h4>' . htmlspecialchars($row['user_name']) . '</h4>
                                        <p>on ' . htmlspecialchars($row['restaurant_name']) . '</p>
                                        <div class="testimonial-card__rating">' . $stars . '</div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<div class="no-reviews">' . 
                             (!empty($error) ? '<p class="error">' . htmlspecialchars($error) . '</p>' : '') .
                             '<p>No reviews found. Be the first to leave a review!</p>' .
                             '</div>';
                    }
                    } catch (Exception $e) {
                        error_log('Error in reviews section: ' . $e->getMessage());
                        echo '<div class="no-reviews">' .
                             '<p>Unable to load reviews at this time. Please try again later.</p>' .
                             '</div>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

   <?php include ("includes/footer.php")?>
    
   <script>
    document.addEventListener('DOMContentLoaded', () => {
        // ----- safe helpers -----
        const safe = (sel) => document.querySelector(sel);
        const safeAll = (sel) => Array.from(document.querySelectorAll(sel));

        // ----- nav toggle (guarded) -----
        const navToggle = document.getElementById('navToggle');
        const mainNav = document.getElementById('main-nav');
        if (navToggle && mainNav) {
            navToggle.addEventListener('click', () => {
                navToggle.classList.toggle('active');
                mainNav.classList.toggle('active');
                navToggle.setAttribute('aria-expanded',
                    navToggle.getAttribute('aria-expanded') === 'false' ? 'true' : 'false'
                );
            });
        }

        // Close mobile nav when clicking on a link (safe)
        const navLinks = safeAll('.main-nav__list a');
        if (navLinks.length) {
            navLinks.forEach(link => link.addEventListener('click', () => {
                if (navToggle) {
                    navToggle.classList.remove('active');
                    navToggle.setAttribute('aria-expanded', 'false');
                }
                if (mainNav) mainNav.classList.remove('active');
            }));
        }

        // Location button (guarded)
        const chooseLocationBtn = document.getElementById("choose-location");
        if (chooseLocationBtn) {
            chooseLocationBtn.addEventListener("click", function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        alert("Your Location:\nLatitude: " + position.coords.latitude +
                                "\nLongitude: " + position.coords.longitude);
                    }, function(error) {
                        alert("Unable to retrieve your location. Please allow location access.");
                    });
                } else {
                    alert("Geolocation is not supported by your browser.");
                }
            });
        }

        // Newsletter (guarded)
        const newsletterForm = safe('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = this.querySelector('input')?.value;
                if (email) {
                    alert('Thank you for subscribing to our newsletter!');
                    this.querySelector('input').value = '';
                }
            });
        }

        // ----- slideshow (robust) -----
        const images = safeAll('.hero-image');
        const dots = safeAll('.dot');

        if (images.length === 0) {
            console.warn('Slideshow: no .hero-image elements found');
            return;
        }

        // find current active index or default to 0
        let currentIndex = images.findIndex(img => img.classList.contains('active'));
        if (currentIndex === -1) currentIndex = 0;

        let slideInterval = null;
        const SLIDE_MS = 2000; 

        function showImage(index) {
            if (!images[index]) return;
            images.forEach(img => img.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            images[index].classList.add('active');
            if (dots[index]) dots[index].classList.add('active');
            currentIndex = index;
        }

        function nextImage() {
            const next = (currentIndex + 1) % images.length;
            showImage(next);
        }

        function startSlideshow() {
            stopSlideshow();
            slideInterval = setInterval(nextImage, SLIDE_MS);
        }

        function stopSlideshow() {
            if (slideInterval) clearInterval(slideInterval);
            slideInterval = null;
        }

        // dot clicks (guarded)
        if (dots.length) {
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    const index = parseInt(dot.getAttribute('data-index'), 10);
                    if (!Number.isNaN(index)) {
                        showImage(index);
                        startSlideshow();
                    }
                });
            });
        }

        // hover to pause (guarded)
        const imageContainer = safe('.hero-image-container');
        if (imageContainer) {
            imageContainer.addEventListener('mouseenter', stopSlideshow);
            imageContainer.addEventListener('mouseleave', startSlideshow);
        }

        // ensure initial state visible
        showImage(currentIndex);
        startSlideshow();

        // ----- small UI scripts from your second block (guarded) -----
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 20px rgba(0,0,0,0.1)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.05)';
            });
        });

        // Enhanced step animations
        function animateSteps() {
            const steps = document.querySelectorAll('.step');
            
            // Function to check if element is in viewport
            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
                    rect.bottom >= 0
                );
            }
            
            // Function to handle scroll animation
            function handleScrollAnimation() {
                steps.forEach((step, index) => {
                    if (isInViewport(step)) {
                        setTimeout(() => {
                            step.classList.add('animate');
                        }, index * 200);
                    }
                });
            }
            
            // Initial check
            handleScrollAnimation();
            
            // Check on scroll
            window.addEventListener('scroll', handleScrollAnimation);
        }
        
        animateSteps();
    });
    </script>

</body>
</html>