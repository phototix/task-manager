<?php
// Database connection
$pdo = new PDO('mysql:host=db.gateway.01.webbypage.com;dbname=daily_coach', 'webbycms', '#Abccy1982#');

// Get all tasks with time values
$stmt = $pdo->prepare("SELECT id, time FROM daily_tasks WHERE time IS NOT NULL AND time != ''");
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 1. Define the existing cron jobs that must be preserved
$protectedCronJobs = [
    "0 9 * * * php /home/ubuntu/dailydose.php",
    "0 5 * * * /home/ubuntu/backup_database.sh",
    "50 2 * * * php -f /var/www/cloud.i-dc.institute/occ files:scan --all && php -f /var/www/cloud.webbypage.com/occ files:scan --all",
    "0 3 * * * php -f /var/www/cloud.i-dc.institute/occ maintenance:mode --on && php -f /var/www/cloud.webbypage.com/occ maintenance:mode --on",
    "15 3 * * * php -f /var/www/cloud.i-dc.institute/occ maintenance:mode --off && php -f /var/www/cloud.webbypage.com/occ maintenance:mode --off"
];

// 2. Get current crontab and filter out our task reminders
exec('crontab -l', $currentCrontabLines);
$preservedLines = [];
$foundOurSection = false;

foreach ($currentCrontabLines as $line) {
    $line = trim($line);
    
    // Skip empty lines and comments (except our marker)
    if (empty($line) || (strpos($line, '#') === 0 && strpos($line, '# Task-Reminder') === false)) {
        $preservedLines[] = $line;
        continue;
    }
    
    // Check if this is one of our protected cron jobs
    $isProtected = false;
    foreach ($protectedCronJobs as $protectedJob) {
        if (strpos($line, $protectedJob) !== false) {
            $isProtected = true;
            break;
        }
    }
    
    if ($isProtected) {
        $preservedLines[] = $line;
    }
}

// 3. Get all tasks with time values from database
$stmt = $pdo->prepare("SELECT id, time FROM daily_tasks WHERE time IS NOT NULL AND time != '' AND is_completed=0");
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Build the new crontab
$newCrontab = array_merge(
    $preservedLines,
    ["# Task-Reminder - Auto-generated tasks. Do not edit manually"]
);

foreach ($tasks as $task) {
    $timeString = trim($task['time']);
    $timestamp = strtotime($timeString);
    
    if ($timestamp === false) {
        error_log("Invalid time format for task ID {$task['id']}: {$timeString}");
        continue;
    }
    
    $hour = date('G', $timestamp);    // 24-hour without leading zeros
    $minute = date('i', $timestamp);  // Minutes with leading zeros
    
    $newCrontab[] = "$minute $hour * * * php /var/www/task-manager/sendReminder.php?taskID={$task['id']}";
}

// 5. Save the new crontab
$tempFile = tempnam(sys_get_temp_dir(), 'cron');
file_put_contents($tempFile, implode(PHP_EOL, $newCrontab) . PHP_EOL);
exec("crontab $tempFile");
unlink($tempFile);

echo "Cron jobs updated successfully. Preserved existing jobs and added " . count($tasks) . " task reminders.";
