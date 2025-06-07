<?php
// index.php - Main router file

// Define base path
define('BASE_PATH', __DIR__);

// Get the requested URI
$request_uri = $_SERVER['REQUEST_URI'];
$clean_uri = parse_url($request_uri, PHP_URL_PATH);
$clean_uri = rtrim($clean_uri, '/');

// Simple router
switch ($clean_uri) {
        
    case '/':
        
    default:
        // Handle 404 Not Found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found'
        ]);
        break;
}