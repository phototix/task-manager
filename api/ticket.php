<?php
header("Content-Type: application/json");
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_GET['user_id'] ?? null;
$ticketId = $_GET['id'] ?? null;

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ($method) {
        case 'GET':
            if ($ticketId) {
                // Get specific ticket
                $stmt = $db->prepare("SELECT * FROM chatbot_inquiries WHERE id = ?");
                $stmt->execute([$ticketId]);
                $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($ticket ?: []);
            } elseif ($userId) {
                // Get tickets by user_id
                $stmt = $db->prepare("SELECT * FROM chatbot_inquiries WHERE user_id = ? ORDER BY inquiry_date DESC");
                $stmt->execute([$userId]);
                $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($tickets);
            } else {
                // Get all tickets
                $stmt = $db->query("SELECT * FROM chatbot_inquiries ORDER BY inquiry_date DESC");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;

        case 'POST':
            // Create a new ticket
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['user_id'], $data['name'], $data['issue'])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields"]);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO chatbot_inquiries (user_id, name, phone, email, company_name, issue, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['user_id'],
                $data['name'],
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['company_name'] ?? null,
                $data['issue'],
                $data['status'] ?? 'Pending'
            ]);

            echo json_encode(["message" => "Ticket created", "id" => $db->lastInsertId()]);
            break;

        case 'PUT':
            if (!$ticketId) {
                http_response_code(400);
                echo json_encode(["error" => "Missing ticket ID"]);
                exit;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            $fields = [];
            $params = [];

            foreach (['name', 'phone', 'email', 'company_name', 'issue', 'status'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(["error" => "No fields to update"]);
                exit;
            }

            $params[] = $ticketId;
            $sql = "UPDATE chatbot_inquiries SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            echo json_encode(["message" => "Ticket updated"]);
            break;

        case 'DELETE':
            if (!$ticketId) {
                http_response_code(400);
                echo json_encode(["error" => "Missing ticket ID"]);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM chatbot_inquiries WHERE id = ?");
            $stmt->execute([$ticketId]);

            echo json_encode(["message" => "Ticket deleted"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
