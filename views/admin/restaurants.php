<?php
session_start();
require_once "../../config/db.php";
include "includes/header.php";

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';

// Build query with filters
$query = "SELECT r.*, u.name AS vendor_name 
          FROM restaurants r 
          JOIN users u ON r.user_id = u.user_id 
          WHERE 1=1";

$params = [];

if ($status_filter !== 'all') {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
}

if (!empty($search_term)) {
    $query .= " AND (r.name LIKE ? OR u.name LIKE ? OR r.phone LIKE ? OR r.cuisine_type LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restaurants Approval</title>
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
.tablecolor{
    background-color: rgb(255, 102, 0);
}
.badge-active { background-color: #d4edda; color: #155724; }
.badge-inactive { background-color: #f8d7da; color: #721c24; }
.badge-closed { background-color: #fff3cd; color: #856404; }

/* Action buttons */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.custom-table-header {
    background-color: rgb(41, 128, 185) !important;
    color: white !important;
}
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
                <option value="closed" <?= $status_filter == 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Search</label>
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search restaurants..." 
                       value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search_term) || $status_filter !== 'all'): ?>
                <a href="restaurants.php" class="btn btn-outline-secondary">
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
                    Total: <?= count($restaurants) ?>
                </span>
            </div>
        </div>
    </form>
</div>

<div class="table-container">
    <table class="table table-bordered table-hover">
    <thead style="background-color: rgb(41, 128, 185) !important; color: white !important;">
    <tr>
        <th>ID</th>
        <th>Vendor Name</th>
        <th>User ID</th>
        <th>Restaurant Name</th>
        <th>Phone</th>
        <th>Cuisine</th>
        <th>Status</th>
        <th>Created</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody id="restaurantsTable">
    <?php foreach($restaurants as $r): ?>
    <tr data-id="<?= $r['restaurant_id'] ?>">
        <td><?= $r['restaurant_id'] ?></td>
        <td><?= htmlspecialchars($r['vendor_name']) ?></td>
        <td><?= $r['user_id'] ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['phone']) ?></td>
        <td><?= htmlspecialchars($r['cuisine_type']) ?></td>
        <td>
            <span class="badge-status badge-<?= $r['status'] ?>">
                <?= ucfirst($r['status']) ?>
            </span>
        </td>
        <td><?= date("Y-m-d", strtotime($r['created_at'])) ?></td>
        <td class="actions">
            <?php if($r['status']=='active'): ?>
            <button class="btn btn-sm btn-warning edit-btn"
             data-id="<?= $r['restaurant_id'] ?>" 
             data-user="<?= $r['user_id'] ?>" 
             data-name="<?= htmlspecialchars($r['name']) ?>" 
             data-address="<?= htmlspecialchars($r['address']) ?>" 
             data-phone="<?= htmlspecialchars($r['phone']) ?>" 
             data-cuisine="<?= htmlspecialchars($r['cuisine_type']) ?>" 
             data-status="<?= $r['status'] ?>">
             <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger deactivate-btn" 
                    data-id="<?= $r['restaurant_id'] ?>"
                    data-name="<?= htmlspecialchars($r['name']) ?>">
                <i class="fas fa-ban"></i> Deactivate
            </button>
            <?php else: ?>
            <button class="btn btn-sm btn-success activate-btn" 
                    data-id="<?= $r['restaurant_id'] ?>"
                    data-name="<?= htmlspecialchars($r['name']) ?>">
                <i class="fas fa-check"></i> Activate
            </button>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    
    <?php if (empty($restaurants)): ?>
    <div class="text-center py-5">
        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
        <h4>No restaurants found</h4>
        <p class="text-muted">Try changing your filters or search term</p>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Restaurant Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<form id="editForm">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Restaurant</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="restaurant_id" id="editRestaurantId">
<div class="mb-3">
    <label class="form-label">User ID</label>
    <input type="number" name="user_id" id="editUserId" readonly class="form-control">
</div>
<div class="mb-3">
    <label class="form-label">Restaurant Name</label>
    <input type="text" name="name" id="editName" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Address</label>
    <input type="text" name="address" id="editAddress" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" id="editPhone" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Cuisine Type</label>
    <input type="text" name="cuisine_type" id="editCuisine" class="form-control">
</div>
<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" id="editStatus" class="form-control">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="closed">Closed</option>
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
<h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Warning: Deactivate Restaurant</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="alert alert-warning">
    <i class="fas fa-info-circle me-2"></i>
    <strong>This action will:</strong>
</div>
<ul>
    <li>Set restaurant status to <span class="badge bg-danger">Inactive</span></li>
    <li>Set user verification to <span class="badge bg-danger">Unverified</span></li>
    <li>Hide restaurant from customers</li>
    <li>Prevent user from logging in</li>
    <li>Stop restaurant from receiving orders</li>
</ul>
<p class="mb-0"><strong>Restaurant:</strong> <span id="warningRestaurantName"></span></p>
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
let currentRestaurantId = null;
let currentRestaurantName = null;

// --- Edit Modal ---
document.addEventListener('click', function(e){
    if(e.target.closest('.edit-btn')){
        const btn = e.target.closest('.edit-btn');
        document.getElementById('editRestaurantId').value = btn.dataset.id;
        document.getElementById('editUserId').value = btn.dataset.user;
        document.getElementById('editName').value = btn.dataset.name;
        document.getElementById('editAddress').value = btn.dataset.address;
        document.getElementById('editPhone').value = btn.dataset.phone;
        document.getElementById('editCuisine').value = btn.dataset.cuisine;
        document.getElementById('editStatus').value = btn.dataset.status;
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }
});

// --- Save Edit ---
document.getElementById('editForm').addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this); 
    fd.append('action','edit_restaurant');
    
    fetch('restaurant_crud.php',{method:'POST', body:fd})
    .then(r => r.json())
    .then(data => {
        if(data.success){ 
            showSuccess('Restaurant updated successfully!');
            setTimeout(() => location.reload(), 1500);
        }
        else alert(data.message);
    });
});

// --- Deactivate with Warning Modal ---
document.addEventListener('click', function(e){
    if(e.target.closest('.deactivate-btn')){
        const btn = e.target.closest('.deactivate-btn');
        currentRestaurantId = btn.dataset.id;
        currentRestaurantName = btn.dataset.name;
        
        document.getElementById('warningRestaurantName').textContent = currentRestaurantName;
        const modal = new bootstrap.Modal(document.getElementById('warningModal'));
        modal.show();
    }
});

// --- Confirm Deactivate ---
document.getElementById('confirmDeactivate').addEventListener('click', function(){
    const fd = new FormData(); 
    fd.append('action','delete'); 
    fd.append('restaurant_id', currentRestaurantId);
    
    fetch('restaurant_crud.php',{method:'POST', body:fd})
    .then(r => r.json())
    .then(data => {
        const warningModal = bootstrap.Modal.getInstance(document.getElementById('warningModal'));
        warningModal.hide();
        
        if(data.success) {
            showSuccess('Restaurant deactivated and user unverified!');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.message);
        }
    });
});

// --- Approve ---
document.addEventListener('click', function(e){
    if(e.target.closest('.activate-btn')){
        const btn = e.target.closest('.activate-btn');
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        
        if(confirm(`Approve "${name}" and verify the user?`)) {
            const fd = new FormData(); 
            fd.append('action','activate'); 
            fd.append('restaurant_id', id);
            
            fetch('restaurant_crud.php',{method:'POST', body:fd})
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    showSuccess('Restaurant activated and user verified!');
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
    const table = document.getElementById('restaurantsTable');
    const rows = Array.from(table.querySelectorAll('tr')).map(row => {
        return Array.from(row.querySelectorAll('td')).map(td => td.textContent);
    });
    
    // Add headers
    const headers = ['ID', 'Vendor Name', 'User ID', 'Restaurant Name', 'Phone', 'Cuisine', 'Status', 'Created', 'Actions'];
    rows.unshift(headers);
    
    const ws = XLSX.utils.aoa_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Restaurants");
    XLSX.writeFile(wb, `restaurants_${new Date().toISOString().slice(0,10)}.xlsx`);
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