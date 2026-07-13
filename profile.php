<?php
require_once '/config/db.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Get user data
$user = [];
$conn = getDBConnection();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    if (empty($name)) {
        $error = 'Name is required';
    } else {
        $name = $conn->real_escape_string($name);
        $phone = $conn->real_escape_string($phone);
        $address = $conn->real_escape_string($address);
        
        $sql = "UPDATE users SET name = '$name', phone = '$phone', address = '$address' WHERE user_id = $userId";
        
        if ($conn->query($sql) === TRUE) {
            $message = 'Profile updated successfully';
            $_SESSION['user_name'] = $name;
        } else {
            $error = 'Error updating profile: ' . $conn->error;
        }
    }
}

// Get updated user data
$sql = "SELECT * FROM users WHERE user_id = $userId";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Food&Me</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.6; margin: 0; padding: 20px; background: #f5f7fa; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .header h1 { color: #333; margin-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn { background: #4a6cf7; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: inline-block; margin-top: 20px; color: #4a6cf7; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Profile</h1>
            <p>Update your personal information</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                <small>Contact support to change your email address</small>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" 
                          rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="btn">Update Profile</button>
        </form>
        
        <div style="margin-top: 30px;">
            <h3>Account Actions</h3>
            <a href="change-password.php" class="btn" style="background: #6c757d;">Change Password</a>
        </div>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>
