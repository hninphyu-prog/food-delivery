<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID.']);
    exit;
}

try {
    // Get basic order info
    $stmt = $pdo->prepare("SELECT order_status, cancellation_reason, restaurant_id, total_amount FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order_info) {
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    // Get restaurant info including logo
    $restaurant_stmt = $pdo->prepare("SELECT name as restaurant_name, logo FROM restaurants WHERE restaurant_id = ?");
    $restaurant_stmt->execute([$order_info['restaurant_id']]);
    $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Get order items with menu options
    $items_stmt = $pdo->prepare("
        SELECT oi.quantity as qty, oi.price, oi.menu_options, 
               mi.name, mi.item_id
        FROM order_items oi 
        LEFT JOIN menu_items mi ON oi.item_id = mi.item_id 
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$order_id]);
    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Parse menu_options JSON for each item
    $items_with_options = [];
    foreach ($items as $item) {
        $menu_options = [];
        
        if (!empty($item['menu_options'])) {
            $options_data = json_decode($item['menu_options'], true);
            if (is_array($options_data)) {
                foreach ($options_data as $option) {
                    if (is_array($option)) {
                        $menu_options[] = [
                            'option_name' => $option['option_name'] ?? '',
                            'value_name' => $option['value_name'] ?? '',
                            'price_modifier' => $option['price_modifier'] ?? 0
                        ];
                    }
                }
            }
        }
        
        $items_with_options[] = [
            'qty' => $item['qty'],
            'name' => $item['name'],
            'price' => $item['price'],
            'options' => $menu_options
        ];
    }

    // Determine if rider has started sharing tracking for this order
    $tstmt = $pdo->prepare("SELECT 1 FROM driver_locations WHERE order_id = ? LIMIT 1");
    $tstmt->execute([$order_id]);
    $tracking_started = (bool)$tstmt->fetchColumn();

    // Fetch assigned rider name if available
    $rider_stmt = $pdo->prepare("SELECT u.name AS rider_name FROM delivery d JOIN users u ON u.user_id = d.delivery_boy_id WHERE d.order_id = ? LIMIT 1");
    $rider_stmt->execute([$order_id]);
    $rider_row = $rider_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $rider_name = $rider_row['rider_name'] ?? null;

    echo json_encode([
        'success' => true, 
        'status' => $order_info['order_status'], 
        'cancellation_reason' => $order_info['cancellation_reason'], 
        'tracking_started' => $tracking_started, 
        'rider_name' => $rider_name,
        'summary' => [
            'restaurant_name' => $restaurant['restaurant_name'] ?? 'Restaurant',
            'logo' => $restaurant['logo'] ?? null, // Add logo here
            'total_amount' => $order_info['total_amount'],
            'items' => $items_with_options
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>