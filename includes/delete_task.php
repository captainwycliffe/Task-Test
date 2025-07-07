<?php
session_start();
require 'database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Get task ID from URL
$taskId = intval($_GET['id'] ?? 0);

if ($taskId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit();
}

// Get and validate JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

try {
    // Delete task (only if it belongs to the user)
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$taskId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Task deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Task not found or access denied']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>