<?php
session_start();
require 'database.php';

header('Content-Type: application/json');

$taskId = $_GET['id'] ?? 0;

$input = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("SELECT status FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    
    if (!$task) {
        echo json_encode(['success' => false, 'error' => 'Task not found']);
        exit();
    }
    
    $newStatus = ($task['status'] === 'completed') ? 'completed' : 'pending';
    
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $taskId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Task status updated successfully',
        'new_status' => $newStatus
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>