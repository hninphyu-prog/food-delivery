<?php
// Ensure session is started and includes are correct
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../../login.php");
    exit;
}

// Get restaurant_id from session or from restaurants table
$restaurant_id = $_SESSION['restaurant_id'] ?? null;
$user_id = $_SESSION['user_id'];

// If restaurant_id is not in session, get it from restaurants table
if (!$restaurant_id) {
    $restaurant_stmt = $pdo->prepare("SELECT restaurant_id FROM restaurants WHERE user_id = ?");
    $restaurant_stmt->execute([$user_id]);
    $restaurant = $restaurant_stmt->fetch(PDO::FETCH_ASSOC);

    if ($restaurant) {
        $restaurant_id = $restaurant['restaurant_id'];
        $_SESSION['restaurant_id'] = $restaurant_id;
    } else {
        header("Location: ../../login.php");
        exit;
    }
}

// Fetch orders for the current restaurant with timeout tracking
$stmt = $pdo->prepare("
    SELECT
        o.order_id,
        o.total_amount as total,
        o.order_status,
        o.created_at,
        u.name as customer_name,
        u.phone as customer_phone,
        d.delivery_boy_id,
        d.status as delivery_status,
        d.created_at as assigned_time,
        rider.name as rider_name,
        rider.phone as rider_phone,
        TIMESTAMPDIFF(MINUTE, d.created_at, NOW()) as minutes_with_rider,
        CASE 
            WHEN d.status = 'assigned' AND TIMESTAMPDIFF(MINUTE, d.created_at, NOW()) >= 12 THEN 'timeout_warning'
            WHEN d.status = 'assigned' AND TIMESTAMPDIFF(MINUTE, d.created_at, NOW()) >= 10 THEN 'timeout_soon'
            WHEN d.status = 'assigned' THEN 'assigned'
            ELSE 'no_rider'
        END as rider_status
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN delivery d ON o.order_id = d.order_id
    LEFT JOIN users rider ON d.delivery_boy_id = rider.user_id
WHERE o.restaurant_id = ? AND o.order_status IN ('pending', 'accepted', 'preparing', 'ready')
    ORDER BY 
        CASE 
            WHEN o.order_status = 'pending' THEN 1
            WHEN o.order_status = 'accepted' THEN 2
            WHEN o.order_status = 'preparing' THEN 3
            WHEN o.order_status = 'ready' THEN 4
            ELSE 5
        END,
        o.created_at DESC
");
$stmt->execute([$restaurant_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="main-container">
    <div class="dashboard-section">
        <h2 class="section-title">Restaurant Orders Dashboard</h2>

        <!-- Timeout Information Box -->
        <div class="info-box">
            <h4>Rider Timeout System</h4>
            <p>Riders have <strong>15 minutes</strong> to pick up orders after acceptance.</p>
            <ul>
                <li>🟢 < 10 mins: Normal</li>
                <li>🟡 10-12 mins: Warning (Timeout soon)</li>
                <li>🔴 12-15 mins: Critical (About to timeout)</li>
                <li>❌ 15+ mins: Reassign to other riders</li>
            </ul>
        </div>

        <div class="orders-list">
            <?php if (empty($orders)): ?>
                <p>No orders at this time.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card" data-order-id="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <div class="order-header">
                            <h3>Order #<?php echo htmlspecialchars($order['order_id']); ?></h3>
                            <span class="order-status <?php echo htmlspecialchars($order['order_status']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['order_status']))); ?>
                            </span>
                        </div>
                        <div class="order-body">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            <p><strong>Total:</strong> <?php echo htmlspecialchars(number_format($order['total'], 2)); ?> MMK</p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars(date('h:i A, M d', strtotime($order['created_at']))); ?></p>

                            <!-- Enhanced Rider Information with Timeout Status -->
                            <div class="rider-info <?php echo htmlspecialchars($order['rider_status']); ?>">
                                <?php if ($order['rider_name']): ?>
                                    <p><strong>Assigned Rider:</strong> <?php echo htmlspecialchars($order['rider_name']); ?></p>
                                    <p><strong>Rider Phone:</strong> <?php echo htmlspecialchars($order['rider_phone']); ?></p>
                                    <p><strong>Delivery Status:</strong>
                                        <span class="delivery-status <?php echo htmlspecialchars($order['delivery_status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($order['delivery_status'])); ?>
                                        </span>
                                    </p>

                                    <!-- Timeout Information -->
                                    <?php if ($order['rider_status'] === 'timeout_warning'): ?>
                                        <div class="timeout-alert critical">
                                            CRITICAL: Rider has <?php echo 15 - $order['minutes_with_rider']; ?> minutes left to pick up!
                                        </div>
                                    <?php elseif ($order['rider_status'] === 'timeout_soon'): ?>
                                        <div class="timeout-alert warning">
                                            Warning: Rider has <?php echo 15 - $order['minutes_with_rider']; ?> minutes left to pick up
                                        </div>
                                    <?php elseif ($order['rider_status'] === 'assigned'): ?>
                                        <div class="timeout-alert normal">
                                            Normal: Rider has <?php echo 15 - $order['minutes_with_rider']; ?> minutes left
                                        </div>
                                    <?php endif; ?>

                                    <p class="time-assigned">
                                        <small>Assigned: <?php echo htmlspecialchars(date('h:i A', strtotime($order['assigned_time']))); ?>
                                            (<?php echo $order['minutes_with_rider']; ?> mins ago)</small>
                                    </p>

                                <?php else: ?>
                                    <p><strong>Rider Status:</strong> <span class="not-assigned">Waiting for rider assignment</span></p>
                                <?php endif; ?>
                            </div>

                            <div class="order-items compact">
                                <h4>Order Items:</h4>
                                <div class="items-container">
                                    <?php
                                    $item_stmt = $pdo->prepare("
            SELECT oi.quantity, mi.name, oi.price, oi.menu_options
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            WHERE oi.order_id = ?
        ");
                                    $item_stmt->execute([$order['order_id']]);
                                    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($items as $item):
                                        $menu_options = [];
                                        if (!empty($item['menu_options'])) {
                                            $menu_options = json_decode($item['menu_options'], true);
                                        }

                                        $item_total = $item['price'] * $item['quantity'];
                                    ?>
                                        <div class="order-item-compact">
                                            <!-- Main Item -->
                                            <div class="main-line">
                                                <span class="qty"><?php echo htmlspecialchars($item['quantity']); ?>x</span>
                                                <span class="name"><?php echo htmlspecialchars($item['name']); ?></span>
                                                <span class="price"><?php echo htmlspecialchars(number_format($item_total, 2)); ?> MMK</span>
                                            </div>

                                            <!-- Options -->
                                            <?php if (!empty($menu_options) && is_array($menu_options)): ?>
                                                <div class="options-container">
                                                    <?php foreach ($menu_options as $option): ?>
                                                        <?php
                                                        $option_price = $option['extra_price'] ?? 0;
                                                        $option_total = $option_price * $item['quantity'];
                                                        ?>
                                                        <div class="option-line">
                                                            <span class="option-text">
                                                                • <?php echo htmlspecialchars($option['option_name'] ?? ''); ?>
                                                                   <?php if (isset($option['value_name'])): ?>
                                                                    : <div class="name"><?php echo htmlspecialchars($option['value_name']); ?></div>
                                                                <?php endif; ?>
                                                            </span>
                                                            <?php if ($option_price > 0): ?>
                                                                <span class="option-amount">+<?php echo htmlspecialchars(number_format($option_total, 2)); ?> MMK</span>
                                                            <?php else: ?>
                                                                <span class="option-amount free">(included)</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="order-actions">
                            <?php if ($order['order_status'] == 'pending'): ?>
                                <button class="btn btn-accept" data-order-id="<?php echo htmlspecialchars($order['order_id']); ?>">Accept Order</button>
                                <button class="btn btn-cancel" data-order-id="<?php echo htmlspecialchars($order['order_id']); ?>">Cancel Order</button>
                            <?php elseif ($order['order_status'] == 'accepted' && $order['rider_status'] === 'timeout_warning'): ?>
                                <div class="urgent-actions">
                                    <p class="urgent-message">⚠️ Rider is taking too long!</p>
                                    <button class="btn btn-warning" onclick="forceReassign(<?php echo $order['order_id']; ?>)">
                                        🔄 Force Reassign to New Rider
                                    </button>
                                </div>

                            <?php elseif ($order['order_status'] == 'preparing' && $order['rider_status'] === 'timeout_warning'): ?>
                                <div class="urgent-actions">
                                    <p class="urgent-message">⚠️ Rider is taking too long!</p>
                                    <button class="btn btn-warning" onclick="forceReassign(<?php echo $order['order_id']; ?>)">
                                        🔄 Force Reassign to New Rider
                                    </button>
                                </div>
                            <?php elseif ($order['order_status'] == 'ready' && $order['rider_status'] === 'timeout_warning'): ?>
                                <div class="urgent-actions">
                                    <p class="urgent-message">⚠️ Rider is taking too long!</p>
                                    <button class="btn btn-warning" onclick="forceReassign(<?php echo $order['order_id']; ?>)">
                                        🔄 Force Reassign to New Rider
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="status-message">
                                    <?php
                                    $statusMessages = [
                                        'accepted' => 'Order accepted - manage in Order Manager',
                                        'preparing' => 'Order being prepared - manage in Order Manager',
                                        'ready' => 'Ready for pickup',
                                        'on_the_way' => 'Rider is on the way'
                                    ];
                                    echo $statusMessages[$order['order_status']] ?? 'Order in progress';
                                    ?>
                                </span>
                                <br>
                                <small><a href="index.php?page=orders" class="manage-link">Go to Order Manager →</a></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .name{
        color: rgb(255,102,0);
    }
    .compact .items-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
    }

    .order-item-compact {
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #dee2e6;
    }

    .order-item-compact:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .main-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .options-container {
        margin-left: 25px;
    }

    /* Blue option names and improved styling */
    .option-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 4px 0;
        padding: 6px 10px;
        background: rgba(59, 130, 246, 0.05);
        border-radius: 8px;
        
    }

    .blue-option {
        color: #1e40af;
        font-weight: 600;
        font-size: 13px;
    }

    .option-value {
        color: #374151;
        font-size: 13px;
        font-weight: 500;
    }

    .highlight-price {
        color: #059669;
        font-weight: 700;
        background: rgba(5, 150, 105, 0.1);
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 12px;
    }

    .item-options {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin: 8px 0 0 20px;
    }

    .option-text {
        flex: 1;
        
    }

    .option-amount {
        font-weight: 600;
        color: #28a745;
        min-width: 80px;
        text-align: right;
    }

    .option-amount.free {
        color: #6c757d;
        font-style: italic;
    }

    .qty {
        min-width: 25px;
        color: #495057;
    }

    .name {
        flex: 1;
        margin: 0 8px;
    }

    .price {
        font-weight: bold;
        color: #e74c3c;
        min-width: 90px;
        text-align: right;
    }

    .order-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .order-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .order-status.pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .order-status.accepted {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .order-status.preparing {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .order-status.ready {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .order-status.on_the_way {
        background: #cce5ff;
        color: #004085;
        border: 1px solid #b8daff;
    }

    /* Rider Info Styles */
    .rider-info {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        margin: 10px 0;
       
    }

    .rider-info.timeout_warning {
        border-left-color: #dc3545;
        background: #f8d7da;
    }

    .rider-info.timeout_soon {
        border-left-color: #ffc107;
        background: #fff3cd;
    }

    .rider-info.assigned {
        border-left-color: #28a745;
        background: #d4edda;
    }

    .timeout-alert {
        padding: 8px 12px;
        border-radius: 4px;
        margin: 8px 0;
        font-weight: bold;
        font-size: 14px;
    }

    .timeout-alert.critical {
        background: #dc3545;
        color: white;
        animation: pulse 2s infinite;
    }

    .timeout-alert.warning {
        background: #ffc107;
        color: #856404;
    }

    .timeout-alert.normal {
        background: #28a745;
        color: white;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }

        100% {
            opacity: 1;
        }
    }

    .delivery-status {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .delivery-status.assigned {
        background: #d1ecf1;
        color: #0c5460;
    }

    .delivery-status.picked {
        background: #cce5ff;
        color: #004085;
    }

    .delivery-status.delivered {
        background: #d4edda;
        color: #155724;
    }

    .not-assigned {
        color: #6c757d;
        font-style: italic;
    }

    .time-assigned {
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
    }

    .order-actions {
        margin-top: 15px;
        text-align: center;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-right: 10px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-accept {
        background: #28a745;
        color: white;
    }

    .btn-cancel {
        background: #dc3545;
        color: white;
    }

    .btn-warning {
        background: #ffc107;
        color: #856404;
    }

    .status-message {
        padding: 10px 15px;
        border-radius: 5px;
        font-weight: bold;
        background: #e9ecef;
        color: #495057;
        display: inline-block;
        margin-bottom: 10px;
    }

    .manage-link {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }

    .manage-link:hover {
        text-decoration: underline;
    }

    .order-items ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .order-items li {
        padding: 5px 0;
        border-bottom: 1px solid #f8f9fa;
        display: flex;
        justify-content: space-between;
    }

    .order-items li:last-child {
        border-bottom: none;
    }

    .info-box {
        background: #e7f3ff;
        border: 1px solid #b8daff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .info-box h4 {
        margin-top: 0;
        color: #004085;
    }

    .info-box ul {
        margin-bottom: 0;
    }

    .urgent-actions {
        text-align: center;
    }

    .urgent-message {
        color: #dc3545;
        font-weight: bold;
        margin-bottom: 10px;
    }

    /* Cancel Reason Modal Styles */
    .cr-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
    }

    .cr-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .4);
    }

    .cr-modal-content {
        position: relative;
        z-index: 10001;
        max-width: 520px;
        margin: 10vh auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, .2);
        overflow: hidden;
    }

    .cr-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }

    .cr-close {
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
    }

    .cr-modal-body {
        padding: 16px;
    }

    .cr-reasons {
        display: grid;
        gap: 8px;
        margin-top: 8px;
    }

    .cr-modal-footer {
        padding: 12px 16px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #eee;
    }

    .cr-error {
        color: #dc3545;
        margin-top: 8px;
        font-weight: bold;
    }
</style>
<script>
    let pendingCancelOrderId = null;

    document.addEventListener('DOMContentLoaded', () => {
        const ordersList = document.querySelector('.orders-list');

        // Handle Accept/Cancel button clicks
        // ordersList.addEventListener('click', (e) => {
        //     if (e.target.classList.contains('btn-accept')) {
        //         const orderId = e.target.dataset.orderId;
        //         updateOrderStatus(orderId, 'accepted');
        //         return;
        //     }

        //     if (e.target.classList.contains('btn-cancel')) {
        //         const orderId = e.target.dataset.orderId;
        //         openCancelModal(orderId);
        //     }
        // });

        function updateOrderStatus(orderId, status, cancellationReason = null) {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);
            if (status === 'canceled' && cancellationReason) {
                formData.append('cancellation_reason', cancellationReason);
            }

            fetch('update_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
                        if (card) {
                            const badge = card.querySelector('.order-status');
                            if (badge) {
                                badge.className = `order-status ${status}`;
                                const badgeTextMap = {
                                    'pending': 'Pending',
                                    'accepted': 'Accepted',
                                    'preparing': 'Preparing',
                                    'ready': 'Ready',
                                    'on_the_way': 'On the Way',
                                    'delivered': 'Delivered',
                                    'canceled': 'Canceled'
                                };
                                badge.textContent = badgeTextMap[status] || status;
                            }

                            const actions = card.querySelector('.order-actions');
                            if (actions) {
                                if (status === 'canceled') {
                                    // Remove card on cancel for immediate feedback
                                    card.parentNode && card.parentNode.removeChild(card);
                                } else if (status === 'accepted') {
                                    actions.innerHTML = `
                                <span class="status-message">Order accepted - manage in Order Manager</span>
                                <br>
                                <small><a href="index.php?page=orders" class="manage-link">Go to Order Manager →</a></small>
                            `;
                                } else if (status === 'preparing') {
                                    actions.innerHTML = `
                                <span class="status-message">Order being prepared - manage in Order Manager</span>
                                <br>
                                <small><a href="index.php?page=orders" class="manage-link">Go to Order Manager →</a></small>
                            `;
                                } else if (status === 'ready') {
                                    actions.innerHTML = `
                                <span class="status-message">Ready for pickup</span>
                                <br>
                                <small><a href="index.php?page=orders" class="manage-link">Go to Order Manager →</a></small>
                            `;
                                } else if (status === 'on_the_way') {
                                    actions.innerHTML = `
                                <span class="status-message">Rider is on the way</span>
                                <br>
                                <small><a href="index.php?page=orders" class="manage-link">Go to Order Manager →</a></small>
                            `;
                                } else if (status === 'delivered') {
                                    actions.innerHTML = `
                                <span class="status-message">Delivered</span>
                            `;
                                }
                            }
                        }
                        // Close cancel modal if open
                        if (typeof closeCancelModal === 'function') closeCancelModal();
                    } else {
                        alert('Error: ' + data.message);
                        console.error('Update error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error updating order status:', error);
                    alert('An error occurred. Please try again.');
                });
        }

        // Force reassign function for urgent cases
        window.forceReassign = function(orderId) {
            if (confirm('Are you sure you want to force reassign this order to a new rider? The current rider will lose this assignment.')) {
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('action', 'force_reassign');

                fetch('../../api/force_reassign.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Order has been reassigned to new riders!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Reassign error:', error);
                        alert('An error occurred. Please try again.');
                    });
            }
        };

        // Auto-refresh every 30 seconds to show real-time timeout updates
        setInterval(function() {
    // Only refresh if user is not interacting with the page
    if (!document.hidden) {
        location.reload();
    }
}, 10000); 
    });

    // Real-time timeout countdown (client-side)
    function updateTimeoutCountdown() {
        document.querySelectorAll('.timeout-alert').forEach(alert => {
            const timeLeft = alert.textContent.match(/(\d+) minutes? left/);
            if (timeLeft) {
                const minutes = parseInt(timeLeft[1]);
                if (minutes > 0) {
                    // Update countdown every minute
                    alert.textContent = alert.textContent.replace(
                        /(\d+) minutes? left/,
                        (minutes - 1) + ' minute' + (minutes - 1 === 1 ? '' : 's') + ' left'
                    );
                }
            }
        });
    }

    // Update countdown every minute
    setInterval(updateTimeoutCountdown, 60000);
    window.fetchOrders = function() {
        location.reload();
    }
</script>