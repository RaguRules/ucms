<?php

if(isset($_SESSION["LOGIN_USERTYPE"])){
    $systemUsertype = $_SESSION["LOGIN_USERTYPE"];
	$systemUsername = $_SESSION["LOGIN_USERNAME"];
}else{
	$systemUsertype = "GUEST";
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$helper = new Helper($conn);
$security = new Security();



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['case_input'])) {
    $input = trim($_POST['case_input']);

    // Fetch case by case_id or case_name
    $stmt = $conn->prepare("SELECT * FROM cases WHERE case_id = ? OR case_name = ?");
    $stmt->bind_param("ss", $input, $input);
    $stmt->execute();
    $caseResult = $stmt->get_result();

    if ($case = $caseResult->fetch_assoc()) {
        // Check if warrant issued
        $isWarrant = $case['is_warrant'];

        // Get latest daily activity
        $stmt2 = $conn->prepare("SELECT activity_date, summary, next_date FROM dailycaseactivities WHERE case_name = ? ORDER BY activity_date DESC LIMIT 1");
        $stmt2->bind_param("s", $case['case_id']);
        $stmt2->execute();
        $activityResult = $stmt2->get_result();
        $latestActivity = $activityResult->fetch_assoc();

        // Return JSON response
        echo json_encode([
            'case_name' => $case['case_name'],
            'is_warrant' => $isWarrant,
            'activity' => $latestActivity
        ]);
    } else {
        echo json_encode(['error' => 'Case not found']);
    }
}


$policeId = null;

if ($systemUsertype === "R07") {
    // Only polices have a police_id in the police table
    $stmt = $conn->prepare("SELECT police_id FROM police WHERE email = ?");
    $stmt->bind_param("s", $systemUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $policeId = $row['police_id'];
    } else {
        // police email not found
        Security::logError("police email not found for: $systemUsername");
        echo "<script>alert('Unauthorized access.'); location.href='login.php';</script>";
        exit;
    }
}


// Get all cases related to this police
// $query = "SELECT * FROM cases WHERE plaintiff_police = ? OR defendant_police = ?";
// $stmt = $conn->prepare($query);
// $stmt->bind_param("ss", $policeId, $policeId);
// $stmt->execute();
// $cases = $stmt->get_result();




if (isset($_POST['case_id'])) {

    // Check for CSRF Tokens
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
	}

    $caseId = $_POST['case_id'];
    $policeId = $helper->getId($systemUsername, $systemUsertype);

    // Verify the police is authorized to access this case
    $query = "SELECT * FROM cases WHERE case_id = ? AND (plaintiff_police = ? OR defendant_police = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $caseId, $policeId, $policeId);
    $stmt->execute();
    $caseResult = $stmt->get_result();

    if ($case = $caseResult->fetch_assoc()) {

        // Case details
        $caseName = $case['case_name'];
        $nature = $case['nature'];
        $isWarrant = $case['is_warrant'];
        $registeredDate = $case['registered_date'];
        $courtId = $helper -> getCourtName($case['court_id']);
        
           // Get Plaintiff Name
        $plaintiffData = $helper->getPartyData($case['plaintiff']);
        $plaintiff = $plaintiffData ? $plaintiffData['first_name'] . ' ' . $plaintiffData['last_name'] : 'Unknown';

        // Get Defendant Name
        $defendantData = $helper->getPartyData($case['defendant']);
        $defendant = $defendantData ? $defendantData['first_name'] . ' ' . $defendantData['last_name'] : 'Unknown';


        // Get Plaintiff police Name
        $plaintiffpoliceData = $helper->getpoliceData($case['plaintiff_police']);
        $plaintiffpolice = $plaintiffpoliceData ? $plaintiffpoliceData['first_name'] . ' ' . $plaintiffpoliceData['last_name'] : 'Unknown';

        // Get Defendant police Name
        $defendantpoliceData = $helper->getpoliceData($case['defendant_police']);
        $defendantpolice = $defendantpoliceData ? $defendantpoliceData['first_name'] . ' ' . $defendantpoliceData['last_name'] : 'Unknown';


        // Fetch daily activities
        $query_activities = "SELECT * FROM dailycaseactivities WHERE case_name = ?";
        $stmt_activities = $conn->prepare($query_activities);
        $stmt_activities->bind_param("s", $caseId);
        $stmt_activities->execute();
        $activities_result = $stmt_activities->get_result();
        ?>

        <!-- Modal HTML -->
        <div class="modal fade" id="caseDetailModal" tabindex="-1" aria-labelledby="caseDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content" style="background-color: #ffffff; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <div class="modal-header">
                        <h5 class="modal-title" style="font-weight: bold; color: #007bff;">Case Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <p style="font-size: 22px; font-weight: bold; color: #007bff;"><?= Security::sanitize($caseName) ?></p>
                                <p><strong>Plaintiff Name:</strong> <?= Security::sanitize($plaintiff) ?></p>
                                <p><strong>Defendant Name:</strong> <?= Security::sanitize($defendant) ?></p>
                                <p><strong>Plaintiff police:</strong> <?= Security::sanitize($plaintiffpolice) ?></p>
                                <p><strong>Defendant police:</strong> <?= Security::sanitize($defendantpolice) ?></p>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <p><strong>Nature of the Case:</strong> <?= Security::sanitize($nature) ?></p>
                                <p><strong>Warrant:</strong> 
                                    <span style="font-weight: bold; padding: 5px 10px; border-radius: 5px; color: #fff; background-color: <?= $isWarrant ? '#dc3545' : '#28a745' ?>;">
                                        <?= $isWarrant ? 'Arrest Warrant Issued' : 'No Warrant' ?>
                                    </span>
                                </p>
                                <p><strong>Court Name:</strong> <?= Security::sanitize($courtId) ?></p>
                                <p><strong>Case Registered Date:</strong> <?= Security::sanitize($registeredDate) ?></p>
                            </div>
                        </div>

                        <!-- Daily Activities -->
                        <div class="mt-4">
                            <h4 style="font-weight: bold; color: #007bff;">Quick Journal</h4>
                            <?php if ($activities_result->num_rows > 0): ?>
                                <?php while ($activity = $activities_result->fetch_assoc()): ?>
                                    <div class="mb-3 p-3" style="background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px;">
                                        <h5 style="color: #007bff;"><?= Security::sanitize($activity['activity_date']) ?></h5>
                                        <p><strong>Summary:</strong> <?= Security::sanitize($activity['summary']) ?></p>
                                        <p><strong>Next Date:</strong> <?= Security::sanitize($activity['next_date']) ?></p>
                                        <p><strong>Next Steps:</strong> <?= Security::sanitize($activity['next_status']) ?></p>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No activities recorded for this case yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auto show modal -->
        <script>
            window.onload = function() {
                var modal = new bootstrap.Modal(document.getElementById('caseDetailModal'));
                modal.show();
            }
        </script>

        <?php
        $stmt_activities->close();
    } else {
        Security::logError("Unauthorized access attempt to case $caseId by $policeId on " . date("Y-m-d H:i:s"));
        echo "<script>alert('Access denied: You are not assigned to this case.');</script>";
    }

    $stmt->close();
}

	

if (isset($_POST["btn_add"])) {
    // Sanitize inputs
    $txtpoliceId = $helper->generateNextpoliceID();
    $txtFirstName = Security::sanitize($_POST["txt_first_name"]);
    $txtLastName = Security::sanitize($_POST["txt_last_name"]);
    $intMobile = Security::sanitize($_POST["int_mobile"]);
    $txtNicNumber = Security::sanitize($_POST["txt_nic_number"]);
    $dateDateOfBirth = Security::sanitize($_POST["date_date_of_birth"]);
    $txtEmail = Security::sanitize($_POST["txt_email"]);
    $txtAddress = Security::sanitize($_POST["txt_address"]);
    $selectStation = Security::sanitize($_POST["select_station"]);
    $dateJoinedDate = Security::sanitize($_POST["date_joined_date"]);
    $selectGender = Security::sanitize($_POST["select_gender"]);
    $txtBadgeNumber = Security::sanitize($_POST["txt_badge_number"]);
    $selectRoleName = "R07";
    $isActive = "1";
    $txtAddedBy = $_SESSION["LOGIN_USERTYPE"];
    $txtWrittenId = $helper->getId($_SESSION["LOGIN_USERNAME"], $_SESSION["LOGIN_USERTYPE"]);
    // $txtAddedBy = "R03";
    // $txtStaffId = "S0001";

    // echo "<script>alert('Added by: $txtAddedBy');</script>";
    // echo "<script>alert('Entered by: $txtWrittenId');</script>";


    // Check for CSRF Tokens
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
	}

    // Validate inputs
    $errors = [];

    if (!preg_match('/^[0-9]{10}$/', $intMobile)) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }

    if (!filter_var($txtEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[0-9]{9}[vVxX0-9]{1,3}$/', $txtNicNumber)) {
        $errors[] = "Invalid NIC number format.";
    }

    if (!$helper->validateDate($dateDateOfBirth)) {
        $errors[] = "Invalid date of birth.";
    }

    if (!$helper->validateDate($dateJoinedDate)) {
        $errors[] = "Invalid joined date.";
    }

    if (empty($txtFirstName) || empty($txtLastName) || empty($txtAddress)) {
        $errors[] = "Name and address fields cannot be empty.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
        exit;
    }

	// Before Insert staff, check for duplicates in staff, police, and police tables
	Security::checkDuplicate($conn, "nic_number", $txtNicNumber, "", "NIC Number already exists!", $txtpoliceId);
	Security::checkDuplicate($conn, "mobile", $intMobile, "", "Mobile number already exists!", $txtpoliceId);
	Security::checkDuplicate($conn, "email", $txtEmail, "", "Email already exists!", $txtpoliceId);

    // Image upload
    $uploadResult = $security->uploadImage('img_profile_photo');

    if (!$uploadResult['success']) {
        die("Image upload failed: " . $uploadResult['error']);
    }

    $txtImagePath = 'uploads/' . $uploadResult['filename'];

    // Begin transaction
	$conn->begin_transaction();

    try {
        // Insert into police table
        $stmtpolice = $conn->prepare("INSERT INTO police (police_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, badge_number, joined_date, is_active, role_id, station, image_path, gender, added_by, staff_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

		$stmtpolice->bind_param(
            "sssisssssssssssss",
            $txtpoliceId,
			$txtFirstName,
			$txtLastName,
			$intMobile,
			$txtEmail,
			$txtAddress,
            $txtNicNumber,
			$dateDateOfBirth,
            $txtBadgeNumber,
			$dateJoinedDate,
            $isActive,
			$selectRoleName,
			$selectStation,
			$txtImagePath,
			$selectGender,
            $txtAddedBy,
            $txtWrittenId
        );

        $stmtpolice->execute();

        // Insert into login table
        $hashedPassword = password_hash($txtNicNumber, PASSWORD_DEFAULT);

        $stmtLogin = $conn->prepare("INSERT INTO login (username, password, otp, status, role_id) VALUES (?, ?, '000000', 'active', ?)");
        $stmtLogin->bind_param("sss", $txtEmail, $hashedPassword, $selectRoleName);
        $stmtLogin->execute();

        //  Both successful
        $conn->commit();

        include_once 'lib/sms_beep.php';
        $message = "Dear {$txtFirstName} {$txtLastName}, your account has been created with Courts Complex-Kilinochchi successfully. Your login credentials are:\nUsername: {$txtEmail}\nPassword: {$txtNicNumber}";

        sendSms($intMobile, $message);

        echo '<script>alert("Successfully added a police.");</script>';
        echo "<script>location.href='index.php?pg=police.php&option=view';</script>";
        exit;

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        // Delete uploaded image
        if (file_exists($txtImagePath)) {
            unlink($txtImagePath);
        }
		Security::logError($e->getMessage()); // log real error for admin

        echo '<script>alert("An error occurred while saving. Please try again.");</script>';
    }
}


if (isset($_POST["btn_update"])) {
    // Sanitize inputs
    $txtpoliceId = Security::sanitize($_POST["txt_police_id"]);
    $txtFirstName = Security::sanitize($_POST["txt_first_name"]);
    $txtLastName = Security::sanitize($_POST["txt_last_name"]);
    $intMobile = Security::sanitize($_POST["int_mobile"]);
    $txtNicNumber = Security::sanitize($_POST["txt_nic_number"]);
    $dateDateOfBirth = Security::sanitize($_POST["date_date_of_birth"]);
    $txtEmail = Security::sanitize($_POST["txt_email"]);
    $txtAddress = Security::sanitize($_POST["txt_address"]);
    $selectStation = Security::sanitize($_POST["select_station"]);
    $selectGender = Security::sanitize($_POST["select_gender"]);
    $txtBadgeNumber = Security::sanitize($_POST["txt_badge_number"]);
    $txtAddedBy = $_SESSION["LOGIN_USERTYPE"];
    $txtWrittenId = $helper->getId($_SESSION["LOGIN_USERNAME"], $_SESSION["LOGIN_USERTYPE"]);
    // $txtAddedBy = "R03";
    // $txtStaffId = "S0001";

    // Check for CSRF Tokens
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
	}

	// Validate inputs
    $errors = [];

    if (!preg_match('/^[0-9]{10}$/', $intMobile)) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }

    if (!filter_var($txtEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[0-9]{9}[vVxX0-9]{1,3}$/', $txtNicNumber)) {
        $errors[] = "Invalid NIC number format.";
    }

    if (!$helper->validateDate($dateDateOfBirth)) {
        $errors[] = "Invalid date of birth.";
    }

    if (empty($txtFirstName) || empty($txtLastName) || empty($txtAddress)) {
        $errors[] = "Name and address fields cannot be empty.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
        exit;
    }

	// Before update staff, check for duplicates in staff, police, and police tables
	// Security::checkDuplicate($conn, "nic_number", $txtNicNumber, "", "NIC Number already exists!", $txtpoliceId);
	// Security::checkDuplicate($conn, "mobile", $intMobile, "", "Mobile number already exists!", $txtpoliceId);
	// Security::checkDuplicate($conn, "email", $txtEmail, "", "Email already exists!", $txtpoliceId);

    // Start Transaction
    $conn->begin_transaction();

    try {
        $stmtUpdate = $conn->prepare("UPDATE police SET first_name=?, last_name=?, mobile=?, email=?, address=?, nic_number=?, date_of_birth=?, badge_number=?, station=?,  gender=?, added_by=?, staff_id=? WHERE police_id=?");

		$stmtUpdate->bind_param(
            "ssissssssssss",
            $txtFirstName,
			$txtLastName,
			$intMobile,
			$txtEmail,
			$txtAddress,
			$txtNicNumber,
            $dateDateOfBirth,
            $txtBadgeNumber,
			$selectStation,
			$selectGender,
			$txtAddedBy,
			$txtWrittenId,
            $txtpoliceId
        );
        $stmtUpdate->execute();

        $conn->commit();

        echo '<script>alert("Successfully updated a police.");</script>';
        echo "<script>location.href='index.php?pg=police.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();

        Security::logError($e->getMessage());
        echo '<script>alert("An error occurred while updating. Please try again.");</script>';
    }
}

if (isset($_POST["btn_delete"])) {
    $txtpoliceId = Security::sanitize($_POST['police_id']);

    // Check for CSRF Tokens
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
	}

    // Start Transaction
     $conn->begin_transaction();

    try {
        // Get email first
        $stmtSelect = $conn->prepare("SELECT email FROM police WHERE police_id=?");
        $stmtSelect->bind_param("s", $txtpoliceId);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No email found for police ID $txtpoliceId.");
        }

        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Set login status as deleted
        $stmtLoginUpdate = $conn->prepare("
            UPDATE login 
            SET status='deleted' 
            WHERE username=?
        ");
        $stmtLoginUpdate->bind_param("s", $email);
        $stmtLoginUpdate->execute();

        // Set police is_active=0
        $stmtpoliceDelete = $conn->prepare("
            UPDATE police 
            SET is_active='0' 
            WHERE police_id=?
        ");
        $stmtpoliceDelete->bind_param("s", $txtpoliceId);
        $stmtpoliceDelete->execute();

        $conn->commit();

        echo '<script>alert("Successfully deleted the police.");</script>';
        echo "<script>location.href='index.php?pg=police.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        Security::logError($e->getMessage());
        echo '<script>alert("An error occurred while deleting. Please try again.");</script>';
    }
}

if (isset($_POST["btn_reactivate"])) {
    $txtpoliceId = Security::sanitize($_POST['police_id']);

    // Check for CSRF Tokens
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
	}

    // Start Transaction
    $conn->begin_transaction();

    try {
        // Get email first
        $stmtSelect = $conn->prepare("SELECT email FROM police WHERE police_id=?");
        $stmtSelect->bind_param("s", $txtpoliceId);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No email found for police ID $txtpoliceId.");
        }

        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Set login status as active
        $stmtLoginUpdate = $conn->prepare("
            UPDATE login 
            SET status='active' 
            WHERE username=?
        ");
        $stmtLoginUpdate->bind_param("s", $email);
        $stmtLoginUpdate->execute();

        // Set police is_active=1 (reactivate)
        $stmtpoliceReactivate = $conn->prepare("
            UPDATE police
            SET is_active='1' 
            WHERE police_id=?
        ");
        $stmtpoliceReactivate->bind_param("s", $txtpoliceId);
        $stmtpoliceReactivate->execute();

        $conn->commit();

        echo '<script>alert("Successfully reactivated the police.");</script>';
        echo "<script>location.href='index.php?pg=police.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        Security::logError($e->getMessage());
        echo '<script>alert("An error occurred while reactivating. Please try again.");</script>';
    }
}


?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Staff Page</title>
	</head>
	<body class="light-mode">
		<!-- VIEW Section-->
		<?php
			if(isset($_GET['option']) && $_GET['option'] == "view") {
			
				// $sql_read = "SELECT police_id, first_name, last_name, nic_number, mobile, court_id, police_id, email FROM staff WHERE is_active = 1";
				
				$sql_read = "SELECT police_id, first_name, last_name, nic_number, mobile, email, badge_number, is_active FROM police";
				$result = $conn->query($sql_read);

				if ($result && $result->num_rows > 0) {
			?>
		<div class="container mt-4">
			<!-- For bigger list  <div class="container-fluid mt-4"> -->
			<div class="d-flex justify-content-start mb-3">
				<a href="index.php?pg=police.php&option=add" class="btn btn-success btn-sm me-1">
				<i class="fas fa-plus"></i> Add police
				</a>
			</div>
			<div class="table-responsive">
				<table class="table table-striped attractive-table w-100">
					<thead>
						<tr>
							<th>No</th>
							<th>Full Name</th>
							<th>NIC</th>
							<th>Mobile</th>
							<th>Supreme Court Reg.No</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; while ($row = $result->fetch_assoc()) { ?>
						<tr>
							<td><?php echo $count; ?></td>
							<td>
								<strong><?php echo $row['first_name']." ".$row['last_name']; ?></strong><br>
								<?php if ($row['is_active']) { ?>
									<span class="badge bg-warning">Active</span>
									<?php } else { ?>
									<span class="badge bg-dark">Deleted</span>
								<?php } ?>
								<small class="text-muted"><?php echo $row['email']; ?></small>
							</td>
							<td><?php echo $row['nic_number']; ?></td>
							<td><?php echo $row['mobile']; ?></td>
							<td><?php echo $row['badge_number']; ?></td>
							<td>
								<div class="d-flex flex-wrap gap-1">
									<form method="POST" action="index.php?pg=police.php&option=edit" class="d-inline">
                                        
										<input type="hidden" name="police_id" value="<?php echo Security::sanitize($row['police_id']); ?>">
										<button class="btn btn-primary btn-sm" type="submit" name="btn_edit">
										<i class="fas fa-edit"></i> Edit
										</button>
									</form>
									<form method="GET" action="index.php" class="d-inline">
										<input type="hidden" name="pg" value="police.php">
										<input type="hidden" name="option" value="full_view">
										<input type="hidden" name="id" value="<?php echo urlencode(Security::sanitize($row['police_id'])); ?>">
										<button type="submit" class="btn btn-info btn-sm text-white">
										<i class="fas fa-eye"></i> Full View
										</button>
									</form>
									<!-- Delete Button or Reactive Button based on status -->
									<?php if ($row['is_active']) { ?>
										<!-- Delete Button for Active User -->
										<form method="POST" action="#" class="d-inline delete-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">	
											<input type="hidden" name="police_id" value="<?php echo Security::sanitize($row['police_id']); ?>">
											<input type="hidden" name="btn_delete" value="1">
											<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
												<i class="fas fa-trash-alt"></i> Delete
											</button>
										</form>
									<?php } else { ?>
										<!-- Reactive Button for Deleted/Inactive User -->
										<form method="POST" action="#" class="d-inline reactive-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">	
											<input type="hidden" name="police_id" value="<?php echo Security::sanitize($row['police_id']); ?>">
											<input type="hidden" name="btn_reactivate" value="1">
											<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" onclick="reactivateConfirmModal(() => this.closest('form').submit())">
												<i class="fas fa-refresh"></i> Reactive
											</button>
										</form>
									<?php } ?>
								</div>
							</td>
						</tr>
						<?php $count++; } ?>
					</tbody>
				</table>
			</div>
		</div>
<?php
	}
			// <!-- FULL VIEW SECTION -->
			}elseif (isset($_GET['option']) && $_GET['option'] == "full_view" && $_GET['id']) {
					$police_id = $_GET['id'];
			
					$row = $helper->getpoliceData(Security::sanitize($police_id));
				?>
		<div class="container py-5">
			<div class="card shadow-lg rounded-4 border-0">
				<div class="card-header bg-dark text-white rounded-top-4 d-flex align-items-center justify-content-between">
					<h3 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>police Profile</h3>
				</div>
				<div class="card-body">
					<div class="row mb-4">
						<p class="mt-2 fw-bold text-secondary">Profile Photo</p>
						<div class="col-md-3 text-center">
							<img 
								src="<?php echo Security::sanitize($row['image_path']); ?>" 
								alt="Profile Photo" 
								class="img-thumbnail shadow-sm border border-3 border-primary" 
								style="width: 300px; height: 300px; object-fit: cover;"
								>
						</div>
						<div class="col-md-9">
							<div class="card-footer text-end bg-light rounded-bottom-4">
								<div class="d-flex justify-content-between">
									<?php
										// Remove the 'S' and convert to integer
										$number = (int) filter_var($row['police_id'], FILTER_SANITIZE_NUMBER_INT);
										
										// Increment the number
										$nextNumber = $number - 1;

										// Pad with leading zeroes and add the prefix 'P'
										$previousPoliceId = 'P' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
										
										// Optional: Check if police exists before rendering the button
										$nextExists = $helper->getpoliceData($previousPoliceId);

										if ($nextExists){ ?>
									<a href="index.php?pg=police.php&option=full_view&id=<?php echo $previousPoliceId; ?>" class="btn btn-outline-success">
									<i class="bi bi-arrow-left-circle"></i> Previous
									</a>
									<?php }else{ ?>
									<button class="btn btn-outline-success" disabled>
									<i class="bi bi-arrow-left-circle"></i> Previous
									</button>
									<?php }; ?>
									<!-- Exit and  go back to view dashboard button -->
									<a href="index.php?pg=police.php&option=view" class="btn btn-danger">
									Exit <i class="bi bi-x-circle"></i>
									</a>
									<?php
										// Remove the 'S' and convert to integer
										$number = (int) filter_var($row['police_id'], FILTER_SANITIZE_NUMBER_INT);
										
										// Increment the number
										$nextNumber = $number + 1;
										
										// Pad with leading zeroes and add the prefix 'P'
										$nextPoliceId = 'P' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
										
										// Optional: Check if staff exists before rendering the button
										$nextExists = $helper->getpoliceData($nextPoliceId);
										
										if ($nextExists){ ?>
									<a href="index.php?pg=police.php&option=full_view&id=<?php echo $nextPoliceId; ?>" class="btn btn-outline-success">
									Next <i class="bi bi-arrow-right-circle"></i>
									</a>
									<?php }else{ ?>
									<button class="btn btn-outline-success" disabled>
									Next <i class="bi bi-arrow-right-circle"></i>
									</button>
									<?php }; ?>
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-hover table-bordered align-middle">
									<tbody>
										<tr>
											<th scope="row">police ID</th>
											<td><?php echo ltrim(substr($row['police_id'], 1), '0'); ?></td>
										</tr>
										<tr>
											<th scope="row">First Name</th>
											<td><?php echo Security::sanitize($row['first_name']); ?></td>
										</tr>
										<tr>
											<th scope="row">Last Name</th>
											<td><?php echo Security::sanitize($row['last_name']); ?></td>
										</tr>
										<tr>
											<th scope="row">Mobile Number</th>
											<td><?php echo Security::sanitize($row['mobile']); ?></td>
										</tr>
										<tr>
											<th scope="row">NIC Number</th>
											<td><?php echo Security::sanitize($row['nic_number']); ?></td>
										</tr>
										<tr>
											<th scope="row">Date of Birth</th>
											<td><?php echo Security::sanitize($row['date_of_birth']); ?></td>
										</tr>
										<tr>
											<th scope="row">Email</th>
											<td><?php echo Security::sanitize($row['email']); ?></td>
										</tr>
										<tr>
											<th scope="row">Gender</th>
											<td><?php echo Security::sanitize($row['gender']); ?></td>
										</tr>
										<tr>
											<th scope="row">Police Badge Number</th>
											<td><?php echo Security::sanitize($row['badge_number']); ?></td>
										</tr>
										<th scope="row">Address</th>
										<td><?php echo Security::sanitize($row['address']); ?></td>
										</tr>
										<tr>
											<th scope="row">Station/ Type</th>
											<td><?php echo Security::sanitize($row['station']); ?></td>
										</tr>
										<tr>
											<th scope="row">Joined Date</th>
											<td><?php echo Security::sanitize($row['joined_date']); ?></td>
										</tr>
										<tr>
											<th scope="row">Status</th>
											<td>
												<?php if ($row['is_active']) { ?>
												<span class="badge bg-success">Active</span>
												<?php } else { ?>
												<span class="badge bg-danger">Deleted</span>
												<?php } ?>
											</td>
										</tr>
										<tr>
											<th scope="row">Role</th>
											<td><?php echo $helper->getRoleName($row['role_id']); ?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<!-- Footer Buttons -->
				<div class="card-footer bg-light rounded-bottom-4">
					<div class="d-flex justify-content-center gap-2">
						<!-- Edit Button within Full View-->
						<form method="POST" action="http://localhost/ucms/index.php?pg=police.php&option=edit" class="d-inline">
							<input type="hidden" name="police_id" value="<?php echo Security::sanitize($row['police_id']); ?>">
							<button type="submit" class="btn btn-primary btn-sm" name="btn_edit">
							<i class="fas fa-pen-to-square me-1"></i> Edit
							</button>
						</form>
						<!-- Delete Button within Full View, This will be submitted by JS, so another Input is created in the button_delete name, so it will received by backend.-->
						<!-- JS submits form, so actual button isn't sent. Add hidden 'btn_delete' input so PHP can detect it. -->
						<!-- Delete Button or Reactive Button based on status -->
						<?php if ($row['is_active']) { ?>
							<!-- Delete Button for Active User -->
							<form method="POST" action="#" class="d-inline delete-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
								<input type="hidden" name="police_id" value="<?php echo Security::sanitize($row['police_id']); ?>">
								<input type="hidden" name="btn_delete" value="1">
								<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
									<i class="fas fa-trash-alt"></i> Delete
								</button>
							</form>
						<?php } else { ?>
							<!-- Reactive Button for Deleted/Inactive User -->
							<form method="POST" action="#" class="d-inline reactive-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
								<input type="hidden" name="police_id" value="<?php echo Security::sanitize($row['police_id']); ?>">
								<input type="hidden" name="btn_reactivate" value="1">
								<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" onclick="reactivateConfirmModal(() => this.closest('form').submit())">
									<i class="fas fa-refresh"></i> Reactive
								</button>
							</form>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>








<?php
// Fetch case results
$query1 = "SELECT * FROM cases WHERE plaintiff_police = ? OR defendant_police = ?";
$cstmt = $conn->prepare($query1);
$cstmt->bind_param("ss", $policeId, $policeId);
$cstmt->execute();
$caseResult = $cstmt->get_result();

$cases = [];
$partyDetails = [];

// Fetch case and party details
while ($row = $caseResult->fetch_assoc()) {
    $cases[] = $row; // store the case for later use

    // Determine which party to retrieve based on the police's role
    $partyId = ($row['plaintiff_police'] == $policeId) ? $row['plaintiff'] : $row['defendant'];

    // Fetch party details
    $query2 = "SELECT party_id, first_name, last_name, mobile, nic_number, email, joined_date, address, date_of_birth, is_active FROM parties WHERE party_id = ?";
    $pstmt = $conn->prepare($query2);
    $pstmt->bind_param("s", $partyId);
    $pstmt->execute();
    $partyResult = $pstmt->get_result();

    if ($partyRow = $partyResult->fetch_assoc()) {
        $partyDetails[] = $partyRow;
    }

    $pstmt->close();
}
$cstmt->close();

// Fetch related daily case activities for each case
foreach ($cases as $case) {
    $caseName = $case['case_id']; // Get the case_id dynamically

    // Fetch related daily case activities from the dailycaseactivities table
    $query3 = "SELECT * FROM dailycaseactivities WHERE case_name = ?";
    $stmt3 = $conn->prepare($query3);
    $stmt3->bind_param("s", $caseName);
    $stmt3->execute();
    $dailyCaseActivitiesResult = $stmt3->get_result();

    // Combine daily activities in the case array
    $case['daily_activities'] = [];
    while ($activity = $dailyCaseActivitiesResult->fetch_assoc()) {
        $case['daily_activities'][] = $activity;
    }
    $stmt3->close();
}
?>


<div class="accordion" id="accordionExample">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
        Client Details
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
      <div class="accordion-body">
		<?php
			foreach ($partyDetails as $party) {
			?>
				<div class='card mb-3 shadow-sm'>
					<div class='card-body'>
						<h5 class='card-title'>Client Name: <?php echo "{$party['first_name']} {$party['last_name']}"; ?></h5>
						<p><strong>NIC:</strong> <?php echo Security::sanitize("{$party['nic_number']}"); ?></p>
						<p><strong>Mobile:</strong> <?php echo Security::sanitize("{$party['mobile']}"); ?></p>
						<p><strong>Email:</strong> <?php echo Security::sanitize("{$party['email']}"); ?></p>
						<p><strong>DOB:</strong> <?php echo Security::sanitize("{$party['date_of_birth']}"); ?></p>
						<p><strong>Joined:</strong> <?php echo Security::sanitize("{$party['joined_date']}"); ?></p>
						<p><strong>Address:</strong> <?php echo Security::sanitize("{$party['address']}"); ?></p>
					</div>
				</div>
			<?php
			}
			?>

      </div>
    </div>
  </div>

  <div class="accordion-item">
  <h2 class="accordion-header" id="headingTwo">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
      Case Details
    </button>
  </h2>
  <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
    <div class="accordion-body">
      <?php foreach ($cases as $case): ?>
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Case: <?= Security::sanitize($case['case_name']) ?></h5>
            <p><strong>Status:</strong> <?= Security::sanitize($case['status']) ?></p>
            <p><strong>Next Date:</strong> <?= Security::sanitize($case['next_date']) ?></p>
            <p><strong>For:</strong> <?= Security::sanitize($case['for_what']) ?></p>

<form method="POST" action="index.php?pg=police.php&option=full_view&id=<?= $policeId ?>" class="d-inline">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="case_id" value="<?= urlencode($case['case_id']) ?>">
    <button type="submit" class="btn btn-info btn-sm text-white">
        <i class="fas fa-eye"></i> Full View
    </button>
</form>


          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>


  <div class="accordion-item">
    <h2 class="accordion-header" id="headingThree">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
        Accordion Item #3
      </button>
    </h2>
    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <strong>This is the third item's accordion body.</strong> It is hidden by default...
      </div>
    </div>
  </div>
</div>








		<?php
			// <!-- ADD SECTION -->
			}elseif (isset($_GET['option']) && $_GET['option'] == "add") {
				$nextPoliceId = $helper->generateNextpoliceID(); // Get the next ID *before* the form
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>ADD NEW police</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="policeForm" name="policeForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="police_id" class="form-label">police ID</label>
							<input hidden type="text" class="form-control" id="txt_police_id" name="txt_police_id" value="<?php echo Security::sanitize($nextPoliceId); ?>" readonly required>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name" onkeypress="return isTextKey(event)" required>
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name" onkeypress="return isTextKey(event)" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile" class="form-label">Mobile Number</label>
								<input type="text" name="int_mobile" id="int_mobile" class="form-control check-duplicate" data-check="mobile" data-feedback="mobileFeedback" onkeypress="return isNumberKey(event)" onblur="validateMobileNumber('int_mobile')" required>
								<small id="mobileFeedback" class="text-danger"></small>
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<!-- <input type="text" class="form-control" id="txt_nic_number" name="txt_nic_number" onblur="nicnumber('txt_nic_number')" required> -->
								<input type="text" name="txt_nic_number" id="txt_nic_number" class="form-control check-duplicate" data-check="nic" data-feedback="nicFeedback" onblur="validateNIC('txt_nic_number')" required>
								<small id="nicFeedback" class="text-danger"></small>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email/ Username</label>
								<input type="email" name="txt_email" id="txt_email" class="form-control check-duplicate" data-check="email" data-feedback="emailFeedback" onblur="validateEmail('txt_email')" required>
								<small id="emailFeedback" class="text-danger"></small>
							</div>
							<div class="col-md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="court_id" class="form-label">Station</label>
								<select class="form-select" id="select_station" name="select_station">
									<option value="" disabled selected hidden>Select Police Station</option>
									<option value="Kilinochchi HQ">Kilinochchi HQ</option>
									<option value="Palai">Palai</option>
									<option value="Poonakari">Poonakari</option>
									<option value="Tharmapuram">Tharmapuram</option>
									<option value="Jeyapuram">Jeyapuram</option>
									<option value="Akkarayan">Akkarayan</option>
									<option value="Maruthankeni">Maruthankeni</option>
									<option value="Ramanathapuram">Ramanathapuram</option>
									<option value="Mulankaavil">Mulankaavil</option>
									<option value="S.C.I.B">S.C.I.B</option>
									<option value="D.C.D.B">D.C.D.B</option>
									<option value="C.I.D">C.I.D</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="profile_photo" class="form-label">Upload Profile Photo</label>
								<input type="file" class="form-control" id="img_profile_photo" name="img_profile_photo" accept="image/*">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth" required>
							</div>
							<div class="col-md-6">
								<label for="court_id" class="form-label">Gender</label>
								<select class="form-select" id="select_gender" name="select_gender" required>
									<option value="" disabled selected hidden>Select Gender</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
									<option value="Other">Other</option>
								</select>
							</div>
						</div>
                        <div class="mb-3">
                            <label for="police_id" class="form-label">police Badge Number</label>
                            <input type="text" class="form-control" id="txt_badge_number" name="txt_badge_number" required>
                        </div>
                        <label>* Plese be kind enough to note that Password will be generated, & sent to Registered email/ mobile</label><br><br>
						<div>
							<label hidden for="joined_date" class="form-label">Joined Date</label>
							<input hidden type="date" class="form-control" id="date_joined_date" max="<?php echo date('Y-m-d'); ?>" name="date_joined_date" value="<?php echo date('Y-m-d'); ?>" required>
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
						</div>
						<button type="submit" class="btn btn-primary" id="btn_add" name="btn_add">Submit</button>
						<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
				</div>
			</div>
		</div>
		`	<?php
			// <!-- EDIT SECTION -->
			}elseif(isset($_GET['option']) && $_GET['option'] == "edit" && isset($_POST['police_id'])) {
				$data = $helper->getpoliceData(Security::sanitize($_POST['police_id']), $conn);
			
				$txtFirstName = $data['first_name'];
				$txtLastName = $data['last_name'];
				$intMobile = $data['mobile'];
				$txtNicNumber = $data['nic_number'];
				$dateDateOfBirth = $data['date_of_birth'];
				$txtEmail = $data['email'];
				$txtAddress = $data['address'];
				$txtBadgeNumber = $data['badge_number'];
				$selectGender = $data['gender'];
                $selectStation = $data['station'];


			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>EDIT police</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="police_id" class="form-label">police ID</label>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<input hidden type="text" class="form-control" id="txt_police_id" name="txt_police_id" value="<?php echo Security::sanitize($_POST['police_id']); ?>" readonly required>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name" value="<?php echo $txtFirstName ?>" onkeypress="return isTextKey(event)" required>
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name"  value="<?php echo $txtLastName ?>" onkeypress="return isTextKey(event)" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile" class="form-label">Mobile Number</label>
								<input type="text" name="int_mobile" id="int_mobile" class="form-control check-duplicate" data-check="mobile" data-feedback="mobileFeedback" value="<?php echo '0'.$intMobile ?>" onkeypress="return isNumberKey(event)" onblur="validateMobileNumber('int_mobile')" required>
								<small id="mobileFeedback" class="text-danger"></small>
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control check-duplicate" id="txt_nic_number" name="txt_nic_number" data-check="nic" data-feedback="nicFeedback" value="<?php echo $txtNicNumber ?>" onblur="validateNIC('txt_nic_number')" required>
								<small id="nicFeedback" class="text-danger"></small>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" name="txt_email" id="txt_email" class="form-control check-duplicate" data-check="email" data-feedback="emailFeedback" value="<?php echo $txtEmail ?>" onblur="validateEmail('txt_email')" required>
								<small id="emailFeedback" class="text-danger"></small>
							</div>
							<div class="col-md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address" value="<?php echo $txtAddress ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="court_id" class="form-label">Station</label>
								<select class="form-select" id="select_station" name="select_station">
									<option value="" disabled selected hidden>Select Police Station</option>
									<option value="Kilinochchi HQ">Kilinochchi HQ</option>
									<option value="Palai">Palai</option>
									<option value="Poonakari">Poonakari</option>
									<option value="Tharmapuram">Tharmapuram</option>
									<option value="Jeyapuram">Jeyapuram</option>
									<option value="Akkarayan">Akkarayan</option>
									<option value="Maruthankeni">Maruthankeni</option>
									<option value="Ramanathapuram">Ramanathapuram</option>
									<option value="Mulankaavil">Mulankaavil</option>
									<option value="S.C.I.B">S.C.I.B</option>
									<option value="D.C.D.B">D.C.D.B</option>
									<option value="C.I.D">C.I.D</option>
								</select>
							</div>
                            <div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth" value="<?php echo $dateDateOfBirth ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="court_id" class="form-label">Gender</label>
								<select class="form-select" id="select_gender" name="select_gender" value="<?php echo $select_gender ?>" required>
									<option value="" disabled hidden>Select Gender</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
									<option value="Other">Other</option>
								</select>
							</div>
                            <div class="col-md-6">
                            <label for="police_id" class="form-label">Police Badge Number</label>
                            <input type="text" class="form-control" id="txt_badge_number" name="txt_badge_number" value="<?php echo $txtBadgeNumber ?>" required>
                        </div>
						</div>
						<button type="submit" class="btn btn-primary" id="btn_update" name="btn_update">Submit</button>
						<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
				</div>
			</div>
		</div>
		<?php
			}else{
				echo "<script> location.href='index.php'; </script>";
				exit; 
			}
			?>
		<!-- Delete Confirmation Modal 1-->
		<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						Are you sure you want to delete?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">Delete</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Reactivate Confirmation Modal -->
		<div class="modal fade" id="reactivateConfirmModal" tabindex="-1" aria-labelledby="reactivateConfirmModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="reactivateConfirmModalLabel">Confirm Reactivation</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						Are you sure you want to reactivate this account?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-success" id="reactivateConfirmBtn" 
								onclick="this.closest('form').submit(); document.getElementById('reactivateConfirmModal').modal('hide');">
							Reactivate
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Generic Modal (Reusable for various cases) -->
		<div class="modal fade" id="genericModal" tabindex="-1" aria-labelledby="genericModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="genericModalLabel">Modal Title</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body" id="genericModalBody">
						<!-- This content will be updated by JS -->
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary" id="modalConfirmBtn" style="display: none;">Confirm</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Case Detail Modal -->

        
        <!-- Case Detail Modal -->
<div class="modal fade" id="caseDetailModal" tabindex="-1" aria-labelledby="caseDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="background-color: #C0C0C0;"> <!-- Silver background -->
      <div class="modal-header">
        <h5 class="modal-title" id="caseDetailModalLabel">Case Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="case-detail-content">
          <?php
            // Check if case data is available
            if (isset($_POST['case_id'])) {
                $caseId = Security::sanitize($_POST['case_id']);

                // Fetch case details based on case_id
                $query = "SELECT * FROM cases WHERE case_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $caseId);
                $stmt->execute();
                $caseResult = $stmt->get_result();

                if ($case = $caseResult->fetch_assoc()) {
                    $caseName = $case['case_name'];
                    $plaintiff = $case['plaintiff'];
                    $defendant = $case['defendant'];
                    $plaintiffpolice = $case['plaintiff_police'];
                    $defendantpolice = $case['defendant_police'];
                    $nature = $case['nature'];
                    $isWarrant = $case['is_warrant'];
                    $courtId = $case['court_id'];
                    $registeredDate = $case['registered_date'];

                    // Fetch related daily case activities
                    $query_activities = "SELECT * FROM dailycaseactivities WHERE case_name = ?";
                    $stmt_activities = $conn->prepare($query_activities);
                    $stmt_activities->bind_param("s", $caseName);
                    $stmt_activities->execute();
                    $activities_result = $stmt_activities->get_result();

                    // Start building the modal content
                    ?>
                    <div class="case-header"><?= Security::sanitize($case_name) ?></div>
                    
                    <div class="case-row">
                      <div class="case-column">
                        <p><span class="case-label">Plaintiff Name:</span> <span class="case-value"><?= Security::sanitize($plaintiff) ?></span></p>
                        <p><span class="case-label">Defendant Name:</span> <span class="case-value"><?= Security::sanitize($defendant) ?></span></p>
                        <p><span class="case-label">Plaintiff police:</span> <span class="case-value"><?= Security::sanitize($plaintiffpolice) ?></span></p>
                        <p><span class="case-label">Defendant police:</span> <span class="case-value"><?= Security::sanitize($defendantpolice) ?></span></p>
                      </div>
                      <div class="case-column">
                        <p><span class="case-label">Nature of the Case:</span> <span class="case-value"><?= Security::sanitize($nature) ?></span></p>
                        <p><span class="case-label">Warrant:</span> 
                          <span class="warrant-status <?= $isWarrant ? 'warrant-yes' : 'warrant-no' ?>">
                            <?= $isWarrant ? 'Arrest Warrant Issued' : 'No Warrant' ?>
                          </span>
                        </p>
                        <p><span class="case-label">Court Name:</span> <span class="case-value"><?= Security::sanitize($courtId) ?></span></p>
                        <p><span class="case-label">Case Registered Date:</span> <span class="case-value"><?= Security::sanitize($registeredDate) ?></span></p>
                      </div>
                    </div>

                    <div class="activities-section">
                      <h4>Daily Case Activities</h4>
                      <?php
                      if ($activities_result->num_rows > 0) {
                          while ($activity = $activities_result->fetch_assoc()) {
                              ?>
                              <div class="activity-card">
                                <h5><?= Security::sanitize($activity['activity_date']) ?></h5>
                                <p><strong>Summary:</strong> <?= Security::sanitize($activity['summary']) ?></p>
                                <p><strong>Next Date:</strong> <?= Security::sanitize($activity['next_date']) ?></p>
                                <p><strong>Next Steps:</strong> <?= Security::sanitize($activity['next_status']) ?></p>
                              </div>
                              <?php
                          }
                      } else {
                          echo "<p>No activities recorded for this case yet.</p>";
                      }
                      ?>
                    </div>
                    <?php
                } else {
                    echo "<p>Case details not found.</p>";
                }

                $stmt->close();
                $stmt_activities->close();
            }
          ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>




<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="card shadow-lg p-4 rounded-4" style="max-width: 600px; width: 100%;">
    <h4 class="text-center mb-4 text-primary fw-bold">🔍 Search for a Case</h4>

    <form id="caseSearchForm">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <div class="input-group input-group-lg mb-3">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control border-start-0" name="case_input" placeholder="Enter Case ID or Name..." required>
        <button class="btn btn-outline-primary" type="submit">Search</button>
      </div>
    </form>

    <div id="caseResult" class="mt-3"></div>
  </div>
</div>




<script>
document.getElementById('caseSearchForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('action/donor.ajax.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const resultDiv = document.getElementById('caseResult');
    if (data.error) {
      resultDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
      return;
    }

    const warrantAlert = data.is_warrant == 1
      ? `<div class="alert alert-danger fw-bold">⚠ Arrest Warrant Issued</div>`
      : `<div class="alert alert-success">✅ No Arrest Warrant</div>`;

    const activity = data.activity
      ? `<div class="card">
           <div class="card-body">
             <h5 class="card-title">Latest Activity (${data.activity.activity_date})</h5>
             <p><strong>Summary:</strong> ${data.activity.summary}</p>
             <p><strong>Next Date:</strong> ${data.activity.next_date}</p>
           </div>
         </div>`
      : `<p class="text-muted">No activity found for this case.</p>`;

    resultDiv.innerHTML = `
      <div class="mb-3"><strong>Case Name:</strong> ${data.case_name}</div>
      ${warrantAlert}
      ${activity}
    `;
  })
  .catch(err => {
    document.getElementById('caseResult').innerHTML = `<div class="alert alert-warning">Error fetching data.</div>`;
  });
});
</script>



</html>