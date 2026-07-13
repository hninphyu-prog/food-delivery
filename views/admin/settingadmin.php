<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "includes/header.php";

require_once '../../config/db.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$vendor_name = $_SESSION['name'];

// Fetch user details
function getUserDetails($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT user_id, name,  phone,  created_at FROM users WHERE user_id = ?");
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
        
        // Basic validation
        if (empty($name) ) {
            $update_message = 'Name is required fields.';
            $update_type = 'error';
        } else {
            try {
                $update_stmt = $pdo->prepare("UPDATE users SET name = ?,  phone = ? WHERE user_id = ?");
                $update_stmt->execute([$name,  $phone, $user_id]);
                
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
    <title>Admin Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main-content {
            padding: 20px;
        }
        
        .card {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: #f0f0f0;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .card-title {
            margin-top: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
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
            background-color: #ff9b00;
            color: #fff;
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
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .form-text {
            font-size: 12px;
            color: #666;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <h1><i class="fas fa-cog"></i> Admin Settings</h1>
        
        <?php if (!empty($update_message)): ?>
            <?= $update_message ?>
        <?php endif; ?>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Profile Information</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user_details['name'] ?? '') ?>" required>
                            </div>
                            
                           
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user_details['phone'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group text-right">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" onsubmit="return validatePassword()">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div id="passwordStrength" class="form-text"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div id="passwordMatch" class="form-text"></div>
                            </div>
                            
                            <div class="form-group text-right">
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.getElementById('passwordStrength');
            
            // Reset strength indicator
            strengthText.className = 'form-text';
            
            if (password.length === 0) {
                strengthText.textContent = '';
                return;
            }
            
            // Check password strength
            let strength = 0;
            let feedback = '';
            
            // Length check
            if (password.length >= 8) strength++;
            
            // Contains numbers
            if (/\d/.test(password)) strength++;
            
            // Contains letters
            if (/[a-zA-Z]/.test(password)) strength++;
            
            // Contains special characters
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Determine strength level
            if (password.length < 8) {
                feedback = 'Password is too short (minimum 8 characters)';
                strengthText.className = 'form-text text-danger';
            } else if (strength <= 2) {
                feedback = 'Password strength: Weak';
                strengthText.className = 'form-text text-danger';
            } else if (strength === 3) {
                feedback = 'Password strength: Good';
                strengthText.className = 'form-text text-warning';
            } else {
                feedback = 'Password strength: Strong';
                strengthText.className = 'form-text text-success';
            }
            
            strengthText.textContent = feedback;
        });

        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const confirmPassword = this.value;
            const newPassword = document.getElementById('new_password').value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if (confirmPassword === newPassword) {
                matchText.textContent = 'Passwords match';
                matchText.className = 'form-text text-success';
            } else {
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'form-text text-danger';
            }
        });

        // Form validation
        function validatePassword() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long.');
                return false;
            }
        };
    </script>
</body>
</html>