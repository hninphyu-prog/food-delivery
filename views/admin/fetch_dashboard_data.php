<?php
// fetch_dashboard_data.php
session_start();
require_once __DIR__ . "/../../config/db.php";

header('Content-Type: application/json');

try {
    $today = date('Y-m-d');
    
    // Function to fetch today's data (same as in dashboard.php)
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

    $data = getDashboardData($pdo, $today);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}