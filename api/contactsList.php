<?php
require_once '../config/database.php';

try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die(json_encode(["error" => $e->getMessage()]));
}

$stmt = $pdo->query("SELECT id, recipients, name, topics, lang FROM contacts WHERE status='1' ORDER BY id DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
