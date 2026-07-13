<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/db.php';

// Check if user is logged in as vendor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'vendor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get restaurant_id from session or from restaurants table
$restaurant_id = $_SESSION['restaurant_id'] ?? null;
$user_id = $_SESSION['user_id'];

// If restaurant_id is not in session, get it from restaurants table
if (!$restaurant_id) {
    $restaurant_stmt = $pdo->prepare("SELECT restaurant_id FROM restaurants WHERE user_id = ?");
    $restaurant_stmt->execute([$user_id]);
    $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);

    if ($restaurant) {
        $restaurant_id = $restaurant['restaurant_id'];
        $_SESSION['restaurant_id'] = $restaurant_id;
    } else {
        echo json_encode(['success' => false, 'message' => 'Restaurant not found for this user']);
        exit;
    }
}

$action = $_POST['action'] ?? '';
$status = $_POST['status'] ?? '';

// Validate input
if ($action !== 'toggle_status' || !in_array($status, ['active','closed'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit;
}

try {
    // Update the restaurant status
    $stmt = $pdo->prepare('UPDATE restaurants SET status = ? WHERE restaurant_id = ?');
    $result = $stmt->execute([$status, (int)$restaurant_id]);
    
    if ($result) {
        // Verify the update was successful
        $verifyStmt = $pdo->prepare('SELECT status FROM restaurants WHERE restaurant_id = ?');
        $verifyStmt->execute([(int)$restaurant_id]);
        $updatedStatus = $verifyStmt->fetch(PDO::FETCH_COLUMN);
        
        if ($updatedStatus === $status) {
            echo json_encode([
                'success' => true, 
                'message' => 'Status updated successfully',
                'new_status' => $status
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Update verification failed'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database update failed'
        ]);
    }
} catch (Exception $e) {
    error_log("Status toggle error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>