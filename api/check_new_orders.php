<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the last checked order ID from the request
$last_order_id = isset($_GET['last_order_id']) ? (int)$_GET['last_order_id'] : 0;

try {
    // Check for new orders assigned to this delivery person
    $stmt = $pdo->prepare("
        SELECT o.*, r.name as restaurant_name 
        FROM orders o 
        JOIN delivery d ON o.order_id = d.order_id 
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id 
        WHERE d.delivery_boy_id = :delivery_boy_id 
        AND o.order_id > :last_order_id 
        AND o.order_status IN ('pending', 'preparing')
        ORDER BY o.order_id DESC
    ");
    $stmt->execute([
        ':delivery_boy_id' => $_SESSION['user_id'],
        ':last_order_id' => $last_order_id
    ]);
    $new_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'new_orders' => $new_orders,
        'last_order_id' => $new_orders ? max(array_column($new_orders, 'order_id')) : $last_order_id
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>