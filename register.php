<?php
	include_once('db.php');
	
	$type = $_GET['type'] ?? '';
	
	if ($type === 'lawyer') {
		$role = "LAWYER";
		// $role_id = "R06";
		// $station = "Private";
	
	
	
	} elseif ($type === 'police') {
		$role = "POLICE";
		// $role_id = "R07";
		// $station = "Magistrate's Court";
	
	
	
	} else {
		$role = "null";
		$role_id = "null";
		echo "<script> location.href='index.php'; </script>";
		exit;
	}
	
	
	function generateNextRegistrationID($conn) {
		// 1. Find the highest existing staff_id
		$sql = "SELECT MAX(reg_id) AS max_id FROM registration";
		$result = mysqli_query($conn, $sql);
	
		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$max_id = $row['max_id'];
	
			if ($max_id === null) {
				// No staff IDs exist yet, start with S0001
				return "R0001";
			} else {
				// Extract the numeric part, increment, and format
				$numeric_part = (int)substr($max_id, 1); // Remove "S", convert to integer
				$next_numeric_part = $numeric_part + 1;
				// Pad with leading zeros to 4 digits, then prepend "S"
				return "R" . str_pad($next_numeric_part, 4, "0", STR_PAD_LEFT);
			}
		} else {
			// Handle errors (e.g., table doesn't exist)
			return "R0001"; // Or throw an exception, log an error, etc.
		}
	}
	
	
	$next_reg_id = generateNextRegistrationID($conn);
	
	if(isset($_POST['btn_add'])){
	
		$txt_enrolment_number = '';
		$int_badge_number = '';
		$txt_reg_id = mysqli_real_escape_string($conn, $_POST["txt_reg_id"]);
		$txt_first_name = mysqli_real_escape_string($conn, $_POST["txt_first_name"]);
		$txt_last_name = mysqli_real_escape_string($conn, $_POST["txt_last_name"]);
		$int_mobile = mysqli_real_escape_string($conn, $_POST["int_mobile"]);
		$txt_email = mysqli_real_escape_string($conn, $_POST["txt_email"]);
		$txt_address = mysqli_real_escape_string($conn, $_POST["txt_address"]);
		$txt_nic_number = mysqli_real_escape_string($conn, $_POST["txt_nic_number"]);
		$txt_role_id = mysqli_real_escape_string($conn, $_POST["txt_role_id"]);
		if ($type === 'lawyer') {
			$txt_enrolment_number = isset($_POST["txt_enrolment_number"]) ? mysqli_real_escape_string($conn, $_POST["txt_enrolment_number"]) : '';
	
		} elseif ($type === 'police') {
			$int_badge_number = isset($_POST["int_badge_number"]) ? mysqli_real_escape_string($conn, $_POST["int_badge_number"]) : '';
		}
		
		$select_station = mysqli_real_escape_string($conn, $_POST["select_station"]);
		$date_joined_date = mysqli_real_escape_string($conn, $_POST["date_joined_date"]);
		$date_date_of_birth = mysqli_real_escape_string($conn, $_POST["date_date_of_birth"]);
		$status = "Pending";
		$txt_password = mysqli_real_escape_string($conn, $_POST["txt_password"]);
		$hashedPassword = password_hash($txt_password, PASSWORD_DEFAULT);
		
	
		require_once 'security.php';
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$upload_result = secure_image_upload('img_profile_photo');
		
			if (!$upload_result['success']) {
				die("Image upload failed: " . $upload_result['error']);
			}
		
			$txt_image_path = 'uploads/' . $upload_result['filename']; // Save in DB
			$txt_image_path = mysqli_real_escape_string($conn, $txt_image_path);
	
		}
		
		
		$sqlInsert = "INSERT INTO registration (reg_id, first_name, last_name, mobile, email, address, nic_number, enrolment_number, badge_number, station, joined_date, date_of_birth, status, role_id, password, image_path) VALUES (
			'$txt_reg_id',
			'$txt_first_name', 
			'$txt_last_name', 
			'$int_mobile', 
			'$txt_email',
			'$txt_address', 
			'$txt_nic_number', 
			'$txt_enrolment_number',
			'$int_badge_number',
			'$select_station',
			'$date_joined_date', 
			'$date_date_of_birth', 	
			'$status',
			'$txt_role_id',
			'$hashedPassword',
			'$txt_image_path'
		)";
		
		$resultInsert = mysqli_query($conn, $sqlInsert) or die("Error in sqlInsert: " . mysqli_error($conn));
		if ($resultInsert) {
			// echo '<script>alert("Successfully submitted your registration request. Wait for the Admin Approval");</script>';
		} else {
			echo '<script>alert("Error: " . mysqli_error($conn) . ".");</script>';
		}
	
			$sqlInsert = "INSERT INTO `courtsmanagement`.`login` (`username`, `password`, `otp`, `status`, `role_id`) VALUES ('$txt_email', '$hashedPassword', '1329', 'pending', '$txt_role_id');";
			$resultInsert = mysqli_query($conn, $sqlInsert) or die("Error in sqlInsert: " . mysqli_error($conn));
			if ($resultInsert) {
				echo '<script>alert("Successfully submitted your registration request. Wait for the Admin Approval");</script>';
				echo '<script>window.location.href = "index.php";</script>'; // redirect to avoid resubmission
	    	exit;
			} else {
				echo '<script>alert("Error: " . mysqli_error($conn) . ".");</script>';
			}
	}
	?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title>Kilinochchi Courts Management System</title>
		<meta name="description" content="Kilinochchi Courts Management System for efficient case management, scheduling, and document handling.">
		<meta name="keywords" content="Kilinochchi Courts, Case Management, Court System, Legal System">
		<link href="assets/img/favicon.png" rel="icon">
		<link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
		<link href="https://fonts.googleapis.com" rel="preconnect">
		<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
		<link href="assets/css/font.css" rel="stylesheet" >
		<link href="assets/css/main.css" rel="stylesheet">
		<link href="assets/css/ragu.css" rel="stylesheet">
		<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
		<link href="assets/vendor/aos/aos.css" rel="stylesheet">
		<link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
		<link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
		<link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
		<link href="assets/vendor/flatpickr/css/flatpickr.min.css" rel="stylesheet">
		<script src="assets/vendor/flatpickr/js/flatpickr.js"></script>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
	</head>
	<body>
		<div class="container-fluid bg-primary text-white text-center py-3">
			<h1>Registration</h1>
		</div>
		<div class="container mt-4">
			<div class="row">
				<div class="col-md-4">
					<div style="margin-top: 100px; text-align: center;">
						<img src="assets/img/auth/court.jpg" alt="Sri Lanka Courts" style="width: 110%; max-width: none; height: auto;">
					</div>
				</div>
				<div class="col-md-8 col-lg-8">
					<form action="register.php?type=<?php echo $type; ?>" method="POST" id="registrationForm" enctype="multipart/form-data">
						<div class="row mb-3">
							<label hidden for="reg_id" class="form-label">Registration ID</label>
							<input hidden type="text" class="form-control" id="txt_reg_id" name="txt_reg_id" value="<?php echo htmlspecialchars($next_reg_id); ?>" readonly>
						</div>
						<div class="mb-3">
							<?php if ($type === 'lawyer'){ ?>
							<input hidden type="text" class="form-control" id="txt_role_id" name="txt_role_id" value="R06">
							<?php }elseif ($type === 'police'){?>
							<input hidden type="text" class="form-control" id="txt_role_id" name="txt_role_id" value="R07">
							<?php }else{ ?>
							<input hidden type="text" class="form-control" id="txt_role_id" name="txt_role_id" value="R00">
							<?php } ?>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="first_name" class="form-label">First Name (Given Name)</label>
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name">
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name (Surname/Family Name)</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile_number" class="form-label">Mobile Number</label>
								<input type="number" class="form-control" id="int_mobile" name="int_mobile">
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
								<input type="text" class="form-control" id="txt_nic_number" name="txt_nic_number">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="email" class="form-label">Email/ Username</label>
								<input type="email" class="form-control" id="txt_email" name="txt_email">
							</div>
							<div class="col-md-6">
								<label for="court_name" class="form-label">Station</label>
								<?php if ($type === 'police'){?>
								<select class="form-select" id="select_station" name="select_station">
									<option value="" disabled selected hidden>Select Police Station</option>
									<option value="Kilinochchi HQ">Kilinochchi HQ</option>
									<option value="Palai">Palai</option>
									<option value="Poonakari">Poonakari</option>
									<option value="Tharmapuram">Tharmapuram</option>
									<option value="Jeyapuram">Jeyapuram</option>
									<option value="Akkarayan">Akkarayan</option>
									<option value="Kilinochchi HQ">...</option>
									<option value="Palai">...</option>
									<option value="Poonakari">...</option>
									<option value="D.C.D..B">D.C.D.B</option>
									<option value="C.I.D">C.I.D</option>
								</select>
								<?php }elseif ($type === 'lawyer'){ ?>
								<select class="form-select" id="select_station" name="select_station">
									<option value="" disabled selected hidden>Job Type</option>
									<option value="Legal Aid Commission">Legal Aid Commission</option>
									<option value="Private">Private</option>
								</select>
								<?php }else{ ?>
								<select class="form-select" id="select_court_name" name="select_court_name">
									<option value="">Select Court</option>
									<option value="Magistrate's Court">Magistrate's Court</option>
									<option value="District Court">District Court</option>
									<option value="High Court">High Court</option>
								</select>
								<?php } ?>
							</div>
							<div class="mb-3">
								<label for="password" class="form-label">Password</label>
								<div class="input-group">
									<input type="password" class="form-control" id="txt_password" name="txt_password" required>
									<button class="btn btn-outline-secondary" type="button" id="togglePassword">
									<i class="bi bi-eye-slash" id="password-icon"></i>
									</button>
								</div>
								<small class="text-muted">8 characters long, & contain at least one uppercase letter, one lowercase letter, one number, and one special character.</small>
							</div>
							<div class="mb-3">
								<label for="confirmPassword" class="form-label">Confirm Password</label>
								<input type="password" class="form-control" id="txt_confirm_password" name="txt_confirm_password" required>
								<div id="password-mismatch-error" class="invalid-feedback">
									Passwords do not match.
								</div>
							</div>
							<div class="md-6">
								<label for="address" class="form-label">Address</label>
								<input type="text" class="form-control" id="txt_address" name="txt_address">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<?php if ($type === 'police'){?>
								<div class="mb-3">
									<label for="badge_number" class="form-label">Police Badge Number</label>
									<input type="text" class="form-control" id="int_badge_number" name="int_badge_number" required>
								</div>
								<?php }elseif ($type === 'lawyer'){ ?>
								<div class="mb-3">
									<label for="lawyer_id" class="form-label">Lawyer Enrolment/Supreme Court Reg. Number</label>
									<input type="text" class="form-control" id="txt_enrolment_number" name="txt_enrolment_number" required>
								</div>
								<?php } ?>
							</div>
							<div class="col-md-6">
								<label for="profile_photo" class="form-label">Upload Profile Photo</label>
								<input type="file" class="form-control" id="img_profile_photo" name="img_profile_photo" accept="image/*" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth">					
							</div>
							<div class="col-md-6">
								<label for="join_date" class="form-label">Join Date</label>
								<input type="date" class="form-control" id="date_joined_date" name="date_joined_date">
							</div>
						</div>
						<div>
							<div class="mb-3 form-check">
								<input type="checkbox" class="form-check-input" id="acceptTerms" name="accept_terms" required>
								<label class="form-check-label" for="acceptTerms">I accept the <a href="terms.html" target="_blank">Terms and Conditions</a></label>
							</div>
							<button type="submit" class="btn btn-primary" id="btn_add" name="btn_add">Submit</button>
							<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
					</div>
				</div>
			</div>
		</div>
		<!-- MODEL : PASSWORD MISMATCH -->
		<div class="modal fade" id="passwordMismatchModal" tabindex="-1" aria-labelledby="passwordMismatchModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="passwordMismatchModalLabel">Password Mismatch</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						The passwords you entered do not match. Please try again.
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODEL : Terms $ Condition -->
		<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="termsModalLabel">Unified Courts Management System of Kilinochchi</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<h2>1. Acceptance of Terms</h2>
						<p>By checking the "I accept the Terms and Conditions" checkbox and accessing or using the System, you represent that you have read, understood, and agree to be legally bound by these Terms, and all applicable laws and regulations.  This constitutes a legally binding agreement between you and the operators of the Unified Courts Management System for Kilinochchi.</p>
						<h2>2. User Eligibility and Accounts</h2>
						<p><strong>2.1 Eligibility:</strong>  The System is intended for authorized users only, including Court staffs, Hon. Judges, Lawyers, and other designated personnel as determined by the Kilinochchi Courts administration.  Unauthorized access is strictly prohibited.</p>
						<p><strong>2.2 Account Creation:</strong> You may be required to create an account to access certain features of the System.  You are responsible for providing accurate and complete information during the registration process.  You agree to update your information promptly if it changes.</p>
						<p><strong>2.3 Account Security:</strong> You are solely responsible for maintaining the confidentiality of your account credentials (username and password).  You agree to notify the System administrator immediately of any unauthorized use of your account or any other breach of security.  You are liable for any losses or damages arising from your failure to protect your account credentials.</p>
						<h2>3. Acceptable Use</h2>
						<p><strong>3.1 Lawful Use:</strong> You agree to use the System only for lawful purposes and in accordance with these Terms.  You must comply with all applicable laws and regulations of Sri Lanka.</p>
						<p><strong>3.2 Prohibited Activities:</strong> You are strictly prohibited from:</p>
						<ul>
							<li>Attempting to gain unauthorized access to the System or any related systems or networks.</li>
							<li>Interfering with or disrupting the operation of the System.</li>
							<li>Uploading or transmitting any viruses, malware, or other harmful code.</li>
							<li>Using the System to transmit any unlawful, harassing, defamatory, abusive, threatening, harmful, vulgar, obscene, or otherwise objectionable material.</li>
							<li>Impersonating any person or entity, or falsely stating or otherwise misrepresenting your affiliation with a person or entity.</li>
							<li>Manipulating or falsifying data within the System.</li>
							<li>Accessing or attempting to access information that you are not authorized to view.</li>
							<li>Sharing your account credentials with any other person.</li>
							<li>Using the System for any commercial purpose without express written consent.</li>
							<li>Engaging in any activity that could damage, disable, overburden, or impair the System.</li>
						</ul>
						<h2>4. Data Privacy and Security</h2>
						<p><strong>4.1 Data Collection:</strong> The System collects and processes personal data in accordance with Privacy. By using the System, you consent to the collection and use of your data.</p>
						<p><strong>4.2 Data Security:</strong> We implement reasonable security measures to protect the confidentiality, integrity, and availability of data stored on the System. However, no data transmission over the internet or electronic storage method is 100% secure.  We cannot guarantee absolute security.</p>
						<p><strong>4.3 User Responsibility:</strong> You also responsible for securing your data. You should not share any confidentional document through this system.</p>
						<h2>5. Intellectual Property and License</h2>
						<p><strong>5.1 Ownership:</strong> The Unified Courts Management System for Kilinochchi, including all software, databases, user interfaces, and content, is the property of the Kilinochchi Courts and is protected by the intellectual property laws of Sri Lanka, including copyright and other applicable laws.</p>
						<p><strong>5.2 Limited License:</strong>  Subject to your compliance with these Terms, the Kilinochchi Courts grants you a limited, non-exclusive, non-transferable, revocable license to access and use the System solely for its intended purpose, which is to facilitate the management of court cases and related administrative tasks within the Magistrate's Court, District Court, and High Court of Kilinochchi.</p>
						<p><strong>5.3 Restrictions:</strong> You may not:</p>
						<ul>
							<li>Copy, modify, adapt, translate, reverse engineer, decompile, or disassemble any portion of the System.</li>
							<li>Create derivative works based on the System.</li>
							<li>Rent, lease, lend, sell, sublicense, distribute, or otherwise transfer any rights to the System.</li>
							<li>Remove, alter, or obscure any copyright, trademark, or other proprietary notices on the System.</li>
							<li>Use the system logo, name with out permission.</li>
						</ul>
						<p><strong>5.4 Developed By:</strong> This system was developed by Srirajeswaran Raguraj, Court Interpreter, Kilinochchi Courts in 2025</p>
						<h2>6. Disclaimer of Warranties</h2>
						<p>The System is provided "as is" and "as available" without any warranties of any kind, either express or implied, including, but not limited to, implied warranties of merchantability, fitness for a particular purpose, non-infringement, or course of performance.  We do not warrant that the System will be uninterrupted, error-free, secure, or free of viruses or other harmful components.</p>
						<h2>7. Limitation of Liability</h2>
						<p>In no event shall the Kilinochchi Courts, its affiliates, directors, officers, employees, agents, or licensors (including the developer, Srirajeswaran Raguraj) be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the System; (ii) any conduct or content of any third party on the System; (iii) any content obtained from the System; and (iv) unauthorized access, use, or alteration of your transmissions or content, whether based on warranty, contract, tort (including negligence), or any other legal theory, whether or not we have been informed of the possibility of such damage.</p>
						<h2>8. Termination</h2>
						<p>We may terminate or suspend your access to the System immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach these Terms.  Upon termination, your right to use the System will immediately cease.</p>
						<h2>9. Governing Law</h2>
						<p>These Terms shall be governed and construed in accordance with the laws of Sri Lanka, without regard to its conflict of law provisions.</p>
						<h2>10. Changes to Terms</h2>
						<p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time.  We will provide notice of any material changes by posting the new Terms on the System or by other reasonable means.  Your continued use of the System after any such changes constitutes your acceptance of the new Terms. It is your responsibility to review these Terms periodically.</p>
						<h2>11. Contact Us</h2>
						<p>If you have any questions about these Terms, please contact us at +94777958841  or sriraguraj@gmail.com</p>
						<p><strong>WARNING:</strong> Any unauthorized access, use, modification, or distribution of the System or its data is a serious offense and may result in criminal prosecution and civil penalties under applicable laws, including but not limited to the <strong>Computer Crimes Act, No. 24 of 2007 of Sri Lanka, the Intellectual Property Act, No. 36 of 2003 of Sri Lanka</strong>, and the <strong> Electronic Transactions Act, No. 19 of 2006 of Sri Lanka.</strong> You are solely responsible for your actions and any consequences thereof.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">I do agree</button>
					</div>
				</div>
			</div>
		</div>
		<script>
			// 1. JS Password Checker
				document.addEventListener('DOMContentLoaded', function() {
				document.getElementById('registrationForm').addEventListener('submit', function(event) {
				const password = document.getElementById('txt_password').value;
				const confirmPassword = document.getElementById('txt_confirm_password').value;
			
				if (password !== confirmPassword) {
					// Create a new Bootstrap Modal instance
					const myModal = new bootstrap.Modal(document.getElementById('passwordMismatchModal'));
					myModal.show();
			
					event.preventDefault();
				}
					});
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
			
		</script>
		<script>
			// 4. JS Password visibility toggle
			    const togglePasswordButton = document.getElementById('togglePassword');
			    const passwordInput = document.getElementById('txt_password');
			    const passwordIcon = document.getElementById('password-icon');
			
			    togglePasswordButton.addEventListener('click', function () {
			        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
			        passwordInput.setAttribute('type', type);
			
			        // Toggle eye icon
			        if (type === 'password') {
			            passwordIcon.classList.remove('bi-eye');
			            passwordIcon.classList.add('bi-eye-slash');
			        } else {
			            passwordIcon.classList.remove('bi-eye-slash');
			            passwordIcon.classList.add('bi-eye');
			        }
			    });
		</script>
		<script>
			//5. JS Clear the form
			document.getElementById('btn_clear').addEventListener('click', function() {
			    document.getElementById('registrationForm').reset();
			});
		</script>
		<script>
			//6. JS MODAL TRIGGER
			  	document.addEventListener('DOMContentLoaded', function() {
			      const acceptTermsCheckbox = document.getElementById('acceptTerms');
			      const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
			
			      acceptTermsCheckbox.addEventListener('change', function() {
			          if (this.checked) {
			              termsModal.show();
			          }
			      });
			
			       document.getElementById('registrationForm').addEventListener('submit', function(event) {
			              const acceptTermsCheckbox = document.getElementById('acceptTerms');
			               if (!acceptTermsCheckbox.checked) {
			                  alert('You must accept the Terms and Conditions to proceed.');
			                   event.preventDefault(); // Prevent form submission
			              }
			          });
			  });
			  
		</script>
	</body>
</html>