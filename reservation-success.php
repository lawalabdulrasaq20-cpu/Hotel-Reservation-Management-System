<?php
// --------------------------------------------------
// START SESSION (SAFE)
// --------------------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// --------------------------------------------------
// PREVENT DIRECT ACCESS
// --------------------------------------------------
if (
    empty($_SESSION['reservation_success']) ||
    empty($_SESSION['reservation_data'])
) {
    header('Location: index.php');
    exit;
}

// --------------------------------------------------
// FETCH RESERVATION DATA
// --------------------------------------------------
$data = $_SESSION['reservation_data'];

// --------------------------------------------------
// GENERATE QR CODE FOR CHECK-IN
// --------------------------------------------------
$qrPayload = json_encode([
    'reservation_id' => $data['reservation_id'],
    'guest_name'     => $data['guest_name'],
    'check_in'       => $data['check_in'],
    'check_out'      => $data['check_out']
]);

$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrPayload);

// Prevent refresh reuse
unset($_SESSION['reservation_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Successful</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow border-0">

                    <!-- HEADER -->
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-1">
                            <i class="fas fa-check-circle me-2"></i>
                            Reservation Successful
                        </h2>
                        <p class="mb-0">Thank you for your booking</p>
                    </div>

                    <!-- BODY -->
                    <div class="card-body p-4">

                        <h5 class="text-primary mb-3">
                            <i class="fas fa-receipt me-2"></i>
                            Reservation Details
                        </h5>

                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Reservation ID</span>
                                <strong>#<?= htmlspecialchars($data['reservation_id']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Guest Name</span>
                                <strong><?= htmlspecialchars($data['guest_name']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Email</span>
                                <strong><?= htmlspecialchars($data['email']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Phone</span>
                                <strong><?= htmlspecialchars($data['phone']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Room Type</span>
                                <strong><?= htmlspecialchars($data['room_type']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Check-in Date</span>
                                <strong><?= htmlspecialchars($data['check_in']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Check-out Date</span>
                                <strong><?= htmlspecialchars($data['check_out']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Nights</span>
                                <strong><?= htmlspecialchars($data['nights']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Guests</span>
                                <strong><?= htmlspecialchars($data['guests']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Price</span>
                                <strong class="text-success">
                                    $<?= number_format($data['total_price'], 2) ?>
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Status</span>
                                <span class="badge bg-warning text-dark">
                                    <?= htmlspecialchars($data['status']) ?>
                                </span>
                            </li>
                        </ul>

                        <!-- IMPORTANT INFO -->
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Important Information
                            </h6>
                            <ul class="mb-0 ps-3">
                                <li>Please save your confirmation number</li>
                                <li>Check-in: 3:00 PM | Check-out: 11:00 AM</li>
                                <li>Valid ID required at check-in</li>
                                <li>A confirmation email will be sent shortly</li>
                                <li>Contact us for changes or cancellations</li>
                            </ul>
                        </div>

                        <!-- QR CODE CHECK-IN -->
                        <div class="card mt-4 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-qrcode me-2"></i>
                                    Mobile Check-In QR Code
                                </h5>
                                <p class="text-muted mb-3">
                                    Present this QR code at the front desk or self check-in kiosk.
                                </p>
                                <img src="<?= $qrCodeUrl ?>" alt="Check-in QR Code" class="img-fluid mb-3" style="max-width:200px;">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This QR code is unique to your reservation.
                                </div>
                            </div>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                            <button type="button" onclick="window.print()" class="btn btn-outline-secondary">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home me-1"></i> Back to Home
                            </a>
                            <a href="reservation-invoice.php" target="_blank" class="btn btn-success">
                                <i class="fas fa-file-pdf me-1"></i> Download Invoice
                            </a>
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="card-footer text-center bg-light">
                        <h6 class="mb-2">Need Help?</h6>
                        <p class="mb-1">
                            <i class="fas fa-phone me-1"></i> +234 (555) 123-4567
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-1"></i> info@Abdulrasaqluxuryhotel.com
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

</body>
</html>
