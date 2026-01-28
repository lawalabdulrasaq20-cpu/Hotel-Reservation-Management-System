<?php
/**
 * Hotel Reservation System
 * Email Helper (PHPMailer)
 * PHP 7.4+
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --------------------------------------------------
// LOAD PHPMailer CLASSES
// --------------------------------------------------
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

// --------------------------------------------------
// SEND RESERVATION CONFIRMATION EMAIL
// --------------------------------------------------
function sendReservationEmail(array $data): bool
{
    // Safety check
    if (empty($data['email']) || empty($data['guest_name'])) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // --------------------------------------------------
        // SMTP CONFIGURATION
        // --------------------------------------------------
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com';   // 🔴 CHANGE
        $mail->Password   = 'your_app_password';      // 🔴 CHANGE (Gmail App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --------------------------------------------------
        // EMAIL HEADERS
        // --------------------------------------------------
        $mail->setFrom('your_email@gmail.com', 'Luxury Hotel');
        $mail->addAddress($data['email'], $data['guest_name']);

        // --------------------------------------------------
        // EMAIL CONTENT
        // --------------------------------------------------
        $mail->isHTML(true);
        $mail->Subject = 'Reservation Confirmation - Luxury Hotel';

        $mail->Body = '
            <h2>Reservation Confirmed</h2>

            <p>Dear <strong>' . htmlspecialchars($data['guest_name']) . '</strong>,</p>

            <p>Your reservation has been successfully received. Below are your booking details:</p>

            <table cellpadding="8" cellspacing="0" border="1" width="100%">
                <tr>
                    <td><strong>Reservation ID</strong></td>
                    <td>#' . $data['reservation_id'] . '</td>
                </tr>
                <tr>
                    <td><strong>Room Type</strong></td>
                    <td>' . htmlspecialchars($data['room_type']) . '</td>
                </tr>
                <tr>
                    <td><strong>Check-in</strong></td>
                    <td>' . $data['check_in'] . '</td>
                </tr>
                <tr>
                    <td><strong>Check-out</strong></td>
                    <td>' . $data['check_out'] . '</td>
                </tr>
                <tr>
                    <td><strong>Nights</strong></td>
                    <td>' . $data['nights'] . '</td>
                </tr>
                <tr>
                    <td><strong>Total Price</strong></td>
                    <td>$' . number_format($data['total_price'], 2) . '</td>
                </tr>
            </table>

            <p>We look forward to welcoming you.</p>

            <p>
                <strong>Luxury Hotel</strong><br>
                Thank you for choosing us.
            </p>
        ';

        // --------------------------------------------------
        // SEND EMAIL
        // --------------------------------------------------
        return $mail->send();

    } catch (Exception $e) {
        error_log('Reservation email error: ' . $mail->ErrorInfo);
        return false;
    }
}
