<?php
if (!isset($_GET['taskID'])) {
    die("taskID required.");
}

$taskID = intval($_GET['taskID']);

require 'config/database.php';

$sql = "SELECT * FROM daily_tasks WHERE id = $taskID";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Task not found.");
}

$task = $result->fetch_assoc();

$payload = json_encode($task);

// Replace with 'webhook' for production
$webhookUrl = "https://n8n.brandon.my/webhook-test/8372396e-90e2-4078-9ca8-c7d49bf19e31";

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

echo "Sent: " . $payload;
?>
