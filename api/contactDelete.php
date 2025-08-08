<?php
require_once '../config/database.php';

try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die(json_encode(["error" => $e->getMessage()]));
}

$recipients = $_GET['recipients'] ?? '';
if (!$recipients) {
  echo json_encode(["status" => "fail", "message" => "Missing recipients"]);
  exit;
}

$stmt = $pdo->prepare("UPDATE contacts SET status='0' WHERE recipients = ?");
$success = $stmt->execute([$recipients]);

echo json_encode([
  "status" => $success ? "ok" : "fail",
  "message" => $success ? "Deleted successfully" : "Failed to delete"
]);
