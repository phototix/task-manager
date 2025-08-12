<?php
require_once '../config/database.php';

try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die(json_encode(["error" => $e->getMessage()]));
}

$recipients = $_GET['user_id'] ?? '';
if (!$recipients) {
  echo json_encode(["error" => "Missing user_id"]);
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM contacts WHERE recipients = ?");
$stmt->execute([$recipients]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
