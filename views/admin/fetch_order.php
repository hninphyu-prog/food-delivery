<?php
session_start();
require_once '../../config/db.php';

// Initialize filters
$where = [];
$params = [];

// Filter by order status
if (!empty($_GET['status'])) {
    $where[] = "o.order_status = ?";
    $params[] = $_GET['status'];
}

// Search by user name, restaurant name, or order ID
if (!empty($_GET['search'])) {
    $where[] = "(u.name LIKE ? OR r.name LIKE ? OR o.order_id LIKE ?)";
    $params[] = "%" . $_GET['search'] . "%";
    $params[] = "%" . $_GET['search'] . "%";
    $params[] = "%" . $_GET['search'] . "%";
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// ✅ Fixed query (removed payments table)
$sql = "
    SELECT 
        o.order_id, 
        u.name AS user_name, 
        r.name AS restaurant_name,
        o.total_amount, 
        o.payment_status, 
        o.order_status, 
        o.created_at,
        db.name AS delivery_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN restaurants r ON o.restaurant_id = r.restaurant_id
    LEFT JOIN delivery d ON o.order_id = d.order_id
    LEFT JOIN users db ON d.delivery_boy_id = db.user_id
    $whereSQL
    ORDER BY o.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Output rows
if ($orders) {
    foreach ($orders as $o) {
        echo "<tr>
            <td>#{$o['order_id']}</td>
            <td>" . htmlspecialchars($o['user_name']) . "</td>
            <td>" . htmlspecialchars($o['restaurant_name']) . "</td>
            <td>" . number_format($o['total_amount'], 2) . "</td>
            <td><span class='badge {$o['payment_status']}'>" . ucfirst($o['payment_status']) . "</span></td>
         
            <td>" . ($o['delivery_name'] 
                ? htmlspecialchars($o['delivery_name']) 
                : "<span class='badge unassigned'>Unassigned</span>") . "</td>
            <td><span class='badge {$o['order_status']}'>" . ucfirst(str_replace('_', ' ', $o['order_status'])) . "</span></td>
            <td>" . date("Y-m-d H:i", strtotime($o['created_at'])) . "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='9'>No orders found.</td></tr>";
}
?>
