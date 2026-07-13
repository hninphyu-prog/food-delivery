<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_deposit'])) {
    $deposit_id = $_POST['deposit_id'] ?? null;
    $rejection_reason = $_POST['rejection_reason'] ?? '';
    
    if (!$deposit_id || empty($rejection_reason)) {
        echo json_encode(['success' => false, 'message' => 'Deposit ID and rejection reason are required']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // First get rider info for the response
        $rider_stmt = $pdo->prepare("
            SELECT u.name as rider_name, u.user_id as rider_id 
            FROM rider_deposits rd 
            JOIN users u ON rd.rider_id = u.user_id 
            WHERE rd.id = ?
        ");
        $rider_stmt->execute([$deposit_id]);
        $rider_info = $rider_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rider_info) {
            throw new Exception('Deposit not found');
        }
        
        // Update deposit status to rejected
        $stmt = $pdo->prepare("
            UPDATE rider_deposits 
            SET verified = 0, 
                status = 'rejected', 
                rejection_reason = ?,
                verified_by = ?,
                verified_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $rejection_reason,
            $_SESSION['admin_id'],
            $deposit_id
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Deposit rejected successfully',
            'rider_name' => $rider_info['rider_name'],
            'rider_id' => $rider_info['rider_id']
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>