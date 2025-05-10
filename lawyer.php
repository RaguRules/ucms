<?php

// require 'db.php';

if(isset($_SESSION["LOGIN_USERTYPE"])){
    $system_usertype = $_SESSION["LOGIN_USERTYPE"];
	$system_username = $_SESSION["LOGIN_USERNAME"];
}else{
	$system_usertype = "GUEST";
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$lawyer_id = null;

if ($system_usertype === "R06" && $system_username) {
    // Get lawyer_id by email
    $stmt = $conn->prepare("SELECT lawyer_id FROM lawyer WHERE email = ?");
    $stmt->bind_param("s", $system_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $lawyer_id = $row['lawyer_id'];
    } else {
        // If email not found in DB, logout or redirect
		// #TODO : logout.php not available
        // echo "<script>location.href='logout.php';</script>";
        // exit;
    }
} else {
    // Not a valid session
    // echo "<script>location.href='login.php';</script>";
    // exit;
}


// Get all cases related to this lawyer
$query = "SELECT * FROM cases WHERE plaintiff_lawyer = ? OR defendant_lawyer = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $lawyer_id, $lawyer_id);
$stmt->execute();
$cases = $stmt->get_result();
	

if (isset($_POST["btn_add"])) {
    // Sanitize inputs
    $txt_staff_id = sanitize_input($_POST["txt_staff_id"]);
    $txt_first_name = sanitize_input($_POST["txt_first_name"]);
    $txt_last_name = sanitize_input($_POST["txt_last_name"]);
    $int_mobile = sanitize_input($_POST["int_mobile"]);
    $txt_nic_number = sanitize_input($_POST["txt_nic_number"]);
    $date_date_of_birth = sanitize_input($_POST["date_date_of_birth"]);
    $txt_email = sanitize_input($_POST["txt_email"]);
    $txt_address = sanitize_input($_POST["txt_address"]);
    $select_court_name = sanitize_input($_POST["select_court_name"]);
    $date_joined_date = sanitize_input($_POST["date_joined_date"]);
    $select_role_name = sanitize_input($_POST["select_role_name"]);
    $select_gender = sanitize_input($_POST["select_gender"]);
    $select_appointment = sanitize_input($_POST["select_appointment"]);
    $status = "active";

	// Check for CSRF Tokens
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
	}

    // Validate inputs
    $errors = [];

    if (!preg_match('/^[0-9]{10}$/', $int_mobile)) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }

    if (!filter_var($txt_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[0-9]{9}[vVxX0-9]{1,3}$/', $txt_nic_number)) {
        $errors[] = "Invalid NIC number format.";
    }

    if (!validateDate($date_date_of_birth)) {
        $errors[] = "Invalid date of birth.";
    }

    if (!validateDate($date_joined_date)) {
        $errors[] = "Invalid joined date.";
    }

    if (empty($txt_first_name) || empty($txt_last_name) || empty($txt_address)) {
        $errors[] = "Name and address fields cannot be empty.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
        exit;
    }

	// Before Insert staff, check for duplicates in staff, lawyer, and police tables
	check_duplicate($conn, "nic_number", $txt_nic_number, "", "NIC Number already exists!", $txt_staff_id);
	check_duplicate($conn, "mobile", $int_mobile, "", "Mobile number already exists!", $txt_staff_id);
	check_duplicate($conn, "email", $txt_email, "", "Email already exists!", $txt_staff_id);

    // Image upload
    $upload_result = secure_image_upload('img_profile_photo');

    if (!$upload_result['success']) {
        die("Image upload failed: " . $upload_result['error']);
    }

    $txt_image_path = 'uploads/' . $upload_result['filename'];

    // Begin transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert into staff table
        $stmtStaff = $conn->prepare("INSERT INTO staff (lawyer_id, first_name, last_name, mobile, nic_number, date_of_birth, email, address, court_id, joined_date, role_id, image_path, gender, appointment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
		$stmtStaff->bind_param(
            "ssssssssssssss",
            $txt_staff_id,
			$txt_first_name,
			$txt_last_name,
			$int_mobile,
			$txt_nic_number,
			$date_date_of_birth,
			$txt_email,
			$txt_address,
			$select_court_name,
			$date_joined_date,
			$select_role_name,
			$txt_image_path,
			$select_gender,
			$select_appointment
        );

        $stmtStaff->execute();

        // Insert into login table
        $hashedPassword = password_hash($txt_nic_number, PASSWORD_DEFAULT);

        $stmtLogin = $conn->prepare("INSERT INTO login (username, password, otp, status, role_id) VALUES (?, ?, '000000', ?, ?)");
        $stmtLogin->bind_param("ssss", $txt_email, $hashedPassword, $status, $select_role_name);
        $stmtLogin->execute();

        //  Both successful
        mysqli_commit($conn);

        echo '<script>alert("Successfully added staff member.");</script>';
        echo "<script>location.href='index.php?pg=lawyer.php&option=view';</script>";
        exit;

    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);

        // Delete uploaded image
        if (file_exists($txt_image_path)) {
            unlink($txt_image_path);
        }
		logError($e->getMessage()); // log real error for admin

        echo '<script>alert("An error occurred while saving. Please try again.");</script>';
    }
}


if (isset($_POST["btn_update"])) {
    // Sanitize inputs
    $txt_staff_id = sanitize_input($_POST["txt_staff_id"]);
    $txt_first_name = sanitize_input($_POST["txt_first_name"]);
    $txt_last_name = sanitize_input($_POST["txt_last_name"]);
    $int_mobile = sanitize_input($_POST["int_mobile"]);
    $txt_nic_number = sanitize_input($_POST["txt_nic_number"]);
    $date_date_of_birth = sanitize_input($_POST["date_date_of_birth"]);
    $txt_email = sanitize_input($_POST["txt_email"]);
    $txt_address = sanitize_input($_POST["txt_address"]);
    $select_court_name = sanitize_input($_POST["select_court_name"]);
    $select_gender = sanitize_input($_POST["select_gender"]);
    $select_role_name = sanitize_input($_POST["select_role_name"]);
    $select_appointment = sanitize_input($_POST["select_appointment"]);

	// CSRF Protection
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
    }

	// Validate inputs
    $errors = [];

    if (!preg_match('/^[0-9]{10}$/', $int_mobile)) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }

    if (!filter_var($txt_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[0-9]{9}[vVxX0-9]{1,3}$/', $txt_nic_number)) {
        $errors[] = "Invalid NIC number format.";
    }

    if (!validateDate($date_date_of_birth)) {
        $errors[] = "Invalid date of birth.";
    }

    if (empty($txt_first_name) || empty($txt_last_name) || empty($txt_address)) {
        $errors[] = "Name and address fields cannot be empty.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
        exit;
    }

	// Before update staff, check for duplicates in staff, lawyer, and police tables
	check_duplicate($conn, "nic_number", $txt_nic_number, "", "NIC Number already exists!", $txt_staff_id);
	check_duplicate($conn, "mobile", $int_mobile, "", "Mobile number already exists!", $txt_staff_id);
	check_duplicate($conn, "email", $txt_email, "", "Email already exists!", $txt_staff_id);

	// Upload image only if a new one was selected
    $new_image_uploaded = ($_FILES['img_profile_photo']['error'] !== 4); // Error 4 = no file uploaded

    if ($new_image_uploaded) {
        $upload_result = secure_image_upload('img_profile_photo');
        if (!$upload_result['success']) {
            die("Image upload failed: " . $upload_result['error']);
        }
        $txt_image_path = 'uploads/' . $upload_result['filename'];
    } else {
        // No new image, fetch old image path from database
        $stmtOld = $conn->prepare("SELECT image_path FROM staff WHERE lawyer_id = ?");
        $stmtOld->bind_param("s", $txt_staff_id);
        $stmtOld->execute();
        $resultOld = $stmtOld->get_result();
        $rowOld = $resultOld->fetch_assoc();
        $txt_image_path = $rowOld['image_path']; // Use old image path
    }

    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        $stmtUpdate = $conn->prepare("UPDATE staff SET first_name=?, last_name=?, mobile=?, nic_number=?, date_of_birth=?, email=?, address=?, court_id=?, role_id=?, image_path=?, gender=?, appointment=? WHERE lawyer_id=?");
        
		$stmtUpdate->bind_param(
            "sssssssssssss",
            $txt_first_name,
			$txt_last_name,
			$int_mobile,
			$txt_nic_number,
            $date_date_of_birth,
			$txt_email,
			$txt_address,
			$select_court_name,
            $select_role_name,
			$txt_image_path,
			$select_gender,
			$select_appointment,
			$txt_staff_id
        );
        $stmtUpdate->execute();

        mysqli_commit($conn);

        echo '<script>alert("Successfully updated staff member.");</script>';
        echo "<script>location.href='index.php?pg=lawyer.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);

		// If new image was uploaded, delete it
        if ($new_image_uploaded && file_exists($txt_image_path)) {
            unlink($txt_image_path);
        }

        logError($e->getMessage());
        echo '<script>alert("An error occurred while updating. Please try again.");</script>';
    }
}

if (isset($_POST["btn_delete"])) {
    $txt_staff_id = sanitize_input($_POST['lawyer_id']);

    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        // Get email first
        $stmtSelect = $conn->prepare("SELECT email FROM staff WHERE lawyer_id=?");
        $stmtSelect->bind_param("s", $txt_staff_id);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No email found for staff ID $txt_staff_id.");
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

        // Set staff is_active=0
        $stmtStaffDelete = $conn->prepare("
            UPDATE staff 
            SET is_active='0' 
            WHERE lawyer_id=?
        ");
        $stmtStaffDelete->bind_param("s", $txt_staff_id);
        $stmtStaffDelete->execute();

        mysqli_commit($conn);

        echo '<script>alert("Successfully deleted staff member.");</script>';
        echo "<script>location.href='index.php?pg=lawyer.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        logError($e->getMessage());
        echo '<script>alert("An error occurred while deleting. Please try again.");</script>';
    }
}

if (isset($_POST["btn_reactivate"])) {
    $txt_staff_id = sanitize_input($_POST['lawyer_id']);

    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        // Get email first
        $stmtSelect = $conn->prepare("SELECT email FROM staff WHERE lawyer_id=?");
        $stmtSelect->bind_param("s", $txt_staff_id);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No email found for staff ID $txt_staff_id.");
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

        // Set staff is_active=1 (reactivate)
        $stmtStaffReactivate = $conn->prepare("
            UPDATE staff 
            SET is_active='1' 
            WHERE lawyer_id=?
        ");
        $stmtStaffReactivate->bind_param("s", $txt_staff_id);
        $stmtStaffReactivate->execute();

        mysqli_commit($conn);

        echo '<script>alert("Successfully reactivated staff member.");</script>';
        echo "<script>location.href='index.php?pg=lawyer.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        logError($e->getMessage());
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
			
				// $sql_read = "SELECT lawyer_id, first_name, last_name, nic_number, mobile, court_id, lawyer_id, email FROM staff WHERE is_active = 1";
				
				$sql_read = "SELECT lawyer_id, first_name, last_name, nic_number, mobile, email, enrolment_number, is_active FROM lawyer";
				$result = mysqli_query($conn, $sql_read);
			
				if ($result && mysqli_num_rows($result) > 0) {
			?>
		<div class="container mt-4">
			<!-- For bigger list  <div class="container-fluid mt-4"> -->
			<div class="d-flex justify-content-start mb-3">
				<a href="index.php?pg=lawyer.php&option=add" class="btn btn-success btn-sm me-1">
				<i class="fas fa-plus"></i> Add Lawyer
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
						<?php $count = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
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
							<td><?php echo $row['enrolment_number']; ?></td>
							<td>
								<div class="d-flex flex-wrap gap-1">
									<form method="POST" action="index.php?pg=lawyer.php&option=edit" class="d-inline">
										<input type="hidden" name="lawyer_id" value="<?php echo sanitize_input($row['lawyer_id']); ?>">
										<button class="btn btn-primary btn-sm" type="submit" name="btn_edit">
										<i class="fas fa-edit"></i> Edit
										</button>
									</form>
									<form method="GET" action="index.php" class="d-inline">
										<input type="hidden" name="pg" value="lawyer.php">
										<input type="hidden" name="option" value="full_view">
										<input type="hidden" name="id" value="<?php echo urlencode(sanitize_input($row['lawyer_id'])); ?>">
										<button type="submit" class="btn btn-info btn-sm text-white">
										<i class="fas fa-eye"></i> Full View
										</button>
									</form>
									<!-- Delete Button or Reactive Button based on status -->
									<?php if ($row['is_active']) { ?>
										<!-- Delete Button for Active User -->
										<form method="POST" action="#" class="d-inline delete-form">
											<input type="hidden" name="lawyer_id" value="<?php echo sanitize_input($row['lawyer_id']); ?>">
											<input type="hidden" name="btn_delete" value="1">
											<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
												<i class="fas fa-trash-alt"></i> Delete
											</button>
										</form>
									<?php } else { ?>
										<!-- Reactive Button for Deleted/Inactive User -->
										<form method="POST" action="#" class="d-inline reactive-form">
											<input type="hidden" name="lawyer_id" value="<?php echo sanitize_input($row['lawyer_id']); ?>">
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
					$lawyer_id = $_GET['id'];
			
					$row = getLawyerDataFromDatabase(sanitize_input($lawyer_id), $conn);
				?>
		<div class="container py-5">
			<div class="card shadow-lg rounded-4 border-0">
				<div class="card-header bg-dark text-white rounded-top-4 d-flex align-items-center justify-content-between">
					<h3 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Lawyer Profile</h3>
				</div>
				<div class="card-body">
					<div class="row mb-4">
						<p class="mt-2 fw-bold text-secondary">Profile Photo</p>
						<div class="col-md-3 text-center">
							<img 
								src="<?php echo sanitize_input($row['image_path']); ?>" 
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
										$number = (int) filter_var($row['lawyer_id'], FILTER_SANITIZE_NUMBER_INT);
										
										// Increment the number
										$next_number = $number - 1;
										
										// Pad with leading zeroes and add the prefix 'S'
										$previous_lawyer_id = 'L' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
										
										// Optional: Check if lawyer exists before rendering the button
										$next_exists = getLawyerDataFromDatabase($previous_lawyer_id, $conn);
										
										if ($next_exists){ ?>
									<a href="index.php?pg=lawyer.php&option=full_view&id=<?php echo $previous_lawyer_id; ?>" class="btn btn-outline-success">
									<i class="bi bi-arrow-left-circle"></i> Previous
									</a>
									<?php }else{ ?>
									<button class="btn btn-outline-success" disabled>
									<i class="bi bi-arrow-left-circle"></i> Previous
									</button>
									<?php }; ?>
									<!-- Exit and  go back to view dashboard button -->
									<a href="index.php?pg=lawyer.php&option=view" class="btn btn-danger">
									Exit <i class="bi bi-x-circle"></i>
									</a>
									<?php
										// Remove the 'S' and convert to integer
										$number = (int) filter_var($row['lawyer_id'], FILTER_SANITIZE_NUMBER_INT);
										
										// Increment the number
										$next_number = $number + 1;
										
										// Pad with leading zeroes and add the prefix 'S'
										$next_lawyer_id = 'L' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
										
										// Optional: Check if staff exists before rendering the button
										$next_exists = getLawyerDataFromDatabase($next_lawyer_id, $conn);
										
										if ($next_exists){ ?>
									<a href="index.php?pg=lawyer.php&option=full_view&id=<?php echo $next_lawyer_id; ?>" class="btn btn-outline-success">
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
											<th scope="row">Lawyer ID</th>
											<td><?php echo ltrim(substr($row['lawyer_id'], 1), '0'); ?></td>
										</tr>
										<tr>
											<th scope="row">First Name</th>
											<td><?php echo sanitize_input($row['first_name']); ?></td>
										</tr>
										<tr>
											<th scope="row">Last Name</th>
											<td><?php echo sanitize_input($row['last_name']); ?></td>
										</tr>
										<tr>
											<th scope="row">Mobile Number</th>
											<td><?php echo sanitize_input($row['mobile']); ?></td>
										</tr>
										<tr>
											<th scope="row">NIC Number</th>
											<td><?php echo sanitize_input($row['nic_number']); ?></td>
										</tr>
										<tr>
											<th scope="row">Date of Birth</th>
											<td><?php echo sanitize_input($row['date_of_birth']); ?></td>
										</tr>
										<tr>
											<th scope="row">Email</th>
											<td><?php echo sanitize_input($row['email']); ?></td>
										</tr>
										<tr>
											<th scope="row">Gender</th>
											<td><?php echo sanitize_input($row['gender']); ?></td>
										</tr>
										<tr>
											<th scope="row">Supreme Court Enrolment Number</th>
											<td><?php echo sanitize_input($row['enrolment_number']); ?></td>
										</tr>
										<th scope="row">Address</th>
										<td><?php echo sanitize_input($row['address']); ?></td>
										</tr>
										<tr>
											<th scope="row">Station/ Type</th>
											<td><?php echo sanitize_input($row['station']); ?></td>
										</tr>
										<tr>
											<th scope="row">Joined Date</th>
											<td><?php echo sanitize_input($row['joined_date']); ?></td>
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
											<td><?php echo getRoleName($row['role_id']); ?></td>
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
						<form method="POST" action="http://localhost/ucms/index.php?pg=lawyer.php&option=edit" class="d-inline">
							<input type="hidden" name="lawyer_id" value="<?php echo sanitize_input($row['lawyer_id']); ?>">
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
								<input type="hidden" name="lawyer_id" value="<?php echo sanitize_input($row['lawyer_id']); ?>">
								<input type="hidden" name="btn_delete" value="1">
								<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
									<i class="fas fa-trash-alt"></i> Delete
								</button>
							</form>
						<?php } else { ?>
							<!-- Reactive Button for Deleted/Inactive User -->
							<form method="POST" action="#" class="d-inline reactive-form">
								<input type="hidden" name="lawyer_id" value="<?php echo sanitize_input($row['lawyer_id']); ?>">
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








		<!-- Fetch all required data -->
<?php

$query1 = "SELECT * FROM cases WHERE plaintiff_lawyer = ? OR defendant_lawyer = ?";
$cstmt = $conn->prepare($query1);
$cstmt->bind_param("ss", $lawyer_id, $lawyer_id);
$cstmt->execute();
$caseResult = $cstmt->get_result();

$party_id = "P0001";
$query2 = "SELECT * FROM parties WHERE party_id = ? AND is_deleted = 0";
$pstmt = $conn->prepare($query2);
$pstmt->bind_param("s", $party_id);
$pstmt->execute();
$partyResult = $pstmt->get_result();

// $case_id = "C0001";
$case_name = "C0001";
$query3 = "SELECT * FROM dailycaseactivities WHERE case_name = ?";
$dstmt = $conn->prepare($query3);
$dstmt->bind_param("s", $case_name);
$dstmt->execute();
$dailyCaseActivityResult = $dstmt->get_result();

$parties = [];
$cases = [];

while ($row = $partyResult->fetch_assoc()) {
    $parties[] = $row;
}
while ($row = $caseResult->fetch_assoc()) {
    $cases[] = $row;
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
			foreach ($parties as $party) {
		?>
		<div class='card mb-3 shadow-sm'>
			<div class='card-body'>
				<h5 class='card-title'>Client Name: <?php echo "{$party['first_name']} {$party['last_name']}"; ?></h5>
				<p><strong>NIC:</strong> <?php echo "{$party['nic_no']}"; ?></p>
				<p><strong>Mobile:</strong> <?php echo "{$party['mobile_number']}"; ?></p>
				<p><strong>Email:</strong> <?php echo "{$party['email_address']}"; ?></p>
				<p><strong>DOB:</strong> <?php echo "{$party['date_of_birth']}"; ?></p>
				<p><strong>Joined:</strong> <?php echo "{$party['joined_date']}"; ?></p>
				<p><strong>Address:</strong> <?php echo "{$party['address']}"; ?></p>
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
		<?php
			foreach ($cases as $case) {
		?>
		<div class='card mb-3 shadow-sm'>
            <div class='card-body'>
				<h5 class='card-title'>Case: <?php echo "{$case['case_name']}";?></h5>
				<p><strong>Status:</strong> <?php echo "{$case['status']}"; ?></p>
				<p><strong>Next Date:</strong> <?php echo "{$case['next_date']}"; ?></p>
				<p><strong>For:</strong> <?php echo "{$case['for_what']}"; ?></p>
				<form method="GET" action="index.php" class="d-inline">
					<!-- <input type="hidden" name="pg" value="lawyer.php">
					<input type="hidden" name="option" value="full_view">
					<input type="hidden" name="id" value="<?php echo urlencode(sanitize_input($row['lawyer_id'])); ?>"> -->
					<button type="submit" class="btn btn-info btn-sm text-white">
					<i class="fas fa-eye"></i> Full View
					</button>
				</form>
			</div>
		</div>
		<?php
			}
		?>
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
				$next_lawyer_id = generateNextStaffID($conn); // Get the next ID *before* the form
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>ADD NEW STAFF</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm" name="staffForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="lawyer_id" class="form-label">Staff ID</label>
							<input hidden type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo sanitize_input($next_staff_id); ?>" readonly required>
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
								<label for="email" class="form-label">Email</label>
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
								<label for="court_id" class="form-label">Officer Classification</label>
								<select class="form-select" id="select_appointment" name="select_appointment" required>
									<option value="" disabled selected hidden>Select type of appointment</option>
									<option value="Judicial Staff (JSC)">Judicial Staff (JSC)</option>
									<option value="Ministry Staff">Ministry Staff</option>
									<option value="O.E.S/ Peon/ Security">O.E.S/ Peon/ Security</option>
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
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="court_id" class="form-label">Court Name</label>
								<select class="form-select" id="select_court_name" name="select_court_name" required>
									<option name=""disabled selected hidden>Select Court</option>
									<option name="Magistrate's Court" value="C01">Magistrate's Court</option>
									<option name="District Court" value="C02">District Court</option>
									<option name="High Court" value="C03">High Court</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="role-id" class="form-label">Role Name</label>
								<select class="form-select" id="select_role_name" name="select_role_name" required>
									<option name="" disabled selected hidden>Select Role</option>
									<option name="Administrator" value="R01">Administrator</option>
									<option name="Hon. Judge" value="R02">Hon. Judge</option>
									<option name="The Registrar" value="R03">The Registrar</option>
									<option name="Interpreter" value="R04">Interpreter</option>
									<option name="Other Staff" value="R05">Other Staff</option>
								</select>
							</div>
						</div>
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
			}elseif(isset($_GET['option']) && $_GET['option'] == "edit" && isset($_POST['lawyer_id'])) {
				$data = getStaffDataFromDatabase(sanitize_input($_POST['lawyer_id']), $conn);
			
				$txt_first_name = $data['first_name'];
				$txt_last_name = $data['last_name'];
				$int_mobile = $data['mobile'];
				$txt_nic_number = $data['nic_number'];
				$date_date_of_birth = $data['date_of_birth'];
				$txt_email = $data['email'];
				$txt_address = $data['address'];
				$select_court_name = $data['court_id'];
				$select_role_name = $data['role_id'];
				$txt_image_path = $data['image_path'];
				$select_gender = $data['gender'];
				$select_appointment = $data['appointment'];
			
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>EDIT STAFF</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="lawyer_id" class="form-label">Staff ID</label>
							<input hidden type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo sanitize_input($_POST['lawyer_id']); ?>" readonly required>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name" value="<?php echo $txt_first_name ?>" onkeypress="return isTextKey(event)" required>
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name"  value="<?php echo $txt_last_name ?>" onkeypress="return isTextKey(event)" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile" class="form-label">Mobile Number</label>
								<input type="text" name="int_mobile" id="int_mobile" class="form-control check-duplicate" data-check="mobile" data-feedback="mobileFeedback" value="<?php echo $int_mobile ?>" onkeypress="return isNumberKey(event)" onblur="validateMobileNumber('int_mobile')" required>
								<small id="mobileFeedback" class="text-danger"></small>
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control check-duplicate" id="txt_nic_number" name="txt_nic_number" data-check="nic" data-feedback="nicFeedback" value="<?php echo $txt_nic_number ?>" onblur="validateNIC('txt_nic_number')" required>
								<small id="nicFeedback" class="text-danger"></small>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" name="txt_email" id="txt_email" class="form-control check-duplicate" data-check="email" data-feedback="emailFeedback" value="<?php echo $txt_email ?>" onblur="validateEmail('txt_email')" required>
								<small id="emailFeedback" class="text-danger"></small>
							</div>
							<div class="col-md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address" value="<?php echo $txt_address ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth" value="<?php echo $date_date_of_birth ?>" required>
							</div>
							<div class="col-md-6">
								<label for="court_name" class="form-label">Court Name</label>
								<select class="form-select" id="select_court_name" name="select_court_name" required>
									<option name="" selected hidden value="<?php echo $select_court_name ?>" ><?php echo getCourtName($select_court_name) ?></option>
									<option name="Magistrate's Court" value="C01">Magistrate's Court</option>
									<option name="District Court" value="C02">District Court</option>
									<option name="High Court" value="C03">High Court</option>
								</select>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="court_name" class="form-label">Role Name</label>
								<select class="form-select" id="select_role_name" name="select_role_name" required>
									<option name="" selected hidden value="<?php echo $select_role_name ?>"><?php echo getRoleName($select_role_name) ?></option>
									<option name="Administrator" value="R01">Administrator</option>
									<option name="Hon. Judge" value="R02">Hon. Judge</option>
									<option name="The Registrar" value="R03">The Registrar</option>
									<option name="Interpreter" value="R04">Interpreter</option>
									<option name="Other Staff" value="R05">Other Staff</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="profile_photo" class="form-label">Upload Profile Photo</label>
								<input type="file" class="form-control" id="img_profile_photo" name="img_profile_photo" accept="image/*" required>
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
								<label for="court_id" class="form-label">Officer Classification</label>
								<select class="form-select" id="select_appointment" name="select_appointment" value="<?php echo $select_appointment ?>" required>
									<option value="" disabled hidden>Select type of appointment</option>
									<option value="Judicial Staff (JSC)">Judicial Staff (JSC)</option>
									<option value="Ministry Staff">Ministry Staff</option>
									<option value="O.E.S/ Peon/ Security">O.E.S/ Peon/ Security</option>
								</select>
							</div>
						</div>
						<div class="mb-3">
							<label hidden for="role_id" class="form-label">Role ID</label>
							<input hidden type="text" class="form-control" id="txt_role_id" name="txt_role_id">
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
	</body>
</html>