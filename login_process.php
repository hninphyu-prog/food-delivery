<?php
session_start();
require_once '/config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_phone = isset($_POST['email_or_phone']) ? trim($_POST['email_or_phone']) : '';
    $pwd = isset($_POST['pwd']) ? (string)$_POST['pwd'] : '';

    if ($email_or_phone === '' || $pwd === '') {
        $error = 'Please enter your email and password.';
    } else {
        // Fetch all users with this email (multiple accounts with same email but different roles)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email_or_phone]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $user = null;
        // Check each user with this email
        foreach ($users as $u) {
            if (password_verify($pwd, $u['password'])) {
                $user = $u;
                break; // Found a matching user, stop checking
            }
        }

        if (!empty($users) && (int)$users[0]['is_verified'] === 0) {
            $error = 'Please verify your email before logging in.';
        } elseif ($user) {
            // Login success
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Determine the redirection page based on user role
            switch ($user['role']) {
                case 'admin':
                    header('Location: views/admin/dashboard.php');
                    exit;
                 case 'customer':
                    header('Location: views/customer/dashboard.php');
                    exit;
                case 'vendor':
                    // Fetch the restaurant_id for the owner and store it in the session
                    $stmt_restaurant = $pdo->prepare('SELECT restaurant_id FROM restaurants WHERE user_id = ? LIMIT 1');
                    $stmt_restaurant->execute([$user['user_id']]);
                    $restaurant = $stmt_restaurant->fetch(PDO::FETCH_ASSOC);

                    if ($restaurant) {
                        $_SESSION['restaurant_id'] = $restaurant['restaurant_id'];
                        header('Location: views/restaurant/index.php');
                        exit;
                    } else {
                        // Handle case where an owner has no linked restaurant
                        $error = 'Your account is not linked to a restaurant.';
                        session_unset();
                        session_destroy();
                        header('Location: login.php');
                        exit;
                    }
                    break;
                case 'delivery':
                    header('Location: views/rider/dashboard.php');
                    exit;
                default:
                    header('Location: index.php');
                    exit;
            }
        } else {
            // Login failed
            $error = 'Invalid email or password.';
        }
    }
}

// If login failed, redirect back to login page with error
if (!empty($error)) {
    $_SESSION['error'] = $error;
    $_SESSION['form_data'] = [
        'email' => $_POST['email_or_phone'] ?? ''
    ];
    header('Location: login.php');
    exit;
}
?>
