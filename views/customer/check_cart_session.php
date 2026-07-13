<?php
session_start();
header('Content-Type: application/json');

$restaurant_id = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['has_cart' => false, 'item_count' => 0]);
    exit;
}

// Check if cart exists for this restaurant
if (isset($_SESSION['cart'][$restaurant_id])) {
    $cart_data = $_SESSION['cart'][$restaurant_id];
    $item_count = 0;
    
    if (isset($cart_data['items'])) {
        foreach ($cart_data['items'] as $item) {
            $item_count += $item['qty'] ?? $item['quantity'] ?? 0;
        }
    }
    
    echo json_encode(['has_cart' => true, 'item_count' => $item_count]);
} else {
    echo json_encode(['has_cart' => false, 'item_count' => 0]);
}
?>