<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/db.php';

try {
    if (!isset($_GET['option_id']) || !is_numeric($_GET['option_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid option_id']);
        exit;
    }

    $option_id = (int)$_GET['option_id'];

    // Fetch option by ID
    $stmt = $pdo->prepare("SELECT option_id, option_name, option_type, is_required FROM menu_options WHERE option_id = ?");
    $stmt->execute([$option_id]);
    $option = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$option) {
        http_response_code(404);
        echo json_encode(['error' => 'Option not found']);
        exit;
    }

    // Normalize data types
    $option['is_required'] = (int)$option['is_required'];

    echo json_encode($option);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
