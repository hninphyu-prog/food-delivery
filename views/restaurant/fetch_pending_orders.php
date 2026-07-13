<?php
// views/restaurant/fetch_pending_orders.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/db.php';


$restaurant_id = $_SESSION['restaurant_id'] ?? null;
if (!$restaurant_id) {
    echo json_encode(['success' => false, 'message' => 'No restaurant selected.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            o.order_id,
            o.total_amount as total,
            o.order_status,
            o.created_at,
            u.name as customer_name,
            u.phone as customer_phone,
            d.delivery_boy_id,
            rider.name as rider_name,
            rider.phone as rider_phone
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN delivery d ON o.order_id = d.order_id
        LEFT JOIN users rider ON d.delivery_boy_id = rider.user_id
        WHERE o.restaurant_id = ? AND o.order_status IN ('pending', 'preparing', 'on_the_way', 'ready', 'accepted')
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$restaurant_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get items for each order
    foreach ($orders as &$order) {
        $item_stmt = $pdo->prepare("
            SELECT oi.quantity, mi.name
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            WHERE oi.order_id = ?
        ");
        $item_stmt->execute([$order['order_id']]);
        $order['items'] = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'orders' => $orders]);
    
} catch (Exception $e) {
    error_log("Fetch Orders Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
?>

