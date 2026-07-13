<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_fee'])) {
    $fee = (int)$_POST['delivery_fee'];
    $_SESSION['delivery_fee'] = $fee;
    $_SESSION['current_delivery_fee'] = $fee;
    $_SESSION['cart_delivery_fee'] = $fee;
    
    echo json_encode(['success' => true, 'fee' => $fee]);
} else {
    echo json_encode(['success' => false]);
}
?>