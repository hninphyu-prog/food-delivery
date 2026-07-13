<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_deposits') {
    // Get current week start and end dates
    $currentWeekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
    $currentWeekEnd = date('Y-m-d 23:59:59', strtotime('sunday this week'));
    
    $sql = "
    SELECT 
        rd.id,
        rd.rider_id,
        rd.order_id,
        rd.amount,
        rd.deposit_txn_id,
        rd.deposited_at,
        rd.verified,
        rd.status,
        rd.rejection_reason,
        rd.transaction_slip,
        u.name as rider_name,
        u.phone as rider_phone,
        o.restaurant_id,
        r.name as restaurant_name,
        rd.verified_by,
        rd.verified_at,
        admin.name as verified_by_name
    FROM rider_deposits rd
    JOIN users u ON rd.rider_id = u.user_id
    JOIN orders o ON rd.order_id = o.order_id
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    LEFT JOIN users admin ON rd.verified_by = admin.user_id
    ORDER BY rd.verified ASC, rd.deposited_at DESC
    ";

    $stmt = $pdo->query($sql);
    $all_deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter for current week verified deposits only
    $current_week_verified = array_filter($all_deposits, function($d) use ($currentWeekStart, $currentWeekEnd) {
        return $d['verified'] && 
               $d['verified_at'] >= $currentWeekStart && 
               $d['verified_at'] <= $currentWeekEnd;
    });

    $pending_deposits = array_filter($all_deposits, fn($d) => !$d['verified'] && $d['status'] != 'rejected');
    $verified_deposits = array_values($current_week_verified);

    $total_pending = array_sum(array_column($pending_deposits, 'amount'));
    $total_verified = array_sum(array_column($verified_deposits, 'amount'));

    if (ob_get_length()) ob_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'pending_deposits' => array_values($pending_deposits),
        'verified_deposits' => $verified_deposits,
        'total_pending' => $total_pending,
        'total_verified' => $total_verified,
        'pending_count' => count($pending_deposits),
        'verified_count' => count($verified_deposits),
        'current_week_start' => $currentWeekStart,
        'current_week_end' => $currentWeekEnd,
        'timestamp' => time()
    ]);
    exit;
}

// Handle AJAX for pending COD deposits
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_pending_cod') {
    $rider_filter = $_GET['rider'] ?? '';
    
    $sql = "
    SELECT 
        d.delivery_boy_id as rider_id,
        u.name as rider_name,
        u.phone as rider_phone,
        COUNT(o.order_id) as pending_orders_count,
        SUM(o.total_amount) as total_order_amount,
        SUM(o.delivery_fee) as total_delivery_fee,
        SUM(o.total_amount) as total_amount_due,
        MAX(o.created_at) as latest_delivery_date,
        MIN(o.created_at) as earliest_delivery_date,
        DATEDIFF(NOW(), MAX(o.created_at)) as days_outstanding
    FROM delivery d
    JOIN orders o ON d.order_id = o.order_id
    JOIN users u ON d.delivery_boy_id = u.user_id
    LEFT JOIN rider_deposits rd ON o.order_id = rd.order_id
    WHERE o.payment_method = 'cod' 
    AND o.order_status = 'delivered'
    AND rd.id IS NULL  
    AND u.role = 'delivery'
    ";
    
    $params = [];
    
    if (!empty($rider_filter)) {
        $sql .= " AND u.name LIKE ?";
        $params[] = "%$rider_filter%";
    }
    
    $sql .= " GROUP BY d.delivery_boy_id, u.name, u.phone
              ORDER BY total_amount_due DESC, days_outstanding DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pending_cod_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $total_pending_amount = array_sum(array_column($pending_cod_data, 'total_amount_due'));
    $total_pending_orders = array_sum(array_column($pending_cod_data, 'pending_orders_count'));
    $total_riders = count($pending_cod_data);

    if (ob_get_length()) ob_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'pending_cod_data' => $pending_cod_data,
        'summary' => [
            'total_pending_amount' => $total_pending_amount,
            'total_pending_orders' => $total_pending_orders,
            'total_riders' => $total_riders
        ]
    ]);
    exit;
}

// Handle AJAX for history data with filters
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_history') {
    $rider_filter = $_GET['rider'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $restaurant_filter = $_GET['restaurant'] ?? '';
    
    $sql = "
    SELECT 
        rd.id,
        rd.rider_id,
        rd.order_id,
        rd.amount,
        rd.deposit_txn_id,
        rd.deposited_at,
        rd.verified,
        rd.transaction_slip,
        u.name as rider_name,
        u.phone as rider_phone,
        o.restaurant_id,
        r.name as restaurant_name,
        rd.verified_by,
        rd.verified_at,
        admin.name as verified_by_name
    FROM rider_deposits rd
    JOIN users u ON rd.rider_id = u.user_id
    JOIN orders o ON rd.order_id = o.order_id
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    LEFT JOIN users admin ON rd.verified_by = admin.user_id
    WHERE rd.verified = 1
    ";
    
    $params = [];
    
    if (!empty($rider_filter)) {
        $sql .= " AND u.name LIKE ?";
        $params[] = "%$rider_filter%";
    }
    
    if (!empty($restaurant_filter)) {
        $sql .= " AND r.name LIKE ?";
        $params[] = "%$restaurant_filter%";
    }
    
    if (!empty($date_from)) {
        $sql .= " AND DATE(rd.verified_at) >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $sql .= " AND DATE(rd.verified_at) <= ?";
        $params[] = $date_to;
    }
    
    $sql .= " ORDER BY rd.verified_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $history_deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_history_amount = array_sum(array_column($history_deposits, 'amount'));

    if (ob_get_length()) ob_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'history_deposits' => $history_deposits,
        'total_history_amount' => $total_history_amount,
        'history_count' => count($history_deposits),
        'filters' => [
            'rider' => $rider_filter,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'restaurant' => $restaurant_filter
        ]
    ]);
    exit;
}

// Handle deposit approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_deposit'])) {
    $deposit_id = $_POST['deposit_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if ($deposit_id && in_array($action, ['approve', 'reject'])) {
        $verified = $action === 'approve' ? 1 : 0;
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $verified_by = $_SESSION['admin_id'] ?? 4;
        
        $stmt = $pdo->prepare("
            UPDATE rider_deposits 
            SET verified = ?, status = ?, verified_by = ?, verified_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$verified, $status, $verified_by, $deposit_id]);
        
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $action === 'approve' ? 'Deposit approved successfully' : 'Deposit rejected'
        ]);
        exit;
    }
}

// Handle deposit rejection with reason
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_deposit'])) {
    $deposit_id = $_POST['deposit_id'] ?? null;
    $rejection_reason = $_POST['rejection_reason'] ?? '';
    
    if (!$deposit_id || empty($rejection_reason)) {
        echo json_encode(['success' => false, 'message' => 'Deposit ID and rejection reason are required']);
        exit;
    }
    
    try {
        $verified_by = $_SESSION['admin_id'] ?? 4;
        
        $stmt = $pdo->prepare("
            UPDATE rider_deposits 
            SET verified = 0, 
                status = 'rejected', 
                rejection_reason = ?,
                verified_by = ?,
                verified_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([$rejection_reason, $verified_by, $deposit_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Deposit rejected successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Continue with normal page load...
include 'includes/header.php';
$adminId = $_SESSION['admin_id'] ?? 4;

$currentWeekStart = date('M j, Y', strtotime('monday this week'));
$currentWeekEnd = date('M j, Y', strtotime('sunday this week'));

$sql = "
SELECT 
    rd.id,
    rd.rider_id,
    rd.order_id,
    rd.amount,
    rd.deposit_txn_id,
    rd.deposited_at,
    rd.verified,
    rd.status,
    rd.rejection_reason,
    rd.transaction_slip,
    u.name as rider_name,
    u.phone as rider_phone,
    o.restaurant_id,
    r.name as restaurant_name,
    rd.verified_by,
    rd.verified_at,
    admin.name as verified_by_name
FROM rider_deposits rd
JOIN users u ON rd.rider_id = u.user_id
JOIN orders o ON rd.order_id = o.order_id
JOIN restaurants r ON o.restaurant_id = r.restaurant_id
LEFT JOIN users admin ON rd.verified_by = admin.user_id
ORDER BY rd.verified ASC, rd.deposited_at DESC
";

$stmt = $pdo->query($sql);
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentWeekStartDB = date('Y-m-d 00:00:00', strtotime('monday this week'));
$currentWeekEndDB = date('Y-m-d 23:59:59', strtotime('sunday this week'));

$pending_deposits = array_filter($deposits, fn($d) => !$d['verified'] && $d['status'] != 'rejected');
$verified_deposits = array_filter($deposits, function($d) use ($currentWeekStartDB, $currentWeekEndDB) {
    return $d['verified'] && 
           $d['verified_at'] >= $currentWeekStartDB && 
           $d['verified_at'] <= $currentWeekEndDB;
});

$total_pending = array_sum(array_column($pending_deposits, 'amount'));
$total_verified = array_sum(array_column($verified_deposits, 'amount'));
?>

<style>
    .summary-cards { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
    .summary-card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; min-width: 200px; }
    .summary-card .amount { font-size: 24px; font-weight: bold; }
    
    table { border-collapse: collapse; width: 100%; margin-top: 20px; background: white; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #f4f4f4; }
    .btn { padding: 6px 12px; margin: 2px; border: none; cursor: pointer; border-radius: 4px; }
    .btn-approve { background: #28a745; color: white; }
    .btn-reject { background: #dc3545; color: white; }
    .btn-view { background: #17a2b8; color: white; }
    .btn-refresh { background: #007bff; color: white; padding: 8px 16px; margin-bottom: 15px; }
    .btn-history { background: #007bff; color: white; padding: 8px 16px; margin-bottom: 15px; margin-left: 10px; }
    
    .modal { 
        display: none; 
        position: fixed; 
        z-index: 2000;
        left: 0; 
        top: 0; 
        width: 100%; 
        height: 100%; 
        background-color: rgba(0,0,0,0.5); 
    }
    .modal-content { 
        background-color: white; 
        margin: 5% auto; 
        padding: 20px; 
        border-radius: 8px; 
        width: 95%; 
        max-width: 1200px;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2); 
    }
    .modal-lg { max-width: 1200px; }
    .modal-success { border-top: 4px solid #28a745; }
    .modal-icon { font-size: 48px; margin-bottom: 15px; }
    .modal-close { 
        background: red; 
        color: white; 
        border: none; 
        padding: 8px 16px; 
        border-radius: 4px; 
        cursor: pointer; 
        margin-top: 15px; 
    }
    .loading { 
        display: none; 
        text-align: center; 
        padding: 10px; 
        color: #007bff; 
    }
    .last-update {
        font-size: 12px;
        color: #666;
        margin-left: 10px;
    }
    .filter-bar {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    .filter-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: end;
    }
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    .filter-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 14px;
    }
    .filter-group input, .filter-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .filter-actions {
        display: flex;
        gap: 10px;
    }
    .week-info {
        background: #e7f3ff;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
       
    }
    .history-summary {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
    }
    .slip-image {
        max-width: 100%;
        max-height: 500px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }
    .badge-danger {
        background-color: #dc3545;
    }
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    .badge-info {
        background-color: #17a2b8;
    }
    .badge-success {
        background-color: #28a745;
    }
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
    }
</style>

<div class="main-content">
    <h2>Rider COD Deposits Approval</h2>
    
    <div style="margin-bottom: 15px;">
        <button class="btn btn-refresh" onclick="manualRefresh()"> Refresh</button>
        <button class="btn btn-history" onclick="openHistoryModal()"> View History</button>
        <button class="btn btn-history" onclick="openPendingCODModal()"> Get COD Deposits From</button>
    </div>
    
    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content modal-success">
            <div class="modal-icon">✅</div>
            <h3>Success!</h3>
            <p id="modalMessage"></p>
            <button class="modal-close" onclick="closeModal()">OK</button>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3>❌ Reject Deposit</h3>
            <p><strong>Deposit ID: </strong><span id="rejectDepositId"></span></p>
            <p><strong>Amount: </strong><span id="rejectDepositAmount"></span></p>
            <p><strong>Rider: </strong><span id="rejectRiderName"></span></p>
            
            <div style="margin: 15px 0;">
                <label><strong>Rejection Reason:</strong></label>
                <textarea id="rejectionReason" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" 
                         placeholder="Please provide reason for rejection..."></textarea>
            </div>
            
            <div style="text-align: right;">
                <button class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button class="btn btn-danger" onclick="confirmRejectDeposit()">Confirm Rejection</button>
            </div>
        </div>
    </div>

    <!-- Slip View Modal -->
    <div id="slipModal" class="modal">
        <div class="modal-content modal-lg">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3>📄 Transaction Slip</h3>
                <button class="modal-close" onclick="closeSlipModal()">✕</button>
            </div>
            <div id="slipContent" style="text-align: center;">
                <!-- Slip content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <div id="historyModal" class="modal">
        <div class="modal-content modal-lg">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">Deposit History</h3>
                <button class="modal-close" onclick="closeHistoryModal()" style="margin: 0;">Close</button>
            </div>
            
            <div class="filter-bar">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Rider Name</label>
                        <input type="text" id="filterRider" placeholder="Search rider...">
                    </div>
                    <div class="filter-group">
                        <label>Restaurant</label>
                        <input type="text" id="filterRestaurant" placeholder="Search restaurant...">
                    </div>
                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" id="filterDateFrom">
                    </div>
                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" id="filterDateTo">
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary" onclick="applyHistoryFilters()">🔍 Apply Filters</button>
                        <button class="btn btn-warning" onclick="clearHistoryFilters()">Clear</button>
                    </div>
                </div>
            </div>
            
            <div class="history-summary">
                <strong> Summary:</strong>
                <span id="historySummary">Loading...</span>
            </div>
            
            <div id="historyLoading" class="loading">Loading history data...</div>
            
            <div id="historyTableContainer">
                <table>
                    <thead>
                        <tr>
                            <th>Deposit ID</th>
                            <th>Rider</th>
                            <th>Order ID</th>
                            <th>Restaurant</th>
                            <th>Amount</th>
                            <th>Verified Date</th>
                            <th>Verified By</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <tr><td colspan="7" style="text-align: center;">Use filters to load history data</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending COD Deposits Modal -->
    <div id="pendingCODModal" class="modal">
        <div class="modal-content modal-lg">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">Pending COD Deposits</h3>
                <button class="modal-close" onclick="closePendingCODModal()" style="margin: 0;">Close</button>
            </div>
            
            <div class="filter-bar">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Search Rider</label>
                        <input type="text" id="filterPendingRider" placeholder="Search rider name...">
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary" onclick="loadPendingCODData()">🔍 Search</button>
                        <button class="btn btn-warning" onclick="clearPendingCODFilters()"> Clear</button>
                   
                    </div>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="amount" id="totalPendingAmount">0 MMK</div>
                    <div>Total Pending Amount</div>
                </div>
                <div class="summary-card">
                    <div class="amount" id="totalPendingOrders">0</div>
                    <div>Pending Orders</div>
                </div>
                <div class="summary-card">
                    <div class="amount" id="totalRidersCount">0</div>
                    <div>Riders with Pending COD</div>
                </div>
            </div>
            
            <div id="pendingCODLoading" class="loading">Loading pending COD data...</div>
            
            <div id="pendingCODTableContainer">
                <table>
                    <thead>
                        <tr>
                            <th>Rider Name</th>
                            <th>Contact</th>
                            <th>Pending Orders</th>
                            <th>Order Amount</th>
                            <th>Delivery Fee</th>
                            <th>Total Due</th>
                            <th>Latest Delivery</th>
                            <th>Days Outstanding</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingCODTableBody">
                        <tr><td colspan="9" style="text-align: center;">Use search to load data</td></tr>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                <small>💡 <strong>Note:</strong> This shows delivered COD orders that riders haven't deposited yet.</small>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="loading"> Processing... Please wait</div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card pending">
            <div class="amount" id="totalPending"><?= number_format($total_pending) ?> MMK</div>
            <div>Pending Verification</div>
            <small id="pendingCount"><?= count($pending_deposits) ?> deposits</small>
        </div>
        <div class="summary-card verified">
            <div class="amount" id="totalVerified"><?= number_format($total_verified) ?> MMK</div>
            <div>Verified This Week</div>
            <small id="verifiedCount"><?= count($verified_deposits) ?> deposits</small>
            <small style="color: #666; display: block; margin-top: 5px;">
                (<?= $currentWeekStart ?> - <?= $currentWeekEnd ?>)
            </small>
        </div>
    </div>

    <!-- Week Info -->
    <div class="week-info">
        <strong>Current Week:</strong> <?= $currentWeekStart ?> to <?= $currentWeekEnd ?>
    </div>

    <!-- Pending Deposits -->
    <h3> Pending Verification (<span id="pendingHeaderCount"><?= count($pending_deposits) ?></span>)</h3>
    <div id="pendingDepositsTable">
        <?php if (!empty($pending_deposits)): ?>
        <table>
            <thead>
                <tr>
                    <th>Deposit ID</th>
                    <th>Rider</th>
                    <th>Order ID</th>
                    <th>Restaurant</th>
                    <th>Amount</th>
                    <th>Transaction Slip</th>
                    <th>Deposit Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingDepositsBody">
                <?php foreach ($pending_deposits as $deposit): ?>
                <tr id="deposit-<?= $deposit['id'] ?>">
                    <td><?= $deposit['deposit_txn_id'] ?></td>
                    <td><?= htmlspecialchars($deposit['rider_name']) ?><br><small><?= $deposit['rider_phone'] ?></small></td>
                    <td>#<?= $deposit['order_id'] ?></td>
                    <td><?= htmlspecialchars($deposit['restaurant_name']) ?></td>
                    <td style="font-weight: bold;"><?= number_format($deposit['amount']) ?> MMK</td>
                    <td>
                        <?php if ($deposit['transaction_slip']): ?>
                            <button class="btn btn-view" onclick="viewSlipModal('<?= $deposit['transaction_slip'] ?>', <?= $deposit['id'] ?>, '<?= htmlspecialchars($deposit['rider_name']) ?>', <?= $deposit['amount'] ?>)">
                                 View Slip
                            </button>
                        <?php else: ?>
                            <span style="color: #dc3545;">No Slip</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M j, Y H:i', strtotime($deposit['deposited_at'])) ?></td>
                    <td>
                        <button type="button" class="btn btn-approve" 
                                onclick="verifyDeposit(<?= $deposit['id'] ?>, 'approve', <?= $deposit['amount'] ?>, '<?= htmlspecialchars($deposit['rider_name']) ?>')">
                             Approve
                        </button>
                        <button type="button" class="btn btn-reject"
                                onclick="rejectDeposit(<?= $deposit['id'] ?>, <?= $deposit['amount'] ?>, '<?= htmlspecialchars($deposit['rider_name']) ?>', '<?= $deposit['deposit_txn_id'] ?>')">
                            Reject
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: green; padding: 10px; background: #d4edda; border-radius: 4px;">
            No pending deposits. All COD amounts have been verified.
        </p>
        <?php endif; ?>
    </div>

    <!-- Verified Deposits (Current Week Only) -->
    <h3> Verified This Week (<span id="verifiedHeaderCount"><?= count($verified_deposits) ?></span>)</h3>
    <div id="verifiedDepositsTable">
        <?php if (!empty($verified_deposits)): ?>
        <table>
            <thead>
                <tr>
                    <th>Deposit ID</th>
                    <th>Rider</th>
                    <th>Order ID</th>
                    <th>Restaurant</th>
                    <th>Amount</th>
                    <th>Verified Date</th>
                    <th>Verified By</th>
                </tr>
            </thead>
            <tbody id="verifiedDepositsBody">
                <?php foreach ($verified_deposits as $deposit): ?>
                <tr>
                    <td><?= $deposit['deposit_txn_id'] ?></td>
                    <td><?= htmlspecialchars($deposit['rider_name']) ?></td>
                    <td>#<?= $deposit['order_id'] ?></td>
                    <td><?= htmlspecialchars($deposit['restaurant_name']) ?></td>
                    <td style="font-weight: bold; color: #28a745;"><?= number_format($deposit['amount']) ?> MMK</td>
                    <td><?= date('M j, Y H:i', strtotime($deposit['verified_at'])) ?></td>
                    <td><?= htmlspecialchars($deposit['verified_by_name'] ?? 'System') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: #666; padding: 10px; background: #f8f9fa; border-radius: 4px;">
             No verified deposits for this week yet.
        </p>
        <?php endif; ?>
    </div>
</div>

<script>
    let autoRefreshEnabled = true;
    let refreshInterval;
    let isProcessingAction = false;
    let currentRejectDepositId = null;

    function startAutoRefresh() {
        refreshData();
        refreshInterval = setInterval(() => {
            if (!isProcessingAction && autoRefreshEnabled) {
                refreshData();
            }
        }, 10000);
    }

    function refreshData() {
        if (isProcessingAction) return;

        const url = `?ajax=get_deposits&t=${new Date().getTime()}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                updateUI(data);
                document.getElementById('lastUpdate').textContent = 'Last update: ' + new Date().toLocaleTimeString();
            })
            .catch(error => {
                console.error('Refresh error:', error);
                document.getElementById('lastUpdate').textContent = 'Last update: Error - ' + new Date().toLocaleTimeString();
            });
    }

    function updateUI(data) {
        document.getElementById('totalPending').textContent = data.total_pending.toLocaleString() + ' MMK';
        document.getElementById('totalVerified').textContent = data.total_verified.toLocaleString() + ' MMK';
        document.getElementById('pendingCount').textContent = data.pending_count + ' deposits';
        document.getElementById('verifiedCount').textContent = data.verified_count + ' deposits';
        
        document.getElementById('pendingHeaderCount').textContent = data.pending_count;
        document.getElementById('verifiedHeaderCount').textContent = data.verified_count;
        
        updatePendingTable(data.pending_deposits);
        updateVerifiedTable(data.verified_deposits);
    }

    function updatePendingTable(deposits) {
        const container = document.getElementById('pendingDepositsTable');
        
        if (deposits.length === 0) {
            container.innerHTML = '<p style="color: green; padding: 10px; background: #d4edda; border-radius: 4px;">No pending deposits. All COD amounts have been verified.</p>';
            return;
        }
        
        let html = `
        <table>
            <thead>
                <tr>
                    <th>Deposit ID</th>
                    <th>Rider</th>
                    <th>Order ID</th>
                    <th>Restaurant</th>
                    <th>Amount</th>
                    <th>Transaction Slip</th>
                    <th>Deposit Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        `;
        
        deposits.forEach(deposit => {
            const slipButton = deposit.transaction_slip ? 
                `<button class="btn btn-view" onclick="viewSlipModal('${deposit.transaction_slip}', ${deposit.id}, '${escapeHtml(deposit.rider_name)}', ${deposit.amount})">
                     View Slip
                </button>` : 
                '<span style="color: #dc3545;">No Slip</span>';
            
            html += `
                <tr id="deposit-${deposit.id}">
                    <td>${deposit.deposit_txn_id}</td>
                    <td>${escapeHtml(deposit.rider_name)}<br><small>${deposit.rider_phone}</small></td>
                    <td>#${deposit.order_id}</td>
                    <td>${escapeHtml(deposit.restaurant_name)}</td>
                    <td style="font-weight: bold;">${parseFloat(deposit.amount).toLocaleString()} MMK</td>
                    <td>${slipButton}</td>
                    <td>${formatDate(deposit.deposited_at)}</td>
                    <td>
                        <button type="button" class="btn btn-approve" 
                                onclick="verifyDeposit(${deposit.id}, 'approve', ${deposit.amount}, '${escapeHtml(deposit.rider_name)}')">
                             Approve
                        </button>
                        <button type="button" class="btn btn-reject"
                                onclick="rejectDeposit(${deposit.id}, ${deposit.amount}, '${escapeHtml(deposit.rider_name)}', '${deposit.deposit_txn_id}')">
                             Reject
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function updateVerifiedTable(deposits) {
        const container = document.getElementById('verifiedDepositsTable');
        
        if (deposits.length === 0) {
            container.innerHTML = '<p style="color: #666; padding: 10px; background: #f8f9fa; border-radius: 4px;">📊 No verified deposits for this week yet.</p>';
            return;
        }
        
        let html = `
        <table>
            <thead>
                <tr>
                    <th>Deposit ID</th>
                    <th>Rider</th>
                    <th>Order ID</th>
                    <th>Restaurant</th>
                    <th>Amount</th>
                    <th>Verified Date</th>
                    <th>Verified By</th>
                </tr>
            </thead>
            <tbody>
        `;
        
        deposits.forEach(deposit => {
            html += `
                <tr>
                    <td>${deposit.deposit_txn_id}</td>
                    <td>${escapeHtml(deposit.rider_name)}</td>
                    <td>#${deposit.order_id}</td>
                    <td>${escapeHtml(deposit.restaurant_name)}</td>
                    <td style="font-weight: bold; color: #28a745;">${parseFloat(deposit.amount).toLocaleString()} MMK</td>
                    <td>${formatDate(deposit.verified_at)}</td>
                    <td>${escapeHtml(deposit.verified_by_name || 'System')}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    // Slip Viewing Functions
    function viewSlipModal(slipFilename, depositId, riderName, amount) {
        if (!slipFilename) {
            alert('No transaction slip available');
            return;
        }
        
        const slipUrl = `../../assets/transaction_slips/${slipFilename}`;
        const slipContent = document.getElementById('slipContent');
        
        if (slipFilename.toLowerCase().endsWith('.pdf')) {
            slipContent.innerHTML = `
                <embed src="${slipUrl}" type="application/pdf" width="100%" height="600px">
                <div style="margin-top: 15px;">
                    <a href="${slipUrl}" download class="btn btn-primary"> Download PDF</a>
                </div>
            `;
        } else {
            slipContent.innerHTML = `
                <img src="${slipUrl}" class="slip-image">
                <div style="margin-top: 15px;">
                    <a href="${slipUrl}" download class="btn btn-primary"> Download Image</a>
                </div>
            `;
        }
        
        document.getElementById('slipModal').style.display = 'block';
    }

    function closeSlipModal() {
        document.getElementById('slipModal').style.display = 'none';
    }

    // Rejection Functions
    function rejectDeposit(depositId, amount, riderName, depositTxnId) {
        currentRejectDepositId = depositId;
        
        document.getElementById('rejectDepositId').textContent = depositTxnId;
        document.getElementById('rejectDepositAmount').textContent = parseFloat(amount).toLocaleString() + ' MMK';
        document.getElementById('rejectRiderName').textContent = riderName;
        document.getElementById('rejectionReason').value = '';
        
        document.getElementById('rejectModal').style.display = 'block';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        currentRejectDepositId = null;
    }

    function confirmRejectDeposit() {
        const reason = document.getElementById('rejectionReason').value.trim();
        
        if (!reason) {
            alert('Please provide a rejection reason');
            return;
        }
        
        if (!currentRejectDepositId) {
            alert('No deposit selected');
            return;
        }
        
        const formData = new FormData();
        formData.append('reject_deposit', '1');
        formData.append('deposit_id', currentRejectDepositId);
        formData.append('rejection_reason', reason);
        
        fetch('<?= $_SERVER['PHP_SELF'] ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ Deposit rejected successfully', 'success');
                closeRejectModal();
                refreshData();
            } else {
                alert('Rejection failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Rejection error:', error);
            alert('Error rejecting deposit');
        });
    }

    // Approval Function
    function verifyDeposit(depositId, action, amount = null, riderName = null) {
        if (isProcessingAction) {
            alert('Please wait, another action is in progress.');
            return;
        }
        
        let confirmMessage;
        
        if (action === 'approve') {
            confirmMessage = `Approve deposit of ${parseFloat(amount).toLocaleString()} MMK from ${riderName}?`;
        } else {
            confirmMessage = 'Reject this deposit? Rider will need to re-submit.';
        }
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        isProcessingAction = true;
        autoRefreshEnabled = false;
        
        document.getElementById('loading').style.display = 'block';
        
        const formData = new FormData();
        formData.append('verify_deposit', '1');
        formData.append('deposit_id', depositId);
        formData.append('action', action);
        
        fetch('<?= $_SERVER['PHP_SELF'] ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loading').style.display = 'none';
            
            if (data.success) {
                document.getElementById('modalMessage').textContent = data.message;
                document.getElementById('successModal').style.display = 'block';
                
                setTimeout(() => {
                    refreshData();
                    setTimeout(() => {
                        autoRefreshEnabled = true;
                    }, 2000);
                }, 1000);
            } else {
                alert('Action failed: ' + (data.message || 'Unknown error'));
                autoRefreshEnabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loading').style.display = 'none';
            alert('An error occurred. Please try again.');
            autoRefreshEnabled = true;
        })
        .finally(() => {
            isProcessingAction = false;
        });
    }

    // History Modal Functions
    function openHistoryModal() {
        document.getElementById('historyModal').style.display = 'block';
        const dateTo = new Date().toISOString().split('T')[0];
        const dateFrom = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        
        document.getElementById('filterDateFrom').value = dateFrom;
        document.getElementById('filterDateTo').value = dateTo;
        
        loadHistoryData();
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').style.display = 'none';
    }

    function applyHistoryFilters() {
        loadHistoryData();
    }

    function clearHistoryFilters() {
        document.getElementById('filterRider').value = '';
        document.getElementById('filterRestaurant').value = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        loadHistoryData();
    }

    function loadHistoryData() {
        const rider = document.getElementById('filterRider').value;
        const restaurant = document.getElementById('filterRestaurant').value;
        const dateFrom = document.getElementById('filterDateFrom').value;
        const dateTo = document.getElementById('filterDateTo').value;
        
        const params = new URLSearchParams({
            ajax: 'get_history',
            rider: rider,
            restaurant: restaurant,
            date_from: dateFrom,
            date_to: dateTo
        });
        
        const url = `?${params.toString()}`;
        
        document.getElementById('historyLoading').style.display = 'block';
        document.getElementById('historyTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center;">Loading...</td></tr>';
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                document.getElementById('historyLoading').style.display = 'none';
                updateHistoryTable(data);
            })
            .catch(error => {
                console.error('History load error:', error);
                document.getElementById('historyLoading').style.display = 'none';
                document.getElementById('historyTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">Error loading data</td></tr>';
            });
    }

    function updateHistoryTable(data) {
        const container = document.getElementById('historyTableBody');
        const summary = document.getElementById('historySummary');
        
        summary.textContent = `${data.history_count} deposits • Total: ${data.total_history_amount.toLocaleString()} MMK`;
        
        if (data.history_count === 0) {
            container.innerHTML = '<tr><td colspan="7" style="text-align: center;">No deposits found with current filters</td></tr>';
            return;
        }
        
        let html = '';
        
        data.history_deposits.forEach(deposit => {
            html += `
                <tr>
                    <td>${deposit.deposit_txn_id}</td>
                    <td>${escapeHtml(deposit.rider_name)}<br><small>${deposit.rider_phone}</small></td>
                    <td>#${deposit.order_id}</td>
                    <td>${escapeHtml(deposit.restaurant_name)}</td>
                    <td style="font-weight: bold; color: #28a745;">${parseFloat(deposit.amount).toLocaleString()} MMK</td>
                    <td>${formatDate(deposit.verified_at)}</td>
                    <td>${escapeHtml(deposit.verified_by_name || 'System')}</td>
                </tr>
            `;
        });
        
        container.innerHTML = html;
    }

    // Pending COD Modal Functions
    function openPendingCODModal() {
        document.getElementById('pendingCODModal').style.display = 'block';
        loadPendingCODData();
    }

    function closePendingCODModal() {
        document.getElementById('pendingCODModal').style.display = 'none';
    }

    function loadPendingCODData() {
        const riderFilter = document.getElementById('filterPendingRider').value;
        
        const params = new URLSearchParams({
            ajax: 'get_pending_cod',
            rider: riderFilter
        });
        
        const url = `?${params.toString()}`;
        
        document.getElementById('pendingCODLoading').style.display = 'block';
        document.getElementById('pendingCODTableBody').innerHTML = '<tr><td colspan="9" style="text-align: center;">Loading...</td></tr>';
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                document.getElementById('pendingCODLoading').style.display = 'none';
                updatePendingCODTable(data);
            })
            .catch(error => {
                console.error('Pending COD load error:', error);
                document.getElementById('pendingCODLoading').style.display = 'none';
                document.getElementById('pendingCODTableBody').innerHTML = '<tr><td colspan="9" style="text-align: center; color: red;">Error loading data</td></tr>';
            });
    }

    function updatePendingCODTable(data) {
        const container = document.getElementById('pendingCODTableBody');
        const summary = data.summary;
        
        // Update summary cards
        document.getElementById('totalPendingAmount').textContent = summary.total_pending_amount.toLocaleString() + ' MMK';
        document.getElementById('totalPendingOrders').textContent = summary.total_pending_orders;
        document.getElementById('totalRidersCount').textContent = summary.total_riders;
        
        if (data.pending_cod_data.length === 0) {
            container.innerHTML = '<tr><td colspan="9" style="text-align: center; color: green;">✅ All COD orders have been deposited!</td></tr>';
            return;
        }
        
        let html = '';
        
        data.pending_cod_data.forEach(rider => {
            const daysOutstanding = rider.days_outstanding || 0;
            const rowColor = daysOutstanding > 3 ? 'style="background-color: #ffe6e6;"' : 
                            daysOutstanding > 1 ? 'style="background-color: #fff3cd;"' : '';
            
            html += `
                <tr ${rowColor}>
                    <td><strong>${escapeHtml(rider.rider_name)}</strong></td>
                    <td>${rider.rider_phone}</td>
                    <td style="text-align: center;"><span class="badge badge-warning">${rider.pending_orders_count}</span></td>
                    <td style="font-weight: bold;">${parseFloat(rider.total_order_amount).toLocaleString()} MMK</td>
                    <td>${parseFloat(rider.total_delivery_fee).toLocaleString()} MMK</td>
                    <td style="font-weight: bold; color: #dc3545;">${parseFloat(rider.total_amount_due).toLocaleString()} MMK</td>
                    <td>${formatDate(rider.latest_delivery_date)}</td>
                    <td>
                        <span class="badge ${daysOutstanding > 3 ? 'badge-danger' : daysOutstanding > 1 ? 'badge-warning' : 'badge-info'}">
                            ${daysOutstanding} day${daysOutstanding !== 1 ? 's' : ''}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="sendReminder(${rider.rider_id}, '${escapeHtml(rider.rider_name)}')">
                             Remind
                        </button>
                        <button class="btn btn-sm btn-info" onclick="viewRiderDetails(${rider.rider_id})">
                             Details
                        </button>
                    </td>
                </tr>
            `;
        });
        
        container.innerHTML = html;
    }

    function clearPendingCODFilters() {
        document.getElementById('filterPendingRider').value = '';
        loadPendingCODData();
    }

    function sendReminder(riderId, riderName) {
        if (confirm(`Send deposit reminder to ${riderName}?`)) {
            showNotification(`📧 Reminder sent to ${riderName}`, 'success');
            console.log(`Reminder sent to rider ${riderId} - ${riderName}`);
        }
    }

    function viewRiderDetails(riderId) {
        alert(`View detailed pending orders for rider ID: ${riderId}\nThis can show individual order details.`);
    }

    function closeModal() {
        document.getElementById('successModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modals = ['successModal', 'historyModal', 'rejectModal', 'slipModal', 'pendingCODModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                if (modalId === 'successModal') closeModal();
                if (modalId === 'historyModal') closeHistoryModal();
                if (modalId === 'rejectModal') closeRejectModal();
                if (modalId === 'slipModal') closeSlipModal();
                if (modalId === 'pendingCODModal') closePendingCODModal();
            }
        });
    }

    function manualRefresh() {
        refreshData();
    }

    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showNotification(message, type = 'info') {
        alert(message);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        startAutoRefresh();
    });
</script>