<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['deposit_txn_id']) || !isset($data['amount']) || !isset($data['order_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$deposit_txn_id = $data['deposit_txn_id'];
$amount = floatval($data['amount']);
$order_ids = $data['order_ids'];

try {
    $pdo->beginTransaction();

    // Verify the amount matches the expected deposit amount
    $verify_stmt = $pdo->prepare("
        SELECT SUM(o.total_amount - o.delivery_fee) as expected_amount
        FROM orders o
        JOIN delivery d ON o.order_id = d.order_id
        WHERE d.delivery_boy_id = ? 
        AND o.order_id IN (" . implode(',', array_fill(0, count($order_ids), '?')) . ")
        AND d.status = 'delivered'
        AND o.order_status = 'delivered'
    ");
    
    $verify_params = array_merge([$rider_id], $order_ids);
    $verify_stmt->execute($verify_params);
    $expected_amount = $verify_stmt->fetch(PDO::FETCH_ASSOC)['expected_amount'];
    
    if (abs($amount - $expected_amount) > 0.01) {
        throw new Exception("Deposit amount doesn't match expected amount");
    }

    // Insert deposit records for each order
    $deposit_stmt = $pdo->prepare("
        INSERT INTO rider_deposits (rider_id, order_id, deposit_txn_id, amount, deposited_at, verified)
        VALUES (?, ?, ?, ?, NOW(), 0)
    ");

    foreach ($order_ids as $order_id) {
        // Calculate amount for this specific order (total - delivery fee)
        $order_amount_stmt = $pdo->prepare("
            SELECT (total_amount - delivery_fee) as deposit_amount 
            FROM orders 
            WHERE order_id = ?
        ");
        $order_amount_stmt->execute([$order_id]);
        $order_amount = $order_amount_stmt->fetch(PDO::FETCH_ASSOC)['deposit_amount'];
        
        $deposit_stmt->execute([$rider_id, $order_id, $deposit_txn_id, $order_amount]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Deposit submitted successfully. Waiting for admin verification.',
        'deposited_orders' => count($order_ids),
        'total_amount' => $amount
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Deposit submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Deposit failed: ' . $e->getMessage()]);
}
?>