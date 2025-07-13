<?php
		// echo "<pre>";
		// print_r($_POST);
		// echo "</pre>";
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
	
	if (isset($_POST["btn_add"])) {
		$txt_staff_id = mysqli_real_escape_string($conn, $_POST["txt_staff_id"]);
	    $txt_first_name = mysqli_real_escape_string($conn, $_POST["txt_first_name"]);
	    $txt_last_name = mysqli_real_escape_string($conn, $_POST["txt_last_name"]);
	    $int_mobile_number = mysqli_real_escape_string($conn, $_POST["int_mobile_number"]);
	    $txt_nic_number = mysqli_real_escape_string($conn, $_POST["txt_nic_number"]);
	    $date_date_of_birth = mysqli_real_escape_string($conn, $_POST["date_date_of_birth"]);
	    $txt_email = mysqli_real_escape_string($conn, $_POST["txt_email"]);
	    $txt_address = mysqli_real_escape_string($conn, $_POST["txt_address"]);
	    $select_court_name = mysqli_real_escape_string($conn, $_POST["select_court_name"]);
	    $date_join_date = mysqli_real_escape_string($conn, $_POST["date_join_date"]);
	    $txt_role_id = mysqli_real_escape_string($conn, $_POST["txt_role_id"]);
	
	    $sqlInsert = "INSERT INTO staff (staff_id, first_name, last_name, mobile_number, nic_number, date_of_birth, email, address, court_name, join_date, role_id) VALUES (
	        '$txt_staff_id',
			'$txt_first_name', 
	        '$txt_last_name', 
	        '$int_mobile_number', 
	        '$txt_nic_number', 
	        '$date_date_of_birth', 
	        '$txt_email', 
	        '$txt_address', 
	        '$select_court_name', 
	        '$date_join_date', 
	        '$txt_role_id'
	    )";
	
	    $resultInsert = mysqli_query($conn, $sqlInsert) or die("Error in sqlInsert: " . mysqli_error($conn));
	
	    if ($resultInsert) {
	        echo '<script>alert("Successfully added staff member."); window.location.href="index.php?pg=staff.php&option=view";</script>';
	    } else {
	        echo '<script>alert("Error: " . mysqli_error($conn) . ".");</script>';
	    }
	}


if(isset($_POST["btn_update"])){
		$txt_staff_id = mysqli_real_escape_string($conn, $_POST["txt_staff_id"]);
		$txt_first_name = mysqli_real_escape_string($conn, $_POST["txt_first_name"]);
		$txt_last_name= mysqli_real_escape_string($conn, $_POST["txt_last_name"]);
		$int_mobile_number= mysqli_real_escape_string($conn, $_POST["int_mobile_number"]);
		$txt_nic_number= mysqli_real_escape_string($conn, $_POST["txt_nic_number"]);
		$date_date_of_birth= mysqli_real_escape_string($conn, $_POST["date_date_of_birth"]);
		$txt_email= mysqli_real_escape_string($conn, $_POST["txt_email"]);
		$txt_address= mysqli_real_escape_string($conn, $_POST["txt_address"]);
		$select_court_name= mysqli_real_escape_string($conn, $_POST["select_court_name"]);
		$date_join_date= mysqli_real_escape_string($conn, $_POST["date_join_date"]);
		$txt_role_id= mysqli_real_escape_string($conn, $_POST["txt_role_id"]);
	
		$sqlUpdate="UPDATE staff SET first_name = '$txt_first_name', last_name='$txt_last_name', mobile_number='$int_mobile_number', nic_number='$txt_nic_number', date_of_birth='$date_date_of_birth', email='$txt_email', address='$txt_address', court_name='$select_court_name', join_date='$date_join_date', role_id='$txt_role_id' WHERE staff_id='$txt_staff_id'";
						
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
	// }elseif(isset($_GET['option']) && $_GET['option'] == "delete" && $_GET['staff_id']) {

	// --- Delete Staff ---  CRITICAL:  This is the delete section
	$staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
	$sqlDelete = "DELETE FROM staff WHERE staff_id = '$staff_id'";
	$resultDelete = mysqli_query($conn, $sqlDelete);

	if ($resultDelete) {
		echo '<script>alert("Successfully Deleted staff member."); window.location.href="index.php?pg=staff.php&option=view";</script>';
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
	<body>
		<?php
			if(isset($_GET['option']) && $_GET['option'] == "view") {
			
				$sql_read = "SELECT staff_id, first_name, last_name, nic_number, mobile_number, court_name, staff_id, email FROM staff";
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
					<a href="http://localhost/ucms/index.php?pg=staff.php&option=add" class="btn btn-success btn-sm me-1"><i class="fas fa-plus"></i> Add Staff</a>
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
										<td scope="row"><?php echo("$count"); ?> 
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
										<td><?php echo $row['mobile_number']; ?></td>
										<td><?php echo $row['court_name']; ?></td>
										<td>
										<form class="d-inline" method="POST" action="http://localhost/ucms/index.php?pg=staff.php&option=edit">
											<input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
											<button class="btn btn-primary btn-sm" type="submit" id="btn_edit" name="btn_edit"><i class="fas fa-edit"></i> Edit</button>
										</form>
										<!-- <a href="http://localhost/ucms/index.php?pg=staff.php&option=edit&staff_id=<?php echo htmlspecialchars($row['staff_id']); ?>" class="btn btn-primary btn-sm">
										<i class="fas fa-edit"></i> Edit
										</a> -->
											<button class="btn btn-info btn-sm">
											<i class="fas fa-eye"></i> Full View
											</button>				
										<a>
										<form class="d-inline" method="POST" action="#">
											<input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
											<button class="btn btn-danger btn-sm" type="submit" id="btn_delete" name="btn_delete"><i class="fas fa-trash-alt"></i> Delete</button>
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
		<?php
			}
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
					<form action="#" method="POST" id="staffForm">
						<div class="row mb-3">
							<label for="staff_id" class="form-label">Staff ID</label>
							<input type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo htmlspecialchars($next_staff_id); ?>" readonly>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name">
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile_number" class="form-label">Mobile Number</label>
								<input type="number" class="form-control" id="int_mobile_number" name="int_mobile_number">
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control" id="txt_nic_number" name="txt_nic_number">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="txt_email" name="txt_email">
							</div>
							<div class="col-md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth">
							</div>
							<div class="col-md-6">
								<label for="join_date" class="form-label">Join Date</label>
								<input type="date" class="form-control" id="date_join_date" name="date_join_date">
							</div>
						</div>
						<div class="mb-3">
							<label for="court_name" class="form-label">Court Name</label>
							<select class="form-select" id="select_court_name" name="select_court_name">
								<option value="">Select Court</option>
								<option value="Magistrate's Court">Magistrate's Court</option>
								<option value="District Court">District Court</option>
								<option value="High Court">High Court</option>
							</select>
						</div>
						<div class="mb-3">
							<label hidden for="role_id" class="form-label">Role ID</label>
							<input hidden type="text" class="form-control" id="txt_role_id" name="txt_role_id">
						</div>
						<button type="submit" class="btn btn-primary" id="btn_add" name="btn_add">Submit</button>
						<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
				</div>
			</div>
		</div>
`			<?php
			// <!-- EDIT SECTION -->
		}elseif(isset($_GET['option']) && $_GET['option'] == "edit" && isset($_POST['staff_id'])) {
				$data = getStaffDataFromDatabase(htmlspecialchars($_POST['staff_id']), $conn);
				// print_r($data);
				$txt_first_name = $data['first_name'];
				$txt_last_name = $data['last_name'];
				$int_mobile_number = $data['mobile_number'];
				$txt_nic_number = $data['nic_number'];
				$date_date_of_birth = $data['date_of_birth'];
				$txt_email = $data['email'];
				$txt_address = $data['address'];
				$select_court_name = $data['court_name'];
				$date_join_date = $data['join_date'];
				$txt_role_id = $data['role_id'];			
		?>
		<div class="container-fluid bg-primary text-white text-center py-3">
		<h1>EDIT STAFF</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<form action="#" method="POST" id="staffForm">
						<div class="row mb-3">
							<label for="staff_id" class="form-label">Staff ID</label>
							<input type="text" class="form-control" id="txt_staff_id" name="txt_staff_id" value="<?php echo htmlspecialchars($_POST['staff_id']); ?>" readonly>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name" value="<?php echo $txt_first_name ?>">
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name"  value="<?php echo $txt_last_name ?>">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile_number" class="form-label">Mobile Number</label>
								<input type="text" class="form-control" id="int_mobile_number" name="int_mobile_number"  value="<?php echo $int_mobile_number ?>">
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control" id="txt_nic_number" name="txt_nic_number"  value="<?php echo $txt_nic_number ?>">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="txt_email" name="txt_email" value="<?php echo $txt_email ?>">
							</div>
							<div class="col-md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address" value="<?php echo $txt_address ?>">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth" value="<?php echo $date_date_of_birth ?>">
							</div>
							<div class="col-md-6">
								<label for="join_date" class="form-label">Join Date</label>
								<input type="date" class="form-control" id="date_join_date" name="date_join_date"  value="<?php echo $date_join_date ?>">
							</div>
						</div>
						<div class="mb-3">
							<label for="court_name" class="form-label">Court Name</label>
							<select class="form-select" id="select_court_name" name="select_court_name" >
								<option value="<?php echo $select_court_name ?>"><?php echo $select_court_name ?></option>
								<option value="Magistrate's Court">Magistrate's Court</option>
								<option value="District Court">District Court</option>
								<option value="High Court">High Court</option>
							</select>
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
					$sqlDelete = "DELETE FROM staff WHERE staff_id = '$staff_id'";
					$resultDelete = mysqli_query($conn, $sqlDelete);
				
					if ($resultDelete) {
						$action = 'view'; // Go back to view after delete.  VERY IMPORTANT
						echo '<script>alert("Staff member deleted successfully.");</script>';
						echo '<script>alert("Successfully added staff member."); window.location.href="index.php?pg=staff.php&option=view";</script>';
						// Consider redirecting here: header("Location: staff.php?option=view"); exit;
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
		<!-- --- JAVASCRIPT AREA--- -->
		<script>
			document.getElementById('btn_clear').addEventListener('click', function() {
			    document.getElementById('staffForm').reset();
			});
		</script>
	</body>
</html>
