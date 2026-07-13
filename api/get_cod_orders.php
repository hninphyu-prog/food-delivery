<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];

try {
    // Get COD orders including deposit status and rejection info
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id,
            o.total_amount,
            o.delivery_fee,
            (o.total_amount - o.delivery_fee) as deposit_amount,
            o.delivery_address,
            r.name as restaurant_name,
            u.name as customer_name,
            d.created_at as delivered_at,
            o.payment_status,
            rd.status as deposit_status,
            rd.rejection_reason,
            rd.verified_at,
            rd.transaction_slip,
            rd.deposited_at
        FROM orders o
        JOIN delivery d ON o.order_id = d.order_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN rider_deposits rd ON o.order_id = rd.order_id AND rd.rider_id = ?
        WHERE d.delivery_boy_id = ? 
        AND d.status = 'delivered'
        AND o.order_status = 'delivered'
        AND o.payment_method = 'cod'
        -- EXCLUDE orders with approved deposits
        -- INCLUDE orders with: no deposits OR pending deposits OR rejected deposits
        AND (rd.id IS NULL OR rd.status IN ('pending', 'rejected'))
        ORDER BY 
            CASE WHEN rd.status = 'rejected' THEN 0 ELSE 1 END,
            d.created_at DESC
    ");
    
    $stmt->execute([$rider_id, $rider_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals: ONLY include orders that need fresh deposit (not rejected)
    $total_cod = 0;
    $total_deposit = 0;
    
    foreach ($orders as $order) {
        // Only include in total if deposit status is NOT 'rejected'
        if ($order['deposit_status'] !== 'rejected') {
            $total_cod += $order['total_amount'];
            $total_deposit += ($order['total_amount'] - $order['delivery_fee']);
        }
    }

    echo json_encode([
        'success' => true,
        'total_cod' => floatval($total_cod),
        'total_to_deposit' => floatval($total_deposit),
        'orders' => $orders,
        'orders_count' => count($orders)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>