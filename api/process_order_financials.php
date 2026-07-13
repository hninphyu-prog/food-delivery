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
    $pdo->beginTransaction();

    // Check for existing pending deposits FIRST
    $check_pending_stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_count 
        FROM rider_deposits 
        WHERE rider_id = ? AND status = 'pending'
    ");
    $check_pending_stmt->execute([$rider_id]);
    $pending_count = $check_pending_stmt->fetch(PDO::FETCH_ASSOC)['pending_count'];

    if ($pending_count > 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'You already have a pending deposit. Please wait for admin verification before making another deposit.']);
        exit;
    }

    // Get COD orders that haven't been deposited - use TOTAL_AMOUNT (not minus delivery fee)
    $orders_stmt = $pdo->prepare("
        SELECT o.order_id, o.total_amount as deposit_amount  
        FROM orders o
        JOIN delivery d ON o.order_id = d.order_id
        WHERE d.delivery_boy_id = ? 
        AND d.status = 'delivered'
        AND o.order_status = 'delivered'
        AND o.payment_method = 'cod'
        AND NOT EXISTS (
            SELECT 1 FROM rider_deposits rd 
            WHERE rd.order_id = o.order_id AND rd.status = 'approved'
        )
    ");
    $orders_stmt->execute([$rider_id]);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No COD orders to deposit']);
        exit;
    }

    $deposit_txn_id = 'DEP' . date('YmdHis') . $rider_id;
    $total_amount = 0;

    // Create deposit records with FULL total_amount
    $deposit_stmt = $pdo->prepare("
        INSERT INTO rider_deposits (rider_id, order_id, deposit_txn_id, amount, deposited_at, status)
        VALUES (?, ?, ?, ?, NOW(), 'pending')
    ");

    foreach ($orders as $order) {
        $deposit_stmt->execute([
            $rider_id, 
            $order['order_id'], 
            $deposit_txn_id, 
            $order['deposit_amount']  // This is the FULL total_amount
        ]);
        $total_amount += $order['deposit_amount'];
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'COD deposit submitted! Admin will verify.',
        'amount' => floatval($total_amount),
        'orders_count' => count($orders),
        'deposit_txn_id' => $deposit_txn_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>