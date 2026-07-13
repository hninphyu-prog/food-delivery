<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/functions.php';

// Initialize variables
$error = '';
$success = '';

// Check if we have a pending user session
if (!isset($_SESSION['pending_user'])) {
    $_SESSION['error'] = 'Session expired. Please log in again.';
    header('Location: login.php');
    exit();
}

// Get user data from session
$pendingUser = $_SESSION['pending_user'];
$email = $pendingUser['email'] ?? '';

// Handle OTP form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['otp'])) {
        $otp = trim($_POST['otp']);
        
        // Verify OTP
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
            $error = 'OTP expired. Please request a new one.';
        } elseif (time() > $_SESSION['otp_expiry']) {
            $error = 'OTP has expired. Please request a new one.';
            unset($_SESSION['otp'], $_SESSION['otp_expiry']);
        } elseif ($_SESSION['otp'] !== $otp) {
            $error = 'Invalid OTP. Please try again.';
        } else {
            // OTP is valid, log the user in
            $_SESSION['user_id'] = $pendingUser['user_id'];
            $_SESSION['email'] = $pendingUser['email'];
            $_SESSION['name'] = $pendingUser['name'];
            $_SESSION['role'] = $pendingUser['role'];
            
            // Handle remember me if set
            if (!empty($pendingUser['remember'])) {
                $token = bin2hex(random_bytes(32));
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Store token in database
                $conn = getDBConnection();
                $sql = "INSERT INTO user_tokens (user_id, token, expires_at) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        token = VALUES(token), 
                        expires_at = VALUES(expires_at)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $pendingUser['user_id'], $hashedToken, $expires);
                $stmt->execute();
                $stmt->close();
                
                // Set cookie
                setcookie('remember_token', $token, [
                    'expires' => strtotime('+30 days'),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }
            
            // Clear OTP and pending user data
            unset(
                $_SESSION['otp'],
                $_SESSION['otp_expiry'],
                $_SESSION['pending_user']
            );
            
            // Get dashboard URL and redirect
            $conn = $conn ?? getDBConnection();
            $dashboardUrl = getDashboardUrlByRole($pendingUser['role'], $pendingUser['user_id'], $conn);
            $conn->close();
            
            header('Location: ' . $dashboardUrl);
            exit();
        }
    } else {
        $error = 'Please enter the OTP';
    }
}

// Handle resend OTP
if (isset($_GET['resend'])) {
    // Generate new OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpExpiry = time() + 60; // 1 minute expiry
    
    // Update session with new OTP
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = $otpExpiry;
    
    // Send OTP email
    $subject = "Your New Verification Code";
    $message = "
        <h2>New Verification Code</h2>
        <p>Your new verification code is: <strong>$otp</strong></p>
        <p>This code will expire in 1 minute.</p>
        <p>If you didn't request this, please secure your account immediately.</p>
    ";
    
    if (sendEmail($email, $subject, $message)) {
        $success = 'A new verification code has been sent to your email.';
    } else {
        $error = 'Failed to send verification email. Please try again.';
    }
}

// Mask email for display
$maskedEmail = '';
if (!empty($email)) {
    $parts = explode('@', $email);
    $username = $parts[0];
    $domain = $parts[1] ?? '';
    $maskedUsername = substr($username, 0, 2) . str_repeat('*', max(0, strlen($username) - 2));
    $maskedEmail = $maskedUsername . '@' . $domain;
}
?>
<?php
// [Previous PHP code remains exactly the same until the HTML starts]
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify OTP | Food&Me</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    body {
       background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 100vh;
      overflow: hidden;
      background-image: url(assets/images/FoodBg.png);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.4);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 999;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }

    .otp-modal {
      background: #21252C;
      border-radius: 15px;
      width: 380px;
      padding: 40px 35px;
      position: relative;
      box-shadow: 0 0 25px rgba(0,0,0,0.2);
      text-align: center;
      animation: zoomIn 0.3s ease;
    }

    @keyframes zoomIn {
      from { transform: scale(0.95); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    .otp-modal .top-icons {
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: absolute;
      top: 15px;
      left: 15px;
      right: 15px;
    }

    .otp-modal .top-icons i {
      font-size: 22px;
      cursor: pointer;
      color: #777;
      transition: 0.2s;
    }

    .otp-modal .top-icons i:hover {
      color: #d4d4d4ff;
    }

    .otp-modal .logo {
      font-size: 32px;
      color: #ff6600;
      margin-top: 15px;
      margin-bottom: 10px;
    }

    .otp-modal h2 {
      color: #ffffff;
      text-align: center;
      margin-bottom: 25px;
      font-size: 24px;
      font-weight: 600;
    }

    .otp-modal p {
      color: #a0a0a0;
      font-size: 14px;
      line-height: 1.5;
      margin-bottom: 25px;
    }

    .otp-inputs {
      display: flex;
      justify-content: space-between;
      margin-bottom: 25px;
    }

    .otp-inputs input {
      width: 50px;
      height: 50px;
      text-align: center;
      font-size: 24px;
      font-weight: 600;
      border: 2px solid #3a3f47;
      background: #2a2f36;
      color: #fff;
      border-radius: 8px;
      outline: none;
      transition: all 0.3s;
    }

    .otp-inputs input:focus {
      border-color: #ff6600;
      box-shadow: 0 0 0 2px rgba(255, 102, 0, 0.2);
    }

    .resend-otp {
      color: #a0a0a0;
      font-size: 14px;
      margin-top: 15px;
    }

    .resend-otp a {
      color: #ff6600;
      text-decoration: none;
      font-weight: 500;
      margin-left: 5px;
    }

    .resend-otp a:hover {
      text-decoration: underline;
    }

    .btn-verify {
      background: #ff6600;
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
      transition: all 0.3s;
    }

    .btn-verify:hover {
      background: #e55c00;
    }

    .error-message {
      color: #ff4d4f;
      font-size: 14px;
      margin-top: 15px;
      text-align: center;
    }

    .success-message {
      color: #52c41a;
      font-size: 14px;
      margin: 15px 0 25px 0;
      text-align: center;
    }

    .email-display {
      color: #ff6600;
      font-weight: 500;
      word-break: break-all;
      display: inline-block;
      margin: 5px 0;
    }
  </style>
</head>
<body>
<div class="overlay">
  <div class="otp-modal">
    <div class="top-icons">
      <a href="login.php" class="back-link"><i class='bx bx-arrow-back' title="Back to Login"></i></a>
      <a href="index.php" class="close-link"><i class='bx bx-x' title="Close"></i></a>
    </div>
    
    <div class="logo">ℱℴℴ𝒹&ℳℯ</div>
    <h2>Verify Your Identity</h2>
    
    <p>We've sent a 6-digit verification code to<br>
      <span class="email-display"><?php echo htmlspecialchars($maskedEmail); ?></span>
    </p>
    
    <?php if (!empty($error)): ?>
      <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form id="otp-form" method="POST" action="">
      <div class="otp-inputs">
        <input type="text" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="one-time-code" required>
        <input type="text" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="one-time-code" required>
        <input type="text" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="one-time-code" required>
        <input type="text" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="one-time-code" required>
        <input type="text" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="one-time-code" required>
        <input type="text" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="one-time-code" required>
      </div>
      <input type="hidden" name="otp" id="otp-code">
      
      <button type="submit" class="btn-verify">Verify OTP</button>
      
      <div class="resend-otp">
        Didn't receive the code? 
        <a href="?resend=1">Resend OTP</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const otpForm = document.getElementById('otp-form');
    const otpInputs = document.querySelectorAll('.otp-inputs input');
    const hiddenOtpInput = document.getElementById('otp-code');
    
    // Auto-focus first input
    otpInputs[0].focus();
    
    // Handle input
    otpInputs.forEach((input, index) => {
      // Allow only numbers
      input.addEventListener('input', (e) => {
        const value = e.target.value;
        if (value && !/^\d+$/.test(value)) {
          e.target.value = '';
          return;
        }
        
        // Move to next input
        if (value && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }
        
        updateHiddenInput();
      });
      
      // Handle backspace
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
          otpInputs[index - 1].focus();
        }
      });
    });
    
    // Update hidden input value
    function updateHiddenInput() {
      let otp = '';
      otpInputs.forEach(input => {
        otp += input.value;
      });
      hiddenOtpInput.value = otp;
    }
    
    // Handle paste
    otpForm.addEventListener('paste', (e) => {
      e.preventDefault();
      const paste = (e.clipboardData || window.clipboardData).getData('text');
      const numbers = paste.replace(/\D/g, '').split('').slice(0, 6);
      
      numbers.forEach((num, i) => {
        if (otpInputs[i]) {
          otpInputs[i].value = num;
        }
      });
      
      updateHiddenInput();
      
      // Focus the next empty input or submit if all are filled
      const emptyInput = Array.from(otpInputs).find(input => !input.value);
      if (emptyInput) {
        emptyInput.focus();
      } else {
        otpForm.submit();
      }
    });
    
    // Auto-submit when all fields are filled
    otpForm.addEventListener('input', () => {
      const allFilled = Array.from(otpInputs).every(input => input.value);
      if (allFilled) {
        otpForm.submit();
      }
    });
  });
</script>
</body>
</html>