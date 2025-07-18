<?php
	if($systemUsertype == 'GUEST'){
		echo "<script>location.href='index.php?pg=404.php';</script>";
	}
	
	if (isset($_POST['toggle_warrant']) && isset($_POST['case_id'])) {

		if ($systemUsertype != 'R01' && $systemUsertype != 'R02' && $systemUsertype != 'R03' && $systemUsertype != 'R04' && $systemUsertype != 'R06') {
			die("Unauthorised access.");
		}
	
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
			die("Invalid CSRF token");
		}
	
	    $caseId = $_POST['case_id'];
	    $current = (int) $_POST['current_value'];
	    $newValue = $current ? 0 : 1;
	
	    if ($newValue == 1) {
	        $warrantId = $helper->generateNextWarrantID($conn);
	        $issuedBy = $helper->getId($_SESSION["LOGIN_USERNAME"], $_SESSION["LOGIN_USERTYPE"]);
	        $issueDate = date('Y-m-d');
	        $warrantType = 'arrest';
	        $status = 'active';
	
	        $stmt = $conn->prepare("INSERT INTO warrants (warrant_id, case_id, issued_for_party_id, issued_by_staff_id, issue_date, warrant_type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
	        $stmt->bind_param("sssssss", $warrantId, $caseId, $caseId, $issuedBy, $issueDate, $warrantType, $status);
	        $stmt->execute();
	
	        $stmt = $conn->prepare("UPDATE cases SET is_warrant = ? WHERE case_id = ?");
	        $stmt->bind_param("is", $newValue, $caseId);
	        $stmt->execute();
	    } else {
	        $stmt = $conn->prepare("DELETE FROM warrants WHERE case_id = ?");
	        $stmt->bind_param("s", $caseId);
	        $stmt->execute();
	
	        $stmt = $conn->prepare("UPDATE cases SET is_warrant = ? WHERE case_id = ?");
	        $stmt->bind_param("is", $newValue, $caseId);
	        $stmt->execute();
	    }
	
	    // Redirect after the operation
	    echo "<script>location.href='index.php?pg=warrant.php&option=view';</script>";
	    exit;
	}
	
	// Search logic
	$searchTerm = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : null;
	// Modify the query to use the warrants table to check if a warrant exists
	$stmt = $conn->prepare("SELECT c.*, w.warrant_id FROM cases c LEFT JOIN warrants w ON c.case_id = w.case_id WHERE c.case_name LIKE ? OR w.case_id IS NOT NULL");
	$stmt->bind_param("s", $searchTerm);
	$stmt->execute();
	$result = $stmt->get_result();
	
	?>
<div class="container py-4">
	<h3 class="mb-4 text-primary">🚨 Cases with Arrest Warrants</h3>
	<form method="GET" action="index.php" class="mb-3 row g-2">
		<input type="hidden" name="pg" value="warrant.php">
		<input type="hidden" name="option" value="view">
		<div class="col-md-6">
			<input type="text" name="search" class="form-control" placeholder="Search by Case Name" value="<?= Security::sanitize($_GET['search'] ?? '') ?>">
		</div>
		<div class="col-auto">
			<button type="submit" class="btn btn-outline-primary">Search</button>
			<a href="index.php?pg=warrant.php&option=view" class="btn btn-outline-secondary">Clear</a>
		</div>
	</form>
	<div class="table-responsive">
		<table class="table table-bordered table-hover">
			<thead class="table-danger">
				<tr>
					<th>Case Name</th>
					<th>Plaintiff</th>
					<th>Defendant</th>
					<th>Status</th>
					<th>Warrant?</th>
					<th>Next Hearing Date</th>
					<?php if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04'): ?>
					<th>Action</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $result->fetch_assoc()): 
					$plaintiffData = $helper->getPartyData($row['plaintiff']);
					$plaintiff = $plaintiffData ? $plaintiffData['first_name'] . ' ' . $plaintiffData['last_name'] : 'Unknown';
					$defendantData = $helper->getPartyData($row['defendant']);
					$defendant = $defendantData ? $defendantData['first_name'] . ' ' . $defendantData['last_name'] : 'Unknown';
					?>
				<tr>
					<td><?= Security::sanitize($row['case_name']) ?></td>
					<td><?= Security::sanitize($plaintiff) ?></td>
					<td><?= Security::sanitize($defendant) ?></td>
					<td><?= Security::sanitize($row['status']) ?></td>
					<td>
						<span class="badge <?= isset($row['warrant_id']) ? 'bg-danger' : 'bg-success' ?>">
						<?= isset($row['warrant_id']) ? 'Yes' : 'No' ?>
						</span>
					</td>
					<td><?= Security::sanitize($row['next_date']) ?></td>
					<?php if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04'){ ?>
					<td>
						<?php
							// Check if the case has a warrant (based on the warrants table)
							if ($row['warrant_id']): // Warrant exists
							    ?>
						<form method="POST" action="index.php?pg=warrant.php&option=view" onsubmit="return confirm('Remove warrant status for this case?')">
							<input type="hidden" name="case_id" value="<?= $row['case_id'] ?>">
							<input type="hidden" name="current_value" value="1"> <!-- Already has warrant, so set current value to 1 -->
							<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
							<button type="submit" name="toggle_warrant" class="btn btn-sm btn-outline-danger">Remove Warrant</button>
						</form>
						<?php else: // No warrant exists ?>
						<form method="POST" action="index.php?pg=warrant.php&option=view" onsubmit="return confirm('Add warrant status for this case?')">
							<input type="hidden" name="case_id" value="<?= $row['case_id'] ?>">
							<input type="hidden" name="current_value" value="0"> <!-- No warrant, so set current value to 0 -->
							<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
							<button type="submit" name="toggle_warrant" class="btn btn-sm btn-outline-success">Add Warrant</button>
						</form>
						<?php endif; ?>
					</td>
					<?php } ?>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>
	<hr>
	<?php if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04'): ?>
	<h4 class="text-success mt-4">➕ Add Warrant to Another Case</h4>
	<form method="POST" action="index.php?pg=warrant.php&option=view" class="row g-3">
		<div class="col-md-6">
			<label for="case_id" class="form-label">Select Case</label>
			<select name="case_id" class="form-select" required>
				<option value="">-- Select a Case --</option>
				<?php
					$res = $conn->query("SELECT case_id, case_name FROM cases WHERE is_warrant = 0");
					while ($r = $res->fetch_assoc()): ?>
				<option value="<?= $r['case_id'] ?>"><?= Security::sanitize($r['case_name']) ?></option>
				<?php endwhile; ?>
			</select>
		</div>
		<input type="hidden" name="current_value" value="0">
		<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
		<div class="col-md-12">
			<button type="submit" name="toggle_warrant" class="btn btn-primary">Add Warrant</button>
		</div>
	</form>
	<?php endif; ?>
</div>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>