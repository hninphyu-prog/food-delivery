<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/db.php';

if (isset($_GET['restaurant_id'])) {
    $_SESSION['restaurant_id'] = $_GET['restaurant_id'];
}
// if (!isset($_GET['item_id']) || !isset($_SESSION['restaurant_id'])) {
//     // die("Invalid request");
//     $item_id = $_GET['item_id'];

// $restaurant_id = $_SESSION['restaurant_id'] ?? null;
// }


$restaurant_id = $_SESSION['restaurant_id'] ?? null;
if (isset($_GET['item_id'])){
   $_SESSION['item_id'] = $_GET['item_id']; 
     $item_id = $_GET['item_id'];

}
$item_id = $_SESSION['item_id'] ?? null;

if (!$restaurant_id) {
    die("No restaurant selected. Please select a restaurant first.");
}

$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ? AND user_id = ?");
$stmt->execute([$restaurant_id, $_SESSION['user_id']]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    die("You don't have permission to access this restaurant");
}

$_SESSION['restaurant_name'] = $restaurant['name'];
$_SESSION['restaurant_address'] = $restaurant['address'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['add_item'])) {
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
        $item_id = $_POST['item_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        // Handle image update logic here...
        $image = $_POST['current_image']; // Start with the current image
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
        $item_id = $_POST['item_id'];
        
        $stmt = $pdo->prepare("UPDATE menu_items SET status= 0 WHERE item_id = ? AND restaurant_id = ?");
        $stmt->execute([$item_id, $restaurant_id]);
        
        $_SESSION['success'] = "Menu item deleted successfully!";
        header("Location: index.php?page=menu");
        exit(); 
    }

    if (isset($_POST['delete_category'])) {
        $category_to_delete = $_POST['category_to_delete'];
            $stmt = $pdo->prepare("UPDATE menu_items SET status= 0 WHERE category = ? AND restaurant_id = ?");

        $stmt->execute([$category_to_delete, $restaurant_id]);
        
        $_SESSION['success'] = "All items in category '" . htmlspecialchars($category_to_delete) . "' have been deleted successfully!";
        header("Location: index.php?page=menu");
        exit();
    }
}

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

include 'header.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'dashboard':
        include 'dashboard.php';
        break;
    case 'menu':
        include 'menu_manager.php';
        break;
    case 'menu_options':
        include 'menu_options.php';
        break;
    case 'orders':
        include 'order_manager.php';
        break;
    case 'notifications':
        include 'confirm_settlements.php';
        break;
    case 'financial':
        include 'financial.php';
        break;
    case 'logout':
        include 'logout.php';
        break;
    case 'profile':
        include 'profile.php';
        break;
    default:
        include 'dashboard.php';
}

include 'footer.php';
?>