<?php
if (!isset($argv[1])) {
    die("Usage: php sendReminder.php <taskID>\n");
}

$taskID = intval($argv[1]);

// Include DB config
require 'config/database.php';

// Create DB connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

// Fetch task
$sql = "SELECT * FROM daily_tasks WHERE id = $taskID";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Task not found.\n");
}

$task = $result->fetch_assoc();

$payload = json_encode($task);

// Send to webhook
$webhookUrl = "https://n8n.ezy.chat/webhook/8372396e-90e2-4078-9ca8-c7d49bf19e31";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
curl_close($ch);

echo "Sent: " . $payload . "\n";

// Mark Completed task
$sql = "UPDATE daily_tasks SET is_completed='1' WHERE id = $taskID AND task_repeat = '0'";
$result = $conn->query($sql);
?>
