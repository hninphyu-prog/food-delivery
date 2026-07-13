<?php
// api/get_restaurant_status.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$restaurant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$restaurant_id) {
    echo json_encode(['success' => false, 'error' => 'missing_restaurant']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT status FROM restaurants WHERE restaurant_id = ?');
    $stmt->execute([$restaurant_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'restaurant_not_found']);
        exit;
    }
    echo json_encode(['success' => true, 'status' => $row['status']]);
} catch (Exception $e) {
    error_log('get_restaurant_status error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
