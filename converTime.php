<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include your DB config
require_once 'config/database.php'; // Make sure this has $mysqli = new mysqli(...);

// Connect to database
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Fetch all tasks
$query = "SELECT id, time FROM daily_tasks";
$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $taskTime12 = $row['task_time'];

    // Convert to 24-hour format
    $dateTime = DateTime::createFromFormat('h:i A', $taskTime12);
    if ($dateTime) {
        $taskTime24 = $dateTime->format('H:i');

        // Optional: update back into the table
        $update = $mysqli->prepare("UPDATE daily_tasks SET task_time = ? WHERE id = ?");
        $update->bind_param("si", $taskTime24, $id);
        $update->execute();

        echo "Task ID $id: '$taskTime12' converted to '$taskTime24'\n";
    } else {
        echo "Task ID $id: Invalid time format '$taskTime12'\n";
    }
}

$mysqli->close();
?>
