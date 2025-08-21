<?php
// index.php - Main router file

// Start session for authentication
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Include database configuration to get the password
require_once BASE_PATH . '/config/database.php';

// Authentication check
function requireAuth() {
    // Check if user is already authenticated via session
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        return true;
    }
    
    // Check if authentication credentials are provided
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Restricted Area"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Authentication required';
        exit;
    }
    
    // Validate credentials against database password
    global $db_pass;
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
    $current_user = $_GET['user_id'];
    
    // For basic auth, you might want to use a specific username or hardcode one
    // since the context only shows database password, not username
    if ($password === $db_pass &&  $username === $current_user ) {
        $_SESSION['authenticated'] = true;
        // Store session ID in cookie for persistence
        setcookie('PHPSESSID', session_id(), time() + 3600, '/'); // 1 hour expiration
        return true;
    }
    
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Invalid credentials';
    exit;
}

// Require authentication for all routes
requireAuth();

// Get the requested URI
$request_uri = $_SERVER['REQUEST_URI'];
$clean_uri = parse_url($request_uri, PHP_URL_PATH);
$clean_uri = rtrim($clean_uri, '/');

// Simple router
switch ($clean_uri) {
    case '/index.php/manageKnowledge':
        // Load the knowledge page
        require_once BASE_PATH . '/manageKnowledge.php';
        break;
    case '/index.php/manageGroup':
        // Load the manage group page
        require_once BASE_PATH . '/manageGroup.php';
        break;
    case '/index.php/manageContacts':
        // Load the manage contacts page
        require_once BASE_PATH . '/manageContacts.php';
        break;
    case '/index.php/manageContactDetails':
        // Load the manage contacts details page
        require_once BASE_PATH . '/manageContactDetails.php';
        break;
    case '/index.php/calendar':
        // Load the calendar page
        require_once BASE_PATH . '/calendar.php';
        break;
    case '/index.php/ticket':
        // Load the ticket page
        require_once BASE_PATH . '/ticket.php';
        break;
    case '/':
    case '':
        // Load the tasks page (default)
        require_once BASE_PATH . '/tasks.php';
        break;
        
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
?>