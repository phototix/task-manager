<?php
// index.php - Main router file with authentication
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Include database configuration
require_once BASE_PATH . '/config/database.php';

// Database connection using your config
function getDBConnection() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", 
                $db_user, 
                $db_pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed'
            ]);
            exit;
        }
    }
    return $db;
}

// Enhanced authentication function with security improvements
function authenticateUser() {
    // Check if already authenticated via session
    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
        // Validate session token against cookie for added security
        if (isset($_COOKIE['auth_token']) && $_COOKIE['auth_token'] === $_SESSION['auth_token']) {
            return true;
        }
    }
    
    // Check for authentication attempt
    if (isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        
        // Validate user_id format (basic sanitization)
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $userId)) {
            return false;
        }
        
        if (isset($_POST['password'])) {
            $password = $_POST['password'];
            
            // Basic validation
            if (empty($_POST['password'])) {
                return false;
            }
            
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT secret FROM contacts WHERE recipients = :recipients");
                $stmt->bindParam(':recipients', $userId, PDO::PARAM_STR);
                $stmt->execute();
                
                $result = $stmt->fetch();
                
                if ($result && md5($_POST['password']) === $result['secret']) {
                    // Authentication successful
                    $_SESSION['loggedIn'] = true;
                    $_SESSION['user_id'] = $userId;
                    
                    // Create a more secure token
                    $authToken = bin2hex(random_bytes(32));
                    $_SESSION['auth_token'] = $authToken;
                    
                    // Set cookie that expires in 1 day (secure settings)
                    setcookie('auth_token', $authToken, [
                        'expires' => time() + 86400,
                        'path' => '/',
                        'secure' => true,    // Send only over HTTPS
                        'httponly' => true,  // Not accessible via JavaScript
                        'samesite' => 'Strict' // Prevent CSRF
                    ]);
                    
                    return true;
                }
            } catch (PDOException $e) {
                error_log("Database error during authentication: " . $e->getMessage());
            }
        }
    }
    
    // Not authenticated
    return false;
}

// Check authentication for protected routes
$protectedRoutes = [
    '/index.php/manageKnowledge',
    '/index.php/manageGroup',
    '/index.php/manageContacts',
    '/index.php/manageContactDetails',
    '/index.php/calendar',
    '/index.php/ticket',
    '/'
];

// Get the requested URI
$request_uri = $_SERVER['REQUEST_URI'];
$clean_uri = parse_url($request_uri, PHP_URL_PATH);
$clean_uri = rtrim($clean_uri, '/');

// Check if route is protected and requires authentication
if (in_array($clean_uri, $protectedRoutes)) {
    if (!authenticateUser()) {
        // Show login form if not authenticated
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Failed login attempt
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication failed. Please check your credentials.' . $_POST['user_id'].$userId.$_POST['password'].md5($_POST['password']).$result['secret']
            ]);
            exit;
        }
        
        // Show login form with CSRF protection
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Login Required - Daily Coach</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; }
                .login-form { background: #f9f9f9; padding: 20px; border-radius: 5px; }
                .form-group { margin-bottom: 15px; }
                label { display: block; margin-bottom: 5px; }
                input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; }
                input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
                button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
                button:hover { background: #45a049; }
            </style>
        </head>
        <body>
            <div class="login-form">
                <h1>Login Required</h1>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="user_id" value="<?php echo htmlspecialchars($_GET['user_id'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Simple router
switch ($clean_uri) {
    case '/index.php/manageKnowledge':
        require_once BASE_PATH . '/manageKnowledge.php';
        break;
    case '/index.php/manageGroup':
        require_once BASE_PATH . '/manageGroup.php';
        break;
    case '/index.php/manageContacts':
        require_once BASE_PATH . '/manageContacts.php';
        break;
    case '/index.php/manageContactDetails':
        require_once BASE_PATH . '/manageContactDetails.php';
        break;
    case '/index.php/calendar':
        require_once BASE_PATH . '/calendar.php';
        break;
    case '/index.php/ticket':
        require_once BASE_PATH . '/ticket.php';
        break;
    case '/':
    case '':
        require_once BASE_PATH . '/tasks.php';
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found'
        ]);
        break;
}
