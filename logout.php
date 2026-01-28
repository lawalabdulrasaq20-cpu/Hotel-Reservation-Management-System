<?php
/**
 * Hotel Reservation System - Admin Logout
 * 
 * Handles admin logout process, clears session data,
 * and redirects to login page.
 * 
 * PHP version 7.4+
 * 
 * @category Hotel_Reservation
 * @package  Admin
 * @author   Hotel Reservation System
 * @license  MIT License
 */

// Start session
session_start();

// Include auth functions
require_once __DIR__ . '/../includes/auth.php';

// Logout admin
adminLogout();

// Redirect to login page
header("Location: login.php");
exit();
?>