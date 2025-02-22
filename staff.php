<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Staff Page</title>
	</head>
	<body>
		<?php include_once('menu.php'); ?>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>STAFF</h1>
		</div>
		<div class="container mt-4">
			<div class="row justify-content-center">
				<div class="col-md-8 col-lg-6">
					<?php
						if (isset($_GET['option']) && $_GET['option'] == "add") {
					?>
					<form action="#" method="POST" id="staffForm">
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
								<input type="number" class="form-control" id="txt_mobile_number" name="txt_mobile_number">
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
							<label for="role_id" class="form-label">Role ID</label>
							<input type="text" class="form-control" id="txt_role_id" name="txt_role_id">
						</div>
						<button type="submit" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
					<?php
						}elseif(isset($_GET['option']) && $_GET['option'] == "edit") {
					?>
					<form action="#" method="POST" id="staffForm">
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
								<input type="number" class="form-control" id="txt_mobile_number" name="txt_mobile_number">
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
							<label for="role_id" class="form-label">Role ID</label>
							<input type="text" class="form-control" id="txt_role_id" name="txt_role_id">
						</div>
						<button type="submit" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
					<?php
						}elseif(isset($_GET['option']) && $_GET['option'] == "fullview") {

						}elseif(isset($_GET['option']) && $_GET['option'] == "delete") {
				
						}else{
							// header("Location: localhost/icms/index.php", true, 301);
							header("Location: index.php", true, 301);
							// echo "<script> location.href='index.php'; </script>";
							exit; 
						}
					?>
				</div>
			</div>
		</div>

		<!-- --- JAVASCRIPT AREA--- -->
		<script>
			document.getElementById('clearBtn').addEventListener('click', function() {
			    document.getElementById('staffForm').reset();
			});
		</script>
	</body>
</html>










<?php
function getStaffDataFromDatabase($staff_id) {
    $conn = new mysqli("localhost", "root", "password", "");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
    $conn->close();
}
?>