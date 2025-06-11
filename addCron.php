<?php
// Database connection
$pdo = new PDO('mysql:host=db.gateway.01.webbypage.com;dbname=daily_coach', 'webbycms', '#Abccy1982#');

// Get all tasks with time values
$stmt = $pdo->prepare("SELECT id, time FROM daily_tasks WHERE time IS NOT NULL AND time != ''");
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 1. Get current crontab and remove all task reminder lines
exec('crontab -l', $crontabLines);
$cleanedCrontab = [];
$foundMarker = false;

foreach ($crontabLines as $line) {
    if (strpos($line, '# Task-Reminder - Auto-generated tasks. Do not edit manually') !== false) {
        $foundMarker = true;
        break; // Stop including lines after we find the marker
    }
    $cleanedCrontab[] = $line;
}

// If we didn't find the marker, keep the entire original crontab
if (!$foundMarker) {
    $cleanedCrontab = $crontabLines;
}

// Add header comment
$newCrontab[] = "# Task-Reminder - Auto-generated tasks. Do not edit manually";

// Process each task
foreach ($tasks as $task) {
    // Parse time string (format: "HH:MM AM/PM" or "HH:MM")
    $timeString = trim($task['time']);
    $timestamp = strtotime($timeString);
    
    if ($timestamp === false) {
        error_log("Invalid time format for task ID {$task['id']}: {$timeString}");
        continue;
    }
    
    $hour = date('G', $timestamp);    // 24-hour without leading zeros
    $minute = date('i', $timestamp);  // Minutes with leading zeros
    
    // Add to crontab
    $cronCmd = "$minute $hour * * * php /var/www/task-manager/sendReminder.php?taskID={$task['id']}";
    $newCrontab[] = $cronCmd;
}

// Save new crontab
$tempFile = tempnam(sys_get_temp_dir(), 'cron');
file_put_contents($tempFile, implode(PHP_EOL, $newCrontab) . PHP_EOL);
exec("crontab $tempFile");
unlink($tempFile);

echo "Cron jobs updated successfully for " . count($tasks) . " tasks";
