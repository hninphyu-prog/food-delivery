<?php
// api/force_reassign.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Remove from delivery table
    $deleteStmt = $pdo->prepare("DELETE FROM delivery WHERE order_id = ?");
    $deleteStmt->execute([$order_id]);

    // 2. Reset order status back to 'accepted'
    $updateOrderStmt = $pdo->prepare("UPDATE orders SET order_status = 'accepted' WHERE order_id = ?");
    $updateOrderStmt->execute([$order_id]);

    // 3. Clear delivery tracking
    $updateTrackingStmt = $pdo->prepare("UPDATE delivery_tracking SET delivery_boy_id = NULL, status = 'pending' WHERE order_id = ?");
    $updateTrackingStmt->execute([$order_id]);

    // 4. Log the forced reassignment
    error_log("Order #$order_id force reassigned by vendor");

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order reassigned successfully']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Force reassign error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>