<?php
	// --- Function to Generate the Next Staff ID ---
	function generateNextStaffID($conn) {
		// 1. Find the highest existing staff_id
		$sql = "SELECT MAX(staff_id) AS max_id FROM staff";
		$result = mysqli_query($conn, $sql);
	
		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$max_id = $row['max_id'];
	
			if ($max_id === null) {
				// No staff IDs exist yet, start with S0001
				return "S0001";
			} else {
				// Extract the numeric part, increment, and format
				$numeric_part = (int)substr($max_id, 1); // Remove "S", convert to integer
				$next_numeric_part = $numeric_part + 1;
				// Pad with leading zeros to 4 digits, then prepend "S"
				return "S" . str_pad($next_numeric_part, 4, "0", STR_PAD_LEFT);
			}
		} else {
			// Handle errors (e.g., table doesn't exist)
			return "S0001"; // Or throw an exception, log an error, etc.
		}
	}
	
	// --- Helper Function to Get Staff Data ---
	function getStaffDataFromDatabase($staff_id, $conn) {
		$staff_id = mysqli_real_escape_string($conn, $staff_id);
		$sql = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
		$result = mysqli_query($conn, $sql);
	
		if ($result && mysqli_num_rows($result) > 0) {
			return mysqli_fetch_assoc($result);
		} else {
			return null;
		}
	}
	
	
	function getCourtName($court_id) {
		switch ($court_id) {
			case 'C01':
				return "Magistrate's Court";
			case 'C02':
				return "District Court";
			case 'C03':
				return "High Court";
			default:
				return "Unknown";
		}
	}
	
	function getRoleName($role_id) {
		switch ($role_id) {
			case 'R01':
				return "Administrator";
			case 'R02':
				return "Hon. Judge";
			case 'R03':
				return "The Registrar";
			case 'R04':
				return "Interpreter";
			case 'R05':
				return "Common Staff";
			case 'R06':
				return "Lawyer";
			case 'R07':
				return "Police";
			default:
				return "Unknown";
		}
	}
	
	if (isset($_POST["btn_add"])) {
		$txt_staff_id = mysqli_real_escape_string($conn, $_POST["txt_staff_id"]);
	    $txt_first_name = mysqli_real_escape_string($conn, $_POST["txt_first_name"]);
	    $txt_last_name = mysqli_real_escape_string($conn, $_POST["txt_last_name"]);
	    $int_mobile = mysqli_real_escape_string($conn, $_POST["int_mobile"]);
	    $txt_nic_number = mysqli_real_escape_string($conn, $_POST["txt_nic_number"]);
	    $date_date_of_birth = mysqli_real_escape_string($conn, $_POST["date_date_of_birth"]);
	    $txt_email = mysqli_real_escape_string($conn, $_POST["txt_email"]);
	    $txt_address = mysqli_real_escape_string($conn, $_POST["txt_address"]);
	    $select_court_name = mysqli_real_escape_string($conn, $_POST["select_court_name"]);
	    $date_joined_date = mysqli_real_escape_string($conn, $_POST["date_joined_date"]);
	    $select_role_name = mysqli_real_escape_string($conn, $_POST["select_role_name"]);
		$status = "active";

	
		require_once 'security.php';
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$upload_result = secure_image_upload('img_profile_photo');
		
			if (!$upload_result['success']) {
				die("Image upload failed: " . $upload_result['error']);
			}
		
			$txt_image_path = 'uploads/' . $upload_result['filename']; // Save in DB
			$txt_image_path = mysqli_real_escape_string($conn, $txt_image_path);
	
		}
	
	
		// Check whether this email is already in use or not
		$sqlEmail = "SELECT email FROM courtsmanagement.staff WHERE email='$txt_email';";
		$result = mysqli_query($conn, $sqlEmail);
	
		if ($result && mysqli_num_rows($result) >= 1) {
	
			echo '<script>alert("This email is already in use. Try different email");</script>';
			echo "<script> location.href='index.php?pg=staff.php&option=add'; </script>";
			exit;
			exit;
		} 
	
		// Writing new staff data into the staff table
	    $sqlInsert = "INSERT INTO staff (staff_id, first_name, last_name, mobile, nic_number, date_of_birth, email, address, court_id, joined_date, role_id, image_path) VALUES (
	        '$txt_staff_id',
			'$txt_first_name', 
	        '$txt_last_name', 
	        '$int_mobile', 
	        '$txt_nic_number', 
	        '$date_date_of_birth', 
	        '$txt_email', 
	        '$txt_address', 
	        '$select_court_name', 
	        '$date_joined_date', 
	        '$select_role_name',
			'$txt_image_path'
	    )";
	
	    $resultInsert = mysqli_query($conn, $sqlInsert) or die("Error in sqlInsert: " . mysqli_error($conn));
	
	    if ($resultInsert) {
	        echo '<script>alert("Successfully added staff member.");</script>';
	    } else {
	        echo '<script>alert("Error: " . mysqli_error($conn) . ".");</script>';
	    }
	
		// Writing staff credentials into the login table
		$hashedPassword = password_hash($txt_nic_number, PASSWORD_DEFAULT);
	
		$sqlInsert = "INSERT INTO `courtsmanagement`.`login` (`username`, `password`, `otp`, `status`, `role_id`) VALUES ('$txt_email', '$hashedPassword', '1329', 'active', '$select_role_name');";
		$resultInsert = mysqli_query($conn, $sqlInsert) or die("Error in sqlInsert: " . mysqli_error($conn));
		if ($resultInsert) {
			echo '<script>alert("Successfully added login credentials.");</script>';
			echo "<script> location.href='index.php?pg=staff.php&option=view'; </script>";
			exit;
		} else {
			echo '<script>alert("Error: " . mysqli_error($conn) . ".");</script>';
		}
	}
	
	
	if(isset($_POST["btn_update"])){
		$txt_staff_id = mysqli_real_escape_string($conn, $_POST["txt_staff_id"]);
		$txt_first_name = mysqli_real_escape_string($conn, $_POST["txt_first_name"]);
		$txt_last_name= mysqli_real_escape_string($conn, $_POST["txt_last_name"]);
		$int_mobile= mysqli_real_escape_string($conn, $_POST["int_mobile"]);
		$txt_nic_number= mysqli_real_escape_string($conn, $_POST["txt_nic_number"]);
		$date_date_of_birth= mysqli_real_escape_string($conn, $_POST["date_date_of_birth"]);
		$txt_email= mysqli_real_escape_string($conn, $_POST["txt_email"]);
		$txt_address= mysqli_real_escape_string($conn, $_POST["txt_address"]);
		$select_court_name= mysqli_real_escape_string($conn, $_POST["select_court_name"]);
		$date_joined_date= mysqli_real_escape_string($conn, $_POST["date_joined_date"]);
		$txt_role_id= mysqli_real_escape_string($conn, $_POST["txt_role_id"]);
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$upload_result = secure_image_upload('$txt_image_path'); 
			echo "<script>alert(1);</script>";
		
			if (!$upload_result['success']) {
				echo "<script>alert(2);</script>";
				die("Image upload failed: " . $upload_result['error']);
			}
		
			$txt_image_path = 'uploads/' . $upload_result['filename']; // Save in DB
			$txt_image_path = mysqli_real_escape_string($conn, $txt_image_path);
	
		}
	
		$sqlUpdate="UPDATE staff SET first_name = '$txt_first_name', last_name='$txt_last_name', mobile='$int_mobile', nic_number='$txt_nic_number', date_of_birth='$date_date_of_birth', email='$txt_email', address='$txt_address', court_name='$select_court_name', joined_date='$date_joined_date', role_id='$txt_role_id', image_path='$txt_image_path' WHERE staff_id='$txt_staff_id'";
						
		$resultUpdate=mysqli_query($conn,$sqlUpdate)or die("Error in sql_update".mysqli_error($con));
		if ($resultUpdate) {
			echo '<script>alert("Successfully updated staff member.");</script>';
			echo "<script> location.href='index.php?pg=staff.php&option=view'; </script>";
			exit;
		} else {
			echo '<script>alert("Error updating staff member: ' . mysqli_error($conn) . '");</script>';
		}
	}
	
	
	
	if(isset($_POST["btn_delete"])){
	
	// Retrieve email from staff table and set 'Deleted' attribute in login table
	$staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
	
	$sqlEmail = "SELECT email FROM courtsmanagement.staff WHERE staff_id='$staff_id';";
	$result = mysqli_query($conn, $sqlEmail);
	
	if (!$result) {
		echo '<script>alert("No email found for this staff ' . mysqli_error($conn) . '");</script>';
	}
	if ($result && mysqli_num_rows($result) >= 0) {
		$row = mysqli_fetch_assoc($result);
		$email = $row['email'];
	}
	
	$sqlUpdate="UPDATE `courtsmanagement`.`login` SET `status` = 'deleted' WHERE (`username` = '$email');";
						
	$resultUpdate=mysqli_query($conn,$sqlUpdate)or die("Error in sql_update".mysqli_error($con));
	if ($resultUpdate) {
		echo '<script>alert("Successfully set Delete attribute to staff member login table.");</script>';
		echo "<script> location.href='index.php?pg=staff.php&option=view'; </script>";
	} else {
		echo '<script>alert("Error deleting staff member: ' . mysqli_error($conn) . '");</script>';
	}
	
	// Finally Delete staff data from staff table.
	$sqlDelete="UPDATE staff SET is_active='0' WHERE staff_id='$staff_id'";
	$resultDelete = mysqli_query($conn, $sqlDelete);
	
	if ($resultDelete) {
		echo '<script>alert("Successfully Deleted staff member."); </script>';
		} else {
		echo '<script>alert("Error deleting staff member: ' . mysqli_error($conn) . '");</script>';
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
			
				$sql_read = "SELECT staff_id, first_name, last_name, nic_number, mobile, court_id, staff_id, email FROM staff WHERE is_active = 1";
				$result = mysqli_query($conn, $sql_read);
			
				if ($result && mysqli_num_rows($result) >= 0) {
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>STAFF LIST</h1>
		</div>
		<div class="container mt-4 text-center">
			<!-- <h2>Staff List</h2> -->
			<div class="table-responsivive">
				<div class="container mt-4 text-left">
					<a href="index.php?pg=staff.php&option=add" class="btn btn-success btn-sm me-1"><i class="fas fa-plus"></i> Add Staff</a>
				</div>
				<div class="container mt-5">
					<div class="row justify-content-center">
						<div class="col-lg-10">
							<table class="table table-responsive table-striped attractive-table">
								<thead>
									<tr>
										<th scope="col">No</th>
										<th scope="col">Full Name</th>
										<th scope="col">NIC</th>
										<th scope="col">Mobile</th>
										<th scope="col">Court Name</th>
										<th scope="col">Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$count = 1;
										while ($row = mysqli_fetch_assoc($result)) {
										
										?>
									<tr>
										<td scope="row">
											<?php echo("$count"); ?> 
											<p hidden class="text-muted mb-0"><?php echo $row['staff_id']; ?></p>
										</td>
										<td>
											<div class="d-flex align-items-center">
												<div class="ms-3">
													<p class="fw-bold mb-1"><?php echo $row['first_name']." ".$row['last_name']; ?></p>
													<p class="text-muted mb-0"><?php echo $row['email']; ?></p>
												</div>
											</div>
										</td>
										<td><?php echo $row['nic_number']; ?></td>
										<td><?php echo $row['mobile']; ?></td>
										<td><?php echo getCourtName($row['court_id']); ?></td>
										<td>
											<form class="d-inline" method="POST" action="http://localhost/ucms/index.php?pg=staff.php&option=edit">
												<input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
												<button class="btn btn-primary btn-sm" type="submit" id="btn_edit" name="btn_edit"><i class="fas fa-edit"></i> Edit</button>
											</form>
											<button class="btn btn-info btn-sm">
											<i class="fas fa-eye"></i> <a href="http://localhost/ucms/index.php?pg=staff.php&option=full_view&id=<?php echo htmlspecialchars($row['staff_id']); ?>"> Full View </a>
											</button>				
											<a>
												<form class="d-inline" method="POST" action="#">
													<input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
													<button class="btn btn-danger btn-sm" type="submit" id="btn_delete" name="btn_delete"><i class="fas fa-trash-alt" data-bs-target="#deleteConfirmModal"></i> Delete</button>
												</form>
										</td>
									</tr>
									<?php
										$count++;
										}
										?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- --- START : CAN BE REMOVED --- -->
		<?php
			}
			// <!-- FULL VIEW SECTION -->
			}elseif (isset($_GET['option']) && $_GET['option'] == "full_view" && $_GET['id']) {
					$staff = $_GET['id'];
			
					// $row = getStaffDataFromDatabase($staff, $conn);
					$row = getStaffDataFromDatabase(htmlspecialchars($staff), $conn);
				?>
		<!-- Bootstrap 5 Required CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
			src="<?php echo htmlspecialchars($row['image_path']); ?>" 
			alt="Profile Photo" 
			class="img-thumbnail shadow-sm border border-3 border-primary" 
			style="width: 300px; height: 300px; object-fit: cover;"
			>
		</div>
		<div class="col-md-9">

		<div class="card-footer text-end bg-light rounded-bottom-4">
		<div class="d-flex justify-content-between">
	<!-- Go previous button -->
	<?php
			// Remove the 'S' and convert to integer
			$number = (int) filter_var($row['staff_id'], FILTER_SANITIZE_NUMBER_INT);
			
			// Increment the number
			$next_number = $number - 1;
			
			// Pad with leading zeroes and add the prefix 'S'
			$previous_staff_id = 'S' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
			
			// Optional: Check if staff exists before rendering the button
			$next_exists = getStaffDataFromDatabase($previous_staff_id, $conn);
			
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
			$next_exists = getStaffDataFromDatabase($next_staff_id, $conn);
			
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
		<td><?php echo htmlspecialchars($row['first_name']); ?></td>
		</tr>
		<tr>
		<th scope="row">Last Name</th>
		<td><?php echo htmlspecialchars($row['last_name']); ?></td>
		</tr>
		<tr>
		<th scope="row">Mobile Number</th>
		<td><?php echo htmlspecialchars($row['mobile']); ?></td>
		</tr>
		<tr>
		<th scope="row">NIC Number</th>
		<td><?php echo htmlspecialchars($row['nic_number']); ?></td>
		</tr>
		<tr>
		<th scope="row">Date of Birth</th>
		<td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
		</tr>
		<tr>
		<th scope="row">Email</th>
		<td><?php echo htmlspecialchars($row['email']); ?></td>
		</tr>
		<tr>
		<th scope="row">Address</th>
		<td><?php echo htmlspecialchars($row['address']); ?></td>
		</tr>
		<tr>
		<th scope="row">Court ID</th>
		<td><?php echo getCourtName($row['court_id']); ?></td>
		</tr>
		<tr>
		<th scope="row">Joined Date</th>
		<td><?php echo htmlspecialchars($row['joined_date']); ?></td>
		</tr>
		<tr>
		<th scope="row">Status</th>
		<td>
		<?php if ($row['is_active']) { ?>
		<span class="badge bg-success">Active</span>
		<?php } else { ?>
		<span class="badge bg-danger">Inactive</span>
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
    <form method="POST" action="http://localhost/ucms/index.php?pg=staff.php&option=edit" class="d-inline">
      <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
      <button type="submit" class="btn btn-primary align-middle">
        <i class="fas fa-pen-to-square me-1"></i> Edit
      </button>
    </form>

    <!-- Delete Button within Full View-->
	<form method="POST" action="#" class="d-inline">
		<input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
		<button type="submit" class="btn btn-danger align-middle" name="btn_delete" id="btn_delete">
		<i class="fas fa-trash-alt me-1"></i> Delete
		</button>
	</form>

  </div>
</div>

		</div>
		</div>
		<!-- --- END : CAN BE REMOVED --- -->
		<?php
			// <!-- ADD SECTION -->
			}elseif (isset($_GET['option']) && $_GET['option'] == "add") {
				$next_staff_id = generateNextStaffID($conn); // Get the next ID *before* the form
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
							<input hidden type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo htmlspecialchars($next_staff_id); ?>" readonly required>
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
								<input type="number" min="0" class="form-control" id="int_mobile" name="int_mobile" onblur="phonenumber('int_mobile')" required>
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control" id="txt_nic_number" name="txt_nic_number" onblur="nicnumber('txt_nic_number')" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="txt_email" name="txt_email" onblur="emailvalidation('txt_email')" required>
							</div>
							<div class="col-md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
							<label for="court_id" class="form-label">Officer Classification</label>
								<select class="form-select" id="select_court_name" name="select_court_name" required>
									<option selected name="Court_staff" value="Court_staff">Judicial Staff</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="profile_photo" class="form-label">Upload Profile Photo</label>
								<input type="file" class="form-control" id="img_profile_photo" name="img_profile_photo" accept="image/*" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth" required>
							</div>
							<div class="col-md-6">
								<label for="joined_date" class="form-label">Joined Date</label>
								<input type="date" class="form-control" id="date_joined_date" max="<?php echo date('d-m-Y'); ?>" name="date_joined_date" value="<?php echo date('d-m-Y'); ?>" required>
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
									<option name="Lawyer" value="R06">Lawyer</option>
									<option name="Police" value="R07">Police</option>
								</select>
							</div>
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
			$data = getStaffDataFromDatabase(htmlspecialchars($_POST['staff_id']), $conn);
			// print_r($data);
			$txt_first_name = $data['first_name'];
			$txt_last_name = $data['last_name'];
			$int_mobile = $data['mobile'];
			$txt_nic_number = $data['nic_number'];
			$date_date_of_birth = $data['date_of_birth'];
			$txt_email = $data['email'];
			$txt_address = $data['address'];
			$select_court_name = $data['court_id'];
			$date_joined_date = $data['joined_date'];
			$txt_role_id = $data['role_id'];
			$txt_image_path = $data['image_path'];
		
		?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>EDIT STAFF</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm">
						<div class="row mb-3">
							<label hidden for="staff_id" class="form-label">Staff ID</label>
							<input hidden type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo htmlspecialchars($_POST['staff_id']); ?>" readonly required>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name" value="<?php echo $txt_first_name ?>" required>
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name"  value="<?php echo $txt_last_name ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile" class="form-label">Mobile Number</label>
								<input type="text" class="form-control" id="int_mobile" name="int_mobile"  value="<?php echo $int_mobile ?>" required>
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control" id="txt_nic_number" name="txt_nic_number"  value="<?php echo $txt_nic_number ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="txt_email" name="txt_email" value="<?php echo $txt_email ?>" required>
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
								<label for="joined_date" class="form-label">Join Date</label>
								<input type="date" class="form-control" id="date_joined_date" name="date_joined_date"  value="<?php echo $date_joined_date ?>" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="court_name" class="form-label">Court Name</label>
								<select class="form-select" id="select_court_name" name="select_court_name" required>
									<option value="" disabled selected hidden>Select Court</option>
									<option value="Magistrate's Court">Magistrate's Court</option>
									<option value="District Court">District Court</option>
									<option value="High Court">High Court</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="profile_photo" class="form-label">Upload Profile Photo</label>
								<input type="file" class="form-control" id="$txt_image_path" name="$txt_image_path" accept="image/*" required>
							</div>
						</div>
						<div class="mb-3">
							<label hidden for="role_id" class="form-label">Role ID</label>
							<input hidden type="text" class="form-control" id="txt_role_id" name="txt_role_id">
						</div>
						<button type="submit" class="btn btn-primary" id="btn_update" name="btn_update">Submit</button>
						<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
				</div>
			</div>
		</div>
		<?php
			// <!-- DELETE SECTION -->
			}elseif(isset($_GET['option']) && $_GET['option'] == "delete" && $_GET['staff_id']) {
			
				    // --- Delete Staff ---  CRITICAL:  This is the delete section
					$staff_id = mysqli_real_escape_string($conn, $_GET['staff_id']);
					// $sqlDelete = "DELETE FROM staff WHERE staff_id = '$staff_id'";
					$sqlDelete="UPDATE staff SET is_active='0' WHERE staff_id='$txt_staff_id'";
		
					$resultDelete = mysqli_query($conn, $sqlDelete);
				
					if ($resultDelete) {
						$action = 'view'; // Go back to view after delete.  VERY IMPORTANT
						echo '<script>alert("Staff member deleted successfully.");</script>';
						echo '<script>alert("Successfully added staff member."); window.location.href="index.php?pg=staff.php&option=view";</script>';
					} else {
						echo '<script>alert("Error deleting staff member: ' . mysqli_error($conn) . '");</script>';
					}
			
			}else{
				// header("Location: localhost/icms/index.php", true, 301);
				echo "<script> location.href='index.php'; </script>";
				// echo "<script> alert('Redirected called'); </script>";
				exit; 
			}
			?>
		<!-- Delete Confirmation Modal -->
		<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						Are you sure you want to delete this item?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">Delete</button>
					</div>
				</div>
			</div>
		</div>
		<script>
			// 1. JS clear the form button
			document.getElementById('btn_clear').addEventListener('click', function() {
			    document.getElementById('staffForm').reset();
			});
		</script>
		<script>
			//2. JS Set joined date to today
			const joinedDateField = document.getElementById('date_joined_date');
				const today = new Date();
				const formattedDate = today.toISOString().split('T')[0]; // Format as YYYY-MM-DD
				joinedDateField.value = formattedDate;
		</script>
		<script>
			//3. JS NIC to Date of Birth Calculation
			const nicInput = document.getElementById('txt_nic_number');
			const dobInput = document.getElementById('date_date_of_birth');
			
			nicInput.addEventListener('input', function() {
				const nic = nicInput.value.trim();
				let year = '';
				let dayOfYear = '';
			
				if (nic.length === 10 && nic.match(/^[0-9]{9}[VvXx]$/)) {
					// Old NIC format
					year = '19' + nic.substring(0, 2);
					dayOfYear = parseInt(nic.substring(2, 5));
				} else if (nic.length === 12 && nic.match(/^[0-9]{12}$/)) {
					// New NIC format
					year = nic.substring(0, 4);
					dayOfYear = parseInt(nic.substring(4, 7));
				} else {
					dobInput.value = ''; // Clear if invalid NIC
					flatpickr(dobInput).clear(); // Clear Flatpickr instance
					return;
				}
			
				// Check if female (over 500)
				if (dayOfYear > 500) {
					dayOfYear -= 500;
				}
			
				// **Corrected Date Calculation**
				let date = new Date(year, 0, dayOfYear-1); // Start from January 1st and adjust correctly
			
				// Format date as yyyy-mm-dd for Flatpickr
				let yyyy = date.getFullYear();
				let mm = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
				let dd = String(date.getDate()).padStart(2, '0');
				let dob = `${yyyy}-${mm}-${dd}`;
			
				dobInput.value = dob;
				flatpickr(dobInput).setDate(dob, true); // Use setDate and pass true for 'triggerChange'
			});
			
			// Initialize Flatpickr (outside the event listener, but after DOM is loaded)
			document.addEventListener('DOMContentLoaded', function() {
				flatpickr("#dob", {
					dateFormat: "Y-m-d",
				});
			});
			
			
			//4. JS to confirm before delete
			
			
				
			
			
			
			
			
		</script>
	</body>
</html>