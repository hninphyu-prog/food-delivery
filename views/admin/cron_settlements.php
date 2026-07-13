<?php
require_once __DIR__ . "/../../config/db.php";

try {
    $adminId = 1; // system/admin user
    $stmt = $pdo->prepare("CALL generate_weekly_settlements(:adminId)");
    $stmt->execute([':adminId' => $adminId]);
    echo "Weekly settlements generated at " . date("Y-m-d H:i:s") . "\n";
 } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}