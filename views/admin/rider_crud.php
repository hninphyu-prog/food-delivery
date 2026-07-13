<?php
session_start();
require_once "../../config/db.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    // Get user_id for both activate and delete actions
    if ($action === "edit_rider") {
        $rider_id = $_POST['rider_id'];
        $user_id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $status = $_POST['status'];

        // Start transaction
        $pdo->beginTransaction();
        
        // 1. Update users table
        $stmt = $pdo->prepare("UPDATE users 
                               SET name=?, email=?, phone=? 
                               WHERE user_id=?");
        $stmt->execute([$name, $email, $phone, $user_id]);
        
        // 2. Update riders table
        $stmt = $pdo->prepare("UPDATE riders 
                               SET status=? 
                               WHERE rider_id=?");
        $stmt->execute([$status, $rider_id]);
        
        // 3. Update user verification based on rider status
        $is_verified = ($status === 'active') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE users 
                               SET is_verified=? 
                               WHERE user_id=?");
        $stmt->execute([$is_verified, $user_id]);
        
        $pdo->commit();
        
        echo json_encode(["success"=>true, "message" => "Rider updated successfully"]);
        exit;
    }

    if ($action === "delete") { // deactivate rider
        $rider_id = $_POST['rider_id'];
        
        // Start transaction
        $pdo->beginTransaction();
        
        // 1. Get user_id from rider
        $getUserStmt = $pdo->prepare("SELECT user_id FROM riders WHERE rider_id=?");
        $getUserStmt->execute([$rider_id]);
        $rider = $getUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rider) {
            throw new Exception("Rider not found");
        }
        
        $user_id = $rider['user_id'];
        
        // 2. Deactivate rider
        $stmt = $pdo->prepare("UPDATE riders SET status='inactive' WHERE rider_id=?");
        $stmt->execute([$rider_id]);
        
        // 3. Unverify user
        $stmt = $pdo->prepare("UPDATE users SET is_verified=0 WHERE user_id=?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        echo json_encode(["success"=>true, "message" => "Rider deactivated and user unverified"]);
        exit;
    }

    if ($action === "activate") {
        $rider_id = $_POST['rider_id'];
        
        // Start transaction
        $pdo->beginTransaction();
        
        // 1. Get user_id from rider
        $getUserStmt = $pdo->prepare("SELECT user_id FROM riders WHERE rider_id=?");
        $getUserStmt->execute([$rider_id]);
        $rider = $getUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rider) {
            throw new Exception("Rider not found");
        }
        
        $user_id = $rider['user_id'];
        
        // 2. Activate rider
        $stmt = $pdo->prepare("UPDATE riders SET status='active' WHERE rider_id=?");
        $stmt->execute([$rider_id]);
        
        // 3. Verify user
        $stmt = $pdo->prepare("UPDATE users SET is_verified=1 WHERE user_id=?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        echo json_encode(["success"=>true, "message" => "Rider activated and user verified"]);
        exit;
    }

    echo json_encode(["success"=>false,"message"=>"Invalid action"]);

} catch (Exception $e) {
    // Rollback transaction if something went wrong
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}