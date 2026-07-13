
<?php
header('Content-Type: application/json');
session_start();
include '../config/db.php';

// Check if we're fetching all notifications or a specific one
if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    // Specific notification details
    $notificationId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    try {
        // Mark notification as read for this user
        $updateStmt = $pdo->prepare("UPDATE request_notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $updateStmt->execute([$notificationId, $userId]);
        
        // Get notification details
        $stmt = $pdo->prepare("SELECT * FROM request_notifications WHERE notification_id = ? AND user_id = ?");
        $stmt->execute([$notificationId, $userId]);
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($notification) {
            // Determine redirect URL based on notification content
            $redirect_url = '#';
            
            // Check for different notification types
          // Check for different notification types
if (strpos($notification['title'], 'Rider Rejected Payout') !== false) {
    $redirect_url = '/foodandme/views/admin/admin_rejected_payouts.php';
} elseif (strpos($notification['title'], 'Settlement') !== false) {
    $redirect_url = '/foodandme/views/admin/admin_rejected_settlements.php';
} elseif (strpos($notification['title'], 'Rider Deposit') !== false) {
    $redirect_url = '/foodandme/views/admin/rider_deposit_approval.php';
} elseif (strpos($notification['title'], 'Partner Request') !== false) {
    $redirect_url = '/foodandme/views/admin/vendor_rider_request.php';
}
            echo json_encode([
                'success' => true,
                'notification' => [
                    'id' => $notification['notification_id'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'created_at' => $notification['created_at'],
                    'redirect_url' => $redirect_url
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Notification not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Fetch all unread notifications for current user
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    $userId = $_SESSION['user_id'];
    
    try {
        // Count unread notifications
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM request_notifications WHERE user_id = ? AND is_read = 0");
        $countStmt->execute([$userId]);
        $unreadCount = $countStmt->fetchColumn();
        
        // Get recent notifications
        $notifStmt = $pdo->prepare("
            SELECT notification_id, title, message, created_at 
            FROM request_notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $notifStmt->execute([$userId]);
        $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => $unreadCount,
            'notifications' => $notifications
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
