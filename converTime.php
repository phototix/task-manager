<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create your own mysqli connection here, without touching database.php
$db_host = 'db.gateway.01.webbypage.com';
$db_name = 'daily_coach';
$db_user = 'webbycms';
$db_pass = '#Abccy1982#';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Fetch tasks
$query = "SELECT id, time FROM daily_tasks";
$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $taskTime12 = $row['time'];

    // Convert 12-hour to 24-hour format
    $dateTime = DateTime::createFromFormat('h:i A', $taskTime12);
    if ($dateTime) {
        $taskTime24 = $dateTime->format('H:i');

        $update = $mysqli->prepare("UPDATE daily_tasks SET time = ? WHERE id = ?");
        $update->bind_param("si", $taskTime24, $id);
        $update->execute();

        echo "Task ID $id: $taskTime12 → $taskTime24\n";
    } else {
        echo "Invalid format for ID $id: '$taskTime12'\n";
    }
}

$mysqli->close();
?>
