<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

requireAuth(); // ✅ Enforce authentication
header('Content-Type: application/json');

// ✅ Sanitize and validate task ID
$taskId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$taskId || $taskId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit();
}

// ✅ Decode and validate CSRF token from JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

try {
    // ✅ Confirm task ownership before deletion
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$taskId, $_SESSION['user_id']]);
    $task = $stmt->fetch();

    if (!$task) {
        echo json_encode(['success' => false, 'error' => 'Task not found or access denied']);
        exit();
    }

    // ✅ Proceed with deletion
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);

    echo json_encode([
        'success' => true,
        'message' => 'Task deleted successfully'
    ]);
} catch (PDOException $e) {
    error_log("Task deletion error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
