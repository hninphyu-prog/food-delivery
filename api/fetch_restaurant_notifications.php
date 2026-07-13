<?php
header('Content-Type: application/json');
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    echo json_encode(['count' => 0]);
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'] ?? null;

if (!$restaurant_id) {
    echo json_encode(['count' => 0]);
    exit;
}

// Get unread notification count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM settlement_notifications 
    WHERE restaurant_id = ? AND status = 'pending'
");
$stmt->execute([$restaurant_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'count' => $result['count'] ?? 0
]);