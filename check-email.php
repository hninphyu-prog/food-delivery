<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Check Your Email | Food&Me</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    body {
      background: #f9f9f9;
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

    .email-modal {
      
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

    .email-modal .top-icons {
      display: flex;
      justify-content: flex-end;
      position: absolute;
      top: 15px;
      right: 15px;
    }

    .email-modal .top-icons i {
      font-size: 22px;
      cursor: pointer;
      color: #777;
      transition: 0.2s;
    }

    .email-modal .top-icons i:hover {
      color: #000;
    }

    .email-modal .logo {
      font-size: 32px;
      color: #ff6600;
      margin-top: 15px;
      margin-bottom: 15px;
    }

    .email-modal h2 {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #ffffff;
    }

    .email-modal p {
      font-size: 15px;
      color: #ffffff;
      margin-bottom: 25px;
      line-height: 1.5;
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
      margin-top: 20px;
    }

    .btn:hover {
      background: #111;
      color: #ff6600;
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

    @media (max-width: 450px) {
      .email-modal {
        width: 90%;
        padding: 30px 25px;
      }
    }
  </style>
</head>
<body>

<div class="overlay">
  <div class="email-modal">
    <div class="top-icons">
      <a href="login.php" class="close-link" title="Close">
        <i class='bx bx-x'></i>
    </a>
    </div>

    <div class="logo"><i class='bx bx-envelope'></i></div>
    <h2>Check Your Email</h2>
    <p>We've sent a password reset link to your email. Please check your inbox and follow the instructions to reset your password.</p>

    <button class="btn" onclick="window.location.href='login.php'">Back to Sign In</button>
  </div>
</div>

<script>
  const overlay = document.querySelector('.overlay');
  const closeBtn = document.querySelector('.bx-x');

  closeBtn.addEventListener('click', () => overlay.style.display = 'none');
</script>

</body>
</html>
