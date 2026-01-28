<?php
/**
 * Hotel Reservation System - Booking Confirmation Page
 * 
 * This page displays the booking confirmation details after a successful reservation.
 * It shows the reservation summary and provides confirmation to the guest.
 * 
 * PHP version 7.4+
 * 
 * @category Hotel_Reservation
 * @package  Public
 * @author   Hotel Reservation System
 * @license  MIT License
 */

// Include header
$pageTitle = 'Booking Confirmed - Luxury Hotel';
include __DIR__ . '/../includes/header.php';

// Check if reservation details exist in session
if (!isset($_SESSION['reservation_id']) || !isset($_SESSION['reservation_details'])) {
    // Redirect to homepage if no reservation data
    header("Location: index.php");
    exit();
}

// Get reservation details
$reservationId = $_SESSION['reservation_id'];
$reservation = $_SESSION['reservation_details'];

// Generate confirmation number
$confirmationNumber = 'LH' . date('Y') . str_pad($reservationId, 6, '0', STR_PAD_LEFT);

// Clear reservation data from session after displaying
unset($_SESSION['reservation_id']);
unset($_SESSION['reservation_details']);

// Format dates for display
$checkInDate = new DateTime($reservation['check_in']);
$checkOutDate = new DateTime($reservation['check_out']);
$formattedCheckIn = $checkInDate->format('F j, Y');
$formattedCheckOut = $checkOutDate->format('F j, Y');
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Reservation Confirmed</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">Confirmation</span>
        </nav>
    </div>
</section>

<!-- Confirmation Section -->
<section class="confirmation-section section">
    <div class="container">
        <div class="confirmation-wrapper">
            <!-- Success Message -->
            <div class="success-message" data-aos="fade-up" data-aos-delay="200">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Thank You, <?php echo htmlspecialchars($reservation['guest_name']); ?>!</h2>
                <p>Your reservation has been confirmed successfully</p>
            </div>
            
            <!-- Confirmation Details -->
            <div class="confirmation-card" data-aos="fade-up" data-aos-delay="400">
                <div class="confirmation-header">
                    <div class="confirmation-number">
                        <span class="label">Confirmation Number:</span>
                        <span class="number"><?php echo $confirmationNumber; ?></span>
                    </div>
                    <div class="status-badge">
                        <i class="fas fa-clock"></i>
                        Pending Confirmation
                    </div>
                </div>
                
                <div class="confirmation-body">
                    <!-- Reservation Details -->
                    <div class="detail-section">
                        <h3><i class="fas fa-info-circle"></i> Reservation Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="label">Guest Name:</span>
                                <span class="value"><?php echo htmlspecialchars($reservation['guest_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Email:</span>
                                <span class="value"><?php echo htmlspecialchars($reservation['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Phone:</span>
                                <span class="value"><?php echo htmlspecialchars($reservation['phone']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Room Type:</span>
                                <span class="value"><?php echo htmlspecialchars($reservation['room_type']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Check-in:</span>
                                <span class="value"><?php echo $formattedCheckIn; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Check-out:</span>
                                <span class="value"><?php echo $formattedCheckOut; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Duration:</span>
                                <span class="value"><?php echo $reservation['nights']; ?> night<?php echo $reservation['nights'] > 1 ? 's' : ''; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Number of Guests:</span>
                                <span class="value"><?php echo $reservation['guests']; ?> guest<?php echo $reservation['guests'] > 1 ? 's' : ''; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Special Requests -->
                    <?php if (!empty($reservation['special_requests'])): ?>
                    <div class="detail-section">
                        <h3><i class="fas fa-comment"></i> Special Requests</h3>
                        <p class="special-requests"><?php echo htmlspecialchars($reservation['special_requests']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Price Summary -->
                    <div class="detail-section price-section">
                        <h3><i class="fas fa-receipt"></i> Price Summary</h3>
                        <div class="price-breakdown">
                            <div class="price-row">
                                <span>Room Rate:</span>
                                <span>$<?php echo number_format($reservation['total_price'] / $reservation['nights'], 2); ?>/night</span>
                            </div>
                            <div class="price-row">
                                <span>Duration:</span>
                                <span><?php echo $reservation['nights']; ?> night<?php echo $reservation['nights'] > 1 ? 's' : ''; ?></span>
                            </div>
                            <div class="price-row total">
                                <span>Total Amount:</span>
                                <span class="total-amount">$<?php echo number_format($reservation['total_price'], 2); ?></span>
                            </div>
                        </div>
                        <p class="price-note">* Additional taxes and fees may apply at checkout</p>
                    </div>
                </div>
                
                <div class="confirmation-footer">
                    <div class="important-notice">
                        <h4><i class="fas fa-exclamation-triangle"></i> Important Information</h4>
                        <ul>
                            <li>Please save your confirmation number for future reference</li>
                            <li>Check-in time is 3:00 PM, check-out time is 11:00 AM</li>
                            <li>A valid ID and credit card will be required at check-in</li>
                            <li>You will receive a confirmation email shortly</li>
                            <li>For any changes or cancellations, please contact us</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="confirmation-actions" data-aos="fade-up" data-aos-delay="600">
                <button onclick="printConfirmation()" class="btn btn-outline btn-hover-scale">
                    <i class="fas fa-print"></i>
                    Print Confirmation
                </button>
                <a href="index.php" class="btn btn-primary btn-hover-scale">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </div>
            
            <!-- Contact Information -->
            <div class="contact-info" data-aos="fade-up" data-aos-delay="800">
                <h3>Need Help?</h3>
                <p>If you have any questions or need assistance with your reservation, please don't hesitate to contact us:</p>
                <div class="contact-methods">
                    <a href="tel:+15551234567" class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+1 (555) 123-4567</span>
                    </a>
                    <a href="mailto:reservations@luxuryhotel.com" class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>reservations@luxuryhotel.com</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Print Styles -->
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

/* Confirmation Section */
.confirmation-section {
    padding: var(--spacing-3xl) 0;
    background-color: var(--gray-100);
}

.confirmation-wrapper {
    max-width: 800px;
    margin: 0 auto;
}

/* Success Message */
.success-message {
    text-align: center;
    margin-bottom: var(--spacing-2xl);
    padding: var(--spacing-3xl);
    background-color: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}

.success-icon {
    font-size: var(--font-size-5xl);
    color: var(--success-color);
    margin-bottom: var(--spacing-lg);
}

.success-message h2 {
    font-size: var(--font-size-3xl);
    color: var(--secondary-color);
    margin-bottom: var(--spacing-md);
}

.success-message p {
    font-size: var(--font-size-lg);
    color: var(--gray-600);
}

/* Confirmation Card */
.confirmation-card {
    background-color: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: var(--spacing-xl);
}

.confirmation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-xl);
    background-color: var(--primary-color);
    color: var(--white);
}

.confirmation-number .label {
    display: block;
    font-size: var(--font-size-sm);
    opacity: 0.9;
    margin-bottom: var(--spacing-xs);
}

.confirmation-number .number {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    letter-spacing: 1px;
}

.status-badge {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    background-color: rgba(255, 255, 255, 0.2);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
}

.confirmation-body {
    padding: var(--spacing-2xl);
}

.detail-section {
    margin-bottom: var(--spacing-2xl);
}

.detail-section h3 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid var(--gray-200);
    color: var(--secondary-color);
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--gray-100);
}

.detail-item .label {
    font-weight: 500;
    color: var(--gray-600);
}

.detail-item .value {
    color: var(--secondary-color);
    font-weight: 500;
}

.special-requests {
    background-color: var(--gray-50);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    color: var(--gray-700);
    line-height: 1.6;
}

/* Price Section */
.price-section .price-breakdown {
    background-color: var(--gray-50);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-sm);
    padding: var(--spacing-sm) 0;
}

.price-row.total {
    font-size: var(--font-size-xl);
    font-weight: 700;
    padding-top: var(--spacing-md);
    border-top: 2px solid var(--primary-color);
    margin-top: var(--spacing-md);
    color: var(--primary-color);
}

.price-row.total .total-amount {
    font-size: var(--font-size-2xl);
}

.price-note {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    font-style: italic;
    margin-top: var(--spacing-md);
}

/* Confirmation Footer */
.confirmation-footer {
    padding: var(--spacing-xl);
    background-color: var(--gray-50);
}

.important-notice h4 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
    color: var(--warning-color);
}

.important-notice ul {
    list-style: none;
    padding-left: 0;
}

.important-notice li {
    position: relative;
    padding-left: var(--spacing-lg);
    margin-bottom: var(--spacing-sm);
    color: var(--gray-700);
}

.important-notice li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: var(--primary-color);
    font-weight: bold;
}

/* Confirmation Actions */
.confirmation-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
}

/* Contact Info */
.contact-info {
    text-align: center;
    padding: var(--spacing-2xl);
    background-color: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}

.contact-info h3 {
    color: var(--secondary-color);
    margin-bottom: var(--spacing-md);
}

.contact-info p {
    color: var(--gray-600);
    margin-bottom: var(--spacing-lg);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.contact-methods {
    display: flex;
    gap: var(--spacing-lg);
    justify-content: center;
    flex-wrap: wrap;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-md) var(--spacing-lg);
    background-color: var(--gray-50);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--secondary-color);
    transition: all var(--transition-base);
}

.contact-item:hover {
    background-color: var(--primary-color);
    color: var(--white);
    transform: translateY(-2px);
}

.contact-item i {
    color: var(--primary-color);
    transition: color var(--transition-base);
}

.contact-item:hover i {
    color: var(--white);
}

/* Print Styles */
@media print {
    .confirmation-actions,
    .contact-info,
    .navbar,
    .footer {
        display: none !important;
    }
    
    .confirmation-section {
        padding: 0;
    }
    
    .confirmation-card {
        box-shadow: none;
        border: 1px solid var(--gray-300);
    }
    
    .success-message {
        box-shadow: none;
        border: 1px solid var(--gray-300);
    }
}

/* Responsive Styles */
@media (max-width: 768px) {
    .confirmation-header {
        flex-direction: column;
        gap: var(--spacing-md);
        text-align: center;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .confirmation-actions {
        flex-direction: column;
    }
    
    .confirmation-actions .btn {
        width: 100%;
    }
    
    .contact-methods {
        flex-direction: column;
    }
}
</style>

<!-- JavaScript for print functionality -->
<script>
function printConfirmation() {
    // Open print dialog
    window.print();
}

// Add print-specific styles
const printStyles = `
    @media print {
        body {
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .confirmation-actions,
        .contact-info,
        .navbar,
        .footer,
        .back-to-top {
            display: none !important;
        }
        
        .confirmation-section {
            padding: 0;
        }
        
        .confirmation-card {
            box-shadow: none;
            border: 1px solid #ccc;
        }
        
        .success-message {
            box-shadow: none;
            border: 1px solid #ccc;
        }
        
        .page-header {
            background: #f8f9fa !important;
            color: #333 !important;
        }
        
        .confirmation-header {
            background: #f8f9fa !important;
            color: #333 !important;
        }
    }
`;

// Inject print styles
const styleSheet = document.createElement('style');
styleSheet.textContent = printStyles;
document.head.appendChild(styleSheet);
</script>

<?php
// Include footer
include __DIR__ . '/../includes/footer.php';
?>