<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

try {
    $restaurant_id = $_SESSION['restaurant_id'] ?? null;
    if (!$restaurant_id) {
        throw new Exception("No restaurant selected.");
    }
    
    // This Week's Performance
    $stmt_this_week = $pdo->prepare("
        SELECT 
            COUNT(*) as this_week_orders,
            COALESCE(SUM(o.subtotal), 0) as this_week_sales,
            COALESCE(SUM(o.subtotal * 0.75), 0) as this_week_earnings,
            COALESCE(SUM(o.subtotal * 0.25), 0) as this_week_commission
        FROM orders o 
        WHERE o.restaurant_id = ? 
        AND o.payment_status = 'paid'
        AND YEARWEEK(o.created_at, 1) = YEARWEEK(NOW(), 1)
    ");
    $stmt_this_week->execute([$restaurant_id]);
    $this_week_stats = $stmt_this_week->fetch(PDO::FETCH_ASSOC);

    // Last Week's Performance (for comparison)
    $stmt_last_week = $pdo->prepare("
        SELECT 
            COALESCE(SUM(o.subtotal * 0.75), 0) as last_week_earnings
        FROM orders o 
        WHERE o.restaurant_id = ? 
        AND o.payment_status = 'paid'
        AND YEARWEEK(o.created_at, 1) = YEARWEEK(NOW(), 1) - 1
    ");
    $stmt_last_week->execute([$restaurant_id]);
    $last_week_earnings = $stmt_last_week->fetchColumn();

    // Monthly Performance
    $stmt_month = $pdo->prepare("
        SELECT 
            COALESCE(SUM(o.subtotal * 0.75), 0) as month_earnings,
            COUNT(*) as month_orders
        FROM orders o 
        WHERE o.restaurant_id = ? 
        AND o.payment_status = 'paid'
        AND YEAR(o.created_at) = YEAR(NOW())
        AND MONTH(o.created_at) = MONTH(NOW())
    ");
    $stmt_month->execute([$restaurant_id]);
    $month_stats = $stmt_month->fetch(PDO::FETCH_ASSOC);

    // Get weekly earnings for chart
    $stmt = $pdo->prepare("
        SELECT 
            YEARWEEK(o.created_at, 1) as week_number,
            SUM(o.subtotal) as weekly_sales,
            SUM(o.subtotal * 0.75) as weekly_earnings,
            SUM(o.subtotal * 0.25) as weekly_commission,
            COUNT(*) as order_count
        FROM orders o 
        WHERE o.restaurant_id = ? 
            AND o.payment_status = 'paid'
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
        GROUP BY YEARWEEK(o.created_at, 1)
        ORDER BY week_number DESC
        LIMIT 8
    ");
    $stmt->execute([$restaurant_id]);
    $weekly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent settlements
    $stmt = $pdo->prepare("
        SELECT rs.*, u.name as released_by_name
        FROM restaurant_settlements rs 
        LEFT JOIN users u ON rs.released_by = u.user_id
        WHERE rs.restaurant_id = ?
        ORDER BY rs.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$restaurant_id]);
    $settlements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pending settlement (ready for payout)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(o.subtotal * 0.75), 0) as pending_settlement
        FROM orders o
        WHERE o.restaurant_id = ? 
            AND o.order_status = 'delivered'
            AND o.payment_status = 'paid'
            AND YEARWEEK(o.created_at, 1) < YEARWEEK(NOW(), 1)
            AND NOT EXISTS (
                SELECT 1 FROM restaurant_settlements rs 
                WHERE rs.restaurant_id = o.restaurant_id 
                AND rs.week_no = YEARWEEK(o.created_at, 1)
            )
    ");
    $stmt->execute([$restaurant_id]);
    $pending_settlement = $stmt->fetchColumn();

    // Calculate growth percentage
    $growth_percentage = 0;
    if ($last_week_earnings > 0 && $this_week_stats['this_week_earnings'] > 0) {
        $growth_percentage = (($this_week_stats['this_week_earnings'] - $last_week_earnings) / $last_week_earnings) * 100;
    }

    // Weekly settlement schedule
    $next_settlement_date = date('Y-m-d', strtotime('next monday'));
    $settlement_date_text = date('M j', strtotime($next_settlement_date));

} catch (PDOException $e) {
    error_log("Database error in financial.php: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
} catch (Exception $e) {
    die($e->getMessage());
}
?>
<!-- Add this section to your restaurant financial.php -->


<div class="container-fluid py-4 bg-light">
    <!-- Financial Overview Cards -->
    <div class="row mb-4">
        <!-- Card 1: This Week's Earnings -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1"><?= number_format($this_week_stats['this_week_earnings']) ?> MMK</h4>
                            <p class="card-text mb-0 opacity-8">This Week's Earnings</p>
                            <small class="opacity-7">
                                <?php if ($growth_percentage > 0): ?>
                                    ↗️ <?= number_format($growth_percentage, 1) ?>% from last week
                                <?php elseif ($growth_percentage < 0): ?>
                                    ↘️ <?= number_format(abs($growth_percentage), 1) ?>% from last week
                                <?php else: ?>
                                    → No change
                                <?php endif; ?>
                            </small>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 2: Pending Settlement -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1"><?= number_format($pending_settlement) ?> MMK</h4>
                            <p class="card-text mb-0 opacity-8">Pending Settlement</p>
                            <small class="opacity-7">Ready for payout</small>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 3: Monthly Performance -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1"><?= number_format($month_stats['month_earnings']) ?> MMK</h4>
                            <p class="card-text mb-0 opacity-8">Month to Date</p>
                            <small class="opacity-7"><?= $month_stats['month_orders'] ?> orders this month</small>
                        </div>
                        <div>
                            <i class="fas fa-calendar-alt fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 4: This Week's Commission -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1"><?= number_format($this_week_stats['this_week_commission']) ?> MMK</h4>
                            <p class="card-text mb-0 opacity-8">This Week's Commission</p>
                            <small class="opacity-7">Platform fees</small>
                        </div>
                        <div>
                            <i class="fas fa-percentage fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="alert-heading mb-1">📊 Current Performance</h6>
                        <p class="mb-0">
                            <strong>This Week:</strong> <?= number_format($this_week_stats['this_week_orders']) ?> orders, 
                            <?= number_format($this_week_stats['this_week_sales']) ?> MMK sales
                            | <strong>Month to Date:</strong> <?= number_format($month_stats['month_orders']) ?> orders
                            | <strong>Next Settlement:</strong> <?= date('M j', strtotime($next_settlement_date)) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">
                            Week <?= date('W') ?> (<?= date('M j') ?> - <?= date('M j', strtotime('sunday')) ?>)<br>
                            <?= date('F Y') ?> Performance
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Summary - FIXED HEIGHT -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Weekly Earnings (Last 8 Weeks)</h5>
                </div>
                <div class="card-body p-3">
                    <div style="height: 400px; position: relative;">
                        <canvas id="earningsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Performance Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="ps-0"><strong>This Week:</strong></td>
                            <td class="text-end"><?= number_format($this_week_stats['this_week_orders']) ?> orders</td>
                        </tr>
                        <tr>
                            <td class="ps-0">Sales:</td>
                            <td class="text-end"><strong><?= number_format($this_week_stats['this_week_sales']) ?> MMK</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-0">Your Earnings (75%):</td>
                            <td class="text-end text-success"><strong><?= number_format($this_week_stats['this_week_earnings']) ?> MMK</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-0">Platform Commission (25%):</td>
                            <td class="text-end text-warning"><strong><?= number_format($this_week_stats['this_week_commission']) ?> MMK</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-0">Weekly Growth:</td>
                            <td class="text-end <?= $growth_percentage > 0 ? 'text-success' : ($growth_percentage < 0 ? 'text-danger' : 'text-muted') ?>">
                                <strong>
                                    <?= $growth_percentage > 0 ? '↗️ ' : ($growth_percentage < 0 ? '↘️ ' : '→ ') ?>
                                    <?= number_format(abs($growth_percentage), 1) ?>%
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-0"><strong>Month to Date:</strong></td>
                            <td class="text-end"><?= number_format($month_stats['month_orders']) ?> orders</td>
                        </tr>
                        <tr>
                            <td class="ps-0">Monthly Earnings:</td>
                            <td class="text-end text-info"><strong><?= number_format($month_stats['month_earnings']) ?> MMK</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-0">Pending Settlement:</td>
                            <td class="text-end text-success"><strong><?= number_format($pending_settlement) ?> MMK</strong></td>
                        </tr>
                    </table>
                    
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Performance Metrics:</strong><br>
                            • <strong class="text-primary">This Week</strong>: Current week performance with growth indicator<br>
                            • <strong class="text-success">Pending Settlement</strong>: Ready for next payout<br>
                            • <strong class="text-info">Month to Date</strong>: <?= date('F') ?> performance<br>
                            • <strong class="text-warning">Commission</strong>: This week's platform fees
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement History -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Settlement History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($settlements)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No settlement records found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Week</th>
                                        <th>Amount</th>
                                        <th>Notes</th>
                                        <th>Released By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($settlements as $settlement): ?>
                                        <tr>
                                            <td><strong>Week <?= htmlspecialchars($settlement['week_no']) ?></strong></td>
                                            <td class="text-success fw-bold"><?= number_format($settlement['amount']) ?> MMK</td>
                                            <td><?= htmlspecialchars($settlement['notes']) ?></td>
                                            <td><?= htmlspecialchars($settlement['released_by_name'] ?? 'System') ?></td>
                                            <td><?= date('M j, Y', strtotime($settlement['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('earningsChart').getContext('2d');
    
    // Prepare chart data
    const weeklyLabels = [<?= 
        implode(',', array_map(function($week) { 
            $weekNum = substr($week['week_number'], 4);
            return "'Week $weekNum'"; 
        }, array_reverse($weekly_data))) 
    ?>];
    
    const weeklyEarnings = [<?= 
        implode(',', array_map(function($week) { 
            return $week['weekly_earnings']; 
        }, array_reverse($weekly_data))) 
    ?>];

    const earningsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: weeklyLabels,
            datasets: [{
                label: 'Weekly Earnings (75%)',
                data: weeklyEarnings,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Earnings: ' + context.parsed.y.toLocaleString() + ' MMK';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' MMK';
                        }
                    },
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>