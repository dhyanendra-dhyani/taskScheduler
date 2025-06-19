<?php

function addTask($task_name) {
    $task_name = trim($task_name);
    if (empty($task_name)) {
        return false;
    }
    
    $tasks = getAllTasks();
    
    // Check for duplicates
    foreach ($tasks as $task) {
        if (strtolower($task['name']) === strtolower($task_name)) {
            return false; // Duplicate task
        }
    }
    
    $new_task = [
        'id' => uniqid(),
        'name' => $task_name,
        'completed' => false
    ];
    
    $tasks[] = $new_task;
    
    return saveToFile('tasks.txt', $tasks);
}

function getAllTasks() {
    return loadFromFile('tasks.txt', []);
}

function markTaskAsCompleted($task_id, $is_completed) {
    $tasks = getAllTasks();
    
    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = (bool)$is_completed;
            return saveToFile('tasks.txt', $tasks);
        }
    }
    
    return false;
}

function deleteTask($task_id) {
    $tasks = getAllTasks();
    
    foreach ($tasks as $index => $task) {
        if ($task['id'] === $task_id) {
            unset($tasks[$index]);
            $tasks = array_values($tasks); // Reindex array
            return saveToFile('tasks.txt', $tasks);
        }
    }
    
    return false;
}

function generateVerificationCode() {
    return sprintf('%06d', mt_rand(100000, 999999));
}

function subscribeEmail($email) {
    $email = trim(strtolower($email));
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check if already subscribed
    $subscribers = getSubscribers();
    if (in_array($email, $subscribers)) {
        return false;
    }
    
    // Check if already pending
    $pending = getPendingSubscriptions();
    if (isset($pending[$email])) {
        return false;
    }
    
    $code = generateVerificationCode();
    $pending[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    
    if (!saveToFile('pending_subscriptions.txt', $pending)) {
        return false;
    }
    
    // Send verification email
    $base_url = getBaseUrl();
    $verification_link = $base_url . "/verify.php?email=" . urlencode($email) . "&code=" . $code;
    
    $subject = "Verify subscription to Task Planner";
    $body = '<p>Click the link below to verify your subscription to Task Planner:</p>' . "\n" .
            '<p><a id="verification-link" href="' . $verification_link . '">Verify Subscription</a></p>';
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";
    
    return mail($email, $subject, $body, $headers);
}

function verifySubscription($email, $code) {
    $email = trim(strtolower($email));
    $pending = getPendingSubscriptions();
    
    if (!isset($pending[$email]) || $pending[$email]['code'] !== $code) {
        return false;
    }
    
    // Move from pending to subscribers
    $subscribers = getSubscribers();
    $subscribers[] = $email;
    
    unset($pending[$email]);
    
    $success1 = saveToFile('subscribers.txt', $subscribers);
    $success2 = saveToFile('pending_subscriptions.txt', $pending);
    
    return $success1 && $success2;
}

function unsubscribeEmail($email) {
    $email = trim(strtolower($email));
    $subscribers = getSubscribers();
    
    $index = array_search($email, $subscribers);
    if ($index !== false) {
        unset($subscribers[$index]);
        $subscribers = array_values($subscribers);
        return saveToFile('subscribers.txt', $subscribers);
    }
    
    return false;
}

function sendTaskReminders() {
    $subscribers = getSubscribers();
    $tasks = getAllTasks();
    
    // Get pending tasks
    $pending_tasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });
    
    if (empty($pending_tasks)) {
        return;
    }
    
    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pending_tasks);
    }
}

function sendTaskEmail($email, $pending_tasks) {
    $base_url = getBaseUrl();
    $unsubscribe_link = $base_url . "/unsubscribe.php?email=" . urlencode($email);
    
    $subject = "Task Planner - Pending Tasks Reminder";
    
    $body = '<h2>Pending Tasks Reminder</h2>' . "\n" .
            '<p>Here are the current pending tasks:</p>' . "\n" .
            '<ul>' . "\n";
    
    foreach ($pending_tasks as $task) {
        $body .= '<li>' . htmlspecialchars($task['name']) . '</li>' . "\n";
    }
    
    $body .= '</ul>' . "\n" .
             '<p><a id="unsubscribe-link" href="' . $unsubscribe_link . '">Unsubscribe from notifications</a></p>';
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";
    
    return mail($email, $subject, $body, $headers);
}

function getSubscribers() {
    return loadFromFile('subscribers.txt', []);
}

function getPendingSubscriptions() {
    return loadFromFile('pending_subscriptions.txt', []);
}

// Helper functions for file operations
function loadFromFile($filename, $default = []) {
    if (!file_exists($filename)) {
        return $default;
    }
    
    $content = file_get_contents($filename);
    if (empty($content)) {
        return $default;
    }
    
    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

function saveToFile($filename, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filename, $json) !== false;
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['PHP_SELF'] ?? '/task-scheduler/src');
    
    return $protocol . '://' . $host . $path;
}
?>
