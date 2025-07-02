<?php
ob_start();
require_once('../../vendor/autoload.php');
include_once('../db.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "
    SELECT o.*, c.case_name, c.plaintiff, c.defendant
    FROM orders o
    JOIN cases c ON o.case_id = c.case_id
    ORDER BY o.given_on DESC
";
$result = $conn->query($query);

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('UCMS');
$pdf->SetAuthor('UCMS System');
$pdf->SetTitle('Order Report');
$pdf->SetMargins(10, 55, 10);
$pdf->AddPage();

$letterhead = 'LH.jpg';
if (file_exists($letterhead)) {
    $pdf->Image($letterhead, 10, 5, 190);
}

$pdf->Ln(45);
$pdf->SetFont('dejavusans', 'B', 14);
$pdf->Cell(0, 10, 'Order Report', 0, 1, 'C');
$pdf->SetFont('dejavusans', '', 10);
$pdf->Ln(3);

$html = '<table border="1" cellpadding="4">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="18%">Case Name</th>
            <th width="22%">Plaintiff</th>
            <th width="22%">Defendant</th>
            <th width="13%">Calculated?</th>
            <th width="25%">Order Date</th>
        </tr>
    </thead>
    <tbody>';

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>' .
        '<td>' . htmlspecialchars($row['case_name']) . '</td>' .
        '<td>' . htmlspecialchars($row['plaintiff']) . '</td>' .
        '<td>' . htmlspecialchars($row['defendant']) . '</td>' .
        '<td>' . ($row['is_calculated'] ? 'Yes' : 'No') . '</td>' .
        '<td>' . htmlspecialchars($row['given_on']) . '</td>' .
    '</tr>';
}
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
ob_end_clean();
$pdf->Output('order_report.pdf', 'I');
exit;
