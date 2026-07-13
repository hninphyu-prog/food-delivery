<?php
// rider_payout_status.php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../../config/db.php";

$riderId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? '';

if (!$riderId || $userRole !== 'delivery') {
    header("Location: login.php");
    exit();
}

// Handle payout actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payoutId = $_POST['payout_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    error_log("Rider payout action: action={$action}, payout_id={$payoutId}, rider_id={$riderId}");
    
    if ($payoutId && in_array($action, ['confirm', 'reject'])) {
        try {
            if ($action === 'confirm') {
                // First check if payout exists and is pending
                $checkStmt = $pdo->prepare("
                    SELECT id, status 
                    FROM rider_payouts 
                    WHERE id = ? 
                    AND rider_id = ?
                ");
                $checkStmt->execute([$payoutId, $riderId]);
                $payout = $checkStmt->fetch();
                
                error_log("Confirm payout check: " . print_r($payout, true));
                
                if (!$payout) {
                    $_SESSION['error_message'] = "Payout not found.";
                } elseif ($payout['status'] !== 'pending') {
                    $_SESSION['error_message'] = "Payout status is already '{$payout['status']}'.";
                } elseif ($payout['rider_confirmed_at']) {
                    $_SESSION['error_message'] = "Payout already confirmed.";
                } elseif ($payout['rider_rejected_at']) {
                    $_SESSION['error_message'] = "Payout already rejected.";
                } else {
                    // Proceed with confirmation
                    $stmt = $pdo->prepare("
                        UPDATE rider_payouts 
                        SET rider_confirmed_at = NOW(),
                            status = 'completed',
                            confirmed_at = NOW(),
                            updated_at = NOW()
                        WHERE id = ? 
                        AND rider_id = ?
                        AND status = 'pending'
                        AND rider_confirmed_at IS NULL
                        AND rider_rejected_at IS NULL
                    ");
                    $stmt->execute([$payoutId, $riderId]);
                    
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success_message'] = "Payout confirmed successfully!";
                        
                        // Notify admin about confirmation
                        $payoutStmt = $pdo->prepare("
                            SELECT rp.amount, u.name as rider_name 
                            FROM rider_payouts rp 
                            JOIN users u ON rp.rider_id = u.user_id 
                            WHERE rp.id = ?
                        ");
                        $payoutStmt->execute([$payoutId]);
                        $payoutDetails = $payoutStmt->fetch();
                        
                        if ($payoutDetails) {
                            $title = "Payout Confirmed by Rider";
                            $message = "Rider " . $payoutDetails['rider_name'] . 
                                      " confirmed payout of " . number_format($payoutDetails['amount'], 2) . 
                                      " MMK.";
                            
                            // Get all admins
                            $adminStmt = $pdo->prepare("SELECT user_id FROM users WHERE role = 'admin'");
                            $adminStmt->execute();
                            $admins = $adminStmt->fetchAll();
                            
                            $notifStmt = $pdo->prepare("
                                INSERT INTO request_notifications 
                                (user_id, title, message, created_at) 
                                VALUES (?, ?, ?, NOW())
                            ");
                            
                            foreach ($admins as $admin) {
                                $notifStmt->execute([$admin['user_id'], $title, $message]);
                            }
                        }
                    } else {
                        $_SESSION['error_message'] = "Failed to confirm payout. Please try again.";
                    }
                }
            } 
            elseif ($action === 'reject') {
                $rejectionReason = $_POST['rejection_reason'] ?? '';
                
                if (empty(trim($rejectionReason))) {
                    $_SESSION['error_message'] = "Please provide a reason for rejection.";
                    header("Location: rider_payout_status.php");
                    exit();
                }
                
                // First check if payout exists and is pending
                $checkStmt = $pdo->prepare("
                    SELECT id, status, rider_confirmed_at, rider_rejected_at 
                    FROM rider_payouts 
                    WHERE id = ? 
                    AND rider_id = ?
                ");
                $checkStmt->execute([$payoutId, $riderId]);
                $payout = $checkStmt->fetch();
                
                error_log("Reject payout check: " . print_r($payout, true));
                
                if (!$payout) {
                    $_SESSION['error_message'] = "Payout not found.";
                } elseif ($payout['status'] !== 'pending') {
                    $_SESSION['error_message'] = "Payout status is already '{$payout['status']}'.";
                } elseif ($payout['rider_confirmed_at']) {
                    $_SESSION['error_message'] = "Payout already confirmed.";
                } elseif ($payout['rider_rejected_at']) {
                    $_SESSION['error_message'] = "Payout already rejected.";
                } else {
                    // Proceed with rejection
                    $stmt = $pdo->prepare("
                        UPDATE rider_payouts 
                        SET rider_rejected_at = NOW(),
                            rider_rejection_reason = ?,
                            status = 'rejected',
                            updated_at = NOW()
                        WHERE id = ? 
                        AND rider_id = ?
                        AND status = 'pending'
                        AND rider_confirmed_at IS NULL
                        AND rider_rejected_at IS NULL
                    ");
                    $stmt->execute([$rejectionReason, $payoutId, $riderId]);
                    
                    if ($stmt->rowCount() > 0) {
                        // Notify admins
                        $payoutStmt = $pdo->prepare("
                            SELECT rp.amount, u.name as rider_name 
                            FROM rider_payouts rp 
                            JOIN users u ON rp.rider_id = u.user_id 
                            WHERE rp.id = ?
                        ");
                        $payoutStmt->execute([$payoutId]);
                        $payoutDetails = $payoutStmt->fetch();
                        
                        if ($payoutDetails) {
                            $title = "Rider Rejected Payout";
                            $message = "Rider " . $payoutDetails['rider_name'] . 
                                      " rejected payout of " . number_format($payoutDetails['amount'], 2) . 
                                      " MMK. Reason: " . $rejectionReason;
                            
                            // Get all admins
                            $adminStmt = $pdo->prepare("SELECT user_id FROM users WHERE role = 'admin'");
                            $adminStmt->execute();
                            $admins = $adminStmt->fetchAll();
                            
                            $notifStmt = $pdo->prepare("
                                INSERT INTO request_notifications 
                                (user_id, title, message, created_at) 
                                VALUES (?, ?, ?, NOW())
                            ");
                            
                            foreach ($admins as $admin) {
                                $notifStmt->execute([$admin['user_id'], $title, $message]);
                            }
                        }
                        
                        $_SESSION['success_message'] = "Payout rejected successfully.";
                    } else {
                        $_SESSION['error_message'] = "Failed to reject payout. Please try again.";
                    }
                }
            }
            
            header("Location: rider_payout_status.php");
            exit();
            
        } catch (Exception $e) {
            error_log("Payout action error: " . $e->getMessage());
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header("Location: rider_payout_status.php");
            exit();
        }
    }
}

// Debug: Check if rider_payouts table exists
$tableCheck = $pdo->query("SHOW TABLES LIKE 'rider_payouts'");
if ($tableCheck->rowCount() == 0) {
    die("rider_payouts table does not exist");
}

// Debug: Check if rider exists in users table
$riderCheck = $pdo->prepare("SELECT user_id, name FROM users WHERE user_id = ? AND role = 'delivery'");
$riderCheck->execute([$riderId]);
$riderData = $riderCheck->fetch();

if (!$riderData) {
    die("Rider not found or invalid rider ID. Make sure the user has 'delivery' role.");
}

error_log("Rider found: " . print_r($riderData, true));

// First, let's check if there are any payouts at all
$allPayoutsCount = $pdo->query("SELECT COUNT(*) as count FROM rider_payouts")->fetch()['count'];
error_log("Total payouts in database: " . $allPayoutsCount);

// Now check payouts for this rider
$riderPayoutsCount = $pdo->prepare("SELECT COUNT(*) as count FROM rider_payouts WHERE rider_id = ?");
$riderPayoutsCount->execute([$riderId]);
$riderPayoutsCount = $riderPayoutsCount->fetch()['count'];
error_log("Payouts for rider ID $riderId: " . $riderPayoutsCount);

// Fetch rider's payouts with detailed error logging
error_log("Fetching payouts for rider ID: " . $riderId);

// Modified query to ensure we're getting all relevant data
$sql = "
SELECT 
    rp.*,
    u.name as released_by_name,
    CASE 
        WHEN rp.status = 'pending' AND rp.rider_confirmed_at IS NULL AND rp.rider_rejected_at IS NULL THEN 'awaiting_confirmation'
        WHEN rp.status = 'pending' AND rp.rider_rejected_at IS NOT NULL THEN 'rejected'
        WHEN rp.status = 'pending' AND rp.rider_confirmed_at IS NOT NULL THEN 'completed'
        ELSE rp.status 
    END as display_status
FROM rider_payouts rp
LEFT JOIN users u ON rp.released_by = u.user_id
WHERE rp.rider_id = ?
ORDER BY rp.created_at DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $executed = $stmt->execute([$riderId]);
    
    if ($executed === false) {
        $error = $stmt->errorInfo();
        die("Query failed: " . $error[2]);
    }
    
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log the number of payouts found
    error_log("Number of payouts found: " . count($payouts));
    
    // Log all payouts for debugging
    if (count($payouts) > 0) {
        error_log("First payout data: " . print_r($payouts[0], true));
    } else {
        error_log("No payouts found for rider ID: " . $riderId);
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: rider_payout_status.php");
    exit();
}

// Calculate totals
$totalReceived = 0;
$pendingAmount = 0;
$awaitingConfirmation = 0;

foreach ($payouts as $payout) {
    if ($payout['status'] === 'completed') {
        $totalReceived += $payout['amount'];
    } elseif ($payout['status'] === 'pending') {
        $pendingAmount += $payout['amount'];
        if (!$payout['rider_confirmed_at'] && !$payout['rider_rejected_at']) {
            $awaitingConfirmation += $payout['amount'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Payouts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial; margin: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center; }
        .stat-card.completed { border-left: 4px solid #28a745; }
        .stat-card.pending { border-left: 4px solid #ffc107; }
        .stat-card.awaiting { border-left: 4px solid #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-awaiting { background: #d1ecf1; color: #0c5460; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; padding: 20px; border-radius: 5px; max-width: 500px; margin: 50px auto; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; }
        .debug-info { background: #e9ecef; padding: 10px; border-radius: 4px; margin: 10px 0; font-size: 12px; color: #495057; }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="dashboard.php" class="btn" style="background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <h2 style="margin: 0;"><i class="fas fa-wallet"></i> My Payouts</h2>
        <div style="width: 140px;"></div> <!-- Empty div to balance the flex layout -->
    </div>
    
    <!-- Debug Info -->
    <div class="debug-info">
        <strong>Debug Info:</strong> Rider ID: <?php echo $riderId; ?> | 
        Payouts Found: <?php echo count($payouts); ?> | 
        Awaiting Confirmation: <?php echo number_format($awaitingConfirmation, 2); ?> MMK
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="stats">
        <div class="stat-card completed">
            <h3>Total Received</h3>
            <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                <?php echo number_format($totalReceived, 2); ?> MMK
            </div>
        </div>
        <div class="stat-card pending">
            <h3>Pending Amount</h3>
            <div style="font-size: 24px; font-weight: bold; color: #ffc107;">
                <?php echo number_format($pendingAmount, 2); ?> MMK
            </div>
        </div>
        <div class="stat-card awaiting">
            <h3>Awaiting Confirmation</h3>
            <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">
                <?php echo number_format($awaitingConfirmation, 2); ?> MMK
            </div>
        </div>
    </div>
    
    <!-- Payouts List -->
    <h3>Payout History</h3>
    
    <?php if (empty($payouts)): ?>
        <p>No payouts found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Period</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Slip</th>
                    <th>Initiated By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payouts as $payout): ?>
                <tr>
                    <td>#<?php echo $payout['id']; ?></td>
                    <td>
                        <?php echo date('M d', strtotime($payout['period_start'])); ?> -<br>
                        <?php echo date('M d', strtotime($payout['period_end'])); ?>
                    </td>
                    <td style="font-weight: bold;">
                        <?php echo number_format($payout['amount'], 2); ?> MMK
                    </td>
                    <td>
                        <?php 
                        $statusClass = 'status-pending';
                        $statusText = 'Pending';
                        
                        if ($payout['status'] === 'completed') {
                            $statusClass = 'status-completed';
                            $statusText = 'Completed';
                        } elseif ($payout['status'] === 'rejected') {
                            $statusClass = 'status-rejected';
                            $statusText = 'Rejected';
                        } elseif ($payout['status'] === 'pending' && !$payout['rider_confirmed_at'] && !$payout['rider_rejected_at']) {
                            $statusClass = 'status-awaiting';
                            $statusText = 'Awaiting Confirmation';
                        } elseif ($payout['status'] === 'pending' && $payout['rider_confirmed_at']) {
                            $statusClass = 'status-completed';
                            $statusText = 'Confirmed';
                        } elseif ($payout['status'] === 'pending' && $payout['rider_rejected_at']) {
                            $statusClass = 'status-rejected';
                            $statusText = 'Rejected';
                        }
                        ?>
                        <span class="status <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                        
                        <?php if ($payout['rider_rejection_reason']): ?>
                            <div style="font-size: 12px; color: #dc3545; margin-top: 5px;">
                                <strong>Reason:</strong> <?php echo htmlspecialchars($payout['rider_rejection_reason']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($payout['status'] === 'pending'): ?>
                            <div style="font-size: 11px; color: #6c757d; margin-top: 3px;">
                                <?php if ($payout['rider_confirmed_at']): ?>
                                    Confirmed: <?php echo date('M d H:i', strtotime($payout['rider_confirmed_at'])); ?>
                                <?php elseif ($payout['rider_rejected_at']): ?>
                                    Rejected: <?php echo date('M d H:i', strtotime($payout['rider_rejected_at'])); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($payout['payment_slip']): ?>
                            <a href="/foodandme/assets/rider_payout_slips/<?php echo htmlspecialchars($payout['payment_slip']); ?>" 
                               target="_blank" style="color: #007bff; text-decoration: none;">
                                <i class="fas fa-file-invoice"></i> View Slip
                            </a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($payout['released_by_name'] ?: 'Admin'); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($payout['created_at'])); ?></td>
                    <td>
                        <?php if ($payout['status'] === 'pending' && !$payout['rider_confirmed_at'] && !$payout['rider_rejected_at']): ?>
                            <button class="btn btn-success btn-sm" onclick="confirmPayout(<?php echo $payout['id']; ?>)">
                                <i class="fas fa-check"></i> Confirm
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="rejectPayout(<?php echo $payout['id']; ?>)" style="margin-top: 5px;">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        <?php elseif ($payout['status'] === 'completed' || ($payout['status'] === 'pending' && $payout['rider_confirmed_at'])): ?>
                            <span style="color: #28a745;">
                                <i class="fas fa-check-circle"></i> Confirmed
                            </span>
                        <?php elseif ($payout['status'] === 'rejected' || ($payout['status'] === 'pending' && $payout['rider_rejected_at'])): ?>
                            <span style="color: #dc3545;">
                                <i class="fas fa-times-circle"></i> Rejected
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-exclamation-triangle"></i> Reject Payout</h3>
        <form id="rejectForm" method="POST">
            <input type="hidden" name="payout_id" id="rejectPayoutId">
            <input type="hidden" name="action" value="reject">
            
            <div class="form-group">
                <label for="rejection_reason">Reason for Rejection *</label>
                <textarea name="rejection_reason" id="rejection_reason" rows="4" 
                          placeholder="Please provide a reason for rejecting this payout..." required></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Submit Rejection</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-check-circle"></i> Confirm Payout</h3>
        <p>Are you sure you want to confirm this payout? This action cannot be undone.</p>
        <form id="confirmForm" method="POST">
            <input type="hidden" name="payout_id" id="confirmPayoutId">
            <input type="hidden" name="action" value="confirm">
            
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Yes, Confirm Payout</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmPayout(payoutId) {
    document.getElementById('confirmPayoutId').value = payoutId;
    document.getElementById('confirmModal').style.display = 'block';
}

function rejectPayout(payoutId) {
    document.getElementById('rejectPayoutId').value = payoutId;
    document.getElementById('rejectModal').style.display = 'block';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('rejection_reason').value = '';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['confirmModal', 'rejectModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            if (modalId === 'confirmModal') closeConfirmModal();
            if (modalId === 'rejectModal') closeRejectModal();
        }
    });
};

// Validate rejection form
document.getElementById('rejectForm').addEventListener('submit', function(e) {
    const reason = document.getElementById('rejection_reason').value.trim();
    if (!reason) {
        e.preventDefault();
        alert('Please provide a reason for rejection.');
        return false;
    }
    return true;
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>