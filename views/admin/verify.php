<?php
session_start();
require_once '../../config/db.php';

$message = ""; // collect messages here

// Validate request
if (!isset($_GET['user_id'])) {
    die("Invalid request.");
}

$user_id = $_GET['user_id'];

// Handle OTP submission
if (isset($_POST['verify'])) {
    $entered_otp = $_POST['otp']; // combined OTP from JS

    if (isset($_SESSION['otp'][$user_id])) {
        $otpData = $_SESSION['otp'][$user_id];

        // Check expiry (1 day = 86400 seconds)
        if (time() > $otpData['expires_at']) {
            $message = "<div class='alert error'>❌ OTP expired. Please request a new one from admin.</div>";
            unset($_SESSION['otp'][$user_id]); // clear expired
        } elseif ($entered_otp == $otpData['code']) {
            // ✅ OTP correct → update DB
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
            $stmt->execute([$user_id]);

            unset($_SESSION['otp'][$user_id]); // clear after success
            $message = "<div class='alert success'>
                ✅ Verification successful! You can now 
                <a href='../../login.php' style='color:#fff;text-decoration:underline;'>login</a>.
                Redirecting in 5 seconds...
            </div>";

            // Auto redirect after 5 seconds
            echo "<script>
                setTimeout(() => { window.location.href='../../login.php'; }, 5000);
            </script>";
        } else {
            $message = "<div class='alert error'>❌The OTP is Invalid . Please check and try again .</div>";
        }
    } else {
        $message = "<div class='alert error'>❌ No OTP found. Please request again from admin.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OTP Verification</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto+Mono&display=swap" rel="stylesheet">
<style>
/* Global */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1600&q=80') no-repeat center center fixed;
    background-size: cover;
    overflow: hidden;
}
.overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(8px);
}
/* Container */
.container {
    position: relative;
    background: #ffffffee;
    padding: 35px 30px 100px;
    padding-top:100px;
    border-radius: 25px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    text-align: center;
    width: 380px;
    z-index: 10;
    animation: fadeIn 0.8s ease forwards;
}
/* Animations */
@keyframes fadeIn { 0%{opacity:0;transform:translateY(-20px);} 100%{opacity:1;transform:translateY(0);} }
/* Heading */
h2 {
    font-family: 'Roboto Mono', monospace;
    font-size: 28px;
    color: #e67e22;
    margin-bottom: 20px;
}
/* Alert styles */
.alert {
    position: fixed;
    top: 25px;
    left: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    color: #fff;
    min-width: 250px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    z-index: 999;
    opacity: 0;
    transform: translateY(-20px);
    animation: slideIn 0.5s forwards;
}
.alert.success { background-color: #27ae60; }
.alert.error { background-color: #e74c3c; }
@keyframes slideIn { to { opacity: 1; transform: translateY(0); } }
.alert.fade-out { animation: fadeOut 0.5s forwards; }
@keyframes fadeOut { to { opacity: 0; transform: translateY(-20px); } }
/* OTP Boxes */
.otp-boxes {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}
.otp-boxes input {
    width: 40px;
    height: 50px;
    font-size: 26px;
    text-align: center;
    border: 2px solid #e67e22;
    border-radius: 12px;
    outline: none;
    transition: 0.3s;
    background: #fff3e6;
    color: #d35400;
    font-weight: bold;
}
.otp-boxes input:focus {
    border-color: #d35400;
    box-shadow: 0 0 10px #f39c12;
}
/* Button */
button {
    background: linear-gradient(135deg, #e67e22, #d35400);
    color: white;
    border: none;
    padding: 14px 0;
    font-size: 17px;
    border-radius: 12px;
    cursor: pointer;
    width: 100%;
    font-weight: 600;
    letter-spacing: 1px;
    transition: 0.3s;
}
button:hover {
    background: linear-gradient(135deg, #d35400, #e67e22);
    transform: scale(1.05);
}
/* JS Error placeholder */
#jsError {
    margin-bottom: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #c0392b;
}
</style>
</head>
<body>
<div class="overlay"></div>
<div class="container">
    <h2>🍴 OTP Verification</h2>

    <!-- PHP Message -->
    <?php if(!empty($message)) echo $message; ?>

    <!-- OTP Form (only if not success) -->
    <?php if (strpos($message, 'success') === false): ?>
    <form id="otpForm" method="POST">
        <div class="otp-boxes">
            <input type="text" maxlength="1" name="otp1" autofocus>
            <input type="text" maxlength="1" name="otp2">
            <input type="text" maxlength="1" name="otp3">
            <input type="text" maxlength="1" name="otp4">
            <input type="text" maxlength="1" name="otp5">
            <input type="text" maxlength="1" name="otp6">
        </div>
        <p id="jsError"></p>
        <button type="submit" name="verify">Verify</button>
    </form>
    <?php endif; ?>
</div>

<script>
// Auto-focus & backspace handling
const inputs = document.querySelectorAll('.otp-boxes input');
const form = document.getElementById('otpForm');
const jsError = document.getElementById('jsError');

if (form) {
    inputs.forEach((input, i) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g,''); // only digits
            if(input.value.length > 0 && i < inputs.length - 1){
                inputs[i+1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if(e.key === 'Backspace' && input.value === '' && i > 0){
                inputs[i-1].focus();
            }
        });
    });

    // Combine OTP and validate
    form.addEventListener('submit', (e) => {
        jsError.textContent = '';
        const otp = Array.from(inputs).map(input => input.value).join('');
        if(otp.length < 6){
            e.preventDefault();
            jsError.textContent = 'Please enter your OTP completely.';
        } else {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'otp';
            hidden.value = otp;
            form.appendChild(hidden);
        }
    });
}

// Auto hide PHP alerts
document.addEventListener('DOMContentLoaded', () => {
    const alert = document.querySelector('.alert');
    if(alert){
        setTimeout(() => alert.classList.add('fade-out'), 4000);
        setTimeout(() => alert.remove(), 4500);
    }
});
</script>
</body>
</html>
