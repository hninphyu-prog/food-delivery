<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/db.php';

try {
    if (!isset($_SESSION['restaurant_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Restaurant not selected']);
        exit;
    }

    if (!isset($_GET['item_id']) || !is_numeric($_GET['item_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid item_id']);
        exit;
    }

    $restaurant_id = (int)$_SESSION['restaurant_id'];
    $item_id = (int)$_GET['item_id'];

    $stmt = $pdo->prepare("SELECT item_id, restaurant_id, name, description, price, image, category, is_available
                            FROM menu_items
                            WHERE item_id = ? AND restaurant_id = ?");
    $stmt->execute([$item_id, $restaurant_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Menu item not found']);
        exit;
    }

    // Normalize data types
    $item['price'] = (float)$item['price'];
    $item['is_available'] = (int)$item['is_available'];

    echo json_encode($item);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>