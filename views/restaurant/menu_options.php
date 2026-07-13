<?php
// Start output buffering to prevent 'headers already sent' errors
ob_start();

// Start session and include database connection
// session_start();
require_once __DIR__ . '/../../config/db.php';

// Check if item_id is provided
if (!isset($_GET['item_id']) || !isset($_SESSION['restaurant_id'])) {
    die("Invalid request");
}

$item_id = $_GET['item_id'];
$restaurant_id = $_SESSION['restaurant_id'];

// Verify item belongs to restaurant
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE item_id = ? AND restaurant_id = ?");
$stmt->execute([$item_id, $restaurant_id]);
$menu_item = $stmt->fetch();

if (!$menu_item) {
    die("Menu item not found or access denied");
}

// Handle form submissions for options
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_option'])) {
        $option_name = $_POST['option_name'];
        $option_type = $_POST['option_type'];
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO menu_options (option_name, option_type, is_required) VALUES (?, ?, ?)");
        $stmt->execute([$option_name, $option_type, $is_required]);
        $option_id = $pdo->lastInsertId();
        
        // Link option to menu item
        $stmt = $pdo->prepare("INSERT INTO menu_item_options (item_id, option_id) VALUES (?, ?)");
        $stmt->execute([$item_id, $option_id]);
        
        $_SESSION['success'] = "Option added successfully!";
        ob_end_clean();
        header("Location: index.php?page=menu_options&item_id=$item_id");
        exit();
    }
    
    if (isset($_POST['edit_option'])) {
        $option_id = $_POST['option_id'];
        $option_name = $_POST['option_name'];
        $option_type = $_POST['option_type'];
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE menu_options SET option_name = ?, option_type = ?, is_required = ? WHERE option_id = ?");
        $stmt->execute([$option_name, $option_type, $is_required, $option_id]);
        
        $_SESSION['success'] = "Option updated successfully!";
        ob_end_clean();
        header("Location: index.php?page=menu_options&item_id=$item_id");
        exit();
    }
    
    if (isset($_POST['delete_option'])) {
        $option_id = $_POST['option_id'];
        
        // Delete option values first
        $stmt = $pdo->prepare("DELETE FROM option_values WHERE option_id = ?");
        $stmt->execute([$option_id]);
        
        // Delete from menu_item_options
        $stmt = $pdo->prepare("DELETE FROM menu_item_options WHERE option_id = ? AND item_id = ?");
        $stmt->execute([$option_id, $item_id]);
        
        // Delete the option
        $stmt = $pdo->prepare("DELETE FROM menu_options WHERE option_id = ?");
        $stmt->execute([$option_id]);
        
        $_SESSION['success'] = "Option deleted successfully!";
        ob_end_clean();
        header("Location: index.php?page=menu_options&item_id=$item_id");
        exit();
    }
    
    if (isset($_POST['add_value'])) {
        $option_id = $_POST['option_id'];
        $value_name = $_POST['value_name'];
        $price_modifier = $_POST['price_modifier'];
        
        $stmt = $pdo->prepare("INSERT INTO option_values (option_id, value_name, price_modifier) VALUES (?, ?, ?)");
        $stmt->execute([$option_id, $value_name, $price_modifier]);
        
        $_SESSION['success'] = "Option value added successfully!";
        // Clear the output buffer before redirecting
        ob_end_clean();
        header("Location: index.php?page=menu_options&item_id=$item_id");
        exit();
    }
    
    if (isset($_POST['edit_value'])) {
        $value_id = $_POST['value_id'];
        $value_name = $_POST['value_name'];
        $price_modifier = $_POST['price_modifier'];
        
        $stmt = $pdo->prepare("UPDATE option_values SET value_name = ?, price_modifier = ? WHERE value_id = ?");
        $stmt->execute([$value_name, $price_modifier, $value_id]);
        
        $_SESSION['success'] = "Option value updated successfully!";
        ob_end_clean();
        header("Location: index.php?page=menu_options&item_id=$item_id");
        exit();
    }
    
    if (isset($_POST['delete_value'])) {
        $value_id = $_POST['value_id'];
        
        $stmt = $pdo->prepare("DELETE FROM option_values WHERE value_id = ?");
        $stmt->execute([$value_id]);
        
        $_SESSION['success'] = "Option value deleted successfully!";
        ob_end_clean();
        header("Location: index.php?page=menu_options&item_id=$item_id");
        exit();
    }
}

// Get options for this menu item
$stmt = $pdo->prepare("
    SELECT mo.* FROM menu_options mo
    JOIN menu_item_options mio ON mo.option_id = mio.option_id
    WHERE mio.item_id = ?
    ORDER BY mo.option_name
");
$stmt->execute([$item_id]);
$options = $stmt->fetchAll();

// Get all available options for this restaurant (not linked ones)
$all_options_stmt = $pdo->prepare("
    SELECT mo.* FROM menu_options mo 
    WHERE mo.option_id NOT IN (
        SELECT mio.option_id FROM menu_item_options mio 
        WHERE mio.item_id = ?
    )
    ORDER BY mo.option_name
");
$all_options_stmt->execute([$item_id]);
$all_options = $all_options_stmt->fetchAll();
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Options for: <?php echo htmlspecialchars($menu_item['name']); ?></h3>
            <a href="index.php?page=menu" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Menu
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Add New Option -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Add New Option</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="option_name" class="form-label">Option Name *</label>
                            <input type="text" class="form-control" id="option_name" name="option_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="option_type" class="form-label">Option Type *</label>
                            <select class="form-select" id="option_type" name="option_type" required>
                                <option value="single_select">Single Select</option>
                                <option value="multi_select">Multi Select</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_required" name="is_required">
                            <label class="form-check-label" for="is_required">Required</label>
                        </div>
                        <button type="submit" name="add_option" class="btn btn-primary">Add Option</button>
                    </form>
                </div>
            </div>
            
            <!-- Select Linked Option -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Link Existing Option</h5>
                </div>
                <div class="card-body">
                    <!-- <form method="POST" action="link_option.php" id="linkOptionForm">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                        <div class="mb-3">
                            <label for="linked_option" class="form-label">Select Option to Link</label>
                            <select class="form-select" id="linked_option" name="option_id" required>
                                <option value="">Choose an option...</option>
                                <?php foreach ($all_options as $option): ?>
                                    <option value="<?php echo $option['option_id']; ?>">
                                        <?php echo htmlspecialchars($option['option_name']); ?> 
                                        (<?php echo $option['option_type']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Link Option</button>
                    </form> -->
                    
                    <!-- Filter Options -->
                    <div class="mt-3">
                        <label for="filter_option" class="form-label"> Current Options</label>
                        <select class="form-select" id="filter_option" onchange="filterOptions()">
                            <option value="">Show All Options</option>
                            <?php foreach ($options as $option): ?>
                                <option value="option_<?php echo $option['option_id']; ?>">
                                    <?php echo htmlspecialchars($option['option_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Current Options -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Current Options</h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showAllOptions()">
                        <i class="fas fa-eye"></i> Show All
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($options)): ?>
                        <p class="text-muted">No options configured for this item.</p>
                    <?php else: ?>
                        <?php foreach ($options as $option): ?>
                            <div class="option-card mb-4 border rounded p-3" data-option-id="<?php echo $option['option_id']; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6><?php echo htmlspecialchars($option['option_name']); ?></h6>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editOptionModal"
                                                onclick="editOption(<?php echo $option['option_id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteOptionModal"
                                                onclick="setDeleteOption(<?php echo $option['option_id']; ?>, '<?php echo htmlspecialchars($option['option_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info"
                                                onclick="showOnlyOption(<?php echo $option['option_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="small text-muted mb-2">
                                    Type: <?php echo $option['option_type']; ?> | 
                                    Required: <?php echo $option['is_required'] ? 'Yes' : 'No'; ?>
                                </div>
                                
                                <!-- Option Values -->
                                <div class="values-section">
                                    <h6 class="mb-2">Values:</h6>
                                    <?php
                                    $values_stmt = $pdo->prepare("SELECT * FROM option_values WHERE option_id = ? ORDER BY value_name");
                                    $values_stmt->execute([$option['option_id']]);
                                    $values = $values_stmt->fetchAll();
                                    ?>
                                    
                                    <?php if (empty($values)): ?>
                                        <p class="text-muted small">No values added.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Value</th>
                                                        <th>Price Modifier</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($values as $value): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($value['value_name']); ?></td>
                                                            <td><?php echo number_format($value['price_modifier'], 2); ?> MMK</td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <button type="button" class="btn btn-outline-primary"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#editValueModal"
                                                                            onclick="editValue(<?php echo $value['value_id']; ?>)">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-danger"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#deleteValueModal"
                                                                            onclick="setDeleteValue(<?php echo $value['value_id']; ?>, '<?php echo htmlspecialchars($value['value_name']); ?>')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Add Value Button -->
                                    <button type="button" class="btn btn-outline-success btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addValueModal"
                                            onclick="setAddValueOption(<?php echo $option['option_id']; ?>)">
                                        <i class="fas fa-plus"></i> Add Value
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Value Modal -->
<div class="modal fade" id="addValueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="add_value_option_id" name="option_id">
                <div class="modal-header">
                    <h5 class="modal-title">Add Option Value</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="value_name" class="form-label">Value Name *</label>
                        <input type="text" class="form-control" id="value_name" name="value_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="price_modifier" class="form-label">Price Modifier (MMK)</label>
                        <input type="number" class="form-control" id="price_modifier" name="price_modifier" step="0.01" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_value" class="btn btn-primary">Add Value</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Option Modal -->
<div class="modal fade" id="editOptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="edit_option_id" name="option_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_option_name" class="form-label">Option Name *</label>
                        <input type="text" class="form-control" id="edit_option_name" name="option_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_option_type" class="form-label">Option Type *</label>
                        <select class="form-select" id="edit_option_type" name="option_type" required>
                            <option value="single_select">Single Select</option>
                            <option value="multi_select">Multi Select</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_required" name="is_required">
                        <label class="form-check-label" for="edit_is_required">Required</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_option" class="btn btn-primary">Update Option</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Value Modal -->
<div class="modal fade" id="editValueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="edit_value_id" name="value_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Option Value</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_value_name" class="form-label">Value Name *</label>
                        <input type="text" class="form-control" id="edit_value_name" name="value_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price_modifier" class="form-label">Price Modifier (MMK)</label>
                        <input type="number" class="form-control" id="edit_price_modifier" name="price_modifier" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_value" class="btn btn-primary">Update Value</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Option Modal -->
<div class="modal fade" id="deleteOptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="delete_option_id" name="option_id">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete option <strong id="delete_option_name"></strong>?</p>
                    <p class="text-danger">This will also delete all associated values.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_option" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Value Modal -->
<div class="modal fade" id="deleteValueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="delete_value_id" name="value_id">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete value <strong id="delete_value_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_value" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set option for adding value
function setAddValueOption(optionId) {
    document.getElementById('add_value_option_id').value = optionId;
}

// Edit option
function editOption(optionId) {
    fetch(`get_option.php?option_id=${optionId}`)
        .then(response => response.json())
        .then(option => {
            document.getElementById('edit_option_id').value = option.option_id;
            document.getElementById('edit_option_name').value = option.option_name;
            document.getElementById('edit_option_type').value = option.option_type;
            document.getElementById('edit_is_required').checked = option.is_required == 1;
        })
        .catch(error => console.error('Error:', error));
}

// Edit value
function editValue(valueId) {
    fetch(`get_option_value.php?value_id=${valueId}`)
        .then(response => response.json())
        .then(value => {
            document.getElementById('edit_value_id').value = value.value_id;
            document.getElementById('edit_value_name').value = value.value_name;
            document.getElementById('edit_price_modifier').value = value.price_modifier;
        })
        .catch(error => console.error('Error:', error));
}

// Set delete option
function setDeleteOption(optionId, optionName) {
    document.getElementById('delete_option_id').value = optionId;
    document.getElementById('delete_option_name').textContent = optionName;
}

// Set delete value
function setDeleteValue(valueId, valueName) {
    document.getElementById('delete_value_id').value = valueId;
    document.getElementById('delete_value_name').textContent = valueName;
}

// Filter options based on selection
function filterOptions() {
    const filterSelect = document.getElementById('filter_option');
    const selectedValue = filterSelect.value;
    const optionCards = document.querySelectorAll('.option-card');
    
    if (selectedValue === '') {
        // Show all options
        optionCards.forEach(card => {
            card.style.display = 'block';
        });
    } else {
        // Show only selected option
        optionCards.forEach(card => {
            const optionId = card.getAttribute('data-option-id');
            if (optionId && `option_${optionId}` === selectedValue) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
}

// Quick view function to show only one option
function showOnlyOption(optionId) {
    const optionCards = document.querySelectorAll('.option-card');
    optionCards.forEach(card => {
        const cardOptionId = card.getAttribute('data-option-id');
        if (cardOptionId == optionId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Set the filter dropdown to match
    document.getElementById('filter_option').value = `option_${optionId}`;
}

// Show all options
function showAllOptions() {
    const optionCards = document.querySelectorAll('.option-card');
    optionCards.forEach(card => {
        card.style.display = 'block';
    });
    document.getElementById('filter_option').value = '';
}
</script>