<?php
// get_cart_count.php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_item_count = 0;

try {
    $stmt = $pdo->prepare("
        SELECT sc.cart_data 
        FROM saved_carts sc 
        WHERE sc.user_id = ? 
          AND (sc.status = 'active' OR sc.status IS NULL)
          AND (sc.order_id IS NULL OR sc.order_id = 0)
    ");
    $stmt->execute([$user_id]);
    $active_carts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($active_carts as $cart) {
        $cart_data = json_decode($cart['cart_data'], true);
        if (isset($cart_data['items'])) {
            foreach ($cart_data['items'] as $item) {
                $cart_item_count += $item['qty'] ?? $item['quantity'] ?? 0;
            }
        }
    }
} catch (Exception $e) {
    $cart_item_count = 0;
}

echo json_encode(['count' => $cart_item_count]);
?>