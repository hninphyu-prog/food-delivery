<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['restaurant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];
$action = $_POST['action'] ?? '';
$notification_id = $_POST['notification_id'] ?? 0;
$settlement_id = $_POST['settlement_id'] ?? 0;
$reason = $_POST['reason'] ?? '';

try {
    if ($action === 'confirm') {
        // Update notification status to confirmed
        $stmt = $pdo->prepare("
            UPDATE settlement_notifications 
            SET status = 'confirmed', confirmed_at = NOW() 
            WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([$notification_id, $restaurant_id]);
        
        // Update settlement record
        $stmt = $pdo->prepare("
            UPDATE restaurant_settlements 
            SET restaurant_confirmed = 1, confirmed_at = NOW() 
            WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([$settlement_id, $restaurant_id]);
        
        echo json_encode(['success' => true, 'message' => 'Payment confirmed successfully']);
        
    } elseif ($action === 'reject') {
        // Update notification status to rejected
        $stmt = $pdo->prepare("
            UPDATE settlement_notifications 
            SET status = 'rejected', rejection_reason = ?, rejected_at = NOW() 
            WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([$reason, $notification_id, $restaurant_id]);
        
        // Update settlement record
        $stmt = $pdo->prepare("
            UPDATE restaurant_settlements 
            SET restaurant_confirmed = 0, rejection_reason = ?, confirmed_at = NOW() 
            WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([$reason, $settlement_id, $restaurant_id]);
        
        echo json_encode(['success' => true, 'message' => 'Payment rejection recorded']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Settlement confirmation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>