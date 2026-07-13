<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];
$deposit_id = $_POST['deposit_id'] ?? null;

if (!$deposit_id) {
    echo json_encode(['success' => false, 'message' => 'Deposit ID required']);
    exit;
}

try {
    // Verify deposit belongs to rider
    $stmt = $pdo->prepare("SELECT id FROM rider_deposits WHERE id = ? AND rider_id = ? AND status = 'rejected'");
    $stmt->execute([$deposit_id, $rider_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Deposit not found or cannot be resubmitted']);
        exit;
    }

    // Handle file upload
    if (!isset($_FILES['transaction_slip']) || $_FILES['transaction_slip']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Please upload a transaction slip']);
        exit;
    }

    $uploadDir = '../assets/transaction_slips/';
    $fileName = uniqid('slip_') . '_' . basename($_FILES['transaction_slip']['name']);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['transaction_slip']['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload transaction slip']);
        exit;
    }

    // Update deposit record
    $stmt = $pdo->prepare("
        UPDATE rider_deposits 
        SET transaction_slip = ?, status = 'pending', rejection_reason = NULL, verified_at = NULL, verified_by = NULL
        WHERE id = ? AND rider_id = ?
    ");
    $stmt->execute([$fileName, $deposit_id, $rider_id]);

    echo json_encode(['success' => true, 'message' => 'Deposit resubmitted successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>