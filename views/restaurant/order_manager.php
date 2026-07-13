<?php
// Ensure session is started and includes are correct
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

// Fetch orders for the current restaurant with specific statuses
$restaurant_id = $_SESSION['restaurant_id'] ?? null;
if (!$restaurant_id) {
    die("No restaurant selected.");
}

$stmt = $pdo->prepare("
    SELECT
        o.order_id,
        o.total_amount,
        o.order_status,
        o.created_at,
        u.name as customer_name,
        u.phone as customer_phone
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.restaurant_id = ? AND o.order_status IN ('pending', 'accepted', 'preparing', 'ready')
    ORDER BY o.created_at DESC
");
$stmt->execute([$restaurant_id]);
$restaurant_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
    body {
        background: rgba(255, 255, 255, 0.9);
        background-attachment: fixed;
        background-image: ('../../../images/howitwork.jpg') no-repeat center center;
    }

    .order-actions {
        display: flex;
        gap: 10px;
        margin: 20px;
    }

    .order-details {
        margin: 20px;
    }

    .btn {
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        color: white;
    }

    .btn-preparing {
        background-color: #f39c12;
    }

    /* Orange */
    .btn-ready {
        background-color: #2ecc71;
    }

    /* Green */
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8em;
        text-transform: capitalize;
        font-weight: bold;
        color: white;
    }

    .status-pending {
        background-color: #3498db;
    }

    .status-accepted {
        background-color: #27ae60;
    }

    .status-preparing {
        background-color: #f1c40f;
    }

    .status-ready {
        background-color: #16a085;
    }

    .status-on_the_way {
        background-color: #e67e22;
    }

    .status-delivered {
        background-color: #2c3e50;
    }

    .status-canceled {
        background-color: #e74c3c;
    }

    /* Add styles for menu options display */
    .order-items-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .order-item {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .item-main {
        display: flex;
        justify-content: space-between;
        font-weight: bold;
    }

    .item-options {
        margin-left: 20px;
        margin-top: 5px;
    }

    .option-line {
        display: flex;
        justify-content: space-between;
        font-size: 0.9em;
        color: #666;
        margin: 2px 0;
    }

    .option-name {
       
        font-weight: bold;
    }
    .option-value-name {
        color: rgb(255,102,0);
        font-weight: bold;
    }
    .option-price {
        color: #059669;
        font-weight: 600;
    }

    .notifications-container {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 300px;
        z-index: 1000;
    }

    .notification-card {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;

        opacity: 0;
        transform: translateY(20px);
        animation: fadeIn 0.5s forwards;
    }

    .notification-card.success {
        border-left-color: #2ecc71;
        background: #d4edda;
        color: #155724;
    }

    .notification-card.error {
        border-left-color: #e74c3c;
        background: #f8d7da;
        color: #721c24;
    }

    .notification-card.warning {
        border-left-color: #f39c12;
        background: #fff3cd;
        color: #856404;
    }

    .notification-card.info {
        border-left-color: #3498db;
        background: #d1ecf1;
        color: #0c5460;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="main-container">
    <div class="orders-list">
        <?php if (empty($restaurant_orders)): ?>
            <p>No orders to manage at this time.</p>
        <?php else: ?>
            <?php foreach ($restaurant_orders as $order): ?>
                <div class="order-card" data-order-id="<?php echo htmlspecialchars($order['order_id']); ?>">
                    <div class="order-header">
                        <span class="order-id">#Order_ID<?php echo htmlspecialchars($order['order_id']); ?></span>
                        <span class="order-date"><?php echo htmlspecialchars($order['created_at']); ?></span>
                        <span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>">
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </span>
                    </div>
                    <div class="order-details">
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?> - <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        <div class="order-items-list">
                            <h4>Items:</h4>
                            <ul>
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
                                    <li class="order-item">
                                        <div class="item-main">
                                            <span><?php echo htmlspecialchars($item['quantity']); ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                                            <span><?php echo htmlspecialchars(number_format($item_total, 2)); ?> MMK</span>
                                        </div>
                                        <?php if (!empty($menu_options) && is_array($menu_options)): ?>
                                            <div class="item-options">
                                                <?php foreach ($menu_options as $option): ?>
                                                    <?php
                                                    $option_price = $option['extra_price'] ?? 0;
                                                    $option_total = $option_price * $item['quantity'];
                                                    ?>
                                                    <div class="option-line">
                                                        <span class="option-name">
                                                            • <?php echo htmlspecialchars($option['option_name'] ?? ''); ?>
                                                            <?php if (isset($option['value_name'])): ?>
                                                                : <div class="option-value-name"><?php echo htmlspecialchars($option['value_name']); ?></div>
                                                            <?php endif; ?>
                                                        </span>
                                                        <?php if ($option_price > 0): ?>
                                                            <span class="option-price">+<?php echo htmlspecialchars(number_format($option_total, 2)); ?> MMK</span>
                                                        <?php else: ?>
                                                            <span class="option-price">(included)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="order-actions">
                        <?php if ($order['order_status'] === 'accepted'): ?>
                            <button class="btn btn-preparing btn-status" data-status="preparing">Start Preparing</button>
                        <?php elseif ($order['order_status'] === 'preparing'): ?>
                            <button class="btn btn-ready btn-status" data-status="ready">Ready for Pickup</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <audio id="notificationSound" preload="auto">
        <source src="../../assets/sound/notisound.mp3" type="audio/mpeg">
    </audio>
</div>

<div class="notifications-container" id="notifications-container"></div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.btn-status').forEach(button => {
            button.addEventListener('click', async (e) => {
                const orderCard = e.target.closest('.order-card');
                const orderId = orderCard.dataset.orderId;
                const newStatus = e.target.dataset.status;

                try {
                    const formData = new FormData();
                    formData.append('order_id', orderId);
                    formData.append('status', newStatus);

                    const response = await fetch('update_order.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update status badge
                        const statusSpan = orderCard.querySelector('.status-badge');
                        statusSpan.textContent = newStatus;
                        statusSpan.className = `status-badge status-${newStatus}`;

                        // Update actions based on new status
                        const orderActionsDiv = orderCard.querySelector('.order-actions');

                        if (newStatus === 'preparing') {
                            orderActionsDiv.innerHTML = `<button class="btn btn-ready btn-status" data-status="ready">Ready for Pickup</button>`;
                        } else if (newStatus === 'ready') {
                            orderActionsDiv.innerHTML = `<span style="color: green; font-weight: bold;">Ready for rider pickup</span>`;
                        }

                        // Show success message
                        showNotification('Order status updated successfully!', 'success');

                        // If you want to refresh the dashboard as well, call this function
                        if (typeof window.fetchOrders === 'function') {
                            window.fetchOrders();
                        }
                    } else {
                        showNotification('Error: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error('Failed to update status:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
        });

        // Simple notification function
        // Replace the entire showNotification function with this:
        function showNotification(message, type = 'info') {
            const container = document.getElementById('notifications-container');
            const notification = document.createElement('div');
            notification.className = `notification-card ${type}`;
            notification.innerHTML = `
        <strong>${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
        <p style="margin: 5px 0 0 0; font-size: 14px;">${message}</p>
    `;

            container.appendChild(notification);

            // Play notification sound
            playNotificationSound();

            // Automatically remove notification after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 500);
                }
            }, 5000);
        }

        // Function to play notification sound
        function playNotificationSound() {
            const sound = document.getElementById('notificationSound');
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(error => {
                    console.log('Audio play failed:', error);
                });
            }
        }
    });
</script>