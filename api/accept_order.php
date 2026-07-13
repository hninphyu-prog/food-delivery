<?php
// api/accept_order.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'error' => 'not_allowed', 'message' => 'You must be logged in as a delivery rider to accept orders.']);
    exit;
}

$delivery_boy_id = (int)$_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'missing_order', 'message' => 'Missing order ID.']);
    exit;
}

try {
    // 1) Ensure a unique constraint on delivery.order_id to prevent double assignment races
    try {
        $pdo->exec("ALTER TABLE delivery ADD UNIQUE KEY uniq_delivery_order (order_id)");
    } catch (PDOException $e) {
        // Ignore 'duplicate key name' error (exists already)
        if ($e->getCode() !== '42000' && $e->getCode() !== '42S11' && strpos($e->getMessage(), 'Duplicate key name') === false) {
            error_log('Unique index ensure error (delivery.order_id): ' . $e->getMessage());
        }
    }

    $pdo->beginTransaction();

    // 2) Lock the order row to serialize acceptance attempts
    $lockStmt = $pdo->prepare("SELECT order_id, restaurant_id, order_status FROM orders WHERE order_id = ? FOR UPDATE");
    $lockStmt->execute([$order_id]);
    $order = $lockStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'order_not_found', 'message' => 'Order not found.']);
        exit;
    }

    // Only allow acceptance when order is in 'accepted' pool
    // if (strtolower((string)$order['order_status']) !== 'accepted' && $order['order_status'] !== null && $order['order_status'] !== '') {
    //     $pdo->rollBack();
    //     echo json_encode(['success' => false, 'error' => 'not_available', 'message' => 'This order is no longer available for acceptance.']);
    //     exit;
    // }

    $allowed_acceptance_statuses = ['accepted', 'preparing', 'ready'];

    $current_status = strtolower((string)$order['order_status']);

    // The check here is: if the current status is NOT in the allowed list, THEN rollback.
    if (!in_array($current_status, $allowed_acceptance_statuses)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'not_available', 'message' => 'This order is no longer available for acceptance.']);
        exit;
    }
    // This approach is cleaner if your list of allowed statuses is small and known.

    // 3) Try to insert assignment. Unique index prevents double assignment.
    try {
        $ins = $pdo->prepare("INSERT INTO delivery (order_id, delivery_boy_id, status, created_at) VALUES (?, ?, 'assigned', NOW())");
        $ins->execute([$order_id, $delivery_boy_id]);
    } catch (PDOException $e) {
        // Duplicate assignment (unique constraint) -> someone else got it first
        if ($e->getCode() === '23000') {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'already_assigned', 'message' => 'This order has already been assigned to another rider.']);
            exit;
        }
        throw $e;
    }

    // 4) Upsert tracking and set a consistent initial status
    $chkTrack = $pdo->prepare("SELECT order_id FROM delivery_tracking WHERE order_id = ?");
    $chkTrack->execute([$order_id]);
    if (!$chkTrack->fetch()) {
        $pdo->prepare("INSERT INTO delivery_tracking (order_id, delivery_boy_id, status) VALUES (?, ?, 'assigned')")
            ->execute([$order_id, $delivery_boy_id]);
    } else {
        $pdo->prepare("UPDATE delivery_tracking SET delivery_boy_id = ?, status = 'assigned' WHERE order_id = ?")
            ->execute([$delivery_boy_id, $order_id]);
    }

    // 5) REMOVED: Don't change order status to 'preparing' - let restaurant control this
    // Order status remains 'accepted' until restaurant changes it to 'preparing'

    // 6) Notify restaurant/vendor
    $restaurantId = $order['restaurant_id'];
    $deliveryBoyNameStmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
    $deliveryBoyNameStmt->execute([$delivery_boy_id]);
    $deliveryBoyName = $deliveryBoyNameStmt->fetchColumn();

    if ($restaurantId && $deliveryBoyName) {
        $vendorStmt = $pdo->prepare("SELECT user_id FROM restaurants WHERE restaurant_id = ?");
        $vendorStmt->execute([$restaurantId]);
        $vendorUserId = $vendorStmt->fetchColumn();
        if ($vendorUserId) {
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, restaurant_id, order_id, recipient_type, title, message) VALUES (?, ?, ?, 'restaurant', ?, ?)");
            $notifStmt->execute([$vendorUserId, $restaurantId, $order_id, "Order #$order_id Accepted", "Rider: $deliveryBoyName has accepted your order. Please prepare the food."]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (PDOException $e) {
    $pdo->rollBack();

    // More specific error messages based on error code
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();

    if ($errorCode == 23000) {
        if (strpos($errorMessage, 'foreign key constraint') !== false) {
            if (strpos($errorMessage, 'order_id') !== false) {
                echo json_encode(['success' => false, 'error' => 'order_not_found', 'message' => 'Order does not exist.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'invalid_data', 'message' => 'Invalid order or rider data.']);
            }
        } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
            echo json_encode(['success' => false, 'error' => 'already_assigned', 'message' => 'This order has already been assigned.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'database_error', 'message' => 'Database constraint violation.']);
        }
    } else {
        error_log("Accept Order Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'server_error', 'message' => 'An unexpected server error occurred.']);
    }
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Accept Order General Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'server_error', 'message' => 'An unexpected server error occurred.']);
}
