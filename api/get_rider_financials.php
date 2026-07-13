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
    // 1. COD to Pay Admin (COD orders that are delivered but not deposited)
    $cod_stmt = $pdo->prepare("
    SELECT COALESCE(SUM(o.total_amount), 0) as cod_balance
    FROM orders o
    JOIN delivery d ON o.order_id = d.order_id
    WHERE d.delivery_boy_id = ? 
    AND d.status = 'delivered'
    AND o.order_status = 'delivered'
    AND o.payment_method = 'cod'
    -- EXCLUDE orders with approved OR pending deposits
    -- ONLY include orders with no deposits OR rejected deposits
    AND NOT EXISTS (
        SELECT 1 FROM rider_deposits rd 
        WHERE rd.order_id = o.order_id 
        AND rd.status IN ('approved', 'pending')
    )
");
    $cod_stmt->execute([$rider_id]);
    $cod_balance = $cod_stmt->fetch(PDO::FETCH_ASSOC)['cod_balance'];

    // 2. Total delivery fees earned (from ALL delivered orders)
    $delivery_stmt = $pdo->prepare("
        SELECT COALESCE(SUM(o.delivery_fee), 0) as delivery_balance
        FROM orders o
        JOIN delivery d ON o.order_id = d.order_id
        WHERE d.delivery_boy_id = ? 
        AND d.status = 'delivered'
        AND o.order_status = 'delivered'
    ");
    $delivery_stmt->execute([$rider_id]);
    $delivery_balance = $delivery_stmt->fetch(PDO::FETCH_ASSOC)['delivery_balance'];

    // 3. Pending deposits
    $pending_stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as pending_deposits
        FROM rider_deposits 
        WHERE rider_id = ? AND verified = 0
    ");
    $pending_stmt->execute([$rider_id]);
    $pending_deposits = $pending_stmt->fetch(PDO::FETCH_ASSOC)['pending_deposits'];

    // 4. Net balance (what rider would get after settling COD)
    $net_balance = $delivery_balance - $cod_balance;

    $transactions_stmt = $pdo->prepare("
    (SELECT 
        'delivery_fee' as type,
        o.order_id,
        o.delivery_fee as amount,
        d.created_at,
        CONCAT('Delivery fee - Order #', o.order_id) as description,
        r.name as restaurant_name
    FROM orders o
    JOIN delivery d ON o.order_id = d.order_id
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    WHERE d.delivery_boy_id = ? 
    AND d.status = 'delivered'
    AND o.order_status = 'delivered')
    
    UNION ALL
    
    (SELECT 
        'cod_deposit' as type,
        rd.order_id,
        -rd.amount as amount,  -- Negative for deposits (money going out)
        rd.deposited_at as created_at,
        CONCAT('COD deposit - Order #', rd.order_id) as description,
        r.name as restaurant_name
    FROM rider_deposits rd
    JOIN orders o ON rd.order_id = o.order_id
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    WHERE rd.rider_id = ?)
    
    ORDER BY created_at DESC
    LIMIT 20
");
    $transactions_stmt->execute([$rider_id, $rider_id]);
    $transactions = $transactions_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'cod_balance' => floatval($cod_balance),
        'delivery_balance' => floatval($delivery_balance),
        'net_balance' => floatval($net_balance),
        'pending_deposits' => floatval($pending_deposits),
        'transactions' => $transactions  // ✅ NOW INCLUDED!
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
