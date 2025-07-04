<?php
if (!isset($_SESSION)){
	session_start();
}

include_once ('lib/db.php');
require_once ('lib/security.php');
require_once ('lib/helper.php');

$helper = new Helper($conn);
$security = new Security();
	
$type = $_GET['type'] ?? '';

if ($type === 'lawyer') {
	$role = "LAWYER";

} elseif ($type === 'police') {
	$role = "POLICE";

} else {
	$role = "null";
	$role_id = "null";
	echo "<script> location.href='index.php'; </script>";
	exit;
}



$next_reg_id = $helper->generateNextRegistrationID();

if (isset($_POST['btn_add'])) {
    // Sanitize input
    $txtRegId = Security::sanitize($_POST["txt_reg_id"]);
    $txtFirstName = Security::sanitize($_POST["txt_first_name"]);
    $txtLastName = Security::sanitize($_POST["txt_last_name"]);
    $intMobile = Security::sanitize($_POST["int_mobile"]);
    $txtEmail = Security::sanitize($_POST["txt_email"]);
    $txtAddress = Security::sanitize($_POST["txt_address"]);
    $txtNicNumber = Security::sanitize($_POST["txt_nic_number"]);
    $txtRoleId = Security::sanitize($_POST["txt_role_id"]);
    $userType = Security::sanitize($_POST["type"]); // Fixed undefined $type
    $txtEnrolmentNumber = '';
    $intBadgeNumber = '';

    if ($userType === 'lawyer') {
        $txtEnrolmentNumber = isset($_POST["txt_enrolment_number"]) ? Security::sanitize($_POST["txt_enrolment_number"]) : '';
    } elseif ($userType === 'police') {
        $intBadgeNumber = isset($_POST["int_badge_number"]) ? Security::sanitize($_POST["int_badge_number"]) : '';
    }

    $selectStation = Security::sanitize($_POST["select_station"]);
    $dateJoinedDate = Security::sanitize($_POST["date_joined_date"]);
    $dateDateOfBirth = Security::sanitize($_POST["date_date_of_birth"]);
    $status = "Pending";
    $selectGender = Security::sanitize($_POST["select_gender"]);
    $txtPassword = Security::sanitize($_POST["txt_password"]);
    $hashedPassword = password_hash($txtPassword, PASSWORD_DEFAULT);

    $txtImagePath = '';

    // Validate & upload image
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $uploadResult = $security->uploadImage('img_profile_photo');

        if (!$uploadResult['success']) {
            echo '<script>alert("Image upload failed: ' . $uploadResult['error'] . '");</script>';
            exit;
        }

        $txtImagePath = 'uploads/' . Security::sanitize($uploadResult['filename']);
    }

    // Begin DB transaction
    $conn->begin_transaction();

    try {
        // Insert into registration table
        $stmtRegister = $conn->prepare("INSERT INTO registration 
            (reg_id, first_name, last_name, mobile, email, address, nic_number, enrolment_number, badge_number, station, joined_date, date_of_birth, status, role_id, password, image_path, gender) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmtRegister->bind_param("sssssssssssssssss",
            $txtRegId,
            $txtFirstName,
            $txtLastName,
            $intMobile,
            $txtEmail,
            $txtAddress,
            $txtNicNumber,
            $txtEnrolmentNumber,
            $intBadgeNumber,
            $selectStation,
            $dateJoinedDate,
            $dateDateOfBirth,
            $status,
            $txtRoleId,
            $hashedPassword,
            $txtImagePath,
            $selectGender
        );

        if (!$stmtRegister->execute()) {
            throw new Exception("Registration insert failed: " . $stmtRegister->error);
        }

		$otp = strval(random_int(10000, 99999)); // 5-digit numeric OTP

        // Insert into login table
        $stmtLogin = $conn->prepare("INSERT INTO login (username, password, otp, status, role_id) VALUES (?, ?, ?, 'pending', ?)");
        $stmtLogin->bind_param("sssi", $txtEmail, $hashedPassword, $otp, $txtRoleId);

        if (!$stmtLogin->execute()) {
            throw new Exception("Login insert failed: " . $stmtLogin->error);
        }

        // Commit transaction if both succeed
        $conn->commit();



		// Assuming registration is successful and $newUserId contains the ID of the newly registered lawyer/police


// Determine user type and ID
$type = ($_POST['type'] === 'lawyer') ? 'lawyer' : 'police';
$recordId = $txtRegId;

$message = ucfirst($type) . " registration pending approval: ID " . $recordId;
$receiver = "admin"; // change based on your role setup

// Generate unique notification ID
$notifId = $helper->generateNextNotificationID();

$stmt = $conn->prepare("INSERT INTO notifications (notification_id, record_id, type, status, message, receiver_id) VALUES (?, ?, ?, 'unread', ?, ?)");
$stmt->bind_param("sssss", $notifId, $recordId, $type, $message, $receiver);
$stmt->execute();




        echo '<script>alert("Successfully submitted your registration request. Please wait for Admin approval.");</script>';
        echo '<script>window.location.href = "index.php";</script>';
        exit;

    } catch (Exception $e) {
        // Rollback transaction on failure
        $conn->rollback();

        // Log the error or alert user
        echo '<script>alert("Registration failed: ' . htmlspecialchars($e->getMessage()) . '");</script>';
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

		<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="assets/js/jsfunctions.js"></script>
		
		
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
					<form name="other_depts" action="register.php?type=<?php echo $type; ?>" method="POST" id="registrationForm" enctype="multipart/form-data">
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
								<input type="text" class="form-control" id="txt_first_name" name="txt_first_name" onkeypress="return isTextKey(event)" required>
							</div>
							<div class="col-md-6">
								<label for="last_name" class="form-label">Last Name (Surname/Family Name)</label>
								<input type="text" class="form-control" id="txt_last_name" name="txt_last_name" onkeypress="return isTextKey(event)" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="mobile_number" class="form-label">Mobile Number</label>
								<input type="text" name="int_mobile" id="int_mobile" class="form-control check-duplicate" data-check="mobile" data-feedback="mobileFeedback" onkeypress="return isNumberKey(event)" onblur="validateMobileNumber('int_mobile')" required>
								<small id="mobileFeedback" class="text-danger"></small>
							</div>
							<div class="col-md-6">
								<label for="nic_number" class="form-label">NIC Number</label>
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
									<option value="Maruthankeni">Maruthankeni</option>
									<option value="Ramanathapuram">Ramanathapuram</option>
									<option value="Mulankaavil">Mulankaavil</option>
									<option value="S.C.I.B">S.C.I.B</option>
									<option value="D.C.D.B">D.C.D.B</option>
									<option value="C.I.D">C.I.D</option>
								</select>
								<?php }elseif ($type === 'lawyer'){ ?>
								<select class="form-select" id="select_station" name="select_station">
									<option value="" disabled selected hidden>Job Type</option>
									<option value="Legal Aid Commission">Legal Aid Commission</option>
									<option value="Attorney General Department">Attorney General Department</option>
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
								<label for="date_of_birth" class="form-label">Date of Birth</label>
								<input type="date" class="form-control" id="date_date_of_birth" name="date_date_of_birth">					
							</div>
							<div class="col-md-6">
								<label for="profile_photo" class="form-label">Upload Profile Photo</label>
								<input type="file" class="form-control" id="img_profile_photo" name="img_profile_photo" accept="image/*" required>
							</div>
							<div>
								<label hidden for="join_date" class="form-label">Join Date</label>
								<input hidden type="date" class="form-control" id="date_joined_date" name="date_joined_date">
								<input hidden type="type" class="form-control" id="type" name="type" value="<?php echo $type; ?>">
							</div>
						</div>
						<div>
							<div class="mb-3 form-check">
								<input type="checkbox" class="form-check-input" id="acceptTerms" name="accept_terms" required>
								<label class="form-check-label" for="acceptTerms"> I accept the <a href="terms.html" target="_blank">Terms, & Conditions</a></label>
							</div>
							<button type="submit" class="btn btn-primary" id="btn_add" name="btn_add">Submit</button>
							<button type="button" class="btn btn-secondary" id="btn_clear" name="btn_clear">Clear Inputs</button>
					</form>
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
		<!-- Duplicates found Modal -->
		<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="actionModalLabel">Modal title</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body" id="actionModalBody">
						<!-- Message will appear here -->
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary" id="actionModalConfirmBtn">Confirm</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>