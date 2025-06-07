<?php
header("Content-Type: application/json");
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_GET['user_id'] ?? null;

// Get task ID if provided
$taskId = $_GET['id'] ?? null;

// Connect to database
$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

switch ($method) {
    case 'GET':
        // Get all tasks for the user for today
        if ($userId) {
            $query = "SELECT * FROM daily_tasks WHERE user_id = :user_id AND task_date = CURDATE() ORDER BY priority, time";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($tasks);
        } 
        // Get single task by ID
        elseif ($taskId) {
            $query = "SELECT * FROM daily_tasks WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $taskId);
            $stmt->execute();
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode($task);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing user_id or task id']);
        }
        break;
        
    case 'POST':
        // Add new task
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id is required']);
            break;
        }
        
        $query = "INSERT INTO daily_tasks (user_id, task_description, priority, remarks, time) 
                  VALUES (:user_id, :task_description, :priority, :remarks, :time)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':task_description', $data['task_description']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':remarks', $data['remarks']);
        $stmt->bindParam(':time', $data['time']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create task']);
        }
        break;
        
    case 'PUT':
        // Update existing task
        if (!$taskId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Task ID is required']);
            break;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "UPDATE daily_tasks SET 
                  task_description = :task_description,
                  priority = :priority,
                  remarks = :remarks,
                  time = :time,
                  is_completed = :is_completed
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $taskId);
        $stmt->bindParam(':task_description', $data['task_description']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':remarks', $data['remarks']);
        $stmt->bindParam(':time', $data['time']);
        $stmt->bindParam(':is_completed', $data['is_completed']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update task']);
        }
        break;
        
    case 'PATCH':
        // Partial update (used for marking complete/incomplete)
        if (!$taskId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Task ID is required']);
            break;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "UPDATE daily_tasks SET is_completed = :is_completed WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $taskId);
        $stmt->bindParam(':is_completed', $data['is_completed']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
        }
        break;
        
    case 'DELETE':
        // Delete task
        if (!$taskId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Task ID is required']);
            break;
        }
        
        $query = "DELETE FROM daily_tasks WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $taskId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
