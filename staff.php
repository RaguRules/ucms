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

	

if (isset($_POST["btn_add"])) {
    // Sanitize inputs
    $txtStaffId = Security::sanitize($_POST["txt_staff_id"]);
    $txtFirstName = Security::sanitize($_POST["txt_first_name"]);
    $txtLastName = Security::sanitize($_POST["txt_last_name"]);
    $intMobile = Security::sanitize($_POST["int_mobile"]);
    $txtNicNumber = Security::sanitize($_POST["txt_nic_number"]);
    $dateDateOfBirth = Security::sanitize($_POST["date_date_of_birth"]);
    $txtEmail = Security::sanitize($_POST["txt_email"]);
    $txtAddress = Security::sanitize($_POST["txt_address"]);
    $selectCourtName = Security::sanitize($_POST["select_court_name"]);
    $dateJoinedDate = Security::sanitize($_POST["date_joined_date"]);
    $selectRoleName = Security::sanitize($_POST["select_role_name"]);
    $selectGender = Security::sanitize($_POST["select_gender"]);
    $selectAppointment = Security::sanitize($_POST["select_appointment"]);
    $status = "active";

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

	// Before Insert staff, check for duplicates in staff, lawyer, and police tables
	Security::checkDuplicate($conn, "nic_number", $txtNicNumber, "", "NIC Number already exists!", $txtStaffId);
	Security::checkDuplicate($conn, "mobile", $intMobile, "", "Mobile number already exists!", $txtStaffId);
	Security::checkDuplicate($conn, "email", $txtEmail, "", "Email already exists!", $txtStaffId);

    // Image upload
    $upload_result = $security->uploadImage('img_profile_photo');

    if (!$upload_result['success']) {
        die("Image upload failed: " . $upload_result['error']);
    }

    $txtImagePath = 'uploads/' . $upload_result['filename'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into staff table
        $stmtStaff = $conn->prepare("INSERT INTO staff (staff_id, first_name, last_name, mobile, nic_number, date_of_birth, email, address, court_id, joined_date, role_id, image_path, gender, appointment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
		$stmtStaff->bind_param(
            "ssssssssssssss",
            $txtStaffId,
			$txtFirstName,
			$txtLastName,
			$intMobile,
			$txtNicNumber,
			$dateDateOfBirth,
			$txtEmail,
			$txtAddress,
			$selectCourtName,
			$dateJoinedDate,
			$selectRoleName,
			$txtImagePath,
			$selectGender,
			$selectAppointment
        );

        $stmtStaff->execute();

        // Insert into login table
        $hashedPassword = password_hash($txtNicNumber, PASSWORD_DEFAULT);

        $stmtLogin = $conn->prepare("INSERT INTO login (username, password, otp, status, role_id) VALUES (?, ?, '000000', ?, ?)");
        $stmtLogin->bind_param("ssss", $txtEmail, $hashedPassword, $status, $selectRoleName);
        $stmtLogin->execute();

        //  Both successful
        $conn->commit();

		include_once 'lib/sms_beep.php';
        $message = "Dear {$txtFirstName} {$txtLastName}, your account has been created with Courts Complex-Kilinochchi successfully. Your login credentials are:\nUsername: {$txtEmail}\nPassword: {$txtNicNumber}";

        sendSms($intMobile, $message);

        echo '<script>alert("Successfully added staff member.");</script>';
        echo "<script>location.href='index.php?pg=staff.php&option=view';</script>";
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
    $txtStaffId = Security::sanitize($_POST["txt_staff_id"]);
    $txtFirstName = Security::sanitize($_POST["txt_first_name"]);
    $txtLastName = Security::sanitize($_POST["txt_last_name"]);
    $intMobile = Security::sanitize($_POST["int_mobile"]);
    $txtNicNumber = Security::sanitize($_POST["txt_nic_number"]);
    $dateDateOfBirth = Security::sanitize($_POST["date_date_of_birth"]);
    $txtEmail = Security::sanitize($_POST["txt_email"]);
    $txtAddress = Security::sanitize($_POST["txt_address"]);
    $selectCourtName = Security::sanitize($_POST["select_court_name"]);
    $selectGender = Security::sanitize($_POST["select_gender"]);
    $selectRoleName = Security::sanitize($_POST["select_role_name"]);
    $selectAppointment = Security::sanitize($_POST["select_appointment"]);

	$updateLoginStatus = null;

	// CSRF Protection
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

	// Before update staff, check for duplicates in staff, lawyer, and police tables
	Security::checkDuplicate($conn, "nic_number", $txtNicNumber, "", "NIC Number already exists!", $txtStaffId);
	Security::checkDuplicate($conn, "mobile", $intMobile, "", "Mobile number already exists!", $txtStaffId);
	Security::checkDuplicate($conn, "email", $txtEmail, "", "Email already exists!", $txtStaffId);


	

    // Start Transaction
    $conn->begin_transaction();

    try {
        $stmtUpdate = $conn->prepare("UPDATE staff SET first_name=?, last_name=?, mobile=?, nic_number=?, date_of_birth=?, email=?, address=?, court_id=?, role_id=?, gender=?, appointment=? WHERE staff_id=?");
        
		$stmtUpdate->bind_param(
            "ssssssssssss",
            $txtFirstName,
			$txtLastName,
			$intMobile,
			$txtNicNumber,
            $dateDateOfBirth,
			$txtEmail,
			$txtAddress,
			$selectCourtName,
            $selectRoleName,
			$selectGender,
			$selectAppointment,
			$txtStaffId
        );
        $stmtUpdate->execute();

		// 2. Update login table
		$updateLogin = $conn->prepare("UPDATE login SET role_id = ? WHERE username = ?");
		$updateLogin->bind_param("ss", $selectRoleName, $txtEmail);
		$updateLogin->execute();

		if ($updateLogin->affected_rows === 0) {
			throw new Exception("No login record updated. Username not found?");
		}

		$conn->commit();

		echo '<script>alert("Final: Successfully updated staff member.");</script>';
        exit;
    } catch (Exception $e) {
        $conn->rollback();

        Security::logError($e->getMessage());
        echo '<script>alert("An error occurred while updating. Please try again.");</script>';
    }
}


if (isset($_POST["btn_dpchange"])) {

	// $txtImagePath = null;
	$txtStaffId = Security::sanitize($_POST["txt_staff_id"]);
    
	// CSRF Protection
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token.");
    }

	// Upload image only if a new one was selected
    $newImageUploaded = ($_FILES['img_profile_photo']['error'] !== 4); // Error 4 = no file uploaded

    if ($newImageUploaded) {
        $uploadResult = $security->uploadImage('img_profile_photo');
        if (!$uploadResult['success']) {
            die("Image upload failed: " . $uploadResult['error']);
        }
        $txtImagePath = 'uploads/' . $uploadResult['filename'];
		echo "<script>alert('right way man: $txtImagePath');</script>";
    } else {
        // No new image, fetch old image path from database
        $stmtOld = $conn->prepare("SELECT image_path FROM staff WHERE staff_id = ?");
        $stmtOld->bind_param("s", $txtStaffId);
        $stmtOld->execute();
        $resultOld = $stmtOld->get_result();
        $rowOld = $resultOld->fetch_assoc();
        $txtImagePath = $rowOld['image_path']; // Use old image path
		echo "<script>alert('going to wrong block');</script>";
    } 

    // Start Transaction
    $conn->begin_transaction();

    try {
        $stmtUpdate = $conn->prepare("UPDATE staff SET image_path=? WHERE staff_id=?");
        
		$stmtUpdate->bind_param(
            "ss",
			$txtImagePath,
			$txtStaffId
        );
        $stmtUpdate->execute();

        $conn->commit();

        echo '<script>alert("Successfully changed profile picture.");</script>';
        echo "<script>location.href='index.php?pg=staff.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();

		// If new image was uploaded, delete it
        if ($newImageUploaded && file_exists($txtImagePath)) {
            unlink($txtImagePath);
        }

        Security::logError($e->getMessage());
        echo '<script>alert("An error occurred while updating. Please try again.");</script>';
    }
}


if (isset($_POST["btn_delete"])) {
    $txtStaffId = Security::sanitize($_POST['staff_id']);

    // Start Transaction
    $conn->begin_transaction();

    try {
        // Get email first
        $stmtSelect = $conn->prepare("SELECT email FROM staff WHERE staff_id=?");
        $stmtSelect->bind_param("s", $txtStaffId);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No email found for staff ID $txtStaffId.");
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
            WHERE staff_id=?
        ");
        $stmtStaffDelete->bind_param("s", $txtStaffId);
        $stmtStaffDelete->execute();

        $conn->commit();

        echo '<script>alert("Successfully deleted staff member.");</script>';
        echo "<script>location.href='index.php?pg=staff.php&option=view';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        Security::logError($e->getMessage());
        echo '<script>alert("An error occurred while deleting. Please try again.");</script>';
    }
}

if (isset($_POST["btn_reactivate"])) {
    $txtStaffId = Security::sanitize($_POST['staff_id']);

    // Start Transaction
    $conn->begin_transaction();

    try {
        // Get email first
        $stmtSelect = $conn->prepare("SELECT email FROM staff WHERE staff_id=?");
        $stmtSelect->bind_param("s", $txtStaffId);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No email found for staff ID $txtStaffId.");
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
            WHERE staff_id=?
        ");
        $stmtStaffReactivate->bind_param("s", $txtStaffId);
        $stmtStaffReactivate->execute();

        $conn->commit();

        echo '<script>alert("Successfully reactivated staff member.");</script>';
        echo "<script>location.href='index.php?pg=staff.php&option=view';</script>";
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
			
				// $sql_read = "SELECT staff_id, first_name, last_name, nic_number, mobile, court_id, staff_id, email FROM staff WHERE is_active = 1";
				
				$sql_read = "SELECT staff_id, first_name, last_name, nic_number, mobile, court_id, staff_id, email, is_active FROM staff";
				$result = $conn->query($sql_read);

			
				if ($result && $result->num_rows > 0) {
			?>
		<div class="container mt-4">
			<!-- For bigger list  <div class="container-fluid mt-4"> -->
			<div class="d-flex justify-content-start mb-3">
				<a href="index.php?pg=staff.php&option=add" class="btn btn-success btn-sm me-1">
				<i class="fas fa-plus"></i> Add Staff
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
							<th>Court Name</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$count = 1;
							while ($row = $result->fetch_assoc()) {
						?>
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
							<td><?php echo $helper->getCourtName($row['court_id']); ?></td>
							<td>
								<div class="d-flex flex-wrap gap-1">
									<form method="POST" action="index.php?pg=staff.php&option=edit" class="d-inline">
										<input type="hidden" name="staff_id" value="<?php echo Security::sanitize($row['staff_id']); ?>">
										<button class="btn btn-primary btn-sm" type="submit" name="btn_edit">
										<i class="fas fa-edit"></i> Edit
										</button>
									</form>
									<form method="GET" action="index.php" class="d-inline">
										<input type="hidden" name="pg" value="staff.php">
										<input type="hidden" name="option" value="full_view">
										<input type="hidden" name="id" value="<?php echo urlencode(Security::sanitize($row['staff_id'])); ?>">
										<button type="submit" class="btn btn-info btn-sm text-white">
										<i class="fas fa-eye"></i> Full View
										</button>
									</form>
									<!-- Delete Button or Reactive Button based on status -->
									<?php if ($row['is_active']) { ?>
										<!-- Delete Button for Active User -->
										<form method="POST" action="#" class="d-inline delete-form">
											<input type="hidden" name="staff_id" value="<?php echo Security::sanitize($row['staff_id']); ?>">
											<input type="hidden" name="btn_delete" value="1">
											<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
												<i class="fas fa-trash-alt"></i> Delete
											</button>
										</form>
									<?php } else { ?>
										<!-- Reactive Button for Deleted/Inactive User -->
										<form method="POST" action="#" class="d-inline reactive-form">
											<input type="hidden" name="staff_id" value="<?php echo Security::sanitize($row['staff_id']); ?>">
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
					$staffId = $_GET['id'];
			
					$row = $helper->getStaffData(Security::sanitize($staffId));
				?>
		<div class="container py-5">
			<div class="card shadow-lg rounded-4 border-0">
				<div class="card-header bg-dark text-white rounded-top-4 d-flex align-items-center justify-content-between">
					<h3 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Staff Profile</h3>
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
								<form method="POST" action="index.php?pg=staff.php&option=dpchange" enctype="multipart/form-data" class="mt-3">
									<div class="mb-2">
										<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
										<input hidden type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo Security::sanitize($staffId); ?>" readonly required>
										<input type="file" name="img_profile_photo" id="img_profile_photo" accept="image/*" class="form-control form-control-sm" required>
									</div>
									<button class="btn btn-secondary btn-sm" name="btn_dpchange" id="btn_dpchange" type="submit">
										<i class="fas fa-upload"></i> Upload New Photo
									</button>
								</form>
						</div>
						
						<div class="col-md-9">
							<div class="card-footer text-end bg-light rounded-bottom-4">
								<div class="d-flex justify-content-between">
									<?php
										// Remove the 'S' and convert to integer
										$number = (int) filter_var($row['staff_id'], FILTER_SANITIZE_NUMBER_INT);
										
										// Increment the number
										$next_number = $number - 1;
										
										// Pad with leading zeroes and add the prefix 'S'
										$previous_staff_id = 'S' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
										
										// Optional: Check if staff exists before rendering the button
										$next_exists = $helper->getStaffData($previous_staff_id);
										
										if ($next_exists){ ?>
									<a href="index.php?pg=staff.php&option=full_view&id=<?php echo $previous_staff_id; ?>" class="btn btn-outline-success">
									<i class="bi bi-arrow-left-circle"></i> Previous
									</a>
									<?php }else{ ?>
									<button class="btn btn-outline-success" disabled>
									<i class="bi bi-arrow-left-circle"></i> Previous
									</button>
									<?php }; ?>
									<!-- Exit and  go back to view dashboard button -->
									<a href="index.php?pg=staff.php&option=view" class="btn btn-danger">
									Exit <i class="bi bi-x-circle"></i>
									</a>
									<?php
										// Remove the 'S' and convert to integer
										$number = (int) filter_var($row['staff_id'], FILTER_SANITIZE_NUMBER_INT);
										
										// Increment the number
										$next_number = $number + 1;
										
										// Pad with leading zeroes and add the prefix 'S'
										$next_staff_id = 'S' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
										
										// Optional: Check if staff exists before rendering the button
										$next_exists = $helper->getStaffData($next_staff_id);
										
										if ($next_exists){ ?>
									<a href="index.php?pg=staff.php&option=full_view&id=<?php echo $next_staff_id; ?>" class="btn btn-outline-success">
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
											<th scope="row">Staff ID</th>
											<td><?php echo ltrim(substr($row['staff_id'], 1), '0'); ?></td>
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
											<th scope="row">Staff Type</th>
											<td><?php echo Security::sanitize($row['appointment']); ?></td>
										</tr>
										<th scope="row">Address</th>
										<td><?php echo Security::sanitize($row['address']); ?></td>
										</tr>
										<tr>
											<th scope="row">Court ID</th>
											<td><?php echo $helper->getCourtName($row['court_id']); ?></td>
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
						<form method="POST" action="http://localhost/ucms/index.php?pg=staff.php&option=edit" class="d-inline">
							<input type="hidden" name="staff_id" value="<?php echo Security::sanitize($row['staff_id']); ?>">
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
								<input type="hidden" name="staff_id" value="<?php echo Security::sanitize($row['staff_id']); ?>">
								<input type="hidden" name="btn_delete" value="1">
								<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
									<i class="fas fa-trash-alt"></i> Delete
								</button>
							</form>
						<?php } else { ?>
							<!-- Reactive Button for Deleted/Inactive User -->
							<form method="POST" action="#" class="d-inline reactive-form">
								<input type="hidden" name="staff_id" value="<?php echo Security::sanitize($row['staff_id']); ?>">
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
			// <!-- ADD SECTION -->
			}elseif (isset($_GET['option']) && $_GET['option'] == "add") {
				$next_staff_id = $helper->generateNextStaffID(); // Get the next ID *before* the form
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>ADD NEW STAFF</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm" name="staffForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="staff_id" class="form-label">Staff ID</label>
							<input hidden type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo Security::sanitize($next_staff_id); ?>" readonly required>
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
			}elseif(isset($_GET['option']) && $_GET['option'] == "edit" && isset($_POST['staff_id'])) {
				$data = $helper->getStaffData($_POST['staff_id']);

				$txtFirstName = $data['first_name'];
				$txtLastName = $data['last_name'];
				$intMobile = $data['mobile'];
				$txtNicNumber = $data['nic_number'];
				$dateDateOfBirth = $data['date_of_birth'];
				$txtEmail = $data['email'];
				$txtAddress = $data['address'];
				$selectCourtName = $data['court_id'];
				$selectRoleName = $data['role_id'];
				// $txtImagePath = $data['image_path'];
				$selectGender = $data['gender'];
				$selectAppointment = $data['appointment'];
			
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>EDIT STAFF</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="staff_id" class="form-label">Staff ID</label>
							<input hidden type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo Security::sanitize($_POST['staff_id']); ?>" readonly required>
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
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth" value="<?php echo $dateDateOfBirth ?>" required>
							</div>
							<div class="col-md-6">
								<label for="court_name" class="form-label">Court Name</label>
								<select class="form-select" id="select_court_name" name="select_court_name" required>
									<option name="" selected hidden value="<?php echo $selectCourtName ?>" ><?php echo $helper->getCourtName($selectCourtName) ?></option>
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
									<option name="" selected hidden value="<?php echo $selectRoleName ?>"><?php echo $helper->getRoleName($selectRoleName) ?></option>
									<option name="Administrator" value="R01">Administrator</option>
									<option name="Hon. Judge" value="R02">Hon. Judge</option>
									<option name="The Registrar" value="R03">The Registrar</option>
									<option name="Interpreter" value="R04">Interpreter</option>
									<option name="Other Staff" value="R05">Other Staff</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="court_id" class="form-label">Officer Classification2</label>
								<select class="form-select" id="select_appointment" name="select_appointment">
									<option value="<?php echo $selectAppointment ?>" disabled selected hidden><?php echo $selectAppointment ?></option>
									<option value="Judicial Staff (JSC)">Judicial Staff (JSC)</option>
									<option value="Ministry Staff">Ministry Staff</option>
									<option value="O.E.S/ Peon/ Security">O.E.S/ Peon/ Security</option>
								</select>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="court_id" class="form-label">Gender</label>
								<select class="form-select" id="select_gender" name="select_gender" value="<?php echo $selectGender ?>" required>
									<option value="" disabled hidden>Select Gender</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
									<option value="Other">Other</option>
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