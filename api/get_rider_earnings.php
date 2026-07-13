<?php
// Enable error reporting for debugging
error_reporting(0); // Disable error output to prevent corrupting JSON
session_start();
ob_start(); // Start output buffering

require_once '../config/db.php';

// Set JSON header first
header('Content-Type: application/json');

// Check if user is logged in and is a delivery rider
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Unknown error occurred'];

try {
    // ... [rest of your existing try block code] ...
    
    // Build response array
    $response = [
        'success' => true,
        'today_earnings' => floatval($today_earnings),
        'week_earnings' => floatval($week_earnings),
        'week_payout' => floatval($week_payout),
        'pending_payout' => floatval($pending_payout),
        'today_orders' => $today_orders,
        'payout_history' => $payout_history,
        'has_new_payout' => !empty($recent_payout),
        'recent_payout' => $recent_payout
    ];

} catch (Exception $e) {
    error_log("Error in get_rider_earnings: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

// Clean any output buffers and output the JSON
while (ob_get_level()) {
    ob_end_clean();
}
echo json_encode($response);
exit;