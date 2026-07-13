<?php
ob_start();
session_start();
require_once __DIR__ . "/../../config/db.php";

$adminId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? '';

// Check if user is admin
if (!$adminId || $userRole !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle payout actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payoutId = $_POST['payout_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if ($payoutId && in_array($action, ['remove', 'resend'])) {
        try {
            if ($action === 'remove') {
                $stmt = $pdo->prepare("DELETE FROM rider_payouts WHERE id = ? AND status = 'rejected'");
                $stmt->execute([$payoutId]);
                
                if ($stmt->rowCount() > 0) {
                    $_SESSION['success_message'] = "Rejected payout removed successfully!";
                }
                
            } elseif ($action === 'resend') {
                // Check if a new slip was uploaded
                $newSlipFilename = null;
                if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . "/../../assets/rider_payout_slips/";
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
                    $file_type = $_FILES['payment_slip']['type'];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        throw new Exception("Invalid file type. Only JPG, PNG, GIF, PDF are allowed.");
                    }
                    
                    if ($_FILES['payment_slip']['size'] > 5 * 1024 * 1024) {
                        throw new Exception("File size too large. Maximum size is 5MB.");
                    }
                    
                    $file_extension = pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION);
                    $newSlipFilename = 'resend_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $newSlipFilename;
                    
                    if (!move_uploaded_file($_FILES['payment_slip']['tmp_name'], $upload_path)) {
                        throw new Exception("Failed to upload payment slip.");
                    }
                }
                
                // Get current payout details
                $payoutStmt = $pdo->prepare("
                    SELECT rider_id, amount, period_start, period_end 
                    FROM rider_payouts 
                    WHERE id = ? AND status = 'rejected'
                ");
                $payoutStmt->execute([$payoutId]);
                $payout = $payoutStmt->fetch();
                
                if (!$payout) {
                    throw new Exception("Payout not found or not rejected.");
                }
                
                // Insert as new payout with new slip or keep old slip
                $slipToUse = $newSlipFilename ?: null; // If no new slip, keep null to use old one
                
                $insertStmt = $pdo->prepare("
                    INSERT INTO rider_payouts 
                    (rider_id, amount, period_start, period_end, payment_slip, 
                     released_by, status, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
                ");
                
                $notes = "Resent by admin. Original payout was rejected. ";
                if ($newSlipFilename) {
                    $notes .= "New payment slip uploaded.";
                }
                
                $insertStmt->execute([
                    $payout['rider_id'],
                    $payout['amount'],
                    $payout['period_start'],
                    $payout['period_end'],
                    $slipToUse,
                    $adminId,
                    $notes
                ]);
                
                $newPayoutId = $pdo->lastInsertId();
                
                // Create notification for rider
                $notifStmt = $pdo->prepare("
                    INSERT INTO request_notifications 
                    (user_id, title, message, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                
                $title = "Payout Resent for Review";
                $message = "Your rejected payout of " . number_format($payout['amount'], 2) . 
                          " MMK has been resent for period {$payout['period_start']} to {$payout['period_end']}. " .
                          "Please review and confirm.";
                
                $notifStmt->execute([$payout['rider_id'], $title, $message]);
                
                // Mark original as archived or delete it
                $updateStmt = $pdo->prepare("
                    UPDATE rider_payouts 
                    SET status = 'archived',
                        notes = CONCAT(COALESCE(notes, ''), '\nArchived - Resent as payout #', ?)
                    WHERE id = ?
                ");
                $updateStmt->execute([$newPayoutId, $payoutId]);
                
                $_SESSION['success_message'] = "Payout resent successfully! New payout ID: #{$newPayoutId}";
            }
            
            header("Location: admin_rejected_payouts.php");
            exit();
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error processing request: " . $e->getMessage();
        }
    }
}

// Fetch rejected payouts
$sql = "
SELECT 
    rp.*,
    u.name as rider_name,
    u.phone as rider_phone,
    u2.name as released_by_name
FROM rider_payouts rp
LEFT JOIN users u ON rp.rider_id = u.user_id
LEFT JOIN users u2 ON rp.released_by = u2.user_id
WHERE rp.status = 'rejected'
AND rp.rider_rejected_at IS NOT NULL
ORDER BY rp.rider_rejected_at DESC
";

$stmt = $pdo->query($sql);
$rejectedPayouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    $updateStmt = $pdo->prepare("
        UPDATE request_notifications 
        SET is_read = 1 
        WHERE user_id = ? 
        AND title LIKE '%Rider Rejected Payout%'
    ");
    $updateStmt->execute([$adminId]);
}

// Include the header
include "includes/header.php";
?>
<!-- Content starts here - no <html>, <head>, or <body> tags -->
<style>
    /* Add only the styles specific to this page */
    .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #f8f9fa; }
    .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
    .btn-danger { background: #dc3545; color: white; }
    .btn-warning { background: #ffc107; color: black; }
    .btn-info { background: #17a2b8; color: white; }
    .btn-success { background: #28a745; color: white; }
    .rejection-reason { background: #f8d7da; padding: 10px; border-radius: 4px; margin: 5px 0; color: #721c24; }
    .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .empty-state { text-align: center; padding: 40px; color: #6c757d; }
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; }
    .modal-content { background: white; padding: 20px; border-radius: 5px; max-width: 500px; margin: 50px auto; }
    .form-group { margin: 15px 0; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="file"] { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; }
    .file-info { font-size: 12px; color: #6c757d; margin-top: 5px; }
</style>

<div class="main-content">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-exclamation-triangle"></i>
            Rejected Rider Payouts
        </h1>
        <div class="header-actions">
            <span class="badge badge-danger"><?php echo count($rejectedPayouts); ?> Rejected</span>
        </div>
    </div>

    <!-- <div class="card">
        <div class="card-header">
            <h2>Rejected Payouts</h2>
            <div class="filter-section">
                <div class="filter-group">
                    <label for="riderFilter">Filter by Rider</label>
                    <select id="riderFilter" class="form-control">
                        <option value="">All Riders</option>
                        <?php 
                        $riders = [];
                        foreach ($rejectedPayouts as $payout) {
                            $riders[$payout['rider_id']] = $payout['rider_name'];
                        }
                        foreach (array_unique($riders) as $id => $name): 
                        ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="dateFilter">Filter by Date</label>
                    <input type="date" id="dateFilter" class="form-control">
                </div>
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-sync-alt"></i> Reset
                </button>
            </div>
        </div> -->
        
        <div class="card-body">
        
            <?php if (empty($rejectedPayouts)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>No Rejected Payouts</h3>
                    <p>All payouts have been processed or are awaiting rider confirmation.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Payout ID</th>
                                <th>Rider Details</th>
                                <th>Amount</th>
                                <th>Period</th>
                                <th>Rejection Details</th>
                                <th>Current Slip</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rejectedPayouts as $payout): ?>
                            <tr data-rider-id="<?php echo $payout['rider_id']; ?>" data-date="<?php echo date('Y-m-d', strtotime($payout['rider_rejected_at'])); ?>">
                                <td>#<?php echo $payout['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <!-- <i class="fas fa-motorcycle fa-lg text-muted"></i> -->
                                        </div>
                                        <div>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($payout['rider_name']); ?></div>
                                            <div class="text-muted small"><?php echo $payout['rider_phone']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-weight-bold">
                                    <?php echo number_format($payout['amount'], 2); ?> MMK
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span><?php echo date('M d, Y', strtotime($payout['period_start'])); ?></span>
                                        <span class="text-muted small">to</span>
                                        <span><?php echo date('M d, Y', strtotime($payout['period_end'])); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="rejection-reason mb-2">
                                            <strong>Reason:</strong>
                                            <?php echo $payout['rider_rejection_reason'] ? htmlspecialchars($payout['rider_rejection_reason']) : '<span class="text-muted">No reason provided</span>'; ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="far fa-clock"></i> 
                                            <?php echo date('M d, Y h:i A', strtotime($payout['rider_rejected_at'])); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($payout['payment_slip']): ?>
                                        <a href="/foodandme/assets/rider_payout_slips/<?php echo $payout['payment_slip']; ?>" 
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-invoice"></i> View Slip
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No slip available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" 
                                                class="btn btn-warning btn-sm" 
                                                onclick="openResendModal(<?php echo $payout['id']; ?>, '<?php echo htmlspecialchars(addslashes($payout['rider_name'])); ?>', <?php echo $payout['amount']; ?>)"
                                                data-toggle="tooltip" 
                                                title="Resend Payout">
                                            <i class="fas fa-redo"></i> Resend
                                        </button>
                                        <form method="POST" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to permanently delete this rejected payout? This action cannot be undone.');">
                                            <input type="hidden" name="payout_id" value="<?php echo $payout['id']; ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm"
                                                    data-toggle="tooltip" 
                                                    title="Delete Payout">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Resend Payout Modal -->
<div id="resendModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-redo"></i> Resend Payout</h3>
            <button type="button" class="close-btn" onclick="closeResendModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <!-- <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Resend this payout to the rider</strong><br>
                    You can upload a new payment slip or keep the existing one.
                </div>
            </div> -->
            
            <div class="current-payout-details mb-4 p-3 bg-light rounded">
                <h5 class="mb-3">Payout Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <!-- <span class="text-muted">Rider:</span> -->
                            <span id="modalRiderName" class="font-weight-bold"></span>
                        </div>
                        <div class="detail-item mb-2">
                            <!-- <span class="text-muted">Amount:</span> -->
                            <span id="modalPayoutAmount" class="font-weight-bold"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <!-- <span class="text-muted">Payout ID:</span> -->
                            <span id="modalPayoutId" class="font-weight-bold"></span>
                        </div>
                        <div class="detail-item">
                            <!-- <span class="text-muted">Current Slip:</span> -->
                            <span id="slipInfo" class="text-primary">Checking...</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <form id="resendForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="payout_id" id="resendPayoutId">
                <input type="hidden" name="action" value="resend">
                
                <div class="form-group">
                    <label for="payment_slip" class="form-label">
                        <i class="fas fa-file-upload me-1"></i> 
                        Upload New Payment Slip (Optional)
                    </label>
                    <input type="file" 
                           class="form-control" 
                           id="payment_slip" 
                           name="payment_slip" 
                           accept="image/*,.pdf"
                           onchange="updateFileInfo(this)">
                    <div class="form-text text-muted">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            Accepted formats: JPG, PNG, GIF, PDF (Max 5MB). 
                            Leave empty to keep current slip.
                        </small>
                    </div>
                    <div id="fileInfo" class="small text-muted mt-1"></div>
                </div>
                
                <div class="form-group mt-4">
                    <!-- <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmResend" required>
                        <label class="form-check-label" for="confirmResend">
                            I confirm that I want to resend this payout
                        </label>
                        <div class="invalid-feedback">
                            Please confirm before resending the payout.
                        </div>
                    </div> -->
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeResendModal()">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Resend Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Store payout data globally
let currentPayoutData = {};
    
function openResendModal(payoutId, riderName, amount) {
    // Set form data
    document.getElementById('resendPayoutId').value = payoutId;
    
    // Store current payout data
    currentPayoutData = {
        id: payoutId,
        riderName: riderName,
        amount: amount
    };
    
    // Update slip info
    document.getElementById('slipInfo').innerHTML = `
        <div style="margin-top: 5px;">
            <i class="fas fa-user"></i> Rider: ${riderName}<br>
            <i class="fas fa-money-bill"></i> Amount: ${amount.toLocaleString()} MMK<br>
        </div>
    `;
    
    // Show modal
    document.getElementById('resendModal').style.display = 'block';
}
    
function closeResendModal() {
    document.getElementById('resendModal').style.display = 'none';
    document.getElementById('resendForm').reset();
    currentPayoutData = {};
}
    
// Validate file size before submit
document.getElementById('resendForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('payment_slip');
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            e.preventDefault();
            alert('File size too large. Maximum size is 5MB.');
            return false;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            e.preventDefault();
            alert('Invalid file type. Only JPG, PNG, GIF, PDF are allowed.');
            return false;
        }
    }
    
    // Confirm resend
    if (!confirm(`Resend payout of ${currentPayoutData.amount.toLocaleString()} MMK to ${currentPayoutData.riderName}?`)) {
        e.preventDefault();
        return false;
    }
    
    return true;
});
    
// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('resendModal');
    if (event.target === modal) {
        closeResendModal();
    }
};
    
// File input preview (optional enhancement)
document.getElementById('payment_slip').addEventListener('change', function(e) {
    if (this.files.length > 0) {
        const file = this.files[0];
        const fileInfo = document.querySelector('.file-info');
        fileInfo.innerHTML = `
            Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)<br>
            Max 5MB. Images or PDF. Leave empty to keep current slip.
        `;
    }
});
</script>

<?php ob_end_flush(); ?>
