<?php
session_start();
require_once __DIR__ . "/../../config/db.php"; 
include "includes/header.php"; // sidebar + user info

// TODAY's date
$today = date('Y-m-d');

// Function to fetch today's data
function getDashboardData($pdo, $today) {
    // ===== TODAY'S General Stats =====
    $data['totalOrdersToday'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = '$today'")->fetchColumn();
    $data['completedDeliveriesToday'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status='delivered' AND DATE(created_at) = '$today'")->fetchColumn();
    $data['pendingDeliveriesToday'] = $pdo->query("SELECT COUNT(*) FROM delivery WHERE status='assigned' AND DATE(created_at) = '$today'")->fetchColumn();
    $data['unreadNotificationsToday'] = $pdo->query("SELECT COUNT(*) FROM request_notifications WHERE is_read='0' AND DATE(created_at) = '$today'")->fetchColumn();

    // ===== TODAY'S Financial Stats =====
    // Total sales today (from paid orders)
    $data['todaysSales'] = $pdo->query("SELECT IFNULL(SUM(total_amount),0) FROM orders WHERE payment_status='paid' AND DATE(created_at) = '$today'")->fetchColumn();
    
    // Admin commission (25% of total sales)
    $data['adminCommissionToday'] = $data['todaysSales'] * 0.25;
    
    // Restaurant share (75% of total sales)
    $data['restaurantShareToday'] = $data['todaysSales'] * 0.75;
    
    // Unpaid balance today
    $data['unpaidBalanceToday'] = $pdo->query("SELECT IFNULL(SUM(total_amount),0) FROM orders WHERE payment_status='unpaid' AND DATE(created_at) = '$today'")->fetchColumn();
    
    // Delivery fees today (for rider payouts)
    $data['deliveryFeesToday'] = $pdo->query("SELECT IFNULL(SUM(delivery_fee),0) FROM orders WHERE DATE(created_at) = '$today'")->fetchColumn();
    
    // Expenses today
    $data['expensesToday'] = $pdo->query("SELECT IFNULL(SUM(amount),0) FROM expenses WHERE DATE(created_at) = '$today'")->fetchColumn();
    
    // Profit today (Admin commission - Expenses)
    $data['profitToday'] = $data['adminCommissionToday'] - $data['expensesToday'];

    // ===== TODAY'S Orders by Status =====
    $statuses = ['pending','preparing','on_the_way','ready','delivered','canceled','accepted'];
    $data['orderStatusCountsToday'] = [];
    foreach ($statuses as $status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_status=? AND DATE(created_at) = '$today'");
        $stmt->execute([$status]);
        $data['orderStatusCountsToday'][$status] = $stmt->fetchColumn();
    }

    // ===== TODAY'S Top 5 Menu Items =====
    $stmt = $pdo->query("
        SELECT mi.name, SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE DATE(o.created_at) = '$today'
        GROUP BY oi.item_id
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $data['topItemsToday'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== TODAY'S Orders Over Time (by hour) =====
    $stmt = $pdo->query("
        SELECT HOUR(created_at) as hour, COUNT(*) as count
        FROM orders
        WHERE DATE(created_at) = '$today'
        GROUP BY HOUR(created_at)
        ORDER BY hour
    ");
    $ordersByHourToday = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fill missing hours with 0
    $ordersByHourFormatted = [];
    for ($i = 0; $i < 24; $i++) {
        $found = false;
        foreach ($ordersByHourToday as $row) {
            if ($row['hour'] == $i) {
                $ordersByHourFormatted[$i] = $row['count'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $ordersByHourFormatted[$i] = 0;
        }
    }
    $data['ordersByHourFormatted'] = $ordersByHourFormatted;

    // ===== TODAY'S Recent Orders =====
    $stmt = $pdo->query("
        SELECT o.order_id, u.name as user_name, r.name as restaurant_name, 
               o.total_amount, o.order_status, o.created_at
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        WHERE DATE(o.created_at) = '$today'
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $data['recentOrdersToday'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== TODAY'S Rider Performance =====
    $stmt = $pdo->query("
        SELECT u.name as rider_name, COUNT(d.delivery_id) as deliveries_today,
               SUM(CASE WHEN d.status='delivered' THEN 1 ELSE 0 END) as completed_today
        FROM delivery d
        JOIN users u ON d.delivery_boy_id = u.user_id
        WHERE DATE(d.created_at) = '$today'
        GROUP BY d.delivery_boy_id
        ORDER BY deliveries_today DESC
        LIMIT 5
    ");
    $data['topRidersToday'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== TODAY'S Restaurant Performance =====
    $stmt = $pdo->query("
        SELECT r.name as restaurant_name, COUNT(o.order_id) as orders_today,
               SUM(o.total_amount) as revenue_today,
               SUM(o.total_amount * 0.75) as restaurant_earnings_today
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        WHERE DATE(o.created_at) = '$today'
        GROUP BY o.restaurant_id
        ORDER BY orders_today DESC
        LIMIT 5
    ");
    $data['topRestaurantsToday'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}

// Get initial data
$data = getDashboardData($pdo, $today);

$userName = isset($user['name']) && $user['name'] !== '' ? htmlspecialchars($user['name']) : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodApp Admin Dashboard - Today</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
        
        .main-content { margin-left:250px; margin-top:20px; padding:20px; }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        
        .dashboard-header h1 {
            margin: 0;
            color: #1e1e2d;
        }
        
        .today-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .today-date {
            font-size: 18px;
            color: #4e73df;
            font-weight: bold;
            background: #f8f9fc;
            padding: 10px 20px;
            border-radius: 20px;
            border: 1px solid #4e73df;
        }
        
        .refresh-status {
            font-size: 14px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .refresh-status .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #1cc88a;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }
        
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h3 { 
            margin: 0; 
            font-size: 14px; 
            color: #555;
            margin-bottom: 10px;
        }
        
        .card .value { 
            font-size: 24px; 
            margin: 0; 
            font-weight: bold; 
            color: #1e1e2d;
        }
        
        .card .subtitle {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 12px; 
            color: #fff; 
            font-weight: bold; 
        }
        
        .badge.pending { background: #f6c23e; }
        .badge.preparing { background: #36b9cc; }
        .badge.on_the_way { background: #4e73df; }
        .badge.ready { background: #1cc88a; }
        .badge.delivered { background: #20c997; }
        .badge.canceled { background: #e74a3b; }
        .badge.accepted { background: #6f42c1; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #fff; 
            margin-top: 20px;
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.1); 
        }
        
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #e3e6f0;
        }
        
        th { 
            background:rgb(255,102,0);
            color: #fff; 
            font-weight: 600;
        }
        
        tr:hover { 
            background: #f8f9fc; 
        }
        
        .section-title {
            color: #1e1e2d;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid rgb(255,102,0);
        }
        
        .charts-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-box {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        
        .top-performers {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .performer-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        
        .performer-card h4 {
            margin: 0 0 15px 0;
            color: rgb(255,102,0);
            font-size: 16px;
        }
        
        .performer-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .performer-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

<div class="main-content">
    <!-- Header with Today's Date -->
    <div class="dashboard-header">
        <h1>Today's Dashboard Overview</h1>
        <div class="today-info">
            <div class="today-date">
                <i class="fas fa-calendar-alt"></i> <?= date('F j, Y') ?>
            </div>
        
        </div>
    </div>

    <!-- TODAY'S General Stats -->
    <h2 class="section-title"> Today's Summary</h2>
    <div class="cards-container" id="summaryCards">
        <div class="card">
            <h3>Orders Today</h3>
            <p class="value" id="totalOrdersToday"><?= $data['totalOrdersToday'] ?></p>
            <p class="subtitle">Total orders placed today</p>
        </div>
        <div class="card">
            <h3>Completed Today</h3>
            <p class="value" id="completedDeliveriesToday"><?= $data['completedDeliveriesToday'] ?></p>
            <p class="subtitle">Deliveries completed</p>
        </div>
        <div class="card">
            <h3>Pending Today</h3>
            <p class="value" id="pendingDeliveriesToday"><?= $data['pendingDeliveriesToday'] ?></p>
            <p class="subtitle">Assignments pending</p>
        </div>
    </div>

    <!-- TODAY'S Financial Stats -->
    <h2 class="section-title"> Today's Financials</h2>
    <div class="cards-container" id="financialCards">
        <div class="card">
            <h3>Today's Sales</h3>
            <p class="value" id="todaysSales"><?= number_format($data['todaysSales'], 0) ?> MMK</p>
            <p class="subtitle">Total revenue from orders</p>
        </div>
        <div class="card">
            <h3>Admin Commission (25%)</h3>
            <p class="value" id="adminCommissionToday" style="color: #1cc88a;">
                <?= number_format($data['adminCommissionToday'], 0) ?> MMK
            </p>
            <p class="subtitle">25% of total sales</p>
        </div>
        <div class="card">
            <h3>Restaurant Share (75%)</h3>
            <p class="value" id="restaurantShareToday"><?= number_format($data['restaurantShareToday'], 0) ?> MMK</p>
            <p class="subtitle">To be paid to restaurants</p>
        </div>
        <div class="card">
            <h3>Delivery Fees</h3>
            <p class="value" id="deliveryFeesToday"><?= number_format($data['deliveryFeesToday'], 0) ?> MMK</p>
            <p class="subtitle">For rider weekly payouts</p>
        </div>
        <div class="card">
            <h3>Today's Net Profit</h3>
            <p class="value" id="profitToday" style="color: <?= $data['profitToday'] >= 0 ? '#1cc88a' : '#e74a3b' ?>">
                <?= number_format($data['profitToday'], 0) ?> MMK
            </p>
            <p class="subtitle">Commission - Expenses</p>
        </div>
    </div>

    <!-- TODAY'S Charts -->
    <h2 class="section-title"> Today's Analytics</h2>
    <div class="charts-container">
        <div class="chart-box">
            <h4>Orders by Hour (Today)</h4>
            <canvas id="ordersByHourChart"></canvas>
        </div>
        <div class="chart-box">
            <h4>Orders by Status (Today)</h4>
            <canvas id="ordersStatusChart"></canvas>
        </div>
        <div class="chart-box">
            <h4>Top Items Today</h4>
            <canvas id="topItemsChart"></canvas>
        </div>
    </div>

    <!-- TODAY'S Top Performers -->
    <h2 class="section-title"> Today's Top Performers</h2>
    <div class="top-performers" id="topPerformersSection">
        <div class="performer-card" id="topRidersCard">
            <h4> Top Riders Today</h4>
            <?php if (!empty($data['topRidersToday'])): ?>
                <?php foreach ($data['topRidersToday'] as $rider): ?>
                    <div class="performer-item">
                        <span><?= htmlspecialchars($rider['rider_name']) ?></span>
                        <span>
                            <span class="badge delivered"><?= $rider['completed_today'] ?> completed</span>
                            <span class="badge on_the_way"><?= $rider['deliveries_today'] ?> total</span>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6c757d; text-align: center;">No rider activity today</p>
            <?php endif; ?>
        </div>
        
        <div class="performer-card" id="topRestaurantsCard">
            <h4> Top Restaurants Today</h4>
            <?php if (!empty($data['topRestaurantsToday'])): ?>
                <?php foreach ($data['topRestaurantsToday'] as $restaurant): ?>
                    <div class="performer-item">
                        <span><?= htmlspecialchars($restaurant['restaurant_name']) ?></span>
                        <span>
                            <span class="badge delivered"><?= $restaurant['orders_today'] ?> orders</span>
                            <span class="badge" style="background: #4e73df;"><?= number_format($restaurant['restaurant_earnings_today'], 0) ?> MMK</span>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6c757d; text-align: center;">No restaurant orders today</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- TODAY'S Orders by Status -->
    <h2 class="section-title"> Today's Orders by Status</h2>
    <div class="cards-container" id="statusCards">
        <?php 
        $statuses = ['pending','preparing','on_the_way','ready','delivered','canceled','accepted'];
        foreach ($statuses as $status): 
            if ($data['orderStatusCountsToday'][$status] > 0): 
        ?>
                <div class="card">
                    <h3><?= ucfirst(str_replace('_', ' ', $status)) ?></h3>
                    <p class="value" id="status_<?= $status ?>"><?= $data['orderStatusCountsToday'][$status] ?></p>
                    <p class="subtitle">Orders today</p>
                </div>
        <?php 
            endif; 
        endforeach; 
        ?>
    </div>

    <!-- TODAY'S Recent Orders -->
    <h2 class="section-title"> Recent Orders Today</h2>
    <div id="recentOrdersSection">
        <?php if (!empty($data['recentOrdersToday'])): ?>
            <table id="recentOrdersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersBody">
                    <?php foreach ($data['recentOrdersToday'] as $order): ?>
                        <tr>
                            <td>#<?= $order['order_id'] ?></td>
                            <td><?= htmlspecialchars($order['user_name']) ?></td>
                            <td><?= htmlspecialchars($order['restaurant_name']) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> MMK</td>
                            <td>
                                <span class="badge <?= $order['order_status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['order_status'])) ?>
                                </span>
                            </td>
                            <td><?= date("H:i", strtotime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;" id="noOrdersMessage">
                <p style="font-size: 18px; color: #6c757d;">No orders placed yet today</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Store initial data for charts
let ordersByHourData = <?= json_encode($data['ordersByHourFormatted']) ?>;
let ordersStatusData = <?= json_encode($data['orderStatusCountsToday']) ?>;
let topItemsData = <?= json_encode($data['topItemsToday']) ?>;

// Charts initialization
let ordersByHourChart = null;
let ordersStatusChart = null;
let topItemsChart = null;

function initializeCharts() {
    // Orders by Hour Today
    ordersByHourChart = new Chart(document.getElementById('ordersByHourChart'), {
        type: 'line',
        data: {
            labels: Object.keys(ordersByHourData),
            datasets: [{
                label: 'Orders',
                data: Object.values(ordersByHourData),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Number of Orders' }
                },
                x: {
                    title: { display: true, text: 'Hour of Day' }
                }
            }
        }
    });

    // Orders by Status Today (Doughnut Chart)
    const statusLabels = Object.keys(ordersStatusData).map(status => 
        status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
    );
    
    ordersStatusChart = new Chart(document.getElementById('ordersStatusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: Object.values(ordersStatusData),
                backgroundColor: [
                    '#f6c23e', '#36b9cc', '#4e73df', '#1cc88a', 
                    '#20c997', '#e74a3b', '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    // Top Items Today (Bar Chart)
    topItemsChart = new Chart(document.getElementById('topItemsChart'), {
        type: 'bar',
        data: {
            labels: topItemsData.map(item => item.name),
            datasets: [{
                label: 'Items Sold Today',
                data: topItemsData.map(item => item.total_sold),
                backgroundColor: [
                    '#4e73df', '#e74a3b', '#1cc88a', 
                    '#f6c23e', '#36b9cc'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Quantity Sold' }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// Update charts with new data
function updateCharts(newData) {
    // Update orders by hour chart
    ordersByHourChart.data.datasets[0].data = Object.values(newData.ordersByHourFormatted);
    ordersByHourChart.update();
    
    // Update orders status chart
    ordersStatusChart.data.datasets[0].data = Object.values(newData.orderStatusCountsToday);
    ordersStatusChart.update();
    
    // Update top items chart
    topItemsChart.data.labels = newData.topItemsToday.map(item => item.name);
    topItemsChart.data.datasets[0].data = newData.topItemsToday.map(item => item.total_sold);
    topItemsChart.update();
}

// Real-time update function
function updateDashboard() {
    fetch('fetch_dashboard_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dashboard = data.data;
                
                // Update summary cards
                document.getElementById('totalOrdersToday').textContent = dashboard.totalOrdersToday;
                document.getElementById('completedDeliveriesToday').textContent = dashboard.completedDeliveriesToday;
                document.getElementById('pendingDeliveriesToday').textContent = dashboard.pendingDeliveriesToday;
                document.getElementById('unreadNotificationsToday').textContent = dashboard.unreadNotificationsToday;
                
                // Update financial cards
                document.getElementById('todaysSales').textContent = dashboard.todaysSales.toLocaleString() + ' MMK';
                document.getElementById('adminCommissionToday').textContent = dashboard.adminCommissionToday.toLocaleString() + ' MMK';
                document.getElementById('restaurantShareToday').textContent = dashboard.restaurantShareToday.toLocaleString() + ' MMK';
                document.getElementById('deliveryFeesToday').textContent = dashboard.deliveryFeesToday.toLocaleString() + ' MMK';
                document.getElementById('expensesToday').textContent = dashboard.expensesToday.toLocaleString() + ' MMK';
                
                // Update profit with color
                const profitElem = document.getElementById('profitToday');
                profitElem.textContent = dashboard.profitToday.toLocaleString() + ' MMK';
                profitElem.style.color = dashboard.profitToday >= 0 ? '#1cc88a' : '#e74a3b';
                
                // Update status cards
                const statuses = ['pending','preparing','on_the_way','ready','delivered','canceled','accepted'];
                statuses.forEach(status => {
                    const elem = document.getElementById('status_' + status);
                    if (elem) {
                        elem.textContent = dashboard.orderStatusCountsToday[status];
                    }
                });
                
                // Update top performers
                updateTopPerformers(dashboard.topRidersToday, dashboard.topRestaurantsToday);
                
                // Update recent orders
                updateRecentOrders(dashboard.recentOrdersToday);
                
                // Update charts
                updateCharts(dashboard);
                
                // Update timestamp
                document.getElementById('lastUpdateTime').textContent = 'Updated: ' + new Date().toLocaleTimeString();
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            document.getElementById('lastUpdateTime').textContent = 'Update failed - ' + new Date().toLocaleTimeString();
        });
}

// Update top performers section
function updateTopPerformers(topRiders, topRestaurants) {
    const ridersCard = document.getElementById('topRidersCard');
    const restaurantsCard = document.getElementById('topRestaurantsCard');
    
    // Update top riders
    if (topRiders.length > 0) {
        let ridersHTML = '<h4> Top Riders Today</h4>';
        topRiders.forEach(rider => {
            ridersHTML += `
                <div class="performer-item">
                    <span>${rider.rider_name}</span>
                    <span>
                        <span class="badge delivered">${rider.completed_today} completed</span>
                        <span class="badge on_the_way">${rider.deliveries_today} total</span>
                    </span>
                </div>
            `;
        });
        ridersCard.innerHTML = ridersHTML;
    } else {
        ridersCard.innerHTML = '<h4> Top Riders Today</h4><p style="color: #6c757d; text-align: center;">No rider activity today</p>';
    }
    
    // Update top restaurants
    if (topRestaurants.length > 0) {
        let restaurantsHTML = '<h4> Top Restaurants Today</h4>';
        topRestaurants.forEach(restaurant => {
            restaurantsHTML += `
                <div class="performer-item">
                    <span>${restaurant.restaurant_name}</span>
                    <span>
                        <span class="badge delivered">${restaurant.orders_today} orders</span>
                        <span class="badge" style="background: #4e73df;">${restaurant.restaurant_earnings_today.toLocaleString()} MMK</span>
                    </span>
                </div>
            `;
        });
        restaurantsCard.innerHTML = restaurantsHTML;
    } else {
        restaurantsCard.innerHTML = '<h4>Top Restaurants Today</h4><p style="color: #6c757d; text-align: center;">No restaurant orders today</p>';
    }
}

// Update recent orders table
function updateRecentOrders(recentOrders) {
    const recentOrdersBody = document.getElementById('recentOrdersBody');
    const noOrdersMessage = document.getElementById('noOrdersMessage');
    
    if (recentOrders.length > 0) {
        // Remove no orders message if it exists
        if (noOrdersMessage) {
            noOrdersMessage.style.display = 'none';
        }
        
        // Create table if it doesn't exist
        if (!recentOrdersBody) {
            const tableHTML = `
                <table id="recentOrdersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Restaurant</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="recentOrdersBody">
                    </tbody>
                </table>
            `;
            document.getElementById('recentOrdersSection').innerHTML = tableHTML;
        }
        
        // Update table body
        const newBody = document.getElementById('recentOrdersBody');
        let ordersHTML = '';
        
        recentOrders.forEach(order => {
            const statusClass = order.order_status;
            const statusText = order.order_status.charAt(0).toUpperCase() + 
                             order.order_status.slice(1).replace('_', ' ');
            const time = new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            ordersHTML += `
                <tr>
                    <td>#${order.order_id}</td>
                    <td>${order.user_name}</td>
                    <td>${order.restaurant_name}</td>
                    <td>${parseFloat(order.total_amount).toLocaleString()} MMK</td>
                    <td>
                        <span class="badge ${statusClass}">${statusText}</span>
                    </td>
                    <td>${time}</td>
                </tr>
            `;
        });
        
        newBody.innerHTML = ordersHTML;
    } else {
        // Show no orders message
        if (noOrdersMessage) {
            noOrdersMessage.style.display = 'block';
        } else {
            const noOrdersHTML = `
                <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;" id="noOrdersMessage">
                    <p style="font-size: 18px; color: #6c757d;">No orders placed yet today</p>
                </div>
            `;
            document.getElementById('recentOrdersSection').innerHTML = noOrdersHTML;
        }
    }
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    
    // Start real-time updates (every 30 seconds)
    updateDashboard(); // Initial update
    setInterval(updateDashboard, 30000); // Update every 30 seconds
    
    // Update timestamp every minute
    setInterval(() => {
        const now = new Date();
        document.getElementById('lastUpdateTime').textContent = 
            'Live - Last update: ' + now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }, 60000);
});
</script>
</body>
</html>