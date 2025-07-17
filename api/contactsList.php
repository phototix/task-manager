<?php
require_once '../config/database.php';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die(json_encode(["error" => $e->getMessage()]));
}

$stmt = $pdo->query("SELECT recipients, name, topics, lang FROM contacts ORDER BY id DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));