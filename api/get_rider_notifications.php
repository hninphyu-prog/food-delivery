<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];

try {
    // Get unread notifications for the rider
    $sql = "
    SELECT 
        n.notification_id,
        n.title,
        n.message,
        n.created_at,
        n.is_read
    FROM notifications n
    WHERE n.user_id = ? 
    AND n.recipient_type = 'delivery'
    ORDER BY n.created_at DESC
    LIMIT 20
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$rider_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mark notifications as read when fetched
    if (!empty($notifications)) {
        $unread_ids = array_column(array_filter($notifications, fn($n) => !$n['is_read']), 'notification_id');
        if (!empty($unread_ids)) {
            $placeholders = str_repeat('?,', count($unread_ids) - 1) . '?';
            $update_sql = "UPDATE notifications SET is_read = 1 WHERE notification_id IN ($placeholders)";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute($unread_ids);
        }
    }
    
    // Get unread count
    $count_sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND recipient_type = 'delivery' AND is_read = 0";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute([$rider_id]);
    $unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}