<?php
// users.php
session_start();
include "includes/header.php";
require_once '../../config/db.php';

// ===== Fetch Top Customers by Number of Orders =====
$stmt = $pdo->query("
    SELECT u.user_id, u.name, u.email, u.phone, u.role, u.address, u.is_verified, u.created_at,
           COUNT(o.order_id) AS total_orders
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id
    WHERE u.role = 'customer'
    GROUP BY u.user_id
    ORDER BY total_orders DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== Count Total Customers =====
$totalStmt = $pdo->query("SELECT COUNT(*) AS total_customers FROM users WHERE role='customer'");
$totalCustomers = $totalStmt->fetch(PDO::FETCH_ASSOC)['total_customers'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background: #fff7f0; padding: 20px; }
        h1 { text-align: center; color: #e67e22; font-weight: bold; margin-bottom: 15px; }
        .total-box {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            background: #ffe5cc;
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
        }
        .table-container { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #e67e22; color: #fff; padding: 14px; text-align: center; }
        tbody td { padding: 12px; text-align: center; border-bottom: 1px solid #f3d4b6; }
        tbody tr:nth-child(even) { background: #fff3e5; }
        tbody tr:hover { background: #ffe5cc; transition: background 0.2s; }
        .status-active { background: #2ecc71; color: white; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: bold; }
        .status-inactive { background: #e74c3c; color: white; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
<div class="main-content">
    

    <!-- Search Bar (left) + Total Customers (right) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div style="width: 300px;">
            <input type="text" id="searchInput" class="form-control"
                   placeholder="Search customers...">
        </div>
        <div class="total-box">
           Total Customers: <?= $totalCustomers ?>
        </div>
    </div>
   
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Total Orders</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($users as $u): ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td><?= htmlspecialchars($u['address']) ?></td>
                        <td><strong><?= $u['total_orders'] ?></strong></td>
                        <td>
                            <?php if ($u['is_verified'] == 1): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date("Y-m-d", strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Search filter script -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>   