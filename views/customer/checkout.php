<?php
session_start();
require_once '../../config/db.php';

// Get restaurant_id from URL
$restaurant_id = $_GET['restaurant_id'] ?? 0;

// Get delivery fee from URL
$delivery_fee = isset($_GET['delivery_fee']) ? (int)$_GET['delivery_fee'] : ($_SESSION['delivery_fee'] ?? 1500);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch user to prefill details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get cart based on restaurant_id
$cart = null;
$cart_items = [];
$subtotal = 0;

if ($restaurant_id && isset($_SESSION['cart'][$restaurant_id])) {
    // Use restaurant-specific cart
    $cart = $_SESSION['cart'][$restaurant_id];
    $cart_items = $cart['items'] ?? [];
    
    // Fetch menu item images
    if (!empty($cart_items)) {
        $item_ids = array_column($cart_items, 'id');
        $placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT item_id, image FROM menu_items WHERE item_id IN ($placeholders)");
        $stmt->execute($item_ids);
        $item_images = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Add images to cart items
        foreach ($cart_items as &$item) {
            $item['image'] = $item_images[$item['id']] ?? 'default.png';
        }
    }
} elseif (isset($_SESSION['cart']['default'])) {
    // Fallback to default cart
    $cart = $_SESSION['cart']['default'];
    $cart_items = $cart['items'] ?? [];
} else {
    // No cart found
    echo "<p>Your cart is empty. <a href='dashboard.php'>Go back</a></p>";
    exit;
}

// Cart check
if (!$cart || empty($cart_items)) {
    echo "<p>Your cart is empty. <a href='dashboard.php'>Go back</a></p>";
    exit;
}

// Get restaurant_id from cart if not provided in URL
if (!$restaurant_id && isset($cart['restaurant_id'])) {
    $restaurant_id = (int)$cart['restaurant_id'];
}

// Fetch restaurant for lat/lng and logo
$stmt = $pdo->prepare("SELECT restaurant_id, name, lat, lng, logo FROM restaurants WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$restaurant) {
    die("Invalid restaurant selected.");
}

$restaurant_lat = $restaurant['lat'] ?? 16.8409;
$restaurant_lng = $restaurant['lng'] ?? 96.1735;
$restaurant_logo = !empty($restaurant['logo']) ? $restaurant['logo'] : 'default-restaurant.jpg';

// Check if user has location set from dashboard
$user_lat = $_SESSION['user_lat'] ?? null;
$user_lng = $_SESSION['user_lng'] ?? null;

// Calculate distance if user location is available
$distance = null;
if ($user_lat && $user_lng) {
    // Haversine formula to calculate distance in km
    $distance_stmt = $pdo->prepare("
        SELECT (6371 * acos(cos(radians(?)) * cos(radians(?)) * cos(radians(?) - radians(?)) + sin(radians(?)) * sin(radians(?)))) AS distance
    ");
    $distance_stmt->execute([$user_lat, $restaurant_lat, $restaurant_lng, $user_lng, $user_lat, $restaurant_lat]);
    $distance_result = $distance_stmt->fetch(PDO::FETCH_ASSOC);
    $distance = $distance_result['distance'] ?? null;
}

// Check if restaurant is within delivery range (3km)
$is_within_range = $distance !== null && $distance <= 3;

// Calculate subtotal
foreach ($cart_items as $it) {
    if (isset($it['price'], $it['qty'])) {
        $subtotal += $it['price'] * $it['qty'];
    }
}

// YOUR ORIGINAL DELIVERY FEE LOGIC
$final_delivery_fee = $delivery_fee;

// Calculate total
$total_default = $subtotal + $final_delivery_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Checkout - Food&Me</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        .cart-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.cart-item {
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    margin-bottom: 6px;
}

.item-options {
    margin-left: 20px;
    margin-bottom: 6px;
}

.option-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #6c757d;
    margin: 3px 0;
}

.option-text {
    flex: 1;
}

.option-price {
    font-weight: 600;
    color: #28a745;
    min-width: 80px;
    text-align: right;
}

.option-price.free {
    color: #6c757d;
    font-style: italic;
}

.price-breakdown {
    font-size: 11px;
    color: #868e96;
    font-style: italic;
    text-align: right;
    margin-top: 4px;
}

.item-qty {
    min-width: 25px;
    color: #495057;
}

.item-name {
    flex: 1;
    margin: 0 8px;
}

.item-price {
    font-weight: bold;
    color: #e74c3c;
    min-width: 90px;
    text-align: right;
}
        .out-of-range-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #856404;
        }
        
        .out-of-range-warning h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        
        .out-of-range-warning p {
            margin: 5px 0;
        }
        
        .checkout-btn:disabled {
            background: #ccc !important;
            cursor: not-allowed !important;
            transform: none !important;
        }
        
        .distance-info {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 10px 15px;
            margin: 10px 0;
            color: #155724;
        }
        
        .distance-warning {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 10px 15px;
            margin: 10px 0;
            color: #721c24;
        }
        
        .custom-marker {
            background: none !important;
            border: none !important;
        }
        
        .leaflet-marker-draggable {
            cursor: move !important;
        }
        
        .restaurant-marker {
            background: none !important;
            border: none !important;
        }
        
        /* New CSS for fixes */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .map-and-search {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .map-and-search > div:first-child {
            flex: 2;
        }

        .map-and-search > .search-panel {
            flex: 1;
        }

        .search-panel {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .controls {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
        }

        .search-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .use-loc-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
        }

        .use-loc-btn:hover {
            background: #0056b3;
        }

        .result-item {
            padding: 8px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .result-item:hover {
            background: #e9ecef;
        }
        
        .pay {
            margin-top: 10px;
        }
        
        .pay label {
            display: block;
            margin: 8px 0;
            cursor: pointer;
        }
        
        .payment-icons {
            width: 24px;
            height: 24px;
            vertical-align: middle;
            margin-left: 8px;
        }
        
        .address-loading {
            color: #666;
            font-style: italic;
        }
        
        /* New styles for images */
        .restaurant-header-checkout {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        
        .restaurant-logo {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #ff6600;
        }
        
        .cart-item-with-image {
            display: flex;
            gap: 15px;
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .cart-item-with-image:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .item-image {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #dee2e6;
        }
        
        .item-qty-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff6600;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            border: 2px solid white;
        }
        
        .cart-items-container {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .cart-items-container::-webkit-scrollbar {
            width: 5px;
        }
        
        .cart-items-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .cart-items-container::-webkit-scrollbar-thumb {
            background: #ff6600;
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            .restaurant-header-checkout {
                flex-direction: column;
                text-align: center;
                padding: 12px;
            }
            
            .cart-item-with-image {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 12px;
            }
            
            .item-image {
                width: 100px;
                height: 100px;
            }
        }
            .back-btn{
      color: white;
      text-decoration: none;
      font-size: 18px;
      font-weight: bold;
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 5px;
      margin-right: 1
    }
    
    .back-btn:hover{
      color: black;
    }
    </style>
</head>
<body>
    <header class="sticky-header">
    <a href="dashboard.php?page=restaurants" class="back-btn">⬅ Back to Restaurants</a>
   <div class="brand">
                    <div class="customer_brand__logo">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <span class="customer_brand__name">Food<span>&amp;</span>Me</span>
                </div>
  
   </header>
<div class="checkout-container">
    <div class="checkout-left">
        <h1 style="text-align: center;margin-bottom:20px;">Review and Place Your Order</h1>
        
        <?php if (!$is_within_range && $distance !== null): ?>
        <div class="out-of-range-warning">
            <h4>⚠️ Delivery Not Available</h4>
            <p>This restaurant is <strong><?= number_format($distance, 1) ?> km</strong> away from your selected location, which is outside our 3km delivery range.</p>
            <p>Please <a href="dashboard.php" style="color: #007bff; text-decoration: underline;">go back to dashboard</a> and choose a restaurant within 3km of your location.</p>
        </div>
        <?php endif; ?>
        
       

        <form id="checkoutForm" action="place_order.php" method="POST" <?= !$is_within_range ? 'onsubmit="return false;"' : '' ?>>
            <input type="hidden" name="restaurant_id" value="<?= (int)$restaurant_id ?>">
            <input type="hidden" name="delivery_fee" id="hidden_delivery_fee" value="<?= $final_delivery_fee ?>">

            <?php
            // Split name for the edit form fields
            $name_parts = explode(' ', $user['name'] ?? ' ', 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';
            ?>

            <input type="hidden" name="customer_name" id="hidden_customer_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
            <input type="hidden" name="customer_email" id="hidden_customer_email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
            <input type="hidden" name="customer_phone" id="hidden_customer_phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">

            <div class="card" id="personalDetailsView">
                <div class="card-header">
                    <strong>Personal details</strong>
                    <a href="#" id="editBtn">Edit</a>
                </div>
                <div class="card-body">
                    <p id="view_name"><?= htmlspecialchars($user['name'] ?? '') ?></p>
                    <p id="view_email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <p id="view_phone"><?= htmlspecialchars($user['phone'] ?? '') ?></p>
                </div>
            </div>

            <div class="card" id="personalDetailsEdit" style="display:none;">
                <div class="card-header">
                    <strong>Personal details</strong>
                    <a href="#" id="cancelBtn">Cancel</a>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="email_edit">Email</label>
                        <input type="email" id="email_edit" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name_edit">First name</label>
                            <input type="text" id="first_name_edit" class="form-control" value="<?= htmlspecialchars($first_name) ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name_edit">Last name</label>
                            <input type="text" id="last_name_edit" class="form-control" value="<?= htmlspecialchars($last_name) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mobile_edit">Mobile number</label>
                        <input type="tel" id="mobile_edit" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <button type="button" id="saveBtn" class="save-button">Save</button>
                </div>
            </div>
            
            <label for="delivery_address" style="margin-top:20px;"><strong>Delivery Address</strong></label>
            <textarea name="delivery_address" id="delivery_address" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-height: 80px;"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>

            <input type="hidden" name="lat" id="lat" value="<?= $user_lat ?>">
            <input type="hidden" name="lng" id="lng" value="<?= $user_lng ?>">

            <div class="map-and-search">
                <div id="map"></div>
                <div class="search-panel">
                    <div style="font-weight:600; margin-bottom:6px;">Search location</div>
                    <div class="controls">
                        <input id="searchQuery" class="search-input" placeholder="Search address or place">
                        <button type="button" id="searchBtn" class="use-loc-btn">Search</button>
                    </div>
                    <button type="button" id="useLocationBtn" class="use-loc-btn" style="width:100%; margin-top: 10px;">Use my current location</button>
                    <div style="margin-top:10px; font-weight:600;">Results</div>
                    <div id="results" style="max-height:200px; overflow:auto; margin-top:6px;"></div>
                    <div style="margin-top:12px; font-weight:600;">Delivery Range Info</div>
                    <small>Restaurant delivery range: <strong>3km</strong>. Orders outside this range cannot be processed.</small>
                    <div id="distanceInfo" style="margin-top: 8px; padding: 8px; border-radius: 4px;"></div>
                </div>
            </div>

            <div>
                <strong style="margin-top: 20px; display: block;">Payment Method</strong>
                <div class="pay">
                <label><input type="radio" name="payment_method" value="cod" required <?= !$is_within_range ? 'disabled' : '' ?>> Cash on Delivery</label>
                
                </div>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="checkout-btn" id="placeOrderBtn" <?= !$is_within_range ? 'disabled' : '' ?> style="background: #ff6b00; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; width: 100%;">
                    <?= $is_within_range ? 'Place Order' : 'Out of Delivery Range' ?>
                </button>
            </div>
        </form>
    </div>

   <aside class="checkout-right">
    <h1 style="text-align: center;margin-bottom:20px;">Your Order</h1>
    
    <!-- Restaurant Header with Logo -->
    <div class="restaurant-header-checkout">
        <img src="../../assets/images/<?= htmlspecialchars($restaurant_logo) ?>" 
             alt="<?= htmlspecialchars($restaurant['name']) ?>" 
             class="restaurant-logo"
             onerror="this.src='../../assets/images/default-restaurant.jpg'; this.onerror=null;">
        <div style="flex: 1;">
            <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #333; font-weight: 600;">
                <?= htmlspecialchars($restaurant['name']) ?>
            </h4>
            
        </div>
    </div>
    
    <div class="cart-box">
        <div class="cart-items-header" style="margin: 15px 0 10px 0; padding-bottom: 10px; border-bottom: 2px solid #ff6600;">
            <h4 style="margin: 0; color: #333; font-size: 14px; font-weight: 600;">
                <i class="fas fa-shopping-cart" style="color: #ff6600; margin-right: 8px;"></i>
                Order Items (<?= count($cart_items) ?>)
            </h4>
        </div>
        
        <div class="cart-items-container">
            <?php foreach ($cart_items as $index => $it):
                $line_total = $it['price'] * $it['qty'];
                $base_price = $it['base_price'] ?? $it['price'];
                $has_options = !empty($it['options']);
                $item_image = $it['image'] ?? 'default.png';
            ?>
            <div class="cart-item-with-image">
                <!-- Item Image -->
                <div style="position: relative; flex-shrink: 0;">
                    <img src="../../assets/images/<?= htmlspecialchars($item_image) ?>" 
                         alt="<?= htmlspecialchars($it['name']) ?>"
                         class="item-image"
                         onerror="this.src='../../assets/images/default.png'; this.onerror=null;">
                    <div class="item-qty-badge">
                        <?= (int)$it['qty'] ?>
                    </div>
                </div>
                
                <!-- Item Details -->
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-weight: 600; font-size: 14px; color: #333; margin-bottom: 4px;">
                                <?= htmlspecialchars($it['name']) ?>
                            </div>
                        </div>
                        <div style="font-weight: bold; color: #ff6600; font-size: 15px;">
                            <?= number_format($line_total, 0) ?> MMK
                        </div>
                    </div>
                    
                    <!-- Options -->
                    <?php if ($has_options): ?>
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #eee;">
                        <?php foreach ($it['options'] as $option): ?>
                            <?php 
                            $option_price = $option['price_modifier'] ?? 0;
                            $option_total = $option_price * $it['qty'];
                            ?>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; margin: 3px 0;">
                                <span style="color: #495057;">
                                    <i class="fas fa-circle" style="font-size: 6px; color: #ff6600; margin-right: 6px;"></i>
                                    <?= htmlspecialchars($option['option_name'] ?? 'Option') ?>
                                    <?php if (!empty($option['value_name'])): ?>
                                        : <span style="font-weight: 500;"><?= htmlspecialchars($option['value_name']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <?php if ($option_price > 0): ?>
                                    <span style="color: #28a745; font-weight: 600; font-size: 10px;">
                                        +<?= number_format($option_total, 0) ?> MMK
                                    </span>
                                <?php else: ?>
                                    <span style="color: #6c757d; font-style: italic; font-size: 10px;">
                                        included
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Subtotal</span> 
                <strong id="subtotal"><?= number_format($subtotal, 0) ?> MMK</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Delivery Fee</span> 
                <strong id="deliveryFee"><?= number_format($final_delivery_fee, 0) ?> MMK</strong>
            </div>
            <hr style="margin: 12px 0; border-color: #dee2e6;">
            <div style="display: flex; justify-content: space-between; font-size: 1.2em;">
                <span style="font-weight: bold;">Total</span> 
                <strong id="totalAmount" style="color: #ff6600; font-weight: bold;"><?= number_format($total_default, 0) ?> MMK</strong>
            </div>
        </div>
    </div>
</aside>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const restaurantLat = <?= json_encode((float)$restaurant_lat) ?>;
const restaurantLng = <?= json_encode((float)$restaurant_lng) ?>;
const subtotal = <?= json_encode((float)$subtotal) ?>;
const defaultFee = <?= json_encode((int)$final_delivery_fee) ?>;
const MAX_DELIVERY_DISTANCE = 3;

let map, marker;
let isReverseGeocoding = false;

// Initialize map
function initMap() {
    try {
        map = L.map('map').setView([restaurantLat, restaurantLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Restaurant marker
        L.marker([restaurantLat, restaurantLng], {
            icon: L.divIcon({
                html: '<i class="fas fa-utensils" style="font-size: 24px; color: #ff6600;"></i>',
                iconSize: [24, 24],
                iconAnchor: [12, 24],
                className: 'restaurant-marker'
            })
        }).addTo(map).bindPopup('<?= htmlspecialchars($restaurant['name'] ?? 'Restaurant') ?>');

        // Delivery radius circle
        L.circle([restaurantLat, restaurantLng], {
            color: '#ff6600',
            fillColor: '#ff6600',
            fillOpacity: 0.1,
            radius: MAX_DELIVERY_DISTANCE * 1000
        }).addTo(map).bindPopup('3km Delivery Radius');

        console.log('Map initialized successfully');
    } catch (error) {
        console.error('Map initialization error:', error);
        document.getElementById('map').innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Map failed to load. Please refresh the page.</div>';
    }
}

function reverseGeocodeAndUpdate(lat, lng) {
    if (isReverseGeocoding) return;
    
    isReverseGeocoding = true;
    const addressTextarea = document.getElementById('delivery_address');
    const originalValue = addressTextarea.value;

    addressTextarea.value = 'Getting address...';
    addressTextarea.classList.add('address-loading');
    
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then(r => r.json())
        .then(data => {
            if (data && data.display_name) {
                addressTextarea.value = data.display_name;
                console.log('Address updated:', data.display_name);
            } else {
                addressTextarea.value = originalValue;
                console.warn('No address found for coordinates:', lat, lng);
            }
        })
        .catch(err => {
            console.error('Geocoding error:', err);
            addressTextarea.value = originalValue;
            addressTextarea.value = `Location at ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        })
        .finally(() => {
            isReverseGeocoding = false;
            addressTextarea.classList.remove('address-loading');
        });
}

function setMarker(lat, lng, draggable = true) {
    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;
    
    if (!marker) {
        const customIcon = L.divIcon({
            html: '<i class="fas fa-map-marker-alt" style="font-size: 32px; color: #ff6600; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);"></i>',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            className: 'custom-marker'
        });
        
        marker = L.marker([lat, lng], { 
            draggable: draggable,
            icon: customIcon
        }).addTo(map);
        
        // Enhanced drag event with real-time updates
        marker.on('dragstart', function() {
            console.log('Started dragging marker');
        });
        
        marker.on('drag', function(event) {
            const newPos = event.target.getLatLng();
            document.getElementById('lat').value = newPos.lat;
            document.getElementById('lng').value = newPos.lng;
            // Update address in real-time during drag
            document.getElementById('delivery_address').value = `Moving to ${newPos.lat.toFixed(6)}, ${newPos.lng.toFixed(6)}...`;
        });
        
        marker.on('dragend', function(event) {
            const newPos = event.target.getLatLng();
            const newLat = newPos.lat;
            const newLng = newPos.lng;
            
            document.getElementById('lat').value = newLat;
            document.getElementById('lng').value = newLng;
            
            console.log('Marker dragged to:', newLat, newLng);
            
            // Get address for new position
            reverseGeocodeAndUpdate(newLat, newLng);
            checkDeliveryRange(newLat, newLng);
        });
        
    } else {
        marker.setLatLng([lat, lng]);
    }
    map.setView([lat, lng], 16);
}

// Haversine distance calculation
function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2-lat1) * Math.PI/180;
    const dLon = (lon2-lon1) * Math.PI/180;
    const a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)*Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Check delivery range
function checkDeliveryRange(userLat, userLng) {
    const distKm = haversine(restaurantLat, restaurantLng, userLat, userLng);
    const isWithinRange = distKm <= MAX_DELIVERY_DISTANCE;
    
    const distanceInfo = document.getElementById('distanceInfo');
    if (isWithinRange) {
        distanceInfo.innerHTML = `<div style="color: #155724; background: #d4edda; padding: 8px; border-radius: 4px;">
            <i class="fas fa-check-circle"></i> Within range: ${distKm.toFixed(1)} km
        </div>`;
        
        if (marker) {
            marker.setIcon(L.divIcon({
                html: '<i class="fas fa-map-marker-alt" style="font-size: 32px; color: #28a745; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);"></i>',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                className: 'custom-marker'
            }));
        }
    } else {
        distanceInfo.innerHTML = `<div style="color: #721c24; background: #f8d7da; padding: 8px; border-radius: 4px;">
            <i class="fas fa-exclamation-triangle"></i> Out of range: ${distKm.toFixed(1)} km (max: ${MAX_DELIVERY_DISTANCE}km)
        </div>`;
        
        if (marker) {
            marker.setIcon(L.divIcon({
                html: '<i class="fas fa-map-marker-alt" style="font-size: 32px; color: #dc3545; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);"></i>',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                className: 'custom-marker'
            }));
        }
    }
    
    updateDeliveryFee(userLat, userLng, isWithinRange);
    return isWithinRange;
}

// Update delivery fee
function updateDeliveryFee(userLat, userLng, isWithinRange) {
    const fee = isWithinRange ? defaultFee : 0;
    
    document.getElementById('deliveryFee').innerText = isWithinRange ? fee.toLocaleString() + " MMK" : "Not available";
    document.getElementById('totalAmount').innerText = isWithinRange ? (subtotal + fee).toLocaleString() + " MMK" : "Not available";
    document.getElementById('hidden_delivery_fee').value = fee;
    
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    
    if (isWithinRange) {
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = 'Place Order';
        paymentRadios.forEach(radio => radio.disabled = false);
    } else {
        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = 'Out of Delivery Range';
        paymentRadios.forEach(radio => radio.disabled = true);
    }
}

// Search functionality
function renderResults(list) {
    const resultsDiv = document.getElementById('results');
    resultsDiv.innerHTML = '';
    if (!Array.isArray(list) || list.length === 0) {
        resultsDiv.innerHTML = '<div style="padding:8px;color:#666">No results found</div>';
        return;
    }
    list.forEach(r => {
        const div = document.createElement('div');
        div.className = 'result-item';
        div.innerHTML = `<div style="font-weight:600;">${r.display_name.split(',')[0]}</div><small>${r.display_name}</small>`;
        div.addEventListener('click', () => {
            const lat = parseFloat(r.lat), lon = parseFloat(r.lon);
            setMarker(lat, lon);
            document.getElementById('delivery_address').value = r.display_name;
            checkDeliveryRange(lat, lon);
            resultsDiv.innerHTML = '';
        });
        resultsDiv.appendChild(div);
    });
}

function doSearch(q) {
    if (!q || q.trim().length === 0) return;
    const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(q)}&limit=6&countrycodes=MM`;
    fetch(url)
        .then(r => r.json())
        .then(list => renderResults(list))
        .catch(() => {
            document.getElementById('results').innerHTML = '<div style="padding:8px;color:#666">Search failed</div>';
        });
}

// Update location preview
function updateLocationPreview() {
    const addressTextarea = document.getElementById('delivery_address');
    const locationPreview = document.getElementById('currentLocationPreview');
    
    if (addressTextarea && locationPreview) {
        const text = addressTextarea.value.trim();
        if (text) {
            // Take first 40 characters for preview
            locationPreview.textContent = text.length > 40 ? text.substring(0, 40) + '...' : text;
        } else {
            locationPreview.textContent = 'Select location';
        }
    }
}

// Event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    initMap();
    
    // Initialize location preview
    updateLocationPreview();
    
    // Update preview when address changes
    document.getElementById('delivery_address').addEventListener('input', updateLocationPreview);
    
    // Edit functionality
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const viewDiv = document.getElementById('personalDetailsView');
    const editDiv = document.getElementById('personalDetailsEdit');

    if (editBtn) {
        editBtn.addEventListener('click', function(e) {
            e.preventDefault();
            viewDiv.style.display = 'none';
            editDiv.style.display = 'block';
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            editDiv.style.display = 'none';
            viewDiv.style.display = 'block';
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const newFirstName = document.getElementById('first_name_edit').value;
            const newLastName = document.getElementById('last_name_edit').value;
            const newFullName = (newFirstName + ' ' + newLastName).trim();
            const newEmail = document.getElementById('email_edit').value;
            const newPhone = document.getElementById('mobile_edit').value;

            // Update the UI immediately for better UX
            document.getElementById('view_name').textContent = newFullName;
            document.getElementById('view_email').textContent = newEmail;
            document.getElementById('view_phone').textContent = newPhone;

            document.getElementById('hidden_customer_name').value = newFullName;
            document.getElementById('hidden_customer_email').value = newEmail;
            document.getElementById('hidden_customer_phone').value = newPhone;

            // Save phone number to database via AJAX
            fetch('../../api/update_phone.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ phone: newPhone })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to update phone number:', data.message);
                    // Optionally show an error message to the user
                    alert('Failed to update phone number. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating phone number:', error);
                // Optionally show an error message to the user
                alert('Error updating phone number. Please try again.');
            });

            editDiv.style.display = 'none';
            viewDiv.style.display = 'block';
        });
    }

    // Map click event
    if (map) {
        map.on('click', function(e) {
            setMarker(e.latlng.lat, e.latlng.lng);
            reverseGeocodeAndUpdate(e.latlng.lat, e.latlng.lng);
            checkDeliveryRange(e.latlng.lat, e.latlng.lng);
            setTimeout(updateLocationPreview, 500);
        });
    }

    // Use location button
    document.getElementById('useLocationBtn').addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            pos => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                setMarker(lat, lng);
                reverseGeocodeAndUpdate(lat, lng);
                checkDeliveryRange(lat, lng);
                setTimeout(updateLocationPreview, 1000);
            }, 
            err => {
                console.error('Geolocation error:', err);
                alert('Unable to get your location. Please check location permissions or search for your location manually.');
            }, 
            { 
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    });

    // Search functionality
    document.getElementById('searchBtn').addEventListener('click', () => doSearch(document.getElementById('searchQuery').value));
    document.getElementById('searchQuery').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            doSearch(document.getElementById('searchQuery').value);
        }
    });

    // Form submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const userLat = parseFloat(document.getElementById('lat').value);
        const userLng = parseFloat(document.getElementById('lng').value);
        
        if (!userLat || !userLng) {
            e.preventDefault();
            alert('Please set your delivery location first by clicking on the map or using the search.');
            return;
        }
        
        const distKm = haversine(restaurantLat, restaurantLng, userLat, userLng);
        if (distKm > MAX_DELIVERY_DISTANCE) {
            e.preventDefault();
            alert('This location is outside our delivery range. Please choose a location within 3km of the restaurant.');
            return;
        }
    });

    // Initialize with user location or restaurant location
    const userLat = <?= json_encode($user_lat) ?>;
    const userLng = <?= json_encode($user_lng) ?>;
    
    if (userLat && userLng && map) {
        setMarker(userLat, userLng);
        reverseGeocodeAndUpdate(userLat, userLng);
        checkDeliveryRange(userLat, userLng);
        setTimeout(updateLocationPreview, 500);
    } else if (map) {
        setMarker(restaurantLat, restaurantLng, false);
        document.getElementById('distanceInfo').innerHTML = `<div style="color: #856404; background: #fff3cd; padding: 8px; border-radius: 4px;">
            <i class="fas fa-exclamation-triangle"></i> Please set your delivery location by clicking on the map
        </div>`;
    }
});

    // Initialize delivery fee display
    document.getElementById('deliveryFee').innerText = defaultFee.toLocaleString() + " MMK";
    document.getElementById('totalAmount').innerText = (subtotal + defaultFee).toLocaleString() + " MMK";
    document.getElementById('hidden_delivery_fee').value = defaultFee;

</script>
</body>
</html>
<?php include("../../includes/footer.php"); ?>