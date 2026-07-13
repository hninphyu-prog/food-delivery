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
    // Get undeposited COD orders - Use FULL total_amount
    $stmt = $pdo->prepare("
        SELECT o.order_id, o.total_amount, o.delivery_fee
        FROM orders o
        JOIN delivery d ON o.order_id = d.order_id
        WHERE d.delivery_boy_id = ? 
        AND d.status = 'delivered'
        AND o.order_status = 'delivered'
        AND o.payment_method = 'cod'
        AND o.order_id NOT IN (
            SELECT order_id FROM rider_deposits 
            WHERE rider_id = ? AND status IN ('pending', 'approved')
        )
    ");
    
    $stmt->execute([$rider_id, $rider_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        echo json_encode(['success' => false, 'message' => 'No COD orders to deposit']);
        exit;
    }

    // Calculate total deposit amount - Use FULL total_amount
    $total_deposit = 0;
    foreach ($orders as $order) {
        $total_deposit += $order['total_amount']; // FIX: Use full amount
    }

    // Handle file upload
    if (!isset($_FILES['transaction_slip']) || $_FILES['transaction_slip']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Please upload a transaction slip']);
        exit;
    }

    $uploadDir = '../assets/transaction_slips/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = uniqid('slip_') . '_' . basename($_FILES['transaction_slip']['name']);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['transaction_slip']['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload transaction slip']);
        exit;
    }

    // Create deposit record for each order - Use FULL total_amount
    date_default_timezone_set('Asia/Yangon');
    $deposit_txn_id = 'DEP' . date('YmdHis') . $rider_id;
     
    foreach ($orders as $order) {
        $deposit_amount = $order['total_amount']; // FIX: Use full amount
        
        $stmt = $pdo->prepare("
            INSERT INTO rider_deposits (rider_id, order_id, deposit_txn_id, amount, transaction_slip, status, deposited_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$rider_id, $order['order_id'], $deposit_txn_id, $deposit_amount, $fileName]);
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Deposit submitted successfully for ' . count($orders) . ' orders',
        'total_amount' => $total_deposit,
        'deposit_txn_id' => $deposit_txn_id
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>