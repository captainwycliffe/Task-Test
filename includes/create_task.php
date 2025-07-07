<?php
session_start();
require 'database.php';

if (isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (strlen($title) < 100) {
    echo json_encode(['success' => false, 'error' => 'Task title must be at least 100 characters']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description) VALUES (" . $_SESSION['user_id'] . ", '$title', '$description')");
    $stmt->execute();
    
    $taskId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Task created successfully',
        'task' => $task
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Database error occurred';
}
?>