<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['restored' => false]);
    exit;
}

$restaurant_id = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;

// Check if cart already exists in session
if (isset($_SESSION['cart'][$restaurant_id]) && !empty($_SESSION['cart'][$restaurant_id])) {
    echo json_encode(['restored' => false, 'reason' => 'Already exists in session']);
    exit;
}

// Check if there's a saved cart for this restaurant
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT * FROM saved_carts 
    WHERE user_id = ? AND restaurant_id = ? 
      AND (status = 'active' OR status IS NULL)
      AND (order_id IS NULL OR order_id = 0)
    ORDER BY updated_at DESC 
    LIMIT 1
");
$stmt->execute([$user_id, $restaurant_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cart) {
    // Restore cart to session
    $cart_data = json_decode($cart['cart_data'], true);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][$restaurant_id] = $cart_data;
    $_SESSION['current_restaurant_id'] = $restaurant_id;
    $_SESSION['cart']['saved_cart_id'] = $cart['saved_cart_id'];
    
    if (isset($cart_data['delivery_fee'])) {
        $_SESSION['delivery_fee'] = $cart_data['delivery_fee'];
    } else {
        $_SESSION['delivery_fee'] = $cart['delivery_fee'] ?? 0;
    }
    
    echo json_encode(['restored' => true, 'item_count' => $cart['item_count'] ?? 0]);
} else {
    echo json_encode(['restored' => false]);
}
?>