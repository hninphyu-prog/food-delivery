<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) && php_sapi_name() !== 'cli') {
    exit('Unauthorized');
}

try {
    $pdo->beginTransaction();

    // Find orders assigned but not picked up within 15 minutes
    $timeoutMinutes = 15; // 15 minutes timeout
    $timeoutTime = date('Y-m-d H:i:s', strtotime("-$timeoutMinutes minutes"));
    
    $stmt = $pdo->prepare("
        SELECT d.delivery_id, d.order_id, d.delivery_boy_id, o.restaurant_id, u.name as rider_name
        FROM delivery d 
        JOIN orders o ON o.order_id = d.order_id 
        JOIN users u ON u.user_id = d.delivery_boy_id
        WHERE d.status = 'assigned' 
        AND d.created_at < ? 
        AND o.order_status IN ('preparing', 'accepted')
    ");
    $stmt->execute([$timeoutTime]);
    $timedOutOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $unassignedCount = 0;
    
    foreach ($timedOutOrders as $order) {
        // 1. Remove from delivery table
        $deleteStmt = $pdo->prepare("DELETE FROM delivery WHERE delivery_id = ?");
        $deleteStmt->execute([$order['delivery_id']]);
        
        // 2. Reset order status back to 'accepted' so other riders can see it
        $updateOrderStmt = $pdo->prepare("UPDATE orders SET order_status = 'accepted' WHERE order_id = ?");
        $updateOrderStmt->execute([$order['order_id']]);
        
        // 3. Clear delivery tracking
        $updateTrackingStmt = $pdo->prepare("UPDATE delivery SET delivery_boy_id = NULL, status = 'pending' WHERE order_id = ?");
        $updateTrackingStmt->execute([$order['order_id']]);
        
        // 4. Notify restaurant about timeout
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (user_id, restaurant_id, order_id, recipient_type, title, message) 
            VALUES (?, ?, ?, 'restaurant', ?, ?)
        ");
        $notifStmt->execute([
            $order['restaurant_id'],
            $order['restaurant_id'],
            $order['order_id'],
            "Order #{$order['order_id']} Rider Timeout",
            "Rider {$order['rider_name']} did not pick up order #{$order['order_id']} within $timeoutMinutes minutes. Order is now available for other riders."
        ]);
        
        // 5. Log the timeout for debugging
        error_log("TIMEOUT: Order #{$order['order_id']} unassigned from rider {$order['rider_name']} (ID: {$order['delivery_boy_id']})");
        
        $unassignedCount++;
    }
    
    $pdo->commit();
    
    if (php_sapi_name() === 'cli') {
        echo "Unassigned $unassignedCount orders due to timeout\n";
        
        // Debug: Show which orders were unassigned
        if ($unassignedCount > 0) {
            foreach ($timedOutOrders as $order) {
                echo " - Order #{$order['order_id']} from rider {$order['rider_name']}\n";
            }
        }
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Timeout monitor error: " . $e->getMessage());
    
    if (php_sapi_name() === 'cli') {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>