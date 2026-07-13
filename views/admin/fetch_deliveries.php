<?php
session_start();
require_once '../../config/db.php';

// Base query: latest status from delivery_tracking
$sql = "
    SELECT d.delivery_id, o.order_id, u.name AS rider_name, 
           COALESCE(dt.status, 'N/A') AS status, 
           dt.last_update
    FROM delivery d
    LEFT JOIN orders o ON d.order_id = o.order_id
    LEFT JOIN users u ON d.delivery_boy_id = u.user_id
    LEFT JOIN (
        SELECT t1.order_id, t1.status, t1.last_update
        FROM delivery_tracking t1
        INNER JOIN (
            SELECT order_id, MAX(last_update) AS last_update
            FROM delivery_tracking
            GROUP BY order_id
        ) t2 ON t1.order_id = t2.order_id AND t1.last_update = t2.last_update
    ) dt ON d.order_id = dt.order_id
    WHERE 1=1
";

$params = [];

// Filters
if (!empty($_GET['name'])) {
    $sql .= " AND LOWER(u.name) LIKE LOWER(:name)";
    $params[':name'] = "%" . $_GET['name'] . "%";
}
if (!empty($_GET['status'])) {
    $sql .= " AND LOWER(dt.status) = LOWER(:status)";
    $params[':status'] = $_GET['status'];
}
if (!empty($_GET['date'])) {
    $sql .= " AND DATE(dt.last_update) = :date";
    $params[':date'] = $_GET['date'];
}

$sql .= " ORDER BY d.delivery_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate table HTML
echo '<table class="table">';
echo '<thead>
        <tr>
            <th>Delivery ID</th>
            <th>Order ID</th>
            <th>Rider</th>
            <th>Status</th>
            <th>Delivery Date</th>
            <th>Track</th>
        </tr>
      </thead>';
echo '<tbody>';
if ($deliveries) {
    foreach ($deliveries as $d) {
        echo '<tr>';
        echo '<td>' . $d['delivery_id'] . '</td>';
        echo '<td>' . $d['order_id'] . '</td>';
        echo '<td>' . htmlspecialchars($d['rider_name']) . '</td>';
        echo '<td>' . ucfirst($d['status']) . '</td>';
        echo '<td>' . ($d['last_update'] ? date("Y-m-d H:i", strtotime($d['last_update'])) : 'No update') . '</td>';
        echo '<td><a href="trackorder.php?order_id=' . $d['order_id'] . '" class="btn-track">🚚 Track Order</a></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" class="text-center text-muted">No deliveries found</td></tr>';
}
echo '</tbody></table>';
