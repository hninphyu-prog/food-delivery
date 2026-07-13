<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

$sql = "
SELECT 
    o.restaurant_id,
    r.name AS restaurant_name,
    YEARWEEK(o.created_at,1) AS week_no,
    ROUND(SUM(o.total_amount)*0.75,2) AS settlement_amount
FROM orders o
JOIN restaurants r 
     ON o.restaurant_id = r.restaurant_id
LEFT JOIN restaurant_settlements rs
     ON rs.restaurant_id = o.restaurant_id 
    AND rs.week_no = YEARWEEK(o.created_at,1)
WHERE o.order_status = 'delivered'
GROUP BY o.restaurant_id, YEARWEEK(o.created_at,1)
HAVING COUNT(rs.restaurant_id) = 0
ORDER BY week_no DESC, r.name ASC;
";

$stmt = $pdo->query($sql);
$unpaid_settlements = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_payout = array_sum(array_column($unpaid_settlements, 'settlement_amount'));

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=unpaid_settlements_" . date('Ymd') . ".csv");

$output = fopen("php://output", "w");
fputcsv($output, ["Restaurant ID", "Restaurant Name", "Week No", "Settlement Amount"]);

foreach ($unpaid_settlements as $row) {
    fputcsv($output, [
        $row['restaurant_id'],
        $row['restaurant_name'],
        $row['week_no'],
        $row['settlement_amount']
    ]);
}

fputcsv($output, ["", "", "Total", $total_payout]);
fclose($output);
exit;
