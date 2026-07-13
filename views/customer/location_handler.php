<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if ($_POST['action'] === 'set_location') {
    $_SESSION['user_lat'] = floatval($_POST['lat']);
    $_SESSION['user_lng'] = floatval($_POST['lng']);
    
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>