<?php
// Load DB config
require_once 'config/database.php';

// Connect to DB
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Truncate tables
$pdo->exec("TRUNCATE TABLE participants");
$pdo->exec("TRUNCATE TABLE group_list");

// Fetch group data from API
$apiUrl = 'https://whatsapp-waha.brandon.my/api/default/groups?sortBy=id';
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Insert data
if (!empty($data)) {
    foreach ($data as $group) {
        $groupName = $group['name'] ?? '';
        $groupId = $group['id']['_serialized'] ?? '';
        $groupDesc = $group['groupMetadata']['desc'] ?? '';

        $stmt = $pdo->prepare("INSERT INTO group_list (Group_Name, Group_ID, Group_Desc) VALUES (?, ?, ?)");
        $stmt->execute([$groupName, $groupId, $groupDesc]);

        $participants = $group['groupMetadata']['participants'] ?? [];
        foreach ($participants as $p) {
            $userId = $p['id']['_serialized'] ?? '';
            $userDisplayName = $userId;
            $userPhone = explode('@', $userId)[0] ?? '';

            $stmt2 = $pdo->prepare("INSERT INTO participants (Group_ID, User_ID, User_DisplayName, User_phone)
                                    VALUES (?, ?, ?, ?)");
            $stmt2->execute([$groupId, $userId, $userDisplayName, $userPhone]);
        }
    }

    echo "Daily update completed successfully.\n";
} else {
    echo "No group data found.\n";
}
