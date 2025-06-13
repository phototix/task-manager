<?php
header("Content-Type: application/json");
require_once '../config/database.php'; // Include your database connection

// Handle PUT requests
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $groupId = isset($_GET['group_id']) ? $_GET['group_id'] : null;
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    if (!$groupId) {
        http_response_code(400);
        echo json_encode(['error' => 'Group ID is required']);
        exit;
    }

    switch ($action) {
        case 'picture':
            updateGroupPicture($groupId, $input);
            break;
        case 'description':
            updateGroupDescription($groupId, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }
}

function updateGroupPicture($groupId, $data) {
    global $pdo;
    
    if (!isset($data['file']['url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Image URL is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE group_list SET group_Picture = ? WHERE Group_ID = ?");
        $stmt->execute([$data['file']['url'], $groupId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Group picture updated successfully',
            'data' => [
                'group_id' => $groupId,
                'image_url' => $data['file']['url']
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateGroupDescription($groupId, $data) {
    global $pdo;
    
    if (!isset($data['description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Description is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE group_list SET Group_Desc = ? WHERE Group_ID = ?");
        $stmt->execute([$data['description'], $groupId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Group description updated successfully',
            'data' => [
                'group_id' => $groupId,
                'description' => $data['description']
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Handle invalid methods
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);