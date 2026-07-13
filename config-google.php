<?php
require_once __DIR__ . '/config/db.php';
require_once 'functions.php';

// Google OAuth configuration
define('GOOGLE_CLIENT_ID', '1058699930857-gul4k68e949a56gaolugkm5or3s6bqnd.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/foodandme/google-auth.php');

// Google OAuth scopes
define('GOOGLE_SCOPES', [
    'https://www.googleapis.com/auth/userinfo.email',
    'https://www.googleapis.com/auth/userinfo.profile',
    'openid'
]);

// Google OAuth consent screen parameters
define('GOOGLE_ACCESS_TYPE', 'offline');
// Set to 'force' to always show consent screen, or 'auto' to only show when needed
define('GOOGLE_PROMPT', 'consent');

// Google OAuth URLs
$googleOauthURL = 'https://accounts.google.com/o/oauth2/auth';
$googleTokenURL = 'https://oauth2.googleapis.com/token';
$googleUserInfoURL = 'https://www.googleapis.com/oauth2/v3/userinfo';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to generate random state
function generateState() {
    return bin2hex(random_bytes(16));
}

// Function to verify state to prevent CSRF
function verifyState($state) {
    return isset($_SESSION['oauth2state']) && hash_equals($_SESSION['oauth2state'], $state);
}

// Function to get user by email
function getUserByEmail($email) {
    $conn = getDBConnection();
    
    if (!$conn) {
        error_log("Failed to get database connection in getUserByEmail");
        return false;
    }

    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user ?: false;
}

// Function to create new user from Google data
function createUserFromGoogle($userData) {
    global $conn;
    
    $name = $userData['name'];
    $email = $userData['email'];
    $google_id = $userData['sub'];
    $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $created_at = date('Y-m-d H:i:s');
    $role = 'customer'; // Set default role to 'customer'
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, google_id, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $google_id, $role, $created_at);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    
    return false;
}

// Function to handle Google user login/signup
function handleGoogleUser($userData) {
    // Debug logging
    error_log('handleGoogleUser - Starting with user data: ' . print_r($userData, true));
    
    if (empty($userData['email'])) {
        $error = "No email provided in Google user data";
        error_log($error);
        return ['status' => 'error', 'message' => $error];
    }

    $conn = getDBConnection();
    if (!$conn) {
        error_log("Failed to get database connection in handleGoogleUser");
        return ['status' => 'error', 'message' => 'Database connection failed'];
    }

    try {
        $existingUser = getUserByEmail($userData['email']);
        
        if ($existingUser) {
            // User exists, log them in
            $userId = $existingUser['user_id'];
            
            // Start session if not already started
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
                error_log('handleGoogleUser - Session started');
            }
            
            // Generate a new session token
            $sessionToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            
            // Delete any existing session for this user
            $deleteSql = "DELETE FROM user_sessions WHERE user_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            if ($deleteStmt) {
                $deleteStmt->bind_param("i", $userId);
                $deleteStmt->execute();
                $deleteStmt->close();
            }
            
            // Insert new session
            $insertSql = "INSERT INTO user_sessions (user_id, session_token, user_agent, ip_address, expires_at) 
                         VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            
            if ($insertStmt) {
                $insertStmt->bind_param("issss", $userId, $sessionToken, $userAgent, $ipAddress, $expiresAt);
                
                if ($insertStmt->execute()) {
                    // Set session variables with consistent naming
                    $_SESSION['user_id'] = $existingUser['user_id'];
                    $_SESSION['email'] = $existingUser['email'];
                    $_SESSION['name'] = $existingUser['name'] ?? '';
                    $_SESSION['role'] = $existingUser['role'] ?? 'customer';
                    
                    // Debug logging
                    error_log('handleGoogleUser - Session variables set:');
                    error_log('- User ID: ' . $_SESSION['user_id']);
                    error_log('- Email: ' . $_SESSION['email']);
                    error_log('- Role: ' . $_SESSION['role']);
                    
                    // Set secure cookie
                    setcookie('remember_token', $sessionToken, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    
                    // Get the correct dashboard URL
                    $dashboardUrl = getDashboardUrlByRole($_SESSION['role'], $existingUser['user_id'], $conn);
                    
                    $insertStmt->close();
                    $conn->close();
                    
                    error_log('handleGoogleUser - Redirecting to: ' . $dashboardUrl);
                    return [
                        'status' => 'success', 
                        'message' => 'Login successful!', 
                        'redirect' => $dashboardUrl
                    ];
                }
                $insertStmt->close();
            }
        } else {
            // Register new user
            $name = $userData['name'] ?? '';
            $email = $userData['email'] ?? '';
            $password = bin2hex(random_bytes(16)); // Generate a random password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (name, email, password, is_verified, role) 
                    VALUES (?, ?, ?, 1, 'customer')";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("sss", $name, $email, $hashedPassword);
                
                if ($stmt->execute()) {
                    $userId = $conn->insert_id;
                    $stmt->close();
                    
                    // Log the user in
                    return handleGoogleUser($userData); // Recursively call to log in the new user
                }
                $stmt->close();
            }
        }
        
        $conn->close();
        return ['status' => 'error', 'message' => 'Authentication failed'];
        
    } catch (Exception $e) {
        error_log("Error in handleGoogleUser: " . $e->getMessage());
        if (isset($conn)) {
            $conn->close();
        }
        return ['status' => 'error', 'message' => 'An error occurred during authentication'];
    }
}



?>
