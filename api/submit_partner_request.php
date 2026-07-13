<?php
// /foodandme/api/submit_partner_request.php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// --- Basic Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$required_fields = ['role', 'name', 'email', 'phone', 'password'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit;
    }
}

// --- Data Collection ---
$role = $_POST['role'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$adminUserId = 4; // Admin ID

$pdo->beginTransaction();

try {
    // --- Step 1: Create the User entry ---
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, phone, password, role, is_verified, address, created_at) 
         VALUES (?, ?, ?, ?, ?, 0, ?, NOW())"
    );
    
    $userAddress = '';
    if ($role === 'vendor') {
        $userAddress = trim($_POST['address'] ?? '');
    } elseif ($role === 'delivery') {
        $userAddress = trim($_POST['rider_address'] ?? '');
    }
    
    $stmt->execute([$name, $email, $phone, $password, $role, $userAddress]);
    $newUserId = $pdo->lastInsertId();

    $notificationTitle = '';
    $notificationMessage = '';
    
    // --- Step 2: Handle Role-Specific Logic ---
    if ($role === 'vendor') {
        $restaurantName = trim($_POST['restaurant_name'] ?? '');
        $restaurantAddress = trim($_POST['address'] ?? '');
        $cuisineType = trim($_POST['cuisine_type'] ?? '');
        $lat = $_POST['lat'] ?? null;
        $lng = $_POST['lng'] ?? null;
        
        // Validate required fields for vendor
        if (empty($restaurantName)) {
            throw new Exception('Restaurant name is required.');
        }
        
        if (empty($lat) || empty($lng)) {
            throw new Exception('Please select restaurant location on the map.');
        }
        
        // Handle logo upload
        $logoFileName = null; // Store only filename
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/assets/images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $logoName = uniqid() . '_' . basename($_FILES['logo']['name']);
            $logoTarget = $uploadDir . $logoName;
            
            $imageFileType = strtolower(pathinfo($logoTarget, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($imageFileType, $allowedTypes)) {
                throw new Exception('Only JPG, JPEG, PNG, GIF & WebP files are allowed for logo.');
            }
            
            if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                throw new Exception('Logo file is too large (max 5MB).');
            }
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoTarget)) {
                // STORE ONLY FILENAME, NOT PATH
                $logoFileName = $logoName;
            } else {
                throw new Exception('Failed to upload logo.');
            }
        } else {
            throw new Exception('Restaurant logo is required.');
        }
        
        // Insert restaurant - store only filename in logo column
        $restoStmt = $pdo->prepare(
            "INSERT INTO restaurants (user_id, name, address, phone, logo, lat, lng, 
                                     cuisine_type, status, preparation_time, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'inactive', 15, NOW())"
        );
        $restoStmt->execute([
            $newUserId, 
            $restaurantName, 
            $restaurantAddress,
            $phone,
            $logoFileName, // Only filename, no path
            floatval($lat),
            floatval($lng),
            $cuisineType
        ]);
        
        $restaurantId = $pdo->lastInsertId();
        $notificationTitle = 'Partner Request';
        $notificationMessage = "New restaurant registration: " . $restaurantName;

    } elseif ($role === 'delivery') {
        $riderAddress = trim($_POST['rider_address'] ?? '');
        
        if (empty($riderAddress)) {
            throw new Exception('Rider address is required.');
        }
        
        $riderId = 'RID' . str_pad($newUserId, 6, '0', STR_PAD_LEFT);
        $riderStmt = $pdo->prepare(
            "INSERT INTO riders (rider_id, user_id, status) 
             VALUES (?, ?, 'inactive')"
        );
        $riderStmt->execute([$riderId, $newUserId]);
        
        $notificationTitle = 'Partner Request';
        $notificationMessage = "New rider registration: " . $name;
        
    } else {
        throw new Exception('Invalid role specified.');
    }

    // --- Step 3: Create Notification in request_notifications table ---
    $notiStmt = $pdo->prepare(
        "INSERT INTO request_notifications (user_id, title, message) VALUES (?, ?, ?)"
    );
    $notiStmt->execute([$adminUserId, $notificationTitle, $notificationMessage]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Application submitted successfully! Our team will review your application within 24-48 hours.'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    
    if ($e instanceof PDOException) {
        if ($e->errorInfo[1] == 1062) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>