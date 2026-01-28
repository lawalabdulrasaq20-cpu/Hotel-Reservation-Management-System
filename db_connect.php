<?php
/**
 * Hotel Reservation System - Database Connection
 *
 * Establishes a secure PDO connection and provides
 * helper utilities used across the system.
 *
 * PHP version 7.4+
 */

// ==========================
// DATABASE CONFIGURATION
// ==========================
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel_reservation_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ==========================
// CREATE DATABASE CONNECTION
// ==========================
function getDBConnection(): PDO
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $options);

    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        die('<h2>Database connection error. Please try again later.</h2>');
    }
}

// ==========================
// CLOSE CONNECTION
// ==========================
function closeDBConnection(?PDO &$pdo): void
{
    $pdo = null;
}

// ==========================
// INPUT SANITIZATION
// ==========================
function sanitizeInput(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// ==========================
// VALIDATION HELPERS
// ==========================
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone(string $phone): bool
{
    $phone = preg_replace('/\D/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

// ==========================
// PRICE CALCULATION
// ==========================
function calculateTotalPrice(float $pricePerNight, string $checkIn, string $checkOut): float
{
    $in  = new DateTime($checkIn);
    $out = new DateTime($checkOut);
    $nights = max(1, $in->diff($out)->days);

    return $pricePerNight * $nights;
}

// ==========================
// ROOM AVAILABILITY CHECK
// ==========================
function isRoomAvailable(PDO $pdo, int $roomId, string $checkIn, string $checkOut, ?int $excludeId = null): bool
{
    $sql = "
        SELECT COUNT(*) 
        FROM reservations
        WHERE room_id = :room_id
          AND status IN ('confirmed','pending')
          AND (check_in < :check_out AND check_out > :check_in)
    ";

    if ($excludeId) {
        $sql .= " AND id != :exclude_id";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':room_id'   => $roomId,
        ':check_in'  => $checkIn,
        ':check_out' => $checkOut,
        ':exclude_id'=> $excludeId
    ]);

    return $stmt->fetchColumn() == 0;
}

// ==========================
// GET AVAILABLE ROOMS
// ==========================
function getAvailableRooms(PDO $pdo, string $checkIn, string $checkOut, int $guests, string $roomType = ''): array
{
    $sql = "
        SELECT r.*
        FROM rooms r
        WHERE r.status = 'available'
          AND r.max_guests >= :guests
          AND (:type = '' OR r.type = :type)
          AND NOT EXISTS (
              SELECT 1 FROM reservations res
              WHERE res.room_id = r.id
                AND res.status IN ('confirmed','pending')
                AND res.check_in < :check_out
                AND res.check_out > :check_in
          )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':guests'    => $guests,
        ':type'      => $roomType,
        ':check_in'  => $checkIn,
        ':check_out' => $checkOut
    ]);

    return $stmt->fetchAll();
}

// ==========================
// INITIALIZE PDO (IMPORTANT)
// ==========================
$pdo = getDBConnection();
