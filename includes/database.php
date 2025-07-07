<?php
$host = 'localhost';
$dbname = 'task_manager';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . " on line " . __LINE__);
}
// rectifying the csrf token generation with the help of random_bytes and stronger hashing algorithm
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] =bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return $_SESSION['csrf_token'] === $token;
}

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'login.php');
        exit();
    }
}