<?php
// api/get_driver_location.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'missing']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id, 
            o.order_status, 
            o.delivery_address, 
            o.lat AS customer_lat,
            o.lng AS customer_lng,
            dt.delivery_boy_id, 
            COALESCE(dl.lat, dt.lat) as lat, 
            COALESCE(dl.lng, dt.lng) as lng, 
            u.name AS rider_name
        FROM orders o
        LEFT JOIN delivery_tracking dt ON dt.order_id = o.order_id
        LEFT JOIN driver_locations dl ON dl.order_id = o.order_id
        LEFT JOIN users u ON u.user_id = dt.delivery_boy_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $data = $stmt->fetch();
    
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'not_found']);
        exit;
    }

    // Check if we have valid coordinates
    if (!$data['lat'] || !$data['lng']) {
        echo json_encode(['success' => false, 'error' => 'no_location_data']);
        exit;
    }

    echo json_encode([
        'success' => true, 
        'data' => [
            'lat' => (float)$data['lat'],
            'lng' => (float)$data['lng'],
            'order_status' => $data['order_status'],
            'rider_name' => $data['rider_name'],
            'delivery_boy_id' => $data['delivery_boy_id'],
            'customer_lat' => isset($data['customer_lat']) ? (float)$data['customer_lat'] : null,
            'customer_lng' => isset($data['customer_lng']) ? (float)$data['customer_lng'] : null
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
?>