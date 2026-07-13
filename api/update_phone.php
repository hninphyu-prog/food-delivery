<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';

// Validate phone number (basic validation)
if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Phone number is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE user_id = ?");
    $result = $stmt->execute([$phone, $_SESSION['user_id']]);
    
    if ($result) {
        // Update the session data
        $_SESSION['user_phone'] = $phone;
        echo json_encode(['success' => true, 'message' => 'Phone number updated successfully']);
    } else {
        throw new Exception('Failed to update phone number');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating phone number: ' . $e->getMessage()]);
}
