<?php
// require_once('tcpdf/tcpdf.php');
require_once('../../vendor/autoload.php'); // Composer autoloader

// create new PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document info
$pdf->SetCreator('UCMS');
$pdf->SetAuthor('District Court Kilinochchi');
$pdf->SetTitle('Case Management Report');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Set background image (Letterhead)
$img_file = 'LH.jpg';
$pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

// Set font
$pdf->SetFont('helvetica', '', 11);

// Move cursor to suitable position (below letterhead top content)
$pdf->SetY(60);

// Example Report Data
$html = '
<h2 style="text-align:center;">Monthly Case Management Report - June 2025</h2>
<br>
<table cellpadding="5" border="1">
  <tr><th>Case ID</th><th>Judge</th><th>Status</th><th>Next Date</th></tr>
  <tr><td>DC/KN/0012/2023</td><td>Hon. M. Suresh</td><td>Pending</td><td>2025-07-15</td></tr>
  <tr><td>MC/KN/0089/2024</td><td>Hon. T. Rajan</td><td>Concluded</td><td>-</td></tr>
</table>
<br><br>
<p><b>Total Pending Cases:</b> 125</p>
<p><b>Total Concluded Cases:</b> 87</p>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Output
$pdf->Output('court_report.pdf', 'I');
?>
