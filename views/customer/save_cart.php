<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];

try {
    // Calculate totals
    $item_count = 0;
    $total_amount = 0;
    
    foreach ($cart['items'] as $item) {
        $item_count += $item['qty'];
        $total_amount += $item['price'] * $item['qty'];
    }
    
    $delivery_fee = $_SESSION['delivery_fee'] ?? 0;
    $cart_data = json_encode($cart);
    
    if (isset($cart['saved_cart_id'])) {
        // Update existing cart
        $stmt = $pdo->prepare("
            UPDATE saved_carts SET 
            cart_data = ?, 
            item_count = ?, 
            total_amount = ?, 
            delivery_fee = ?, 
            updated_at = NOW()
            WHERE saved_cart_id = ? AND user_id = ?
        ");
        $stmt->execute([$cart_data, $item_count, $total_amount, $delivery_fee, $cart['saved_cart_id'], $user_id]);
        $saved_cart_id = $cart['saved_cart_id'];
    } else {
        // Create new cart
        $stmt = $pdo->prepare("
            INSERT INTO saved_carts (user_id, restaurant_id, cart_name, cart_data, item_count, total_amount, delivery_fee, created_at, updated_at, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)
        ");
        $cart_name = "Cart " . date('M d, H:i');
        $stmt->execute([$user_id, $cart['restaurant_id'] ?? 0, $cart_name, $cart_data, $item_count, $total_amount, $delivery_fee]);
        $saved_cart_id = $pdo->lastInsertId();
        $_SESSION['cart']['saved_cart_id'] = $saved_cart_id;
    }
    
    echo json_encode([
        'success' => true,
        'saved_cart_id' => $saved_cart_id,
        'item_count' => $item_count,
        'total_amount' => $total_amount,
        'delivery_fee' => $delivery_fee,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Error saving cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}