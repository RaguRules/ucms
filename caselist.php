<?php

$upload_dir = "uploads/caselist/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_SESSION["LOGIN_USERTYPE"])) {
    $systemUsertype = $_SESSION["LOGIN_USERTYPE"];
    $systemUsername = $_SESSION["LOGIN_USERNAME"];

    $court = isset($_GET['list']) ? $_GET['list'] : 'mc';

    $staffId = $helper->getId($systemUsername, $systemUsertype);
    $staffDetail = $helper->getStaffData($staffId);
    $staffCourt = $staffDetail['court_id'];
    $staffRole = $staffDetail['role_id'];

    // echo "$staffRole $staffCourt <br>";

    // Access Control
    if($staffRole != 'R01'){
        if($staffCourt=='C01'){
            $staffCourt = 'mc' ;
        } elseif($staffCourt=='C02') {
            $staffCourt = 'dc' ;
        } elseif($staffCourt=='C03') {
            $staffCourt = 'hc' ;
        }
        if($staffCourt != $court) {
            echo "<div class='alert alert-warning' role='alert'>You are only authorized to update your court's case list only.</div>";
            exit;
        }  
    }
} else {
    $systemUsername = "GUEST";
    $systemUsertype = "GUEST";
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['case_list']) && $_FILES['case_list']['error'] == 0) {
    $uploaded_file = $_FILES['case_list'];

    if ($uploaded_file['type'] !== 'application/pdf') {
        echo "<div class='alert alert-danger' role='alert'>Invalid file type. Only PDF files are allowed.</div>";
        exit;
    }

    $court = isset($_POST['court']) ? $_POST['court'] : '';

    $file_path = $upload_dir;

    switch ($court) {
        case 'mc':
            $file_path .= 'magistrates_court/';
            break;
        case 'dc':
            $file_path .= 'district_court/';
            break;
        case 'hc':
            $file_path .= 'high_court/';
            break;
        default:
            echo "Invalid court selected.";
            exit;
    }

    if (!is_dir($file_path)) {
        mkdir($file_path, 0777, true);
    }

    $file_name = basename($uploaded_file['name']);
    $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name); 
    $file_path .= 'case_list' . '.pdf';

    if (move_uploaded_file($uploaded_file['tmp_name'], $file_path)) {
        echo "<div class='alert alert-success' role='alert'>File uploaded successfully!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Failed to upload file.</div>";
    }
}

if (in_array($systemUsertype, ['R01', 'R02', 'R03', 'R04', 'R05'])) {
    
    $court = isset($_GET['list']) ? $_GET['list'] : 'mc';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload Case List</title>
    </head>
    <body>
        <div class="container mt-5">
            <h2 class="text-center mb-4">Upload Today's Case List</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="case_list" class="form-label">Select Case List File (PDF)</label>
                    <input type="hidden" name="court" value="<?php echo Security::sanitize($court); ?>">
                    <input type="file" class="form-control" id="case_list" name="case_list" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </body>
    </html>
<?php
} else {
    $court = isset($_GET['list']) ? $_GET['list'] : 'mc';

    switch ($court) {
        case 'mc':
            $relative_path = 'uploads/caselist/magistrates_court/';
            break;
        case 'dc':
            $relative_path = 'uploads/caselist/district_court/';
            break;
        case 'hc':
            $relative_path = 'uploads/caselist/high_court/';
            break;
        default:
            echo "Invalid court selected.";
            exit;
    }

    $filename = 'case_list' . '.pdf';
    $file_path = $relative_path . $filename;

    if (file_exists($file_path)) {

        $baseUrl = 'http://localhost/';  // it will be replaced by me when move to online.
        $fullUrl = $baseUrl . $file_path;

        echo "<script>window.location.href = '$fullUrl';</script>";
        exit;

    } else {
        echo "<div class='alert alert-warning' role='alert'>Case list for today is not available.</div>";
    }
}
?>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>