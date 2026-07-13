<?php
// Error reporting - set first to catch all errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:\\xampp\\php\logs\\php_errors.log');

// Session configuration - must be before session_start()
if (session_status() === PHP_SESSION_NONE) {
    // Set a custom session path inside the application directory
    $sessionPath = __DIR__ . '/../sessions';
    
    // Create the directory if it doesn't exist
    if (!file_exists($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    
    // Set session configuration
    ini_set('session.save_path', $sessionPath);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.gc_maxlifetime', 86400); // 24 minutes
    
    // Set session name
    session_name('foodme_session');
    
    // Start session with error handling
    try {
        // Check if the session directory is writable
        if (!is_writable($sessionPath)) {
            throw new RuntimeException("Session directory is not writable: " . $sessionPath);
        }
        
        // Start the session
        if (!session_start()) {
            throw new RuntimeException('Failed to start session');
        }
        
        // Regenerate session ID to prevent session fixation
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    } catch (Exception $e) {
        error_log('Session start failed: ' . $e->getMessage());
        // Provide more detailed error in development
        if (ini_get('display_errors')) {
            die('Session Error: ' . $e->getMessage());
        } else {
            die('Unable to start session. Please try again later.');
        }
    }
}

// Define base URL for easier reference
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));

// Error reporting - after session start
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'foodapp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Initialize variables for backward compatibility
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;
$charset = DB_CHARSET;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_errno) {
    die("MySQLi connection failed: " . $conn->connect_error);
}
$conn->set_charset($charset);

// reCAPTCHA Configuration
define('RECAPTCHA_SITE_KEY', '6LcgnQ0sAAAAAFaFOz3qOjqidWkGC-ZkkV4sQsV5'); // Replace with your actual site key
define('RECAPTCHA_SECRET_KEY', '6LcgnQ0sAAAAACD39FYitwgtmGQCdqKGuiUCHW2M'); // Replace with your actual secret key
// Set default timezone
date_default_timezone_set('Asia/Yangon');

// Site configuration
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', $protocol . $host . '/foodandme');
define('ADMIN_EMAIL', 'admin@jeffauren.com');
define('TOKEN_EXPIRY', 3600 * 24 * 30); // 30 days


// Include functions
require_once dirname(__DIR__) . '/functions.php';
