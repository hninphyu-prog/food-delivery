<?php
session_start();
require_once "../../config/db.php";
include "includes/header.php";

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';

// Build query with filters - Join riders with users table
$query = "SELECT r.*, u.name AS rider_name, u.email, u.phone, u.is_verified, u.created_at AS user_created
          FROM riders r 
          JOIN users u ON r.user_id = u.user_id 
          WHERE 1=1";

$params = [];

if ($status_filter !== 'all') {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
}

if (!empty($search_term)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$riders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riders Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f4f6f9; }
.main-content { padding: 20px; }
.table th, .table td { vertical-align: middle; }
.actions button { margin-right:5px; margin-bottom: 5px; }

/* Sticky filter section */
.sticky-filter {
    position: sticky;
    top: 0;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 100;
    margin-bottom: 20px;
    margin-top: 20px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    font-weight: 500;
    margin-bottom: 5px;
    display: block;
}

.table-container {
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

/* Status badges */
.badge-status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85em;
}

.badge-active { background-color: #d4edda; color: #155724; }
.badge-inactive { background-color: #f8d7da; color: #721c24; }

/* Verification badges */
.badge-verified { background-color: #d1ecf1; color: #0c5460; }
.badge-unverified { background-color: #f8d7da; color: #721c24; }

/* Action buttons */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Table header */
.table-container table thead tr {
    background-color: rgb(255, 102, 0) !important;
    color: white !important;
}

.table-container table thead th {
    background-color: rgb(255, 102, 0) !important;
    color: white !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
}
</style>
</head>
<body>
<div class="main-content">

<!-- Sticky Filter Section -->
<div class="sticky-filter">
    <form id="filterForm" method="GET" class="filter-row">
        <div class="filter-group">
            <label>Status Filter</label>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Search Riders</label>
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email or phone..." 
                       value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search_term) || $status_filter !== 'all'): ?>
                <a href="riders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="filter-group">
            <label>Actions</label>
            <div>
                <button type="button" onclick="exportToExcel()" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i>Export
                </button>
                <span class="badge bg-primary ms-2">
                    Total: <?= count($riders) ?>
                </span>
            </div>
        </div>
    </form>
</div>

<div class="table-container">
    <table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th>Rider ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>User Status</th>
        <th>Rider Status</th>
        <th>Joined Date</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody id="ridersTable">
    <?php foreach($riders as $r): ?>
    <tr data-id="<?= $r['rider_id'] ?>">
        <td><?= $r['rider_id'] ?></td>
        <td><?= htmlspecialchars($r['rider_name']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars($r['phone']) ?></td>
        <td>
            <span class="badge-status <?= $r['is_verified'] ? 'badge-verified' : 'badge-unverified' ?>">
                
                <?= $r['is_verified'] ? 'Verified' : 'Unverified' ?>
            </span>
        </td>
        <td>
            <span class="badge-status badge-<?= $r['status'] ?>">
                <?= ucfirst($r['status']) ?>
            </span>
        </td>
        <td><?= date("Y-m-d", strtotime($r['user_created'])) ?></td>
        <td class="actions">
            <?php if($r['status']=='active'): ?>
            <button class="btn btn-sm btn-warning edit-btn"
             data-id="<?= $r['rider_id'] ?>" 
             data-user="<?= $r['user_id'] ?>" 
             data-name="<?= htmlspecialchars($r['rider_name']) ?>" 
             data-email="<?= htmlspecialchars($r['email']) ?>" 
             data-phone="<?= htmlspecialchars($r['phone']) ?>" 
             data-status="<?= $r['status'] ?>">
             <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger deactivate-btn" 
                    data-id="<?= $r['rider_id'] ?>"
                    data-name="<?= htmlspecialchars($r['rider_name']) ?>">
                <i class="fas fa-ban"></i> Deactivate
            </button>
            <?php else: ?>
            <button class="btn btn-sm btn-success activate-btn" 
                    data-id="<?= $r['rider_id'] ?>"
                    data-name="<?= htmlspecialchars($r['rider_name']) ?>">
                <i class="fas fa-check"></i> Activate
            </button>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    
    <?php if (empty($riders)): ?>
    <div class="text-center py-5">
        <i class="fas fa-motorcycle fa-3x text-muted mb-3"></i>
        <h4>No riders found</h4>
        <p class="text-muted">Try changing your filters or search term</p>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Rider Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<form id="editForm">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Rider</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="rider_id" id="editRiderId">
<input type="hidden" name="user_id" id="editUserId">
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" id="editName" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" id="editEmail" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" id="editPhone" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Rider Status</label>
    <select name="status" id="editStatus" class="form-control">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary">Save Changes</button>
</div>
</div>
</form>
</div>
</div>

<!-- Warning Modal for Deactivate -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header bg-warning text-dark">
<h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Warning: Deactivate Rider</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="alert alert-warning">
    <i class="fas fa-info-circle me-2"></i>
    <strong>This action will:</strong>
</div>
<ul>
    <li>Set rider status to <span class="badge bg-danger">Inactive</span></li>
    <li>Set user verification to <span class="badge bg-danger">Unverified</span></li>
    <li>Prevent rider from receiving delivery assignments</li>
    <li>Prevent user from logging in</li>
    <li>Remove rider from active duty</li>
</ul>
<p class="mb-0"><strong>Rider:</strong> <span id="warningRiderName"></span></p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-danger" id="confirmDeactivate">Confirm Deactivate</button>
</div>
</div>
</div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-sm">
<div class="modal-content">
<div class="modal-header bg-success text-white border-0">
<h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Success</h5>
</div>
<div class="modal-body text-center">
<p id="successMessage"></p>
</div>
<div class="modal-footer border-0 justify-content-center">
<button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
</div>
</div>
</div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script>
let currentRiderId = null;
let currentRiderName = null;

// --- Edit Modal ---
document.addEventListener('click', function(e){
    if(e.target.closest('.edit-btn')){
        const btn = e.target.closest('.edit-btn');
        document.getElementById('editRiderId').value = btn.dataset.id;
        document.getElementById('editUserId').value = btn.dataset.user;
        document.getElementById('editName').value = btn.dataset.name;
        document.getElementById('editEmail').value = btn.dataset.email;
        document.getElementById('editPhone').value = btn.dataset.phone;
        document.getElementById('editStatus').value = btn.dataset.status;
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }
});

// --- Save Edit ---
document.getElementById('editForm').addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this); 
    fd.append('action','edit_rider');
    
    fetch('rider_crud.php',{method:'POST', body:fd})
    .then(r => r.json())
    .then(data => {
        if(data.success){ 
            showSuccess('Rider updated successfully!');
            setTimeout(() => location.reload(), 1500);
        }
        else alert(data.message);
    });
});

// --- Deactivate with Warning Modal ---
document.addEventListener('click', function(e){
    if(e.target.closest('.deactivate-btn')){
        const btn = e.target.closest('.deactivate-btn');
        currentRiderId = btn.dataset.id;
        currentRiderName = btn.dataset.name;
        
        document.getElementById('warningRiderName').textContent = currentRiderName;
        const modal = new bootstrap.Modal(document.getElementById('warningModal'));
        modal.show();
    }
});

// --- Confirm Deactivate ---
document.getElementById('confirmDeactivate').addEventListener('click', function(){
    const fd = new FormData(); 
    fd.append('action','delete'); 
    fd.append('rider_id', currentRiderId);
    
    fetch('rider_crud.php',{method:'POST', body:fd})
    .then(r => r.json())
    .then(data => {
        const warningModal = bootstrap.Modal.getInstance(document.getElementById('warningModal'));
        warningModal.hide();
        
        if(data.success) {
            showSuccess('Rider deactivated and user unverified!');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.message);
        }
    });
});

// --- Activate ---
document.addEventListener('click', function(e){
    if(e.target.closest('.activate-btn')){
        const btn = e.target.closest('.activate-btn');
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        
        if(confirm(`Activate "${name}" and verify the user?`)) {
            const fd = new FormData(); 
            fd.append('action','activate'); 
            fd.append('rider_id', id);
            
            fetch('rider_crud.php',{method:'POST', body:fd})
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    showSuccess('Rider activated and user verified!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(data.message);
                }
            });
        }
    }
});

// --- Success Modal ---
function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
}

// --- Export to Excel ---
function exportToExcel() {
    const table = document.getElementById('ridersTable');
    const rows = Array.from(table.querySelectorAll('tr')).map(row => {
        return Array.from(row.querySelectorAll('td')).map(td => td.textContent);
    });
    
    // Add headers
    const headers = ['Rider ID', 'Name', 'Email', 'Phone', 'User Status', 'Rider Status', 'Joined Date', 'Actions'];
    rows.unshift(headers);
    
    const ws = XLSX.utils.aoa_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Riders");
    XLSX.writeFile(wb, `riders_${new Date().toISOString().slice(0,10)}.xlsx`);
}

// --- Make filter sticky on scroll ---
window.addEventListener('scroll', function() {
    const filter = document.querySelector('.sticky-filter');
    if (window.scrollY > 50) {
        filter.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
    } else {
        filter.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    }
});
</script>
</body>
</html>