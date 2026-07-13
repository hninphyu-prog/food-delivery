<?php
// Fetch user's orders with review status
$orderStmt = $pdo->prepare("
    SELECT 
        o.order_id, 
        o.total_amount, 
        o.order_status, 
        o.created_at, 
        o.is_reviewed,
        r.name AS restaurant_name,
        r.restaurant_id
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$orderStmt->execute([$_SESSION['user_id']]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.order-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
    background-color: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.order-header h3 {
    color: rgb(255,102,0);
    margin: 0;
    font-size: 1.3em;
}

.order-status {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.9em;
    font-weight: bold;
    color: white;
}

.status-delivered { background-color: #28a745; }
.status-pending { background-color: #ffc107; color: #000; }
.status-preparing { background-color: #17a2b8; }
.status-cancelled { background-color: #dc3545; }
.status-default { background-color: #6c757d; }

.order-details {
    margin-bottom: 10px;
    color: #333;
}

.order-details strong {
    color: #555;
}

.order-items {
    margin-bottom: 15px;
}

.order-items strong {
    color: #555;
    display: block;
    margin-bottom: 5px;
}

.order-items ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
}

.order-items li {
    background-color: #f8f9fa;
    margin: 3px 0;
    padding: 5px 10px;
    border-radius: 4px;
    border-left: 2px solid rgb(255,102,0);
}

.order-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.reorder-btn, .review-btn {
    display: inline-block;
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.reorder-btn {
    background-color: rgb(255,102,0);
    color: white;
}

.reorder-btn:hover {
    background-color: rgb(230,92,0);
}

.review-btn {
    background-color: #28a745;
    color: white;
}

.review-btn:hover {
    background-color: #218838;
}

.review-btn:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.reviewed-badge {
    background-color: #17a2b8;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    font-size: 14px;
}

h2 {
    color: rgb(255,102,0);
    border-bottom: 2px solid rgb(255,102,0);
    padding-bottom: 5px;
}

p {
    color: #666;
    font-style: italic;
}

/* Review Modal Styles */
#reviewModal {
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
    transition: all 0.3s ease;
    padding: 20px;
    box-sizing: border-box;
}

#reviewModal.visible {
    opacity: 1;
    visibility: visible;
}

.review-modal-content {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    position: relative;
    padding: 30px;
    transform: translateY(30px) scale(0.95);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

#reviewModal.visible .review-modal-content {
    transform: translateY(0) scale(1);
}

.review-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.review-modal-title {
    font-size: 24px;
    color: #333;
    font-weight: 700;
    margin: 0;
}

.close-review-modal-btn {
    background: url('../../assets/images/cancel.jpg') no-repeat center center;
    background-size: contain;
    width: 70px;
    height: 70px;
    border: none;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.close-review-modal-btn:hover {
    transform: rotate(90deg);
}

.rating-section {
    margin-bottom: 20px;
    text-align: center;
}

.rating-stars {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 15px 0;
}

.rating-star {
    font-size: 32px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
}

.rating-star:hover,
.rating-star.active {
    color: #ff6600;
}

.rating-text {
    color: #666;
    font-size: 14px;
    margin-top: 5px;
}

.review-textarea {
    width: 100%;
    min-height: 120px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    resize: vertical;
    font-family: inherit;
    font-size: 14px;
    margin-bottom: 20px;
    box-sizing: border-box;
}

.review-textarea:focus {
    outline: none;
    border-color: #ff6600;
    box-shadow: 0 0 0 2px rgba(255, 102, 0, 0.1);
}

.submit-review-btn {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-review-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
}

.submit-review-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.review-success {
    text-align: center;
    padding: 20px;
    color: #28a745;
}

.review-success i {
    font-size: 48px;
    margin-bottom: 15px;
}
</style>

<h2>Orders & Reviews</h2>

<?php if (empty($orders)): ?>
    <p>You haven't placed any orders yet.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <h3><?= htmlspecialchars($order['restaurant_name']) ?></h3>
                <span class="order-status status-<?= htmlspecialchars($order['order_status']) ?>">
                    <?= htmlspecialchars(ucfirst($order['order_status'])) ?>
                </span>
            </div>
            <div class="order-details">
                <strong>Order Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?><br>
                <strong>Total:</strong> <?= number_format($order['total_amount']) ?> MMK
            </div>
            <div class="order-items">
                <strong>Items:</strong>
                <ul>
                    <?php
                    // Fetch items for this specific order
                    $itemStmt = $pdo->prepare("
                        SELECT oi.quantity, mi.name AS item_name
                        FROM order_items oi
                        JOIN menu_items mi ON oi.item_id = mi.item_id
                        WHERE oi.order_id = ?
                    ");
                    $itemStmt->execute([$order['order_id']]);
                    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($items as $item) {
                        echo '<li>' . htmlspecialchars($item['quantity']) . ' x ' . htmlspecialchars($item['item_name']) . '</li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="order-actions">
                <a href="restaurants.php?id=<?= $order['restaurant_id'] ?>" class="reorder-btn">Reorder</a>
                
                <?php if ($order['order_status'] === 'delivered'): ?>
                    <?php if ($order['is_reviewed']): ?>
                        <span class="reviewed-badge">✓ Reviewed</span>
                    <?php else: ?>
                        <button class="review-btn" 
                                onclick="openReviewModal(<?= $order['order_id'] ?>, <?= $order['restaurant_id'] ?>, '<?= htmlspecialchars($order['restaurant_name']) ?>')">
                            Write Review
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Review Modal -->
<div id="reviewModal" class="modal-backdrop" aria-hidden="true">
    <div class="review-modal-content" role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
        <div class="review-modal-header">
            <h3 class="review-modal-title" id="reviewModalTitle">Write a Review</h3>
            <button onclick="closeReviewModal()" class="close-review-modal-btn" type="button"></button>
        </div>
        
        <div id="reviewForm">
            <div class="rating-section">
                <label><strong>How would you rate your experience?</strong></label>
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="rating-star" data-rating="<?= $i ?>" onclick="setRating(<?= $i ?>)">★</span>
                    <?php endfor; ?>
                </div>
                <div class="rating-text" id="ratingText">Tap a star to rate</div>
            </div>
            
            <div>
                <label><strong>Your Review (optional)</strong></label>
                <textarea class="review-textarea" id="reviewComment" placeholder="Share your experience with this restaurant..."></textarea>
            </div>
            
            <button class="submit-review-btn" onclick="submitReview()" id="submitReviewBtn">Submit Review</button>
        </div>
        
        <div id="reviewSuccess" style="display: none;">
            <div class="review-success">
                <i class="fas fa-check-circle"></i>
                <h3>Thank You!</h3>
                <p>Your review has been submitted successfully.</p>
                <button class="submit-review-btn" onclick="closeReviewModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;
let currentRestaurantId = null;
let currentRating = 0;
const ratingDescriptions = {
    1: "Poor",
    2: "Fair", 
    3: "Good",
    4: "Very Good",
    5: "Excellent"
};

function openReviewModal(orderId, restaurantId, restaurantName) {
    currentOrderId = orderId;
    currentRestaurantId = restaurantId;
    currentRating = 0;
    
    // Reset form
    document.getElementById('reviewModalTitle').textContent = `Review: ${restaurantName}`;
    document.getElementById('reviewComment').value = '';
    document.getElementById('ratingText').textContent = 'Tap a star to rate';
    
    // Reset stars
    document.querySelectorAll('.rating-star').forEach(star => {
        star.classList.remove('active');
    });
    
    // Show form, hide success
    document.getElementById('reviewForm').style.display = 'block';
    document.getElementById('reviewSuccess').style.display = 'none';
    
    // Show modal
    document.getElementById('reviewModal').classList.add('visible');
    document.body.style.overflow = 'hidden';
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('visible');
    document.body.style.overflow = '';
}

function setRating(rating) {
    currentRating = rating;
    
    // Update stars visually
    document.querySelectorAll('.rating-star').forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
    
    // Update rating text
    document.getElementById('ratingText').textContent = ratingDescriptions[rating] || 'Tap a star to rate';
}

function submitReview() {
    if (currentRating === 0) {
        alert('Please select a rating before submitting.');
        return;
    }
    
    const comment = document.getElementById('reviewComment').value.trim();
    const submitBtn = document.getElementById('submitReviewBtn');
    
    // Disable button to prevent multiple submissions
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    // Create form data
    const formData = new FormData();
    formData.append('order_id', currentOrderId);
    formData.append('restaurant_id', currentRestaurantId);
    formData.append('rating', currentRating);
    formData.append('comment', comment);
    formData.append('action', 'submit_review');
    
    // Submit via AJAX
    fetch('review_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            document.getElementById('reviewForm').style.display = 'none';
            document.getElementById('reviewSuccess').style.display = 'block';
            
            // Reload the page after a delay to show the updated review status
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert('Error: ' + (data.message || 'Failed to submit review'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Review';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your review.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Review';
    });
}

// Close modal when clicking outside
document.getElementById('reviewModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeReviewModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReviewModal();
    }
});
</script>
