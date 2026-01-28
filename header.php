<?php
/**
 * Luxury Hotel Reservation System
 * Global Header Template
 * Compatible with PHP 7.4+
 */

// ==================================================
// SESSION INITIALIZATION
// ==================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// CORE DEPENDENCIES
// ==================================================
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth.php';

// ==================================================
// BASE APPLICATION URL
// ==================================================
define('BASE_URL', '/hotel-system/');

// ==================================================
// REQUEST CONTEXT
// ==================================================
$requestUri  = strtok($_SERVER['REQUEST_URI'], '?');
$currentPage = basename($requestUri) ?: 'index';
$isAdminPage = strpos($requestUri, '/admin/') !== false;

// ==================================================
// PAGE TITLE MAPPING
// ==================================================
$pageTitles = [
    'index'       => 'Luxury Hotel — Welcome',
    'rooms'       => 'Our Rooms — Luxury Hotel',
    'reservation' => 'Make a Reservation — Luxury Hotel',
    'confirm'     => 'Booking Confirmed — Luxury Hotel',
    'login'       => 'Admin Login — Luxury Hotel',
    'dashboard'   => 'Admin Dashboard — Luxury Hotel',
    'bookings'    => 'Reservation Management — Luxury Hotel',
];

// Fallback title
$pageTitle = $pageTitles[$currentPage] ?? 'Luxury Hotel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Experience luxury, comfort, and world-class hospitality at Luxury Hotel.">
    <meta name="author" content="Luxury Hotel">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- ==================================================
         FAVICON
    ================================================== -->
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon.ico">

    <!-- ==================================================
         STYLESHEETS
    ================================================== -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/animations.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/responsive.css">

    <!-- ==================================================
         ICONS & FONTS
    ================================================== -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- ==================================================
         AOS (ANIMATE ON SCROLL)
    ================================================== -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
</head>

<body class="<?= $isAdminPage ? 'admin-layout' : 'public-layout' ?> page-<?= htmlspecialchars($currentPage, ENT_QUOTES, 'UTF-8') ?>">
