<?php
// export_payouts_csv.php
require_once __DIR__ . "/../../config/db.php"; // Adjust path as needed

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="weekly_rider_payouts.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Rider ID', 'Rider Name', 'Period Start', 'Period End', 'Total Paid (MMK)']);

$stmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.name AS rider_name,
        COALESCE(SUM(rp.amount), 0) AS total_paid,
        rp.period_start,
        rp.period_end
    FROM rider_payouts rp
    JOIN users u ON rp.rider_id = u.user_id
    WHERE u.role = 'delivery'
    GROUP BY u.user_id, rp.period_start, rp.period_end
    ORDER BY rp.period_start DESC
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    fputcsv($output, [
        $row['user_id'],
        $row['rider_name'],
        $row['period_start'],
        $row['period_end'],
        number_format($row['total_paid'], 0, '.', ',')
    ]);
}

fclose($output);
exit;