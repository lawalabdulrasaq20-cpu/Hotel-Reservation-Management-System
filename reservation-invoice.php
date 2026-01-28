<?php
/**
 * Reservation Invoice PDF (With QR Code)
 */

// --------------------------------------------------
// START SESSION (SAFE)
// --------------------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// --------------------------------------------------
// PREVENT DIRECT ACCESS
// --------------------------------------------------
if (empty($_SESSION['reservation_data'])) {
    header('Location: index.php');
    exit;
}

// --------------------------------------------------
// FETCH RESERVATION DATA
// --------------------------------------------------
$data = $_SESSION['reservation_data'];

// --------------------------------------------------
// GENERATE QR CODE (CHECK-IN)
// --------------------------------------------------
$qrPayload = json_encode([
    'reservation_id' => $data['reservation_id'],
    'guest_name'     => $data['guest_name'],
    'check_in'       => $data['check_in'],
    'check_out'      => $data['check_out']
]);

$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrPayload);

// --------------------------------------------------
// LOAD TCPDF
// --------------------------------------------------
require_once __DIR__ . '/includes/tcpdf/tcpdf.php';

// --------------------------------------------------
// CREATE PDF
// --------------------------------------------------
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('Luxury Hotel');
$pdf->SetAuthor('Luxury Hotel');
$pdf->SetTitle('Reservation Invoice');

$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// --------------------------------------------------
// PDF CONTENT
// --------------------------------------------------
$html = '

<h2 style="text-align:center;">Abdulrasaq Luxury Hotel</h2>
<p style="text-align:center; font-size:12px;">Reservation Invoice</p>
<hr>

<h4>Guest Information</h4>
<p>
<strong>Reservation ID:</strong> #' . htmlspecialchars($data['reservation_id']) . '<br>
<strong>Guest Name:</strong> ' . htmlspecialchars($data['guest_name']) . '<br>
<strong>Email:</strong> ' . htmlspecialchars($data['email']) . '<br>
<strong>Phone:</strong> ' . htmlspecialchars($data['phone']) . '
</p>

<br>

<h4>Reservation Details</h4>
<table border="1" cellpadding="6" width="100%">
<tr style="background-color:#f2f2f2;">
    <th><strong>Room Type</strong></th>
    <th><strong>Check-in</strong></th>
    <th><strong>Check-out</strong></th>
    <th><strong>Nights</strong></th>
    <th><strong>Total Price</strong></th>
</tr>
<tr>
    <td>' . htmlspecialchars($data['room_type']) . '</td>
    <td>' . htmlspecialchars($data['check_in']) . '</td>
    <td>' . htmlspecialchars($data['check_out']) . '</td>
    <td align="center">' . (int)$data['nights'] . '</td>
    <td align="right">$' . number_format($data['total_price'], 2) . '</td>
</tr>
</table>

<br>

<p><strong>Status:</strong> ' . htmlspecialchars($data['status']) . '</p>

<hr>

<h4>Important Information</h4>
<ul>
    <li>Please save your confirmation number</li>
    <li>Check-in time is 3:00 PM | Check-out time is 11:00 AM</li>
    <li>A valid ID and credit card are required at check-in</li>
    <li>A confirmation email has been sent</li>
    <li>Contact us for any changes or cancellations</li>
</ul>

<hr>

<h4 style="text-align:center;">Mobile Check-In QR Code</h4>
<p style="text-align:center;">
Scan this QR code at the front desk or self check-in kiosk.
</p>

<p style="text-align:center;">
<img src="' . $qrCodeUrl . '" width="140">
</p>

<p style="text-align:center; font-size:11px; color:#555;">
This QR code is unique to your reservation.
</p>

<br>

<p style="text-align:center;">
Thank you for choosing <strong>Abdulrasaq Luxury Hotel</strong>.<br>
We wish you a pleasant stay.
</p>
';

// --------------------------------------------------
// WRITE PDF
// --------------------------------------------------
$pdf->writeHTML($html, true, false, true, false, '');

// --------------------------------------------------
// OUTPUT PDF
// --------------------------------------------------
$pdf->Output('reservation_invoice.pdf', 'I');
exit;
