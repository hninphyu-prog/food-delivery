<?php
// api/get_active_deliveries.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

$response = ['success' => false, 'data' => []];

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    $response['message'] = 'Unauthorized access.';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

$rider_id = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT
            o.order_id,
            o.order_status as status,
            r.name as restaurant_name
        FROM orders o
        JOIN users r ON o.restaurant_id = r.user_id
        WHERE o.rider_id = ? AND o.order_status IN ('accepted', 'on_the_way', 'ready')
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$rider_id]);
    $active_deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $active_deliveries;

} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
