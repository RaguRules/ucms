<?php
	if($systemUsertype == 'GUEST'){
	    echo "<script>location.href='index.php?pg=404.php';</script>";
	}
	
	$staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);
	$today = date('Y-m-d');

	if (isset($_POST['add_judgement'])) {
	   if ($systemUsertype != 'R01' && $systemUsertype != 'R02' && $systemUsertype != 'R03' && $systemUsertype != 'R04' && $systemUsertype != 'R05') {
	       die("Unauthorised access.");
	   }
	   $caseId = $_POST['case_id'];
	   $isContested = isset($_POST['is_contested']) ? 1 : 0;
	   $givenOn = $_POST['given_on'];
	
	   $judId = $helper->generateNextJudgementID();
	   $stmt = $conn->prepare("INSERT INTO judgements (jud_id, case_id, is_contested, given_on, staff_id) VALUES (?, ?, ?, ?, ?)");
	   $stmt->bind_param("ssiss", $judId, $caseId, $isContested, $givenOn, $staffId);
	   $stmt->execute();
	
	   $activityId = $helper->generateNextActivityID();
	   $summary = 'Judgement Delivered';
	   $currentStatus = 'Trial';
	   $nextStatus = 'Judgement';
	   $isTaken = 1;
	
	   $stmt2 = $conn->prepare("INSERT INTO dailycaseactivities (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
	   $stmt2->bind_param("ssssssisi", $activityId, $caseId, $summary, $givenOn, $currentStatus, $nextStatus, $isTaken, $givenOn, $staffId);
	   $stmt2->execute();
	
	   echo "<script>alert('Judgement added successfully.'); location.href='index.php?pg=judgement.php';</script>";
	   exit;
	}
	
	$monthFilter = isset($_GET['month']) ? $_GET['month'] : '';
	$searchCase = isset($_GET['search_case']) ? $conn->real_escape_string($_GET['search_case']) : '';
	
	$sqlFilter = "";
	if ($monthFilter !== '') {
	   $sqlFilter .= " AND MONTH(given_on) = " . intval($monthFilter);
	}
	if ($searchCase !== '') {
	   $sqlFilter .= " AND c.case_name LIKE '%" . $searchCase . "%'";
	}
	
	// Fetch case_ids that already have judgments
	$existingJudgements = [];
	$existingResult = $conn->query("SELECT case_id FROM judgements");
	while ($row = $existingResult->fetch_assoc()) {
	   $existingJudgements[] = $row['case_id'];
	}
	
	// Fetch eligible cases (exclude those already judged)
	$sqlEligible = "
	SELECT d.case_name, c.case_id, c.case_name AS display_name
	FROM dailycaseactivities d
	JOIN (
	   SELECT case_name, MAX(activity_date) AS latest
	   FROM dailycaseactivities
	   GROUP BY case_name
	) latest ON d.case_name = latest.case_name AND d.activity_date = latest.latest
	JOIN cases c ON d.case_name = c.case_id
	WHERE d.next_status IN ('Trial', 'Order', 'Pre Trial Conference', 'Calling', 'Judgement', 'Laid By', 'Inquiry')
	GROUP BY d.case_name
	";
	$eligibleCases = $conn->query($sqlEligible);
	
	// Fetch past judgements
	$sqlPast = "
	SELECT j.*, c.case_name AS case_display, c.plaintiff, c.defendant
	FROM judgements j
	JOIN cases c ON j.case_id = c.case_id
	WHERE 1=1 $sqlFilter
	ORDER BY j.given_on DESC
	";
	$pastJudgements = $conn->query($sqlPast);
	?>
<div class="container py-4">
	<?php
		if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04' || $systemUsertype == 'R05'){
		?>
	<h3 class="mb-4 text-primary">⚖️ Add New Judgement</h3>
	<form method="POST" class="row g-3">
		<div class="col-md-6">
			<label for="case_id" class="form-label">Select Case</label>
			<select name="case_id" class="form-select" required>
				<option value="">-- Choose a Case --</option>            
				<?php while ($r = $eligibleCases->fetch_assoc()): ?>
				<?php if (!in_array($r['case_id'], $existingJudgements)): ?>
				<option value="<?= Security::sanitize($r['case_id']) ?>"><?= Security::sanitize($r['display_name']) ?></option>
				<?php endif; ?>
				<?php endwhile; ?>
			</select>
		</div>
		<div class="col-md-4">
			<label for="given_on" class="form-label">Judgement Date</label>
			<input type="date" name="given_on" class="form-control" max="<?= date('Y-m-d') ?>" required>
		</div>
		<div class="col-md-2">
			<label class="form-label">Contested?</label><br>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="is_contested" id="contestedCheck">
				<label class="form-check-label" for="contestedCheck">Yes</label>
			</div>
		</div>
		<div class="col-12">
			<button type="submit" name="add_judgement" class="btn btn-success">Submit Judgement</button>
		</div>
	<hr class="my-5">
		</form>
		<form method="POST" action="lib/rep/report4.php" target="_blank" class="mb-3">
		<input type="hidden" name="month" value="<?= Security::sanitize($monthFilter) ?>">
		<input type="hidden" name="search_case" value="<?= Security::sanitize($searchCase) ?>">
		<button type="submit" name="print_judgement_report" class="btn btn-outline-dark">🖨️ Print A4 Report</button>
	</form>
	<?php 
		}
		?>
	<h4 class="text-secondary">🗂️ Past Judgements</h4>

	<form method="GET" class="row g-3 mb-3">
		<input type="hidden" name="pg" value="judgement.php">
		<div class="col-md-3">
			<label class="form-label">Filter by Month</label>
			<select name="month" class="form-select">
				<option value="">-- All Months --</option>
				<?php for ($m = 1; $m <= 12; $m++): ?>
				<option value="<?= $m ?>" <?= ($monthFilter == $m) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
				<?php endfor; ?>
			</select>
		</div>
		<div class="col-md-4">
			<label class="form-label">Search by Case Name</label>
			<input type="text" name="search_case" class="form-control" placeholder="Enter case name..." value="<?= Security::sanitize($searchCase) ?>">
		</div>
		<div class="col-md-2 d-flex align-items-end">
			<button type="submit" class="btn btn-outline-primary">Search</button>
		</div>
		<div class="col-md-2 d-flex align-items-end">
			<a href="index.php?pg=judgement.php" class="btn btn-outline-secondary">Clear</a>
		</div>
	</form>
	<div class="table-responsive">
		<table class="table table-bordered table-hover">
			<thead class="table-dark text-white">
				<tr>
					<th>Case Name</th>
					<th>Plaintiff</th>
					<th>Defendant</th>
					<th>Contested</th>
					<th>Judgement Date</th>
					<th>Entered By</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $pastJudgements->fetch_assoc()){
					$plaintiffData = $helper->getPartyData($row['plaintiff']);
					$plaintiff = $plaintiffData ? $plaintiffData['first_name'] . ' ' . $plaintiffData['last_name'] : 'Unknown';
					$defendantData = $helper->getPartyData($row['defendant']);
					$defendant = $defendantData ? $defendantData['first_name'] . ' ' . $defendantData['last_name'] : 'Unknown';
					$staffData = $helper->getStaffData($row['staff_id']);
					$staff = $staffData ? $staffData['first_name'] . ' ' . $staffData['last_name'] : 'Unknown';
					
					?>
				<tr>
					<td><?= Security::sanitize($row['case_display']) ?></td>
					<td><?= Security::sanitize($plaintiff) ?></td>
					<td><?= Security::sanitize($defendant) ?></td>
					<td><?= $row['is_contested'] ? 'Yes' : 'No' ?></td>
					<td><?= Security::sanitize($row['given_on']) ?></td>
					<td><?= Security::sanitize($staff) ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>