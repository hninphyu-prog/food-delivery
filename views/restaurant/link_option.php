<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['item_id']) || !isset($_POST['option_id']) || !isset($_SESSION['restaurant_id'])) {
        $_SESSION['error'] = "Invalid request";
        header("Location: index.php?page=menu");
        exit();
    }
    
    $item_id = $_POST['item_id'];
    $option_id = $_POST['option_id'];
    $restaurant_id = $_SESSION['restaurant_id'];
    
    // Verify item belongs to restaurant
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE item_id = ? AND restaurant_id = ?");
    $stmt->execute([$item_id, $restaurant_id]);
    $menu_item = $stmt->fetch();
    
    if (!$menu_item) {
        $_SESSION['error'] = "Menu item not found";
        header("Location: index.php?page=menu");
        exit();
    }
    
    // Check if option is already linked
    $stmt = $pdo->prepare("SELECT * FROM menu_item_options WHERE item_id = ? AND option_id = ?");
    $stmt->execute([$item_id, $option_id]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Option is already linked to this item";
    } else {
        // Link option to menu item
        $stmt = $pdo->prepare("INSERT INTO menu_item_options (item_id, option_id) VALUES (?, ?)");
        if ($stmt->execute([$item_id, $option_id])) {
            $_SESSION['success'] = "Option linked successfully!";
        } else {
            $_SESSION['error'] = "Failed to link option";
        }
    }
    
    header("Location: index.php?page=menu_options&item_id=$item_id");
    exit();
}
?>