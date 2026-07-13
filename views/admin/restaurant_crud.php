<?php
session_start();
require_once "../../config/db.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    // Get user_id for both activate and delete actions
    if ($action === "edit_restaurant") {
        $id     = $_POST['restaurant_id'];
        $name   = $_POST['name'];
        $addr   = $_POST['address'];
        $phone  = $_POST['phone'];
        $cuisine= $_POST['cuisine_type'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE restaurants 
                               SET name=?, address=?, phone=?, cuisine_type=?, status=? 
                               WHERE restaurant_id=?");
        $stmt->execute([$name, $addr, $phone, $cuisine, $status, $id]);

        echo json_encode(["success"=>true]);
        exit;
    }

    if ($action === "delete") { // deactivate
        $id = $_POST['restaurant_id'];
        
        // First get the user_id from the restaurant
        $getUserStmt = $pdo->prepare("SELECT user_id FROM restaurants WHERE restaurant_id=?");
        $getUserStmt->execute([$id]);
        $restaurant = $getUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($restaurant) {
            $user_id = $restaurant['user_id'];
            
            // Start transaction to ensure both updates happen
            $pdo->beginTransaction();
            
            // 1. Deactivate restaurant
            $stmt = $pdo->prepare("UPDATE restaurants SET status='inactive' WHERE restaurant_id=?");
            $stmt->execute([$id]);
            
            // 2. Unverify user (set is_verified = 0)
            $userStmt = $pdo->prepare("UPDATE users SET is_verified=0 WHERE user_id=?");
            $userStmt->execute([$user_id]);
            
            $pdo->commit();
            
            echo json_encode(["success"=>true, "message" => "Restaurant deactivated and user unverified"]);
        } else {
            echo json_encode(["success"=>false, "message" => "Restaurant not found"]);
        }
        exit;
    }

    if ($action === "activate") {
        $id = $_POST['restaurant_id'];
        
        // First get the user_id from the restaurant
        $getUserStmt = $pdo->prepare("SELECT user_id FROM restaurants WHERE restaurant_id=?");
        $getUserStmt->execute([$id]);
        $restaurant = $getUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($restaurant) {
            $user_id = $restaurant['user_id'];
            
            // Start transaction to ensure both updates happen
            $pdo->beginTransaction();
            
            // 1. Activate restaurant
            $stmt = $pdo->prepare("UPDATE restaurants SET status='active' WHERE restaurant_id=?");
            $stmt->execute([$id]);
            
            // 2. Verify user (set is_verified = 1)
            $userStmt = $pdo->prepare("UPDATE users SET is_verified=1 WHERE user_id=?");
            $userStmt->execute([$user_id]);
            
            $pdo->commit();
            
            echo json_encode(["success"=>true, "message" => "Restaurant activated and user verified"]);
        } else {
            echo json_encode(["success"=>false, "message" => "Restaurant not found"]);
        }
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