<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/functions.php';

// Regenerate session ID for security
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email exists
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            $error = 'Database connection failed';
        } else {
            $stmt = $conn->prepare("SELECT user_id, name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in session
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_expires'] = $expires;
                
                // Create the reset link using BASE_URL
                $resetLink = BASE_URL . '/reset-password.php?token=' . urlencode($token);
                
                // Log the reset link for debugging
                error_log('Password reset link generated for ' . $email . ': ' . $resetLink);
                
                $subject = "Password Reset Request";
                $message = "
                    <h2>Password Reset</h2>
                    <p>Hello {$user['name']},</p>
                    <p>You requested to reset your password. Click the link below to set a new password:</p>
                    <p><a href='$resetLink' style='background: #ff6600; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0;'>Reset Password</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                ";
                
                if (sendEmail($email, $subject, $message)) {
                    // Redirect to check-email.php without showing any message
                    header('Location: check-email.php');
                    exit();
                } else {
                    $error = 'Failed to send reset email. Please try again.';
                }
            } else {
                // For security, don't reveal if email exists or not
                $success = 'No account found with this email. If your email exists in our system, you will receive a password reset link.';
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password | Food&Me</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    body {
      background-image: url(assets/images/FoodBg.png);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      overflow: hidden;
    }

    /* Overlay */
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.4);
      backdrop-filter: blur(4px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    /* Modal Box */
    .forgot-modal {
      background-color: rgba(255, 102, 0, 0.7);
      border-radius: 18px;
      padding: 35px 30px;
      width: 380px;
      position: relative;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      animation: popIn 0.3s ease;
    }

    @keyframes popIn {
      from { transform: scale(0.9); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    /* Top Icons (Close & Back) */
    .forgot-modal .top-icons {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .forgot-modal .top-icons i {
      font-size: 35px;
      cursor: pointer;
      color: #ffffffff; /* Same as login popup close button */
      transition: 0.2s;
    }

    .forgot-modal .top-icons i:hover {
      color: #d4d4d4ff;
    }

    /* Illustration */
    .forgot-modal .illustration {
      text-align: center;
      margin-top: 20px;
    }

    .forgot-modal .illustration img {
      width: 80px;
      height: auto;
    }

    /* Heading */
    .forgot-modal h2 {
      font-size: 20px;
      font-weight: 700;
      color: #ffffff;
      margin-top: 20px;
    }

    .forgot-modal p {
      color: #ffffff;
      font-size: 14px;
      margin-top: 8px;
      line-height: 1.5;
    }

    /* Input */
    .input-group {
      position: relative;
      margin-top: 15px;
    }

    .input-group label {
      font-size: 14px;
      color: #333;
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
    }

    .input-group input {
      width: 100%;
      padding: 12px 45px 12px 40px;
      border-radius: 8px;
      font-size: 15px;
      outline: none;
      transition: 0.2s;
      height: 50px;
      flex: 1 1 0%;
      background-color: transparent !important;
      color: #ffffff; /* White text for better visibility */
      border: 1px solid #ffffffff;
      padding-left: 45px !important;
    }
     .input-group input::placeholder {
      color: #ffffffff;
    }
    .input-group input:focus {
      border-color: #000000ff;
    }

    /* Button */
    .btn {
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
      margin-top: 20px;
    }

    .btn:hover {
      background: #111;
      color: #ff6600;
    }

    /* Back Link */
    .back-link {
      text-align: center;
      margin-top: 10px;
    }

    .back-link a {
      color: #ff6600;
      font-size: 14px;
      text-decoration: none;
      font-weight: 500;
    }

    .back-link a:hover {
      text-decoration: underline;
    }

    @media (max-width: 450px) {
      .forgot-modal {
        width: 90%;
        padding: 25px 20px;
      }
    }
  </style>
</head>
<body>

<div class="overlay">
  <div class="forgot-modal">
    <div class="top-icons">
      <a href="login.php" class="back-link"><i class='bx bx-arrow-back' title="Back"></i></a>
      <a href="index.php" class="close-link"><i class='bx bx-x' id="closeBtn" title="Close"></i></a>
    </div>

    <div class="illustration">
      <img src="https://cdn-icons-png.flaticon.com/512/6195/6195699.png" alt="Reset Icon">
    </div>

    <h2>Forgot your password?</h2>
    <p>Enter your email and we'll send you a link to reset your password</p>

    <?php if (!empty($error)): ?>
      <div class="error-message" style="color: #ff6600; margin-bottom: 15px; padding: 10px; background: #ffebee; border-radius: 5px; font-size: 14px;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="success-message" style="color: #2e7d32; margin-bottom: 15px; padding: 10px; background: #e8f5e9; border-radius: 5px; font-size: 14px;">
        <?php echo $success; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="forgotPasswordForm">
      <div class="input-group">
        <i class='bx bx-envelope' style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #ffffffff; font-size: 20px;"></i>
        <input type="email" id="email" name="email" placeholder="Email" required 
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>

      <button type="submit" class="btn">Reset password</button>
    </form>

    <div class="back-link">
      <a href="login.php">Back to login</a>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.querySelector('.overlay');
    const closeBtn = document.querySelector('.bx-x');
    const backArrow = document.querySelector('.back-link a');
    const form = document.getElementById('forgotPasswordForm');

    // Handle close button click
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        window.location.href = 'index.php';
      });
    }

    // Handle form submission
    if (form) {
      form.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (!email) {
          e.preventDefault();
          return false;
        }
        
        // Show loading state
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = 'Sending...';
        }
        
        return true;
      });
    }
    
    // Handle back link
    if (backArrow) {
      backArrow.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.getAttribute('href');
      });
    }
  });
</script>

</body>
</html>
