<?php
require_once 'functions.php';

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        $task_name = $_POST['task-name'];
        if (addTask($task_name)) {
            $message = 'Task added successfully!';
        } else {
            $message = 'Failed to add task. It might be a duplicate or empty.';
        }
    }
    
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        if (subscribeEmail($email)) {
            $message = 'Verification email sent! Please check your inbox.';
        } else {
            $message = 'Failed to subscribe. Email might be invalid or already subscribed.';
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'toggle_task' && isset($_GET['id']) && isset($_GET['completed'])) {
        $result = markTaskAsCompleted($_GET['id'], $_GET['completed'] === 'true');
        echo json_encode(['success' => $result]);
        exit;
    }
    
    if ($_GET['action'] === 'delete_task' && isset($_GET['id'])) {
        $result = deleteTask($_GET['id']);
        echo json_encode(['success' => $result]);
        exit;
    }
}

$tasks = getAllTasks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        h1, h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        .delete-task {
            background-color: #dc3545;
            padding: 5px 10px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .delete-task:hover {
            background-color: #c82333;
        }
        
        .tasks-list {
            list-style: none;
            padding: 0;
        }
        
        .task-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .task-item.completed {
            background: #d4edda;
            border-left-color: #28a745;
            text-decoration: line-through;
            opacity: 0.7;
        }
        
        .task-content {
            display: flex;
            align-items: center;
            flex-grow: 1;
        }
        
        .task-status {
            margin-right: 10px;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .no-tasks {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task Scheduler</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <h2>Add New Task</h2>
        <form method="POST">
            <div class="form-group">
                <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
            </div>
            <button type="submit" id="add-task">Add Task</button>
        </form>
    </div>
    
    <div class="container">
        <h2>Tasks</h2>
        <?php if (empty($tasks)): ?>
            <div class="no-tasks">No tasks yet. Add your first task above!</div>
        <?php else: ?>
            <ul class="tasks-list">
                <?php foreach ($tasks as $task): ?>
                    <li class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
                        <div class="task-content">
                            <input type="checkbox" class="task-status" 
                                   <?php echo $task['completed'] ? 'checked' : ''; ?>
                                   onchange="toggleTask('<?php echo $task['id']; ?>', this.checked)">
                            <span><?php echo htmlspecialchars($task['name']); ?></span>
                        </div>
                        <button class="delete-task" onclick="deleteTask('<?php echo $task['id']; ?>')">Delete</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2>Email Notifications</h2>
        <p>Subscribe to receive hourly email reminders for pending tasks.</p>
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email address" required>
            </div>
            <button type="submit" id="submit-email">Submit</button>
        </form>
    </div>

    <script>
        function toggleTask(taskId, isCompleted) {
            fetch(`?action=toggle_task&id=${taskId}&completed=${isCompleted}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update task');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
        }
        
        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                fetch(`?action=delete_task&id=${taskId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete task');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred');
                    });
            }
        }
    </script>
</body>
</html>
