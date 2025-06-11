<?php
if (!isset($argv[1])) {
    die("Usage: php sendReminder.php <taskID>\n");
}

$taskID = intval($argv[1]);

require 'config/database.php';

$sql = "SELECT * FROM daily_tasks WHERE id = $taskID";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Task not found.\n");
}

$task = $result->fetch_assoc();

$payload = json_encode($task);

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

echo "Sent: " . $payload . "\n";
