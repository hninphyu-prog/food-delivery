<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];
$status = $_GET['status'] ?? 'pending'; // pending, approved, rejected

try {
    $sql = "
        SELECT 
            rd.id,
            rd.order_id,
            rd.amount,
            rd.deposited_at,
            rd.verified_at,
            rd.status,
            rd.rejection_reason,
            rd.transaction_slip,
            o.total_amount,
            o.delivery_fee,
            r.name as restaurant_name
        FROM rider_deposits rd
        LEFT JOIN orders o ON rd.order_id = o.order_id
        LEFT JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        WHERE rd.rider_id = ? AND rd.status = ?
        ORDER BY rd.deposited_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$rider_id, $status]);
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'deposits' => $deposits
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>