<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle settlement reprocessing WITH SLIP UPLOAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reprocess_settlement'])) {
    $settlement_id = $_POST['settlement_id'];
    
    try {
        // Verify database connection
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }
        
        $pdo->beginTransaction();
        
        // Get the rejected settlement details
        $stmt = $pdo->prepare("
            SELECT DISTINCT rs.*, r.restaurant_id, r.name as restaurant_name 
            FROM restaurant_settlements rs
            JOIN restaurants r ON rs.restaurant_id = r.restaurant_id
            WHERE rs.id = ? AND rs.status = 'rejected'
        ");
        $stmt->execute([$settlement_id]);
        $settlement = $stmt->fetch();
        
        if (!$settlement) {
            throw new Exception("Settlement not found or not rejected.");
        }
        
        // Handle new payment slip upload
        $new_slip_filename = $settlement['payment_slip']; // Keep old slip if no new one
        
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
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION);
            $new_slip_filename = 'reprocessed_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_slip_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['payment_slip']['tmp_name'], $upload_path)) {
                throw new Exception("Failed to upload payment slip. Check directory permissions.");
            }
            
            error_log("New payment slip uploaded for reprocessed settlement: " . $new_slip_filename);
        }
        
        // Reset settlement status to pending WITH NEW SLIP
        $stmt = $pdo->prepare("
            UPDATE restaurant_settlements 
            SET status = 'pending', 
                rejection_reason = NULL,
                payment_slip = ?,
                notes = CONCAT(COALESCE(notes, ''), '\nReprocessed on ', NOW(), ' with new payment slip'),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$new_slip_filename, $settlement_id]);
        
        // Reset notification status WITH NEW SLIP
        $stmt = $pdo->prepare("
            UPDATE settlement_notifications 
            SET status = 'pending', 
                rejection_reason = NULL,
                payment_slip = ?,
                rejected_at = NULL,
                created_at = NOW()
            WHERE settlement_id = ?
        ");
        $stmt->execute([$new_slip_filename, $settlement_id]);
        
        // Update expense record to remove rejection note and update slip
        $stmt = $pdo->prepare("
            UPDATE expenses 
            SET note = REPLACE(note, 'REJECTED: ', ''),
                payment_slip = ?
            WHERE note LIKE ? AND restaurant_id = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([
            $new_slip_filename,
            "%Week {$settlement['week_no']}%",
            $settlement['restaurant_id']
        ]);
        
        $pdo->commit();
        $success_message = "Settlement has been reset to pending status with new payment slip and will reappear for restaurant confirmation.";
        
    } catch (Exception $e) {
        // Only rollback if transaction is active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = "Error reprocessing settlement: " . $e->getMessage();
    }
}

// Get rejected settlements
$stmt = $pdo->prepare("
    SELECT  DISTINCT rs.*, r.name as restaurant_name, u.name as admin_name
    FROM restaurant_settlements rs
    JOIN restaurants r ON rs.restaurant_id = r.restaurant_id
    LEFT JOIN users u ON rs.released_by = u.user_id
    WHERE rs.status = 'rejected'
    ORDER BY rs.updated_at DESC
");
$stmt->execute();
$rejected_settlements = $stmt->fetchAll();

// Get total rejected amount
$total_rejected = 0;
foreach ($rejected_settlements as $settlement) {
    $total_rejected += $settlement['amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Settlements - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rejected-card {
            border-left: 4px solid #dc3545;
            margin-bottom: 1rem;
        }
        .rejection-reason {
            background: #f8f9fa;
            border-left: 3px solid #dc3545;
            padding: 10px;
            margin: 10px 0;
        }
        .summary-card {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .slip-preview {
            max-width: 200px;
            max-height: 150px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }
        .modal-slip-section {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .current-slip {
            margin-top: 15px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-times-circle me-2"></i> Rejected Settlements</h2>
            <div>
                <a href="weekly_settlements_export.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Settlements
                </a>
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Summary Card -->
        <div class="summary-card">
            <div class="row">
                <div class="col-md-6">
                    <h4><i class="fas fa-times-circle me-2"></i> Rejected Settlements Summary</h4>
                    <p class="mb-0">Total amount held due to rejections</p>
                </div>
                <div class="col-md-6 text-end">
                    <h2><?= number_format($total_rejected, 2) ?> MMK</h2>
                    <p class="mb-0"><?= count($rejected_settlements) ?> Rejected Settlement(s)</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i> Rejected Payment Settlements</h5>
            </div>
            <div class="card-body">
                <?php if (empty($rejected_settlements)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">No rejected settlements found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rejected_settlements as $settlement): ?>
                        <div class="card mb-3 rejected-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title">
                                            <span class="badge bg-danger">Week <?= $settlement['week_no'] ?></span>
                                            <span class="ms-2"><?= htmlspecialchars($settlement['restaurant_name']) ?></span>
                                        </h5>
                                        
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <strong>Amount:</strong> 
                                                    <span class="text-danger fw-bold">
                                                        <?= number_format($settlement['amount'], 2) ?> MMK
                                                    </span>
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Processed By:</strong> 
                                                    <?= htmlspecialchars($settlement['admin_name'] ?? 'System') ?>
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Initial Payment Date:</strong> 
                                                    <?= date('M j, Y g:i A', strtotime($settlement['created_at'])) ?>
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Rejected On:</strong> 
                                                    <?= date('M j, Y g:i A', strtotime($settlement['updated_at'])) ?>
                                                </p>
                                                
                                                <?php if (!empty($settlement['payment_slip'])): 
                                                    $slip_url = "../../assets/transaction_slips/" . $settlement['payment_slip'];
                                                ?>
                                                    <div class="mt-2">
                                                        <strong>Previous Slip:</strong>
                                                        <div>
                                                            <a href="<?= htmlspecialchars($slip_url) ?>" 
                                                               target="_blank" class="btn btn-sm btn-outline-info">
                                                                <i class="fas fa-eye"></i> View Previous Slip
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="rejection-reason">
                                                    <strong><i class="fas fa-comment-alt me-1"></i> Rejection Reason:</strong>
                                                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($settlement['rejection_reason'])) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#reprocessModal<?= $settlement['id'] ?>">
                                            <i class="fas fa-redo"></i> Reprocess
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reprocess Modal -->
                        <div class="modal fade" id="reprocessModal<?= $settlement['id'] ?>" tabindex="-1" aria-labelledby="reprocessModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="POST" action="admin_rejected_settlements.php" enctype="multipart/form-data">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title" id="reprocessModalLabel">
                                                <i class="fas fa-redo"></i> Reprocess Settlement
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>You are reprocessing payment for <strong><?= htmlspecialchars($settlement['restaurant_name']) ?></strong> - Week <?= $settlement['week_no'] ?> (<?= number_format($settlement['amount'], 2) ?> MMK).</p>
                                            
                                            <div class="rejection-reason mb-3">
                                                <strong>Original Rejection Reason:</strong>
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($settlement['rejection_reason'])) ?></p>
                                            </div>

                                            <div class="modal-slip-section">
                                                <h6><i class="fas fa-receipt"></i> Upload New Payment Slip</h6>
                                                <p class="text-muted small">Upload a new payment slip as proof of transaction. The restaurant will see this slip.</p>
                                                
                                                <div class="mb-3">
                                                    <label for="payment_slip<?= $settlement['id'] ?>" class="form-label">Select Payment Slip:</label>
                                                    <input type="file" class="form-control" id="payment_slip<?= $settlement['id'] ?>" name="payment_slip" accept="image/*">
                                                    <div class="form-text">Supported formats: JPG, PNG, GIF (Max: 2MB)</div>
                                                </div>
                                                
                                                <?php if (!empty($settlement['payment_slip'])): 
                                                    $slip_url = "../../assets/transaction_slips/" . $settlement['payment_slip'];
                                                ?>
                                                    <div class="current-slip">
                                                        <strong>Current Slip:</strong>
                                                        <div class="mt-2">
                                                            <img src="<?= htmlspecialchars($slip_url) ?>" 
                                                                 class="slip-preview" 
                                                                 alt="Current payment slip"
                                                                 onerror="this.style.display='none'">
                                                            <div class="mt-1">
                                                                <a href="<?= htmlspecialchars($slip_url) ?>" 
                                                                   target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-external-link-alt"></i> View Current Slip
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> 
                                                <strong>Note:</strong> This will reset the settlement to pending status and notify the restaurant to confirm the new payment.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="hidden" name="settlement_id" value="<?= $settlement['id'] ?>">
                                            <input type="hidden" name="reprocess_settlement" value="1">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-paper-plane"></i> Reprocess with New Slip
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // File upload validation - ONLY for admin_rejected_settlements.php
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size too large. Maximum size is 2MB.');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Invalid file type. Please upload JPG, PNG, or GIF images only.');
                        this.value = '';
                        return;
                    }
                }
            });
        });
    </script>
</body>
</html>