<?php
// sendAnnouncement.php
require 'config/database.php'; // Include your DB connection

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

if ($argc !== 2) {
    echo "Usage: php sendAnnouncement.php YYYY-MM-DD\n";
    exit(1);
}

$date = $argv[1];

// Get announcement(s) scheduled for today and not sent
$stmt = $pdo->prepare("SELECT * FROM announcement WHERE date = ? AND sent = 0");
$stmt->execute([$date]);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($announcements)) {
    echo "No announcement to send for {$date}.\n";
    exit;
}

// Get all contacts
$contactsStmt = $pdo->query("SELECT name, recipients FROM contacts WHERE recipients IS NOT NULL");
$contacts = $contactsStmt->fetchAll(PDO::FETCH_ASSOC);

// Send each announcement to each contact
foreach ($announcements as $announcement) {
    foreach ($contacts as $contact) {
        $message = $announcement['content'];
        $phone = $contact['recipients'];

        // Send via WhatsApp API
        sendWhatsApp($phone, $message);
    }

    // Mark as sent
    $updateStmt = $pdo->prepare("UPDATE announcement SET sent = 1 WHERE id = ?");
    $updateStmt->execute([$announcement['id']]);
}

function sendWhatsApp($phone, $message) {
    $url = 'https://whatsapp-waha.brandon.my/api/sendText';

    // If sending to individuals, make sure to format chatId as phone@c.us
    $chatId = $phone;

    $data = [
        "chatId" => $chatId,
        "reply_to" => null,
        "text" => $message,
        "linkPreview" => true,
        "linkPreviewHighQuality" => false,
        "session" => "default"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Optional: You can log or check if it fails
    if ($httpCode === 200) {
        echo "✅ Sent to {$phone}\n";
    } else {
        echo "❌ Failed to send to {$phone}: HTTP {$httpCode}\n";
        echo "Response: {$response}\n";
    }
}

?>
