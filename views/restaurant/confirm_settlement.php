<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['restaurant_id'])) {
    header("Location: login.php");
    exit();
}

$restaurant_id = $_SESSION['restaurant_id'];
$message = '';
$message_type = '';

// Handle confirmation/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settlement_id = $_POST['settlement_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $rejection_reason = $_POST['rejection_reason'] ?? '';
    
   try {
        // Verify the settlement belongs to this restaurant
        $stmt = $pdo->prepare("
            SELECT rs.*, r.name as restaurant_name 
            FROM restaurant_settlements rs
            JOIN restaurants r ON rs.restaurant_id = r.restaurant_id
            WHERE rs.id = ? AND rs.restaurant_id = ? AND rs.status = 'pending'
        ");
        $stmt->execute([$settlement_id, $restaurant_id]);
        $settlement = $stmt->fetch();
        
        // CRITICAL CHECK BEFORE STARTING TRANSACTION
        if (!$settlement) {
            throw new Exception("Invalid settlement or already processed.");
        }
        
      $pdo->beginTransaction();
        
        if ($action === 'confirm') {
            // Update settlement status to confirmed
            $stmt = $pdo->prepare("
                UPDATE restaurant_settlements 
                SET status = 'completed', 
                    confirmed_at = NOW(),
                    updated_at = NOW()
                WHERE id = ? AND restaurant_id = ?
            ");
            $stmt->execute([$settlement_id, $restaurant_id]);
            
            // Update notification
            $stmt = $pdo->prepare("
                UPDATE settlement_notifications 
                SET status = 'confirmed', 
                    confirmed_at = NOW()
                WHERE settlement_id = ? AND restaurant_id = ?
            ");
            $stmt->execute([$settlement_id, $restaurant_id]);
            
            // Update expense record note
            $stmt = $pdo->prepare("
                UPDATE expenses 
                SET note = CONCAT(note, ' - CONFIRMED by restaurant on ', NOW())
                WHERE note LIKE ? AND restaurant_id = ?
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([
                "%Week {$settlement['week_no']}%",
                $restaurant_id
            ]);
            
          $message = "Payment of " . number_format($settlement['amount'], 2) . " MMK for Week {$settlement['week_no']} has been confirmed.";
            $message_type = 'success';
            
        } elseif ($action === 'reject') {
            if (empty($rejection_reason)) {
                throw new Exception("Please provide a reason for rejection.");
            }
            
            // Update settlement status to rejected
            $stmt = $pdo->prepare("
                UPDATE restaurant_settlements 
                SET status = 'rejected', 
                    rejection_reason = ?,
                    updated_at = NOW()
                WHERE id = ? AND restaurant_id = ?
            ");
            $stmt->execute([$rejection_reason, $settlement_id, $restaurant_id]);
            
            // Update notification
            $stmt = $pdo->prepare("
                UPDATE settlement_notifications 
                SET status = 'rejected', 
                    rejection_reason = ?,
                    rejected_at = NOW()
                WHERE settlement_id = ? AND restaurant_id = ?
            ");
            $stmt->execute([$rejection_reason, $settlement_id, $restaurant_id]);
            
            // Update expense record note to mark as rejected
            $stmt = $pdo->prepare("
                UPDATE expenses 
                SET note = CONCAT('REJECTED: ', ?, ' - ', note)
                WHERE note LIKE ? AND restaurant_id = ?
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([
                $rejection_reason,
                "%Week {$settlement['week_no']}%",
                $restaurant_id
            ]);
            
            // ===== ADD NOTIFICATION FOR ADMIN ABOUT REJECTION =====
            $adminUserId = 4; // Your admin user ID
            
            // Get restaurant name for the notification
            $restaurant_stmt = $pdo->prepare("SELECT name FROM restaurants WHERE restaurant_id = ?");
            $restaurant_stmt->execute([$restaurant_id]);
            $restaurant_name = $restaurant_stmt->fetchColumn();
            
            // Create notification message
            $notification_title = "Settlement Rejected";
            $notification_message = "Restaurant '{$restaurant_name}' rejected settlement of " . number_format($settlement['amount'], 2) . " MMK for Week {$settlement['week_no']}. Reason: {$rejection_reason}";
            
            // Insert into request_notifications table
            $notif_stmt = $pdo->prepare("
                INSERT INTO request_notifications 
                (user_id, title, message, is_read, created_at) 
                VALUES (?, ?, ?, 0, NOW())
            ");
            $notif_stmt->execute([
                $adminUserId,
                $notification_title,
                $notification_message
            ]);
            
          $message = "Payment of " . number_format($settlement['amount'], 2) . " MMK for Week {$settlement['week_no']} has been rejected. Admin has been notified.";
            $message_type = 'warning';
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        // CRITICAL FIX: Only call rollBack if a transaction is currently active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "Error processing request: " . $e->getMessage();
        $message_type = 'danger';
    }
}

// Get pending settlements for this restaurant
$stmt = $pdo->prepare("
    SELECT rs.*, u.name as admin_name 
    FROM restaurant_settlements rs
    LEFT JOIN users u ON rs.released_by = u.user_id
    WHERE rs.restaurant_id = ? 
    AND rs.status = 'pending'
    ORDER BY rs.week_no DESC
");
$stmt->execute([$restaurant_id]);
$pending_settlements = $stmt->fetchAll();

// Get completed settlements for history
$stmt = $pdo->prepare("
    SELECT rs.*, u.name as admin_name 
    FROM restaurant_settlements rs
    LEFT JOIN users u ON rs.released_by = u.user_id
    WHERE rs.restaurant_id = ? 
    AND rs.status IN ('completed', 'rejected')
    ORDER BY rs.week_no DESC
    LIMIT 10
");
$stmt->execute([$restaurant_id]);
$settlement_history = $stmt->fetchAll();

// Get notification count for header
$notification_stmt = $pdo->prepare("
    SELECT COUNT(*) as pending_count 
    FROM settlement_notifications 
    WHERE restaurant_id = ? AND status = 'pending'
");
$notification_stmt->execute([$restaurant_id]);
$notification_count = $notification_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Settlement - Restaurant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .settlement-card {
            border-left: 4px solid #28a745;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }
        .settlement-card.rejected {
            border-left-color: #dc3545;
        }
        .settlement-amount {
            font-size: 1.25rem;
            font-weight: bold;
            color: #28a745;
        }
        .settlement-card.rejected .settlement-amount {
            color: #dc3545;
        }
        .week-badge {
            background-color: #6c757d;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .payment-slip-preview {
            max-width: 300px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
            cursor: pointer;
            object-fit: cover;
        }
        .slip-container {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .file-not-found {
            color: #dc3545;
            font-style: italic;
        }
        .slip-actions {
            margin-top: 10px;
        }
        .slip-actions .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-money-bill-wave"></i> Payment Confirmations</h2>
            <div>
                <a href="index.php?page=financial" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Financials
                </a>
                <!-- <?php if ($notification_count > 0): ?>
                    <span class="badge bg-danger ms-2">
                        <i class="fas fa-bell"></i> <?= $notification_count ?> Pending
                    </span>
                <?php endif; ?> -->
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Confirmations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_settlements)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">No pending payment confirmations.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_settlements as $settlement): ?>
                        <div class="card mb-3 settlement-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">
                                            <span class="week-badge">Week <?= $settlement['week_no'] ?></span>
                                            <small class="text-muted ms-2"> 
                                                <?= date('M j, Y', strtotime($settlement['created_at'])) ?>
                                            </small>
                                        </h5>
                                        <p class="mb-1">
                                            <i class="fas fa-user-tie text-muted me-1"></i> <?= htmlspecialchars($settlement['admin_name'] ?? 'Admin') ?>
                                        </p>
                                        <p class="mb-1 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> <?= nl2br(htmlspecialchars($settlement['notes'])) ?>
                                        </p>

                                        <?php if (!empty($settlement['payment_slip'])): 
                                            // The path to the slip is relative from the current file's directory
                                            $slip_filename = $settlement['payment_slip'];
                                            $slip_url = "../../assets/transaction_slips/" . $slip_filename;
                                        ?>
                                            <div class="slip-container">
                                                <strong><i class="fas fa-receipt me-1"></i>Payment Slip:</strong>
                                                <div class="slip-actions">
                                                    <a href="<?= htmlspecialchars($slip_url) ?>" 
                                                       target="_blank" class="btn btn-sm btn-info" 
                                                       title="View Payment Slip">
                                                        <i class="fas fa-eye"></i> View Slip
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            title="Download Payment Slip"
                                                            onclick="downloadFile('<?= htmlspecialchars($slip_url) ?>', 'Payment_Slip_<?= htmlspecialchars($settlement['id']) ?>')">
                                                        <i class="fas fa-download"></i> Download
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; // End of slip display ?>
                                        
                                        <h4 class="settlement-amount mt-3">
                                            <?= number_format($settlement['amount'], 2) ?> MMK
                                        </h4>
                                    </div>

                                    <div class="text-end">
                                        <form method="POST" action="confirm_settlement.php" onsubmit="return confirm('Are you sure you want to CONFIRM this payment? This cannot be undone.');">
                                            <input type="hidden" name="settlement_id" value="<?= $settlement['id'] ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="btn btn-success mb-2">
                                                <i class="fas fa-check"></i> Confirm Payment
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $settlement['id'] ?>">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="rejectModal<?= $settlement['id'] ?>" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="POST" action="confirm_settlement.php">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="rejectModalLabel">Reject Payment Confirmation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to reject the payment for Week <?= $settlement['week_no'] ?> (<?= number_format($settlement['amount'], 2) ?> MMK)?</p>
                                            <div class="mb-3">
                                                <label for="rejection_reason<?= $settlement['id'] ?>" class="form-label">Reason for Rejection (Required)</label>
                                                <textarea class="form-control" id="rejection_reason<?= $settlement['id'] ?>" name="rejection_reason" rows="3" required></textarea>
                                            </div>
                                            <input type="hidden" name="settlement_id" value="<?= $settlement['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Submit Rejection</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Settlement History (Last 10)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($settlement_history)): ?>
                    <p class="text-center text-muted py-3">No history records found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Week</th>
                                    <th>Amount (MMK)</th>
                                    <th>Status</th>
                                    <th>Slip</th>
                                    <th>Processed By</th>
                                    <th>Notes/Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($settlement_history as $settlement): ?>
                                    <tr class="<?= $settlement['status'] === 'rejected' ? 'table-danger' : ($settlement['status'] === 'completed' ? 'table-success' : '') ?>">
                                        <td><?= htmlspecialchars($settlement['id']) ?></td>
                                        <td><?= htmlspecialchars($settlement['week_no']) ?></td>
                                        <td><?= number_format($settlement['amount'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $settlement['status'] === 'rejected' ? 'danger' : ($settlement['status'] === 'completed' ? 'success' : 'secondary') ?>">
                                                <?= htmlspecialchars(ucfirst($settlement['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($settlement['payment_slip'])): 
                                                $slip_url = "../../assets/transaction_slips/" . $settlement['payment_slip'];
                                            ?>
                                                <div class="d-flex align-items-center">
                                                    <a href="<?= htmlspecialchars($slip_url) ?>" 
                                                       target="_blank" class="btn btn-sm btn-outline-info me-1" 
                                                       title="View Slip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            title="Download Slip"
                                                            onclick="downloadFile('<?= htmlspecialchars($slip_url) ?>', 'Payment_Slip_<?= htmlspecialchars($settlement['id']) ?>')">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($settlement['admin_name'] ?? 'System') ?></td>
                                        <td>
                                            <?= nl2br(htmlspecialchars($settlement['rejection_reason'] ?? $settlement['notes'])) ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    
 
    // CRITICAL: This function is required for the download buttons to work
      
       
        function downloadFile(url, filename) {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
