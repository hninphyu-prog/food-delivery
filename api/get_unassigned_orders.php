<?php
// api/get_unassigned_orders.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

try {
    // Get orders that are accepted but NOT assigned to ANY rider yet
    $sql = "
    SELECT 
        o.order_id, 
        o.delivery_address, 
        o.total_amount, 
        o.lat, 
        o.lng, 
        o.created_at, 
        r.name AS restaurant_name,
        r.lat AS restaurant_lat,
        r.lng AS restaurant_lng
    FROM orders o
    JOIN restaurants r ON r.restaurant_id = o.restaurant_id
    WHERE o.order_status IN ('accepted','preparing','ready')
      AND o.order_id NOT IN (SELECT order_id FROM delivery WHERE order_id IS NOT NULL)
    ORDER BY o.created_at DESC
    LIMIT 50
    ";
    
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'orders' => $orders]);
    
} catch (Exception $e) {
    error_log("Get unassigned orders error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
?>