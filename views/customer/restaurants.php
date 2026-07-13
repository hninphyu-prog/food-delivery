<?php
session_start();

// allow dashboard-carried delivery values to be used
define('IS_VALID_ENTRY_POINT', true);

require_once '../../config/db.php';
// require_once 'deli_fee.php'; // removed — we now use session values from dashboard.php

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$restaurant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$restLat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$restLng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;

// NOTE: we no longer rely on DEFAULT_PREP_TIME constant from deli_fee.php here.
// prep time will be determined after we fetch the restaurant record (prefer session value).
if ($restaurant_id === 0) {
    header("Location: dashboard.php");
    exit;
}

// restaurant details with address and logo
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    header("Location: dashboard.php");
    exit;
}
$restaurant_closed = isset($restaurant['status']) && $restaurant['status'] !== 'active';

// determine prep_time: prefer value carried in session (from dashboard), otherwise use restaurant value or 15
$prep_time = isset($_SESSION['prep_time']) ? (int)$_SESSION['prep_time'] : (int)($restaurant['preparation_time'] ?? 15);

// Fetch reviews with user information
$stmt = $pdo->prepare("
    SELECT r.*, u.name
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.restaurant_id = ? AND r.status = 'visible' 
    ORDER BY r.review_id DESC
");
$stmt->execute([$restaurant_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get review statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating
    FROM reviews 
    WHERE restaurant_id = ? AND status = 'visible'
");
$stmt->execute([$restaurant_id]);
$review_stats = $stmt->fetch(PDO::FETCH_ASSOC);

if ($restaurant_id === 0) {
    header("Location: dashboard.php");
    exit;
}

// restaurant details with address and logo
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    header("Location: dashboard.php");
    exit;
}

// menu with options (include unavailable items so customers can see them but not order)
$stmt = $pdo->prepare("
    SELECT mi.* 
    FROM menu_items mi
    WHERE mi.restaurant_id=? AND status=1
    ORDER BY
      (SELECT MIN(mi2.item_id) 
     FROM menu_items mi2 
     WHERE mi2.restaurant_id = mi.restaurant_id 
       AND mi2.category = mi.category),
    mi.item_id;
");
$stmt->execute([$restaurant_id]);
$menu = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all options for each menu item
$menu_with_options = [];
foreach ($menu as $item) {
    $item_id = $item['item_id'];
    $menu_with_options[$item_id] = $item;
    
    // Get options for this menu item
    $stmt = $pdo->prepare("
        SELECT mo.* 
        FROM menu_options mo
        JOIN menu_item_options mio ON mo.option_id = mio.option_id
        WHERE mio.item_id = ?
        ORDER BY mo.option_id
    ");
    $stmt->execute([$item_id]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // For each option, get its values
    foreach ($options as &$option) {
        $stmt = $pdo->prepare("
            SELECT ovo.value_id, ovo.value_name, ovo.price_modifier
            FROM option_values ovo
            WHERE ovo.option_id = ?
            ORDER BY ovo.value_id
        ");
        $stmt->execute([$option['option_id']]);
        $option['values'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $menu_with_options[$item_id]['options'] = $options;
}

// categories (do not filter by availability so users can still navigate categories)
$stmt = $pdo->prepare("SELECT DISTINCT category FROM menu_items WHERE restaurant_id=?");
$stmt->execute([$restaurant_id]);
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($restaurant['name'] ?? 'Menu') ?> - Food&Me</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Add this to your restaurants.php style section */
:root {
    --primary-orange: #ff6600;
    --dark-bg: #1a1a1a;
    --light-bg: #ffffff;
    --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

/* Floating Cart Button */
#floating-cart-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-orange) 0%, #ff8533 100%);
    border: none;
    box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
    cursor: pointer;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

#floating-cart-btn:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 10px 30px rgba(255, 102, 0, 0.6);
}

#floating-cart-btn svg {
    width: 28px;
    height: 28px;
    fill: white;
}

#cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff3333;
    color: white;
    font-size: 12px;
    font-weight: bold;
    min-width: 22px;
    height: 22px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    border: 2px solid white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Cart Container - Modern Side Drawer */
.cart-container {
    position: fixed;
    top: 0;
    right: -400px;
    width: 380px;
    height: 100vh;
    background: white;
    box-shadow: -5px 0 30px rgba(0, 0, 0, 0.15);
    z-index: 1001;
    transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.cart-container.open {
    right: 0;
}

.cart-header {
    background: linear-gradient(135deg, var(--dark-bg) 0%, #2a2a2a 100%);
    color: white;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cart-header .total-items {
    background: var(--primary-orange);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 14px;
}

.close-cart {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.close-cart:hover {
    opacity: 1;
}

/* Cart Items Scrollable Area */
.cart-items-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.cart-item {
    background: white;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    display: flex;
    gap: 15px;
    transition: transform 0.2s;
}

.cart-item:hover {
    transform: translateY(-2px);
}

.item-image {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.item-options {
    font-size: 13px;
    color: #666;
    margin: 5px 0;
}

.option-detail {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 2px 0;
}

.option-dot {
    color: var(--primary-orange);
    font-size: 12px;
}

.item-price {
    font-weight: 600;
    color: var(--primary-orange);
    margin-top: 8px;
}

/* Quantity Controls */
.qty-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f0f0f0;
    border: none;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: var(--primary-orange);
    color: white;
}

.qty-display {
    font-weight: 600;
    min-width: 30px;
    text-align: center;
}

.remove-item {
    color: #ff4444;
    background: none;
    border: none;
    font-size: 14px;
    cursor: pointer;
    margin-top: 5px;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.remove-item:hover {
    opacity: 1;
    text-decoration: underline;
}

/* Cart Summary */
.cart-summary {
    background: white;
    padding: 25px;
    border-top: 1px solid #eee;
    box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #666;
}

.summary-row.total {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px dashed #eee;
}

.summary-value {
    font-weight: 600;
    color: #333;
}

.delivery-info {
    background: #e8f5e9;
    padding: 12px;
    border-radius: 8px;
    margin: 15px 0;
    font-size: 14px;
    color: #2e7d32;
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkout-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, var(--primary-orange) 0%, #ff8533 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 15px;
}

.checkout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 102, 0, 0.4);
}

.checkout-btn:disabled {
    background: #cccccc;
    cursor: not-allowed;
    transform: none;
}

.empty-cart {
    text-align: center;
    padding: 50px 20px;
    color: #999;
}

.empty-cart-icon {
    font-size: 60px;
    margin-bottom: 20px;
    color: #ddd;
}

/* Overlay for background dim */
.cart-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(3px);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

.cart-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Responsive */
@media (max-width: 480px) {
    .cart-container {
        width: 100%;
        right: -100%;
    }
    
    #floating-cart-btn {
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
    }
}
    .restaurant-header {
      background: white;
      padding: 15px 20px;
      margin-bottom: 10px;
      transition: transform 0.3s ease;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .restaurant-info {
      display: flex;
      gap: 15px;
      align-items: center;
      margin-left: 20%;
    }
    
    .restaurant-image {
      width: 10%;
      height: 10%;
      border-radius: 10px;
      object-fit: cover;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .restaurant-details {
      flex: 1;
      margin-left: 20px;
    }
    
    .restaurant-name {
      margin: 0 0 5px 0;
      color: #333;
      font-size: 20px;
    }
    .status-pill { display:inline-block; margin-left:8px; padding:3px 8px; border-radius:999px; font-size:12px; line-height:1; color:#fff; background:#198754; }
    .status-pill.closed { background:#dc3545; }
    
    .restaurant-address {
      color: #666;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
    }
    
    .restaurant-rating {
      display: flex;
      align-items: center;
      gap: 5px;
      color: #ff6600;
      font-weight: bold;
      margin: 5px 0;
      font-size: 14px;
    }
    
    .restaurant-meta {
      display: flex;
      gap: 15px;
      margin-top: 8px;
      color: #666;
      font-size: 13px;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      gap: 3px;
    }

.view-reviews-btn {
    background: #ff6600;
    color: white;
    border: none;
    height: 20px; /* adjust height if needed */
    padding: 0 16px; /* vertical padding handled by flex */
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: 10px;

    display: flex;            /* make button a flex container */
    justify-content: center;  /* horizontal centering */
    align-items: center;      /* vertical centering */
}

.view-reviews-btn p {
    margin: 0; /* remove default p margins */
    font-weight: bold;
}


    .view-reviews-btn:hover {
      background: #e55a00;
      transform: translateY(-1px);
    }
    
    /* Header when scrolled */
    .restaurant-header.hidden {
      transform: translateY(-100%);
    }
    
    .menu-filter-bar {
      position: sticky;
      top: 60px; 
      z-index: 999;
      background: #f7f7f7; 
      padding: 10px 20px; 
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      width: 100%;       
      box-sizing: border-box; 
      margin: 0;     
    }

    .category-tabs {
      display: flex;
      gap: 8px;
      flex: 1; 
      overflow-x: auto;
      position: sticky;
    }

    .category-tab {
      padding: 6px 12px;
      border-radius: 20px;
      border: 1px solid #ddd;
      cursor: pointer;
      white-space: nowrap;
    }

    .category-tab.active {
      background: rgb(255, 102, 0);
      color: #fff;
      border-color: rgb(255, 102, 0);
    }

    .search-box {
      flex-shrink: 0;
    }

    .search-box input {
      padding: 8px 12px;
      border-radius: 20px;
      border: 1px solid #ddd;
      min-width: 220px; 
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
    
    /* Reviews Modal Styles */
    #reviewsModal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(8px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 10000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      padding: 20px; 
      box-sizing: border-box;
    }

    #reviewsModal.visible {
      opacity: 1;
      visibility: visible;
    }

    .reviews-modal-content {
      background: white;
      border-radius: 20px;
      width: 90%;
      max-width: 600px;
      height: 80vh;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      position: relative;
      padding: 30px;
      transform: translateY(30px) scale(0.95);
      transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      scrollbar-width: none;
      -ms-overflow-style: none;
    }

    .reviews-modal-content::-webkit-scrollbar {
       display: none;
    }
    #reviewsModal.visible .reviews-modal-content {
      transform: translateY(0) scale(1);
    }

    .reviews-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;

      border-bottom: 1px solid #eee;
      z-index: 1000;
      top: -30px;
      position: sticky;
      background: white;
    }

    .reviews-modal-title {
      font-size: 24px;
      color: #333;
      font-weight: 700;
      margin: 0;
    }

    .close-reviews-modal-btn {
      background: url('../../assets/images/cancel.jpg') no-repeat center center;
      background-size: contain;
      width: 60px;
      height: 60px;
      top: 0;
      border: none;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .close-reviews-modal-btn:hover {
      transform: rotate(90deg);
    }

    .review-item {
      padding: 20px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .review-item:last-child {
      border-bottom: none;
    }

    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #f0f0f0;
      top: 0;
    }

    .review-user {
      font-weight: 600;
      color: #333;
      font-size: 16px;
    }

    .review-rating {
      color: #ff6600;
      font-size: 14px;
    }

    .review-comment {
      color: #666;
      line-height: 1.5;
      font-size: 14px;
    }

    .no-reviews {
      text-align: center;
      color: #999;
      padding: 40px 0;
      font-size: 16px;
    }

    .reviews-stats {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
    }

    .average-rating {
      font-size: 24px;
      font-weight: bold;
      color: #ff6600;
      margin-bottom: 5px;
    }

    .total-reviews {
      color: #666;
      font-size: 14px;
    }
    
    /* Modal styles for options */
    .option-group {
      margin: 15px 0;
      border-bottom: 1px solid #eee;
      padding-bottom: 15px;
    }
    
    .option-title {
      font-weight: bold;
      margin-bottom: 8px;
      display: block;
    }
    
    .option-required {
      color: #ff0000;
    }
    
    .option-item {
      margin: 8px 0;
      display: flex;
      align-items: center;
    }
    
    .option-item input[type="radio"],
    .option-item input[type="checkbox"] {
      margin-right: 10px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .restaurant-info {
        flex-direction: column;
        align-items: flex-start;
        margin-left: 0;
      }
      
      .restaurant-image {
        width: 60px;
        height: 60px;
      }
      
      .menu-filter-bar {
        margin: 10px 15px;
      }
      
      .search-box input {
        min-width: 150px;
      }

      .reviews-modal-content {
        width: 95%;
        padding: 20px;
        height: 85vh;
      }

      .reviews-modal-title {
        font-size: 20px;
      }

      .view-reviews-btn {
        margin-left: 0;
        margin-top: 10px;
        align-self: flex-start;
      }
    }

    @media (max-width: 480px) {
      .restaurant-details {
        margin-left: 0;
      }

      .reviews-modal-content {
        padding: 15px;
        height: 90vh;
      }

      .review-item {
        padding: 15px 0;
      }

      .review-user {
        font-size: 14px;
      }

      .review-comment {
        font-size: 13px;
      }
    }
    
#addToCartModal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10001;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 20px; 
  box-sizing: border-box;
}

#addToCartModal.visible {
  opacity: 1;
  visibility: visible;
}

.modal-content {
  background: linear-gradient(135deg, #fff 0%, #f9f9f9 100%);
  border-radius: 20px;
  width: 90%;
  max-width: 500px;
  height: auto; 
  max-height: 90vh; 
  overflow-y: auto; 
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  position: relative;
  padding: 30px;
  transform: translateY(30px) scale(0.95);
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255, 102, 0, 0.1);
  display: flex;
  flex-direction: column;
}

#addToCartModal.visible .modal-content {
  transform: translateY(0) scale(1);
}

.modal-content h3 {
  font-size: 24px;
  margin: 0 0 20px 0;
  color: #333;
  font-weight: 700;
  text-align: center;
  padding-right: 40px;
  flex-shrink: 0; 
}
.close-modal-btn:hover {
  transform: rotate(90deg);
}

.close-modal-btn::before,
.close-modal-btn::after {
  position: absolute;
  width: 20px;
  height: 2px;
  background: #ffffffff;
  border-radius: 1px;
}

.close-modal-btn::before {
  transform: rotate(45deg);
}

.close-modal-btn::after {
  transform: rotate(-45deg);
}


#modalOptionsContainer {
  flex: 1; 
  overflow-y: auto; 
  margin-bottom: 20px;
  min-height: 150px; 
}
#modalOptionsContainer:empty {
  min-height: 0;
  margin-bottom: 0;
}
/* Option groups */
.option-group {
  margin: 20px 0;
  border-bottom: 1px solid rgba(255, 102, 0, 0.1);
  padding-bottom: 20px;
}

.option-group:last-child {
  border-bottom: none;
}

.option-title {
  font-weight: 700;
  margin-bottom: 15px;
  display: block;
  font-size: 16px;
  color: #333;
  position: relative;
  padding-left: 10px;
}

.option-title::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 16px;
  background: #ff6600;
  border-radius: 2px;
}

.option-required {
  color: red;
  margin-left: 4px;
}

.option-item {
  margin: 12px 0;
  display: flex;
  align-items: center;
  padding: 10px 15px;
  border-radius: 10px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.option-item:hover {
  background: rgba(255, 102, 0, 0.05);
}

.option-item input[type="radio"],
.option-item input[type="checkbox"] {
  margin-right: 12px;
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.option-item input[type="radio"] {
  accent-color: #ff6600;
}

.option-item input[type="checkbox"] {
  accent-color: #ff6600;
  border-radius: 4px;
}

.option-item label {
  flex: 1;
  cursor: pointer;
  font-size: 15px;
  color: #555;
}

.option-price {
  margin-left: auto;
  color: #ff6600;
  font-weight: 600;
  font-size: 14px;
}

/* Quantity controls */
.modal-qty-control {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 25px 0;
  background: rgba(151, 61, 1, 0.05);
  border-radius: 50px;
  max-width: 100%;
  margin-left: auto;
  margin-right: auto;
  flex-shrink: 0; 
}

.qty-btn {
  width: 90%;
  height: 40%;
  border-radius: 50%;
  background: #ff6600;
  color: white;
  border: none;
  font-size: 20px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.qty-btn:hover:not(:disabled) {
  background: #e55a00;
  transform: scale(1.05);
}

.qty-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
}

.qty {
  margin: 0 20px;
  font-size: 18px;
  font-weight: 700;
  color: #333;
  min-width: 30px;
  text-align: center;
}

/* Add to cart button */
#modalAddToCartBtn {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
  margin-top: 10px;
  position: relative;
  overflow: hidden;
  flex-shrink: 0; 
}

#modalAddToCartBtn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

#modalAddToCartBtn:hover::before {
  left: 100%;
}

#modalAddToCartBtn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
}

#modalAddToCartBtn:active {
  transform: translateY(0);
}

.option-error {
  color: red;
  font-size: 14px;
  margin: 10px 0;
  text-align: center;
  display: none;
  background: rgba(255, 56, 96, 0.1);
  padding: 10px;
  border-radius: 8px;
  border-left: 4px solid red;
  flex-shrink: 0;
}

/* Animation for option items */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.option-item {
  animation: fadeInUp 0.3s ease forwards;
}

.option-item:nth-child(1) { animation-delay: 0.05s; }
.option-item:nth-child(2) { animation-delay: 0.1s; }
.option-item:nth-child(3) { animation-delay: 0.15s; }
.option-item:nth-child(4) { animation-delay: 0.2s; }
.option-item:nth-child(5) { animation-delay: 0.25s; }

#modalOptionsContainer::-webkit-scrollbar {
  width: 6px;
}

#modalOptionsContainer::-webkit-scrollbar-track {
  background: rgba(255, 102, 0, 0.1);
  border-radius: 3px;
}

#modalOptionsContainer::-webkit-scrollbar-thumb {
  background: rgba(255, 102, 0, 0.3);
  border-radius: 3px;
}

#modalOptionsContainer::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 102, 0, 0.5);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    padding: 20px;
    border-radius: 15px;
    max-height: 95vh;
  }
  
  .modal-content h3 {
    font-size: 20px;
  }
  
  .option-item {
    padding: 8px 12px;
  }
  
  .modal-qty-control {
    max-width: 180px;
  }
  
  .qty-btn {
    width: 36px;
    height: 36px;
  }
  
  #addToCartModal {
    padding: 10px;
  }
}

@media (max-width: 480px) {
  .modal-content {
    padding: 15px;
    max-height: 98vh;
  }
  
  .modal-content h3 {
    font-size: 18px;
    margin-bottom: 15px;
  }
  
  .option-group {
    margin: 15px 0;
    padding-bottom: 15px;
  }
  
  .option-title {
    font-size: 15px;
  }
  
  .option-item label {
    font-size: 14px;
  }
  
  #modalOptionsContainer {
    min-height: 120px;
  }
}

  /* Special case for modals with many options */
  .modal-content.long-content {
    max-height: 95vh;
  }
  .modal-content.long-content #modalOptionsContainer {
    max-height: 60vh;
  }
  /* Unavailable item styling */
  .menu-img { position: relative; }
  .unavailable-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #dc3545;
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    z-index: 2;
  }
  .menu-card.unavailable { opacity: 0.6; }
  .menu-card .menu-info button:disabled { background: #ccc; cursor: not-allowed; }
  </style>
</head>
<body>
<header class="sticky-header">
  <a href="dashboard.php?page=restaurants" class="back-btn">⬅ Back to Restaurants</a>
  <h2>
    <?= htmlspecialchars($restaurant['name'] ?? 'Menu') ?>
    <span class="status-pill <?= $restaurant_closed ? 'closed' : '' ?>"><?= $restaurant_closed ? 'Closed' : 'Open' ?></span>
  </h2>
</header>
<?php if ($restaurant_closed): ?>
  <div id="closedBanner" style="background:#dc3545;color:#fff;padding:10px 16px;margin:10px 0;border-radius:8px;display:flex;align-items:center;gap:8px;">
    <i class="fas fa-store-slash"></i>
    <strong>This restaurant is currently closed. Ordering is disabled.</strong>
  </div>
<?php endif; ?>


<!-- Restaurant summary header -->
<?php
// ---- START: dynamic restaurant header (uses centralized deli logic) ----
$rest_id = (int)($restaurant['restaurant_id'] ?? 0);

// get avg rating & count
$avg_rating = null;
$rating_count = 0;
if ($rest_id) {
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE restaurant_id = ?");
    $stmt->execute([$rest_id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r && $r['avg_rating'] !== null) {
        $avg_rating = round((float)$r['avg_rating'], 1);
        $rating_count = (int)$r['cnt'];
    }
}

$rest_lat = $restaurant['lat'] ?? $restaurant['latitude'] ?? null;
$rest_lng = $restaurant['lng'] ?? $restaurant['longitude'] ?? null;
$prep_time = (int)($restaurant['preparation_time'] ?? 15); // Default 15 min prep time

// Get user location from session
$user_lat = $_SESSION['user_lat'] ?? null;
$user_lng = $_SESSION['user_lng'] ?? null;

// Calculate delivery fee based on distance (same logic as dashboard)
$delivery_fee = 0;
$delivery_fee_display = 'Fee varies';
$eta_display = '30-40 min';

if ($user_lat && $user_lng && $rest_lat && $rest_lng) {
    // Haversine distance calculation
    function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $R = 6371; // Earth's radius in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }
    
    $distance_km = calculateDistance($user_lat, $user_lng, $rest_lat, $rest_lng);
    
    // Calculate delivery fee based on distance (same logic as dashboard)
    if ($distance_km <= 2) {
        $delivery_fee = 700;
    } else if ($distance_km <= 4) {
        $delivery_fee = 1400;
    } else if ($distance_km <= 6) {
        $delivery_fee = 2100;
    } else {
        $delivery_fee = 2100 + round(($distance_km - 6) * 500);
    }
    
    $delivery_fee_display = number_format($delivery_fee) . ' MMK';
    
    // Calculate ETA
    $average_speed_kmh = 20;
    $travel_time_minutes = ($distance_km / $average_speed_kmh) * 60;
    $total_time_minutes = $prep_time + $travel_time_minutes;
    
    // Format time range (same as dashboard)
    $lower = floor($total_time_minutes / 5) * 5;
    $upper = $lower + 10;
    $eta_display = $lower . '-' . $upper . ' min';
}

// Store the delivery fee in session for checkout to use
$_SESSION['delivery_fee'] = $delivery_fee;
$_SESSION['delivery_fee_display'] = $delivery_fee_display;
$_SESSION['delivery_eta'] = $eta_display;

// Also store restaurant info for checkout
$_SESSION['current_restaurant'] = [
    'id' => $restaurant_id,
    'lat' => $rest_lat,
    'lng' => $rest_lng,
    'name' => $restaurant['name']
];

// helper displays
$rating_html = $avg_rating ? ($avg_rating . ($rating_count ? " ({$rating_count})" : "")) : '—';

$rest_logo = htmlspecialchars($restaurant['logo'] ?? 'default-restaurant.jpg');
$rest_name = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
$rest_address = htmlspecialchars($restaurant['address'] ?? 'Address not available');
$rest_cuisine = htmlspecialchars($restaurant['cuisine_type'] ?? '');
?>
<!-- dynamic restaurant header -->
<div class="restaurant-header" id="restaurantHeader"
     data-restaurant-id="<?= $restaurant_id ?>"
     data-lat="<?= htmlspecialchars($rest_lat) ?>"
     data-lng="<?= htmlspecialchars($rest_lng) ?>"
     data-prep="<?= htmlspecialchars($prep_time) ?>"
     window.currentRestaurantId = <?= $restaurant_id ?>;>
     
  <div class="restaurant-info">
    <img src="../../assets/images/<?= $rest_logo ?>" 
         alt="<?= $rest_name ?>" class="restaurant-image">
    <div class="restaurant-details">
      <p class="restaurant-address">
        <i class="fas fa-map-marker-alt"></i>
        <?= $rest_address ?>
      </p>
      <div class="restaurant-rating">
        <i class="fas fa-star"></i>
        <span id="ratingText"><?= $rating_html ?> <?= $rest_cuisine ? "• {$rest_cuisine}" : "" ?></span>
        <?php if ($review_stats && $review_stats['total_reviews'] > 0): ?>
          <button class="view-reviews-btn" onclick="openReviewsModal()">
            <p>View all reviews </p>(<?= $review_stats['total_reviews'] ?>)
          </button>
        <?php endif; ?>
      </div>
      <div class="restaurant-meta">
        <div class="meta-item">
          <i class="fas fa-clock"></i>
          <span id="etaDisplay"><?= htmlspecialchars($eta_display) ?></span>
        </div>
        <div class="meta-item">
          <i class="fas fa-tag"></i>
          <span id="feeDisplay"><?= htmlspecialchars($delivery_fee_display) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Reviews Modal -->
<div id="reviewsModal" class="modal-backdrop" aria-hidden="true">
  <div class="reviews-modal-content" role="dialog" aria-modal="true" aria-labelledby="reviewsModalTitle">
    <div class="reviews-modal-header">
      <h3 class="reviews-modal-title" id="reviewsModalTitle">
        Customer Reviews - <?= htmlspecialchars($restaurant['name']) ?>
      </h3>
      <button onclick="closeReviewsModal()" class="close-reviews-modal-btn" type="button"></button>
    </div>

    <?php if ($review_stats && $review_stats['total_reviews'] > 0): ?>
      <div class="reviews-stats">
        <div class="average-rating">
          ★ <?= number_format($review_stats['avg_rating'], 1) ?> 
        </div>
        <div class="total-reviews">
          Based on <?= $review_stats['total_reviews'] ?> reviews
        </div>
      </div>

      <div class="reviews-list">
        <?php foreach ($reviews as $review): ?>
          <div class="review-item">
            <div class="review-header">
              <div class="review-user"><?= htmlspecialchars($review['name']) ?></div>
              <div class="review-rating">
                <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
              </div>
            </div>
            <div class="review-comment">
              <?= htmlspecialchars($review['comment']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="no-reviews">
        <i class="fas fa-comment-slash" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
        <p>No reviews yet for this restaurant.</p>
        <p>Be the first to share your experience!</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Reviews Modal Functionality
function openReviewsModal() {
  const modal = document.getElementById('reviewsModal');
  if (modal) {
    modal.classList.add('visible');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
  }
}

function closeReviewsModal() {
  const modal = document.getElementById('reviewsModal');
  if (modal) {
    modal.classList.remove('visible');
    document.body.style.overflow = ''; // Restore scrolling
  }
}

// Close modal when clicking outside content
document.getElementById('reviewsModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closeReviewsModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeReviewsModal();
  }
});
</script>

<script>
// client-side: use SAME logic as dashboard.php
(function(){
  const header = document.getElementById('restaurantHeader');
  if (!header) return;

  const restLat = parseFloat(header.dataset.lat);
  const restLng = parseFloat(header.dataset.lng);
  const prep = parseFloat(header.dataset.prep) || 15;

  const etaEl = document.getElementById('etaDisplay');
  const feeEl = document.getElementById('feeDisplay');

  function haversine(lat1, lon1, lat2, lon2){
    const R=6371;
    const dLat = (lat2-lat1)*Math.PI/180;
    const dLon = (lon2-lon1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
  }

  function formatPrice(n){ 
    try { 
      return Number(n).toLocaleString() + ' MMK'; 
    } catch(e) { 
      return n + ' MMK'; 
    } 
  }

  // SAME TIME FORMATTING AS DASHBOARD.PHP
  function formatTimeRange(minutes) {
    const lower = Math.floor(minutes / 5) * 5;
    const upper = lower + 10;
    return `${lower}-${upper} min`;
  }

  function updateForCoordinates(userLat, userLng){
    if (isNaN(restLat) || isNaN(restLng) || isNaN(userLat) || isNaN(userLng)) {
      // Keep the server-calculated values
      return;
    }
    
    const dist = haversine(userLat, userLng, restLat, restLng);
    
    // SAME FEE CALCULATION AS DASHBOARD.PHP
    let fee = 0;
    if (dist <= 2) {
        fee = 700;
    } else if (dist <= 4) {
        fee = 1400;
    } else if (dist <= 6) {
        fee = 2100;
    } else {
        fee = 2100 + Math.round((dist - 6) * 500);
    }
    
    // SAME TIME CALCULATION AS DASHBOARD.PHP
    const averageSpeedKmh = 20;
    const travelTimeMinutes = (dist / averageSpeedKmh) * 60;
    const totalTimeMinutes = prep + travelTimeMinutes;

    feeEl.textContent = formatPrice(fee);
    etaEl.textContent = formatTimeRange(totalTimeMinutes);

    // Update session storage with new calculated values
    sessionStorage.setItem('deliveryFee', fee);
    sessionStorage.setItem('deliveryFeeDisplay', formatPrice(fee));
  }

  // Only update if user location changes significantly
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => updateForCoordinates(pos.coords.latitude, pos.coords.longitude),
      err => { console.warn('geolocation failed', err); },
      { enableHighAccuracy: true, timeout: 8000 }
    );
  }
})();
</script>
<!-- ---- END dynamic header ---- -->


<div class="page-container">
    <main class="menu-container">
        <!-- Category Tabs -->
        <div class="menu-filter-bar">
          <div class="category-tabs" id="categoryTabs">
              <div class="category-tab active" data-category="all">All</div>
              <?php foreach ($categories as $cat): ?>
                <div class="category-tab" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></div>
              <?php endforeach; ?>
          </div>

          <!-- Search Box -->
          <div class="search-box">
            <input type="text" id="menuSearch" placeholder="Search for restaurants and cuisines...">
          </div>
        </div>
        
        <!-- Menu Grid -->
        <div class="menu-grid" id="menuGrid">
          <?php foreach ($menu_with_options as $item): ?>
            <div class="menu-card <?= ((int)$item['is_available'] === 1 && !$restaurant_closed) ? '' : 'unavailable' ?>" 
                 data-category="<?= htmlspecialchars($item['category']) ?>" 
                 data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>">

              <div class="menu-img">
                <img src="../../assets/images/<?= htmlspecialchars($item['image'] ?? 'default.png') ?>" 
                     alt="<?= htmlspecialchars($item['name']) ?>">
                <?php if ($restaurant_closed): ?>
                  <div class="unavailable-badge">Closed</div>
                <?php elseif ((int)$item['is_available'] !== 1): ?>
                  <div class="unavailable-badge">Unavailable</div>
                <?php endif; ?>

              </div>

              <div class="menu-info">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p><?= htmlspecialchars($item['description']) ?></p>
                <strong><?= number_format($item['price']) ?> MMK</strong>
                <?php if ($restaurant_closed): ?>
                  <button disabled title="Restaurant is closed">Restaurant closed</button>
                <?php elseif ((int)$item['is_available'] === 1): ?>
                  <button onclick="openAddToCartModal(<?= $item['item_id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['price'] ?>, <?= htmlspecialchars(json_encode($item['options']), ENT_QUOTES) ?>)">Add To Cart</button>
                <?php else: ?>
                  <button disabled title="This item is currently unavailable">Not available</button>
                <?php endif; ?>

              </div>

            </div>
          <?php endforeach; ?>
        </div>
    </main>

    <!-- Cart container -->
    <aside id="cart-container" class="cart-container"></aside>
</div>

<!-- NEW MODAL with options -->
<div id="addToCartModal" class="modal-backdrop" aria-hidden="true">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalItemName">
    <button onclick="closeModal()" 
        class="close-modal-btn" 
        type="button"
        style="position:absolute; right:15px; 
               width:50px; height:50px;  top:0px;
               background:url('../../assets/images/cancel.jpg') no-repeat center center; 
               background-size:contain; 
               border:none; cursor:pointer;">
    </button>

    <h3 id="modalItemName">Item Name</h3>
    
    <div id="modalOptionsContainer"></div>
    
    <div class="modal-qty-control">
      <button class="qty-btn" id="modalDecrementBtn" type="button">-</button>
      <span class="qty" id="modalQuantity">1</span>
      <button class="qty-btn" id="modalIncrementBtn" type="button">+</button>
    </div>
    
    <div id="optionError" class="option-error">Please select all required options</div>
    
    <button id="modalAddToCartBtn" class="checkout-btn" type="button">
      Add to Cart - <span id="modalTotalPrice">0</span> MMK
    </button>
  </div>
</div>

<script src="../../assets/js/cart.js"></script>

<!-- Scroll handling for restaurant header -->
<script>
const restaurantHeader = document.getElementById('restaurantHeader');
let lastScrollTop = 0;

window.addEventListener('scroll', function() {
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
  if (scrollTop > lastScrollTop && scrollTop > 100) {
    // Scrolling down - hide header
    if (restaurantHeader) restaurantHeader.classList.add('hidden');
  } else {
    // Scrolling up - show header
    if (restaurantHeader) restaurantHeader.classList.remove('hidden');
  }
  
  lastScrollTop = scrollTop;
});
</script>

<!-- Filtering Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const search = document.getElementById('menuSearch');
  const cards = document.querySelectorAll('#menuGrid .menu-card');
  const tabs = document.querySelectorAll('.category-tab');

  const normalize = s => (s || '').toString().toLowerCase().trim();

  function filter() {
    const query = normalize(search ? search.value : '');
    const activeEl = document.querySelector('.category-tab.active');
    const activeTab = normalize(activeEl ? activeEl.dataset.category : 'all');

    cards.forEach(card => {
      const name = normalize(card.dataset.name);
      const category = normalize(card.dataset.category);

      const matchCategory = (activeTab === 'all' || category === activeTab);
      const matchSearch = (!query || name.includes(query));

      card.style.display = (matchCategory && matchSearch) ? '' : 'none';
    });
  }

  if (search) search.addEventListener('input', filter);

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      filter();
    });
  });

  filter();
});
</script>

<!-- Modal functionality -->
<script>
// DOM references and modal state
const modal = document.getElementById('addToCartModal');
const modalItemName = document.getElementById('modalItemName');
const modalOptionsContainer = document.getElementById('modalOptionsContainer');
const modalQuantity = document.getElementById('modalQuantity');
const modalDecrementBtn = document.getElementById('modalDecrementBtn');
const modalIncrementBtn = document.getElementById('modalIncrementBtn');
const modalTotalPrice = document.getElementById('modalTotalPrice');
const modalAddToCartBtn = document.getElementById('modalAddToCartBtn');
const optionErrorEl = document.getElementById('optionError');

let currentItemId = null;
let currentItemPrice = 0;
let currentQuantity = 1;
let currentItemOptions = [];
let selectedOptions = {};

function openAddToCartModal(id, name, price, options = []) {
    currentItemId = id;
    currentItemPrice = Number(price) || 0;
    currentQuantity = 1;
    currentItemOptions = Array.isArray(options) ? options : [];
    selectedOptions = {};

    if (modalItemName) modalItemName.textContent = name;
    renderOptions();
    updateModalDisplay();
    if (modal) modal.classList.add('visible');
}

function closeModal() {
    if (modal) modal.classList.remove('visible');
    // optional: reset modal content
    // modalOptionsContainer.innerHTML = '';
    // selectedOptions = {};
}

if (modalDecrementBtn) {
    modalDecrementBtn.addEventListener('click', () => {
        if (currentQuantity > 1) {
            currentQuantity--;
            updateModalDisplay();
        }
    });
}
if (modalIncrementBtn) {
    modalIncrementBtn.addEventListener('click', () => {
        currentQuantity++;
        updateModalDisplay();
    });
}

function renderOptions() {
    const container = modalOptionsContainer;
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!currentItemOptions || currentItemOptions.length === 0) {
        // no options for this item
        return;
    }
    
    currentItemOptions.forEach(option => {
        const optionGroup = document.createElement('div');
        optionGroup.className = 'option-group';
        
        const title = document.createElement('span');
        title.className = 'option-title';
        title.textContent = option.option_name || option.name || 'Option';
        if (parseInt(option.is_required) === 1) {
            title.innerHTML += ' <span class="option-required">*</span>';
        }
        
        optionGroup.appendChild(title);
        
        if (option.option_type === 'single_select' || option.option_type === 'radio') {
            renderRadioOptions(optionGroup, option);
        } else {
            renderCheckboxOptions(optionGroup, option);
        }
        
        container.appendChild(optionGroup);
    });
}

function renderRadioOptions(container, option) {
    (option.values || []).forEach(value => {
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = `option_${option.option_id}`;
        radio.value = value.value_id;
        radio.id = `opt_${option.option_id}_${value.value_id}`;
        radio.addEventListener('change', () => {
            selectedOptions[option.option_id] = radio.checked ? [value] : [];
            updateModalDisplay();
        });
        
        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = value.value_name;
        
        const priceSpan = document.createElement('span');
        priceSpan.className = 'option-price';
        priceSpan.textContent = (Number(value.price_modifier) > 0) ? `+${Number(value.price_modifier).toLocaleString()} MMK` : '';
        
        optionItem.appendChild(radio);
        optionItem.appendChild(label);
        optionItem.appendChild(priceSpan);
        container.appendChild(optionItem);
    });
}

function renderCheckboxOptions(container, option) {
    (option.values || []).forEach(value => {
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = `option_${option.option_id}[]`;
        checkbox.value = value.value_id;
        checkbox.id = `opt_${option.option_id}_${value.value_id}`;
        checkbox.addEventListener('change', () => {
            if (!selectedOptions[option.option_id]) selectedOptions[option.option_id] = [];
            if (checkbox.checked) {
                selectedOptions[option.option_id].push(value);
            } else {
                selectedOptions[option.option_id] = selectedOptions[option.option_id].filter(item => item.value_id != value.value_id);
            }
            updateModalDisplay();
        });
        
        const label = document.createElement('label');
        label.htmlFor = checkbox.id;
        label.textContent = value.value_name;
        
        const priceSpan = document.createElement('span');
        priceSpan.className = 'option-price';
        priceSpan.textContent = (Number(value.price_modifier) > 0) ? `+${Number(value.price_modifier).toLocaleString()} MMK` : '';
        
        optionItem.appendChild(checkbox);
        optionItem.appendChild(label);
        optionItem.appendChild(priceSpan);
        container.appendChild(optionItem);
    });
}

function validateOptions() {
    let isValid = true;
    
    (currentItemOptions || []).forEach(option => {
        if (parseInt(option.is_required) === 1) {
            if (!selectedOptions[option.option_id] || selectedOptions[option.option_id].length === 0) {
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function calculateOptionPrice() {
    let optionTotal = 0;
    
    Object.values(selectedOptions).forEach(optionArray => {
        (optionArray || []).forEach(option => {
            optionTotal += Number(option.price_modifier) || 0;
        });
    });
    
    return optionTotal;
}

function updateModalDisplay() {
    if (modalQuantity) modalQuantity.textContent = currentQuantity;
    
    const optionPrice = calculateOptionPrice();
    const totalPrice = (currentItemPrice + optionPrice) * currentQuantity;
    
    if (modalTotalPrice) modalTotalPrice.textContent = Number(totalPrice).toLocaleString();
    if (modalDecrementBtn) modalDecrementBtn.disabled = currentQuantity <= 1;
    
    if (optionErrorEl) optionErrorEl.style.display = validateOptions() ? 'none' : 'none';
}

// Hook add to cart
if (modalAddToCartBtn) {
    modalAddToCartBtn.addEventListener('click', () => {
        if (!currentItemId) return;
        if (!validateOptions()) {
            if (optionErrorEl) optionErrorEl.style.display = 'block';
            return;
        }
        if (typeof updateCart === 'function') {
            // Get current delivery fee from the header
            const feeDisplay = document.getElementById('feeDisplay');
            let deliveryFee = null;
            
            if (feeDisplay && feeDisplay.textContent) {
                const feeText = feeDisplay.textContent.trim();
                if (feeText !== 'Free delivery' && feeText !== 'Fee varies') {
                    const feeMatch = feeText.match(/(\d+[\d,]*)/);
                    if (feeMatch) {
                        deliveryFee = parseInt(feeMatch[1].replace(/,/g, ''));
                    }
                }
            }
            
            updateCart('add_with_qty', currentItemId, currentQuantity, selectedOptions, deliveryFee);
        } else {
            console.warn('updateCart() not found. Ensure cart.js is loaded and defines updateCart.');
        }
        closeModal();
    });
}
 setInterval(function() {
    // Only refresh if user is not interacting with the page
    if (!document.hidden) {
        location.reload();
    }
}, 30000); 
</script>

<script>

  // In restaurants.php, add this function and modify the cart initialization
function initializeCartForPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const restaurantId = urlParams.get('id');
    
    if (restaurantId) {
        // Check if we have cart data in session for this restaurant
        fetch('check_cart_session.php?restaurant_id=' + restaurantId)
            .then(response => response.json())
            .then(data => {
                if (data.has_cart) {
                    // Cart exists, update the badge
                    updateCartBadge(data.item_count);
                } else {
                    // No cart, badge should be 0
                    updateCartBadge(0);
                }
            })
            .catch(error => {
                console.error('Error checking cart:', error);
            });
    }
}

function updateCartBadge(count) {
    const badge = document.getElementById('cart-badge');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('has-items', count > 0);
    }
}

// Call this on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCartForPage();
    
    // Your existing initialization code...
    const urlParams = new URLSearchParams(window.location.search);
    const restaurantId = urlParams.get('id');
    
    if (restaurantId && typeof initCartForRestaurant === 'function') {
        initCartForRestaurant(restaurantId);
    }
    
    if (typeof displayCartItems === 'function') {
        displayCartItems();
    }
});
// Initialize cart for current restaurant
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const restaurantId = urlParams.get('id');
    
    if (restaurantId && typeof initCartForRestaurant === 'function') {
        initCartForRestaurant(restaurantId);
    }
    
    // Load cart items for this restaurant
    if (typeof displayCartItems === 'function') {
        displayCartItems();
    }
});
</script>
<!-- Floating cart button -->
<button id="floating-cart-btn" title="Open cart" aria-label="Open cart" onclick="toggleCart()" type="button">
  <svg id="cart-svg" width="24" height="24" viewBox="0 0 24 24">
    <path fill="currentColor" d="M7 18c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm10 0c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zM7.16 14l.84-2h8.99c.54 0 1.02-.35 1.17-.86l1.98-6.14a1 1 0 0 0-.96-1.3H5.21l-.94-2.5A1 1 0 0 0 3.33 1H1v2h1.89l3.6 9.6c.15.4.53.66.95.66h9.53v-2H8.53l-.12-.26L7.16 14z"/>
  </svg>
  <span id="cart-badge">0</span>
</button>

</body>
</html>
<?php include('../../includes/footer.php'); ?>