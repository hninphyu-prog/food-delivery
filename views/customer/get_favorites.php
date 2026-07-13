<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's favorite restaurants with details
    $stmt = $pdo->prepare("
        SELECT r.*, 
               (6371 * acos(cos(radians(?)) * cos(radians(r.lat)) * cos(radians(r.lng) - radians(?)) + sin(radians(?)) * sin(radians(r.lat)))) AS distance
        FROM restaurants r
        INNER JOIN user_favorites uf ON r.restaurant_id = uf.restaurant_id
        WHERE uf.user_id = ? 
          AND r.status IN ('active','closed')
        ORDER BY r.name ASC
    ");
    
    $userLat = $_SESSION['user_lat'] ?? 0;
    $userLng = $_SESSION['user_lng'] ?? 0;
    
    $stmt->execute([$userLat, $userLng, $userLat, $user_id]);
    $favorite_restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add review data for each restaurant
    foreach ($favorite_restaurants as &$restaurant) {
        $reviewStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating
            FROM reviews 
            WHERE restaurant_id = ? AND status = 'visible'
        ");
        $reviewStmt->execute([$restaurant['restaurant_id']]);
        $review_stats = $reviewStmt->fetch(PDO::FETCH_ASSOC);
        
        $restaurant['total_reviews'] = $review_stats['total_reviews'] ?? 0;
        $restaurant['avg_rating'] = $review_stats['avg_rating'] ? round($review_stats['avg_rating'], 1) : 0;
    }
    
    echo json_encode([
        'success' => true,
        'restaurants' => $favorite_restaurants
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading favorites'
    ]);
}
?>