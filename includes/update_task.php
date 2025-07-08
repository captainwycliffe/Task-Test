<?php
require_once 'database.php';
require_once 'functions.php';

requireAuth();
header('Content-Type: application/json');

$taskId = $_GET['id'] ?? 0;
$input = json_decode(file_get_contents('php://input'), true);

// Validate CSRF token
if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

// Check task ownership
$stmt = $pdo->prepare("SELECT status FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$taskId, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    echo json_encode(['success' => false, 'error' => 'Task not found or unauthorized']);
    exit();
}

// Toggle status
$newStatus = ($task['status'] === 'completed') ? 'pending' : 'completed';

$stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $taskId]);

echo json_encode([
    'success' => true,
    'message' => 'Task status updated successfully',
    'new_status' => $newStatus
]);

?>