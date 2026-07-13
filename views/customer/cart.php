<?php
session_start();
error_log("=== CART.PHP SESSION DEBUG ===");
error_log("User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("Delivery Fee: " . ($_SESSION['delivery_fee'] ?? 'NOT SET'));
error_log("All Session: " . print_r($_SESSION, true));

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: text/html; charset=utf-8');

error_log("Delivery fee in session: " . ($_SESSION['delivery_fee'] ?? 'NOT SET'));

// Function to save cart to database - UPDATED
function saveCartToDatabase(PDO $pdo, $user_id, $cart)
{
    if (empty($cart['items'])) {
        return null;
    }

    // Don't save if cart is marked as ordered
    if (isset($cart['status']) && $cart['status'] === 'ordered') {
        return null;
    }

    // Calculate totals
    $item_count = 0;
    $total_amount = 0;

    foreach ($cart['items'] as $item) {
        // FIX: Check if item has the expected structure
        if (isset($item['qty']) && isset($item['price'])) {
            $item_count += $item['qty'];
            $total_amount += $item['price'] * $item['qty'];
        }
    }

    $delivery_fee = $_SESSION['delivery_fee'] ?? 0;
    $cart_data = json_encode($cart);

    // Check if cart already exists in database (only active, not ordered)
    $stmt = $pdo->prepare("
        SELECT saved_cart_id 
        FROM saved_carts 
        WHERE user_id = ? 
          AND restaurant_id = ? 
          AND status = 'active'
          AND (order_id IS NULL OR order_id = 0)
    ");
    $stmt->execute([$user_id, $cart['restaurant_id'] ?? 0]);
    $existing_cart = $stmt->fetch();

    if ($existing_cart) {
        // Update existing cart
        $sql = "UPDATE saved_carts SET 
                cart_data = ?, 
                item_count = ?, 
                total_amount = ?, 
                delivery_fee = ?, 
                updated_at = NOW(),
                last_accessed = NOW()
                WHERE saved_cart_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cart_data, $item_count, $total_amount, $delivery_fee, $existing_cart['saved_cart_id']]);
        return $existing_cart['saved_cart_id'];
    } else {
        // Create new cart
        $sql = "INSERT INTO saved_carts (user_id, restaurant_id, cart_name, cart_data, item_count, total_amount, delivery_fee, created_at, updated_at, last_accessed, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW(), 'active')";
        $stmt = $pdo->prepare($sql);
        $cart_name = "Cart " . date('M d, H:i');
        $stmt->execute([$user_id, $cart['restaurant_id'] ?? 0, $cart_name, $cart_data, $item_count, $total_amount, $delivery_fee]);
        return $pdo->lastInsertId();
    }
}

// Get restaurant_id from POST
$restaurant_id = $_POST['restaurant_id'] ?? null;

// Initialize restaurant-specific cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Use restaurant-specific cart if restaurant_id is provided
if ($restaurant_id) {
    if (!isset($_SESSION['cart'][$restaurant_id])) {
        $_SESSION['cart'][$restaurant_id] = [
            'restaurant_id' => $restaurant_id,
            'items' => []
        ];
    }
    $cart = &$_SESSION['cart'][$restaurant_id];
} else {
    // Fallback to default cart (for backward compatibility)
    if (!isset($_SESSION['cart']['default'])) {
        $_SESSION['cart']['default'] = [
            'restaurant_id' => null,
            'items' => []
        ];
    }
    $cart = &$_SESSION['cart']['default'];
}

if (isset($_POST['delivery_fee']) && is_numeric($_POST['delivery_fee'])) {
    $_SESSION['delivery_fee'] = (int)$_POST['delivery_fee'];
    $_SESSION['cart_delivery_fee'] = (int)$_POST['delivery_fee'];

    // Also store in current cart
    $cart['delivery_fee'] = (int)$_POST['delivery_fee'];
}

if (!isset($_SESSION['user_id'])) {
    echo "<div class='cart-box'><p>Please <a href='../login.php'>login</a> to add items.</p></div>";
    // always output an item-count so front-end can read it
    echo "<div id='cart-item-count' style='display:none;'>0</div>";
    exit;
}

$action   = $_POST['action'] ?? 'view';
$itemId   = isset($_POST['id']) ? (int)$_POST['id'] : null;
$newQty   = isset($_POST['qty']) ? (int)$_POST['qty'] : null;
$options  = isset($_POST['options']) ? json_decode($_POST['options'], true) : [];

function fetchMenuItem(PDO $pdo, int $itemId)
{
    $sql = "SELECT item_id, restaurant_id, name, price, image FROM menu_items WHERE item_id = ? AND is_available = 1";
    $st = $pdo->prepare($sql);
    $st->execute([$itemId]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetchOptionDetails(PDO $pdo, $optionId, $valueId)
{
    $sql = "SELECT mo.option_name, ovo.value_name, ovo.price_modifier 
            FROM menu_options mo 
            JOIN option_values ovo ON mo.option_id = ovo.option_id 
            WHERE mo.option_id = ? AND ovo.value_id = ?";
    $st = $pdo->prepare($sql);
    $st->execute([$optionId, $valueId]);
    return $st->fetch(PDO::FETCH_ASSOC);
}

switch ($action) {
    case 'add_with_qty':
        if (!$itemId || !$newQty || $newQty <= 0) break;
        $item = fetchMenuItem($pdo, $itemId);
        if (!$item) break;

        $itemRestaurantId = $item['restaurant_id'];

        // If restaurant_id was provided but doesn't match item's restaurant, use item's restaurant
        if ($restaurant_id && $restaurant_id != $itemRestaurantId) {
            // Switch to item's restaurant
            $restaurant_id = $itemRestaurantId;
            if (!isset($_SESSION['cart'][$restaurant_id])) {
                $_SESSION['cart'][$restaurant_id] = [
                    'restaurant_id' => $restaurant_id,
                    'items' => []
                ];
            }
            $cart = &$_SESSION['cart'][$restaurant_id];
        } else if (!$restaurant_id) {
            // No restaurant_id provided, use the item's restaurant
            $restaurant_id = $itemRestaurantId;
            if (!isset($_SESSION['cart'][$restaurant_id])) {
                $_SESSION['cart'][$restaurant_id] = [
                    'restaurant_id' => $restaurant_id,
                    'items' => []
                ];
            }
            $cart = &$_SESSION['cart'][$restaurant_id];
        }

        // Set restaurant_id in cart
        $cart['restaurant_id'] = $restaurant_id;

        // Calculate additional price from options
        $optionPrice = 0;
        $optionDetails = [];

        if (!empty($options)) {
            foreach ($options as $optionId => $selectedOptions) {
                foreach ($selectedOptions as $opt) {
                    if (isset($opt['value_id'])) {
                        $optionInfo = fetchOptionDetails($pdo, $optionId, $opt['value_id']);
                        if ($optionInfo) {
                            $optionPrice += $optionInfo['price_modifier'];
                            $optionDetails[] = [
                                'option_id' => $optionId,
                                'option_name' => $optionInfo['option_name'],
                                'value_id' => $opt['value_id'],
                                'value_name' => $opt['value_name'],
                                'price_modifier' => $optionInfo['price_modifier']
                            ];
                        }
                    }
                }
            }
        }

        // Create a unique key that includes the item ID and selected options
        $optionsKey = md5($itemId . json_encode($optionDetails));

        if (isset($cart['items'][$optionsKey])) {
            // FIX: Ensure qty key exists
            if (isset($cart['items'][$optionsKey]['qty'])) {
                $cart['items'][$optionsKey]['qty'] += $newQty;
            } else {
                $cart['items'][$optionsKey]['qty'] = $newQty;
            }
        } else {
            // FIX: Ensure all required keys are set
            $cart['items'][$optionsKey] = [
                'id' => (int)$item['item_id'],
                'name' => $item['name'],
                'base_price' => (float)$item['price'],
                'price' => (float)$item['price'] + $optionPrice,
                'qty' => $newQty,
                'restaurant_id' => (int)$item['restaurant_id'],
                'image' => $item['image'] ?? '',
                'options' => $optionDetails,
                'options_key' => $optionsKey
            ];
        }
        break;

    case 'update_qty':
        $itemKey = isset($_POST['key']) ? $_POST['key'] : null;
        if ($itemKey === null || $newQty === null) break;
        if (isset($cart['items'][$itemKey])) {
            if ($newQty <= 0) {
                unset($cart['items'][$itemKey]);
                if (empty($cart['items'])) {
                    // Don't remove restaurant_id
                }
            } else {
                $cart['items'][$itemKey]['qty'] = $newQty;
            }
        }
        break;

    case 'remove':
        $itemKey = isset($_POST['key']) ? $_POST['key'] : null;
        if ($itemKey !== null && isset($cart['items'][$itemKey])) {
            unset($cart['items'][$itemKey]);
            if (empty($cart['items'])) {
                // Don't remove restaurant_id
            }
        }
        break;

    case 'clear':
        // Clear current restaurant's cart
        $cart['items'] = [];
        break;

    case 'clear_all':
        // Clear all carts
        $_SESSION['cart'] = [];
        break;

    case 'view':
    default:
        // Save cart to database if user is logged in
        if (isset($_SESSION['user_id']) && !empty($cart['items'])) {
            $saved_cart_id = saveCartToDatabase($pdo, $_SESSION['user_id'], $cart);
            if ($saved_cart_id) {
                $cart['saved_cart_id'] = $saved_cart_id;
            }
        }
        break;
}

//debugging
echo "<!-- DEBUG: Session delivery_fee = " . ($_SESSION['delivery_fee'] ?? 'NOT SET') . " -->";
echo "<!-- DEBUG: POST delivery_fee = " . ($_POST['delivery_fee'] ?? 'NOT SENT') . " -->";
echo "<!-- DEBUG: Action = " . ($action ?? 'NONE') . " -->";

function renderCartHtml(array $cart)
{
    $items = $cart['items'] ?? [];
    $count = 0;

    // FIX: Check if items exist and have qty key
    foreach ($items as $it) {
        if (isset($it['qty'])) {
            $count += (int)$it['qty'];
        }
    }

    if (empty($items)) {
        echo "<div class='cart-box'><h3>Your Cart</h3><p>Your cart is empty.Please choose the menu items that you want to order.</p></div>";
        echo "<div id='cart-item-count' style='display:none;'>0</div>";
        return;
    }

    $subtotal = 0;
    // FIX: Check if price and qty keys exist
    foreach ($items as $it) {
        if (isset($it['price'], $it['qty'])) {
            $subtotal += $it['price'] * $it['qty'];
        }
    }

    // FIXED: Get delivery fee from session (set by dashboard/restaurant page)
    $deliveryFee = $_SESSION['delivery_fee'] ?? $_SESSION['cart_delivery_fee'] ?? 1500;

    // Also update the current cart's delivery fee
    $cart['delivery_fee'] = $deliveryFee;

    $total = $subtotal + $deliveryFee;

    echo "<div class='cart-box'>";
    echo "<h2>Your Items</h2>";

    echo "<ul class='cart-list'>";

    foreach ($items as $key => $it) {
        // FIX: Check if all required keys exist
        if (!isset($it['price'], $it['qty'], $it['name'])) {
            continue; // Skip invalid items
        }

        $lineTotal = $it['price'] * $it['qty'];
        $name = htmlspecialchars($it['name'] ?? '');
        $id = (int)($it['id'] ?? 0);
        $qty = (int)$it['qty'];
        $basePrice = $it['base_price'] ?? $it['price'];
        $hasOptions = !empty($it['options']);
        $image = $it['image'] ?? '';
        $imagePath = '../../assets/images/' . $image;

        echo "<li class='cart-line'>";
        echo "<div class='cart-item-container'>";
        echo "<div class='cart-item-image'>";
        echo "<img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($name) . "' onerror=\"this.src='../../assets/images/default.jpg'\" />";
        echo "</div>";

        echo "<div class='cart-item-details'>";
        echo "<div class='item-name-price'>";
        echo "<span class='item-name'>{$name}</span>";
        echo "</div>";

        // Display options if any
        if ($hasOptions && isset($it['options']) && is_array($it['options'])) {
            echo "<div class='item-options'>";
            foreach ($it['options'] as $option) {
                $optionPrice = $option['price_modifier'] ?? 0;
                $modifier = "";
                if ($optionPrice > 0) {
                    $modifier = " (+" . number_format($optionPrice, 0) . " MMK)";
                } elseif ($optionPrice == 0) {
                    $modifier = " (included)";
                }
                echo "<div class='option-line'>{$option['option_name']}: {$option['value_name']}{$modifier}</div>";
            }
            echo "</div>";
        }

        echo "</div>"; // Close cart-item-details
        echo "</div>"; // Close cart-item-container

        // Bottom controls
        echo "<div class='cart-controls-bottom'>";
        echo "<div class='quantity-controls'>";
        echo "<button class='qty-btn' onclick='updateQty(\"{$key}\", " . ($qty - 1) . ")'>-</button>";
        echo "<span class='qty'>{$qty}</span>";
        echo "<button class='qty-btn' onclick='updateQty(\"{$key}\", " . ($qty + 1) . ")'>+</button>";
        echo "</div>";
        echo "<span class='line-total'>" . number_format($lineTotal, 0) . " MMK</span>";
        echo "<button class='remove-btn' onclick='removeFromCart(\"{$key}\")' title='Remove'>×</button>";
        echo "</div>";

        // Show price breakdown
        if ($hasOptions) {
            $optionsTotal = 0;
            if (isset($it['options']) && is_array($it['options'])) {
                foreach ($it['options'] as $option) {
                    $optionsTotal += ($option['price_modifier'] ?? 0) * $qty;
                }
            }
        }

        echo "</li>";
    }
    echo "</ul>";

    // Get restaurant_id from the current cart
    $current_restaurant_id = $cart['restaurant_id'] ?? 0;

    echo "<div class='cart-summary'>
    <div class='subtotal'><span>Subtotal</span><strong>" . number_format($subtotal, 0) . " MMK</strong></div>
    <div class='delivery'><span>Delivery Fee</span><strong>" . number_format($deliveryFee, 0) . " MMK</strong></div>
    <div class='total'><span>Total</span><strong>" . number_format($total, 0) . " MMK</strong></div>
</div>
<div style='display: flex; gap: 10px; margin-top: 15px;'>
    <a href='checkout.php?restaurant_id=$current_restaurant_id&delivery_fee=$deliveryFee' class='review'>Go To Checkout</a>
    <a href='cart_history.php' class='review'>View Cart Items</a>
</div>";

    // hidden count for JS
    echo "<div id='cart-item-count' style='display:none;'>" . intval($count) . "</div>";
    // Add saved cart ID
    if (isset($cart['saved_cart_id'])) {
        echo "<div id='saved-cart-id' style='display:none;'>" . $cart['saved_cart_id'] . "</div>";
    }

    // Add restaurant ID as data attribute
    if (!empty($cart['restaurant_id'])) {
        echo "<div id='cart-restaurant-id' data-restaurant-id='" . htmlspecialchars($cart['restaurant_id']) . "' style='display:none;'></div>";
    }
}

// Use the correct cart
renderCartHtml($cart);

?>
<html>
<style>
    /* Cart Modal */
    .cart-box {
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        border: 1px solid #ff0000ff;
        max-width: 420px;
        width: 86%;
        height: 100%;
        margin: 10 auto;
        font-family: 'Segoe UI', system-ui, sans-serif;
        max-height: 95vh;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #cart-container {
        background-color: rgba(177, 175, 175, 0.12);
        position: fixed;
        height: 100%;
        top: 80px;
        right: 30px;
        z-index: 1100;
        width: 380px;
        max-height: calc(100vh - 160px);
        overflow-y: auto;
        box-sizing: border-box;
        padding: 8px;
        border-radius: 20px;
    }

    #cart-container::-webkit-scrollbar {
        display: none;
    }

    .cart-box h2 {
        font-size: 22px;
        margin: 0 0 20px 0;
        color: #1a1a1a;
        font-weight: 700;
        text-align: center;
        position: relative;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }

    .cart-box h3 {
        font-size: 40px;
        margin: 0 0 16px 0;
        color: #333;
        text-align: center;
    }

    .cart-box>p {
        text-align: center;
        color: #666;
        margin: 20px 0;
        font-size: 20px;
        line-height: 1.5;
    }

    .cart-box>p a {
        color: #ff6b00;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .cart-box>p a:hover {
        color: #e55e00;
        text-decoration: underline;
    }

    /* Cart List - Hidden Scrollbar */
    .cart-list {
        list-style: none;
        padding: 0;
        max-height: 450px;
        overflow-y: auto;
        padding-right: 8px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .cart-list::-webkit-scrollbar {
        display: none;
    }

    .cart-box::-webkit-scrollbar {
        display: none;
    }

    /* Cart Line Items - Fixed Layout */
    .cart-line {
        padding: 18px 0;
        border-bottom: 1px dotted #000000ff !important;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cart-line:last-child {
        border-bottom: none;
    }

    .cart-item-container {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        width: 100%;
    }

    .cart-item-image {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #f0f0f0;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .cart-item-image:hover img {
        transform: scale(1.05);
    }

    .cart-item-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .item-name-price {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 4px;
    }

    .item-name {
        font-weight: 700;
        color: #1a1a1a;
        font-size: 16px;
        line-height: 1.3;
        flex: 1;
        text-align: center;
        margin-top: 10px;
    }

    .item-price {
        font-weight: 600;
        color: #ff6b00;
        font-size: 15px;
        white-space: nowrap;
    }

    .item-options {
        margin: 4px 0 0 0;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .option-line {
        font-size: 12px;
        color: #666;
        background: #f8f8f8;
        padding: 3px 8px;
        border-radius: 10px;
        display: inline-block;
    }

    /* Bottom controls section */
    .cart-controls-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;

    }

    .quantity-controls {
        display: flex;
        align-items: center;

    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border-radius: 30px;
        background: #ff6b00;
        color: white;
        border: none;
        font-size: 22px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(255, 107, 0, 0.2);
    }

    .qty-btn:hover {
        background: #ff8533;
        transform: scale(1.05);
    }

    .qty-btn:active {
        transform: scale(0.95);
    }

    .qty {
        font-weight: 700;
        color: #333;
        min-width: 36px;
        text-align: center;
        font-size: 26px;

    }

    .line-total {
        font-weight: 700;
        color: #ff6b00;
        font-size: 16px;
        min-width: 100px;
        text-align: center;
    }

    .remove-btn {
        width: 32px;
        height: 32px;
        border-radius: 30px;
        background: rgba(255, 86, 86, 0.1);
        color: red;
        border: 1px solid rgba(255, 86, 86, 0.2);
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-btn:hover {
        background: #ff5656;
        color: white;
        transform: scale(1.05);
    }

    /* Price breakdown */
    .price-breakdown {
        font-size: 12px;
        color: #888;
        padding: 8px 0 0 0;
        margin-top: 8px;
    }

    .price-breakdown small {
        margin-right: 10px;
    }

    /* Cart Summary - Fixed Layout */
    .cart-summary {
        background: #e4e1e1ff;
        border-radius: 12px;
        padding: 20px;
        margin: 24px 0;
        border: 1px solid #f0f0f0;
    }

    .cart-summary>div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        font-size: 15px;
    }

    .subtotal,
    .delivery {
        color: #666;
        border-bottom: 1px solid #f0f0f0;
    }

    .total {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 8px;
        padding-top: 12px;
        border-top: 2px solid #e0e0e0;
    }

    .total strong {
        color: #ff6b00;
        font-size: 20px;
    }

    /* Checkout Button */
    .review {
        display: block;
        width: 95%;
        background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(255, 107, 0, 0.25);
        position: inherit;
        overflow: hidden;
    }

    .review:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(255, 107, 0, 0.35);
        color: white;
        text-decoration: none;
    }

    .review:active {
        transform: translateY(0);
    }

    /* Empty Cart State */
    .cart-box:has(.cart-list:empty) {
        text-align: center;
        padding: 40px 24px;
    }

    .cart-box:has(.cart-list:empty) h3 {
        margin-bottom: 16px;
        color: #666;
    }

    .cart-box:has(.cart-list:empty) p {
        color: #888;
        font-size: 16px;
        margin: 0;
        line-height: 1.5;
    }

    /* Loading State */
    .cart-box.loading {
        opacity: 0.7;
        pointer-events: none;
        position: relative;
    }

    .cart-box.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 32px;
        height: 32px;
        border: 3px solid rgba(255, 107, 0, 0.3);
        border-top: 3px solid #ff6b00;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        z-index: 10;
    }

    @keyframes spin {
        0% {
            transform: translate(-50%, -50%) rotate(0deg);
        }

        100% {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .cart-box {
            padding: 20px;
            margin: 10px;
            max-width: none;
            border-radius: 12px;
        }

        .cart-box h2 {
            font-size: 20px;
            margin-bottom: 18px;
        }

        .cart-line {
            padding: 16px 0;
            gap: 10px;
        }

        .cart-item-image {
            width: 70px;
            height: 70px;
        }

        .cart-item-container {
            gap: 12px;
        }

        .item-name {
            font-size: 15px;
        }

        .item-price {
            font-size: 14px;
        }

        .cart-controls-bottom {
            flex-wrap: wrap;
            gap: 10px;
        }

        .line-total {
            order: -1;
            width: 100%;
            text-align: left;
            margin-bottom: 8px;
        }

        .cart-list {
            max-height: 420px;
        }

        .review {
            padding: 14px 20px;
            font-size: 15px;
            width: 90%;
        }
    }

    @media (max-width: 480px) {
        .cart-box {
            padding: 16px;
            border-radius: 10px;
        }

        .cart-box h2 {
            font-size: 18px;
            margin-bottom: 16px;
        }

        .cart-item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
        }

        .cart-item-container {
            gap: 10px;
        }

        .item-name-price {
            flex-direction: column;
            gap: 4px;
        }

        .item-name {
            font-size: 14px;
        }

        .item-price {
            font-size: 13px;
            align-self: flex-start;
        }

        .option-line {
            font-size: 11px;
            padding: 2px 6px;
        }

        .cart-list {
            max-height: 380px;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            font-size: 16px;
        }

        .line-total {
            font-size: 14px;
        }

        .review {
            width: 90%;
        }
    }

    /* Animation Improvements */
    .cart-line {
        animation: slideIn 0.3s ease forwards;
        opacity: 0;
        transform: translateY(10px);
    }

    @keyframes slideIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .cart-line:nth-child(1) {
        animation-delay: 0.05s;
    }

    .cart-line:nth-child(2) {
        animation-delay: 0.1s;
    }

    .cart-line:nth-child(3) {
        animation-delay: 0.15s;
    }

    .cart-line:nth-child(4) {
        animation-delay: 0.2s;
    }

    /* Focus states for accessibility */
    .qty-btn:focus,
    .remove-btn:focus,
    .review:focus {
        outline: 2px solid #ff6b00;
        outline-offset: 2px;
    }
</style>

<script>
    // Enhanced cart functions with animations
    function updateQty(key, newQty) {
        const cartBox = document.querySelector('.cart-box');
        cartBox.classList.add('loading');

        // Add visual feedback
        const itemElement = document.querySelector(`[onclick="updateQty('${key}', ${newQty})"]`).closest('.cart-line');
        itemElement.style.transform = 'scale(0.98)';
        itemElement.style.opacity = '0.8';

        setTimeout(() => {
            fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=update_qty&key=${encodeURIComponent(key)}&qty=${newQty}`
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('cart-container').innerHTML = html;
                    updateCartBadge();
                    cartBox.classList.remove('loading');
                })
                .catch(error => {
                    console.error('Error updating cart:', error);
                    cartBox.classList.remove('loading');
                    itemElement.style.transform = '';
                    itemElement.style.opacity = '';
                });
        }, 300);
    }

    function removeFromCart(key) {
        const cartBox = document.querySelector('.cart-box');
        const itemElement = document.querySelector(`[onclick="removeFromCart('${key}')"]`).closest('.cart-line');

        // Animation for removal
        itemElement.style.transform = 'translateX(-100%)';
        itemElement.style.opacity = '0';
        itemElement.style.transition = 'all 0.3s ease';

        setTimeout(() => {
            fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=remove&key=${encodeURIComponent(key)}`
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('cart-container').innerHTML = html;
                    updateCartBadge();
                })
                .catch(error => {
                    console.error('Error removing from cart:', error);
                    itemElement.style.transform = '';
                    itemElement.style.opacity = '';
                });
        }, 300);
    }

    // Add hover effects programmatically
    document.addEventListener('DOMContentLoaded', function() {
        const cartContainer = document.getElementById('cart-container');

        // Delegate events for dynamic cart content
        cartContainer.addEventListener('mouseover', function(e) {
            if (e.target.classList.contains('qty-btn') || e.target.closest('.qty-btn')) {
                const btn = e.target.classList.contains('qty-btn') ? e.target : e.target.closest('.qty-btn');
                btn.style.transform = 'scale(1.05)';
            }
        });

        cartContainer.addEventListener('mouseout', function(e) {
            if (e.target.classList.contains('qty-btn') || e.target.closest('.qty-btn')) {
                const btn = e.target.classList.contains('qty-btn') ? e.target : e.target.closest('.qty-btn');
                btn.style.transform = 'scale(1)';
            }
        });
    });
</script>

</html>