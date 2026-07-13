<?php
session_start();
include "includes/header.php";
require_once '../../config/db.php';

// ===== Fetch Deliveries with Filters =====
$sql = "
    SELECT d.*, o.order_id, u.name AS rider_name, dt.status, dt.last_update
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

// Filter by Rider Name OR Order ID
if (!empty($_GET['name'])) {
    $sql .= " AND (u.name LIKE :name OR d.order_id LIKE :name)";
    $params[':name'] = "%" . $_GET['name'] . "%";
}

// Filter by Status
if (!empty($_GET['status'])) {
    $sql .= " AND dt.status = :status";
    $params[':status'] = $_GET['status'];
}

// Filter by Date
if (!empty($_GET['date'])) {
    $sql .= " AND DATE(dt.last_update) = :date";
    $params[':date'] = $_GET['date'];
}

$sql .= " ORDER BY d.delivery_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deliveries Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background: #fff8f0; padding: 20px; }
        h1 { color: #d35400; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; }
        th { background: #e67e22; color: #fff; font-size: 15px; }
        tr:nth-child(even) { background: #fff3e0; }
        tr:hover { background: #ffe0b2; transition: 0.2s; }
        .btn-track { background: #e67e22; color:#fff; border:none; border-radius:6px; padding:6px 12px; font-size:13px; text-decoration:none; }
        .btn-track:hover { background: #d35400; text-decoration:none; }
    </style>
</head>
<body>
    <div class="main-content">
        <h1>Deliveries Management</h1>

        <!-- Search / Filter Form -->
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" id="search-name" class="form-control" placeholder="Search by Rider Name Or Order Id...">
            </div>
            <div class="col-md-3">
                <input type="date" id="search-date" class="form-control">
            </div>
            <div class="col-md-3">
                <select id="search-status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="on the way">On the Way</option>
                    <option value="delivered">Delivered</option>
                </select>
            </div>
        </div>

        <!-- Deliveries Table -->
        <table class="table" id="deliveries-table">
            <thead>
                <tr>
                    <th>Delivery ID</th>
                    <th>Order ID</th>
                    <th>Rider</th>
                    <th>Status</th>
                    <th>Delivery Date</th>
                    <th>Track</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $d): ?>
                <tr>
                    <td><?= $d['delivery_id'] ?></td>
                    <td><?= $d['order_id'] ?></td>
                    <td><?= htmlspecialchars($d['rider_name']) ?></td>
                    <td><?= $d['status'] ?? 'N/A' ?></td>
                    <td><?= isset($d['last_update']) ? date("Y-m-d H:i", strtotime($d['last_update'])) : 'No update' ?></td>
                    <td><a href="trackorder.php?order_id=<?= $d['order_id'] ?>" class="btn-track">🚚 Track Order</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        const nameInput = document.getElementById('search-name');
        const dateInput = document.getElementById('search-date');
        const statusSelect = document.getElementById('search-status');
        const tableBody = document.querySelector('#deliveries-table tbody');

        function fetchDeliveries() {
            const params = new URLSearchParams();
            if(nameInput.value) params.append('name', nameInput.value);
            if(dateInput.value) params.append('date', dateInput.value);
            if(statusSelect.value) params.append('status', statusSelect.value);

            fetch('deliveries.php?' + params.toString())
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTbody = doc.querySelector('#deliveries-table tbody');
                    tableBody.innerHTML = newTbody.innerHTML;
                });
        }

        nameInput.addEventListener('input', fetchDeliveries);
        dateInput.addEventListener('change', fetchDeliveries);
        statusSelect.addEventListener('change', fetchDeliveries);
    </script>
</body>
</html>
