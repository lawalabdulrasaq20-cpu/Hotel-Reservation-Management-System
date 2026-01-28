<?php
/**
 * Luxury Hotel Reservation System
 * Reservation Page
 * PHP 7.4+
 */

// --------------------------------------------------
// SAFE SESSION START
// --------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------
// DEPENDENCIES
// --------------------------------------------------
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mail.php';

// --------------------------------------------------
// PAGE META
// --------------------------------------------------
$pageTitle = 'Make a Reservation - Luxury Hotel';
include __DIR__ . '/includes/header.php';

// --------------------------------------------------
// VARIABLES
// --------------------------------------------------
$errors = [];
$room = null;
$totalPrice = 0;
$nights = 1;

// --------------------------------------------------
// QUERY PARAMS
// --------------------------------------------------
$selectedRoomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$checkIn  = $_GET['check_in']  ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests   = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;

// --------------------------------------------------
// FETCH SELECTED ROOM (SAFE CHECK)
// --------------------------------------------------
if ($selectedRoomId > 0) {
    $stmt = $pdo->prepare("
        SELECT r.*
        FROM rooms r
        WHERE r.id = :id
        AND NOT EXISTS (
            SELECT 1 FROM reservations res
            WHERE res.room_id = r.id
            AND res.status IN ('confirmed', 'checked_in')
        )
        LIMIT 1
    ");
    $stmt->execute(['id' => $selectedRoomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        $errors['room'] = 'Selected room is not available.';
    }
}

// --------------------------------------------------
// HANDLE FORM SUBMISSION
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {

    $form = [
        'room_id'          => (int)($_POST['room_id'] ?? 0),
        'guest_name'       => trim($_POST['guest_name'] ?? ''),
        'email'            => trim($_POST['email'] ?? ''),
        'phone'            => trim($_POST['phone'] ?? ''),
        'check_in'         => $_POST['check_in'] ?? '',
        'check_out'        => $_POST['check_out'] ?? '',
        'guests'           => (int)($_POST['guests'] ?? 1),
        'special_requests' => trim($_POST['special_requests'] ?? '')
    ];

    // --------------------------------------------------
    // VALIDATION
    // --------------------------------------------------
    if ($form['room_id'] <= 0) {
        $errors['room'] = 'Please select a room.';
    }

    if (strlen($form['guest_name']) < 3) {
        $errors['guest_name'] = 'Guest name must be at least 3 characters.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address.';
    }

    if (strlen($form['phone']) < 7) {
        $errors['phone'] = 'Invalid phone number.';
    }

    if (empty($form['check_in']) || empty($form['check_out'])) {
        $errors['dates'] = 'Please select check-in and check-out dates.';
    } else {
        $checkInDate  = new DateTime($form['check_in']);
        $checkOutDate = new DateTime($form['check_out']);

        if ($checkOutDate <= $checkInDate) {
            $errors['dates'] = 'Check-out must be after check-in.';
        }
    }

    // --------------------------------------------------
    // PROCESS RESERVATION
    // --------------------------------------------------
    if (empty($errors)) {

        // Re-check availability (CRITICAL)
        $stmt = $pdo->prepare("
            SELECT r.*
            FROM rooms r
            WHERE r.id = :id
            AND NOT EXISTS (
                SELECT 1 FROM reservations res
                WHERE res.room_id = r.id
                AND res.status IN ('confirmed', 'checked_in')
            )
            LIMIT 1
        ");
        $stmt->execute(['id' => $form['room_id']]);
        $roomData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$roomData) {
            $errors['room'] = 'Room is no longer available.';
        } elseif ($form['guests'] > $roomData['max_guests']) {
            $errors['guests'] = 'Guest count exceeds room capacity.';
        } else {

            // Calculate price
            $nights = max(1, $checkInDate->diff($checkOutDate)->days);
            $totalPrice = $roomData['price'] * $nights;

            // Insert reservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations
                (guest_name, email, phone, room_id, check_in, check_out, total_price, status, special_requests)
                VALUES
                (:guest_name, :email, :phone, :room_id, :check_in, :check_out, :total_price, 'pending', :special_requests)
            ");

            $stmt->execute([
                'guest_name'       => $form['guest_name'],
                'email'            => $form['email'],
                'phone'            => $form['phone'],
                'room_id'          => $form['room_id'],
                'check_in'         => $form['check_in'],
                'check_out'        => $form['check_out'],
                'total_price'      => $totalPrice,
                'special_requests' => $form['special_requests']
            ]);

            $reservationId = $pdo->lastInsertId();

            // Store success data
            $_SESSION['reservation_success'] = true;
            $_SESSION['reservation_data'] = [
    'reservation_id' => $reservationId,
    'guest_name'     => $form['guest_name'],
    'email'          => $form['email'],
    'phone'          => $form['phone'], // ✅ ADD THIS
    'room_type'      => $roomData['type'],
    'check_in'       => $form['check_in'],
    'check_out'      => $form['check_out'],
    'guests'         => $form['guests'],
    'nights'         => $nights,
    'total_price'    => $totalPrice,
    'status'         => 'Pending'
];


            sendReservationEmail($_SESSION['reservation_data']);

            header('Location: reservation-success.php');
            exit;
        }
    }
}

// --------------------------------------------------
// FETCH AVAILABLE ROOMS (FIXED)
// --------------------------------------------------
$allRooms = $pdo->query("
    SELECT r.*
    FROM rooms r
    WHERE NOT EXISTS (
        SELECT 1 FROM reservations res
        WHERE res.room_id = r.id
        AND res.status IN ('confirmed', 'checked_in')
    )
    ORDER BY r.price ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>


<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Make a Reservation</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <a href="rooms.php">Rooms</a>
            <span class="separator">/</span>
            <span class="current">Reservation</span>
        </nav>
    </div>
</section>

<!-- Reservation Section -->
<section class="reservation-section section">
    <div class="container">
        <div class="reservation-wrapper">

            <!-- Room Selection (if no room pre-selected) -->
            <?php if (!$selectedRoomId): ?>
                <div class="room-selection-card" data-aos="fade-up" data-aos-delay="200">
                    <h3><i class="fas fa-search"></i> Select Your Room</h3>
                    <p>Choose from our available rooms for your stay</p>

                    <div class="rooms-list">
                        <?php foreach ($allRooms as $roomOption): 
                            $isAvailable = ($roomOption['status'] ?? 'unavailable') === 'available';
                        ?>
                            <div class="room-option-card <?= $isAvailable ? '' : 'unavailable'; ?>"
                                 <?= $isAvailable
                                     ? "onclick=\"selectRoom(
                                            {$roomOption['id']},
                                            '" . htmlspecialchars($roomOption['type'], ENT_QUOTES) . "',
                                            {$roomOption['price']},
                                            {$roomOption['max_guests']}
                                        )\""
                                     : '' ?>>

                                <div class="room-option-image">
                                    <img src="/assets/images/rooms/room-default.jpg"
                                         alt="<?= htmlspecialchars($roomOption['type']); ?>">
                                </div>

                                <div class="room-option-details">
                                    <h4><?= htmlspecialchars($roomOption['type']); ?></h4>
                                    <p>Room #<?= htmlspecialchars($roomOption['room_number']); ?></p>

                                    <div class="room-option-price">
                                        <span class="price">$<?= number_format($roomOption['price'], 2); ?></span>
                                        <span class="unit">/night</span>
                                    </div>

                                    <div class="room-option-capacity">
                                        <i class="fas fa-users"></i>
                                        <?= $roomOption['max_guests']; ?>
                                        Guest<?= $roomOption['max_guests'] > 1 ? 's' : ''; ?>
                                    </div>
                                </div>

                                <?php if (!$isAvailable): ?>
                                    <div class="unavailable-overlay">
                                        <span>Currently Unavailable</span>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>


            <!-- Reservation Form -->
            <div class="reservation-form-card" data-aos="fade-up" data-aos-delay="400">
                <div class="form-header">
                    <h3><i class="fas fa-calendar-check"></i> Book Your Stay</h3>
                    <?php if ($room): ?>
                    <p>Complete your reservation for <?php echo htmlspecialchars($room['type']); ?></p>
                    <?php else: ?>
                    <p>Please select a room above to continue</p>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $errors['database']; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['availability'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $errors['availability']; ?>
                    </div>
                <?php endif; ?>
                
                <form action="reservation.php" method="POST" class="reservation-form" id="reservation-form">
                    <?php if ($room): ?>
                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="room_id" id="selected_room_id" value="">
                    <?php endif; ?>
                    
                    <!-- Selected Room Info -->
                    <?php if ($room): ?>
                    <div class="selected-room-info">
                        <div class="room-image">
                            <img src="assets/images/room-default.jpg" alt="<?php echo htmlspecialchars($room['type']); ?>">
                        </div>
                        <div class="room-details">
                            <h4><?php echo htmlspecialchars($room['type']); ?></h4>
                            <p>Room #<?php echo htmlspecialchars($room['room_number']); ?></p>
                            <p class="price">$<?php echo number_format($room['price'], 2); ?>/night</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Date Selection -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="check_in" class="form-label">
                                <i class="fas fa-calendar-alt"></i> Check-in Date *
                            </label>
                            <input type="date" 
                                   id="check_in" 
                                   name="check_in" 
                                   class="form-control <?php echo isset($errors['check_in']) ? 'error' : ''; ?>"
                                   value="<?php echo htmlspecialchars($checkIn ?: $today); ?>"
                                   min="<?php echo $today; ?>"
                                   required
                                   onchange="updateCheckoutMinDate(); updatePrice();">
                            <?php if (isset($errors['check_in'])): ?>
                                <span class="error-message"><?php echo $errors['check_in']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="check_out" class="form-label">
                                <i class="fas fa-calendar-alt"></i> Check-out Date *
                            </label>
                            <input type="date" 
                                   id="check_out" 
                                   name="check_out" 
                                   class="form-control <?php echo isset($errors['check_out']) ? 'error' : ''; ?>"
                                   value="<?php echo htmlspecialchars($checkOut ?: $tomorrow); ?>"
                                   min="<?php echo $tomorrow; ?>"
                                   required
                                   onchange="updatePrice();">
                            <?php if (isset($errors['check_out'])): ?>
                                <span class="error-message"><?php echo $errors['check_out']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Guest Information -->
                    <div class="form-section">
                        <h4><i class="fas fa-user"></i> Guest Information</h4>
                        
                        <div class="form-group">
                            <label for="guest_name" class="form-label">
                                <i class="fas fa-user"></i> Full Name *
                            </label>
                            <input type="text" 
                                   id="guest_name" 
                                   name="guest_name" 
                                   class="form-control <?php echo isset($errors['guest_name']) ? 'error' : ''; ?>"
                                   placeholder="Enter your full name"
                                   value="<?php echo isset($_POST['guest_name']) ? htmlspecialchars($_POST['guest_name']) : ''; ?>"
                                   required>
                            <?php if (isset($errors['guest_name'])): ?>
                                <span class="error-message"><?php echo $errors['guest_name']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address *
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                                       placeholder="your@email.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <span class="error-message"><?php echo $errors['email']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i> Phone Number *
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       class="form-control <?php echo isset($errors['phone']) ? 'error' : ''; ?>"
                                       placeholder="+1 (555) 123-4567"
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                       required>
                                <?php if (isset($errors['phone'])): ?>
                                    <span class="error-message"><?php echo $errors['phone']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="guests" class="form-label">
                                <i class="fas fa-users"></i> Number of Guests *
                            </label>
                            <select id="guests" 
                                    name="guests" 
                                    class="form-control <?php echo isset($errors['guests']) ? 'error' : ''; ?>"
                                    required
                                    onchange="updatePrice();">
                                <?php 
                                $maxGuests = $room ? $room['max_guests'] : 6;
                                for ($i = 1; $i <= $maxGuests; $i++): 
                                ?>
                                    <option value="<?php echo $i; ?>" 
                                            <?php echo (isset($_POST['guests']) && $_POST['guests'] == $i) || (!isset($_POST['guests']) && $guests == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <?php if (isset($errors['guests'])): ?>
                                <span class="error-message"><?php echo $errors['guests']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Special Requests -->
                    <div class="form-group">
                        <label for="special_requests" class="form-label">
                            <i class="fas fa-comment"></i> Special Requests (Optional)
                        </label>
                        <textarea id="special_requests" 
                                  name="special_requests" 
                                  class="form-control"
                                  rows="3"
                                  placeholder="Any special requests or requirements..."><?php echo isset($_POST['special_requests']) ? htmlspecialchars($_POST['special_requests']) : ''; ?></textarea>
                    </div>
                    
                    <!-- Price Summary -->
                    <?php if ($room): ?>
                    <div class="price-summary" id="price-summary">
                        <h4><i class="fas fa-receipt"></i> Price Summary</h4>
                        <div class="price-breakdown">
                            <div class="price-row">
                                <span>Room Rate:</span>
                                <span>$<?php echo number_format($room['price'], 2); ?>/night</span>
                            </div>
                            <div class="price-row">
                                <span>Duration:</span>
                                <span id="nights-display">
                                    <?php if (!empty($checkIn) && !empty($checkOut)): ?>
                                        <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>
                                    <?php else: ?>
                                        Select dates
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="price-row total">
                                <span>Total Price:</span>
                                <span id="total-price-display">
                                    $<?php echo number_format($totalPrice, 2); ?>
                                </span>
                            </div>
                        </div>
                        <p class="price-note">* Taxes and additional fees may apply</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Terms and Conditions -->
                    <div class="form-group terms-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                            <span>I agree to the <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Privacy Policy</a> *</span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" name="submit_reservation" class="btn btn-primary btn-lg btn-block btn-hover-scale">
                            <i class="fas fa-calendar-check"></i>
                            Confirm Reservation
                        </button>
                        <p class="form-note">* All fields marked with asterisk are required</p>
                    </div>
                </form>
            </div>
        </div>
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

/* Reservation Section */
.reservation-section {
    padding: var(--spacing-3xl) 0;
    background-color: var(--gray-100);
}

.reservation-wrapper {
    max-width: 900px;
    margin: 0 auto;
}

/* Room Selection Card */
.room-selection-card {
    background-color: var(--white);
    padding: var(--spacing-2xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: var(--spacing-xl);
}

.room-selection-card h3 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    color: var(--secondary-color);
}

.room-selection-card p {
    color: var(--gray-600);
    margin-bottom: var(--spacing-xl);
}

.rooms-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: var(--spacing-lg);
}

.room-option-card {
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
    cursor: pointer;
    transition: all var(--transition-base);
    position: relative;
}

.room-option-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.room-option-card.selected {
    border-color: var(--primary-color);
    box-shadow: var(--shadow-lg);
}

.room-option-card.unavailable {
    opacity: 0.6;
    cursor: not-allowed;
}

.room-option-card.unavailable:hover {
    transform: none;
    box-shadow: none;
}

.room-option-image {
    height: 150px;
    overflow: hidden;
}

.room-option-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.room-option-details {
    padding: var(--spacing-lg);
}

.room-option-details h4 {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-xs);
    color: var(--secondary-color);
}

.room-option-details p {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-sm);
}

.room-option-price {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-sm);
}

.room-option-price .price {
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--primary-color);
}

.room-option-price .unit {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.room-option-capacity {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.unavailable-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-weight: 500;
}

/* Reservation Form Card */
.reservation-form-card {
    background-color: var(--white);
    padding: var(--spacing-2xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}

.form-header {
    text-align: center;
    margin-bottom: var(--spacing-2xl);
    padding-bottom: var(--spacing-lg);
    border-bottom: 1px solid var(--gray-200);
}

.form-header h3 {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    color: var(--secondary-color);
}

.form-header p {
    color: var(--gray-600);
}

/* Selected Room Info */
.selected-room-info {
    display: flex;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-2xl);
    padding: var(--spacing-lg);
    background-color: var(--gray-50);
    border-radius: var(--radius-lg);
}

.room-image {
    flex-shrink: 0;
    width: 120px;
    height: 80px;
    border-radius: var(--radius-md);
    overflow: hidden;
}

.room-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.room-details h4 {
    margin-bottom: var(--spacing-xs);
    color: var(--secondary-color);
}

.room-details p {
    color: var(--gray-600);
    margin-bottom: var(--spacing-xs);
}

.room-details .price {
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--primary-color);
}

/* Form Sections */
.form-section {
    margin-bottom: var(--spacing-2xl);
}

.form-section h4 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    color: var(--secondary-color);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid var(--gray-200);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
}

/* Price Summary */
.price-summary {
    background-color: var(--gray-50);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-xl);
}

.price-summary h4 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    color: var(--secondary-color);
}

.price-breakdown {
    margin-bottom: var(--spacing-lg);
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-sm);
    font-size: var(--font-size-base);
}

.price-row.total {
    font-size: var(--font-size-lg);
    font-weight: 600;
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--gray-300);
    margin-top: var(--spacing-md);
}

.price-row span:first-child {
    color: var(--gray-600);
}

.price-row span:last-child {
    color: var(--secondary-color);
    font-weight: 500;
}

.price-note {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    font-style: italic;
}

/* Terms Group */
.terms-group {
    margin-bottom: var(--spacing-xl);
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-sm);
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    margin-top: 4px;
}

.checkbox-label span {
    color: var(--gray-700);
    line-height: 1.5;
}

.checkbox-label a {
    color: var(--primary-color);
    text-decoration: none;
}

.checkbox-label a:hover {
    text-decoration: underline;
}

/* Form Actions */
.form-actions {
    text-align: center;
}

.form-actions .btn {
    margin-bottom: var(--spacing-md);
}

.form-note {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
}

/* Alert Messages */
.alert {
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-lg);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.alert-danger {
    background-color: rgba(231, 76, 60, 0.1);
    color: var(--danger-color);
    border: 1px solid rgba(231, 76, 60, 0.2);
}

.alert-warning {
    background-color: rgba(243, 156, 18, 0.1);
    color: var(--warning-color);
    border: 1px solid rgba(243, 156, 18, 0.2);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .reservation-form-card {
        padding: var(--spacing-lg);
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .selected-room-info {
        flex-direction: column;
        text-align: center;
    }
    
    .room-image {
        width: 100%;
        height: 150px;
        margin-bottom: var(--spacing-md);
    }
    
    .rooms-list {
        grid-template-columns: 1fr;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<!-- JavaScript -->
<script>
// Global variables
let selectedRoom = null;
let roomPrice = <?php echo $room ? $room['price'] : 0; ?>;

// Select room function
function selectRoom(roomId, roomType, price, maxGuests) {
    selectedRoom = {
        id: roomId,
        type: roomType,
        price: price,
        maxGuests: maxGuests
    };
    
    roomPrice = price;
    
    // Update hidden input
    document.getElementById('selected_room_id').value = roomId;
    
    // Update UI
    document.querySelectorAll('.room-option-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    event.currentTarget.classList.add('selected');
    
    // Update price
    updatePrice();
    
    // Show success message
    showFlashMessage('Room selected successfully!', 'success');
}

// Update checkout minimum date
function updateCheckoutMinDate() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput.value) {
        const checkInDate = new Date(checkInInput.value);
        const nextDay = new Date(checkInDate);
        nextDay.setDate(nextDay.getDate() + 1);
        
        const nextDayStr = nextDay.toISOString().split('T')[0];
        checkOutInput.setAttribute('min', nextDayStr);
        
        // If checkout is before or same as checkin, update it
        if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
            checkOutInput.value = nextDayStr;
        }
    }
}

// Update price calculation
function updatePrice() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const nightsDisplay = document.getElementById('nights-display');
    const totalPriceDisplay = document.getElementById('total-price-display');
    
    if (checkInInput.value && checkOutInput.value && roomPrice > 0) {
        const checkInDate = new Date(checkInInput.value);
        const checkOutDate = new Date(checkOutInput.value);
        
        if (checkOutDate > checkInDate) {
            const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
            const totalPrice = roomPrice * nights;
            
            // Update displays
            nightsDisplay.textContent = nights + ' night' + (nights > 1 ? 's' : '');
            totalPriceDisplay.textContent = '$' + totalPrice.toFixed(2);
        }
    }
}

// Form validation
function validateReservationForm() {
    const form = document.getElementById('reservation-form');
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    // Validate email format
    const emailField = document.getElementById('email');
    if (emailField.value && !isValidEmail(emailField.value)) {
        showFieldError(emailField, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate phone format
    const phoneField = document.getElementById('phone');
    if (phoneField.value && !isValidPhone(phoneField.value)) {
        showFieldError(phoneField, 'Please enter a valid phone number');
        isValid = false;
    }
    
    // Validate dates
    const checkInField = document.getElementById('check_in');
    const checkOutField = document.getElementById('check_out');
    if (checkInField.value && checkOutField.value) {
        const checkInDate = new Date(checkInField.value);
        const checkOutDate = new Date(checkOutField.value);
        
        if (checkOutDate <= checkInDate) {
            showFieldError(checkOutField, 'Check-out date must be after check-in date');
            isValid = false;
        }
    }
    
    // Validate room selection
    if (!selectedRoom && !document.getElementById('selected_room_id').value) {
        showFlashMessage('Please select a room first', 'error');
        isValid = false;
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('error');
    
    const errorSpan = document.createElement('span');
    errorSpan.className = 'error-message';
    errorSpan.textContent = message;
    
    field.parentNode.appendChild(errorSpan);
}

// Enhanced email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Enhanced phone validation
function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
    return phoneRegex.test(phone);
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservation-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateReservationForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            showButtonLoading(submitBtn);
        });
    }
    
    // Initialize date pickers
    updateCheckoutMinDate();
    updatePrice();
});
</script>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>