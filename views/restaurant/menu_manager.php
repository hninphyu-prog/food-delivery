<?php
// Check if restaurant_id is set in session
if (!isset($_SESSION['restaurant_id'])) {
    die("Restaurant not selected. Please select a restaurant first.");
}

$restaurant_id = $_SESSION['restaurant_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_item'])) {
        // Add new menu item
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = "../../assets/images/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $filename;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO menu_items (restaurant_id, name, description, price, image, category, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$restaurant_id, $name, $description, $price, $image, $category, $is_available]);
        
        $_SESSION['success'] = "Menu item added successfully!";
        header("Location: index.php?page=menu");
        exit();
    }
    
    if (isset($_POST['edit_item'])) {
        // Edit existing menu item
        $item_id = $_POST['item_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        // Handle image upload
        $image = $_POST['current_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = "../../assets/images/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old image if exists
            if ($image && file_exists($upload_dir . $image)) {
                unlink($upload_dir . $image);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $filename;
            }
        }
        
        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, image = ?, category = ?, is_available = ? WHERE item_id = ? AND restaurant_id = ?");
        $stmt->execute([$name, $description, $price, $image, $category, $is_available, $item_id, $restaurant_id]);
        
        $_SESSION['success'] = "Menu item updated successfully!";
        header("Location: index.php?page=menu");
        exit();
    }
    
    if (isset($_POST['delete_item'])) {
        // Delete menu item
        $item_id = $_POST['item_id'];
        
        // Get image path to delete
        $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE item_id = ? AND restaurant_id = ?");
        $stmt->execute([$item_id, $restaurant_id]);
        $item = $stmt->fetch();
        
        if ($item && $item['image']) {
            $upload_dir = "../../assets/images/";
            if (file_exists($upload_dir . $item['image'])) {
                unlink($upload_dir . $item['image']);
            }
        }
        
        // Delete from database
        $stmt = $pdo->prepare("UPDATE menu_items SET status= 0 WHERE item_id = ? AND restaurant_id = ?");
        $stmt->execute([$item_id, $restaurant_id]);
        
        $_SESSION['success'] = "Menu item deleted successfully!";
        header("Location: index.php?page=menu");
        exit();
    }

    // Add this block inside the main POST request handler
    if (isset($_POST['delete_category'])) {
        // Update status to 0 for all items in the category (soft delete)
        $category_to_delete = $_POST['category_to_delete'];
        $stmt = $pdo->prepare("UPDATE menu_items SET status = 0 WHERE category = ? AND restaurant_id = ?");
        $stmt->execute([$category_to_delete, $restaurant_id]);
        
        $_SESSION['success'] = "All items in category '" . htmlspecialchars($category_to_delete) . "' have been archived successfully!";
        header("Location: index.php?page=menu");
        exit();
    }
}

// Get all menu items for this restaurant
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id = ? AND status=1 ORDER BY category, name");
$stmt->execute([$restaurant_id]);
$menu_items = $stmt->fetchAll();

// Get unique categories for filter
$categories_stmt = $pdo->prepare("SELECT DISTINCT category FROM menu_items WHERE restaurant_id = ? AND category IS NOT NULL AND status=1 ORDER BY category");
$categories_stmt->execute([$restaurant_id]);
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="content">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>Menu Items</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus"></i> Add New Item
            </button>
        </div>
        <div class="card-body">
            <!-- Category Filter -->
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    
    <div>
        <label for="categoryFilter" class="form-label">Filter by Category:</label>
        <select id="categoryFilter" class="form-select" style="min-width: 250px;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div>
        <label for="categoryDelete" class="form-label text-danger">Delete Whole Category:</label>
        <div class="input-group">
            <select id="categoryDelete" class="form-select" style="min-width: 250px;">
                <option value="" selected>Select a category to delete...</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
            </select>
            <button id="deleteCategoryBtn" type="button" class="btn btn-outline-danger ms-2" disabled>
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>

</div>
            
            <!-- Menu Items Table -->
            <div class="table-responsive">
                <table class="table table-striped" id="menuTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($menu_items)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No menu items found. Add your first item!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($menu_items as $item): ?>
                                <tr data-category="<?php echo htmlspecialchars($item['category']); ?>">
                                    <td>
                                        <?php if ($item['image']): ?>
                                            <img src="../../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="no-image" style="width: 50px; height: 50px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-utensils text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['description'] ?? 'No description'); ?></td>
                                    <td><?php echo number_format($item['price'], 2); ?> MMK</td>
                                    <td><?php echo htmlspecialchars($item['category'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $item['is_available'] ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editItemModal" 
                                                    onclick="editItem(<?php echo $item['item_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="manageOptions(<?php echo $item['item_id']; ?>)">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteItemModal" 
                                                    onclick="setDeleteItem(<?php echo $item['item_id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="addItemForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (MMK) *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" list="categories">
                                <datalist id="categories">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this, 'addPreview')">
                                
                                <!-- Image Preview for Add Modal -->
                                <div class="mt-2" id="addPreviewContainer" style="display: none;">
                                    <label class="form-label">Image Preview:</label>
                                    <div class="border rounded p-2 text-center">
                                        <img id="addPreview" src="#" alt="Image Preview" class="img-fluid" style="max-height: 150px;">
                                        <!-- <div class="mt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('add')">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_available" name="is_available" checked>
                        <label class="form-check-label" for="is_available">Available for ordering</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="editItemForm">
                <input type="hidden" id="edit_item_id" name="item_id">
                <input type="hidden" id="current_image" name="current_image">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_price" class="form-label">Price (MMK) *</label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="edit_category" name="category" list="categories">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="edit_image" name="image" accept="image/*" onchange="previewImage(this, 'editPreview')">
                                
                                <!-- Current Image -->
                                <div class="mt-2" id="currentImageContainer">
                                    <label class="form-label">Current Image:</label>
                                    <div id="current_image_preview" class="border rounded p-2 text-center"></div>
                                </div>
                                
                                <!-- New Image Preview -->
                                <div class="mt-2" id="editPreviewContainer" style="display: none;">
                                    <label class="form-label">New Image Preview:</label>
                                    <div class="border rounded p-2 text-center">
                                        <img id="editPreview" src="#" alt="New Image Preview" class="img-fluid" style="max-height: 150px;">
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('edit')">
                                                <i class="fas fa-times"></i> Remove New Image
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_available" name="is_available">
                        <label class="form-check-label" for="edit_is_available">Available for ordering</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_item" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="delete_item_id" name="item_id">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_item_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_item" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="delete_category_name_input" name="category_to_delete">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle"></i> Confirm Category Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete all menu items in the category <strong id="delete_category_name_display"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_category" class="btn btn-danger">Yes, Delete All</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const category = this.value;
    const rows = document.querySelectorAll('#menuTable tbody tr');
    
    rows.forEach(row => {
        if (category === '' || row.getAttribute('data-category') === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Edit item function
function editItem(itemId) {
    fetch(`get_menu_item.php?item_id=${itemId}`)
        .then(response => response.json())
        .then(item => {
            document.getElementById('edit_item_id').value = item.item_id;
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_price').value = item.price;
            document.getElementById('edit_description').value = item.description || '';
            document.getElementById('edit_category').value = item.category || '';
            document.getElementById('current_image').value = item.image || '';
            document.getElementById('edit_is_available').checked = item.is_available == 1;
            
            // Show current image preview
            const preview = document.getElementById('current_image_preview');
            if (item.image) {
                preview.innerHTML = `<img src="../../assets/images/${item.image}" 
                                        alt="Current Image" 
                                        class="img-fluid" 
                                        style="max-height: 150px;">
                                   <div class="mt-1">
                                       <small class="text-muted">Current image</small>
                                   </div>`;
            } else {
                preview.innerHTML = '<div class="text-muted p-3"><i class="fas fa-image fa-2x"></i><br><small>No image</small></div>';
            }
            
            // Reset new image preview
            document.getElementById('editPreviewContainer').style.display = 'none';
            document.getElementById('edit_image').value = '';
        })
        .catch(error => console.error('Error:', error));
}

// Set delete item details
function setDeleteItem(itemId, itemName) {
    document.getElementById('delete_item_id').value = itemId;
    document.getElementById('delete_item_name').textContent = itemName;
}

// Manage options - redirect to options management page
function manageOptions(itemId) {
    window.location.href = `index.php?page=menu_options&item_id=${itemId}`;
}

// Image preview function
function previewImage(input, previewType) {
    const preview = document.getElementById(previewType);
    const previewContainer = document.getElementById(previewType + 'Container');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
            
            // Hide current image container in edit modal when new image is selected
            if (previewType === 'editPreview') {
                document.getElementById('currentImageContainer').style.display = 'none';
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.style.display = 'none';
        
        // Show current image container again if no new image is selected in edit modal
        if (previewType === 'editPreview') {
            document.getElementById('currentImageContainer').style.display = 'block';
        }
    }
}

// Remove image function
function removeImage(type) {
    if (type === 'add') {
        document.getElementById('image').value = '';
        document.getElementById('addPreviewContainer').style.display = 'none';
    } else if (type === 'edit') {
        document.getElementById('edit_image').value = '';
        document.getElementById('editPreviewContainer').style.display = 'none';
        document.getElementById('currentImageContainer').style.display = 'block';
    }
}

// Reset add modal when closed
document.getElementById('addItemModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('addItemForm').reset();
    document.getElementById('addPreviewContainer').style.display = 'none';
});

// Reset edit modal when closed
document.getElementById('editItemModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('editPreviewContainer').style.display = 'none';
    document.getElementById('currentImageContainer').style.display = 'block';
});

// Handle Delete Category feature
const categoryDeleteSelect = document.getElementById('categoryDelete');
const deleteCategoryBtn = document.getElementById('deleteCategoryBtn');

categoryDeleteSelect.addEventListener('change', function() {
    // Enable the delete button only if a category is selected
    if (this.value !== '') {
        deleteCategoryBtn.disabled = false;
    } else {
        deleteCategoryBtn.disabled = true;
    }
});

deleteCategoryBtn.addEventListener('click', function() {
    const categoryToDelete = categoryDeleteSelect.value;
    if (categoryToDelete) {
        // Set the category name in the modal's confirmation text
        document.getElementById('delete_category_name_display').textContent = `'${categoryToDelete}'`;
        
        // Set the category name in the hidden input for the form submission
        document.getElementById('delete_category_name_input').value = categoryToDelete;
        
        // Show the confirmation modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
        deleteModal.show();
    }
});
</script>