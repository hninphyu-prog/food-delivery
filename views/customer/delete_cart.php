<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_POST['saved_cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'No cart specified']);
    exit;
}

$user_id = $_SESSION['user_id'];
$saved_cart_id = (int)$_POST['saved_cart_id'];

try {
    // Check if user owns this cart
    $stmt = $pdo->prepare("SELECT saved_cart_id FROM saved_carts WHERE saved_cart_id = ? AND user_id = ?");
    $stmt->execute([$saved_cart_id, $user_id]);
    $cart = $stmt->fetch();
    
    if (!$cart) {
        echo json_encode(['success' => false, 'message' => 'Cart not found or access denied']);
        exit;
    }
    
    // Delete the cart
    $stmt = $pdo->prepare("DELETE FROM saved_carts WHERE saved_cart_id = ?");
    $stmt->execute([$saved_cart_id]);
    
    // If this was the active cart, clear session cart
    if (isset($_SESSION['cart']['saved_cart_id']) && $_SESSION['cart']['saved_cart_id'] == $saved_cart_id) {
        $_SESSION['cart'] = ['restaurant_id' => null, 'items' => []];
        unset($_SESSION['cart']['saved_cart_id']);
    }
    
    echo json_encode(['success' => true, 'message' => 'Cart deleted successfully']);
    
} catch (Exception $e) {
    error_log("Error deleting cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}