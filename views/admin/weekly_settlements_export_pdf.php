<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../fpdf186/fpdf.php";

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

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Unpaid Weekly Settlements',0,1,'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial','B',12);
$pdf->Cell(30,10,'Restaurant ID',1);
$pdf->Cell(70,10,'Restaurant Name',1);
$pdf->Cell(30,10,'Week No',1);
$pdf->Cell(40,10,'Amount ($)',1);
$pdf->Ln();

// Table rows
$pdf->SetFont('Arial','',12);
foreach ($unpaid_settlements as $row) {
    $pdf->Cell(30,10,$row['restaurant_id'],1);
    $pdf->Cell(70,10,substr($row['restaurant_name'],0,30),1);
    $pdf->Cell(30,10,$row['week_no'],1);
    $pdf->Cell(40,10,number_format($row['settlement_amount'],2),1);
    $pdf->Ln();
}

// Total payout
$pdf->SetFont('Arial','B',12);
$pdf->Cell(130,10,'Total',1);
$pdf->Cell(40,10,number_format($total_payout,2),1);
$pdf->Ln();

$pdf->Output('D','unpaid_settlements_'.date('Ymd').'.pdf');
exit;
