<?php
session_start();
require 'includes/database.php';

$csrf_token = generateCSRFToken();

try {
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $taskIds = $stmt->fetchAll();
    
    $tasks = [];
    foreach ($taskIds as $taskId) {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId['id']]);
        $tasks[] = $stmt->fetch();
    }
} catch (PDOException $e) {
    $tasks = [];
    $error = 'Error fetching tasks.';
}

$pending_count = 0;
$completed_count = 0;
foreach ($tasks as $task) {
    if ($task['status'] === 'pending') {
        $pending_count++;
    } else {
        $completed_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f8f9fa; }
        
        .navbar {
            background: #343a40;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .navbar h1 { color: white; }
        .navbar-right { display: flex; align-items: center; gap: 15px; }
        .navbar-right span { color: #adb5bd; }
        .navbar-right a { color: #dc3545; text-decoration: none; }
        .navbar-right a:hover { text-decoration: underline; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number { font-size: 2rem; font-weight: bold; margin-bottom: 5px; }
        .stat-label { color: #6c757d; }
        .stat-pending { color: #ffc107; }
        .stat-completed { color: #28a745; }
        .stat-total { color: #007bff; }
        
        .task-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .task-form h3 { margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        
        .tasks-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .task-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.2s;
        }
        
        .task-card:hover { transform: translateY(-2px); }
        
        .task-card.completed {
            opacity: 0.7;
            background: #f8f9fa;
        }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .task-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .task-card.completed .task-title {
            text-decoration: line-through;
            color: #6c757d;
        }
        
        .task-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        
        .task-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .task-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .task-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .no-tasks {
            text-align: center;
            color: #6c757d;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .navbar-content { flex-direction: column; gap: 10px; }
            .stats { grid-template-columns: 1fr; }
            .tasks-container { grid-template-columns: 1fr; }
            .task-actions { justify-content: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <h1>Task Manager</h1>
            <div class="navbar-right">
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div id="alerts"></div>
        
        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number stat-pending"><?= $pending_count ?></div>
                <div class="stat-label">Pending Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-completed"><?= $completed_count ?></div>
                <div class="stat-label">Completed Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-total"><?= count($tasks) ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
        </div>
        
        <!-- Task Creation Form -->
        <div class="task-form">
            <h3>Create New Task</h3>
            <form id="taskForm" action="includes/create_task.php" method="POST">
                <div class="form-group">
                    <label for="title">Task Title:</label>
                    <input type="text" id="title" name="title" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3" placeholder="Optional task description"></textarea>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="btn btn-primary">Add Task</button>
            </form>
        </div>
        
        <!-- Tasks List -->
        <div class="tasks-container" id="tasksContainer">
            <?php if (empty($tasks)): ?>
                <div class="no-tasks">
                    <h3>No tasks yet!</h3>
                    <p>Create your first task using the form above.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card <?= $task['status'] === 'completed' ? 'completed' : '' ?>" id="task-<?= $task['id'] ?>">
                        <div class="task-header">
                            <h4 class="task-title"><?= htmlspecialchars($task['title']) ?></h4>
                            <span class="task-status status-<?= $task['status'] ?>">
                                <?= $task['status'] ?>
                            </span>
                        </div>
                        
                        <?php if ($task['description']): ?>
                            <div class="task-description">
                                <?= nl2br(htmlspecialchars($task['description'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="task-meta">
                            Created: <?= date('M j, Y g:i A', strtotime($task['created_at'])) ?>
                        </div>
                        
                        <div class="task-actions">
                            <button class="btn btn-secondary" onclick="toggleStatus(<?= $task['id'] ?>)">
                                <?= $task['status'] === 'pending' ? 'Mark Complete' : 'Mark Pending' ?>
                            </button>
                            <button class="btn btn-danger" onclick="deleteTask(<?= $task['id'] ?>)">
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // CSRF token for AJAX requests
        const csrfToken = '<?= $csrf_token ?>';
        
        // Show alert message
        function showAlert(message, type = 'success') {
            const alertsContainer = document.getElementById('alerts');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alertsContainer.appendChild(alert);
            
            // Remove alert after 3 seconds
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
        
        // Toggle task status
        async function toggleStatus(taskId) {
            const taskCard = document.getElementById(`task-${taskId}`);
            taskCard.classList.add('loading');
            
            try {
                const response = await fetch(`includes/update_task.php?id=${taskId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ csrf_token: csrfToken })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update UI
                    taskCard.classList.toggle('completed');
                    const statusSpan = taskCard.querySelector('.task-status');
                    const actionBtn = taskCard.querySelector('.btn-secondary');
                    
                    if (taskCard.classList.contains('completed')) {
                        statusSpan.textContent = 'completed';
                        statusSpan.className = 'task-status status-completed';
                        actionBtn.textContent = 'Mark Pending';
                    } else {
                        statusSpan.textContent = 'pending';
                        statusSpan.className = 'task-status status-pending';
                        actionBtn.textContent = 'Mark Complete';
                    }
                    
                    // Update stats
                    updateStats();
                    showAlert('Task status updated successfully!');
                } else {
                    showAlert(data.error || 'Failed to update task status', 'error');
                }
            } catch (error) {
                showAlert('An error occurred while updating the task', 'error');
            } finally {
                taskCard.classList.remove('loading');
            }
        }
        
        // Delete task
        async function deleteTask(taskId) {
            if (!confirm('Are you sure you want to delete this task?')) {
                return;
            }
            
            const taskCard = document.getElementById(`task-${taskId}`);
            taskCard.classList.add('loading');
            
            try {
                const response = await fetch(`includes/delete_task.php?id=${taskId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ csrf_token: csrfToken })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove task from UI
                    taskCard.remove();
                    updateStats();
                    showAlert('Task deleted successfully!');
                    
                    // Check if no tasks remain
                    const remainingTasks = document.querySelectorAll('.task-card');
                    if (remainingTasks.length === 0) {
                        document.getElementById('tasksContainer').innerHTML = `
                            <div class="no-tasks">
                                <h3>No tasks yet!</h3>
                                <p>Create your first task using the form above.</p>
                            </div>
                        `;
                    }
                } else {
                    showAlert(data.error || 'Failed to delete task', 'error');
                }
            } catch (error) {
                showAlert('An error occurred while deleting the task', 'error');
            } finally {
                taskCard.classList.remove('loading');
            }
        }
        
        // Update statistics
        function updateStats() {
            const tasks = document.querySelectorAll('.task-card');
            const completedTasks = document.querySelectorAll('.task-card.completed');
            const pendingTasks = tasks.length - completedTasks.length;
            
            document.querySelector('.stat-pending .stat-number').textContent = pendingTasks;
            document.querySelector('.stat-completed .stat-number').textContent = completedTasks.length;
            document.querySelector('.stat-total .stat-number').textContent = tasks.length;
        }
        
        // Handle task form submission
        document.getElementById('taskForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Adding...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('includes/create_task.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Clear form
                    this.reset();
                    
                    // Add new task to UI
                    const tasksContainer = document.getElementById('tasksContainer');
                    const noTasks = tasksContainer.querySelector('.no-tasks');
                    
                    if (noTasks) {
                        noTasks.remove();
                    }
                    
                    const taskHtml = `
                        <div class="task-card" id="task-${data.task.id}">
                            <div class="task-header">
                                <h4 class="task-title">${escapeHtml(data.task.title)}</h4>
                                <span class="task-status status-pending">pending</span>
                            </div>
                            ${data.task.description ? `<div class="task-description">${escapeHtml(data.task.description).replace(/\n/g, '<br>')}</div>` : ''}
                            <div class="task-meta">
                                Created: ${new Date(data.task.created_at).toLocaleString()}
                            </div>
                            <div class="task-actions">
                                <button class="btn btn-secondary" onclick="toggleStatus(${data.task.id})">
                                    Mark Complete
                                </button>
                                <button class="btn btn-danger" onclick="deleteTask(${data.task.id})">
                                    Delete
                                </button>
                            </div>
                        </div>
                    `;
                    
                    tasksContainer.insertAdjacentHTML('afterbegin', taskHtml);
                    updateStats();
                    showAlert('Task created successfully!');
                } else {
                    showAlert(data.error || 'Failed to create task', 'error');
                }
            } catch (error) {
                showAlert('An error occurred while creating the task', 'error');
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
        
        // Utility function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>