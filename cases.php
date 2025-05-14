<?php

	$staffId = null;
	
	// Pagination Setup
	$limit = 1000;
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$start = ($page - 1) * $limit;
	
	// Get total case count
	$totalSql = "SELECT COUNT(*) AS total FROM cases";
	$totalResult = $conn->query($totalSql);
	$totalRow = $totalResult->fetch_assoc();
	$totalCases = $totalRow['total'];
	$totalPages = ceil($totalCases / $limit);
	
	// Fetch paginated cases
	$sql = "SELECT * FROM cases ORDER BY registered_date DESC LIMIT ?, ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ii", $start, $limit);
	$stmt->execute();
	$result = $stmt->get_result();
	
	//It comes from Add new case Model in view section
	if (isset($_POST["btn_add"])){
	
	    // CSRF token check
	    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	        die("CSRF token validation failed");
	    }
	
	    // Sanitize & collect form data
	    $txtCaseId = $helper->generateNextCaseID();
	    $txtCaseName = strtoupper(Security::sanitize($_POST['case_name']));
	    $dateRegisteredDate = Security::sanitize($_POST['registered_date']);
	    $txtPlaintiff = strtoupper(Security::sanitize($_POST['plaintiff']));
	    $txtDefendant = strtoupper(Security::sanitize($_POST['defendant']));
	    $txtPlaintiffLawyer = strtoupper(Security::sanitize($_POST['plaintiff_lawyer']));
	    $txtDefendantLawyer = strtoupper(Security::sanitize($_POST['defendant_lawyer']));
	    $selectNature = Security::sanitize($_POST['nature']);
	    $selectStatus = Security::sanitize($_POST['status']);
	    $selectIsWarrant = Security::sanitize($_POST['is_warrant']);
	    $dateNextDate = Security::sanitize($_POST['next_date']);
	    $txtForWhat = Security::sanitize($_POST['for_what']);
	    $txtStaffId = $helper->getId($systemUsername, $systemUsertype);
	    $txtCourtId = $_POST['court_id'];
	    $txtIsActive = 1;
	
	    // Prepare & execute INSERT query
	    $sql = "INSERT INTO cases 
	        (case_id, case_name, plaintiff, defendant, plaintiff_lawyer, defendant_lawyer, nature, status, is_warrant, registered_date, next_date, for_what, staff_id, court_id, is_active)
	        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	
	    $stmt = $conn->prepare($sql);
	    $stmt->bind_param(
	        "sssssssssssssss",
	        $txtCaseId,
	        $txtCaseName,
	        $txtPlaintiff,
	        $txtDefendant,
	        $txtPlaintiffLawyer,
	        $txtDefendantLawyer,
	        $selectNature,
	        $selectStatus,
	        $selectIsWarrant,
	        $dateRegisteredDate,
	        $dateNextDate,
	        $txtForWhat,
	        $txtStaffId,
	        $txtCourtId,
	        $txtIsActive
	    );
	
	    if ($stmt->execute()) {
	        echo "<script>location.href='index.php?pg=cases.php&option=view';</script>";
	        exit;
	    } else {
	        echo "<script>location.href='index.php?pg=cases.php&option=view';</script>";
	        exit;
	    }
	
	}
	
	
	//It comes from view section
	else if (isset($_POST["btn_full_view"], $_POST["case_id"])) {
	// Check for CSRF Tokens
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
			die("Invalid CSRF token.");
		}
	
	    $caseId = $_POST['case_id'];
	    $staffId = $helper->getId($systemUsername, $systemUsertype);
	
	    // Verify the lawyer is authorized to access this case
	    $query = "SELECT * FROM cases WHERE case_id = ? AND (plaintiff_lawyer = ? OR defendant_lawyer = ?)";
	    $stmt = $conn->prepare($query);
	    $stmt->bind_param("sss", $caseId, $staffId, $staffId);
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
	
	
	        // Get Plaintiff Lawyer Name
	        $plaintiffLawyerData = $helper->getLawyerData($case['plaintiff_lawyer']);
	        $plaintiffLawyer = $plaintiffLawyerData ? $plaintiffLawyerData['first_name'] . ' ' . $plaintiffLawyerData['last_name'] : 'Unknown';
	
	        // Get Defendant Lawyer Name
	        $defendantLawyerData = $helper->getLawyerData($case['defendant_lawyer']);
	        $defendantLawyer = $defendantLawyerData ? $defendantLawyerData['first_name'] . ' ' . $defendantLawyerData['last_name'] : 'Unknown';
	
	
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
                    <div class="modal-content" style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);">
                        <div class="modal-header border-0" style="background-color: #f1f5f9; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                            <h5 class="modal-title fw-bold text-primary" id="caseDetailModalLabel">Case Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 py-3">
                            <div class="row g-4">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="text-primary fw-bold mb-3"><?= Security::sanitize($caseName) ?></h4>
                                    <p><strong>Plaintiff Name:</strong> <?= Security::sanitize($plaintiff) ?></p>
                                    <p><strong>Defendant Name:</strong> <?= Security::sanitize($defendant) ?></p>
                                    <p><strong>Plaintiff Lawyer:</strong> <?= Security::sanitize($plaintiffLawyer) ?></p>
                                    <p><strong>Defendant Lawyer:</strong> <?= Security::sanitize($defendantLawyer) ?></p>
                                </div>
                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <p><strong>Nature of the Case:</strong> <?= Security::sanitize($nature) ?></p>
                                    <p>
                                        <strong>Warrant:</strong>
                                        <span class="badge <?= $isWarrant ? 'bg-danger' : 'bg-success' ?> px-3 py-1 rounded-pill">
                                        <?= $isWarrant ? 'Arrest Warrant Issued' : 'No Warrant' ?>
                                        </span>
                                    </p>
                                    <p><strong>Court Name:</strong> <?= Security::sanitize($courtId) ?></p>
                                    <p><strong>Case Registered Date:</strong> <?= Security::sanitize($registeredDate) ?></p>
                                </div>
                            </div>
                            <!-- Daily Activities Section -->
                            <hr class="my-4">
                            <div>
                                <h4 class="text-primary fw-bold mb-3">Quick Journal</h4>
                                <?php if ($activities_result->num_rows > 0): ?>
                                <?php while ($activity = $activities_result->fetch_assoc()): ?>
                                <div class="mb-4 p-3 shadow-sm rounded" style="background-color: #f9fafb; border-left: 4px solid #0d6efd;">
                                    <h6 class="fw-semibold text-primary mb-2"><?= Security::sanitize($activity['activity_date']) ?></h6>
                                    <p class="mb-1"><strong>Summary:</strong> <?= Security::sanitize($activity['summary']) ?></p>
                                    <p class="mb-1"><strong>Next Date:</strong> <?= Security::sanitize($activity['next_date']) ?></p>
                                    <p class="mb-0"><strong>Next Steps:</strong> <?= Security::sanitize($activity['next_status']) ?></p>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <p class="text-muted">No activities recorded for this case yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer border-0" style="background-color: #f1f5f9; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
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
        Security::logError("Unauthorized access attempt to case $caseId by $staffId on " . date("Y-m-d H:i:s"));
        echo "<script>alert('Access denied: You are not assigned to this case.');</script>";
		echo "<script>location.href='index.php?pg=cases.php&option=view';</script>";
        }
        
    $stmt->close();
    
	
	
	//It comes from Edit a case section
	}else if (isset($_POST["btn_update"], $_POST["case_id"])) {
	   // CSRF check
	   if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	       die("Invalid CSRF token.");
	   }
	
	   // Sanitize and fetch form data
	   $txtCaseId = $_POST['case_id'];
	   $txtCaseName = strtoupper(Security::sanitize($_POST['case_name']));
	   $dateRegisteredDate = Security::sanitize($_POST['registered_date']);
	   $txtPlaintiff = strtoupper(Security::sanitize($_POST['plaintiff']));
	   $txtDefendant = strtoupper(Security::sanitize($_POST['defendant']));
	   $txtPlaintiffLawyer = strtoupper(Security::sanitize($_POST['plaintiff_lawyer']));
	   $txtDefendantLawyer = strtoupper(Security::sanitize($_POST['defendant_lawyer']));
	   $selectNature = Security::sanitize($_POST['nature']);
	   $selectStatus = Security::sanitize($_POST['status']);
	   $selectIsWarrant = Security::sanitize($_POST['is_warrant']);
	   $dateNextDate = Security::sanitize($_POST['next_date']);
	   $txtForWhat = Security::sanitize($_POST['for_what']);
	   $txtCourtId = Security::sanitize($_POST['court_id']);
	   $txtStaffId = $helper->getId($systemUsername, $systemUsertype);
	   $txtIsActive = 1;
	
	   // Update query
	   $sql = "UPDATE cases SET 
	       case_name = ?, 
	       plaintiff = ?, 
	       defendant = ?, 
	       plaintiff_lawyer = ?, 
	       defendant_lawyer = ?, 
	       nature = ?, 
	       status = ?, 
	       is_warrant = ?, 
	       registered_date = ?, 
	       next_date = ?, 
	       for_what = ?, 
	       staff_id = ?, 
	       court_id = ?, 
	       is_active = ?
	       WHERE case_id = ?";
	
	   $stmt = $conn->prepare($sql);
	   $stmt->bind_param(
	       "sssssssssssssss",
	       $txtCaseName,
	       $txtPlaintiff,
	       $txtDefendant,
	       $txtPlaintiffLawyer,
	       $txtDefendantLawyer,
	       $selectNature,
	       $selectStatus,
	       $selectIsWarrant,
	       $dateRegisteredDate,
	       $dateNextDate,
	       $txtForWhat,
	       $txtStaffId,
	       $txtCourtId,
	       $txtIsActive,
	       $txtCaseId
	   );
	
	   if ($stmt->execute()) {
	       echo "<script>location.href='index.php?pg=cases.php&option=view&msg=updated';</script>";
	   } else {
	       echo "<script>alert('Failed to update case'); location.href='index.php?pg=cases.php&option=view';</script>";
	   }
	
	   $stmt->close();
	
	
    ?>
<!-- VIEW Section-->
<?php
	}else if(isset($_GET['option']) && $_GET['option'] == "view") {
	?>
<div class="container-fluid py-5">
	<h3 class="mb-4">Case Management</h3>
	<div class="table-responsive">
		<table id="casesTable" class="table table-striped table-bordered">
			<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCaseModal">
			+ Add New Case
			</button>
			<thead class="table-dark">
				<tr>
					<th>#</th>
					<th>Case Name</th>
					<th>Plaintiff</th>
					<th>Defendant</th>
					<th>Lawyers</th>
					<th>Registered</th>
					<th>Status</th>
					<th>Warrant</th>
					<th>Next Date</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $result->fetch_assoc()): ?>
				<tr>
					<td></td>
					<td><?= Security::sanitize($row['case_name']) ?></td>
					<td><?= Security::sanitize($row['plaintiff']) ?></td>
					<td><?= Security::sanitize($row['defendant']) ?></td>
					<td><?= Security::sanitize($row['plaintiff_lawyer']) ?> / <?= Security::sanitize($row['defendant_lawyer']) ?></td>
					<td><?= Security::sanitize($row['registered_date']) ?></td>
					<td><span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-danger' ?>"><?= $row['is_active'] ? 'Active' : 'Inactive' ?></span></td>
					<td><span class="badge <?= $row['is_warrant'] ? 'bg-danger' : 'bg-secondary' ?>"><?= $row['is_warrant'] ? 'Issued' : 'Not Issued' ?></span></td>
					<td><?= Security::sanitize($row['next_date']) ?></td>
					<td>
						<form method="POST" action="index.php?pg=cases.php&option=edit" class="d-inline">
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_id']) ?>">
							<button type="submit" name="btn_edit" class="btn btn-sm btn-warning">Edit</button>
						</form>
						<form method="POST" action="index.php?pg=cases.php&option=fullview" class="d-inline">
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_id']) ?>">
							<button type="submit" name="btn_full_view" class="btn btn-sm btn-secondary">FullView</button>
						</form>
						<?php if ($row['is_active']) { ?>
						<!-- Delete Button for Active User -->
						<form method="POST" action="index.php?pg=cases.php&option=delete" class="d-inline delete-form">
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_id']) ?>">
							<input type="hidden" name="btn_delete" value="1">
							<button type="button" class="btn btn-danger btn-sm" onclick="deleteConfirmModal(() => this.closest('form').submit())">
							 Delete
							</button>
						</form>
						<?php } else { ?>
						<!-- Reactive Button for Deleted/Inactive User -->
						<form method="POST" action="index.php?pg=cases.php&option=reactive" class="d-inline reactive-form">
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_id']) ?>">
							<input type="hidden" name="btn_reactivate" value="1">
							<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" onclick="reactivateConfirmModal(() => this.closest('form').submit())">
							 Reactivate
							</button>
						</form>
						<?php } ?>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>
	<!-- Pagination -->
<!-- Pagination -->
<nav aria-label="Page navigation">
	<ul class="pagination justify-content-center">
		<?php if ($page > 1): ?>
			<li class="page-item">
				<a class="page-link" href="index.php?pg=cases.php&option=view&page=<?= $page - 1 ?>">&laquo;</a>
			</li>
		<?php endif; ?>
		
		<?php for ($i = 1; $i <= $totalPages; $i++): ?>
			<li class="page-item <?= $i === $page ? 'active' : '' ?>">
				<a class="page-link" href="index.php?pg=cases.php&option=view&page=<?= $i ?>"><?= $i ?></a>
			</li>
		<?php endfor; ?>
		
		<?php if ($page < $totalPages): ?>
			<li class="page-item">
				<a class="page-link" href="index.php?pg=cases.php&option=view&page=<?= $page + 1 ?>">&raquo;</a>
			</li>
		<?php endif; ?>
	</ul>
</nav>

<!-- Add Case Modal -->
<div class="modal fade" id="addCaseModal" tabindex="-1" aria-labelledby="addCaseModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content border-0 shadow-lg rounded-4">
			<form method="POST" action="#" autocomplete="off">
				<div class="modal-header bg-primary text-white rounded-top-4">
					<h5 class="modal-title" id="addCaseModalLabel">Add New Case</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
					<?php
						$caseId = $helper->generateNextCaseID(); 
						$staffId = $helper->getId($systemUsertype, $systemUsername); 
						?>
					<input type="hidden" name="case_id" value="<?= $caseId ?>">
					<input type="hidden" name="staff_id" value="<?= $staffId ?>">
					<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
					<div class="row g-3">
						<div class="col-md-6">
							<label>Case Name</label>
							<input type="text" name="case_name" class="form-control" style="text-transform: uppercase;" required>
						</div>
						<div class="col-md-6">
							<label hidden>Registered Date</label>
							<input hidden type="date" name="registered_date" id="registered_date" class="form-control"
								max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
						</div>
						<div class="col-md-6">
							<label>Plaintiff/ Petitioner - if Criminal case enter "Police"</label>
							<input type="text" name="plaintiff" class="form-control" style="text-transform: uppercase;" required>
						</div>
						<div class="col-md-6">
							<label>Defendant/ Respondant/ Suspect/ Accused</label>
							<input type="text" name="defendant" class="form-control" style="text-transform: uppercase;" required>
						</div>
						<div class="col-md-6">
							<label>Plaintiff Lawyer, if available</label>
							<input type="text" name="plaintiff_lawyer" class="form-control" style="text-transform: uppercase;">
						</div>
						<div class="col-md-6">
							<label>Defendant Lawyer, if available</label>
							<input type="text" name="defendant_lawyer" class="form-control" style="text-transform: uppercase;">
						</div>
						<div class="col-md-6">
							<label>Nature</label>
							<select name="nature" class="form-select" required>
								<option value="">-- Select --</option>
								<option value="Criminal">Criminal</option>
								<option value="Civil">Civil</option>
							</select>
						</div>
						<div class="col-md-6">
							<label>Status</label>
							<select name="status" class="form-select" required>
								<option value="Calling">Calling</option>
								<option value="Pre Trial Conference">Pre Trial Conference</option>
								<option value="Trial">Trial</option>
								<option value="Inquiry">Inquiry</option>
								<option value="Order">Order</option>
								<option value="Judgement">Judgement</option>
								<option value="Post Judgement Calling">Post Judgement Calling</option>
								<option value="Laid By">Laid By</option>
								<option value="Appeal">Appeal</option>
							</select>
						</div>
						<div class="col-md-6">
							<label>Arrest Warrant Issued?</label>
							<select name="is_warrant" class="form-select" required>
								<option value="0">No</option>
								<option value="1">Yes</option>
							</select>
						</div>
						<div class="col-md-6">
							<label>Next Date</label>
							<input type="date" name="next_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
						</div>
						<div class="col-md-6">
							<label>For What</label>
							<select name="for_what" class="form-select" required>
								<option value="Calling">Calling</option>
								<option value="Pre Trial Conference">Pre Trial Conference</option>
								<option value="Trial">Trial</option>
								<option value="Inquiry">Inquiry</option>
								<option value="Order">Order</option>
								<option value="Judgement">Judgement</option>
								<option value="Post Judgement Calling">Post Judgement Calling</option>
								<option value="Laid By">Laid By</option>
								<option value="Appeal">Appeal</option>
							</select>
						</div>
						<div class="col-md-6">
							<label>Court</label>
							<select name="court_id" class="form-select" required>
								<option value="C04">Juvenile Magistrate's court</option>
								<option value="C01">Magistrate's court</option>
								<option value="C02">District Court</option>
								<option value="C03">High Court</option>
							</select>
						</div>
					</div>
				</div>
				<div class="modal-footer d-flex justify-content-between rounded-bottom-4 bg-light">
					<div>
						<button type="submit" name="btn_add" class="btn btn-success" onclick="this.closest('form').submit(); document.getElementById('addCaseModal').modal('hide');">Save and Exit</button>
					</div>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Edit Section -->
<?php
	} else if (isset($_GET['option']) && $_GET['option'] === "edit" && $_POST['case_id']) {
	   //TODO: Only Admin/ Staff can edit.
	
	   $staffId = $helper->getId($systemUsertype, $systemUsername); 
	   $row = $helper->getCaseData($_POST['case_id']);
	
	   if (!$row) {
	       echo "<p class='text-danger'>Case not found.</p>";
	       exit;
	   }
	?>
    <div class="container mt-5">
        <h2 class="mb-4 text-primary">Edit Case: <?= Security::sanitize($row['case_name']) ?></h2>
        <form method="POST" action="#" autocomplete="off">
            <input type="hidden" name="case_id" value="<?= $row['case_id'] ?>">
            <input type="hidden" name="staff_id" value="<?= $staffId ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Case Name</label>
                    <input type="text" name="case_name" class="form-control" required value="<?= Security::sanitize($row['case_name']) ?>" style="text-transform: uppercase;">
                </div>
                <div class="col-md-6">
                    <label hidden>Registered Date</label>
                    <input hidden type="date" name="registered_date" class="form-control" value="<?= Security::sanitize($row['registered_date']) ?>"  required>
                </div>
                <div class="col-md-6">
                    <label>Plaintiff/ enter 'Police' if prosecution</label>
                    <input type="text" name="plaintiff" class="form-control" required value="<?= Security::sanitize($row['plaintiff']) ?>" style="text-transform: uppercase;">
                </div>
                <div class="col-md-6">
                    <label>Defendant/ Respondant/ Accused</label>
                    <input type="text" name="defendant" class="form-control" required value="<?= Security::sanitize($row['defendant']) ?>" style="text-transform: uppercase;">
                </div>
                <div class="col-md-6">
                    <label>Plaintiff Lawyer</label>
                    <input type="text" name="plaintiff_lawyer" class="form-control" value="<?= Security::sanitize($row['plaintiff_lawyer']) ?>" style="text-transform: uppercase;">
                </div>
                <div class="col-md-6">
                    <label>Defendant Lawyer</label>
                    <input type="text" name="defendant_lawyer" class="form-control" value="<?= Security::sanitize($row['defendant_lawyer']) ?>" style="text-transform: uppercase;">
                </div>
                <div class="col-md-6">
                    <label>Nature</label>
                    <select name="nature" class="form-select" required>
                        <option disabled selected hidden value="<?php echo $row['nature'] ?>"><?php echo $row['nature'] ?></option>
                        <option value="Criminal" <?= $row['nature'] == 'Criminal' ? 'selected' : '' ?>>Criminal</option>
                        <option value="Civil" <?= $row['nature'] == 'Civil' ? 'selected' : '' ?>>Civil</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option disabled selected hidden value="<?php echo $row['status'] ?>"><?php echo $row['status'] ?></option>
                    <?php
                        $statuses = ["Calling", "Pre Trial Conference", "Trial", "Inquiry", "Order", "Judgement", "Post Judgement Calling", "Laid By", "Appeal"];
                        foreach ($statuses as $status) {
                            echo "<option value=\"$status\" " . ($row['status'] == $status ? 'selected' : '') . ">$status</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Arrest Warrant Issued?</label>
                    <select name="is_warrant" class="form-select" required>
                        <option disabled selected hidden value="<?php echo $row['is_warrant'] ?>"><?php echo $row['is_warrant'] ?></option>
                        <option value="0" <?= $row['is_warrant'] == 0 ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= $row['is_warrant'] == 1 ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Next Date</label>
                    <input type="date" name="next_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= Security::sanitize($row['next_date']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label>For What</label>
                    <select name="for_what" class="form-select" required>
                        <option disabled selected hidden value="<?php echo $row['for_what'] ?>"><?php echo $row['for_what'] ?></option>
                    <?php
                        foreach ($statuses as $status) {
                            echo "<option value=\"$status\" " . ($row['for_what'] == $status ? 'selected' : '') . ">$status</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Court</label>
                    <select name="court_id" class="form-select" required>
                        <option disabled selected hidden value="<?php echo $helper->getCourtName($row['court_id']) ?>"><?php echo $helper->getCourtName($row['court_id']) ?></option>
                        <option value="C04">Juvenile Magistrate's court</option>
                        <option value="C01">Magistrate's court</option>
                        <option value="C02">District Court</option>
                        <option value="C03">High Court</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" name="btn_update" class="btn btn-success">Save Changes</button>
                <a href="index.php?pg=cases.php&option=view" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php
	}else if (isset($_POST["btn_delete"])) {
	    // Ensure only Admin or Staff can delete
	    $staffId = $helper->getId($systemUsertype, $systemUsername);
	    $caseId = $_POST['case_id'];
	
	    $row = $helper->getCaseData($caseId);
	    if (!$row) {
	        echo "<p class='text-danger'>Case not found.</p>";
	        exit;
	    }
	
	    // Soft delete: set is_active = 0
	    $sql = "UPDATE cases SET is_active = 0 WHERE case_id = ?";
	    $stmt = $conn->prepare($sql);
	    $stmt->bind_param("s", $caseId);
	
	    if ($stmt->execute()) {
	        echo "<script>alert('Case deleted successfully.'); location.href='index.php?pg=cases.php&option=view';</script>";
	    } else {
	        echo "<script>alert('Failed to delete the case.'); location.href='index.php?pg=cases.php&option=view';</script>";
	    }
	
	    $stmt->close();
	
	
	
	}else if (isset($_POST["btn_reactivate"])) {
	    // Ensure only Admin or Staff can reactivate
	    $staffId = $helper->getId($systemUsertype, $systemUsername);
	    $caseId = $_POST['case_id'];
	
	    $row = $helper->getCaseData($caseId);
	    if (!$row) {
	        echo "<p class='text-danger'>Case not found.</p>";
	        exit;
	    }
	
	    // Soft delete: set is_active = 0
	    $sql = "UPDATE cases SET is_active = 1 WHERE case_id = ?";
	    $stmt = $conn->prepare($sql);
	    $stmt->bind_param("s", $caseId);
	
	    if ($stmt->execute()) {
	        echo "<script>alert('Case reactivated successfully.'); location.href='index.php?pg=cases.php&option=view';</script>";
	    } else {
	        echo "<script>alert('Failed to reactivate the case.'); location.href='index.php?pg=cases.php&option=view';</script>";
	    }
	
	    $stmt->close();
	}else{
	    echo "<script> location.href='index.php'; </script>";
	    // exit; 
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

<!-- DataTable Setup -->
<script>
	$(document).ready(function () {
	  const table = $('#casesTable').DataTable({
	    responsive: true,
	    pageLength: 1000,
	    lengthMenu: [5, 10, 25, 50, 100, 500, 1000],
	    order: [[5, 'desc']],
	    columnDefs: [
	      { orderable: false, targets: [-1, 0] },
	      { searchable: false, targets: 0 }
	    ]
	  });
	  table.on('order.dt search.dt draw.dt', function () {
	    table.column(0, { search: 'applied', order: 'applied', page: 'current' })
	      .nodes().each((cell, i) => cell.innerHTML = i + 1);
	  }).draw();
	});
</script>