<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_POST['action'] === 'toggle_favorite') {
    $restaurant_id = (int)$_POST['restaurant_id'];
    
    // Check if already favorited
    $stmt = $pdo->prepare("SELECT * FROM user_favorites WHERE user_id = ? AND restaurant_id = ?");
    $stmt->execute([$user_id, $restaurant_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Remove from favorites
        $stmt = $pdo->prepare("DELETE FROM user_favorites WHERE user_id = ? AND restaurant_id = ?");
        $stmt->execute([$user_id, $restaurant_id]);
        $is_favorite = false;
    } else {
        // Add to favorites
        $stmt = $pdo->prepare("INSERT INTO user_favorites (user_id, restaurant_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $restaurant_id]);
        $is_favorite = true;
    }
    
    // Get updated favorite count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as favorite_count FROM user_favorites WHERE user_id = ?");
    $countStmt->execute([$user_id]);
    $favorite_count = $countStmt->fetchColumn();
    
    echo json_encode([
        'success' => true, 
        'is_favorite' => $is_favorite,
        'favorite_count' => $favorite_count
    ]);
} 
elseif ($_POST['action'] === 'remove_favorite') {
    $restaurant_id = (int)$_POST['restaurant_id'];
    
    // Remove from favorites
    $stmt = $pdo->prepare("DELETE FROM user_favorites WHERE user_id = ? AND restaurant_id = ?");
    $stmt->execute([$user_id, $restaurant_id]);
    
    // Get updated favorite count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as favorite_count FROM user_favorites WHERE user_id = ?");
    $countStmt->execute([$user_id]);
    $favorite_count = $countStmt->fetchColumn();
    
    echo json_encode([
        'success' => true, 
        'favorite_count' => $favorite_count,
        'message' => 'Restaurant removed from favorites'
    ]);
}
?>