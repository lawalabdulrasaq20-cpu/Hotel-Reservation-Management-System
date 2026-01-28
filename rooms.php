<?php
/**
 * Hotel Reservation System - Rooms Page
 * 
 * This page displays all available rooms with search and filter functionality.
 * Users can search for rooms by dates, number of guests, and room type.
 * 
 * PHP version 7.4+
 * 
 * @category Hotel_Reservation
 * @package  Public
 * @author   Hotel Reservation System
 * @license  MIT License
 */

// Include header
$pageTitle = 'Our Rooms - Luxury Hotel';
include __DIR__ . '/includes/header.php';

// Include database connection
require_once __DIR__ . '/includes/db_connect.php';

// Initialize variables
$rooms = [];
$searchParams = [
    'check_in' => '',
    'check_out' => '',
    'guests' => 1,
    'room_type' => ''
];
$errors = [];
$searchPerformed = false;

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchPerformed = true;
    
    // Sanitize input
    $searchParams['check_in'] = isset($_GET['check_in']) ? sanitizeInput($_GET['check_in']) : '';
    $searchParams['check_out'] = isset($_GET['check_out']) ? sanitizeInput($_GET['check_out']) : '';
    $searchParams['guests'] = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;
    $searchParams['room_type'] = isset($_GET['room_type']) ? sanitizeInput($_GET['room_type']) : '';
    
    // Validate dates
    if (empty($searchParams['check_in'])) {
        $errors['check_in'] = 'Please select a check-in date';
    }
    
    if (empty($searchParams['check_out'])) {
        $errors['check_out'] = 'Please select a check-out date';
    }
    
    if (!empty($searchParams['check_in']) && !empty($searchParams['check_out'])) {
        $checkInDate = new DateTime($searchParams['check_in']);
        $checkOutDate = new DateTime($searchParams['check_out']);
        
        if ($checkOutDate <= $checkInDate) {
            $errors['dates'] = 'Check-out date must be after check-in date';
        }
    }
    
    // If no errors, search for available rooms
    if (empty($errors)) {
        try {
            $rooms = getAvailableRooms($pdo, $searchParams['check_in'], $searchParams['check_out'], $searchParams['guests'], $searchParams['room_type']);
        } catch (PDOException $e) {
            $errors['database'] = 'An error occurred while searching for rooms. Please try again.';
            error_log("Room search error: " . $e->getMessage());
        }
    }
} else {
    // Show all available rooms by default
    try {
        $sql = "SELECT id, room_number, type, price, description, image, max_guests, status 
                FROM rooms 
                WHERE status = 'available'
                ORDER BY price ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching rooms: " . $e->getMessage());
        $rooms = [];
    }
}

// Fetch room types for filter dropdown
$roomTypes = [];
try {
    $sql = "SELECT DISTINCT type FROM rooms WHERE status = 'available' ORDER BY type";
    $stmt = $pdo->query($sql);
    $roomTypes = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    error_log("Error fetching room types: " . $e->getMessage());
}

// Get today's date and tomorrow's date for date picker defaults
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Our Rooms</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">Rooms</span>
        </nav>
    </div>
</section>

<!-- Search Section -->
<section class="search-section section-sm bg-light">
    <div class="container">
        <div class="search-form-wrapper" data-aos="fade-up" data-aos-delay="200">
            <form action="rooms.php" method="GET" class="search-form" id="room-search-form">
                <input type="hidden" name="search" value="1">
                
                <div class="search-form-row">
                    <!-- Check-in Date -->
                    <div class="search-field">
                        <label for="check_in" class="form-label">
                            <i class="fas fa-calendar-alt"></i> Check-in
                        </label>
                        <input type="date" 
                               id="check_in" 
                               name="check_in" 
                               class="form-control <?php echo isset($errors['check_in']) ? 'error' : ''; ?>"
                               value="<?php echo htmlspecialchars($searchParams['check_in']); ?>"
                               min="<?php echo $today; ?>"
                               required>
                        <?php if (isset($errors['check_in'])): ?>
                            <span class="error-message"><?php echo $errors['check_in']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Check-out Date -->
                    <div class="search-field">
                        <label for="check_out" class="form-label">
                            <i class="fas fa-calendar-alt"></i> Check-out
                        </label>
                        <input type="date" 
                               id="check_out" 
                               name="check_out" 
                               class="form-control <?php echo isset($errors['check_out']) ? 'error' : ''; ?>"
                               value="<?php echo htmlspecialchars($searchParams['check_out']); ?>"
                               min="<?php echo $tomorrow; ?>"
                               required>
                        <?php if (isset($errors['check_out'])): ?>
                            <span class="error-message"><?php echo $errors['check_out']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Number of Guests -->
                    <div class="search-field">
                        <label for="guests" class="form-label">
                            <i class="fas fa-users"></i> Guests
                        </label>
                        <select id="guests" name="guests" class="form-control" required>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $searchParams['guests'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Room Type -->
                    <div class="search-field">
                        <label for="room_type" class="form-label">
                            <i class="fas fa-bed"></i> Room Type
                        </label>
                        <select id="room_type" name="room_type" class="form-control">
                            <option value="">All Types</option>
                            <?php foreach ($roomTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" 
                                        <?php echo $searchParams['room_type'] === $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <?php if (isset($errors['dates'])): ?>
                    <div class="error-message global-error"><?php echo $errors['dates']; ?></div>
                <?php endif; ?>
                
                <div class="search-form-actions">
                    <button type="submit" class="btn btn-primary btn-lg btn-hover-scale">
                        <i class="fas fa-search"></i>
                        Search Available Rooms
                    </button>
                    <?php if ($searchPerformed): ?>
                        <a href="rooms.php" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            Clear Search
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Rooms List Section -->
<section class="rooms-section section">
    <div class="container">
        <?php if ($searchPerformed && empty($errors)): ?>
            <!-- Search Results Header -->
            <div class="search-results-header" data-aos="fade-up" data-aos-delay="200">
                <h2>Search Results</h2>
                <p>
                    <?php if (!empty($rooms)): ?>
                        Found <?php echo count($rooms); ?> available room<?php echo count($rooms) > 1 ? 's' : ''; ?> 
                        for <?php echo htmlspecialchars($searchParams['guests']); ?> guest<?php echo $searchParams['guests'] > 1 ? 's' : ''; ?>
                        <?php if (!empty($searchParams['check_in']) && !empty($searchParams['check_out'])): ?>
                            from <?php echo date('M j, Y', strtotime($searchParams['check_in'])); ?> 
                            to <?php echo date('M j, Y', strtotime($searchParams['check_out'])); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        No rooms available for your selected dates and criteria.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <!-- Default Header -->
            <div class="section-title" data-aos="fade-up" data-aos-delay="200">
                <h2>Our Accommodations</h2>
                <p>Choose from our selection of premium rooms and suites</p>
            </div>
        <?php endif; ?>
        
<!-- Rooms Grid -->
<?php if (!empty($rooms)): ?>
    <div class="rooms-grid" data-aos="fade-up" data-aos-delay="400">
        <?php foreach ($rooms as $room): 
            $totalPrice = $room['price'];
            $nights = 1;

            if (!empty($searchParams['check_in']) && !empty($searchParams['check_out'])) {
                $checkInDate  = new DateTime($searchParams['check_in']);
                $checkOutDate = new DateTime($searchParams['check_out']);

                $nights = max(1, $checkInDate->diff($checkOutDate)->days);
                $totalPrice = $room['price'] * $nights;
            }
        ?>

                    <div class="room-card">
                        <div class="room-card-image">
                            <img src="assets/images/rooms/<?php echo htmlspecialchars($room['image'] ?? 'room-default.jpg'); ?>">
                                 alt="<?php echo htmlspecialchars($room['type']); ?>" 
                                 loading="lazy">
                            <div class="room-card-badge">
                                <span><?php echo htmlspecialchars($room['max_guests']); ?> Guests</span>
                            </div>
                            <?php if ($room['status'] !== 'available'): ?>
                                <div class="room-status-badge unavailable">
                                    <span><?php echo ucfirst($room['status']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="room-card-content">
                            <div class="room-card-header">
                                <h3 class="room-card-title"><?php echo htmlspecialchars($room['type']); ?></h3>
                                <span class="room-number">Room #<?php echo htmlspecialchars($room['room_number']); ?></span>
                            </div>
                            
                            <p class="room-card-description">
                                <?php echo htmlspecialchars(substr($room['description'], 0, 120)) . '...'; ?>
                            </p>
                            
                            <div class="room-features">
                                <span class="feature-tag">
                                    <i class="fas fa-users"></i> 
                                    <?php echo $room['max_guests']; ?> Guest<?php echo $room['max_guests'] > 1 ? 's' : ''; ?>
                                </span>
                                <span class="feature-tag">
                                    <i class="fas fa-wifi"></i> Free WiFi
                                </span>
                                <span class="feature-tag">
                                    <i class="fas fa-snowflake"></i> AC
                                </span>
                            </div>
                            
                            <div class="room-card-footer">
                                <div class="room-card-price">
                                    <span class="price-amount">$<?php echo number_format($totalPrice, 2); ?></span>
                                    <span class="price-unit">
                                        <?php if ($searchPerformed && $nights > 1): ?>
                                            for <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>
                                        <?php else: ?>
                                            /night
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <?php if ($room['status'] === 'available'): ?>
                                    <a href="reservation.php?room_id=<?php echo $room['id']; ?>
                                        <?php if ($searchPerformed): ?>
                                            &check_in=<?php echo urlencode($searchParams['check_in']); ?>
                                            &check_out=<?php echo urlencode($searchParams['check_out']); ?>
                                            &guests=<?php echo $searchParams['guests']; ?>
                                        <?php endif; ?>" 
                                       class="btn btn-primary btn-hover-scale">
                                        <i class="fas fa-calendar-check"></i>
                                        Book Now
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-ban"></i>
                                        Unavailable
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($searchPerformed): ?>
            <!-- No Results -->
            <div class="no-results" data-aos="fade-up" data-aos-delay="400">
                <div class="no-results-icon">
                    <i class="fas fa-bed"></i>
                </div>
                <h3>No Rooms Found</h3>
                <p>We couldn't find any available rooms matching your criteria. Please try different dates or room types.</p>
                <a href="rooms.php" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search Again
                </a>
            </div>
        <?php else: ?>
            <!-- Loading State -->
            <div class="loading-state" data-aos="fade-up" data-aos-delay="400">
                <div class="spinner"></div>
                <p>Loading rooms...</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Additional CSS -->
<style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-dark));
    color: var(--white);
    padding: var(--spacing-3xl) 0 var(--spacing-xl);
    text-align: center;
}

.page-header h1 {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--spacing-md);
    color: var(--white);
}

.breadcrumb {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: var(--font-size-sm);
}

.breadcrumb a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color var(--transition-base);
}

.breadcrumb a:hover {
    color: var(--white);
}

.breadcrumb .separator {
    color: rgba(255, 255, 255, 0.5);
}

.breadcrumb .current {
    color: var(--primary-color);
    font-weight: 500;
}

/* Search Section */
.search-section {
    background-color: var(--gray-100);
}

.search-form-wrapper {
    max-width: 1000px;
    margin: 0 auto;
}

.search-form {
    background-color: var(--white);
    padding: var(--spacing-2xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}

.search-form-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.search-field .form-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    font-weight: 500;
    color: var(--gray-700);
}

.search-form-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
}

/* Rooms Section */
.rooms-section {
    padding: var(--spacing-3xl) 0;
}

.search-results-header {
    text-align: center;
    margin-bottom: var(--spacing-2xl);
}

.search-results-header h2 {
    font-size: var(--font-size-3xl);
    margin-bottom: var(--spacing-md);
}

.search-results-header p {
    font-size: var(--font-size-lg);
    color: var(--gray-600);
}

.rooms-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-xl);
}

.room-card {
    background-color: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    transition: transform var(--transition-base), box-shadow var(--transition-base);
}

.room-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-xl);
}

.room-card-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.room-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.room-card:hover .room-card-image img {
    transform: scale(1.1);
}

.room-card-badge {
    position: absolute;
    top: var(--spacing-md);
    right: var(--spacing-md);
    background-color: var(--primary-color);
    color: var(--white);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
}

.room-status-badge {
    position: absolute;
    top: var(--spacing-md);
    left: var(--spacing-md);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
}

.room-status-badge.unavailable {
    background-color: var(--danger-color);
    color: var(--white);
}

.room-card-content {
    padding: var(--spacing-lg);
}

.room-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
}

.room-card-title {
    font-size: var(--font-size-xl);
    margin-bottom: 0;
    color: var(--secondary-color);
}

.room-number {
    color: var(--gray-500);
    font-size: var(--font-size-sm);
}

.room-card-description {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: var(--spacing-lg);
}

.room-features {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
}

.feature-tag {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    background-color: var(--gray-100);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--gray-700);
}

.room-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--gray-200);
}

.room-card-price {
    display: flex;
    flex-direction: column;
}

.price-amount {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--primary-color);
}

.price-unit {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

/* No Results */
.no-results {
    text-align: center;
    padding: var(--spacing-4xl) 0;
}

.no-results-icon {
    font-size: var(--font-size-5xl);
    color: var(--gray-300);
    margin-bottom: var(--spacing-lg);
}

.no-results h3 {
    font-size: var(--font-size-2xl);
    color: var(--gray-700);
    margin-bottom: var(--spacing-md);
}

.no-results p {
    color: var(--gray-600);
    margin-bottom: var(--spacing-xl);
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: var(--spacing-4xl) 0;
}

.loading-state .spinner {
    width: 40px;
    height: 40px;
    margin: 0 auto var(--spacing-lg);
}

.loading-state p {
    color: var(--gray-600);
}

/* Error Messages */
.error-message {
    display: block;
    margin-top: var(--spacing-xs);
    font-size: var(--font-size-sm);
    color: var(--danger-color);
}

.global-error {
    text-align: center;
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-sm);
    background-color: rgba(231, 76, 60, 0.1);
    border-radius: var(--radius-md);
}

/* Responsive Styles */
@media (max-width: 992px) {
    .search-form-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .rooms-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .search-form {
        padding: var(--spacing-lg);
    }
    
    .search-form-row {
        grid-template-columns: 1fr;
    }
    
    .rooms-grid {
        grid-template-columns: 1fr;
    }
    
    .room-card-image {
        height: 200px;
    }
    
    .search-form-actions {
        flex-direction: column;
    }
    
    .search-form-actions .btn {
        width: 100%;
    }
}
</style>

<!-- JavaScript for date validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput && checkOutInput) {
        // Update min date for checkout when checkin changes
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const nextDay = new Date(checkInDate);
            nextDay.setDate(nextDay.getDate() + 1);
            
            const nextDayStr = nextDay.toISOString().split('T')[0];
            checkOutInput.setAttribute('min', nextDayStr);
            
            // If checkout is before or same as checkin, update it
            if (checkOutInput.value && checkOutInput.value <= this.value) {
                checkOutInput.value = nextDayStr;
            }
        });
    }
});
</script>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>