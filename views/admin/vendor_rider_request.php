<?php
session_start();
include "includes/header.php";
require_once '../../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: adminlogin.php");
    exit;
}

// Initialize alert
$alert = null;

/*----------------------------------------
 | ACCEPT BUTTON HANDLER
----------------------------------------*/
if (isset($_POST['accept'])) {
    $user_id = $_POST['user_id'];

    // Fetch role first
    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify user
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
        if ($stmt->execute([$user_id])) {

            // If rider, also insert into riders table
            if ($user['role'] === 'delivery') {
                $checkRider = $pdo->prepare("SELECT COUNT(*) FROM riders WHERE user_id = ?");
                $checkRider->execute([$user_id]);
                $exists = $checkRider->fetchColumn();

                if ($exists == 0) {
                    $rider_id = uniqid('RID');
                    $status = 'active';
                    $insertRider = $pdo->prepare("INSERT INTO riders (rider_id, user_id, status) VALUES (?, ?, ?)");
                    $insertRider->execute([$rider_id, $user_id, $status]);
                }
            }

            // If vendor, update restaurant status
            if ($user['role'] === 'vendor') {
                $updateRestaurant = $pdo->prepare("UPDATE restaurants SET status = 'active' WHERE user_id = ?");
                $updateRestaurant->execute([$user_id]);
            }

            $alert = [
                'type' => 'success',
                'message' => "✅ User ID $user_id has been verified successfully!"
            ];
        } else {
            $alert = [
                'type' => 'error',
                'message' => "❌ Failed to verify user. Please try again later."
            ];
        }
    }
}

/*----------------------------------------
 | REJECT BUTTON HANDLER
----------------------------------------*/
if (isset($_POST['reject'])) {
    $user_id = $_POST['user_id'];
    
    // Fetch user info before deleting
    $stmt = $pdo->prepare("SELECT role, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $pdo->beginTransaction();
        
        try {
            // Delete from riders table if it's a rider
            if ($user['role'] === 'delivery') {
                $stmt = $pdo->prepare("DELETE FROM riders WHERE user_id = ?");
                $stmt->execute([$user_id]);
            }
            
            // Delete from restaurants table if it's a vendor
            if ($user['role'] === 'vendor') {
                // First get restaurant info to delete logo file
                $restoStmt = $pdo->prepare("SELECT logo FROM restaurants WHERE user_id = ?");
                $restoStmt->execute([$user_id]);
                $restaurant = $restoStmt->fetch();
                
                // Delete restaurant
                $stmt = $pdo->prepare("DELETE FROM restaurants WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Delete restaurant logo file if exists
                if ($restaurant && $restaurant['logo'] && file_exists("../../assets/images/" . $restaurant['logo'])) {
                    unlink("../../assets/images/" . $restaurant['logo']);
                }
            }
            
            // Delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Delete from request_notifications
            $stmt = $pdo->prepare("DELETE FROM request_notifications WHERE message LIKE ?");
            $stmt->execute(['%"new_user_id":' . $user_id . '%']);
            
            $pdo->commit();
            
            $alert = [
                'type' => 'warning',
                'message' => "❌ User ID $user_id has been rejected and removed from the system."
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $alert = [
                'type' => 'error',
                'message' => "❌ Failed to reject user: " . $e->getMessage()
            ];
        }
    }
}

/*----------------------------------------
 | ACTIVATE / INACTIVATE BUTTON HANDLER
 ----------------------------------------*/
if (isset($_POST['set_inactive']) || isset($_POST['set_active'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    // Determine new status (text form)
    $newStatus = isset($_POST['set_active']) ? 'active' : 'inactive';

    if ($role === 'vendor') {
        // Update restaurants table
        $stmt = $pdo->prepare("UPDATE restaurants SET status = ? WHERE user_id = ?");
        $success = $stmt->execute([$newStatus, $user_id]);
    } elseif ($role === 'delivery') {
        // Update riders table (text status)
        $stmt = $pdo->prepare("UPDATE riders SET status = ? WHERE user_id = ?");
        $success = $stmt->execute([$newStatus, $user_id]);
    }

    if (!empty($success)) {
        $alert = [
            'type' => $newStatus === 'active' ? 'success' : 'warning',
            'message' => $newStatus === 'active'
                ? "✅ User ID $user_id has been activated."
                : "⚠️ User ID $user_id has been deactivated."
        ];
    } else {
        $alert = [
            'type' => 'error',
            'message' => "❌ Failed to update status for User ID $user_id."
        ];
    }
}

/*----------------------------------------
 | FETCH USERS
 ----------------------------------------*/
$pending_vendors = $pdo->query("
    SELECT u.user_id, u.name, u.email, u.created_at, r.logo, r.name as restaurant_name, r.cuisine_type, r.address
    FROM users u
    LEFT JOIN restaurants r ON u.user_id = r.user_id
    WHERE u.is_verified = 0 AND u.role = 'vendor'
")->fetchAll();

$pending_riders = $pdo->query("
    SELECT user_id, name, email, created_at 
    FROM users 
    WHERE is_verified = 0 AND role = 'delivery'
")->fetchAll();

// Verified vendors with restaurant status
$verified_vendors = $pdo->query("
    SELECT u.user_id, u.name, u.email, u.role, r.status, r.logo, r.name as restaurant_name
    FROM users u
    JOIN restaurants r ON u.user_id = r.user_id
    WHERE u.is_verified = 1 AND u.role = 'vendor'
")->fetchAll(PDO::FETCH_ASSOC);

// Verified riders with rider status
$verified_riders = $pdo->query("
    SELECT u.user_id, u.name, u.email, u.role, rd.status 
    FROM users u
    JOIN riders rd ON u.user_id = rd.user_id
    WHERE u.is_verified = 1 AND u.role = 'delivery'
")->fetchAll(PDO::FETCH_ASSOC);

$all_partners = array_merge($verified_vendors, $verified_riders);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin: Vendor & Rider Requests</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #fdf6f0;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #ff6a00;
            margin-bottom: 15px;
            border-bottom: 2px solid #ff6a00;
            padding-bottom: 5px;
        }

        /* Alert styles */
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: 500;
            animation: fadeIn 0.5s;
        }
        .alert.success { background-color: #d4edda; color: #155724; }
        .alert.error   { background-color: #f8d7da; color: #721c24; }
        .alert.warning { background-color: #fff3cd; color: #856404; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Cards */
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 15px;
            flex: 1 1 300px;
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-5px); }
        .card p { margin: 5px 0; }
        .card form { margin-top: 10px; display: flex; gap: 10px; }
        .card button {
            flex: 1;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
            font-weight: 600;
        }
        
        .accept-btn {
            background: #28a745;
            color: #fff;
        }
        .accept-btn:hover {
            background: #218838;
        }
        
        .reject-btn {
            background: #dc3545;
            color: #fff;
        }
        .reject-btn:hover {
            background: #c82333;
        }
        
        /* Restaurant logo in cards */
        .restaurant-logo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
            margin: 5px 0;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .restaurant-logo:hover {
            transform: scale(1.05);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        thead {
            background: #ff6a00;
            color: #fff;
        }
        tbody tr:nth-child(even) { background: #fdf6f0; }
        
        /* Table logo */
        .table-logo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .table-logo:hover {
            transform: scale(1.1);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 90%;
          
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            margin: 5% auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
            animation: zoomIn 0.3s;
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 35px;
            color: white;
            width: 50px;
            height: 50px;
            text-align: center;
            background-color: red;
            border-radius: 50px;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
            z-index: 1001;
        }
        
        .close-modal:hover {
            color: red;
            background-color: white;
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .image-caption {
            text-align: center;
            color: white;
            padding: 10px;
            font-size: 14px;
            background: rgba(0,0,0,0.7);
            border-radius: 0 0 10px 10px;
            margin-top: -5px;
        }

        .btn-success {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        
        /* View larger text */
        .view-larger {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: block;
        }
        
        /* Button confirmation for reject */
        .confirm-reject {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
        }
        .confirm-reject p {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .confirm-buttons {
            display: flex;
            gap: 10px;
        }
        .confirm-yes {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .confirm-no {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="main-content">

    <!-- Alert display -->
    <?php if($alert): ?>
        <div class="alert <?= $alert['type'] ?>">
            <?= $alert['message'] ?>
        </div>
    <?php endif; ?>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modalImage">
        <div id="modalCaption" class="image-caption"></div>
    </div>

    <h2>Pending Vendor Requests</h2>
    <?php if(count($pending_vendors) > 0): ?>
        <div class="card-container">
        <?php foreach($pending_vendors as $user): ?>
            <div class="card">
                <p><strong>Vendor Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Request Date:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
                <?php if($user['restaurant_name']): ?>
                    <p><strong>Restaurant:</strong> <?= htmlspecialchars($user['restaurant_name']) ?></p>
                    <p><strong>Cuisine:</strong> <?= htmlspecialchars($user['cuisine_type']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
                <?php else: ?>
                    <p style="color:orange;">No restaurant info.</p>
                <?php endif; ?>
                
                <!-- Restaurant Logo Display -->
                <?php if($user['logo']): ?>
                    <div style="margin: 10px 0;">
                        <strong>Restaurant Logo:</strong><br>
                        <img src="../../assets/images/<?= htmlspecialchars($user['logo']) ?>" 
                             alt="Restaurant Logo" 
                             class="restaurant-logo"
                             onclick="openModal(this)"
                             data-caption="<?= htmlspecialchars($user['restaurant_name'] ?: 'Restaurant Logo') ?>">
                        <span class="view-larger">Click to view larger</span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="action-form">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <button type="submit" name="accept" class="accept-btn">Accept</button>
                    <button type="button" onclick="showRejectConfirmation(this)" class="reject-btn">Reject</button>
                </form>
                
                <!-- Reject Confirmation -->
                <div class="confirm-reject" id="confirm-<?= $user['user_id'] ?>">
                    <p>Are you sure you want to reject this application? This action cannot be undone.</p>
                    <div class="confirm-buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <button type="submit" name="reject" class="confirm-yes">Yes, Reject</button>
                        </form>
                        <button type="button" onclick="hideRejectConfirmation(<?= $user['user_id'] ?>)" class="confirm-no">Cancel</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No pending vendor requests.</p>
    <?php endif; ?>

    <h2>Pending Rider Requests</h2>
    <?php if(count($pending_riders) > 0): ?>
        <div class="card-container">
        <?php foreach($pending_riders as $user): ?>
            <div class="card">
                <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Request Date:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
                
                <form method="POST" class="action-form">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <button type="submit" name="accept" class="accept-btn">Accept</button>
                    <button type="button" onclick="showRejectConfirmation(this)" class="reject-btn">Reject</button>
                </form>
                
                <!-- Reject Confirmation -->
                <div class="confirm-reject" id="confirm-<?= $user['user_id'] ?>">
                    <p>Are you sure you want to reject this application? This action cannot be undone.</p>
                    <div class="confirm-buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <button type="submit" name="reject" class="confirm-yes">Yes, Reject</button>
                        </form>
                        <button type="button" onclick="hideRejectConfirmation(<?= $user['user_id'] ?>)" class="confirm-no">Cancel</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No pending rider requests.</p>
    <?php endif; ?>

    
</div>

<script>
    // Get the modal
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    const captionText = document.getElementById("modalCaption");
    const closeModal = document.querySelector(".close-modal");

    // Function to open modal
    function openModal(imgElement) {
        modal.style.display = "block";
        modalImg.src = imgElement.src;
        captionText.innerHTML = imgElement.getAttribute("data-caption") || "Restaurant Logo";
    }

    // Close modal when clicking X
    closeModal.onclick = function() {
        modal.style.display = "none";
    }

    // Close modal when clicking outside the image
    modal.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === "block") {
            modal.style.display = "none";
        }
    });

    // Reject confirmation functions
    function showRejectConfirmation(button) {
        const form = button.closest('.action-form');
        const userId = form.querySelector('input[name="user_id"]').value;
        const confirmDiv = document.getElementById('confirm-' + userId);
        
        // Hide the original buttons
        form.style.display = 'none';
        // Show confirmation
        confirmDiv.style.display = 'block';
    }
    
    function hideRejectConfirmation(userId) {
        const confirmDiv = document.getElementById('confirm-' + userId);
        const form = confirmDiv.previousElementSibling;
        
        // Show the original buttons
        form.style.display = 'flex';
        // Hide confirmation
        confirmDiv.style.display = 'none';
    }

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }, 5000);
        });
    });
</script>
</body>
</html>