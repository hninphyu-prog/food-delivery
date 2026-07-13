<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all saved carts for this user - ONLY ACTIVE CARTS (not ordered)
$stmt = $pdo->prepare("
    SELECT sc.*, r.name as restaurant_name, r.logo, r.status as restaurant_status
    FROM saved_carts sc 
    JOIN restaurants r ON sc.restaurant_id = r.restaurant_id 
    WHERE sc.user_id = ? 
      AND (sc.status = 'active' OR sc.status IS NULL)
      AND (sc.order_id IS NULL OR sc.order_id = 0)
    ORDER BY sc.updated_at DESC
");
$stmt->execute([$user_id]);
$saved_carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current active cart ID if exists
$current_cart_id = null;
if (isset($_SESSION['cart']['saved_cart_id'])) {
    $current_cart_id = $_SESSION['cart']['saved_cart_id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Saved Carts - Food&Me</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Keep all your existing CSS styles */
        .cart-history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .page-header p {
            color: #666;
            font-size: 16px;
        }

        .carts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 50px;
            margin-bottom: 40px;
        }

        .cart-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .cart-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .cart-card-header {
            background: #e2e2e2ff;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .restaurant-logo {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .restaurant-info {
            flex: 1;
        }

        .restaurant-name {
            font-weight: 600;
            color: #333;
            margin: 0 0 4px 0;
            font-size: 16px;
        }

        .cart-name {
            color: #666;
            font-size: 14px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cart-name i {
            color: #ff6600;
        }

        .cart-card-body {
            padding: 20px;
        }

        .cart-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 18px;
            color: #666;
            margin-bottom: 4px;
        }

        .stat-value {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .cart-items-preview {
            margin-bottom: 20px;
        }

        .preview-title {
            font-size: 16px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .item-preview {
            font-size: 13px;
            color: #555;
            padding: 4px 0;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            justify-content: space-between;
        }

        .item-preview:last-child {
            border-bottom: none;
        }

        .item-name {
            flex: 1;
        }

        .item-qty {
            color: #ff6600;
            font-size: 16px;
            margin-left: 8px;
        }

        /* NEW STYLES FOR OPTION VALUES */
        .option-values-container {
            margin-left: 10px;
            padding-left: 10px;
            border-left: 2px solid #ff6600;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .option-value-item {
            font-size: 14px;
            color: #666;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
        }

        .option-value-item:last-child {
            margin-bottom: 0;
        }

        .option-dot {
            color: #ff6600;
            margin-right: 5px;
            font-size: 14px;
        }

        .option-name {
            font-weight: 500;
            margin-right: 5px;
        }

        .option-value {
            color: #333;
        }

        .option-price {
            color: #28a745;
            font-weight: 600;
            margin-left: auto;
            font-size: 11px;
        }

        .cart-timestamps {
            background: #e2e2e2ff;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
            color: #777;
            margin-bottom: 15px;
        }

        .timestamp {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .timestamp:last-child {
            margin-bottom: 0;
        }

        .cart-actions {
            display: grid;
            grid-template-rows: 1fr 1fr;
            gap: 10px;
        }

        .cart-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 14px;
        }

        .continue-btn {
            background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
            color: white;
        }

        .continue-btn:hover {
            background: linear-gradient(135deg, #e55a00 0%, #ff6600 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 102, 0, 0.2);
        }

        .delete-btn {
            background: #f8f9fa;
            color: #dc3545;
            border: 1px solid #ddd;
        }

        .delete-btn:hover {
            background: #dc3545;
            color: white;
        }

        .rename-btn {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
            grid-column: span 2;
        }

        .rename-btn:hover {
            background: #e9ecef;
        }

        .restaurant-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        .status-open {
            background: #d4edda;
            color: #155724;
        }

        .status-closed {
            background: #f8d7da;
            color: #721c24;
        }

        .no-carts-message {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .no-carts-icon {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .no-carts-message h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .no-carts-message p {
            color: #888;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .back-to-dashboard {
            display: inline-block;
            padding: 10px 20px;
            background: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .back-to-dashboard:hover {

            color: black;
            transform: translateY(-1px);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-title {
            margin-top: 0;
            color: #333;
            margin-bottom: 15px;
        }

        .modal-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-cancel {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }

        .modal-save {
            background: #ff6600;
            color: white;
        }

        .modal-cancel:hover {
            background: #e9ecef;
        }

        .modal-save:hover {
            background: #e55a00;
        }

        @media (max-width: 768px) {
            .carts-grid {
                grid-template-columns: 1fr;
            }

            .cart-history-container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <?php include 'dashboard_header.php'; ?>

    <div class="cart-history-container">

        <?php if (empty($saved_carts)): ?>
            <div class="no-carts-message">
                <div class="no-carts-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>No saved carts yet</h3>
                <p>Start adding items to your cart and they'll be saved here automatically.</p>
                <a href="dashboard.php" class="back-to-dashboard">
                    <i class="fas fa-arrow-left"></i> Back to Restaurants
                </a>
            </div>
        <?php else: ?>
            <div class="carts-grid">
                <?php foreach ($saved_carts as $cart):
                    $cart_data = json_decode($cart['cart_data'], true);
                    $items = $cart_data['items'] ?? [];
                    $restaurant_status = $cart['restaurant_status'];
                    $is_active = ($cart['saved_cart_id'] == $current_cart_id);
                    $can_continue = $restaurant_status === 'active';
                ?>
                    <div class="cart-card <?= $is_active ? 'active' : '' ?>">
                        <div class="cart-card-header">
                            <img src="../../assets/images/<?= htmlspecialchars($cart['logo'] ?? 'default_restaurant.png') ?>"
                                alt="<?= htmlspecialchars($cart['restaurant_name']) ?>"
                                class="restaurant-logo">
                            <div class="restaurant-info">
                                <h3 class="restaurant-name" data-restaurant-id="<?= $cart['restaurant_id'] ?>">
                                    <?= htmlspecialchars($cart['restaurant_name']) ?>
                                    <span class="restaurant-status <?= $restaurant_status === 'active' ? 'status-open' : 'status-closed' ?>">
                                        <?= $restaurant_status === 'active' ? 'Open' : 'Closed' ?>
                                    </span>
                                </h3>
                                <p class="cart-name">
                                    <i class="fas fa-shopping-cart"></i>
                                    <?= htmlspecialchars($cart['cart_name']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="cart-card-body">
                            <div class="cart-stats">
                                <div class="stat-item">
                                    <div class="stat-label">Items</div>
                                    <div class="stat-value"><?= $cart['item_count'] ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Total</div>
                                    <div class="stat-value"><?= number_format($cart['total_amount']) ?> MMK</div>
                                </div>

                            </div>

                            <?php if (!empty($items)): ?>
                                <div class="cart-items-preview">
                                    <div class="preview-title">Items in cart:</div>
                                    <?php
                                    $count = 0;
                                    foreach ($items as $key => $item):
                                        if ($count++ >= 3) break; // Show only first 3 items

                                        // Safely get item details
                                        $itemName = $item['name'] ?? 'Unknown Item';
                                        $itemQty = $item['qty'] ?? $item['quantity'] ?? 1;
                                        $itemOptions = $item['options'] ?? [];
                                    ?>
                                        <div class="item-preview">
                                            <span class="item-name">
                                                <?= htmlspecialchars($itemName) ?>
                                                <span class="item-qty">× <?= $itemQty ?></span>
                                            </span>
                                        </div>

                                        <!-- FIXED: Single option display section -->
                                        <?php if (!empty($itemOptions) && is_array($itemOptions)): ?>
                                            <div class="option-values-container">
                                                <?php foreach ($itemOptions as $option):
                                                    // Skip if option is empty
                                                    if (empty($option)) continue;

                                                    $optionName = $option['option_name'] ?? 'Option';
                                                    $valueName = $option['value_name'] ?? '';
                                                    $priceModifier = $option['price_modifier'] ?? 0;

                                                    // Skip if this is an empty option
                                                    if (empty($valueName) && empty($optionName)) continue;
                                                ?>
                                                    <div class="option-value-item">
                                                        <span class="option-dot">•</span>
                                                        <span class="option-name"><?= htmlspecialchars($optionName) ?>:</span>
                                                        <span class="option-value"><?= htmlspecialchars($valueName) ?></span>
                                                        <?php if ($priceModifier > 0): ?>
                                                            <span class="option-price">+<?= number_format($priceModifier) ?> MMK</span>
                                                        <?php elseif ($priceModifier == 0): ?>
                                                            <span class="option-price" style="color: #6c757d;">included</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <?php if (count($items) > 3): ?>
                                        <div class="item-preview">
                                            <span class="item-name">+<?= count($items) - 3 ?> more items</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="cart-timestamps">
                                <div class="timestamp">
                                    <span>Created:</span>
                                    <span><?= date('M d, Y', strtotime($cart['created_at'])) ?></span>
                                </div>
                                <div class="timestamp">
                                    <span>Last updated:</span>
                                    <span><?= date('M d, H:i', strtotime($cart['updated_at'])) ?></span>
                                </div>
                            </div>

                            <div class="cart-actions">
                                <?php if ($can_continue): ?>
                                    <button class="cart-btn continue-btn"
                                        onclick="loadCart(<?= $cart['saved_cart_id'] ?>)">
                                        <i class="fas fa-plus"></i> Add More Items
                                    </button>
                                <?php else: ?>
                                    <button class="cart-btn continue-btn"
                                        onclick="alert('This restaurant is currently closed. Please try again later.')"
                                        style="opacity: 0.6; cursor: not-allowed;">
                                        <i class="fas fa-ban"></i> Restaurant Closed
                                    </button>
                                <?php endif; ?>

                                <button class="cart-btn delete-btn"
                                    onclick="deleteCart(<?= $cart['saved_cart_id'] ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>

                                <button class="cart-btn rename-btn"
                                    onclick="renameCart(<?= $cart['saved_cart_id'] ?>, '<?= htmlspecialchars(addslashes($cart['cart_name'])) ?>')">
                                    <i class="fas fa-edit"></i> Rename Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>

    <!-- Rename Modal -->
    <div id="renameModal" class="modal-overlay">
        <div class="modal-content">
            <h3 class="modal-title">Rename Cart</h3>
            <input type="text" id="newCartName" class="modal-input" placeholder="Enter new cart name">
            <input type="hidden" id="cartIdToRename">
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-cancel" onclick="closeRenameModal()">Cancel</button>
                <button type="button" class="modal-btn modal-save" onclick="saveCartName()">Save</button>
            </div>
        </div>
    </div>


    <script>
        // Function to update cart count badge
        function updateCartCountBadge() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartBadge = document.querySelector('.cart-count-badge');
                    const cartIconLink = document.querySelector('.cart-icon-link');

                    if (data.count > 0) {
                        if (cartBadge) {
                            cartBadge.textContent = data.count;
                        } else if (cartIconLink) {
                            // Create badge if it doesn't exist
                            const badge = document.createElement('span');
                            badge.className = 'cart-count-badge';
                            badge.textContent = data.count;
                            cartIconLink.appendChild(badge);
                        }
                    } else {
                        // Remove badge if count is 0
                        if (cartBadge) {
                            cartBadge.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching cart count:', error);
                });
        }

        function loadCart(cartId) {
            // Get restaurant ID from the card
            const cartCard = event.target.closest('.cart-card');
            const restaurantNameElement = cartCard.querySelector('.restaurant-name');
            const restaurantId = restaurantNameElement ? restaurantNameElement.dataset.restaurantId : null;

            if (!restaurantId) {
                alert('Cannot determine restaurant. Please contact support.');
                return;
            }

            // Show loading indicator
            const continueBtn = event.target.closest('.continue-btn');
            const originalHtml = continueBtn.innerHTML;
            continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            continueBtn.disabled = true;

            fetch('load_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `saved_cart_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Store restaurant ID for restaurant.php to detect
                        localStorage.setItem('loading_cart_from_history', 'true');
                        localStorage.setItem('saved_cart_restaurant_id', data.restaurant_id);
                        localStorage.setItem('saved_cart_id', cartId);

                        if (data.restaurant_closed) {
                            alert('This restaurant is currently closed. You can view your cart but cannot add new items.');
                        }

                        // Update cart count badge before redirecting
                        updateCartCountBadge();

                        // Redirect to restaurant page
                        setTimeout(() => {
                            window.location.href = `restaurants.php?id=${data.restaurant_id}`;
                        }, 500);
                    } else {
                        alert('Error: ' + (data.message || 'Failed to load cart'));
                        continueBtn.innerHTML = originalHtml;
                        continueBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load cart. Please try again.');
                    continueBtn.innerHTML = originalHtml;
                    continueBtn.disabled = false;
                });
        }

        function deleteCart(cartId) {
            if (!confirm('Delete this saved cart? This action cannot be undone.')) {
                return;
            }

            const deleteBtn = event.target.closest('.delete-btn');
            const originalHtml = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            deleteBtn.disabled = true;

            fetch('delete_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `saved_cart_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count badge before reloading
                        updateCartCountBadge();

                        // Remove the cart card from the DOM without reloading the page
                        const cartCard = deleteBtn.closest('.cart-card');
                        if (cartCard) {
                            cartCard.style.opacity = '0.5';
                            cartCard.style.pointerEvents = 'none';

                            // Fade out and remove the card
                            setTimeout(() => {
                                cartCard.style.transition = 'all 0.3s ease';
                                cartCard.style.transform = 'translateX(100%)';
                                cartCard.style.opacity = '0';

                                setTimeout(() => {
                                    cartCard.remove();

                                    // Check if no carts left
                                    const remainingCarts = document.querySelectorAll('.cart-card');
                                    if (remainingCarts.length === 0) {
                                        // Show "no carts" message
                                        location.reload(); // Or show empty state dynamically
                                    }
                                }, 300);
                            }, 200);
                        } else {
                            // Fallback: reload the page
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete cart'));
                        deleteBtn.innerHTML = originalHtml;
                        deleteBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete cart. Please try again.');
                    deleteBtn.innerHTML = originalHtml;
                    deleteBtn.disabled = false;
                });
        }

        function renameCart(cartId, currentName) {
            document.getElementById('cartIdToRename').value = cartId;
            document.getElementById('newCartName').value = currentName;
            document.getElementById('renameModal').style.display = 'flex';
            document.getElementById('newCartName').focus();
        }

        function closeRenameModal() {
            document.getElementById('renameModal').style.display = 'none';
        }

        function saveCartName() {
            const cartId = document.getElementById('cartIdToRename').value;
            const newName = document.getElementById('newCartName').value.trim();

            if (!newName) {
                alert('Please enter a cart name');
                return;
            }

            const saveBtn = document.querySelector('.modal-save');
            const originalHtml = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            saveBtn.disabled = true;

            fetch('rename_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `saved_cart_id=${cartId}&cart_name=${encodeURIComponent(newName)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to rename cart'));
                        saveBtn.innerHTML = originalHtml;
                        saveBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to rename cart. Please try again.');
                    saveBtn.innerHTML = originalHtml;
                    saveBtn.disabled = false;
                });
        }

        // Close modal when clicking outside
        document.getElementById('renameModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRenameModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRenameModal();
            }
        });

        // Initialize cart count badge on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCountBadge();
        });
    </script>
</body>

</html>