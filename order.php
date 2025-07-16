<?php
	if($systemUsertype == 'GUEST'){
	    echo "<script>location.href='index.php?pg=404.php';</script>";
	}
	
	$staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);
	$today = date('Y-m-d');
	
	if (isset($_POST['add_order'])) {
	    if ($systemUsertype != 'R01' && $systemUsertype != 'R02' && $systemUsertype != 'R03' && $systemUsertype != 'R04' && $systemUsertype != 'R05') {
	        die("Unauthorised access.");
	    }
	    
	    $caseId = $_POST['case_id'];
	    $isCalculated = isset($_POST['is_calculated']) ? 1 : 0;
	    $givenOn = $_POST['given_on'];
	
	    $orderId = $helper->generateNextOrderID();
	    $stmt = $conn->prepare("INSERT INTO orders (order_id, case_id, is_calculated, given_on, staff_id) VALUES (?, ?, ?, ?, ?)");
	    $stmt->bind_param("ssiss", $orderId, $caseId, $isCalculated, $givenOn, $staffId);
	    $stmt->execute();

	    $activityId = $helper->generateNextActivityID();
	    $summary = 'Order Issued';
	    $currentStatus = 'Order';
	    $nextStatus = 'Calling'; 
	    $isTaken = 1;
	
	    $stmt2 = $conn->prepare("INSERT INTO dailycaseactivities (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
	    $stmt2->bind_param("ssssssisi", $activityId, $caseId, $summary, $givenOn, $currentStatus, $nextStatus, $isTaken, $givenOn, $staffId);
	    $stmt2->execute();
	
	    echo "<script>alert('Order added successfully.'); location.href='index.php?pg=order.php';</script>";
	    exit;
	}
	
	$orders = $conn->query("SELECT o.*, c.case_name, c.plaintiff, c.defendant FROM orders o JOIN cases c ON o.case_id = c.case_id ORDER BY o.given_on DESC");
	$cases = $conn->query("SELECT case_id, case_name FROM cases");
	?>
<div class="container py-4">
	<?php
		if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04' || $systemUsertype == 'R05'){
		?>
	<h3 class="text-primary">➕ Add New Order</h3>
	<form method="POST" class="row g-3">
		<div class="col-md-6">
			<label class="form-label">Select Case</label>
			<select name="case_id" class="form-select" required>
				<option value="">-- Choose Case --</option>
				<?php while ($c = $cases->fetch_assoc()): ?>
				<option value="<?= $c['case_id'] ?>"><?= Security::sanitize($c['case_name']) ?></option>
				<?php endwhile; ?>
			</select>
		</div>
		<div class="col-md-3">
			<label class="form-label">Order Date</label>
			<input type="date" name="given_on" class="form-control" required>
		</div>
		<div class="col-md-3">
			<label class="form-label">Calculated?</label><br>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="is_calculated" id="calculated">
				<label class="form-check-label" for="calculated">Yes</label>
			</div>
		</div>
		<div class="col-12">
			<button type="submit" name="add_order" class="btn btn-success">Save Order</button>
		</div>
	</form>
	<hr class="my-4">
    <form method="POST" action="lib/rep/report5.php" target="_blank" class="mb-3">
    <button type="submit" name="print_order_report" class="btn btn-outline-dark">🖨️ Print A4 Report</button>
	</form>
    	<?php
		}
		?>
	<h4 class="text-secondary">📄 Orders History</h4>

	<div class="table-responsive">
		<table class="table table-bordered">
			<thead class="table-light">
				<tr>
					<th>Case Name</th>
					<th>Plaintiff</th>
					<th>Defendant</th>
					<th>Order Date</th>
					<th>Calculated?</th>
					<th>Staff ID</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($r = $orders->fetch_assoc()){ 
					$plaintiffData = $helper->getPartyData($r['plaintiff']);
					$plaintiff = $plaintiffData ? $plaintiffData['first_name'] . ' ' . $plaintiffData['last_name'] : 'Unknown';
					$defendantData = $helper->getPartyData($r['defendant']);
					$defendant = $defendantData ? $defendantData['first_name'] . ' ' . $defendantData['last_name'] : 'Unknown';
					$staffData = $helper->getStaffData($r['staff_id']);
					$staff = $staffData ? $staffData['first_name'] . ' ' . $staffData['last_name'] : 'Unknown';
					
					?>
				<tr>
					<td><?= Security::sanitize($r['case_name']) ?></td>
					<td><?= Security::sanitize($plaintiff) ?></td>
					<td><?= Security::sanitize($defendant) ?></td>
					<td><?= Security::sanitize($r['given_on']) ?></td>
					<td><?= $r['is_calculated'] ? 'Yes' : 'No' ?></td>
					<td><?= Security::sanitize($staff) ?></td>
				</tr>
				<?php }?>
			</tbody>
		</table>
	</div>
</div>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>