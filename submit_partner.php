<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Required fields
$required = ['role', 'name', 'email', 'phone', 'password'];
foreach ($required as $f) {
    if (empty($_POST[$f])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit;
    }
}

$role = $_POST['role'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$pdo->beginTransaction();

try {
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$name, $email, $phone, $password, $role]);
    $user_id = $pdo->lastInsertId();

    // Handle logo upload (vendors only)
    $logoFileName = null; // only filename
    if ($role === 'vendor' && isset($_FILES['restaurant_logo']) && $_FILES['restaurant_logo']['error'] === 0) {
        $uploadDir = __DIR__ . '/../assets/images/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['restaurant_logo']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['restaurant_logo']['tmp_name'], $targetPath)) {
            $logoFileName = $fileName; // save only the file name
        }
    }

    if ($role === 'vendor') {
        $restaurant_name    = trim($_POST['restaurant_name'] ?? '');
        $restaurant_address = trim($_POST['restaurant_address'] ?? '');
        $cuisine_type       = trim($_POST['cuisine_type'] ?? '');
        $latitude           = $_POST['latitude'] ?? null;
        $longitude          = $_POST['longitude'] ?? null;

        // Save restaurant including phone
        $stmt = $pdo->prepare("
            INSERT INTO restaurants (user_id, name, address, cuisine_type, lat, lng, logo, phone, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'inactive')
        ");
        $stmt->execute([$user_id, $restaurant_name, $restaurant_address, $cuisine_type, $latitude, $longitude, $logoFileName, $phone]);

        // Update user address
        $stmt = $pdo->prepare("UPDATE users SET address = ? WHERE user_id = ?");
        $stmt->execute([$restaurant_address, $user_id]);

        $title = 'New Vendor Request';

    } elseif ($role === 'delivery') {
        $rider_address = trim($_POST['rider_address'] ?? '');
        $stmt = $pdo->prepare("UPDATE users SET address = ? WHERE user_id = ?");
        $stmt->execute([$rider_address, $user_id]);

        $title = 'New Rider Request';
    } else {
        throw new Exception('Invalid role');
    }

    // Notify admin
    $adminUserId = 4;
    $message = json_encode(['new_user_id' => $user_id]);
    $stmt = $pdo->prepare("INSERT INTO request_notifications (user_id, title, message) VALUES (?, ?, ?)");
    $stmt->execute([$adminUserId, $title, $message]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);

} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->errorInfo[1] == 1062) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
