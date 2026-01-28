<?php
/**
 * Hotel Reservation System
 * Admin Authentication & Security Helpers
 *
 * PHP 7.4+
 */

// --------------------------------------------------
// SAFE SESSION START
// --------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------
// CHECK ADMIN LOGIN STATUS
// --------------------------------------------------
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && (int) $_SESSION['admin_id'] > 0;
}

// --------------------------------------------------
// REQUIRE ADMIN LOGIN (GUARD)
// --------------------------------------------------
function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: /hotel-system/admin/login');
        exit;
    }
}

// --------------------------------------------------
// ADMIN LOGIN
// --------------------------------------------------
function adminLogin(PDO $pdo, string $username, string $password): array
{
    if (trim($username) === '' || trim($password) === '') {
        return [
            'success' => false,
            'message' => 'Username and password are required.'
        ];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                username,
                password,
                email,
                full_name
            FROM admin
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }

        // Store admin session
        $_SESSION['admin_id']        = (int) $admin['id'];
        $_SESSION['admin_username']  = $admin['username'];
        $_SESSION['admin_email']     = $admin['email'];
        $_SESSION['admin_full_name'] = $admin['full_name'];

        // Update last login
        $pdo->prepare("
            UPDATE admin 
            SET last_login = NOW() 
            WHERE id = :id
        ")->execute(['id' => $admin['id']]);

        return [
            'success' => true,
            'admin'   => $admin
        ];

    } catch (PDOException $e) {
        error_log('Admin login error: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'System error. Please try again later.'
        ];
    }
}

// --------------------------------------------------
// GET ADMIN DETAILS BY ID
// --------------------------------------------------
function getAdminDetails(PDO $pdo, int $adminId): array
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                username,
                email,
                full_name,
                last_login
            FROM admin
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        return $admin ?: [];

    } catch (PDOException $e) {
        error_log('getAdminDetails error: ' . $e->getMessage());
        return [];
    }
}

// --------------------------------------------------
// ADMIN LOGOUT
// --------------------------------------------------
function adminLogout(): void
{
    session_unset();
    session_destroy();

    header('Location: /hotel-system/admin/login');
    exit;
}

// --------------------------------------------------
// CSRF TOKEN HELPERS
// --------------------------------------------------
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}
