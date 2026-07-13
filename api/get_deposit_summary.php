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
    //get deposit summary by status
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
            COALESCE(SUM(
            CASE 
                WHEN status = 'approved' AND DATE(deposited_at) = CURDATE() 
                THEN amount 
                ELSE 0 
            END
            ), 0) AS approved_amount,
            COALESCE(SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END), 0) as rejected_amount
        FROM rider_deposits 
        WHERE rider_id = ?
    ");
    $stmt->execute([$rider_id]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'pending_amount' => floatval($summary['pending_amount']),
        'approved_amount' => floatval($summary['approved_amount']),
        'rejected_amount' => floatval($summary['rejected_amount'])
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
