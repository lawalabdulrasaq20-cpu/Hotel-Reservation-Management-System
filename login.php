<?php
session_start();

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$username = '';
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors['login'] = 'Invalid request.';
    } else {

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '') {
            $errors['username'] = 'Username is required';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required';
        }

        if (!$errors) {
            $result = adminLogin($pdo, $username, $password);

            if ($result['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $errors['login'] = $result['message'];
            }
        }
    }
}

$csrfToken = generateCSRFToken();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hotel Reservation System - Admin Login">
    <title>Admin Login - Luxury Hotel</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /* Admin Login Styles */
        body {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-dark)),
                        url('../assets/images/admin-bg.jpg') center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            padding: var(--spacing-2xl) var(--spacing-xl) var(--spacing-xl);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
        }
        
        .login-header .logo {
            font-size: var(--font-size-3xl);
            margin-bottom: var(--spacing-md);
        }
        
        .login-header h1 {
            font-size: var(--font-size-2xl);
            margin-bottom: var(--spacing-sm);
            color: var(--white);
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: var(--font-size-sm);
        }
        
        .login-form {
            padding: var(--spacing-2xl);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .input-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            z-index: 2;
        }
        
        .input-group .form-control {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-md) var(--spacing-md) 3rem;
            font-size: var(--font-size-base);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            transition: all var(--transition-base);
            background-color: var(--gray-50);
        }
        
        .input-group .form-control:focus {
            border-color: var(--primary-color);
            background-color: var(--white);
            box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.1);
            outline: none;
        }
        
        .input-group .form-control.error {
            border-color: var(--danger-color);
            background-color: rgba(231, 76, 60, 0.05);
        }
        
        .error-message {
            display: block;
            margin-top: var(--spacing-xs);
            font-size: var(--font-size-sm);
            color: var(--danger-color);
        }
        
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
        
        .btn-login {
            width: 100%;
            padding: var(--spacing-md);
            font-size: var(--font-size-base);
            font-weight: 600;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-base);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .login-footer {
            text-align: center;
            padding: var(--spacing-xl);
            border-top: 1px solid var(--gray-200);
            background-color: var(--gray-50);
        }
        
        .login-footer p {
            font-size: var(--font-size-sm);
            color: var(--gray-600);
            margin-bottom: var(--spacing-sm);
        }
        
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: var(--spacing-md);
        }
        
        .forgot-password a {
            color: var(--gray-600);
            text-decoration: none;
            font-size: var(--font-size-sm);
        }
        
        .forgot-password a:hover {
            color: var(--primary-color);
        }
        
        /* Password visibility toggle */
        .password-toggle {
            position: absolute;
            right: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            z-index: 2;
            transition: color var(--transition-base);
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: var(--white);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: var(--spacing-sm);
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                margin: 0;
            }
            
            .login-header {
                padding: var(--spacing-xl) var(--spacing-lg) var(--spacing-lg);
            }
            
            .login-form {
                padding: var(--spacing-xl) var(--spacing-lg);
            }
            
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Login Header -->
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-hotel"></i>
            </div>
            <h1>Admin Login</h1>
            <p>Welcome back! Please sign in to your account.</p>
        </div>
        
      <!-- Login Form -->
<form method="POST" action="login.php" class="login-form" id="login-form">

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

    <!-- Global Error -->
    <?php if (!empty($errors['login'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $errors['login']; ?>
        </div>
    <?php endif; ?>

    <!-- Username -->
    <div class="form-group">
        <label for="username">Username</label>
        <div class="input-group">
            <i class="fas fa-user input-icon"></i>
            <input
                type="text"
                id="username"
                name="username"
                class="form-control <?php echo isset($errors['username']) ? 'error' : ''; ?>"
                placeholder="Enter your username"
                value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
                autofocus
            >
        </div>
        <?php if (isset($errors['username'])): ?>
            <span class="error-message"><?php echo $errors['username']; ?></span>
        <?php endif; ?>
    </div>

    <!-- Password -->
    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                placeholder="Enter your password"
                required
            >
            <button type="button" class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <?php if (isset($errors['password'])): ?>
            <span class="error-message"><?php echo $errors['password']; ?></span>
        <?php endif; ?>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt"></i> Sign In
    </button>

    <!-- Forgot Password -->
    <div class="forgot-password">
        <a href="#">Forgot your password?</a>
    </div>

</form>


        
        <!-- Login Footer -->
        <div class="login-footer">
            <p>Default Login:</p>
            <p><strong>Username:</strong> admin | <strong>Password:</strong> admin123</p>
            <p><a href="../index.php">← Back to Hotel Website</a></p>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const loginBtn = document.getElementById('login-btn');
            
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(el => el.remove());
                document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
                
                let hasErrors = false;
                
                if (!username) {
                    showError('username', 'Please enter your username');
                    hasErrors = true;
                }
                
                if (!password) {
                    showError('password', 'Please enter your password');
                    hasErrors = true;
                }
                
                if (hasErrors) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span class="spinner"></span> Signing in...';
            });
            
            function showError(fieldId, message) {
                const field = document.getElementById(fieldId);
                field.classList.add('error');
                
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error-message';
                errorSpan.textContent = message;
                
                field.parentNode.parentNode.appendChild(errorSpan);
            }
        });
    </script>
</body>
</html>