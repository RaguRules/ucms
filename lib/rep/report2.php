<?php
ob_start();  // Start output buffering to avoid accidental output

require_once('../../vendor/autoload.php'); // Composer autoload for TCPDF
include_once('../db.php');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare query with MySQLi object-oriented
$stmt = $conn->prepare("SELECT lawyer_id, first_name, last_name, mobile, email, address, nic_number, enrolment_number, joined_date, is_active, station, image_path, added_by, staff_id FROM lawyer WHERE is_active = 1 ORDER BY first_name ASC");
$stmt->execute();
$result = $stmt->get_result();

// Initialize TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('UCMS');
$pdf->SetTitle('Registered Lawyers Report');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// Set background image (Letterhead)
$img_file = 'LH.jpg';  // Path to your letterhead image

// Get the image dimensions
list($img_width, $img_height) = getimagesize($img_file);

// Calculate the aspect ratio
$aspect_ratio = $img_width / $img_height;

// Set the width and height to fit the A4 page size (210mm x 297mm)
$page_width = 210;
$page_height = 297;

// Scale the image while maintaining aspect ratio
if ($img_width > $img_height) {
    // Landscape image
    $new_width = $page_width;
    $new_height = $page_width / $aspect_ratio;
    if ($new_height > $page_height) {
        $new_height = $page_height;
        $new_width = $page_height * $aspect_ratio;
    }
} else {
    // Portrait image
    $new_height = $page_height;
    $new_width = $page_height * $aspect_ratio;
    if ($new_width > $page_width) {
        $new_width = $page_width;
        $new_height = $page_width / $aspect_ratio;
    }
}

// Set the image size while maintaining the aspect ratio
$pdf->Image($img_file, 0, 0, $new_width, $new_height, '', '', '', false, 300, '', false, false, 0);

// Add the current date to the letterhead (we overlay the date)
$pdf->SetY(25);  // Move the date further down
$pdf->SetFont('helvetica', 'R', 12);
$pdf->Cell(0, 15,  date('Y-m-d'), 0, 1, 'R');  // Right-aligned current date

// Move below header content
$pdf->SetY(65);  // Adjust this to control vertical position of table
$pdf->SetFont('helvetica', 'BU', 14);
$pdf->Cell(0, 10, 'List of Active Registered Lawyers', 0, 1, 'C');
$pdf->Ln(5);  // Space after header
$pdf->SetFont('helvetica', '', 9);  // Reduce font size for compact design

// Begin table with reduced padding and tighter layout
$html = '
<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; width: 100%; font-size: 9px;">
  <tr style="background-color:#f2f2f2;">
    <th style="width: 5%; text-align: center;"><b>No</b></th>
    <th style="width: 20%;"><b>Full Name</b></th>
    <th style="width: 15%;"><b>Mobile</b></th>
    <th style="width: 15%;"><b>Email</b></th>
    <th style="width: 15%;"><b>Enrolment No</b></th>
    <th style="width: 10%;"><b>Station</b></th>
    <th style="width: 10%;"><b>NIC</b></th>
    <th style="width: 10%;"><b>Joined Date</b></th>
  </tr>';

// Initialize row counter for numbering
$counter = 1;

// Loop through results and display lawyer details
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
        $mobile = htmlspecialchars($row['mobile']);
        $email = htmlspecialchars($row['email']);
        $enrolmentNo = htmlspecialchars($row['enrolment_number']);
        $station = htmlspecialchars($row['station']);
        $nic = htmlspecialchars($row['nic_number']);
        $joinedDate = htmlspecialchars($row['joined_date']);
        
        // Format the joined date
        $joinedDateFormatted = date('Y-m-d', strtotime($joinedDate));

        // Add the current row with numbering
        $html .= "
        <tr>
            <td style='text-align: center;'>$counter</td>
            <td>$fullName</td>
            <td>$mobile</td>
            <td>$email</td>
            <td>$enrolmentNo</td>
            <td>$station</td>
            <td>$nic</td>
            <td>$joinedDateFormatted</td>
        </tr>";

        // Increment the counter for the next row
        $counter++;
    }
} else {
    $html .= '<tr><td colspan="8" style="text-align: center;">No lawyer records found.</td></tr>';
}

$html .= '</table>';

// Output the table in PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output final PDF
ob_end_clean();  // Clean (discard) any output before sending the PDF
$pdf->Output('lawyers_report.pdf', 'I');
?>
