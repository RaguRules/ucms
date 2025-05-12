<?php
	if(isset($_SESSION["LOGIN_USERTYPE"])){
	    $system_usertype = $_SESSION["LOGIN_USERTYPE"];
		$system_username = $_SESSION["LOGIN_USERNAME"];
	}else{
		$system_usertype = "GUEST";
	}
	if (empty($_SESSION['csrf_token'])) {
	    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	
	$helper = new Helper($conn);
	$security = new Security();
	
		
	
	if (isset($_POST["btn_add"])) {
	    // Sanitize inputs
	    $txt_party_id = Security::sanitize($_POST["txt_party_id"]);
	    $txt_first_name = Security::sanitize($_POST["txt_first_name"]);
	    $txt_last_name = Security::sanitize($_POST["txt_last_name"]);
	    $int_mobile = Security::sanitize($_POST["int_mobile"]);
	    $txt_nic_number = Security::sanitize($_POST["txt_nic_number"]);
	    $date_date_of_birth = Security::sanitize($_POST["date_date_of_birth"]);
	    $txt_email = Security::sanitize($_POST["txt_email"]);
	    $txt_address = Security::sanitize($_POST["txt_address"]);
	    $select_gender = Security::sanitize($_POST["select_gender"]);
	    $int_is_active = 1;
	    // $txt_added_by = Security::sanitize($_POST["txt_added_by"]);
	    // $txt_staff_id = Security::sanitize($_POST["txt_staff_id"]);
	    $txt_added_by = "R03";
	    $txt_staff_id = "S0001";
	
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
	
	    if (!$helper->validateDate($date_date_of_birth)) {
	        $errors[] = "Invalid date of birth.";
	    }
	
	    if (!$helper->validateDate($date_joined_date)) {
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
	
	
	    // Begin transaction
	    $conn->begin_transaction();
	
	    try {
	        // Insert into staff table
	        $stmtParty = $conn->prepare("INSERT INTO parties (party_id, first_name, last_name, mobile, nic_number, email, joined_date, address, date_of_birth, gender, is_active, added_by, staff_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	
			$stmtParty->bind_param(
	            "sssissdsssiss",
	            $txt_party_id,
				$txt_first_name,
				$txt_last_name,
				$int_mobile,
				$txt_nic_number,
	            $txt_email,
	            $date_joined_date,
	            $txt_address,
				$date_date_of_birth,
				$select_gender,
				$int_is_active,
	            $txt_added_by,
	            $txt_staff_id
	        );
	
	        $stmtParty->execute();
	
	        $conn->commit();
	
	        echo '<script>alert("Successfully added a Party.");</script>';
	        echo "<script>location.href='index.php?pg=party.php&option=view';</script>";
	        exit;
	
	    } catch (Exception $e) {
	        // Rollback on error
	        $conn->rollback();
	
	        echo '<script>alert("An error occurred while saving. Please try again.");</script>';
	    }
	}
	
	
	if (isset($_POST["btn_update"])) {
	    // Sanitize inputs
	    $txt_party_id = $_POST['txt_party_id'];  // Party ID from form (hidden field)
	    $txt_first_name = $_POST['txt_first_name'];
	    $txt_last_name = $_POST['txt_last_name'];
	    $int_mobile = $_POST['int_mobile'];
	    $txt_nic_number = $_POST['txt_nic_number'];
	    $txt_email = $_POST['txt_email'];
	    $txt_address = $_POST['txt_address'];
	    $date_date_of_birth = $_POST['date_date_of_birth'];
	    $select_gender = $_POST['select_gender'];
	    $int_is_active = 1;
	    // $added_by = $_SESSION["LOGIN_USERNAME"]; 
	    // $staff_id = $_SESSION["LOGIN_USERTYPE"]; 
	    $txt_added_by = "R03";
	    $txt_staff_id = "S0001";
	
	
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
	
	    if (!$helper->validateDate($date_date_of_birth)) {
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
	
		// Before update party, check for duplicates in staff, lawyer, and police tables
		// Security::checkDuplicate($conn, "nic_number", $txt_nic_number, "", "NIC Number already exists!", $txt_party_id);
		// Security::checkDuplicate($conn, "mobile", $int_mobile, "", "Mobile number already exists!", $txt_party_id);
		// Security::checkDuplicate($conn, "email", $txt_email, "", "Email already exists!", $txt_party_id);
		
	
	    // Start Transaction
	    $conn->begin_transaction();
	
	    try {
	        // Update query to edit existing record
	        $stmtUpdate = $conn->prepare("UPDATE parties SET first_name = ?, last_name = ?, mobile = ?, nic_number = ?, email = ?, address = ?, date_of_birth = ?, gender = ?, added_by = ?, staff_id = ? WHERE party_id = ?");
	
	        $stmtUpdate->bind_param(
	            "ssissssssss",
	            $txt_first_name,
	            $txt_last_name,
	            $int_mobile,
	            $txt_nic_number,
	            $txt_email,
	            $txt_address,
	            $date_date_of_birth,
	            $select_gender,
	            $txt_added_by,
	            $txt_staff_id,
	            $txt_party_id
	        );
	        $stmtUpdate->execute();
	
	        $conn->commit();
	
			$stmtUpdate->close();
	
	        echo '<script>alert("Successfully updated party details.");</script>';        
	        echo "<script>location.href='index.php?pg=parties.php&option=view';</script>";
	
	} catch (Exception $e) {
	    $conn->rollback();
	
	    Security::logError($e->getMessage());
	    echo "<div class='alert alert-danger'>Error updating party details: " . $conn->error . "</div>";
	    echo '<script>alert("An error occurred while updating. Please try again.");</script>';
	}
	}
	
	
	if (isset($_POST["btn_delete"])) {
	    $txt_party_id = Security::sanitize($_POST['party_id']);
	
	    // Start Transaction
	    $conn->begin_transaction();
	
	    try {
	        // Get email first
	        $stmtSelect = $conn->prepare("SELECT email FROM parties WHERE party_id=?");
	        $stmtSelect->bind_param("s", $txt_party_id);
	        $stmtSelect->execute();
	        $result = $stmtSelect->get_result();
	
	        if ($result->num_rows === 0) {
	            throw new Exception("No email found for party ID $txt_staff_id.");
	        }
	
	        $row = $result->fetch_assoc();
	        $email = $row['email'];
	
	        // Set staff is_active=0
	        $stmtPartyDelete = $conn->prepare("UPDATE parties SET is_active='0' WHERE party_id=?
	        ");
	        $stmtPartyDelete->bind_param("s", $txt_party_id);
	        $stmtPartyDelete->execute();
	
	        $conn->commit();
	
	        echo '<script>alert("Successfully deleted a party.");</script>';
	        echo "<script>location.href='index.php?pg=parties.php&option=view';</script>";
	        exit;
	    } catch (Exception $e) {
	        $conn->rollback();
	        Security::logError($e->getMessage());
	        echo '<script>alert("An error occurred while deleting. Please try again.");</script>';
	    }
	}
	
	if (isset($_POST["btn_reactivate"])) {
	    $txt_party_id = Security::sanitize($_POST['party_id']);
	
	    // Start Transaction
	    $conn->begin_transaction();
	
	    try {
	        // Get email first
	        $stmtSelect = $conn->prepare("SELECT email FROM parties WHERE party_id=?");
	        $stmtSelect->bind_param("s", $txt_party_id);
	        $stmtSelect->execute();
	        $result = $stmtSelect->get_result();
	
	        if ($result->num_rows === 0) {
	            throw new Exception("No email found for party.");
	        }
	
	        $row = $result->fetch_assoc();
	        $email = $row['email'];
	
	
	        // Set party is_active=1 (reactivate)
	        $stmtStaffReactivate = $conn->prepare("
	            UPDATE parties 
	            SET is_active='1' 
	            WHERE party_id=?
	        ");
	        $stmtStaffReactivate->bind_param("s", $txt_party_id);
	        $stmtStaffReactivate->execute();
	
	        $conn->commit();
	
	        echo '<script>alert("Successfully reactivated the party.");</script>';
	        echo "<script>location.href='index.php?pg=parties.php&option=view';</script>";
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
		<title>Registered Parties</title>
		<style>
			.table th, .table td {
			vertical-align: middle;
			text-align: center;
			}
			.table thead {
			background-color: #007bff;
			color: white;
			}
			.badge-active {
			background-color: #28a745;
			}
			.badge-inactive {
			background-color: #dc3545;
			}
			.btn-action {
			min-width: 100px;
			margin: 2px;
			}
		</style>
	</head>
	<body class="bg-light">
		<div class="container mt-5">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h3 class="fw-bold">Party Records</h3>
				<a href="index.php?pg=parties.php&option=add" class="btn btn-primary">
				<i class="fas fa-user-plus me-1"></i> Add Party
				</a>
			</div>
			<?php
				if (isset($_GET['option']) && $_GET['option'] == "view") {
				            ?>
			<div class="card p-3 shadow-sm">
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Full Name</th>
								<th>Contact Details</th>
								<th>Address</th>
								<th>Date of Birth</th>
								<th>Gender</th>
								<th>Status</th>
								<th>Added By/ Last Edited By</th>
								<th>Staff ID</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$sql = "SELECT party_id, first_name, last_name, mobile, nic_number, email, joined_date, address, date_of_birth, gender, is_active, added_by, staff_id FROM parties ORDER BY joined_date DESC";
								$result = $conn->query($sql);
								
								if ($result && $result->num_rows > 0):
								    while ($row = $result->fetch_assoc()):
								        $status = $row['is_active'] ? 'Active' : 'Inactive';
								        $statusClass = $row['is_active'] ? 'badge-active' : 'badge-inactive';
								        $toggleText = $row['is_active'] ? 'Delete' : 'Reactivate';
								        $toggleIcon = $row['is_active'] ? 'fa-trash' : 'fa-undo';
								        $toggleStatus = $row['is_active'] ? 0 : 1;
								?>
							<tr>
								<td><?= Security::sanitize($row['first_name'].$row['last_name']) ?><br><small class="text-muted"><?php echo $row['nic_number']; ?></small></td>
								<td><?= Security::sanitize($row['mobile']) ?> <br><small class="text-muted"><?php echo $row['email']; ?></small></td>
								<td><?= Security::sanitize($row['address']) ?></td>
								<td><?= Security::sanitize($row['date_of_birth']) ?></td>
								<td><?= Security::sanitize($row['gender']) ?></td>
								<td>
									<span class="badge <?= $statusClass ?>"><?= $status ?></span>
								</td>
								<td><?= Security::sanitize($helper->getRoleName($row['added_by'])) ?></td>
								<?php
									$staff = $helper->getStaffData($row['staff_id']);
									$staffFullName = $staff ? $staff['first_name'] . ' ' . $staff['last_name'] : 'Unknown';
									?>
								<td><?= Security::sanitize($staffFullName) ?></td>
								<td>
									<form method="POST" action="index.php?pg=parties.php&option=edit" class="d-inline">
										<input type="hidden" name="party_id" value="<?php echo Security::sanitize($row['party_id']); ?>">
										<button name="btn_edit" class="btn btn-primary btn-sm" type="submit">
										<i class="fas fa-edit"></i> Edit
										</button>
										<!-- Delete Button or Reactive Button based on status -->
										<?php if ($row['is_active']) { ?>
										<!-- Delete Button for Active a Party -->
									<form method="POST" action="#" class="d-inline delete-form">
										<input type="hidden" name="party_id" value="<?php echo Security::sanitize($row['party_id']); ?>">
										<input type="hidden" name="btn_delete" value="1">
										<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
										<i class="fas fa-trash-alt"></i> Delete
										</button>
									</form>
									<?php } else { ?>
									<!-- Reactive Button for Deleted/Inactive a Party -->
									<form method="POST" action="#" class="d-inline reactive-form">
										<input type="hidden" name="party_id" value="<?php echo Security::sanitize($row['party_id']); ?>">
										<input type="hidden" name="btn_reactivate" value="1">
										<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" onclick="reactivateConfirmModal(() => this.closest('form').submit())">
										<i class="fas fa-refresh"></i> Reactive
										</button>
									</form>
									<?php } ?>
								</td>
							</tr>
							<?php endwhile; else: ?>
							<tr>
								<td colspan="14" class="text-center text-muted">No party records found.</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
			// <!-- ADD SECTION -->
			}elseif (isset($_GET['option']) && $_GET['option'] == "add") {
				$nextPartyId = $helper->generateNextPartyID(); // Get the next ID *before* the form
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>ADD A PARTY</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm" name="staffForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="party_id" class="form-label">Party ID</label>
							<input hidden type="text" class="form-control" id="txt_party_id" name="txt_party_id" value="<?php echo Security::sanitize($nextPartyId); ?>" readonly required>
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
						<label hidden for="joined_date" class="form-label">Joined Date</label>
						<input hidden type="date" class="form-control" id="txt_added_by"name="txt_added_by" value="<?php echo $_SESSION["LOGIN_USERTYPE"]; ?>" required>
						<input hidden type="date" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo $_SESSION["LOGIN_USERNAME"]; ?>" required>
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
			// EDIT SECTION
			} elseif (isset($_GET['option']) && $_GET['option'] == "edit" && isset($_POST['party_id'])) {
			    $partyId = $_POST['party_id'];
			    $partyData = $helper->getPartyData($partyId); // Your existing function to get party info
			
			    if ($partyData) {
			?>
		<div class="container-fluid bg-warning text-dark text-center py-3">
			<h1>EDIT PARTY</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="editPartyForm" name="editPartyForm">
						<input type="hidden" name="txt_party_id" value="<?= Security::sanitize($partyId) ?>">
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name" value="<?= Security::sanitize($partyData['first_name']) ?>" required>
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name" value="<?= Security::sanitize($partyData['last_name']) ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile" class="form-label">Mobile Number</label>
								<input type="text" class="form-control" id="int_mobile" name="int_mobile" value="<?= '0'.Security::sanitize($partyData['mobile']) ?>" required>
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control" id="txt_nic_number" name="txt_nic_number" value="<?= Security::sanitize($partyData['nic_number']) ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="txt_email" name="txt_email" value="<?= Security::sanitize($partyData['email']) ?>" required>
							</div>
							<div class="col-md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address" value="<?= Security::sanitize($partyData['address']) ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth" value="<?= Security::sanitize($partyData['date_of_birth']) ?>" required>
							</div>
							<div class="col-md-6">
								<label for="gender" class="form-label">Gender</label>
								<select class="form-select" id="select_gender" name="select_gender" required>
									<option value="Male" <?= $partyData['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
									<option value="Female" <?= $partyData['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
									<option value="Other" <?= $partyData['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
								</select>
							</div>
						</div>
						<input type="hidden" name="txt_added_by" value="<?= $_SESSION['LOGIN_USERTYPE'] ?>">
						<input type="hidden" name="txt_staff_id" value="<?= $_SESSION['LOGIN_USERNAME'] ?>">
						<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
						<button type="submit" class="btn btn-primary" id="btn_update" name="btn_update">Update</button>
						<a href="index.php?pg=parties.php&option=view" class="btn btn-secondary">Cancel</a>
					</form>
				</div>
			</div>
		</div>
		<?php
			} else {
			    echo "<div class='alert alert-danger text-center'>Party not found.</div>";
			}
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
	</body>
</html>