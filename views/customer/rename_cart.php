<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_POST['saved_cart_id']) || !isset($_POST['cart_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$saved_cart_id = (int)$_POST['saved_cart_id'];
$cart_name = trim($_POST['cart_name']);

if (empty($cart_name) || strlen($cart_name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Cart name must be 1-100 characters']);
    exit;
}

try {
    // Check if user owns this cart
    $stmt = $pdo->prepare("SELECT saved_cart_id FROM saved_carts WHERE saved_cart_id = ? AND user_id = ?");
    $stmt->execute([$saved_cart_id, $user_id]);
    $cart = $stmt->fetch();
    
    if (!$cart) {
        echo json_encode(['success' => false, 'message' => 'Cart not found or access denied']);
        exit;
    }
    
    // Update cart name
    $stmt = $pdo->prepare("UPDATE saved_carts SET cart_name = ?, updated_at = NOW() WHERE saved_cart_id = ?");
    $stmt->execute([$cart_name, $saved_cart_id]);
    
    echo json_encode(['success' => true, 'message' => 'Cart renamed successfully']);
    
} catch (Exception $e) {
    error_log("Error renaming cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}