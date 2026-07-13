<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$email = $_GET['email'] ?? ($_SESSION['verification_email'] ?? '');

// Check for resend success
if (isset($_GET['resend_success']) && $_GET['resend_success'] === '1') {
    $success = 'A new verification code has been sent to your email.';
}

// Check if user is coming from registration
if (empty($email)) {
    // No email provided, redirect to signup
    header('Location: signup.php');
    exit();
}

// Handle Resend OTP
if (isset($_GET['resend']) && $_GET['resend'] === '1' && !empty($email)) {
    // Check if we've already processed a resend in this session
    if (!isset($_SESSION['last_resend']) || (time() - $_SESSION['last_resend'] > 30)) {
        // Generate new OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiry = date('Y-m-d H:i:s', time() + 60); // 1 minute expiry
        
        // Update session with new OTP
        $_SESSION['verification_code'] = $otp;
        $_SESSION['verification_expires'] = $otpExpiry;
        $_SESSION['last_resend'] = time(); // Track when we last sent an OTP
        
        // Send new OTP email
        $subject = "Your New Verification Code";
        $message = "
            <h2>New Verification Code</h2>
            <p>Your new verification code is: <strong>$otp</strong></p>
            <p>This code will expire in 1 minute.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        if (sendEmail($email, $subject, $message)) {
            $success = 'A new verification code has been sent to your email.';
        } else {
            $error = 'Failed to send new verification code. Please try again.';
        }
        
        // Redirect to remove resend parameter from URL
        header('Location: verify-otp.php?email=' . urlencode($email) . '&resend_success=1');
        exit();
    } else {
        $error = 'Please wait at least 30 seconds before requesting a new code.';
    }
}

// Handle OTP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);
    $userId = $_SESSION['verification_user_id'] ?? 0;
    $sessionOtp = $_SESSION['verification_code'] ?? null;
    $otpExpiry = $_SESSION['verification_expires'] ?? null;
    
    if (empty($otp)) {
        $error = 'Please enter the verification code';
    } elseif (strlen($otp) !== 6 || !is_numeric($otp)) {
        $error = 'Please enter a valid 6-digit code';
    } elseif (empty($sessionOtp) || empty($otpExpiry)) {
        $error = 'No verification code found. Please request a new one.';
    } elseif (time() > strtotime($otpExpiry)) {
        $error = 'Verification code has expired. Please request a new one.';
    } elseif ($otp !== $sessionOtp) {
        $error = 'Invalid verification code. Please try again.';
    } else {
        // OTP is valid, verify the user
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Mark user as verified
        $updateSql = "UPDATE users SET is_verified = 1 WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $userId);
        
        if ($updateStmt->execute()) {
            // Clear verification session data
            unset($_SESSION['verification_user_id']);
            unset($_SESSION['verification_email']);
            unset($_SESSION['verification_code']);
            unset($_SESSION['verification_expires']);
            
            // Set user as logged in
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            
            // Redirect to registration success page
            header('Location: register-success.php');
            exit();
        } else {
            $error = 'Failed to verify your account. Please try again.';
        }
        
        $updateStmt->close();
        $conn->close();
    }
}
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
      background-image: url(assets/images/FoodBg.png);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
       background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
     
    
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
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .otp-modal p {
      font-size: 15px;
      color: #ffffff;
      margin-bottom: 20px;
    }

    .otp-inputs {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 25px;
    }

    .otp-inputs input {
      flex: 1;
      max-width: 50px;
      min-width: 40px;
      padding: 12px;
      font-size: 18px;
      text-align: center;
      background-color: transparent !important;
      color: #ffffff; /* White text for better visibility */
      border: 1px solid #555;
      border-radius: 8px;
      outline: none;
      transition: 0.2s;
    }

    .otp-inputs input:focus {
      border-color: #ff6600;
    }

    .btn {
      width: 100%;
      padding: 12px;
      background: #000;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.2s;
    }

    .btn:hover {
      background: #111;
      color: #ff6600;
    }

    .resend {
      margin-top: 15px;
      font-size: 13px;
      color: #ffffff;
    }

    .resend a {
      color: #ff6600;
      text-decoration: none;
      font-weight: 500;
    }

    .resend a:hover {
      text-decoration: underline;
    }

    @media (max-width: 450px) {
      .otp-modal {
        width: 90%;
        padding: 30px 25px;
      }
      .otp-inputs input {
        max-width: 40px;
        padding: 10px;
        font-size: 16px;
      }
    }
  </style>
</head>
<body>

<div class="overlay">
  <div class="otp-modal">
    <div class="top-icons">
      <a href="signup.php" class="back-link"><i class='bx bx-arrow-back' title="Back to Sign Up"></i></a>
      <a href="index.php" class="close-link"><i class='bx bx-x' title="Close"></i></a>
    </div>

    <div class="logo"><i class='bx bx-restaurant'></i></div>
    <h2>Verify Your Email</h2>
    <p>We've sent a 6-digit verification code to <strong><?php echo htmlspecialchars($email); ?></strong></p>
    
    <?php if (!empty($error)): ?>
      <div class="error-message" style="color: #e74c3c; margin: 15px 0; padding: 10px; background: #fde8e8; border-radius: 5px; font-size: 14px;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <form method="POST" id="otp-form" class="otp-form">
      <div class="otp-inputs">
        <input type="text" name="otp1" maxlength="1" pattern="\d" inputmode="numeric" autofocus required>
        <input type="text" name="otp2" maxlength="1" pattern="\d" inputmode="numeric" required>
        <input type="text" name="otp3" maxlength="1" pattern="\d" inputmode="numeric" required>
        <input type="text" name="otp4" maxlength="1" pattern="\d" inputmode="numeric" required>
        <input type="text" name="otp5" maxlength="1" pattern="\d" inputmode="numeric" required>
        <input type="text" name="otp6" maxlength="1" pattern="\d" inputmode="numeric" required>
      </div>
      <input type="hidden" name="otp" id="otp-code">
      <button type="submit" class="btn">Verify Email</button>
    </form>
    
    <?php if (!empty($success)): ?>
      <div class="success-message" style="color: #2ecc71; margin: 15px 0; padding: 10px; background: #e8f9f0; border-radius: 5px; font-size: 14px;">
        <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>
    
    <div class="resend">
      Didn't receive code? <a href="?email=<?php echo urlencode($email); ?>&resend=1">Resend Code</a>
    </div>
    
    
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const otpForm = document.getElementById('otp-form');
    const otpInputs = document.querySelectorAll('.otp-inputs input');
    const hiddenOtpInput = document.getElementById('otp-code');
    
    // Auto-focus first input on page load
    if (otpInputs.length > 0) {
      otpInputs[0].focus();
    }

    // Handle input events for OTP fields
    otpInputs.forEach((input, index) => {
      // Allow only numbers
      input.addEventListener('input', (e) => {
        // Remove any non-digit characters
        input.value = input.value.replace(/\D/g, '');
        
        // Auto-focus next input if current input has a value
        if (input.value.length === 1 && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }
        
        // Update hidden input value
        updateHiddenOtp();
      });
      
      // Handle backspace and arrow keys
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value === '' && index > 0) {
          // Move to previous input on backspace if current is empty
          otpInputs[index - 1].focus();
        } else if (e.key === 'ArrowLeft' && index > 0) {
          // Move left with left arrow key
          e.preventDefault();
          otpInputs[index - 1].focus();
        } else if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
          // Move right with right arrow key
          e.preventDefault();
          otpInputs[index + 1].focus();
        } else if (e.key === 'Enter' && index === otpInputs.length - 1) {
          // Submit form on Enter in the last field
          e.preventDefault();
          otpForm.submit();
        }
      });
    });
    
    // Update hidden input with complete OTP
    function updateHiddenOtp() {
      let otp = '';
      otpInputs.forEach(input => {
        otp += input.value;
      });
      hiddenOtpInput.value = otp;
    }
    
    // Handle form submission
    otpForm.addEventListener('submit', function(e) {
      // Prevent form submission if OTP is not complete
      if (hiddenOtpInput.value.length !== 6) {
        e.preventDefault();
        alert('Please enter the complete 6-digit verification code.');
        // Focus on first empty field
        for (let i = 0; i < otpInputs.length; i++) {
          if (!otpInputs[i].value) {
            otpInputs[i].focus();
            break;
          }
        }
      }
    });
    
    // Handle paste event for OTP
    otpForm.addEventListener('paste', function(e) {
      e.preventDefault();
      const pasteData = e.clipboardData.getData('text/plain').trim();
      const numbers = pasteData.replace(/\D/g, ''); // Keep only digits
      
      // Fill the OTP fields with the pasted data
      for (let i = 0; i < Math.min(numbers.length, otpInputs.length); i++) {
        otpInputs[i].value = numbers[i];
      }
      
      // Update hidden input and focus the next empty field or submit
      updateHiddenOtp();
      for (let i = 0; i < otpInputs.length; i++) {
        if (!otpInputs[i].value) {
          otpInputs[i].focus();
          return;
        }
      }
      // If all fields are filled, submit the form
      otpForm.submit();
    });
  });
</script>

</body>
</html>
