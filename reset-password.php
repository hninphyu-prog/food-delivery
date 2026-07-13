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
$showForm = false;

// Debug information
error_log('Reset Password - Token from URL: ' . ($_GET['token'] ?? 'Not provided'));
error_log('Session token: ' . ($_SESSION['reset_token'] ?? 'Not set'));
error_log('Session expires: ' . ($_SESSION['reset_expires'] ?? 'Not set'));

// Check if token is valid
if (isset($_GET['token'])) {
    if (!isset($_SESSION['reset_token'])) {
        $error = 'No password reset request found. Please request a new reset link.';
    } elseif (!isset($_SESSION['reset_expires'])) {
        $error = 'Invalid reset session. Please request a new reset link.';
    } elseif (!isset($_SESSION['reset_email'])) {
        $error = 'Email not found in session. Please request a new reset link.';
    } elseif ($_SESSION['reset_token'] !== $_GET['token']) {
        $error = 'Invalid or expired reset token. Please request a new reset link.';
        error_log('Token mismatch - Session: ' . $_SESSION['reset_token'] . ', URL: ' . $_GET['token']);
    } elseif (strtotime($_SESSION['reset_expires']) <= time()) {
        $error = 'Reset link has expired. Please request a new reset link.';
    } else {
        
        $showForm = true;
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || empty($confirm_password)) {
                $error = 'Please fill in all fields';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long';
            } else {
                // Check if new password is different from old password
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                if ($conn->connect_error) {
                    $error = 'Database connection failed';
                } else {
                    $email = $_SESSION['reset_email'];
                    
                    // First, get the current hashed password
                    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        $stmt->close();
                        
                        // Verify if the new password is different from the old one
                        if (password_verify($password, $user['password'])) {
                            $error = 'New password cannot be the same as your current password';
                        } else {
                            // If passwords are different, proceed with the update
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                            $updateStmt->bind_param("ss", $hashedPassword, $email);
                            
                            if ($updateStmt->execute()) {
                                // Clear reset session
                                unset($_SESSION['reset_token']);
                                unset($_SESSION['reset_email']);
                                unset($_SESSION['reset_expires']);
                                
                                $success = 'Your password has been reset successfully. You can now <a href="login.php">sign in</a> with your new password.';
                                $showForm = false;
                            } else {
                                $error = 'Failed to reset password. Please try again.';
                            }
                            $updateStmt->close();
                        }
                    } else {
                        $error = 'User not found. Please request a new password reset link.';
                        $stmt->close();
                    }
                    $conn->close();
                }
            }
        }
    }
} else {
    $error = 'Invalid reset link. Please use the link from your email.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | Food&Me</title>
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
      background: #f9f9f9;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .reset-container {
       position: relative;
      background: #21252C;
      width: 400px;
      padding: 40px 35px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
      text-align: left;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .reset-container img {
      width: 80px;
      display: block;
      margin: 0 auto 20px;
    }

    h2 {
      font-size: 20px;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 10px;
      text-align: left;
    }

    p {
      font-size: 14px;
      color: #ffffff;
      margin-bottom: 25px;
    }

    .close-link {
    position: absolute;
    top: 20px;
    right: 20px;
    color: #777;
    font-size: 24px;
    text-decoration: none;
    transition: all 0.3s ease;
    z-index: 1000;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
}

.close-link:hover {
    color: #d4d4d4ff;
    transform: rotate(90deg);
}


    .input-box {
      position: relative;
      margin-bottom: 15px;
    }

    .input-box input {
    width: 100%;
    padding: 12px 45px 12px 45px; /* Equal padding on both sides */
    border-radius: 8px;
    font-size: 15px;
    outline: none;
    transition: 0.2s;
    height: 50px;
    background-color: transparent !important;
    color: #ffffff;
    border: 1px solid #555;
    box-sizing: border-box; /* Ensure padding is included in width */
}

.input-box i:first-child {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    font-size: 20px;
    pointer-events: none; /* Allow clicks to pass through */
}

/* Style for the eye icon */
.input-box i:last-child {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    font-size: 20px;
    cursor: pointer;
}

    .input-box input:focus {
      border-color: #ff6600;
    }

    .input-box i {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      color: #ffffff;
      font-size: 20px;
      cursor: pointer;
    }

    .strength-label {
      font-size: 13px;
      color: #666;
      margin: 10px 0 5px;
    }

    .strength-bar {
      width: 100%;
      height: 6px;
      background: #eee;
      border-radius: 4px;
      margin-bottom: 15px;
      overflow: hidden;
    }

    .bar-fill {
      height: 100%;
      width: 0;
      background: #ff6600;
      border-radius: 4px;
      transition: width 0.3s ease;
    }

    .requirements {
      font-size: 13px;
      color: #333;
      margin-bottom: 20px;
    }

    .requirements li {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 6px;
      color: #777;
    }

    .requirements li.valid {
      color: #28a745;
    }

    .requirements li i {
      font-size: 16px;
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

    @media (max-width: 450px) {
      .reset-container {
        width: 90%;
        padding: 30px 25px;
      }
    }
  </style>
</head>
<body>

  <div class="reset-container">
    <a href="index.php" class="close-link" title="Close">
        <i class='bx bx-x'></i>
    </a>
    <img src="https://cdn-icons-png.flaticon.com/512/6195/6195699.png" alt="Reset Icon">
    
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
    
    <?php if ($showForm): ?>
    <h2>Choose a new password</h2>
    <p>Enter the password you want to set for your account</p>

    <form method="POST" action="" id="resetPasswordForm">

   <div class="top-icons">
    <a href="index.php" class="close-link" title="Close">
        <i class='bx bx-x'></i>
    </a>
  </div>
    
      <div class="input-box">
    <i class='bx bx-lock'></i>
    <input type="password" id="password" name="password" placeholder="New password" required>
    <i class='bx bx-hide' id="togglePassword"></i>
    </div>

    <div class="input-box" style="margin-top: 15px;">
    <i class='bx bx-lock'></i>
    <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" required>
    <i class='bx bx-hide' id="toggleConfirmPassword"></i>
    </div>

      <button type="submit" class="btn" id="resetBtn">
        Reset Password
      </button>
    </form>
    <?php endif; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const password = document.getElementById('password');
      const confirmPassword = document.getElementById('confirmPassword');
      const togglePassword = document.getElementById('togglePassword');
      const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
      const form = document.getElementById('resetPasswordForm');
      const resetBtn = document.getElementById('resetBtn');

      // Add hover and focus effects to the reset button
      if (resetBtn) {
        // Hover effect
        resetBtn.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-2px)';
          this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
          this.style.background = 'linear-gradient(#000, #222)';
        });
        
        // Mouse leave effect
        resetBtn.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
          this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
          this.style.background = 'linear-gradient(#000, #222)';
        });
        
        // Focus effect
        resetBtn.addEventListener('focus', function() {
          this.style.outline = 'none';
          this.style.boxShadow = '0 0 0 3px #222)';
        });
        
        // Remove focus effect when clicking away
        resetBtn.addEventListener('blur', function() {
          this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
        });
        
        // Add active/click effect
        resetBtn.addEventListener('mousedown', function() {
          this.style.transform = 'translateY(1px)';
        });
        
        resetBtn.addEventListener('mouseup', function() {
          this.style.transform = 'translateY(-2px)';
        });
      }

      // Toggle password visibility
      function togglePasswordVisibility(input, icon) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        icon.className = type === 'password' ? 'bx bx-hide' : 'bx bx-show';
      }

      // Handle password visibility toggle
      if (togglePassword) {
        togglePassword.addEventListener('click', () => {
          togglePasswordVisibility(password, togglePassword);
        });
      }

      if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener('click', () => {
          togglePasswordVisibility(confirmPassword, toggleConfirmPassword);
        });
      }

      // Form submission
      if (form) {
        form.addEventListener('submit', function(e) {
          const passwordValue = password.value.trim();
          const confirmPasswordValue = confirmPassword.value.trim();
          
          // Basic client-side validation
          if (passwordValue.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long');
            return false;
          }
          
          if (passwordValue !== confirmPasswordValue) {
            e.preventDefault();
            alert('Passwords do not match');
            return false;
          }
          
          // Show loading state
          if (resetBtn) {
            resetBtn.disabled = true;
            resetBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Resetting...';
          }
          
          return true;
        });
      } else {
        continueBtn.classList.remove('active');
        continueBtn.disabled = true;
      }
    });
  </script>

</body>
</html>
