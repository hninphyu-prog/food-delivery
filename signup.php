<?php
// Include configuration first
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/functions.php';

// Process form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verify reCAPTCHA first
  $recaptcha_secret = RECAPTCHA_SECRET_KEY;
  $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

  if (empty($recaptcha_response)) {
    $error = 'Please complete the reCAPTCHA verification';
  } else {
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
      'secret' => $recaptcha_secret,
      'response' => $recaptcha_response,
      'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
      'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($recaptcha_data)
      ]
    ];

    $context = stream_context_create($options);
    $recaptcha_result = file_get_contents($recaptcha_url, false, $context);
    $recaptcha_response_data = json_decode($recaptcha_result);

    if (!$recaptcha_response_data->success) {
      $error = 'reCAPTCHA verification failed. Please try again.';
    }
  }

  // Only proceed with form processing if reCAPTCHA was successful
  if (empty($error)) {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
      $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
      $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
      $error = 'Password must be at least 6 characters long';
    } else {
      // Attempt to register the user
      $result = registerUser($name, $email, $password);

      // Check for both 'success' and 'verify' status for backward compatibility
      if ($result['status'] === 'success' || $result['status'] === 'verify') {
        // Store user ID and email in session for verification
        session_start();
        $_SESSION['verification_user_id'] = $result['user_id'];
        $_SESSION['verification_email'] = $email;

        // Redirect to verification page
        header('Location: verify-otp.php?email=' . urlencode($email));
        exit();
      } else {
        $error = $result['message'];
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up | Food&Me</title>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
      overflow: hidden;
      background-image: url(assets/images/FoodBg.png);
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 100vh;
    }

    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.2);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 999;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    /* ===== Register Container Layout ===== */
    .register-container {
      background: rgba(255, 102, 0, 0.5);
      width: 380px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
      padding: 40px 35px;
      text-align: center;
      position: relative;
      animation: zoomIn 0.3s ease;
    }

    @keyframes slideUp {
      from {
        transform: translateY(20px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    /* Top Icons */
    .top-icons {
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: absolute;
      top: 15px;
      left: 15px;
      right: 15px;
    }

    .top-icons i {
      font-size: 35px;
      cursor: pointer;
      color: #ffffffff;
      transition: 0.2s;
    }

    .top-icons i:hover {
      color: #d4d4d4ff;
    }

    .logo {
      font-size: 44px;
      color: #ffffffff;
      margin-bottom: 4px;
    }

    .register-container h2 {
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
      ;
    }

    /* Password toggle button */
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
      color: #ff6600;
    }

    .btn-signup {
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

    .btn-signup:hover {
      background: #111;
      color: #ff6600;
    }

    .divider {
      display: flex;
      align-items: center;
      margin: 25px 0;
      /* matched Sign In spacing */
      color: #777;
      font-size: 13px;
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
      /* matched Sign In gap */
    }

    .social-btn {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: #fff;
      cursor: pointer;
      transition: 0.2s;
      font-size: 14px;
      font-weight: 500;
    }

    .social-btn:hover {
      border-color: #ff6600;
      color: #ff6600;
    }

    .social-btn i {
      font-size: 18px;
    }

    @media (max-width: 450px) {
      .register-container {
        width: 90%;
        padding: 30px 25px;
      }
    }
  </style>
</head>

<body>

  <?php
  $error = '';
  $success = '';

  // Process form submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
      $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
      $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
      $error = 'Passwords do not match.';
    } elseif (emailExists($email)) {
      $error = 'Email already exists. Please use a different email or login.';
    } else {
      // Attempt to register the user
      $result = registerUser($name, $email, $password);

      if ($result['status'] === 'success') {
        $success = $result['message'];
        // Redirect to verification page or show success message
        header('Location: verify-otp.php?email=' . urlencode($email));
        exit();
      } else {
        $error = $result['message'];
      }
    }
  }
  ?>

  <div class="overlay" id="registerOverlay">
    <div class="register-container">
      <div class="top-icons">
        <a href="login.php" class="back-link"><i class='bx bx-arrow-back' title="Back"></i></a>
        <a href="index.php" class="close-link"><i class='bx bx-x' id="closeBtn" title="Close"></i></a>
      </div>

      <div class="logo"><i class=''></i>ℱℴℴ𝒹&ℳℯ</div>
      <h2>Create Account</h2>

      <?php if ($error): ?>
        <div class="error-message" style="color: #ff6600; margin-bottom: 15px; background: #ffebf1; padding: 10px; border-radius: 5px;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="success-message" style="color: #28a745; margin-bottom: 15px; background: #e8f5e9; padding: 10px; border-radius: 5px;">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" id="signupForm">
        <div class="input-box">
          <i class='bx bx-user'></i>
          <input type="text" name="name" placeholder="Full Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>

        <div class="input-box">
          <i class='bx bx-envelope'></i>
          <input type="email" name="email" placeholder="Email" value="<?php 
            if (isset($_POST['email'])) {
              echo htmlspecialchars($_POST['email']);
            } elseif (isset($_GET['email'])) {
              echo htmlspecialchars(urldecode($_GET['email']));
            }
          ?>" required>
        </div>

        <div class="input-box">
          <i class='bx bx-lock-alt'></i>
          <input type="password" id="password" name="password" placeholder="Password (min 6 characters)" required>
          <button type="button" class="toggle-password" id="togglePassword">
            <i class='bx bx-hide'></i>
          </button>
        </div>

        <div class="input-box">
          <i class='bx bx-lock-alt'></i>
          <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
          <button type="button" class="toggle-password" id="toggleConfirmPassword">
            <i class='bx bx-hide'></i>
          </button>
        </div>

        <!-- reCAPTCHA Widget -->
        <div class="recaptcha-container" style="margin: 20px 0;">
          <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
          <?php if (!empty($error) && strpos($error, 'reCAPTCHA') !== false): ?>
            <div style="color: #e74c3c; font-size: 14px; margin-top: 5px;">
              <?php echo $error; ?>
            </div>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn-signup">Sign Up</button>
      </form>

      <!-- Social login buttons removed as per request -->
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle password visibility
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
      const confirmPasswordInput = document.getElementById('confirmPassword');

      // Toggle main password
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

      // Toggle confirm password
      if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
          const icon = this.querySelector('i');

          if (confirmPasswordInput.type === 'password') {
            confirmPasswordInput.type = 'text';
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
          } else {
            confirmPasswordInput.type = 'password';
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
          }
        });
      }

      // Close modal when clicking the close button or overlay
      const closeBtn = document.getElementById('closeBtn');
      const overlay = document.getElementById('registerOverlay');

      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          window.location.href = 'login.php';
        });
      }

      // Form validation
      const form = document.querySelector('form');
      if (form) {
        form.addEventListener('submit', function(e) {
          const password = document.getElementById('password').value;
          const confirmPassword = document.getElementById('confirmPassword').value;

          if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
          }

          if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
          }

          return true;
        });
      }
    });
  </script>

</body>

</html>