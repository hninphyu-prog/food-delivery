<?php
// Start output buffering to prevent headers already sent error
ob_start();
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . "/../../config/db.php"; 

$adminId = $_SESSION['user_id'] ?? null;
$success_message = '';
$error_message = '';

// Check admin authentication
if (!$adminId || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get rejected settlements count for admin notification
$rejected_count = 0;
if ($adminId) {
    $rejected_stmt = $pdo->prepare("
        SELECT COUNT(*) as rejected_count 
        FROM restaurant_settlements 
        WHERE status = 'rejected'
    ");
    $rejected_stmt->execute();
    $rejected_count = $rejected_stmt->fetchColumn();
}

// Check for success/error messages
if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']) {
    $success_message = 'Payments processed successfully!';
    unset($_SESSION['payment_success']);
}

if (isset($_SESSION['payment_error'])) {
    $error_message = $_SESSION['payment_error'];
    unset($_SESSION['payment_error']);
}

// ===== Handle Bulk Pay Action =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    error_log('Payment processing started');
    
    // Validate admin session
    if (!$adminId) {
        $_SESSION['payment_error'] = "Admin session expired. Please login again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if (!empty($_POST['settlements'])) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            $pdo->beginTransaction();
            $payment_details = [];
            
           // Handle slip file upload
            $slip_filename = null;
            $upload_path = null;
            
            if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . "/../../assets/transaction_slips/";
                
                // Create upload directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        throw new Exception("Failed to create upload directory.");
                    }
                }
                
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = $_FILES['payment_slip']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception("Invalid file type. Only JPG, PNG, GIF images are allowed.");
                }
                
                // Validate file size (2MB max)
                if ($_FILES['payment_slip']['size'] > 2 * 1024 * 1024) {
                    throw new Exception("File size too large. Maximum size is 2MB.");
                }
                
                // Generate unique filename - same pattern as admin_rejected_settlements.php
                $file_extension = pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION);
                $slip_filename = 'slip_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $slip_filename;
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['payment_slip']['tmp_name'], $upload_path)) {
                    throw new Exception("Failed to upload payment slip. Check directory permissions.");
                }
                
                error_log("Payment slip uploaded to transaction_slips folder: " . $slip_filename);
            }
            
            // Get settlements from form
            $settlements = $_POST['settlements'];
            if (!is_array($settlements)) {
                $settlements = [$settlements];
            }
            
            error_log('Processing ' . count($settlements) . ' settlements');
            
            foreach ($settlements as $settlement) {
                if (empty($settlement)) continue;
                
                // Parse the settlement data
                $parts = explode('|', $settlement);
                if (count($parts) !== 3) {
                    error_log('Invalid settlement format: ' . $settlement);
                    continue;
                }
                
                $restaurant_id = (int)$parts[0];
                $week_no = (int)$parts[1];
                $amount = (float)$parts[2];
                
                if ($amount <= 0) {
                    error_log('Invalid amount for restaurant ' . $restaurant_id . ': ' . $amount);
                    continue;
                }

                // Check if settlement already exists (excluding rejected ones)
                $check = $pdo->prepare("
                    SELECT id FROM restaurant_settlements 
                    WHERE restaurant_id = ? AND week_no = ? AND status != 'rejected'
                ");
                $check->execute([$restaurant_id, $week_no]);
                $existing = $check->fetch();

                if (!$existing) {
                    // Insert into restaurant_settlements table
                    $notes = "Settlement for week $week_no - Pending confirmation";
                    if ($slip_filename) {
                        $notes .= " | Payment slip uploaded";
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO restaurant_settlements 
                        (restaurant_id, week_no, amount, notes, released_by, status, payment_slip, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())
                    ");
                    
                    $stmt->execute([
                        $restaurant_id,
                        $week_no,
                        $amount,
                        $notes,
                        $adminId,
                         $slip_filename ?: '' 
                    ]);
                    
                    $settlement_id = $pdo->lastInsertId();
                    error_log("Inserted settlement record ID: $settlement_id for restaurant $restaurant_id, week $week_no");

                    // Insert into settlement_notifications table
                    $notif_stmt = $pdo->prepare("
                        INSERT INTO settlement_notifications 
                        (restaurant_id, settlement_id, amount, week_no, payment_slip, status, created_at)
                        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                    ");
                    
                    $notif_stmt->execute([
                        $restaurant_id,
                        $settlement_id,
                        $amount,
                        $week_no,
                         $slip_filename ?: '' 
                    ]);
                    
                    $notification_id = $pdo->lastInsertId();
                    error_log("Created notification ID: $notification_id");

                    // Record expense
                    $restaurant_stmt = $pdo->prepare("SELECT name FROM restaurants WHERE restaurant_id = ?");
                    $restaurant_stmt->execute([$restaurant_id]);
                    $restaurant_name = $restaurant_stmt->fetchColumn() ?: "Restaurant $restaurant_id";

                    $exp = $pdo->prepare("
                        INSERT INTO expenses 
                        (category, amount, note, restaurant_id, payment_slip, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $exp->execute([
                        "Restaurant Payout - $restaurant_name", 
                        $amount, 
                        "Settlement for Week $week_no (ID: $settlement_id)", 
                        $restaurant_id,
                         $slip_filename ?: '' 
                    ]);
                    
                    $expense_id = $pdo->lastInsertId();
                    error_log("Created expense record ID: $expense_id");
                    
                    $payment_details[] = [
                        'restaurant_id' => $restaurant_id,
                        'week_no' => $week_no,
                        'amount' => $amount,
                        'status' => 'pending',
                        'settlement_id' => $settlement_id,
                        'payment_slip' => $slip_filename,
                        'success' => true
                    ];
                    
                } else {
                    // Update existing record
                    $update_notes = "Re-initiated on " . date('Y-m-d H:i:s');
                    if ($slip_filename) {
                        $update_notes .= " | New payment slip uploaded";
                    }
                    
                    $update_stmt = $pdo->prepare("
                        UPDATE restaurant_settlements 
                        SET status = 'pending', 
                            amount = ?,
                            notes = CONCAT(COALESCE(notes, ''), '\n', ?),
                            payment_slip = COALESCE(?, payment_slip),
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $update_stmt->execute([$amount, $update_notes,  $slip_filename ?: '' , $existing['id']]);
                    
                    $affected_rows = $update_stmt->rowCount();
                    error_log("Updated existing settlement ID: " . $existing['id'] . " - Affected rows: " . $affected_rows);
                    
                    // Also update notification
                    $notif_update = $pdo->prepare("
                        UPDATE settlement_notifications 
                        SET status = 'pending', 
                            payment_slip = COALESCE(?, payment_slip),
                            rejection_reason = NULL,
                            rejected_at = NULL
                        WHERE settlement_id = ?
                    ");
                    $notif_update->execute([$slip_filename, $existing['id']]);
                    
                    $payment_details[] = [
                        'restaurant_id' => $restaurant_id,
                        'week_no' => $week_no,
                        'amount' => $amount,
                        'status' => 'updated',
                        'settlement_id' => $existing['id'],
                        'payment_slip' => $slip_filename
                    ];
                }
            }
            
            // Check if any settlements were processed
            if (empty($payment_details)) {
                throw new Exception("No valid settlements were processed. Please check your selection.");
            }
            
            $pdo->commit();
            error_log('Transaction committed successfully. Processed ' . count($payment_details) . ' settlements');
            
            $_SESSION['payment_results'] = $payment_details;
            $_SESSION['payment_success'] = true;
            
            // Clear output buffer and redirect
            ob_end_clean();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            // Delete uploaded file if transaction failed
            if (isset($slip_filename) && isset($upload_path) && file_exists($upload_path)) {
                unlink($upload_path);
                error_log("Deleted uploaded slip due to transaction failure: " . $slip_filename);
            }
            
            error_log("Payment processing error: " . $e->getMessage());
            error_log("Error in file: " . $e->getFile() . " on line: " . $e->getLine());
            $_SESSION['payment_error'] = "Error processing payments: " . $e->getMessage();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['payment_error'] = "No settlements selected for payment.";
        header("Location: weekly_settlements_export.php");
        exit();
    }
}

// ===== Fetch unpaid weekly settlements =====
$sql = "
SELECT 
    o.restaurant_id,
    r.name AS restaurant_name,
    YEARWEEK(o.created_at,1) AS week_no,
    ROUND(SUM(o.subtotal)*0.75,2) AS settlement_amount,
    COUNT(o.order_id) as order_count,
    SUM(o.subtotal) as total_sales,
    SUM(o.subtotal)*0.25 as admin_commission
FROM orders o
JOIN restaurants r ON o.restaurant_id = r.restaurant_id
LEFT JOIN restaurant_settlements rs ON rs.restaurant_id = o.restaurant_id 
    AND rs.week_no = YEARWEEK(o.created_at,1)
    AND rs.status IN ('completed', 'pending')  -- Only check completed or pending settlements
WHERE o.order_status = 'delivered'
    AND o.payment_status = 'paid'
    AND rs.id IS NULL  -- Only show if no completed/pending settlement exists
GROUP BY o.restaurant_id, YEARWEEK(o.created_at,1)
HAVING settlement_amount > 0
ORDER BY week_no DESC, r.name ASC;
";

$stmt = $pdo->query($sql);
$unpaid_settlements = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_payout = array_sum(array_column($unpaid_settlements, 'settlement_amount'));
$total_commission = array_sum(array_column($unpaid_settlements, 'admin_commission'));
?>
<?php include "includes/header.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Unpaid Weekly Settlements</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body{font-family:Arial;margin:20px;background:#f4f4f4;}
.main-content{background:white;padding:20px;border-radius:5px;box-shadow:0 1px 3px rgba(0,0,0,0.1);}
table{border-collapse:collapse;width:100%;margin-top:20px;font-size:14px;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#f4f4f4;font-weight:bold;}
button{padding:6px 12px;margin:5px;border:none;cursor:pointer;border-radius:4px;font-size:14px;}
.btn-primary{background:#007bff;color:#fff;}
.btn-success{background:#28a745;color:#fff;}
.btn-danger{background:#dc3545;color:#fff;}
.btn-secondary{background:#6c757d;color:#fff;}
.btn-info{background:#17a2b8;color:#fff;}
button:disabled{background:#999;cursor:not-allowed;}
button:hover:not(:disabled){opacity:0.9;}
.select-all{cursor:pointer;}
.total{margin-top:15px;font-weight:bold;}
.summary-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
}
.summary-card h4 {
    margin: 0 0 10px 0;
    color: #495057;
    font-size: 16px;
}
.confirmation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}
.confirmation-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 700px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}
.payment-success {background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:10px;border-radius:5px;margin:10px 0;font-size:14px;}
.payment-error {background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px;border-radius:5px;margin:10px 0;font-size:14px;}
.text-muted{color:#6c757d;font-size:12px;}
.badge{display:inline-block;padding:3px 8px;font-size:12px;font-weight:bold;border-radius:4px;margin:0 2px;}
.bg-primary{background:#007bff;color:white;}
.bg-info{background:#17a2b8;color:white;}
.bg-warning{background:#ffc107;color:black;}
.bg-success{background:#28a745;color:white;}
.bg-danger{background:#dc3545;color:white;}
.slip-section {
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}
.slip-preview {
    max-width: 200px;
    max-height: 150px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 10px 0;
    display: none;
}
.slip-info {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
}
.admin-notification {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 14px;
}
.form-text {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}
</style>
</head>

<body>
    <div class="main-content">
        <?php if (!empty($success_message)): ?>
            <div class="payment-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="payment-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0;"><i class="fas fa-money-bill-wave"></i> Unpaid Weekly Settlements</h2>
    <div class="form-group" style="margin: 0; width: 300px;">
        <input type="text" id="restaurantFilter" class="form-control" 
               placeholder="Filter by restaurant name..." 
               style="padding: 8px 12px; font-size: 14px;">
    </div>
</div>

        <!-- Admin Notification for Rejected Settlements -->
        <?php if ($rejected_count > 0): ?>
            <div class="admin-notification">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention:</strong> There are <?= $rejected_count ?> rejected settlements that need review.
                <a href="admin_rejected_settlements.php" class="btn btn-sm btn-warning ms-2">
                    <i class="fas fa-eye"></i> View Rejected Settlements
                </a>
            </div>
        <?php endif; ?>

    <!-- Summary Cards -->
    <div class="summary-card">
        <h4><i class="fas fa-chart-bar"></i> Settlement Summary</h4>
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div>
                <strong>Total Pending Payout:</strong><br>
                <span style="font-size: 18px; color: #28a745;"><?= number_format($total_payout,2) ?> MMK</span>
            </div>
            <div>
                <strong>Admin Commission:</strong><br>
                <span style="font-size: 18px; color: #dc3545;"><?= number_format($total_commission,2) ?> MMK</span>
            </div>
            <div>
                <strong>Total Sales:</strong><br>
                <span style="font-size: 18px; color: #007bff;"><?= number_format(array_sum(array_column($unpaid_settlements, 'total_sales')),2) ?> MMK</span>
            </div>
            <div>
                <strong>Pending Weeks:</strong><br>
                <span style="font-size: 18px; color: #6c757d;"><?= count($unpaid_settlements) ?></span>
            </div>
            <?php if ($rejected_count > 0): ?>
            <div>
                <strong>Rejected Settlements:</strong><br>
                <span style="font-size: 18px; color: #dc3545;"><?= $rejected_count ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Selected Items Summary -->
        <div id="selectedSummary" style="margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 5px; display: none;">
            <h5 style="margin: 0 0 8px 0; font-size: 14px;"><i class="fas fa-list"></i> Selected for Payment:</h5>
            <div style="display: flex; gap: 15px; flex-wrap: wrap; font-size: 13px;">
                <div><strong>Settlements:</strong> <span id="selectedCount" class="badge bg-primary">0</span></div>
                <div><strong>Restaurants:</strong> <span id="selectedRestaurants" class="badge bg-info">0</span></div>
                <div><strong>Weeks:</strong> <span id="selectedWeeks" class="badge bg-warning">0</span></div>
                <div><strong>Total Amount:</strong> <span id="selectedAmount" class="badge bg-success">0.00 MMK</span></div>
            </div>
        </div>
    </div>

    <?php if(!empty($unpaid_settlements)): ?>
    <!-- Filter Section -->
    
   
    
    <form method="post" id="mainForm" enctype="multipart/form-data">
    <table>
    <tr>
    <th width="50"><input type="checkbox" class="select-all" onclick="toggleSelectAll(this)"> Select All</th>
    <th>Restaurant</th>
    <th>Week No</th>
    <th>Orders</th>
    <th>Total Sales</th>
    <th>Restaurant (75%)</th>
    <th>Admin (25%)</th>
    </tr>
    <?php foreach($unpaid_settlements as $row): ?>
    <tr>
    <td><input type="checkbox" name="settlements[]" value="<?= $row['restaurant_id'] ?>|<?= $row['week_no'] ?>|<?= $row['settlement_amount'] ?>" onchange="updateSelectedSummary()"></td>
    <td>
        <strong><?= htmlspecialchars($row['restaurant_name']) ?></strong><br>
        <small class="text-muted">ID: <?= $row['restaurant_id'] ?></small>
    </td>
    <td><?= $row['week_no'] ?></td>
    <td><?= $row['order_count'] ?></td>
    <td><?= number_format($row['total_sales'],2) ?> MMK</td>
    <td style="color: #28a745; font-weight: bold;"><?= number_format($row['settlement_amount'],2) ?> MMK</td>
    <td style="color: #dc3545;"><?= number_format($row['admin_commission'],2) ?> MMK</td>
    </tr>
    <?php endforeach; ?>
    </table>
    
    <div style="margin-top:20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" onclick="showConfirmationModal()" id="payButton" class="btn btn-success" disabled>
            <i class="fas fa-paper-plane"></i> Process Selected Payments
        </button>
        
        <button type="submit" name="export_excel" class="btn btn-primary">
            <i class="fas fa-file-excel"></i> Export to Excel
        </button>
        
        <?php if ($rejected_count > 0): ?>
            <a href="admin_rejected_settlements.php" class="btn btn-warning">
                <i class="fas fa-exclamation-triangle"></i> View Rejected (<?= $rejected_count ?>)
            </a>
        <?php endif; ?>
       
    </div>
    
    <!-- Hidden input for form submission -->
    <input type="hidden" name="confirm_payment" value="1">
    </form>
    <?php else: ?>
    <div style="text-align: center; padding: 40px;">
        <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
        <p style="color:green; padding: 15px; background: #d4edda; border-radius: 5px; margin-top: 20px;">
            ✅ All settlements are paid up to date. No pending payouts.
        </p>
    </div>
    <?php endif; ?>
    </div>

    <!-- Confirmation Modal with Slip Upload Inside -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="confirmation-content">
            <h3 style="color: #28a745; margin-bottom: 15px; font-size: 18px;">
                <i class="fas fa-paper-plane"></i> Initiate Payment Process
            </h3>
            <p style="font-size: 14px;">You are about to initiate the payment process for the selected settlements. This will notify restaurants to confirm receipt of payment.</p>
            
            <!-- Payment Details -->
            <div id="paymentSummary" style="margin: 15px 0; padding: 12px; background: #f8f9fa; border-radius: 5px;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px;"><i class="fas fa-list"></i> Payment Details</h4>
                <div id="paymentDetails" style="max-height: 200px; overflow-y: auto; margin: 8px 0; font-size: 13px;">
                    <!-- Payment details will be inserted here -->
                </div>
                <div class="total" style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #dee2e6; font-size: 14px;">
                    <strong>Total Amount to Pay: <span id="totalPaymentAmount" style="color: #28a745;">0.00</span> MMK</strong>
                </div>
            </div>
            
            <!-- Payment Slip Section INSIDE Modal -->
            <div class="slip-section">
                <h4 style="margin: 0 0 10px 0; font-size: 16px;"><i class="fas fa-receipt"></i> Payment Slip (Optional)</h4>
                <p style="font-size: 13px; color: #6c757d; margin-bottom: 10px;">
                    Add payment slip as proof of transaction. Supported formats: JPG, PNG, GIF (Max: 2MB)
                </p>
                
                <div style="margin-bottom: 15px;">
                    <label for="paymentSlip" class="form-label">Select Payment Slip:</label>
                    <input type="file" id="paymentSlip" name="payment_slip" accept="image/*" style="display: none;" onchange="previewSlip(this)">
                    
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <button type="button" onclick="document.getElementById('paymentSlip').click()" class="btn btn-info">
                            <i class="fas fa-upload"></i> Choose File
                        </button>
                        <span id="fileName" style="font-size: 13px; color: #6c757d;">No file chosen</span>
                    </div>
                    
                    <div class="form-text">
                        Supported formats: JPG, PNG, GIF (Max: 2MB)
                    </div>
                </div>
                
                <!-- Slip Preview -->
                <img id="slipPreview" class="slip-preview" src="" alt="Slip preview">
                <div id="slipFileName" class="slip-info"></div>
                
                <button type="button" onclick="removeSlip()" class="btn btn-secondary" style="display: none; margin-top: 10px;" id="removeSlipBtn">
                    <i class="fas fa-times"></i> Remove Slip
                </button>
            </div>
            
           
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <button type="button" class="btn btn-secondary" onclick="hideConfirmationModal()" style="padding: 6px 12px;">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmPayment()" style="padding: 6px 12px;">
                    <i class="fas fa-paper-plane"></i> Initiate Payment Process
                </button>
            </div>
        </div>
    </div>

<script>
// Table filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    const restaurantFilter = document.getElementById('restaurantFilter');
    
    if (restaurantFilter) {
        restaurantFilter.addEventListener('input', function() {
            const filterText = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tr:not(:first-child)'); // Skip header row
            
            rows.forEach(row => {
                // Get the restaurant name from the second column (index 1)
                const restaurantName = row.cells[1]?.textContent?.toLowerCase() || '';
                if (restaurantName.includes(filterText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});

let selectedSettlements = [];
let hasSlip = false;

function toggleSelectAll(source){
    let checkboxes = document.querySelectorAll('input[name="settlements[]"]');
    checkboxes.forEach(cb => {
        cb.checked = source.checked;
    });
    updateSelectedSummary();
    togglePayButton();
}

function togglePayButton(){
    let checkboxes = document.querySelectorAll('input[name="settlements[]"]');
    let btn = document.getElementById('payButton');
    let selectedSummary = document.getElementById('selectedSummary');
    
    // Add null checks to prevent errors
    if (!btn || !selectedSummary) return;
    
    const hasChecked = Array.from(checkboxes).some(cb => cb.checked);
    btn.disabled = !hasChecked;
    
    // Show/hide selected summary
    if (hasChecked) {
        selectedSummary.style.display = 'block';
    } else {
        selectedSummary.style.display = 'none';
    }
}

function updateSelectedSummary() {
    const checkboxes = document.querySelectorAll('input[name="settlements[]"]:checked');
    selectedSettlements = Array.from(checkboxes).map(cb => cb.value);
    
    let totalAmount = 0;
    let restaurantCount = new Set();
    let weekCount = new Set();
    
    selectedSettlements.forEach(settlement => {
        const parts = settlement.split('|');
        if (parts.length === 3) {
            totalAmount += parseFloat(parts[2]);
            restaurantCount.add(parts[0]);
            weekCount.add(parts[1]);
        }
    });
    
    // Add null checks for all elements
    const selectedCountEl = document.getElementById('selectedCount');
    const selectedRestaurantsEl = document.getElementById('selectedRestaurants');
    const selectedWeeksEl = document.getElementById('selectedWeeks');
    const selectedAmountEl = document.getElementById('selectedAmount');
    
    if (selectedCountEl) selectedCountEl.textContent = selectedSettlements.length;
    if (selectedRestaurantsEl) selectedRestaurantsEl.textContent = restaurantCount.size;
    if (selectedWeeksEl) selectedWeeksEl.textContent = weekCount.size;
    if (selectedAmountEl) selectedAmountEl.textContent = totalAmount.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + ' MMK';
    
    togglePayButton();
}

function previewSlip(input) {
    const file = input.files[0];
    if (file) {
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size too large. Maximum size is 2MB.');
            input.value = '';
            document.getElementById('fileName').textContent = 'No file chosen';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Only JPG, PNG, GIF images are allowed.');
            input.value = '';
            document.getElementById('fileName').textContent = 'No file chosen';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('slipPreview').src = e.target.result;
            document.getElementById('slipPreview').style.display = 'block';
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('slipFileName').textContent = 'File: ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
            document.getElementById('removeSlipBtn').style.display = 'inline-block';
            hasSlip = true;
        }
        reader.readAsDataURL(file);
    }
}

function removeSlip() {
    document.getElementById('paymentSlip').value = '';
    document.getElementById('slipPreview').style.display = 'none';
    document.getElementById('fileName').textContent = 'No file chosen';
    document.getElementById('slipFileName').textContent = '';
    document.getElementById('removeSlipBtn').style.display = 'none';
    hasSlip = false;
}

function showConfirmationModal() {
    if (selectedSettlements.length === 0) {
        alert('Please select at least one settlement to process.');
        return;
    }

    const paymentDetails = document.getElementById('paymentDetails');
    const totalAmountEl = document.getElementById('totalPaymentAmount');
    
    let totalAmount = 0;
    let restaurantCount = new Set();
    let weekCount = new Set();
    let detailsHTML = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px;">';
    detailsHTML += '<thead><tr style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">';
    detailsHTML += '<th style="padding: 6px; text-align: left;">Restaurant</th>';
    detailsHTML += '<th style="padding: 6px; text-align: left;">Week</th>';
    detailsHTML += '<th style="padding: 6px; text-align: right;">Amount</th>';
    detailsHTML += '</tr></thead><tbody>';

    selectedSettlements.forEach(settlement => {
        const parts = settlement.split('|');
        if (parts.length === 3) {
            const amount = parseFloat(parts[2]);
            totalAmount += amount;
            restaurantCount.add(parts[0]);
            weekCount.add(parts[1]);
            
            // Get restaurant name from the table row
            const checkbox = document.querySelector(`input[value="${settlement}"]`);
            const row = checkbox ? checkbox.closest('tr') : null;
            const restaurantName = row ? (row.cells[1]?.textContent.trim().split('\n')[0] || `Restaurant ${parts[0]}`) : `Restaurant ${parts[0]}`;
            
            detailsHTML += `
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 6px;">${restaurantName}</td>
                    <td style="padding: 6px;">Week ${parts[1]}</td>
                    <td style="padding: 6px; text-align: right;">${amount.toFixed(2)} MMK</td>
                </tr>
            `;
        }
    });

    detailsHTML += '</tbody></table>';
    detailsHTML += `<div style="background: #f8f9fa; padding: 6px; border-radius: 4px; margin-top: 8px; font-size: 12px;">
        <strong>Summary:</strong> 
        ${restaurantCount.size} restaurants, 
        ${weekCount.size} weeks
    </div>`;

    paymentDetails.innerHTML = detailsHTML;
    totalAmountEl.textContent = totalAmount.toFixed(2);
    
    // Reset slip section when modal opens
    removeSlip();
    
    // Show the modal
    document.getElementById('confirmationModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function hideConfirmationModal() {
    document.getElementById('confirmationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function confirmPayment() {
    if (selectedSettlements.length === 0) {
        alert('Please select at least one settlement.');
        return;
    }

    // Submit the form directly
    document.getElementById('mainForm').submit();
}

// Close modal when clicking outside
document.getElementById('confirmationModal').addEventListener('click', function(e) {
    if (e.target.id === 'confirmationModal') {
        hideConfirmationModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideConfirmationModal();
    }
});

// Initialize - Only run if this page has settlement checkboxes
document.addEventListener('DOMContentLoaded', function(){
    let checkboxes = document.querySelectorAll('input[name="settlements[]"]');
    
    // Only run if this page has settlement checkboxes
    if (checkboxes.length > 0) {
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateSelectedSummary();
            });
        });
        updateSelectedSummary();
    }
});
</script>
</body>
</html>