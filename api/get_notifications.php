<?php
// api/get_notifications.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'restaurant') {
    echo json_encode(['success' => false, 'error' => 'not_allowed']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM request_notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($notifications)) {
        $pdo->prepare("UPDATE request_notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$user_id]);
    }

    echo json_encode(['success' => true, 'notifications' => $notifications]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error', 'detail' => $e->getMessage()]);
}
?>