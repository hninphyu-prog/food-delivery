<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/db.php';

try {
    if (!isset($_GET['value_id']) || !is_numeric($_GET['value_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid value_id']);
        exit;
    }

    $value_id = (int)$_GET['value_id'];

    // Fetch option value by ID
    $stmt = $pdo->prepare("SELECT value_id, option_id, value_name, price_modifier FROM option_values WHERE value_id = ?");
    $stmt->execute([$value_id]);
    $value = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$value) {
        http_response_code(404);
        echo json_encode(['error' => 'Option value not found']);
        exit;
    }

    // Normalize data types
    $value['price_modifier'] = (float)$value['price_modifier'];

    echo json_encode($value);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
