<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

try {
    // Fetch order to validate ownership and status
    $stmt = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $status = $order['order_status'];
    // Allow cancel only when order is still pending (before restaurant accepts)
    if ($status !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'This order can only be canceled before it is accepted']);
        exit;
    }

    // Update order status to canceled and store reason
    $upd = $pdo->prepare("UPDATE orders SET order_status = 'canceled', cancellation_reason = ? WHERE order_id = ? AND user_id = ?");
    $upd->execute([$reason !== '' ? $reason : 'Canceled by customer', $order_id, $user_id]);

    // Soft update delivery_tracking if exists
    try {
        $pdo->prepare("UPDATE delivery_tracking SET status = 'canceled' WHERE order_id = ?")->execute([$order_id]);
    } catch (Exception $e) { /* ignore if table not present */ }

    echo json_encode(['success' => true, 'message' => 'Order canceled successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
