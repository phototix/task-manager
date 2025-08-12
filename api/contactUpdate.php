<?php
require_once '../config/database.php';

try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die(json_encode(["error" => $e->getMessage()]));
}

$data = $_POST;
$sql = "UPDATE contacts SET name = ?, topics = ?, contact_type = ?, lang = ?, systemPrompt = ? WHERE recipients = ?";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
  $data['name'],
  $data['topics'],
  $data['contact_type'],
  $data['lang'],
  $data['recipients'],
  $data['systemPrompt']
]);

echo json_encode([
  "status" => $success ? "ok" : "fail",
  "message" => $success ? "Updated successfully" : "Failed to update"
]);
