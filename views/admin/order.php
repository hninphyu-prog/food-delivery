<?php
session_start();
include "includes/header.php"; 
require_once '../../config/db.php'; 

// Status list for dropdown
$statuses = ['pending','preparing','on_the_way','ready','delivered','canceled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders - Admin</title>
    <style>
        body { font-family: "Segoe UI", sans-serif; background: #f1f3f6; margin: 0; }
        .container { padding: 30px; }
        h1 { margin-bottom: 20px; font-size: 28px; color: #333; }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px 16px; text-align: left; }
        th { background: #e67e22; color: #fff; font-size: 14px; }
        tr:nth-child(even) { background: #f9f9f9; }
        tr:hover { background: #f1f5ff; }
        td { font-size: 14px; color: #333; }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            text-transform: capitalize;
            white-space: nowrap;
        }
        .badge.pending { background: #f6c23e; }
        .badge.preparing { background: #36b9cc; }
        .badge.on_the_way { background: #4e73df; }
        .badge.ready { background: #1cc88a; }
        .badge.delivered { background: #20c997; }
        .badge.canceled { background: #e74a3b; }

        .badge.paid { background: #1cc88a; }
        .badge.unpaid { background: #e74a3b; }

        .badge.kpay { background: #6f42c1; }
        .badge.wave { background: #fd7e14; }
        .badge.paypal { background: #17a2b8; }
        .badge.card { background: #ad17b8ff; }
        .badge.unassigned { background: #6c757d; }

        form { margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
        select, input[type="text"] {
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="main-content">
    <div class="container">
        <div class="card">
            <h1>All Orders</h1>

            <!-- Filters -->
            <form id="filterForm" onsubmit="return false;">
                <select name="status" id="status">
                    <option value="">-- Filter by Status --</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>"><?= ucfirst(str_replace("_"," ",$s)) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="search" id="search" placeholder="Search by user, restaurant, or order ID">
            </form>

            <!-- Orders Table -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Delivery Name</th>
                        <th>Order Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="ordersData">
                    <tr><td colspan="9">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>

<script>
function loadOrders() {
    const status = document.getElementById("status").value;
    const search = document.getElementById("search").value;

    fetch("fetch_order.php?status=" + encodeURIComponent(status) + "&search=" + encodeURIComponent(search))
        .then(res => res.text())
        .then(data => {
            document.getElementById("ordersData").innerHTML = data;
        });
}

// Load data on page load
window.onload = loadOrders;

// Filters
document.getElementById("status").addEventListener("change", loadOrders);
document.getElementById("search").addEventListener("keyup", function() {
    loadOrders();
});
</script>
</body>
</html>
