<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$order_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID or status']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // 1. Verify the order belongs to this rider and can be updated
    $check_stmt = $pdo->prepare("
        SELECT o.order_id, o.order_status, d.status as delivery_status, o.payment_method
        FROM orders o 
        JOIN delivery d ON o.order_id = d.order_id 
        WHERE o.order_id = ? AND d.delivery_boy_id = ?
    ");
    $check_stmt->execute([$order_id, $rider_id]);
    $order = $check_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found or not assigned to you']);
        exit;
    }

    // 2. Validate status transition for riders
    // Support DB statuses: assigned -> picked -> delivered
    // Also accept legacy 'pick_up' as equivalent to 'picked'
    $valid_transitions = [
        'assigned' => ['picked', 'pick_up'],
        'picked'   => ['delivered'],
        'pick_up'  => ['delivered'],
        'delivered'=> []
    ];

    $current_delivery_status = $order['delivery_status'];
    if (!in_array($status, $valid_transitions[$current_delivery_status] ?? [])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => "Invalid status transition from $current_delivery_status to $status"]);
        exit;
    }

    // 3. Additional check: For 'picked'/'pick_up', ensure order is ready
    if (($status === 'picked' || $status === 'pick_up') && $order['order_status'] !== 'ready') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => "Cannot pick up order - restaurant hasn't marked it as ready yet"]);
        exit;
    }

    // 4. Update delivery status
    $update_delivery_stmt = $pdo->prepare("
        UPDATE delivery
        SET status = ?, created_at = CURRENT_TIMESTAMP
        WHERE order_id = ? AND delivery_boy_id = ?
    ");
    $update_delivery_stmt->execute([$status, $order_id, $rider_id]);

    // 5. Update order/tracking based on delivery status
    $payment_status_updated = false;
    if ($status === 'picked' || $status === 'pick_up') {
        // When rider picks up, change order status to 'on_the_way'
        $update_order_stmt = $pdo->prepare("
            UPDATE orders
            SET order_status = 'on_the_way'
            WHERE order_id = ?
        ");
        $update_order_stmt->execute([$order_id]);

        // Update delivery tracking status
        $update_tracking_stmt = $pdo->prepare("
            UPDATE delivery_tracking
            SET status = 'on_the_way'
            WHERE order_id = ?
        ");
        $update_tracking_stmt->execute([$order_id]);

    } elseif ($status === 'delivered') {
        // When rider delivers, change order status to 'delivered' and mark payment
        $update_order_stmt = $pdo->prepare("
            UPDATE orders
            SET order_status = 'delivered',
                payment_status = 'paid'
            WHERE order_id = ?
        ");
        $update_order_stmt->execute([$order_id]);

        // Update delivery tracking status
        $update_tracking_stmt = $pdo->prepare("
            UPDATE delivery_tracking
            SET status = 'delivered'
            WHERE order_id = ?
        ");
        $update_tracking_stmt->execute([$order_id]);

        $payment_status_updated = true;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Order status updated to $status",
        'payment_status_updated' => $payment_status_updated,
        'payment_method' => $order['payment_method']
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>