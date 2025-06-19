<?php
require_once 'functions.php';

// Set the HTTP_HOST for email links if running from command line
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['PHP_SELF'] = '/task-scheduler/src/cron.php';
}

$log_message = "Starting task reminder cron job at " . date('Y-m-d H:i:s') . "\n";

try {
    sendTaskReminders();
    $log_message .= "Task reminders sent successfully!\n";
} catch (Exception $e) {
    $log_message .= "Error sending task reminders: " . $e->getMessage() . "\n";
}

$log_message .= "Cron job completed at " . date('Y-m-d H:i:s') . "\n\n";

// Log to file
file_put_contents('cron.log', $log_message, FILE_APPEND | LOCK_EX);

// Output for command line
echo $log_message;
?>
