<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
require_once '../../config/db.php';

$order_info = null;
if ($order_id > 0) {
    // include restaurant_id so we can lookup logo
    $stmt = $pdo->prepare("SELECT order_status, cancellation_reason, restaurant_id FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order_info = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get temporary summary data from the session, then clear it
$order_summary = $_SESSION['temp_order_summary'] ?? null;
unset($_SESSION['temp_order_summary']); 

// Ensure $order_summary is an array so we can attach logo/restaurant_name safely
if (!is_array($order_summary)) $order_summary = [];

// Resolve restaurant_id: prefer summary, then order_info, then query orders table as fallback
$restaurantId = null;
if (!empty($order_summary['restaurant_id'])) {
    $restaurantId = (int)$order_summary['restaurant_id'];
} elseif (!empty($order_info['restaurant_id'])) {
    $restaurantId = (int)$order_info['restaurant_id'];
} elseif ($order_id > 0) {
    $stmtRid = $pdo->prepare("SELECT restaurant_id FROM orders WHERE order_id = ? LIMIT 1");
    $stmtRid->execute([$order_id]);
    $rrow = $stmtRid->fetch(PDO::FETCH_ASSOC);
    if ($rrow && !empty($rrow['restaurant_id'])) {
        $restaurantId = (int)$rrow['restaurant_id'];
    }
}

// Default logo path (absolute)
$defaultLogo = '/foodandme/assets/images/default_restaurant.png';

// If restaurantId found, query the restaurants table for logo (and optionally name)
if (!empty($restaurantId)) {
    try {
        $stmtR = $pdo->prepare("SELECT name, logo FROM restaurants WHERE restaurant_id = ? LIMIT 1");
        $stmtR->execute([$restaurantId]);
        $res = $stmtR->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            if (empty($order_summary['restaurant_name'])) {
                $order_summary['restaurant_name'] = $res['name'] ?? 'Restaurant';
            }
            $logoFile = trim($res['logo'] ?? '');
            if ($logoFile !== '') {
                // If DB stores a full URL or absolute path, use it; otherwise treat as filename under /foodandme/assets/images/
                if (preg_match('#^https?://#i', $logoFile) || strpos($logoFile, '/') === 0) {
                    $order_summary['logo'] = $logoFile;
                } else {
                    $order_summary['logo'] = '/foodandme/assets/images/' . $logoFile;
                }
            } else {
                $order_summary['logo'] = $defaultLogo;
            }
        } else {
            // restaurant row not found
            if (empty($order_summary['restaurant_name'])) $order_summary['restaurant_name'] = 'Restaurant';
            if (empty($order_summary['logo'])) $order_summary['logo'] = $defaultLogo;
        }
    } catch (Exception $e) {
        if (empty($order_summary['restaurant_name'])) $order_summary['restaurant_name'] = 'Restaurant';
        if (empty($order_summary['logo'])) $order_summary['logo'] = $defaultLogo;
    }
} else {
    // No restaurant id available; ensure defaults exist
    if (empty($order_summary['restaurant_name'])) $order_summary['restaurant_name'] = 'Restaurant';
    if (empty($order_summary['logo'])) $order_summary['logo'] = $defaultLogo;
}

// Prepare summary data for JavaScript injection
$js_summary_data = json_encode($order_summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Placed Successfully</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .cancellation-reason {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e74c3c;
            background-color: #fce7e7;
            color: #c0392b;
            border-radius: 8px;
        }
        body {
            padding-bottom: 500px; 
        }
    </style>
</head>
<body>
<div class="container" style="text-align: center; padding-top: 50px;">
    <?php if ($order_id > 0 && $order_info): ?>
        <h1>Thank You! </h1>
        <h2>Your order has been placed successfully.</h2>
        <p>Order ID: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
        <p>You can track its progress using the status bar at the bottom of the screen.</p>
    <?php else: ?>
        <h1>Order Error</h1>
        <p>No valid order ID was provided or order not found.</p>
    <?php endif; ?> 

    <br>
    <a href="dashboard.php" class="checkout-btn" style="display: inline-block; width: auto; padding: 10px 20px;">Back to Restaurants</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const orderId = <?= $order_id ?: 0 ?>;
    const summaryData = <?= $js_summary_data ?? 'null' ?>;

    if (orderId > 0) {
        try {
            // Retrieve list of tracked objects
            let activeOrders = JSON.parse(localStorage.getItem('active_orders') || '[]');
            
            let orderToTrack = {
                order_id: orderId,
                // Use server-provided summary (now includes restaurant_name and logo path)
                summary: summaryData || {
                    restaurant_name: "Loading...",
                    item_count: "...",
                    total_amount: "...",
                    currency: "MMK",
                    logo: "/foodandme/assets/images/default_restaurant.png"
                }
            };
            
            let index = activeOrders.findIndex(item => Number(item.order_id) === Number(orderId));

            if (index === -1) {
                // New order: Add to the list
                activeOrders.push(orderToTrack);
            } else {
                // Existing order (in case of refresh): Update summary if available
                if (summaryData) {
                    activeOrders[index].summary = summaryData;
                }
            }
            
            // Save the updated list back to Local Storage
            localStorage.setItem('active_orders', JSON.stringify(activeOrders));
            console.log(`Order ${orderId} added/updated in active tracking list with summary.`);

            // Optionally open the orders modal immediately:
            // if (window.notifyOrderPlaced) window.notifyOrderPlaced(true);

        } catch (e) {
            console.error('Error handling Local Storage for active orders:', e);
        }
    }
});
</script>

<?php 
include 'track_status_bar.php'; 
?>

</body>
</html>
