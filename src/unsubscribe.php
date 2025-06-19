<?php
require_once 'functions.php';

$message = '';
$success = false;

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    if (unsubscribeEmail($email)) {
        $message = 'You have been successfully unsubscribed from task reminders.';
        $success = true;
    } else {
        $message = 'Email not found in our subscription list.';
    }
} else {
    $message = 'Invalid unsubscribe link.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Task Scheduler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success {
            color: #28a745;
        }
        
        .error {
            color: #dc3545;
        }
        
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Unsubscribe</h1>
        <p class="<?php echo $success ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
        <a href="index.php" class="btn">Back to Task Scheduler</a>
    </div>
</body>
</html>
