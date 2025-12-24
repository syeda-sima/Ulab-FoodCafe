<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ulab_foodcafe');

// Site Configuration
define('SITE_URL', 'http://localhost/Ulab_FoodCafe');
define('SITE_NAME', 'ULAB FoodCafe');

// SSL Commerce Payment Gateway Configuration
define('SSL_STORE_ID', 'your_store_id');
define('SSL_STORE_PASSWORD', 'your_store_password');
define('SSL_IS_LIVE', false); // Set to true for production

// bKash Configuration
define('BKASH_APP_KEY', 'your_bkash_app_key');
define('BKASH_APP_SECRET', 'your_bkash_app_secret');

// Email Configuration (for verification)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_email_password');

// Database Connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return $user;
}

// Check user role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Generate Order Number
function generateOrderNumber() {
    return 'ULAB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Generate Verification Token
function generateToken() {
    return bin2hex(random_bytes(32));
}
?>

