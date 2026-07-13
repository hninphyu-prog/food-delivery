
<?php
header('Content-Type: application/json');
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM request_notifications WHERE user_id = ? AND is_read = 0");
    $countStmt->execute([$userId]);
    $unreadCount = $countStmt->fetchColumn();

    $notifStmt = $pdo->prepare("
        SELECT notification_id, title, message, created_at 
        FROM request_notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $notifStmt->execute([$userId]);
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => $unreadCount,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
