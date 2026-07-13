<?php
// api/get_order_status.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'error' => 'not_allowed']);
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'missing_order']);
    exit;
}

try {
    // Get current order status and assigned rider
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id,
            o.order_status,
            d.delivery_boy_id,
            d.status as delivery_status,
            d.created_at as assigned_time,
            u.name as assigned_rider_name,
            TIMESTAMPDIFF(MINUTE, d.created_at, NOW()) as minutes_since_assigned
        FROM orders o
        LEFT JOIN delivery d ON d.order_id = o.order_id
        LEFT JOIN users u ON u.user_id = d.delivery_boy_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'order_not_found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'order' => $order]);
    
} catch (Exception $e) {
    error_log("Get order status error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
?>