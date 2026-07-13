<?php
session_start();

require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get restaurant_id from POST
$restaurant_id = isset($_POST['restaurant_id']) ? (int)$_POST['restaurant_id'] : 0;

// GET CART ITEMS - FIXED VERSION
$cart_items = [];
$cart = null;

if ($restaurant_id && isset($_SESSION['cart'][$restaurant_id])) {
    // Use restaurant-specific cart
    $cart = $_SESSION['cart'][$restaurant_id];
    $cart_items = $cart['items'] ?? [];
} elseif (isset($_SESSION['cart']['default'])) {
    // Fallback to default cart
    $cart = $_SESSION['cart']['default'];
    $cart_items = $cart['items'] ?? [];
} else {
    // Try to find any cart
    foreach ($_SESSION['cart'] ?? [] as $cartKey => $cartData) {
        if (is_array($cartData) && isset($cartData['items']) && !empty($cartData['items'])) {
            $cart = $cartData;
            $cart_items = $cartData['items'];
            break;
        }
    }
}

// Debug: Check what we found
error_log("Restaurant ID: " . $restaurant_id);
error_log("Cart items count: " . count($cart_items));
error_log("Full cart structure: " . print_r($_SESSION['cart'] ?? [], true));

if (empty($cart_items)) {
    die("Your cart is empty.");
}

$user_id = (int)$_SESSION['user_id'];
$delivery_address = trim($_POST['delivery_address'] ?? '');
$lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
$lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;
$payment_method = $_POST['payment_method'] ?? 'cod';

if (!$restaurant_id || !$delivery_address || $lat === null || $lng === null) {
    die("Invalid order data.");
}

// Fetch restaurant name and location
$stmt = $pdo->prepare("SELECT name, lat, lng FROM restaurants WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$rest = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rest) {
    die("Invalid restaurant selected.");
}
$restaurant_name = $rest['name']; // Store name for summary

// Calculate subtotal, total, and collect item details - USE $cart_items NOT $cart['items']
$subtotal = 0;
$total_items = 0;
$item_details = []; // Array to store item details for the summary

foreach ($cart_items as $it) {
    $qty = isset($it['qty']) ? (int)$it['qty'] : (isset($it['quantity']) ? (int)$it['quantity'] : 0);
    $price = isset($it['price']) ? (float)$it['price'] : 0;
    
    if ($qty > 0 && $price > 0) {
        $subtotal += $qty * $price;
        $total_items += $qty;

        // Collect the necessary details for the tracking bar
        $item_details[] = [
            'name' => $it['name'] ?? 'Unknown Item',
            'qty' => $qty
        ];
    }
}

// Get delivery fee from form
$delivery_fee = isset($_POST['delivery_fee']) ? (int)$_POST['delivery_fee'] : 1500;
$total_amount = $subtotal + $delivery_fee;

$payment_status = 'unpaid';
$order_status = 'pending';

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, restaurant_id, delivery_address, total_amount, delivery_fee, subtotal, payment_status, order_status, lat, lng, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $restaurant_id,
        $delivery_address,
        $total_amount,
        $delivery_fee,
        $subtotal,
        $payment_status,
        $order_status,
        $lat,
        $lng,
        0
    ]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $itemInsertStmt = $pdo->prepare("INSERT INTO order_items (order_id, item_id, quantity, price, menu_options) VALUES (?, ?, ?, ?, ?)");

    foreach ($cart_items as $it) {
        $item_id = isset($it['id']) ? (int)$it['id'] : 0;
        $qty = isset($it['qty']) ? (int)$it['qty'] : (isset($it['quantity']) ? (int)$it['quantity'] : 0);
        $price = isset($it['price']) ? (float)$it['price'] : 0;
        
        if ($item_id <= 0 || $qty <= 0) {
            continue; // Skip invalid items
        }
        
        // DEBUG: Check what options are available
        error_log("Cart item options before storing: " . print_r($it['options'] ?? 'NO OPTIONS', true));
        
        // Store menu options as JSON - ensure proper structure
        $menu_options = null;
        if (isset($it['options']) && is_array($it['options']) && !empty($it['options'])) {
            $cleaned_options = [];
            foreach ($it['options'] as $option) {
                // Make sure we're using the correct price field
                $price_modifier = $option['price_modifier'] ?? 0;
                $extra_price = $option['extra_price'] ?? $price_modifier;
                
                $cleaned_option = [
                    'option_name' => $option['option_name'] ?? $option['name'] ?? 'Option',
                    'value_name' => $option['value_name'] ?? $option['choice'] ?? '',
                    'extra_price' => floatval($extra_price),
                    'price_modifier' => floatval($price_modifier) 
                ];
                $cleaned_options[] = $cleaned_option;
            }
            $menu_options = json_encode($cleaned_options);
            error_log("Storing options JSON: " . $menu_options);
        }
        
        $itemInsertStmt->execute([$order_id, $item_id, $qty, $price, $menu_options]);
    }

    // Commit the transaction
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Order placement failed: " . $e->getMessage());
}

// MARK CART AS ORDERED
if (isset($_SESSION['cart']['saved_cart_id'])) {
    $saved_cart_id = $_SESSION['cart']['saved_cart_id'];
    
    // Update the saved cart to mark it as ordered
    $stmt = $pdo->prepare("
        UPDATE saved_carts 
        SET order_id = ?, 
            status = 'ordered',
            updated_at = NOW()
        WHERE saved_cart_id = ?
    ");
    $stmt->execute([$order_id, $saved_cart_id]);
    
    // Also archive any other active carts for this user
    $stmt = $pdo->prepare("
        UPDATE saved_carts 
        SET status = 'archived'
        WHERE user_id = ? 
          AND status = 'active'
          AND saved_cart_id != ?
          AND (order_id IS NULL OR order_id = 0)
    ");
    $stmt->execute([$_SESSION['user_id'], $saved_cart_id]);
}

// CLEAR SESSION CART - Clear properly based on restaurant_id
if ($restaurant_id && isset($_SESSION['cart'][$restaurant_id])) {
    unset($_SESSION['cart'][$restaurant_id]);
} elseif (isset($_SESSION['cart']['default'])) {
    unset($_SESSION['cart']['default']);
} else {
    // Clear any cart
    unset($_SESSION['cart']);
}

// Clear saved cart ID
unset($_SESSION['cart']['saved_cart_id']);

// Also clear delivery fee
unset($_SESSION['delivery_fee']);
unset($_SESSION['cart_delivery_fee']);

// 1. Create the Order Summary for the tracking bar, including item details
$_SESSION['temp_order_summary'] = [
    'restaurant_name' => $restaurant_name,
    'item_count' => $total_items,
    'total_amount' => $total_amount,
    'currency' => 'MMK',
    'items' => $item_details // NEW: Detailed item list
];

// 3. Redirect to success page
header("Location: order_success.php?order_id=" . $order_id);
exit;