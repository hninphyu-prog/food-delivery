<?php
// admin_rider_payouts.php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../../config/db.php";


$adminId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? '';

if (!$adminId || $userRole !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle payout processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_payout'])) {
        error_log("Payout processing started");
        
        if (!empty($_POST['payouts'])) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            try {
                $pdo->beginTransaction();
                $processedPayouts = [];
                
                // Handle file upload
                $slip_filename = null;
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
                    $slip_filename = 'payout_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $slip_filename;
                    
                    if (!move_uploaded_file($_FILES['payment_slip']['tmp_name'], $upload_path)) {
                        throw new Exception("Failed to upload payment slip.");
                    }
                }
                
                // Process each payout
                $payouts = is_array($_POST['payouts']) ? $_POST['payouts'] : [$_POST['payouts']];
                
                foreach ($payouts as $payoutData) {
                    $parts = explode('|', $payoutData);
                    if (count($parts) < 3) continue;
                    
                    list($rider_id, $period, $amount) = $parts;
                    $rider_id = (int)$rider_id;
                    $amount = (float)$amount;
                    
                    if ($amount <= 0) continue;
                    
                    // Parse period
                    $period_parts = explode('_to_', $period);
                    if (count($period_parts) !== 2) continue;
                    
                    list($period_start, $period_end) = $period_parts;
                    
                    // Check if payout already exists for this period
                    $checkStmt = $pdo->prepare("
                        SELECT id, status 
                        FROM rider_payouts 
                        WHERE rider_id = ? 
                        AND period_start = ? 
                        AND period_end = ?
                    ");
                    $checkStmt->execute([$rider_id, $period_start, $period_end]);
                    $existing = $checkStmt->fetch();
                    
                    $payout_id = null;
                    
                    if ($existing) {
                        // Update existing payout
                        if ($existing['status'] === 'rejected') {
                            $updateStmt = $pdo->prepare("
                                UPDATE rider_payouts 
                                SET amount = ?, 
                                    payment_slip = ?,
                                    notes = CONCAT(COALESCE(notes, ''), ' | Re-initiated'),
                                    released_by = ?,
                                    status = 'pending',
                                    rider_confirmed_at = NULL,
                                    rider_rejected_at = NULL,
                                    rider_rejection_reason = NULL,
                                    updated_at = NOW()
                                WHERE id = ?
                            ");
                            $updateStmt->execute([
                                $amount,
                                $slip_filename,
                                $adminId,
                                $existing['id']
                            ]);
                            $payout_id = $existing['id'];
                        }
                        // Skip if already pending/completed
                    } else {
                        // Insert new payout with period
                        $insertStmt = $pdo->prepare("
                            INSERT INTO rider_payouts 
                            (rider_id, amount, period_start, period_end, payment_slip, 
                             released_by, status, notes, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
                        ");
                        
                        $notes = "Payout for period {$period_start} to {$period_end}";
                        $insertStmt->execute([
                            $rider_id,
                            $amount,
                            $period_start,
                            $period_end,
                            $slip_filename,
                            $adminId,
                            $notes
                        ]);
                        
                        $payout_id = $pdo->lastInsertId();
                    }
                    
                    if ($payout_id) {
                        // Create notification for rider
                        $notifStmt = $pdo->prepare("
                            INSERT INTO request_notifications 
                            (user_id, title, message, created_at)
                            VALUES (?, ?, ?, NOW())
                        ");
                        
                        $title = "New Payout Available";
                        $message = "A payout of " . number_format($amount, 2) . 
                                  " MMK has been initiated for period {$period_start} to {$period_end}. " .
                                  "Please review and confirm.";
                        
                        $notifStmt->execute([$rider_id, $title, $message]);
                        
                        // Record expense
                        $expenseStmt = $pdo->prepare("
                            INSERT INTO expenses 
                            (category, amount, note, rider_id, payment_slip, created_at)
                            VALUES (?, ?, ?, ?, ?, NOW())
                        ");
                        
                        $riderNameStmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
                        $riderNameStmt->execute([$rider_id]);
                        $rider_name = $riderNameStmt->fetchColumn() ?: "Rider {$rider_id}";
                        
                        $expenseStmt->execute([
                            "Rider Payout - {$rider_name}",
                            $amount,
                            "Payout ID: {$payout_id}, Period: {$period_start} to {$period_end}",
                            $rider_id,
                            $slip_filename
                        ]);
                        
                        $processedPayouts[] = [
                            'rider_id' => $rider_id,
                            'payout_id' => $payout_id,
                            'amount' => $amount,
                            'period' => $period
                        ];
                        
                        // DEBUG: Verify the payout was created
                        error_log("Created payout ID: {$payout_id} for rider: {$rider_id}");
                    }
                }
                
                if (empty($processedPayouts)) {
                    throw new Exception("No payouts were processed. Please check your selection.");
                }
                
                $pdo->commit();
                
                // DEBUG: Log processed payouts
                error_log("Successfully processed " . count($processedPayouts) . " payout(s)");
                error_log("Processed payouts details: " . print_r($processedPayouts, true));
                
                $_SESSION['success_message'] = "Successfully processed " . 
                    count($processedPayouts) . " payout(s). Riders have been notified.";
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
                error_log("Payout processing error: " . $e->getMessage());
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['error_message'] = "No payouts selected.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    // Handle removal of rejected payout
    if (isset($_POST['remove_rejected'])) {
        $payoutId = $_POST['payout_id'] ?? null;
        
        if ($payoutId) {
            try {
                $deleteStmt = $pdo->prepare("DELETE FROM rider_payouts WHERE id = ? AND status = 'rejected'");
                $deleteStmt->execute([$payoutId]);
                
                if ($deleteStmt->rowCount() > 0) {
                    $_SESSION['success_message'] = "Rejected payout removed successfully.";
                }
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Error removing payout: " . $e->getMessage();
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

// Fetch unpaid rider commissions - Grouped by Week
$unpaidSql = "
SELECT 
    d.delivery_boy_id as rider_id,
    u.name as rider_name,
    u.phone as rider_phone,
    DATE(DATE_FORMAT(o.created_at, '%Y-%m-%d') - INTERVAL (WEEKDAY(o.created_at)) DAY) as week_start,
    DATE(DATE_FORMAT(o.created_at, '%Y-%m-%d') - INTERVAL (WEEKDAY(o.created_at) - 6) DAY) as week_end,
    COUNT(DISTINCT o.order_id) as delivered_orders,
    ROUND(SUM(o.delivery_fee), 2) as total_commission,
    ROUND(SUM(o.delivery_fee), 2) as rider_commission
FROM orders o
JOIN delivery d ON o.order_id = d.order_id
JOIN users u ON d.delivery_boy_id = u.user_id
WHERE o.order_status = 'delivered'
    AND o.payment_status = 'paid'
    AND d.status = 'delivered'
    AND NOT EXISTS (
        SELECT 1 
        FROM rider_payouts rp 
        WHERE rp.rider_id = d.delivery_boy_id 
        AND DATE(DATE_FORMAT(o.created_at, '%Y-%m-%d') - INTERVAL (WEEKDAY(o.created_at)) DAY) = rp.period_start
        AND DATE(DATE_FORMAT(o.created_at, '%Y-%m-%d') - INTERVAL (WEEKDAY(o.created_at) - 6) DAY) = rp.period_end
        AND rp.status IN ('completed', 'pending')
    )
GROUP BY d.delivery_boy_id, week_start, week_end
HAVING rider_commission > 0
ORDER BY week_end DESC, u.name ASC
";

$unpaidStmt = $pdo->query($unpaidSql);
$unpaid_commissions = $unpaidStmt->fetchAll(PDO::FETCH_ASSOC);

// DEBUG: Log unpaid commissions
error_log("Unpaid commissions found: " . count($unpaid_commissions));
if (count($unpaid_commissions) > 0) {
    error_log("Sample unpaid commission: " . print_r($unpaid_commissions[0], true));
}

// Fetch all payouts for admin view
$allPayoutsSql = "
SELECT 
    rp.*,
    u.name as rider_name,
    u2.name as released_by_name
FROM rider_payouts rp
LEFT JOIN users u ON rp.rider_id = u.user_id
LEFT JOIN users u2 ON rp.released_by = u2.user_id
ORDER BY rp.created_at DESC
LIMIT 50
";

$allStmt = $pdo->query($allPayoutsSql);
$allPayouts = $allStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Rider Payouts</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
    --primary-color: #4e73df;
    --success-color: #1cc88a;
    --info-color: #36b9cc;
    --warning-color: #f6c23e;
    --danger-color: #e74a3b;
    --secondary-color: #858796;
    --light: #f8f9fc;
    --dark: #5a5c69;
}

body {
    font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background-color: #f8f9fc;
    color: #333;
    line-height: 1.6;
}

.main-content {
    margin-left: 14rem;
    padding: 2rem;
}

.section {
    background: #fff;
    border-radius: 0.35rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    margin-bottom: 2rem;
    padding: 1.5rem;
}

.section h3 {
    color: var(--dark);
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e3e6f0;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-top: 1px solid #e3e6f0;
}

.table thead th {
    background-color: #f8f9fc;
    color: #4e73df;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.08em;
    border-bottom: 1px solid #e3e6f0;
}

.table tbody tr:hover {
    background-color: #f8f9fc;
}

.btn {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 0.35rem;
    transition: all 0.3s;
}

.btn i {
    margin-right: 0.3rem;
}

.btn-success {
    background-color: var(--success-color);
    border-color: var(--success-color);
}

.btn-success:hover {
    background-color: #17a673;
    border-color: #169b6b;
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: #2e59d9;
    border-color: #2653d4;
}

.btn-danger {
    background-color: var(--danger-color);
    border-color: var(--danger-color);
}

.btn-danger:hover {
    background-color: #e02d1b;
    border-color: #d52a1a;
}

.alert {
    border: none;
    border-radius: 0.35rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d1f3e8;
    color: #0f6848;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.35em 0.65em;
    border-radius: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.badge-success {
    background-color: #d1f3e8;
    color: #0f6848;
}

.badge-warning {
    background-color: #fef3d4;
    color: #856404;
}

.badge-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.badge-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 1.5rem;
    border: none;
    border-radius: 0.35rem;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal h3 {
    color: var(--dark);
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e3e6f0;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 0.9rem;
    font-weight: 400;
    line-height: 1.5;
    color: #6e707e;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    color: #6e707e;
    background-color: #fff;
    border-color: #bac8f3;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .section {
        padding: 1rem;
    }
    
    .table-responsive {
}
}
</style>
</head>
<body>
<?php include "includes/header.php"; ?>

<div class="main-content">
    <div class="section">
        <h2><i class="fas fa-money-bill-wave"></i> Rider Payout Management</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success d-flex align-items-center">
        <i class="fas fa-check-circle me-2"></i>
        <div><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
    </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger d-flex align-items-center">
        <i class="fas fa-exclamation-circle me-2"></i>
        <div><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
    </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- Debug Info (remove in production) -->
    <?php if (isset($show_debug) && $show_debug): ?>
    <div class="alert alert-warning mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-bug me-2"></i>
                <strong>Debug Info:</strong> 
                Found <?php echo count($unpaid_commissions); ?> unpaid commission(s) | 
                Total payouts in system: <?php echo count($allPayouts); ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Unpaid Commissions Section -->
    <div class="section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0"><i class="fas fa-clock"></i> Unpaid Commissions</h3>
            <div class="d-flex">
                <div class="input-group me-2" style="max-width: 250px;">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" id="riderSearch" class="form-control" placeholder="Filter by rider name..." onkeyup="filterRiders()">
                </div>
            </div>
        </div>
        
        <form id="payoutForm" method="POST" enctype="multipart/form-data">
            <?php if (empty($unpaid_commissions)): ?>
                <p>No unpaid commissions found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="checkbox-cell"><input type="checkbox" id="selectAll"></th>
                            <th>Rider</th>
                            <th>Week</th>
                            <th>Orders</th>
                            <th>Amount (MMK)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unpaid_commissions as $commission): 
                            $period_key = $commission['week_start'] . '_to_' . $commission['week_end'];
                        ?>
                        <tr>
                            <td class="checkbox-cell">
                                <input type="hidden" name="payouts[]" value="<?php echo $commission['rider_id'] . '|' . $commission['week_start'] . '_to_' . $commission['week_end'] . '|' . $commission['rider_commission']; ?>">
                                <input type="checkbox" class="payout-checkbox" 
                                       data-rider="<?php echo $commission['rider_id']; ?>"
                                       data-period="<?php echo $period_key; ?>"
                                       data-amount="<?php echo $commission['rider_commission']; ?>"
                                       data-date="<?php echo $commission['week_start']; ?>">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($commission['rider_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($commission['rider_phone']); ?></small><br>
                                <small>ID: <?php echo $commission['rider_id']; ?></small>
                            </td>
                            <td>
                                Week of <?php 
                                    echo date('M j', strtotime($commission['week_start'])) . ' - ' . 
                                         date('M j, Y', strtotime($commission['week_end'])); 
                                ?>
                            </td>
                            <td><?php echo $commission['delivered_orders']; ?></td>
                            <td style="font-weight: bold; color: #28a745;">
                                <?php echo number_format($commission['rider_commission'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>
                
                <div class="card border-left-primary shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Payment Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="text-gray-800 mb-1">Selected Amount</h6>
                                <div class="h4 font-weight-bold text-success" id="selectedAmount">0.00 MMK</div>
                                <small class="text-muted"><span id="selectedCount">0</span> payouts selected</small>
                            </div>
                            <div class="icon-circle bg-success">
                                <i class="fas fa-money-bill-wave text-white"></i>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_slip" class="form-label">
                                <i class="fas fa-file-upload me-1"></i> Upload Payment Slip (Optional)
                            </label>
                            <input type="file" class="form-control" name="payment_slip" id="payment_slip" 
                                   accept="image/*,.pdf">
                            <div class="form-text">Max 5MB. Supported formats: JPG, PNG, GIF, PDF</div>
                        </div>
                        
                        <input type="hidden" name="confirm_payout" value="1">
                        
                        <button type="button" id="processBtn" class="btn btn-success btn-block py-2" disabled>
                            <i class="fas fa-paper-plane me-2"></i> Process Selected Payouts
                        </button>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Hidden fields will be added by JavaScript -->
        </form>
    </div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-check-circle text-success"></i> Confirm Payout Processing</h3>
        <div class="alert alert-info mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span><i class="fas fa-list-check me-2"></i>Total Payouts:</span>
                <span class="fw-bold" id="modalCount">0</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span><i class="fas fa-money-bill-wave me-2"></i>Total Amount:</span>
                <span class="fw-bold text-success" id="modalAmount">0.00</span> MMK
            </div>
           
        </div>
        
        <div class="mb-4">
           
            
            
        </div>
        
        <div class="d-flex justify-content-between" >
            <button type="button" class="btn btn-secondary" onclick="closeModal()">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
            <button type="button" class="btn btn-success" onclick="submitForm()">
                <i class="fas fa-paper-plane me-1"></i> Confirm & Process
            </button>
        </div>
    </div>
</div>

<script>
// Add icon circle styles
const style = document.createElement('style');
style.textContent = `
    .icon-circle {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 1.5rem;
        border-radius: 0.35rem;
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .table th {
        white-space: nowrap;
    }
`;
document.head.appendChild(style);

// Function to filter riders by name
function filterRiders() {
    const searchTerm = document.getElementById('riderSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#payoutForm tbody tr');
    
    rows.forEach(row => {
        const riderName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (riderName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update select all checkbox state
    updateSelectAllCheckbox();
}

// Update select all checkbox state based on visible rows
function updateSelectAllCheckbox() {
    const selectAll = document.getElementById('selectAll');
    if (!selectAll) return;
    
    const visibleCheckboxes = Array.from(document.querySelectorAll('.payout-checkbox:not([style*="display: none"])'));
    const checkedCheckboxes = visibleCheckboxes.filter(checkbox => checkbox.checked);
    
    if (visibleCheckboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else {
        selectAll.checked = checkedCheckboxes.length === visibleCheckboxes.length;
        selectAll.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < visibleCheckboxes.length;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.payout-checkbox');
    const processBtn = document.getElementById('processBtn');
    const confirmModal = document.getElementById('confirmModal');
    const form = document.getElementById('payoutForm');
    
    let selectedPayouts = [];
    
    // Update selection
    function updateSelection() {
        selectedPayouts = [];
        let totalAmount = 0;
        let count = 0;
        let riderIds = new Set();
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const riderId = checkbox.dataset.rider;
                const period = checkbox.dataset.period;
                const amount = parseFloat(checkbox.dataset.amount);
                const date = checkbox.dataset.date;
                const riderName = checkbox.closest('tr').querySelector('td:nth-child(2)').textContent.split('\n')[0].trim();
                
                selectedPayouts.push({
                    riderId: riderId,
                    riderName: riderName,
                    period: period,
                    amount: amount,
                    date: date
                });
                
                totalAmount += amount;
                count++;
                riderIds.add(riderName);
            }
        });
        
        // Format amount with thousand separators
        const formattedAmount = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(totalAmount);
        
        // Update UI
        const selectedAmountEl = document.getElementById('selectedAmount');
        if (selectedAmountEl) {
            selectedAmountEl.textContent = `${formattedAmount} MMK`;
            // Add animation when amount changes
            selectedAmountEl.classList.add('pulse-animation');
            setTimeout(() => selectedAmountEl.classList.remove('pulse-animation'), 500);
        }
        
        const selectedCountEl = document.getElementById('selectedCount');
        if (selectedCountEl) selectedCountEl.textContent = count;
        
        const modalCountEl = document.getElementById('modalCount');
        if (modalCountEl) modalCountEl.textContent = count;
        
        const modalAmountEl = document.getElementById('modalAmount');
        if (modalAmountEl) modalAmountEl.textContent = formattedAmount;
        
        const modalRidersEl = document.getElementById('modalRiders');
        if (modalRidersEl) {
            const ridersList = Array.from(riderIds);
            if (ridersList.length > 3) {
                modalRidersEl.textContent = `${ridersList.slice(0, 3).join(', ')} and ${ridersList.length - 3} more`;
            } else {
                modalRidersEl.textContent = ridersList.join(', ');
            }
        }
        
        // Enable/disable process button with animation
        if (processBtn) {
            const wasDisabled = processBtn.disabled;
            processBtn.disabled = count === 0;
            
            if (wasDisabled && !processBtn.disabled) {
                // Button was enabled, add animation
                processBtn.classList.add('btn-pulse');
                setTimeout(() => processBtn.classList.remove('btn-pulse'), 1000);
            }
            
            // Update button text based on selection count
            const btnText = count > 0 ? 
                `Process ${count} Payout${count > 1 ? 's' : ''}` : 
                '';
            
            const btnIcon = processBtn.querySelector('i');
            if (btnIcon) {
                btnIcon.className = count > 0 ? 'fas fa-paper-plane me-2' : 'fas fa-hand-pointer me-2';
            }
            
            const btnTextEl = processBtn.querySelector('span');
            if (btnTextEl) {
                btnTextEl.textContent = btnText;
            } else {
                const span = document.createElement('span');
                span.textContent = btnText;
                processBtn.appendChild(span);
            }
        }
        
        // Update select all state
        if (selectAll) {
            const allChecked = count === checkboxes.length && checkboxes.length > 0;
            selectAll.checked = allChecked;
            selectAll.indeterminate = count > 0 && count < checkboxes.length;
        }
    }
    
    // Select all functionality
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelection();
    });
    
    // Individual checkbox functionality
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });
    
    // Process button click
    processBtn.addEventListener('click', function() {
        if (selectedPayouts.length === 0) {
            alert('Please select at least one payout.');
            return;
        }
        
        // Show confirmation with details
        console.log('Selected payouts:', selectedPayouts);
        confirmModal.style.display = 'block';
    });
    
    // Close modal
    window.closeModal = function() {
        confirmModal.style.display = 'none';
    };
    
    // Submit form
    window.submitForm = function() {
        // Clear any existing hidden inputs
        const existingInputs = form.querySelectorAll('input[name="payouts[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add hidden inputs for each selected payout
        selectedPayouts.forEach(payout => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'payouts[]';
            input.value = `${payout.riderId}|${payout.period}|${payout.amount}`;
            form.appendChild(input);
        });
        
        // Submit the form
        console.log('Submitting form with', selectedPayouts.length, 'payouts');
        form.submit();
    };
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === confirmModal) {
            closeModal();
        }
    };
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add animation styles
    const animationStyle = document.createElement('style');
    animationStyle.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .pulse-animation {
            animation: pulse 0.5s ease-in-out;
        }
        .btn-pulse {
            animation: pulse 0.5s ease-in-out;
        }
        .table-hover > tbody > tr {
            transition: background-color 0.2s ease;
        }
        .badge {
            transition: all 0.2s ease;
        }
    `;
    document.head.appendChild(animationStyle)   ;
    
    // Initialize
    updateSelection();

});

</script>

</body>
</html>