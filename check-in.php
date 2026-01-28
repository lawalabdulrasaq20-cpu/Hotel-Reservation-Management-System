<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

if (empty($_GET['code'])) {
    die('Invalid check-in code.');
}

$code = $_GET['code'];

// Example: validate QR code (adjust to your DB)
$stmt = $pdo->prepare("
    SELECT r.*
    FROM reservations r
    WHERE CONCAT('CHK-', r.id, '-', MD5(r.email)) = ?
");
$stmt->execute([$code]);
$reservation = $stmt->fetch();

if (!$reservation) {
    die('Reservation not found or invalid QR code.');
}

// Mark as checked in
$update = $pdo->prepare("
    UPDATE reservations
    SET status = 'checked_in'
    WHERE id = ?
");
$update->execute([$reservation['id']]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check-In Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5 text-center">
    <div class="card shadow p-4">
        <h2 class="text-success">
            <i class="fas fa-check-circle"></i> Check-In Successful
        </h2>

        <p class="mt-3">
            Welcome, <strong><?= htmlspecialchars($reservation['guest_name']) ?></strong>
        </p>

        <p>
            Room Type: <strong><?= htmlspecialchars($reservation['room_type']) ?></strong>
        </p>

        <p class="text-muted">
            Enjoy your stay!
        </p>
    </div>
</div>

</body>
</html>
