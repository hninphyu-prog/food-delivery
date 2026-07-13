<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$order_id = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;
// --- CRITICAL ADDITION: Retrieve the cancellation reason ---
$cancellation_reason = $_POST['cancellation_reason'] ?? null;
// --------------------------------------------------------

// Get restaurant_id from session (for vendor) or check if user is admin/delivery
$restaurant_id = $_SESSION['restaurant_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$valid_statuses = ['accepted', 'preparing', 'ready', 'on_the_way', 'picked', 'delivered', 'canceled'];

if (!$order_id || !$status || !in_array($status, $valid_statuses)) {
    $response['message'] = 'Missing required data or invalid status.';
    echo json_encode($response);
    exit;
}

try {
    // First, verify the order exists and get its details
    $check_stmt = $pdo->prepare("
        SELECT o.*, r.user_id as restaurant_user_id 
        FROM orders o 
        LEFT JOIN restaurants r ON o.restaurant_id = r.restaurant_id 
        WHERE o.order_id = ?
    ");
    $check_stmt->execute([$order_id]);
    $order_data = $check_stmt->fetch(PDO::FETCH_ASSOC);

    // Authorization checks (simplified - check if user is restaurant owner for this order)
    $authorized = false;
    if ($restaurant_id && $order_data['restaurant_id'] == $restaurant_id) {
        $authorized = true;
    }
    // Add logic for admin/delivery roles if needed
    if ($user_role == 'admin' || $user_role == 'delivery') {
        $authorized = true;
    }

    if (!$authorized) {
        $response['message'] = 'You are not authorized to update this order.';
        echo json_encode($response);
        exit;
    }


    // --- CRITICAL FIX: Conditionally Update Query to save reason ---
    if ($status === 'canceled') {
        // If canceling, update both status AND cancellation reason
        $sql = "UPDATE orders SET order_status = ?, cancellation_reason = ? WHERE order_id = ?";
        $stmt = $pdo->prepare($sql);
        // Pass both $status and $cancellation_reason
        $stmt->execute([$status, $cancellation_reason, $order_id]);

    } else {
        // For all other status updates, only update the status column
        $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $order_id]);
    }
    // ----------------------------------------------------------------
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Order status updated successfully.';
        
        // If status is changed to 'delivered', update delivery status to 'delivered'
        if ($status === 'delivered') {
            $delivery_update_stmt = $pdo->prepare("UPDATE delivery SET status = 'delivered' WHERE order_id = ?");
            $delivery_update_stmt->execute([$order_id]);
        }
        
    } else {
//$response['success'] = true; // Treat as success if status is already correct
        //  $response['message'] = 'Order status not changed (already in requested status, or no row matched).';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);

?>