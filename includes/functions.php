<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a secure CSRF token and store it in session
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token from form or AJAX
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require user authentication
 */
function requireAuth() {
    if (!isset($_SESSION['user_id']) || !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)) {
        header('Location: login.php');
        exit();
    }

    // Optional: Session fingerprinting
    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    } elseif ($_SESSION['fingerprint'] !== hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'])) {
        session_destroy();
        header('Location: login.php');
        exit();
    }

    // Optional: Session timeout
    $timeout = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    $_SESSION['last_activity'] = time();
}


/**
 * Sanitize user input (e.g., from $_POST or $_GET)
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for safe HTML rendering
 */
function escapeHTML($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
