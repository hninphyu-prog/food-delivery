<?php
// Prevent direct access
if (!defined('DB_HOST')) {
    die('Direct access not permitted');
}

/**
 * Get database connection
 * @param bool $createIfNotExists Whether to create the database if it doesn't exist
 * @return mysqli Database connection object
 * @throws Exception If connection fails
 */
function getDBConnection($createIfNotExists = false) {
    static $conn = null;
    
    if ($conn === null) {
        // First try to connect to the database
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // If connection failed and we should create the database
        if ($conn->connect_error && $createIfNotExists) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($conn->connect_error) {
                error_log("Database connection failed: " . $conn->connect_error);
                return null;
            }
            
            // Create the database
            if (!$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                error_log("Failed to create database: " . $conn->error);
                return null;
            }
            
            // Select the database
            if (!$conn->select_db(DB_NAME)) {
                error_log("Failed to select database: " . $conn->error);
                return null;
            }
        } elseif ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }
        
        // Set charset
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Error setting charset: " . $conn->error);
            // Don't return null here, as the connection might still work
        }
    }
    
    return $conn;
}

/**
 * User Registration
 */
function registerUser($name, $email, $password) {
    $conn = null;
    $stmt = null;
    
    try {
        // Input validation
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Check if email exists first
        if (emailExists($email)) {
            throw new Exception('Email already exists');
        }

        // Create a new connection for this operation
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
        
        $name = $conn->real_escape_string($name);
        $email = $conn->real_escape_string($email);
        $hashedPassword = hashPassword($password);
        $createdAt = date('Y-m-d H:i:s');
        
        // Insert user as unverified first
        $sql = "INSERT INTO users (name, email, password, is_verified, created_at) 
                VALUES (?, ?, ?, 0, ?)";
                
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $createdAt);
        
        if (!$stmt->execute()) {
            throw new Exception('Registration failed: ' . $stmt->error);
        }
        
        $userId = $conn->insert_id;
        
        // Generate OTP and set expiry (1 minute from now)
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiry = date('Y-m-d H:i:s', time() + 60); // 1 minute expiry
        
        // Store OTP in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['verification_code'] = $otp;
        $_SESSION['verification_expires'] = $otpExpiry;
        $_SESSION['verification_email'] = $email;
        $_SESSION['verification_user_id'] = $userId;
        
        // Send OTP email
        $subject = "Verify Your Email";
        $message = "
            <h2>Email Verification</h2>
            <p>Your verification code is: <strong>$otp</strong></p>
            <p>This code will expire in 1 minute.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        if (!sendEmail($email, $subject, $message)) {
            throw new Exception('Failed to send verification email. Please try again.');
        }
        
        // Close the statement and connection
        $stmt->close();
        $conn->close();
        $stmt = null;
        $conn = null;
        
        return [
            'status' => 'verify',
            'message' => 'Please check your email for the verification code.',
            'user_id' => $userId,
            'email' => $email
        ];
        
    } catch (Exception $e) {
        // Clean up resources
        if (isset($stmt) && $stmt) $stmt->close();
        if (isset($conn) && $conn) $conn->close();
        
        error_log('Registration error: ' . $e->getMessage());
        return [
            'status' => 'error', 
            'message' => $e->getMessage()
        ];
    }
}

function loginUser($email, $password) {
    $conn = getDBConnection();
    $email = $conn->real_escape_string($email);
    
    // First, check if email exists and account is not locked
    $sql = "SELECT user_id, name, email, password, is_verified, role, 
                   account_locked_until, login_attempts,
                   (account_locked_until > NOW()) as is_locked
            FROM users 
            WHERE email = ? 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Check if account is locked
        if ($user['is_locked']) {
            $unlockTime = date('M j, Y g:i A', strtotime($user['account_locked_until']));
            $conn->close();
            return ['status' => 'error', 'message' => "Account locked. Please try again after $unlockTime"];
        }
        
        // Now verify password
        if (!password_verify($password, $user['password'])) {
            // Track failed attempt
            $now = date('Y-m-d H:i:s');
            $attempts = $user['login_attempts'] + 1;
            $lockTime = $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+30 minutes')) : null;
            $attemptsLeft = 5 - $attempts;
            
            $updateSql = "UPDATE users SET 
                         login_attempts = ?,
                         last_failed_attempt = ?,
                         account_locked_until = ?
                         WHERE email = ?";
            
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("isss", $attempts, $now, $lockTime, $email);
            $updateStmt->execute();
            $updateStmt->close();
            $conn->close();
            
            return [
                'status' => 'error', 
                'message' => 'Incorrect password' . ($attemptsLeft > 0 ? ". $attemptsLeft attempts remaining before account lockout." : '')
            ];
        }
        
        if (!$user['is_verified']) {
            $conn->close();
            return ['status' => 'error', 'message' => 'Please verify your email before logging in'];
        }
        
        // Reset login attempts and update last login on successful login
        $updateSql = "UPDATE users SET 
                     login_attempts = 0,
                     account_locked_until = NULL,
                     last_failed_attempt = NULL
                     WHERE email = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("s", $email);
        $updateStmt->execute();
        $updateStmt->close();
        
        // Start session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Generate a new session token
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userId = $user['user_id'];
        
        // First, delete any existing session for this user
        $deleteSql = "DELETE FROM user_sessions WHERE user_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $userId);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Insert new session
        $insertSql = "INSERT INTO user_sessions (user_id, session_token, user_agent, ip_address, expires_at) 
                     VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("issss", $userId, $sessionToken, $userAgent, $ipAddress, $expiresAt);
        
        if ($insertStmt->execute()) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set secure cookie
            setcookie('remember_token', $sessionToken, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            $insertStmt->close();
            $conn->close();
            return ['status' => 'success', 'message' => 'Login successful!'];
        }
    }
    
    $conn->close();
    return ['status' => 'error', 'message' => 'Invalid email or password'];
}

// Track login attempts and implement account lockout
function trackLoginAttempt($email, $success, $conn = null) {
    $shouldCloseConnection = false;
    
    if ($conn === null) {
        $conn = getDBConnection();
        $shouldCloseConnection = true;
    }
    
    $email = $conn->real_escape_string($email);
    $now = date('Y-m-d H:i:s');
    
    if ($success) {
        // Reset login attempts on successful login
        $sql = "UPDATE users 
                SET login_attempts = 0, 
                    account_locked_until = NULL,
                    last_failed_attempt = NULL
                WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
    } else {
        // Increment failed login attempts and check for lockout
        $sql = "UPDATE users 
                SET login_attempts = login_attempts + 1,
                    last_failed_attempt = ?,
                    account_locked_until = IF(login_attempts >= 4, DATE_ADD(NOW(), INTERVAL 24 HOUR), account_locked_until)
                WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $now, $email);
    }
    
    $result = $stmt->execute();
    $stmt->close();
    
    if ($shouldCloseConnection) {
        $conn->close();
    }
    
    return $result;
}

/**
 * Get dashboard URL based on user role
 */
function getDashboardUrlByRole($role, $userId, $conn = null) {
    $shouldCloseConnection = false;
    
    if ($conn === null) {
        $conn = getDBConnection();
        $shouldCloseConnection = true;
    }
    
    $redirectUrl = 'dashboard.php'; // Default dashboard
    
    switch (strtolower($role)) {
        case 'admin':
            $redirectUrl = 'views/admin/dashboard.php';
            break;
            
        case 'customer':
            $redirectUrl = 'views/customer/dashboard.php';
            break;
            
        case 'vendor':
            // Get restaurant ID for vendor
            $restaurantSql = "SELECT restaurant_id FROM restaurants WHERE user_id = ? LIMIT 1";
            $restaurantStmt = $conn->prepare($restaurantSql);
            $restaurantStmt->bind_param("i", $userId);
            $restaurantStmt->execute();
            $restaurantResult = $restaurantStmt->get_result();
            
            if ($restaurantResult->num_rows === 1) {
                $restaurant = $restaurantResult->fetch_assoc();
                $_SESSION['restaurant_id'] = $restaurant['restaurant_id'];
                $redirectUrl = 'views/restaurant/index.php';
            } else {
                // If no restaurant linked, redirect to restaurant setup page
                $redirectUrl = 'views/restaurant/setup.php';
            }
            $restaurantStmt->close();
            break;
            
        case 'delivery':
            $redirectUrl = 'views/rider/dashboard.php';
            break;
            
        default:
            $redirectUrl = 'dashboard.php';
    }
    
    if ($shouldCloseConnection) {
        $conn->close();
    }
    
    return $redirectUrl;
}

/**
 * Get current user's dashboard URL
 */
function getCurrentUserDashboard() {
    if (!isLoggedIn()) {
        return 'signin.php';
    }
    
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['role'];
    
    return getDashboardUrlByRole($userRole, $userId);
}

/**
 * Redirect to user's dashboard based on role
 */
function redirectToDashboard() {
    $dashboardUrl = getCurrentUserDashboard();
    redirect($dashboardUrl);
}

/**
 * Send Password Reset Email
 */
function sendPasswordResetEmail($email) {
    if (!emailExists($email)) {
        return ['status' => 'error', 'message' => 'No account found with this email'];
    }
    
    $conn = getDBConnection();
    $token = generateToken();
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $email = $conn->real_escape_string($email);
    
    // Delete any existing tokens for this email
    $sql = "DELETE FROM password_resets WHERE email = '$email'";
    $conn->query($sql);
    
    // Insert new token
    $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES ('$email', '$token', '$expires')";
    
    if ($conn->query($sql) === TRUE) {
        // Send email with reset link
        $resetLink = SITE_URL . "/reset-password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "
            <h2>Password Reset</h2>
            <p>You requested a password reset. Click the link below to reset your password:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        if (sendEmail($email, $subject, $message)) {
            $conn->close();
            return ['status' => 'success', 'message' => 'Password reset link has been sent to your email.'];
        } else {
            $conn->close();
            return ['status' => 'error', 'message' => 'Failed to send reset email. Please try again.'];
        }
    } else {
        $error = $conn->error;
        $conn->close();
        return ['status' => 'error', 'message' => 'Failed to process your request: ' . $error];
    }
}

/**
 * Reset Password
 */
function resetPassword($token, $newPassword) {
    $conn = getDBConnection();
    $token = $conn->real_escape_string($token);
    $currentTime = date('Y-m-d H:i:s');
    
    // Check if token exists and is not expired
    $sql = "SELECT email FROM password_resets WHERE token = '$token' AND expires_at > '$currentTime' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $hashedPassword = hashPassword($newPassword);
        
        // Update user's password
        $sql = "UPDATE users SET password = '$hashedPassword' WHERE email = '$email'";
        
        if ($conn->query($sql) === TRUE) {
            // Delete the used token
            $sql = "DELETE FROM password_resets WHERE email = '$email'";
            $conn->query($sql);
            
            $conn->close();
            return ['status' => 'success', 'message' => 'Password has been reset successfully.'];
        } else {
            $error = $conn->error;
            $conn->close();
            return ['status' => 'error', 'message' => 'Failed to reset password: ' . $error];
        }
    } else {
        $conn->close();
        return ['status' => 'error', 'message' => 'Invalid or expired token.'];
    }
}

/**
 * Verify Email
 */
function verifyEmail($token) {
    $conn = getDBConnection();
    $token = $conn->real_escape_string($token);
    
    $sql = "SELECT user_id FROM users WHERE verification_token = '$token' AND is_verified = 0 LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE user_id = " . $user['user_id'];
        
        if ($conn->query($sql) === TRUE) {
            $conn->close();
            return ['status' => 'success', 'message' => 'Email verified successfully!'];
        } else {
            $error = $conn->error;
            $conn->close();
            return ['status' => 'error', 'message' => 'Failed to verify email: ' . $error];
        }
    } else {
        $conn->close();
        return ['status' => 'error', 'message' => 'Invalid or expired verification link.'];
    }
}

/**
 * Send Verification Email
 */
function sendVerificationEmail($email, $token) {
    try {
        if (empty($email) || empty($token)) {
            throw new Exception('Email and token are required');
        }
        
        $verificationLink = (defined('SITE_URL') ? SITE_URL : 'http://' . $_SERVER['HTTP_HOST']) . "/verify-email.php?token=" . urlencode($token);
        $subject = "Verify Your Email Address";
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .button {
                        display: inline-block; 
                        padding: 10px 20px; 
                        background-color: #4CAF50; 
                        color: white; 
                        text-decoration: none; 
                        border-radius: 5px;
                        margin: 15px 0;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Email Verification</h2>
                    <p>Thank you for registering! Please click the button below to verify your email address:</p>
                    <p>
                        <a href='$verificationLink' class='button'>Verify Email</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p><small>$verificationLink</small></p>
                    <p>If you didn't create an account, you can safely ignore this email.</p>
                </div>
            </body>
            </html>
        ";
        
        $emailSent = sendEmail($email, $subject, $message);
        
        if (!$emailSent) {
            throw new Exception('Failed to send verification email');
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log('Verification email error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get User Data
 */
function getUserData($userId) {
    $conn = getDBConnection();
    $userId = (int)$userId;
    $sql = "SELECT user_id, name, email, phone, role, address, created_at, is_verified 
            FROM users WHERE user_id = $userId LIMIT 1";
    $result = $conn->query($sql);
    $user = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $conn->close();
    return $user;
}

/**
 * Logout User
 */
function logout() {
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $conn = getDBConnection();
        $token = $conn->real_escape_string($token);
        $conn->query("DELETE FROM user_sessions WHERE session_token = '$token'");
        $conn->close();
        
        // Clear the remember me cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Clear session data
    $_SESSION = array();
    
    // Destroy the session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    
    // Redirect to login page
    header('Location: signin.php');
    exit();
}

/**
 * Check if email exists
 */
function emailExists($email) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    $email = $conn->real_escape_string($email);
    $sql = "SELECT user_id FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);
    $exists = $result && $result->num_rows > 0;
    
    $conn->close();
    return $exists;
}

/**
 * Generate a random token
 */
function generateToken($length = 32) {
    try {
        return bin2hex(random_bytes($length));
    } catch (Exception $e) {
        // Fallback for systems without random_bytes()
        $token = '';
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length * 2; $i++) {
            $token .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $token;
    }
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    if (!is_string($data)) {
        return $data;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Send email using PHPMailer with Gmail SMTP
 */
// Include PHPMailer files directly
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email with error handling and logging using PHPMailer
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message HTML email content
 * @return bool True if email was sent successfully, false otherwise
 */
function sendEmail($to, $subject, $message) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'jeffauren@gmail.com';                  // Your Gmail address
        $mail->Password   = 'vwxo lxui hwgs xpxv';                  // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
        $mail->Port       = 587;                                    // TCP port to connect to
        $mail->SMTPDebug  = 0;                                      // 0 = no output, 2 = verbose output
        
        // Recipients
        $mail->setFrom('jeffauren@gmail.com', 'Jeff Auren');
        $mail->addAddress($to);                                     // Add a recipient
        
        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);                      // Plain text version
        
        // Send the email
        $mail->send();
        error_log("Email sent successfully to: $to");
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed to $to. Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Redirect to a URL
 */
function redirect($url, $statusCode = 303) {
    if (!headers_sent()) {
        // Convert relative URLs to absolute
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = rtrim(SITE_URL, '/') . '/' . ltrim($url, '/');
        }
        
        header('Location: ' . $url, true, $statusCode);
    } else {
        echo "<script>window.location.href='$url';</script>";
    }
    
    exit();
}

function isLoggedIn() {
    // Start session if not already started
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Check if user is already logged in via session
    if (!empty($_SESSION['user_id'])) {
        return true;
    }
    
    // Check remember me cookie
    if (!empty($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $currentTime = date('Y-m-d H:i:s');
        
        $conn = getDBConnection();
        
        // First, clean up any expired sessions
        $conn->query("DELETE FROM user_sessions WHERE expires_at < '$currentTime'");
        
        // Check if token is valid
        $sql = "SELECT u.* 
                FROM users u 
                JOIN user_sessions us ON u.user_id = us.user_id 
                WHERE us.session_token = ? 
                AND us.expires_at > ? 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $token, $currentTime);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Update last activity
            $updateSql = "UPDATE user_sessions SET last_activity = NOW() 
                         WHERE user_id = ? AND session_token = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("is", $user['user_id'], $token);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            
            $stmt->close();
            $conn->close();
            return true;
        }
        
        $stmt->close();
        $conn->close();
    }
    
    return false;
}

/**
 * Verify OTP code
 */
function verifyOTP($otp) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['otp']) || empty($_SESSION['otp_expiry']) || empty($_SESSION['verification_email']) || empty($_SESSION['verification_user_id'])) {
        return ['status' => 'error', 'message' => 'Invalid verification session'];
    }
    
    if (time() > $_SESSION['otp_expiry']) {
        return ['status' => 'error', 'message' => 'OTP has expired. Please request a new one.'];
    }
    
    if ($_SESSION['otp'] !== $otp) {
        return ['status' => 'error', 'message' => 'Invalid OTP. Please try again.'];
    }
    
    // OTP is valid, verify the user
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        return ['status' => 'error', 'message' => 'Database connection failed'];
    }
    
    $userId = $_SESSION['verification_user_id'];
    $sql = "UPDATE users SET is_verified = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        $conn->close();
        return ['status' => 'error', 'message' => 'Database error'];
    }
    
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    if (!$result) {
        return ['status' => 'error', 'message' => 'Failed to verify email'];
    }
    
    // Clear OTP session data
    unset($_SESSION['otp']);
    unset($_SESSION['otp_expiry']);
    unset($_SESSION['verification_email']);
    unset($_SESSION['verification_user_id']);
    
    return ['status' => 'success', 'message' => 'Email verified successfully!'];
}

/**
 * Resend OTP to user's email
 */
function resendOTP() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['verification_email'])) {
        return ['status' => 'error', 'message' => 'No verification in progress'];
    }
    
    $email = $_SESSION['verification_email'];
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpExpiry = time() + (60); // 1 minute from now
    
    // Store new OTP in session
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = $otpExpiry;
    
    // Send OTP email
    $subject = "Your New Verification Code";
    $message = "
        <h2>New Verification Code</h2>
        <p>Your new verification code is: <strong>$otp</strong></p>
        <p>This code will expire in 1 minute.</p>
        <p>If you didn't request this, please ignore this email.</p>
    ";
    
    if (sendEmail($email, $subject, $message)) {
        return ['status' => 'success', 'message' => 'A new verification code has been sent to your email.'];
    } else {
        return ['status' => 'error', 'message' => 'Failed to send verification code. Please try again.'];
    }
}

function requireLogin($redirect = 'signin.php') {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect($redirect);
    }
}

/**
 * Require specific user role
 */
function requireRole($roles, $redirect = 'dashboard.php') {
    requireLogin($redirect);
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    $userRole = strtolower($_SESSION['role'] ?? 'customer');
    $roles = array_map('strtolower', $roles);
    
    if (!in_array($userRole, $roles)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        redirect($redirect);
    }
}
?>