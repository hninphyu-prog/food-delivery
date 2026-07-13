<?php
// Ensure session is started and includes are correct
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure there is no whitespace before this <?php tag.
header('Content-Type: application/json');


require_once __DIR__ . '/../../config/db.php';


// Security check: only allow restaurant or delivery users to change status
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['restaurant', 'delivery'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$order_id = $_POST['order_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;

if (!$order_id || !$new_status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order ID or new status.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $order_id]);

    echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>