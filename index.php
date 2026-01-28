<?php
/**
 * Hotel Reservation System - Homepage
 */

// Include header (this already loads db_connect.php)
$pageTitle = 'Luxury Hotel - Welcome';
include __DIR__ . '/includes/header.php';

// ==========================
// FETCH FEATURED ROOMS
// ==========================
$featuredRooms = [];

try {
    $sql = "
        SELECT id, room_number, type, price, description, image, max_guests
        FROM rooms
        WHERE status = 'available'
        ORDER BY price ASC
        LIMIT 3
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $featuredRooms = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Featured rooms error: ' . $e->getMessage());
}

// ==========================
// FETCH HOTEL STATISTICS
// ==========================
try {
    $stats = [
        'total_rooms'     => 0,
        'available_rooms' => 0,
        'total_bookings'  => 0,
        'avg_rating'      => 4.8
    ];

    $stats['total_rooms'] =
        $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();

    $stats['available_rooms'] =
        $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn();

    $stats['total_bookings'] =
        $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();

} catch (PDOException $e) {
    error_log('Stats error: ' . $e->getMessage());
}
?>

<!-- Hero Section with Video Background -->
<section class="hero-section">
    <!-- Video Background -->
    <video autoplay muted loop playsinline class="video-background" poster="../assets/images/hero-poster.jpg">
        <source src="assets/videos/hotel-bg.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/hero-fallback.jpg" alt="Luxury Hotel Background" class="video-fallback">
    </video>
    
    <!-- Video Overlay -->
    <div class="video-overlay"></div>
    
    <!-- Hero Content -->
    <div class="hero-content" data-aos="fade-up" data-aos-delay="200">
        <h1 class="hero-title">
            <span>Welcome</span> <span>to</span> <span>Abdulrasaq Luxury</span>
        </h1>
        <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="400">
            Experience the perfect blend of elegance, comfort, and exceptional service
        </p>
        <div class="hero-buttons" data-aos="fade-up" data-aos-delay="600">
            <a href="rooms.php" class="btn btn-primary btn-lg btn-hover-scale">
                <i class="fas fa-bed"></i>
                Explore Rooms
            </a>
            <a href="reservation.php" class="btn btn-outline-white btn-lg btn-hover-scale">
                <i class="fas fa-calendar-check"></i>
                Book Now
            </a>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="scroll-indicator" data-aos="fade-up" data-aos-delay="800">
        <span>Scroll to explore</span>
        <div class="scroll-arrow"></div>
    </div>
</section>

<!-- Hotel Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_rooms']; ?></div>
                <div class="stat-label">Total Rooms</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-number"><?php echo $stats['available_rooms']; ?></div>
                <div class="stat-label">Available Rooms</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
                <div class="stat-label">Happy Guests</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number"><?php echo $stats['avg_rating']; ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content" data-aos="fade-right" data-aos-delay="200">
                <h2>Experience Unparalleled Luxury</h2>
                <p>Welcome to Abdulrasaq Luxury Hotel, where elegance meets comfort in the heart of the city. Our prestigious establishment has been serving distinguished guests for over two decades, offering world-class amenities and impeccable service.</p>
                <p>From our beautifully appointed rooms to our gourmet dining options, every aspect of your stay has been carefully curated to exceed your expectations. Whether you're here for business or leisure, we promise an unforgettable experience.</p>
                <div class="about-features">
                    <div class="feature-item">
                        <i class="fas fa-concierge-bell"></i>
                        <span>24/7 Concierge Service</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-spa"></i>
                        <span>World-Class Spa</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-utensils"></i>
                        <span>Gourmet Restaurant</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-wifi"></i>
                        <span>High-Speed WiFi</span>
                    </div>
                </div>
            </div>
            <div class="about-image" data-aos="fade-left" data-aos-delay="400">
                <div class="image-wrapper">
                    <img src="assets/images/hotel-lobby.png" alt="Luxury Hotel Lobby" loading="lazy">
                    <!-- <div class="image-overlay">
                        <span>Est. 2001</span>
                    </div>-->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Rooms Section -->
<section class="featured-rooms section bg-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up" data-aos-delay="200">
            <h2>Featured Rooms</h2>
            <p>Discover our most popular accommodations, each designed for your comfort</p>
        </div>
        
        <div class="rooms-grid" data-aos="fade-up" data-aos-delay="400">
            <?php foreach ($featuredRooms as $room): ?>
            <div class="room-card">
                <div class="room-card-image">
                    <img src="assets/images/rooms/<?php echo htmlspecialchars($room['image'] ?? 'room-default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($room['type']); ?>" loading="lazy">
                    <div class="room-card-badge">
                        <span><?php echo htmlspecialchars($room['max_guests']); ?> Guests</span>
                    </div>
                </div>
                <div class="room-card-content">
                    <h3 class="room-card-title"><?php echo htmlspecialchars($room['type']); ?></h3>
                    <p class="room-card-description"><?php echo htmlspecialchars(substr($room['description'], 0, 100)) . '...'; ?></p>
                    <div class="room-card-footer">
                        <div class="room-card-price">
                            <span class="price-amount">$<?php echo number_format($room['price'], 2); ?></span>
                            <span class="price-unit">/night</span>
                        </div>
                        <a href="rooms.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center" data-aos="fade-up" data-aos-delay="600">
            <a href="rooms.php" class="btn btn-outline btn-lg">
                View All Rooms
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section section">
    <div class="container">
        <div class="section-title" data-aos="fade-up" data-aos-delay="200">
            <h2>Our Services</h2>
            <p>We offer a wide range of premium services to make your stay unforgettable</p>
        </div>
        
        <div class="services-grid" data-aos="fade-up" data-aos-delay="400">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h3>Fitness Center</h3>
                <p>State-of-the-art gym equipment and personal training sessions available 24/7</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-swimming-pool"></i>
                </div>
                <h3>Swimming Pool</h3>
                <p>Olympic-size heated swimming pool with poolside bar and relaxation area</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-cocktail"></i>
                </div>
                <h3>Bar & Lounge</h3>
                <p>Enjoy signature cocktails and premium spirits in our elegant bar</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-business-time"></i>
                </div>
                <h3>Business Center</h3>
                <p>Fully equipped business center with meeting rooms and conference facilities</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3>Valet Parking</h3>
                <p>Complimentary valet parking service for all our guests</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <h3>Airport Transfer</h3>
                <p>Luxury airport transfer service available upon request</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section section bg-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up" data-aos-delay="200">
            <h2>Guest Reviews</h2>
            <p>What our guests say about their experience</p>
        </div>
        
        <div class="testimonials-carousel" data-aos="fade-up" data-aos-delay="400">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"An absolutely wonderful experience. The staff went above and beyond to make our anniversary special. The room was immaculate and the views were breathtaking."</p>
                <div class="testimonial-author">
                    <strong>Johnson</strong>
                    <span>Ikeja , Lagos state</span>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Business trip turned into a pleasure stay. Excellent conference facilities, fast WiFi, and the restaurant served the best meals I've had in months."</p>
                <div class="testimonial-author">
                    <strong>Michael</strong>
                    <span>Ifo , ogun state</span>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Perfect family vacation! The kids loved the pool and the family room was spacious enough for all of us. Will definitely be coming back!"</p>
                <div class="testimonial-author">
                    <strong>Emma</strong>
                    <span>Abuja</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content" data-aos="fade-up" data-aos-delay="200">
            <h2>Ready for an Unforgettable Stay?</h2>
            <p>Book your room today and experience the luxury you deserve</p>
            <div class="cta-buttons">
                <a href="reservation.php" class="btn btn-primary btn-lg btn-hover-scale">
                    <i class="fas fa-calendar-check"></i>
                    Make a Reservation
                </a>
                <a href="tel:+15551234567" class="btn btn-outline-white btn-lg btn-hover-scale">
                    <i class="fas fa-phone"></i>
                    Call Us Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Additional Homepage CSS -->
<style>
/* Hero Section Styles */
.hero-section {
    position: relative;
    height: 100vh;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.video-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -1;
}

.video-fallback {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.7));
    z-index: 1;
}

.hero-content {
    text-align: center;
    color: var(--white);
    z-index: 2;
    max-width: 800px;
    padding: 0 var(--spacing-lg);
}

.hero-title {
    font-size: var(--font-size-5xl);
    font-weight: 700;
    margin-bottom: var(--spacing-lg);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.hero-title span {
    display: inline-block;
    animation: letterFadeIn 0.8s ease-out both;
}

.hero-title span:nth-child(1) { animation-delay: 0.2s; }
.hero-title span:nth-child(2) { animation-delay: 0.3s; }
.hero-title span:nth-child(3) { animation-delay: 0.4s; }

@keyframes letterFadeIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-subtitle {
    font-size: var(--font-size-xl);
    margin-bottom: var(--spacing-2xl);
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.hero-buttons {
    display: flex;
    gap: var(--spacing-lg);
    justify-content: center;
    flex-wrap: wrap;
}

.scroll-indicator {
    position: absolute;
    bottom: var(--spacing-2xl);
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: var(--white);
    z-index: 2;
    animation: bounce 2s infinite;
}

.scroll-indicator span {
    display: block;
    margin-bottom: var(--spacing-sm);
    font-size: var(--font-size-sm);
    opacity: 0.8;
}

.scroll-arrow {
    width: 20px;
    height: 20px;
    border-right: 2px solid var(--white);
    border-bottom: 2px solid var(--white);
    transform: rotate(45deg);
    margin: 0 auto;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateX(-50%) translateY(0);
    }
    40% {
        transform: translateX(-50%) translateY(-10px);
    }
    60% {
        transform: translateX(-50%) translateY(-5px);
    }
}

/* Stats Section */
.stats-section {
    background-color: var(--primary-color);
    color: var(--white);
    padding: var(--spacing-2xl) 0;
    margin-top: -1px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-xl);
    text-align: center;
}

.stat-item {
    padding: var(--spacing-lg);
}

.stat-icon {
    font-size: var(--font-size-3xl);
    margin-bottom: var(--spacing-md);
    color: var(--white);
}

.stat-number {
    font-size: var(--font-size-4xl);
    font-weight: 700;
    margin-bottom: var(--spacing-sm);
}

.stat-label {
    font-size: var(--font-size-base);
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* About Section */
.about-section {
    padding: var(--spacing-4xl) 0;
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-3xl);
    align-items: center;
}

.about-content h2 {
    font-size: var(--font-size-3xl);
    margin-bottom: var(--spacing-lg);
    color: var(--secondary-color);
}

.about-content p {
    font-size: var(--font-size-lg);
    line-height: 1.8;
    margin-bottom: var(--spacing-lg);
    color: var(--gray-700);
}

.about-features {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xl);
}

.feature-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background-color: var(--gray-100);
    border-radius: var(--radius-md);
}

.feature-item i {
    color: var(--primary-color);
    font-size: var(--font-size-lg);
}

.about-image .image-wrapper {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.about-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.about-image .image-wrapper:hover img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    bottom: var(--spacing-lg);
    left: var(--spacing-lg);
    background-color: var(--primary-color);
    color: var(--white);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-md);
    font-weight: 600;
}

/* Services Section */
.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-xl);
}

.service-card {
    text-align: center;
    padding: var(--spacing-2xl) var(--spacing-lg);
    background-color: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    transition: transform var(--transition-base), box-shadow var(--transition-base);
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-xl);
}

.service-icon {
    font-size: var(--font-size-4xl);
    color: var(--primary-color);
    margin-bottom: var(--spacing-lg);
}

.service-card h3 {
    font-size: var(--font-size-xl);
    margin-bottom: var(--spacing-md);
    color: var(--secondary-color);
}

.service-card p {
    color: var(--gray-600);
    line-height: 1.6;
}

/* Testimonials Section */
.testimonials-carousel {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-xl);
}

.testimonial-card {
    background-color: var(--white);
    padding: var(--spacing-2xl) var(--spacing-lg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    text-align: center;
    transition: transform var(--transition-base);
}

.testimonial-card:hover {
    transform: translateY(-5px);
}

.testimonial-rating {
    color: var(--warning-color);
    margin-bottom: var(--spacing-lg);
}

.testimonial-text {
    font-style: italic;
    color: var(--gray-700);
    line-height: 1.8;
    margin-bottom: var(--spacing-lg);
}

.testimonial-author strong {
    display: block;
    color: var(--secondary-color);
    margin-bottom: var(--spacing-xs);
}

.testimonial-author span {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-dark)),
                url('assets/images/cta-bg.jpg') center/cover;
    padding: var(--spacing-4xl) 0;
    text-align: center;
    color: var(--white);
}

.cta-content h2 {
    font-size: var(--font-size-3xl);
    color: var(--white);
    margin-bottom: var(--spacing-lg);
}

.cta-content p {
    font-size: var(--font-size-xl);
    opacity: 0.9;
    margin-bottom: var(--spacing-2xl);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    gap: var(--spacing-lg);
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .hero-title {
        font-size: var(--font-size-3xl);
    }
    
    .hero-subtitle {
        font-size: var(--font-size-lg);
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .about-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-2xl);
    }
    
    .about-features {
        grid-template-columns: 1fr;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .testimonials-carousel {
        grid-template-columns: 1fr;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .hero-title {
        font-size: var(--font-size-2xl);
    }
    
    .scroll-indicator {
        display: none;
    }
}
</style>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>