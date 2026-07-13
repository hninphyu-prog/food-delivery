<?php
// api/update_location.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'error' => 'not_allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['lat']) || !isset($data['lng'])) {
    echo json_encode(['success' => false, 'error' => 'missing_coordinates']);
    exit;
}

$order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$lat = floatval($data['lat']);
$lng = floatval($data['lng']);

try {
    // Store in session for immediate access
    $_SESSION['last_lat'] = $lat;
    $_SESSION['last_lng'] = $lng;

    // If we have an order_id, update both tracking tables
    if ($order_id > 0) {
        // Update delivery_tracking table
        $checkStmt = $pdo->prepare("SELECT order_id FROM delivery_tracking WHERE order_id = ?");
        $checkStmt->execute([$order_id]);
        $trackingExists = $checkStmt->fetch();
        
        if ($trackingExists) {
            // Update existing tracking
            $stmt = $pdo->prepare("
                UPDATE delivery_tracking 
                SET lat = ?, lng = ?, last_update = NOW() 
                WHERE order_id = ?
            ");
            $stmt->execute([$lat, $lng, $order_id]);
        } else {
            // Insert new tracking record
            $stmt = $pdo->prepare("
                INSERT INTO delivery_tracking (order_id, delivery_boy_id, lat, lng, status, last_update)
                VALUES (?, ?, ?, ?, 'on_the_way', NOW())
            ");
            $stmt->execute([$order_id, $_SESSION['user_id'], $lat, $lng]);
        }

        // Update driver_locations table (for customer tracking)
        $checkDriverStmt = $pdo->prepare("SELECT order_id FROM driver_locations WHERE order_id = ?");
        $checkDriverStmt->execute([$order_id]);
        $driverExists = $checkDriverStmt->fetch();
        
        if ($driverExists) {
            // Update existing driver location
            $driverStmt = $pdo->prepare("
                UPDATE driver_locations 
                SET lat = ?, lng = ?, updated_at = NOW() 
                WHERE order_id = ?
            ");
            $driverStmt->execute([$lat, $lng, $order_id]);
        } else {
            // Insert new driver location
            $driverStmt = $pdo->prepare("
                INSERT INTO driver_locations (order_id, lat, lng, updated_at)
                VALUES (?, ?, ?, NOW())
            ");
            $driverStmt->execute([$order_id, $lat, $lng]);
        }
    }
    
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    
} catch (PDOException $e) {
    error_log("Location update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'database_error']);
}
?>