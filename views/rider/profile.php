<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/db.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$vendor_name = $_SESSION['name'];

// Fetch user details
function getUserDetails($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT user_id, name,  phone, address, created_at FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verify current password
function verifyCurrentPassword($pdo, $user_id, $current_password) {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return password_verify($current_password, $user['password']);
}

// Update password
function updatePassword($pdo, $user_id, $new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    return $stmt->execute([$hashed_password, $user_id]);
}

$user_details = getUserDetails($pdo, $user_id);

// Handle profile updates
$update_message = '';
$update_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile update
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        // $address = trim($_POST['address']);
        
        // Basic validation
        if (empty($name) ) {
            $update_message = 'Name is required fields.';
            $update_type = 'error';
        } else {
            try {
                $update_stmt = $pdo->prepare("UPDATE users SET name = ?,  phone = ?WHERE user_id = ?");
                $update_stmt->execute([$name,  $phone, $address, $user_id]);
                
                // Update session name if changed
                $_SESSION['name'] = $name;
                
                $update_message = 'Profile updated successfully!';
                $update_type = 'success';
                
                // Refresh user details
                $user_details = getUserDetails($pdo, $user_id);
                
            } catch (Exception $e) {
                $update_message = 'Error updating profile: ' . $e->getMessage();
                $update_type = 'error';
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $update_message = 'All password fields are required.';
            $update_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $update_message = 'New password and confirmation password do not match.';
            $update_type = 'error';
        } elseif (strlen($new_password) < 6) {
            $update_message = 'New password must be at least 6 characters long.';
            $update_type = 'error';
        } elseif (!verifyCurrentPassword($pdo, $user_id, $current_password)) {
            $update_message = 'Current password is incorrect.';
            $update_type = 'error';
        } else {
            try {
                if (updatePassword($pdo, $user_id, $new_password)) {
                    $update_message = 'Password changed successfully!';
                    $update_type = 'success';
                    
                    // Clear password fields
                    $_POST['current_password'] = $_POST['new_password'] = $_POST['confirm_password'] = '';
                } else {
                    $update_message = 'Error changing password. Please try again.';
                    $update_type = 'error';
                }
            } catch (Exception $e) {
                $update_message = 'Error changing password: ' . $e->getMessage();
                $update_type = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Restaurant Management</title>
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .home-link:hover {
            background: #5a6268;
            text-decoration: none;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .header-title h1 {
            color: #333;
            margin: 0;
            font-size: 28px;
        }
        
        .header-title p {
            color: #666;
            font-size: 16px;
            margin: 5px 0 0 0;
        }
        
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .profile-sections {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-left {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ff9b00;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #ff9b00, #ff6a00);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .section-title {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff9b00;
            font-size: 18px;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .strength-weak {
            color: #dc3545;
        }
        
        .strength-medium {
            color: #ffc107;
        }
        
        .strength-strong {
            color: #28a745;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .button-group {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                margin-left: 0;
                margin-bottom: 10px;
            }
        }

        /* Mobile-specific improvements */
        @media (max-width: 480px) {
            .profile-container {
                padding: 15px;
            }
            
            .profile-card {
                padding: 20px;
            }
            
            .home-link {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="header-left">
                <a href="dashboard.php" class="home-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <div class="header-title">
                    <h1><i class="fas fa-user-circle"></i> User Profile</h1>
                    <!-- <p>Manage your personal information and account settings</p> -->
                </div>
            </div>
        </div>
        
        <?php if ($update_message): ?>
            <div class="alert alert-<?= $update_type === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($update_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-sections">
            <!-- Personal Information Form -->
            <div class="profile-card">
                <h2 class="section-title">Personal Information</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($user_details['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($user_details['phone'] ?? '') ?>">
                    </div>
                    
                    <!-- <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"><?= htmlspecialchars($user_details['address'] ?? '') ?></textarea>
                    </div> -->
                    
                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password Section -->
            <div class="profile-card">
                <h2 class="section-title">Change Password</h2>
                <form method="POST" action="" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                        <div class="password-strength" id="passwordMatch"></div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="change_password" class="btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                        <button type="button" class="btn-secondary" onclick="clearPasswordForm()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthText.textContent = '';
                strengthText.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                strengthText.textContent = 'Weak password';
                strengthText.className = 'password-strength strength-weak';
            } else if (strength <= 4) {
                strengthText.textContent = 'Medium strength password';
                strengthText.className = 'password-strength strength-medium';
            } else {
                strengthText.textContent = 'Strong password';
                strengthText.className = 'password-strength strength-strong';
            }
        });
        
        // Password match indicator
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                matchText.className = 'password-strength';
                return;
            }
            
            if (newPassword === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.className = 'password-strength strength-strong';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.className = 'password-strength strength-weak';
            }
        });
        
        // Clear password form
        function clearPasswordForm() {
            document.getElementById('passwordForm').reset();
            document.getElementById('passwordStrength').textContent = '';
            document.getElementById('passwordMatch').textContent = '';
        }
        
        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New password and confirmation password do not match.');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long.');
                return false;
            }
        });
    </script>
</body>
</html>