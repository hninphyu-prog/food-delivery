<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

try {
    // Update order status to delivered
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET order_status = 'delivered' 
        WHERE order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    
    // Update delivery status
    $stmt = $pdo->prepare("
        UPDATE delivery 
        SET status = 'delivered' 
        WHERE order_id = :order_id AND delivery_boy_id = :delivery_boy_id
    ");
    $stmt->execute([
        ':order_id' => $order_id,
        ':delivery_boy_id' => $_SESSION['user_id']
    ]);
    
    // Update delivery tracking status
    $stmt = $pdo->prepare("
        UPDATE delivery_tracking 
        SET status = 'delivered' 
        WHERE order_id = :order_id AND delivery_boy_id = :delivery_boy_id
    ");
    $stmt->execute([
        ':order_id' => $order_id,
        ':delivery_boy_id' => $_SESSION['user_id']
    ]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>