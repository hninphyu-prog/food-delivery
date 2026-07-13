<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['saved_cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'No cart ID provided']);
    exit;
}

$saved_cart_id = (int)$_POST['saved_cart_id'];
$user_id = $_SESSION['user_id'];

// Fetch the saved cart
$stmt = $pdo->prepare("
    SELECT sc.*, r.restaurant_id, r.name as restaurant_name, r.status as restaurant_status
    FROM saved_carts sc 
    JOIN restaurants r ON sc.restaurant_id = r.restaurant_id 
    WHERE sc.saved_cart_id = ? AND sc.user_id = ?
      AND (sc.status = 'active' OR sc.status IS NULL)
      AND (sc.order_id IS NULL OR sc.order_id = 0)
");
$stmt->execute([$saved_cart_id, $user_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    echo json_encode(['success' => false, 'message' => 'Cart not found']);
    exit;
}

// Decode cart data
$cart_data = json_decode($cart['cart_data'], true);

// Check if restaurant is closed
$restaurant_closed = ($cart['restaurant_status'] !== 'active');

// IMPORTANT: Set the restaurant-specific cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Store cart under the restaurant's ID
$restaurant_id = $cart['restaurant_id'];

// Clear any existing cart for this restaurant
if (isset($_SESSION['cart'][$restaurant_id])) {
    unset($_SESSION['cart'][$restaurant_id]);
}

// Set the new cart for this restaurant
$_SESSION['cart'][$restaurant_id] = $cart_data;

// Also set it as the current active cart
$_SESSION['current_restaurant_id'] = $restaurant_id;
$_SESSION['cart']['saved_cart_id'] = $saved_cart_id;

// Update session delivery fee if present
if (isset($cart_data['delivery_fee'])) {
    $_SESSION['delivery_fee'] = $cart_data['delivery_fee'];
} else {
    // Use the delivery fee from saved_carts table
    $_SESSION['delivery_fee'] = $cart['delivery_fee'];
}

echo json_encode([
    'success' => true,
    'restaurant_id' => $restaurant_id,
    'restaurant_closed' => $restaurant_closed,
    'message' => 'Cart loaded successfully'
]);
?>