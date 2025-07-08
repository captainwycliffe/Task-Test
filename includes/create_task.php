<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

// ✅ Enforce authentication
requireAuth();

// ✅ Validate CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

// ✅ Sanitize and validate input
$title = sanitizeInput($_POST['title'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$dueDate = $_POST['due_date'] ?? null;
$priority = $_POST['priority'] ?? 'medium';
$attachmentPath = null;

if (strlen($title) < 3) {
    echo json_encode(['success' => false, 'error' => 'Task title must be at least 3 characters']);
    exit();
}

// Handle file upload
$attachmentPath = null;

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/';
    
    // ✅ Create the uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // 0755 permissions, recursive creation
    }

    // ✅ Generate a unique filename
    $originalName = basename($_FILES['attachment']['name']);
    $filename = uniqid('task_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
    $targetPath = $uploadDir . $filename;

    // ✅ Move the uploaded file
    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
        $attachmentPath = 'uploads/' . $filename;
    }
}


try {
    // ✅ Use parameterized query to prevent SQL injection
$stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority, attachment) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $title, $description, $dueDate, $priority, $attachmentPath]);

    $taskId = $pdo->lastInsertId();

    // ✅ Fetch the newly created task
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Task created successfully',
        'task' => $task
    ]);
} catch (Exception $e) {
    error_log("Task creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
