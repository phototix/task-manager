<?php
header("Content-Type: application/json");
require_once '../config/database.php';

// Database connection
$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get request parameters
$userId = $_GET['user_id'] ?? null;

// Validate required parameters
if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'user_id are required']);
    exit;
}

// Function to get user details
function getUserDetails($db, $userId) {
    try {
        $query = "SELECT id, name, email, contact_type, lang, topics 
                  FROM contacts 
                  WHERE recipients = :user_id 
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

// Get user details
$userDetails = getUserDetails($db, $userId);

if ($userDetails) {
    // Success response
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $userDetails['id'],
            'name' => $userDetails['name'],
            'email' => $userDetails['email'],
            'type' => $userDetails['contact_type'],
            'language' => $userDetails['lang'] ?? 'en',
            'topics' => $userDetails['topics'] ? json_decode($userDetails['topics'], true) : []
        ]
    ]);
} else {
    // User not found
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found in contacts'
    ]);
}
