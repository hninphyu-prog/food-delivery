<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration Successful | Food&Me</title>
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

    .container {
      background: #21252C;
      width: 400px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
      padding: 40px 35px;
      text-align: center;
      animation: zoomIn 0.3s ease;
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

    .success-icon {
      width: 70px;
      height: 70px;
      background: #ff6600;
      color: #fff;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 36px;
      margin: 0 auto 20px;
    }

    h2 {
      font-weight: 600;
      font-size: 22px;
      color: #ff6600;
      margin-bottom: 10px;
    }

    p {
      font-size: 15px;
      color: #ffffff;
      margin-bottom: 30px;
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
    }

    .btn:hover {
      background: #111;
      color: #ff6600;
    }

    .footer-note {
      font-size: 13px;
      color: #ffffff;
      margin-top: 20px;
    }

    @media (max-width: 450px) {
      .container {
        width: 90%;
        padding: 30px 25px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="success-icon">
      <i class='bx bx-check'></i>
    </div>
    <h2>Registration Successful!</h2>
    <p>Your account has been created successfully.<br></p>

    <button class="btn" onclick="window.location.href='login.php'">Continue to Sign In</button>

    <p class="footer-note">© 2025 Food&Me. All rights reserved.</p>
  </div>
</body>
</html>
