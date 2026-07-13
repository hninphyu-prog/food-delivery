<?php
// api/get_assigned_deliveries.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'error' => 'not_allowed']);
    exit;
}

$current_rider_id = (int)$_SESSION['user_id'];

try {
    // Get deliveries assigned to CURRENT RIDER ONLY with timeout info
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id, 
            o.delivery_address, 
            o.total_amount, 
            o.order_status, 
            o.lat, 
            o.lng, 
            u.name AS customer_name,  
            u.phone AS customer_phone,
            r.name AS restaurant_name,
            r.lat AS restaurant_lat,
            r.lng AS restaurant_lng,
            d.delivery_boy_id,
            d.status AS delivery_status,
            d.created_at as assigned_time,
            TIMESTAMPDIFF(MINUTE, d.created_at, NOW()) as minutes_since_assigned,
            u2.name as rider_name
        FROM delivery d
        JOIN orders o ON o.order_id = d.order_id
        JOIN restaurants r ON r.restaurant_id = o.restaurant_id
        JOIN users u ON u.user_id = o.user_id 
        JOIN users u2 ON u2.user_id = d.delivery_boy_id 
        WHERE d.delivery_boy_id = ?  
        AND d.status IN ('assigned','picked','on_the_way','ready')
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$current_rider_id]);
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order items for each delivery
    foreach ($deliveries as &$delivery) {
        $itemsStmt = $pdo->prepare("
            SELECT oi.item_id, oi.quantity, oi.price, mi.name
            FROM order_items oi
            JOIN menu_items mi ON mi.item_id = oi.item_id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$delivery['order_id']]);
        $delivery['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true, 
        'deliveries' => $deliveries,
        'current_rider_id' => $current_rider_id
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_assigned_deliveries: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
?>