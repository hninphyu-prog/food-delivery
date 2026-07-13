<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/functions.php';

// Clear any old form data
if (isset($_SESSION['form_data'])) {
  unset($_SESSION['form_data']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email_or_phone'] ?? '';
  $password = $_POST['pwd'] ?? '';
  $remember = isset($_POST['remember']) ? true : false;

  // Basic validation
  if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please fill in all fields';
    $_SESSION['form_data']['email'] = $email;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  }

  try {
    // Sanitize email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Get database connection
    $conn = getDBConnection();
    if (!$conn) {
      throw new Exception('Database connection failed');
    }

    // First check if email exists and if account is locked
    $checkUser = $conn->prepare("SELECT user_id, account_locked_until, login_attempts FROM users WHERE email = ?");
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $userCheck = $checkUser->get_result();

    if ($userCheck->num_rows === 0) {
      $checkUser->close();
      header('Location: signup.php?email=' . urlencode($email));
      exit();
      throw new Exception('No account found with this email address.');
    }

    $userData = $userCheck->fetch_assoc();
    $checkUser->close();

    // Check if account is locked
    if (!empty($userData['account_locked_until'])) {
      $lockTime = strtotime($userData['account_locked_until']);
      $currentTime = time();

      if ($lockTime > $currentTime) {
        $remainingTime = $lockTime - $currentTime;
        $hours = floor($remainingTime / 3600);
        $minutes = floor(($remainingTime % 3600) / 60);

        throw new Exception("Account locked. Please try again in $hours hours and $minutes minutes.");
      } else {
        // Reset lock if time has expired
        $resetLock = $conn->prepare("UPDATE users SET account_locked_until = NULL, login_attempts = 0 WHERE email = ?");
        $resetLock->bind_param("s", $email);
        $resetLock->execute();
        $resetLock->close();
      }
    }

    // If email exists and account is not locked, proceed with login attempt
    $sql = "SELECT user_id, name, email, password, role, is_verified, login_attempts 
                FROM users 
                WHERE email = ? 
                LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      // Track login attempt
      if (password_verify($password, $user['password'])) {
        // Successful login - reset login attempts
        trackLoginAttempt($email, true, $conn);
        if ($user['is_verified']) {
          // Set session variables
          $_SESSION['user_id'] = $user['user_id'];
          $_SESSION['email'] = $user['email'];
          $_SESSION['name'] = $user['name'];
          $_SESSION['role'] = $user['role'];

          // Handle remember me
          if ($remember) {
            $token = bin2hex(random_bytes(32));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

            // Store token in database
            $sql = "INSERT INTO user_tokens (user_id, token, expires_at) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                token = VALUES(token), 
                                expires_at = VALUES(expires_at)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user['user_id'], $hashedToken, $expires);
            $stmt->execute();

            // Set cookie
            setcookie('remember_token', $token, [
              'expires' => strtotime('+30 days'),
              'path' => '/',
              'secure' => true,
              'httponly' => true,
              'samesite' => 'Strict'
            ]);
          }

          // Generate OTP and set expiry (1 minute from now)
          $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
          $otpExpiry = time() + 60; // 1 minute expiry

          // Store all required session data for verification
          $_SESSION['otp'] = $otp;
          $_SESSION['otp_expiry'] = $otpExpiry;
          $_SESSION['verification_user_id'] = $user['user_id'];
          $_SESSION['verification_email'] = $user['email'];

          // Store user data in session for after verification
          $_SESSION['pending_user'] = [
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
            'remember' => $remember
          ];

          // Send OTP email
          $subject = "Your Login Verification Code";
          $message = "
                        <h2>Login Verification</h2>
                        <p>Your verification code is: <strong>$otp</strong></p>
                        <p>This code will expire in 1 minute.</p>
                        <p>If you didn't request this, please secure your account immediately.</p>
                    ";

          // Send email
          if (sendEmail($user['email'], $subject, $message)) {
            // Close connections
            $stmt->close();
            $conn->close();

            // Redirect to OTP verification
            header('Location: verify_otp.php');
            exit();
          } else {
            throw new Exception('Failed to send verification email. Please try again.');
          }
        } else {
          trackLoginAttempt($email, false, $conn);
          throw new Exception('Please verify your email address before logging in.');
        }
      } else {
        // Failed login - track attempt
        trackLoginAttempt($email, false, $conn);

        // Get remaining attempts
        $remainingAttempts = 5 - ($user['login_attempts'] + 1);
        if ($remainingAttempts > 0) {
          throw new Exception("Invalid password. $remainingAttempts attempts remaining!");
        } else {
          throw new Exception('Account locked due to too many failed attempts. Please try again later.');
        }
      }
    } else {
      // This should theoretically never be reached because we already checked if email exists
      throw new Exception('An unexpected error occurred. Please try again.');
    }
  } catch (Exception $e) {
    // Log the error
    error_log('Login error: ' . $e->getMessage());

    // Set error message
    $_SESSION['error'] = $e->getMessage();
    $_SESSION['form_data']['email'] = $email;

    // Close connection if it's still open
    if (isset($conn)) $conn->close();

    // Redirect back to login page
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In | Food&Me</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
      background:
        url('assets/images/FoodBg.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 100vh;
      overflow: hidden;
    }

    /* ===== Overlay ===== */
    .overlay {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 100;
      animation: fadeIn 0.3s ease;
    }

    /* ===== Modal Box ===== */
    .modal {
      background: rgba(255, 102, 0, 0.7);
      width: 380px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
      padding: 40px 35px;
      text-align: center;
      position: relative;
      animation: zoomIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes zoomIn {
      from {
        transform: scale(0.95);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .close-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      font-size: 35px;
      color: #ffffffff;
      cursor: pointer;
      transition: 0.2s;
    }

    .close-btn:hover {
      color: #d4d4d4ff;
    }

    /* ===== Login Form ===== */
    .logo {
      font-size: 44px;
      color: #ffffffff;
      margin-bottom: 4px;
    }

    .modal h2 {
      color: #ffffff;
      text-align: center;
      margin-bottom: 25px;
      font-size: 24px;
      font-weight: 600;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .input-box {
      position: relative;
      margin-bottom: 20px;
      width: 100%;
    }

    .input-box input {
      width: 100%;
      padding: 12px 45px 12px 40px;
      border-radius: 8px;
      font-size: 15px;
      outline: none;
      transition: 0.2s;
      height: 50px;
      flex: 1 1 0%;
      background-color: transparent !important;
      color: #ffffff;
      /* White text for better visibility */
      border: 1px solid #ffffffff;
    }

    .input-box input::placeholder {
      color: #ffffffff;
    }

    /* Focus state */
    .input-box input:focus {
      background-color: rgba(255, 255, 255, 0.05) !important;
      /* Slight highlight on focus */
      border-color: #000000ff;
      /* Orange border on focus */
      outline: none;
      /* Remove default focus outline */
      box-shadow: none;
      /* Remove any default shadow */
    }

    /* When input has content */
    .input-box input:not(:placeholder-shown) {
      background-color: rgba(255, 255, 255, 0.05) !important;
      /* Slight background when filled */
    }


    .input-box i {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #ffffff;
      font-size: 20px;
    }

    /* eye toggle */
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 20px;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #666;
      cursor: pointer;
      font-size: 20px;
      transition: 0.2s;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
    }

    .toggle-password:hover {
      color: #c01963;
    }

    .forgot {
      display: block;
      text-align: right;
      font-size: 13px;
      color: #555;
      margin-top: -10px;
      margin-bottom: 25px;
      text-decoration: none;
    }

    .forgot:hover {
      text-decoration: underline;
    }

    .btn-login {
      width: 100%;
      padding: 12px;
      background: #ffffffff;
      color: #000000ff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.2s;
    }

    .btn-login:hover {
      background: #111;
      color: #ff6600;
    }

    .divider {
      display: flex;
      align-items: center;
      margin: 20px 0;
      color: #ffffffff;
      font-size: 15px;
    }

    .divider::before,
    .divider::after {
      content: "";
      flex: 1;
      height: 1px;
      background: #ddd;
    }

    .divider span {
      padding: 0 10px;
    }

    .social-login {
      display: flex;
      justify-content: center;
      gap: 12px;
    }

    .google-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 12px;
      width: 100%;
      background-color: transparent !important;
      color: #ffffff;
      /* White text for better visibility */
      border: 1px solid #ffffffff;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 500;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s;
    }

    .google-btn:hover {
      background-color: #f8f9fa;
      border-color: #d2e3fc;
      box-shadow: 0 1px 3px 1px rgba(66, 64, 67, 0.15);
      transform: translateY(-1px);
      ;
    }

    .google-icon {
      width: 20px;
      height: 20px;
      margin-right: 10px;
    }

    .signup-text {
      margin-top: 25px;
      font-size: 14px;
      color: #555;
    }

    .signup-text a {
      color: #000;
      font-weight: 600;
      text-decoration: none;
      margin-left: 5px;
    }

    .signup-text a:hover {
      text-decoration: underline;
    }



    /* Responsive */
    @media (max-width: 450px) {
      .modal {
        width: 90%;
        padding: 30px 25px;
      }
    }
    /* ===== Error Message Styling ===== */
.error-message {
    color: #ffffffff;
    background-color: rgba(255, 255, 255, 0.1);
    border: 4px solid #ff3333;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
    text-align: left;
    animation: slideIn 0.3s ease-out;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(255, 51, 51, 0.1);
}

.error-message::before {
    content: '⚠️';
    font-size: 16px;
    flex-shrink: 0;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Shake animation for form on error */
@keyframes shakeError {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.has-error {
    animation: shakeError 0.5s ease-in-out;
}

/* Specific error states for input fields */
.input-box.error input {
    border-color: #ff3333 !important;
    background-color: rgba(255, 51, 51, 0.05) !important;
}

.input-box.error i {
    color: #ff3333;
}

/* Success message styling (optional) */
.success-message {
    color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
    border-left: 4px solid #28a745;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
    text-align: left;
    animation: slideIn 0.3s ease-out;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.success-message::before {
    content: '✅';
    font-size: 16px;
    flex-shrink: 0;
}
  </style>
</head>

<body>

  <!-- Overlay + Modal -->
  <div class="overlay" id="overlay">
    <div class="modal">
      <a href="index.php" class="close-btn" id="closeBtn" title="Close">
        <i class='bx bx-x'></i>
      </a>
      <div class="logo">
        <i class=''></i> ℱℴℴ𝒹&ℳℯ
      </div>
      <h2>Welcome Back!</h2>

      <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
        <?php if (isset($_SESSION['error'])): ?>
          <div class="error-message" >
            <?php
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); // Clear the error after displaying it
            ?>
          </div>
        <?php endif; ?>
        <div class="input-box">
          <i class='bx bx-envelope'></i>
          <input type="email" name="email_or_phone" id="email" placeholder="Email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
        </div>

        <div class="input-box">
          <i class='bx bx-lock-alt'></i>
          <input type="password" name="pwd" id="password" placeholder="Password" required>
          <button type="button" class="toggle-password" id="togglePassword">
            <i class='bx bx-hide'></i>
          </button>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 13px;">
          
          <a href="forgot-password.php" style="color: #ffffff; text-decoration: none; transition: all 0.2s; position: relative;"
            onmouseover="this.style.color='#000000ff'; this.querySelector('.underline').style.width='100%';"
            onmouseout="this.style.color='#ffffff'; this.querySelector('.underline').style.width='0%';">
            Forgot password?
            <span class="underline" style="position: absolute; bottom: -2px; left: 0; width: 0%; height: 1px; background-color: #ff6600; transition: width 0.2s ease-in-out;"></span>
          </a>
        </div>
        <button type="submit" class="btn-login">Sign In</button>
        <p style="text-align: center; margin-top: 15px; font-size: 14px; color: #ffffff;">
          Don't have an account? <a href="signup.php" style="color: #000000ff; text-decoration: none; font-weight: bold; font-size:16px;position: relative;"
            onmouseover="this.style.color='#ffffffff'; this.querySelector('.signup-underline').style.width='100%';"
            onmouseout="this.style.color='#000000ff'; this.querySelector('.signup-underline').style.width='0%';">
            Sign up
            <span class="signup-underline" style="position: absolute; bottom: -2px; left: 0; width: 0%; height: 1px; background-color: #ff6600; transition: width 0.2s ease-in-out;"></span>
          </a>
        </p>
      </form>

      <div class="divider"><span>or</span></div>

      <div class="social-login">
        <a href="google-auth.php?action=login" class="google-btn">
          <img src="https://www.google.com/favicon.ico" alt="Google" class="google-icon">
          <span>Continue with Google</span>
        </a>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const closeBtn = document.getElementById('closeBtn');
      const overlay = document.getElementById('overlay');

      if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
          const icon = this.querySelector('i');

          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
          } else {
            passwordInput.type = 'password';
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
          }
        });
      }

      // Close modal when clicking the close button or overlay
      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          window.location.href = 'index.php';
        });
      }


      // Prevent form submission on Enter key in input fields
      const form = document.getElementById('loginForm');
      if (form) {
        form.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'submit') {
            e.preventDefault();
          }
        });
      }
    });
  </script>
</body>

</html>