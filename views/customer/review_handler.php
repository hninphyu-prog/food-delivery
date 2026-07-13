<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_POST['action'] === 'submit_review') {
    try {
        $order_id = (int)$_POST['order_id'];
        $restaurant_id = (int)$_POST['restaurant_id'];
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        $user_id = $_SESSION['user_id'];
        
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Invalid rating');
        }
        
        // Verify that the order belongs to the user and is delivered
        $stmt = $pdo->prepare("
            SELECT order_id FROM orders 
            WHERE order_id = ? AND user_id = ? AND order_status = 'delivered' AND is_reviewed = FALSE
        ");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception('Order not found or already reviewed');
        }
        
        // Insert the review
        $stmt = $pdo->prepare("
            INSERT INTO reviews (restaurant_id, user_id, rating, comment, status) 
            VALUES (?, ?, ?, ?, 'visible')
        ");
        $stmt->execute([$restaurant_id, $user_id, $rating, $comment]);
        
        // Mark order as reviewed
        $stmt = $pdo->prepare("UPDATE orders SET is_reviewed = TRUE WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>