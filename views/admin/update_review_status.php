<?php
session_start();
require_once "../../config/db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];
    $current = $_POST['status'];

    // Toggle
    $new = ($current === "visible") ? "hidden" : "visible";

    $stmt = $pdo->prepare("UPDATE reviews SET status = :st WHERE review_id = :id");
    $stmt->execute([
        ":st" => $new,
        ":id" => $id
    ]);

    echo json_encode([
        "success" => true,
        "new_status" => $new
    ]);

    exit;
}

echo json_encode(["success" => false]);
