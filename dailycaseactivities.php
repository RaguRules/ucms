<?php
	if($systemUsertype == 'GUEST'){
	    echo "<script>location.href='index.php?pg=404.php';</script>";
	}
	
	$staffId = $helper->getId($_SESSION["LOGIN_USERNAME"], $_SESSION["LOGIN_USERTYPE"]);
	$today = date('Y-m-d');
	$isEdaybook = isset($_GET['view']) && $_GET['view'] === 'e-daybook';
	
	
	if (isset($_POST['btn_add_activity'])) {
	    $caseName = Security::sanitize($_POST['case_name']);
	    $summary = Security::sanitize($_POST['summary']);
	    $nextDate = $_POST['next_date'];
	    $activityDate = $_POST['activity_date'];
	    $currentStatus = Security::sanitize($_POST['current_status']);
	    $nextStatus = Security::sanitize($_POST['next_status']);
	    $isTaken = isset($_POST['is_taken']) ? 1 : 0;
	    $activityId = $helper->generateNextActivityID();
	
	        if ($systemUsertype != 'R01' && $systemUsertype != 'R02' && $systemUsertype != 'R03' && $systemUsertype != 'R04' && $systemUsertype != 'R05') {
		        die("Unauthorised access.");
		    }
	
	    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		       die("Invalid CSRF token.");
		   }
	    
	    $stmt = $conn->prepare("INSERT INTO dailycaseactivities 
	        (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id)
	        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
	    $stmt->bind_param("sssssssss", $activityId, $caseName, $summary, $nextDate, $currentStatus, $nextStatus, $isTaken, $activityDate, $staffId);
	    $stmt->execute();
	
	
	
	    if ($stmt->affected_rows > 0) {
	        $update = $conn->prepare("UPDATE cases SET status=?, is_warrant=?, next_date=?, for_what=?, staff_id=? WHERE case_id=?");
	        $isWarrant = isset($_POST['is_warrant']) ? 1 : 0;
	        $update->bind_param("sissss", $currentStatus, $isWarrant, $nextDate, $nextStatus, $staffId, $caseName);
	        $update->execute();
	
	        $caseData = $helper->getCaseData($caseName);
	        $caseId = $caseData['case_id'] ?? null;
	
	        if ($caseId) {
	            // 1. Fixed for Judgement notification
	            if (in_array($nextStatus, ['Judgement'])) {
	                $message="'{$caseData['case_name']}' has been fixed for Judgement on {$nextDate}.";
	                $helper->triggerNextDateUpdated($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	
	            // 2. Judgement Delivered notification
	            if (in_array($currentStatus, ['Judgement']) && in_array($nextStatus, ['Completed - Judgement Delivered'])) {
	
	                $judgementId = $helper->generateNextJudgementID();
	                $isContested = 1;
	
	                $stmt = $conn->prepare("INSERT INTO judgements (jud_id, case_id, is_contested, given_on, staff_id) 
	                                        VALUES (?, ?, ?, ?, ?)");
	
	                $stmt->bind_param("ssiss", $judgementId, $caseId, $isContested, $activityDate, $staffId);
	
	                if ($stmt->execute()) {
	                    echo "Judgement details successfully Registered.";
	                } else {
	                    echo "<script>alert('Failed at registering Judgement details');</script>";
	                }
	
	                $stmt->close();
	
	                $message="Judgement has been delivered to '{$caseData['case_name']}' on {$nextDate}.";
	                $helper->triggerJudgementNotification($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);   
	            }
	
	            // 3. Fixed for Order notification
	            if (in_array($nextStatus, ['Order'])) {
	                $message="'{$caseData['case_name']}' has been fixed for Order on {$nextDate}.";
	                $helper->triggerNextDateUpdated($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	
	            // 4. Order Made notification and inserting into Order table
	            if (in_array($currentStatus, ['Order']) && in_array($nextStatus, ['Completed - Order Made'])) {
	
	                $orderId = $helper->generateNextOrderID();
	                $isCalculated = 1;
	
	                $stmt = $conn->prepare("INSERT INTO orders (order_id, case_id, is_calculated, given_on, staff_id) 
	                                        VALUES (?, ?, ?, ?, ?)");
	
	                $stmt->bind_param("ssiss", $orderId, $caseId, $isCalculated, $activityDate, $staffId);
	
	                if ($stmt->execute()) {
	                    echo "Order details successfully Registered.";
	                } else {
	                    echo "<script>alert('Failed at registering order details');</script>";
	                }
	
	                $stmt->close();
	
	                $message="Order has been given to '{$caseData['case_name']}' on {$nextDate}.";
	                $helper->triggerOrderNotification($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	            // 5. Next Date Changed notification
	            if (!empty($nextDate) && !in_array($nextStatus, ['Order', 'Judgement'])) {
	                $message="Next hearing date has been updated for case '{$caseData['case_name']}' to {$nextDate}.";
	                $helper->triggerNextDateUpdated($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	            echo "<script>location.href='index.php?pg=dailycaseactivities.php';</script>";
	            exit;
	        }
	    }
	}
	
	
	if (isset($_POST['btn_update']) && isset($_POST['activity_id'])) {
	    $summary = Security::sanitize($_POST['summary']);
	    $nextDate = $_POST['next_date'];
	    $currentStatus = Security::sanitize($_POST['current_status']);
	    $nextStatus = Security::sanitize($_POST['next_status']);
	    $isTaken = Security::sanitize($_POST['is_taken']);
	    $caseName = Security::sanitize($_POST['case_name']);
	
	    if ($systemUsertype != 'R01' && $systemUsertype != 'R02' && $systemUsertype != 'R03' && $systemUsertype != 'R04' && $systemUsertype != 'R05') {
	        die("Unauthorised access.");
	    }
	
	    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		       die("Invalid CSRF token.");
		}
	
	    $activityId = $_POST['activity_id'];
	    $stmt = $conn->prepare("UPDATE dailycaseactivities SET summary=?, next_date=?, current_status=?, next_status=?, is_taken=? WHERE activity_id=?");
	    $stmt->bind_param(
	        "ssssis",
	        $summary,
	        $nextDate,
	        $currentStatus,
	        $nextStatus,
	        $isTaken,
	        $activityId
	    );
	    $stmt->execute();
	
	
	    if ($stmt->affected_rows > 0) {
	        $update = $conn->prepare("UPDATE cases SET status=?, is_warrant=?, next_date=?, for_what=?, staff_id=? WHERE case_id=?");
	        $isWarrant = isset($_POST['is_warrant']) ? 1 : 0;
	        $nextStatus = $_POST['next_status'];
	        $update->bind_param("sissss", $currentStatus, $isWarrant, $nextDate, $nextStatus, $staffId, $caseName);
	        $update->execute();
	
	        if ($update === false) {
	            echo "<script>alert('Error updating case details: " . $conn->error . "');</script>";
	            exit;
	        }else{
	            echo "<script>alert('Success updating case details in cases table');</script>";
	        }
	
	        // Now trigger notifications based on status
	        $caseData = $helper->getCaseData($caseName);
	        $caseId = $caseData['case_id'] ?? null;
	
	        if ($caseId) {
	            // 1. Fixed for Judgement notification
	            if (in_array($nextStatus, ['Judgement'])) {
	                $message="'{$caseData['case_name']}' has been fixed for Judgement on {$nextDate}.";
	                $helper->triggerNextDateUpdated($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	
	            // 2. Judgement Delivered notification
	            if (in_array($currentStatus, ['Judgement']) && in_array($nextStatus, ['Completed - Judgement Delivered'])) {
	
	                $judgementId = $helper->generateNextJudgementID();
	                $isContested = 1;
	
	                $stmt = $conn->prepare("INSERT INTO judgements (jud_id, case_id, is_contested, given_on, staff_id) 
	                                        VALUES (?, ?, ?, ?, ?)");
	
	                $stmt->bind_param("ssiss", $judgementId, $caseId, $isContested, $activityDate, $staffId);
	
	                if ($stmt->execute()) {
	                    echo "Judgement details successfully Registered.";
	                } else {
	                    echo "<script>alert('Failed at registering Judgement details');</script>";
	                }
	
	                $stmt->close();
	
	                $message="Judgement has been delivered to '{$caseData['case_name']}' on {$nextDate}.";
	                $helper->triggerJudgementNotification($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);   
	            }
	
	            // 3. Fixed for Order notification
	            if (in_array($nextStatus, ['Order'])) {
	                $message="'{$caseData['case_name']}' has been fixed for Order on {$nextDate}.";
	                $helper->triggerNextDateUpdated($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	
	            // 4. Order Made notification and inserting into Order table
	            if (in_array($currentStatus, ['Order']) && in_array($nextStatus, ['Completed - Order Made'])) {
	
	                $orderId = $helper->generateNextOrderID();
	                $isCalculated = 1;
	
	                $stmt = $conn->prepare("INSERT INTO orders (order_id, case_id, is_calculated, given_on, staff_id) 
	                                        VALUES (?, ?, ?, ?, ?)");
	
	                $stmt->bind_param("ssiss", $orderId, $caseId, $isCalculated, $activityDate, $staffId);
	
	                if ($stmt->execute()) {
	                    echo "Order details successfully Registered.";
	                } else {
	                    echo "<script>alert('Failed at registering order details');</script>";
	                }
	
	                $stmt->close();
	
	                $message="Order has been given to '{$caseData['case_name']}' on {$nextDate}.";
	                $helper->triggerOrderNotification($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	            // 5. Next Date Changed notification
	            if (!empty($nextDate) && !in_array($nextStatus, ['Order', 'Judgement'])) {
	                $message="Next hearing date has been updated for case '{$caseData['case_name']}' to {$nextDate}.";
	                $helper->triggerNextDateUpdated($caseId, $message);
	                $helper->sendHearingDateSMS($caseId, $nextDate, $message);
	            }
	
	            echo "<script>location.href='index.php?pg=dailycaseactivities.php';</script>";
	            exit;
	        }
	    }else {
	        // No rows were affected
	        echo "<script>alert('Error occurred, Contacy system administrator');</script>";
	    }
	    echo "<script>location.href='index.php?pg=dailycaseactivities.php';</script>";
	    exit;
	}
	 
	$casesToShow = [];
	$today = date('Y-m-d');
	
	// STEP 1: Fetch all cases indexed by case_id
	$allCases = [];
	$sql_cases = "SELECT case_id, case_name, next_date FROM cases";
	$result_cases = $conn->query($sql_cases);
	while ($row = $result_cases->fetch_assoc()) {
	    $allCases[$row['case_id']] = [
	        'case_id' => $row['case_id'],
	        'case_name' => $row['case_name'],
	        'next_date' => $row['next_date']
	    ];
	}
	
	// STEP 2: Get MAX(next_date) from dailycaseactivities per case_name (which is actually case_id)
	$sql = "
	    SELECT case_name AS case_id, MAX(next_date) AS max_next_date
	    FROM dailycaseactivities
	    GROUP BY case_name
	";
	$res = $conn->query($sql);
	
	$activityNextDates = [];
	while ($r = $res->fetch_assoc()) {
	    $activityNextDates[$r['case_id']] = $r['max_next_date'];
	}
	
	// STEP 3: Compare by case_id
	foreach ($allCases as $case_id => $caseData) {
	    if (isset($activityNextDates[$case_id])) {
	        // use max next_date from dailycaseactivities
	        if ($activityNextDates[$case_id] <= $today) {
	            $casesToShow[] = [
	                'case_id' => $caseData['case_id'],
	                'case_name' => $caseData['case_name'],
	                'next_date' => $activityNextDates[$case_id]
	            ];
	        }
	    } else {
	        // no activity found, use next_date from cases table
	        if ($caseData['next_date'] <= $today) {
	            $casesToShow[] = [
	                'case_id' => $caseData['case_id'],
	                'case_name' => $caseData['case_name'],
	                'next_date' => $caseData['next_date']
	            ];
	        }
	    }
	}
	?>

<!-- FINAL UI OUTPUT -->
<div class="container py-4">
	<a href="index.php?pg=dailycaseactivities.php&view=e-daybook" class="btn btn-outline-secondary d-print-none float-end mb-3">
	📘 View e-DayBook
	</a>
	<?php
		if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04' || $systemUsertype == 'R05'){
		?>
	<h4 class="text-primary">📅 Pending Hearings (Due Today or Missed)</h4>
	<?php if (!empty($casesToShow)): ?>
	<div class="table-responsive">
		<table class="table table-bordered table-hover shadow-sm">
			<thead class="table-dark">
				<tr>
					<th>Case Name</th>
					<th>Due Hearing Date</th>
					<?php
						if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04' || $systemUsertype == 'R05'){
						?>
					<th>Actions</th>
					<?php
						}
					?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($casesToShow as $case): ?>
				<tr>
					<?php $realCase = $helper->getCaseData($case['case_id']); ?>
					<td class="fw-semibold"><?= Security::sanitize($realCase['case_name']) ?></td>
					<td class="text-danger fw-bold">
						<?= date('d M Y', strtotime($case['next_date'])) ?>
					</td>
					<td>
						<form method="POST" action="index.php?pg=dailycaseactivities.php">
							<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
							<input type="hidden" name="case_name" value="<?= Security::sanitize($case['case_id']) ?>">
							<input type="hidden" name="activity_date" value="<?= Security::sanitize($case['next_date']) ?>">
							<button type="submit" name="btn_add_form" class="btn btn-success btn-sm">
							➕ Add Activity
							</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php else: ?>
	<div class="alert alert-info">✅ No cases with pending hearings for today or earlier.</div>
	<?php endif; 
		}
		?>
</div>
<!-- ADD FORM (Displayed only when btn_add_form is clicked) -->
<?php if (isset($_POST['btn_add_form'], $_POST['case_name'], $_POST['activity_date'])): ?>
<div class="container py-4">
	<?php
		$realCaseForAdd = $helper->getCaseData($_POST['case_name']); 
		?>
	<h4>Add Activity for <b><?= Security::sanitize($realCaseForAdd['case_name']) ?> </b> on <i><?= Security::sanitize($_POST['activity_date']) ?></i></h4>
	<form method="POST" action="index.php?pg=dailycaseactivities.php">
		<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
		<input type="hidden" name="case_name" value="<?= $_POST['case_name'] ?>">
		<input type="hidden" name="activity_date" value="<?= $_POST['activity_date'] ?>">
		<div class="mb-3">
			<label>Summary</label>
			<textarea name="summary" class="form-control" required></textarea>
		</div>
		<div class="row">
			<div class="col-md-6">
				<label>Current Status</label>
				<select name="current_status" class="form-select" required>
					<option value="">-- Select Current Status --</option>
					<option value="Calling">Calling</option>
					<option value="Pre Trial Conference">Pre Trial Conference</option>
					<option value="Trial">Trial</option>
					<option value="Inquiry">Inquiry</option>
					<option value="Order">Order</option>
					<option value="Judgement">Judgement</option>
					<option value="Post Judgement Calling">Post Judgement Calling</option>
					<option value="Laid By">Laid By</option>
					<option value="Motion">Motion</option>
					<option value="Dismissed">Dismissed</option>
					<option value="Completed - Judgement Delivered">Completed - Judgement Delivered</option>
					<option value="Appeal">Appeal</option>
					<option value="Completed - Order Made">Completed - Order Made</option>
				</select>
			</div>
			<div class="col-md-6">
				<label>Next Status</label>
				<select name="next_status" class="form-select" required>
					<option value="">-- Select Current Status --</option>
					<option value="Calling">Calling</option>
					<option value="Pre Trial Conference">Pre Trial Conference</option>
					<option value="Trial">Trial</option>
					<option value="Inquiry">Inquiry</option>
					<option value="Order">Order</option>
					<option value="Judgement">Judgement</option>
					<option value="Post Judgement Calling">Post Judgement Calling</option>
					<option value="Laid By">Laid By</option>
					<option value="Motion">Motion</option>
					<option value="Dismissed">Dismissed</option>
					<option value="Completed - Judgement Delivered">Completed - Judgement Delivered</option>
					<option value="Appeal">Appeal</option>
					<option value="Completed - Order Made">Completed - Order Made</option>
				</select>
			</div>
			<div class="col-md-6" id="next-date-container">
				<label>Next Date</label>
				<input type="date" name="next_date" id="next_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
			</div>
			<div class="col-md-6 mt-2">
				<label>&nbsp;</label>
				<button type="button" class="btn btn-outline-primary w-100" id="suggest_trial_date">
				📅 Suggest Next Date
				</button>
			</div>
			<div class="col-md-6">
				<label>Arrest Warrant Issued?</label>
				<select name="is_warrant" class="form-select" required>
					<option value="0">No</option>
					<option value="1">Yes</option>
				</select>
			</div>
			<div class="col-md-6">
				<label>Mark as Taken? (select No, if Hon. Judge is Leave)</label>
				<select name="is_taken" class="form-select" required>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</div>
		</div>
		<div class="mt-3">
			<button type="submit" name="btn_add_activity" class="btn btn-primary">Save Activity</button>
			<a href="index.php?pg=dailycaseactivities.php" class="btn btn-secondary">Cancel</a>
		</div>
	</form>
</div>
<?php endif; ?>
<!-- EDIT SECTION FORM (Displayed only when btn_edit is clicked) -->
<?php
	if (isset($_POST['btn_edit'], $_POST['activity_id'], $_POST['case_id'])):
	    $row = $helper->getActivityData($_POST['activity_id']);
	    ?>
<div class="container py-4">
	<?php $realCase = $helper->getCaseData($row['case_name']);
		$caseName = $realCase['case_name'];
		?>
	<h4>Edit Activity for <b><?= Security::sanitize($caseName) ?></b> on <i><?= Security::sanitize($row['activity_date']) ?></i></h4>
	<form method="POST" action="index.php?pg=dailycaseactivities.php">
		<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
		<input type="hidden" name="case_name" value="<?= $_POST['case_id'] ?>">
		<input type="hidden" name="activity_id" value="<?= $_POST['activity_id'] ?>">
		<div class="mb-3">
			<label>Summary</label>
			<textarea name="summary" value="<?= Security::sanitize($row['summary']) ?>" class="form-control" required><?php echo Security::sanitize($row['summary']) ?></textarea>
		</div>
		<div class="row">
			<div class="col-md-6">
				<label>Next Date</label>
				<input type="date" name="next_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?php echo $row['next_date'] ?>" required>
			</div>
			<div class="col-md-6">
				<label>Current Status</label>
				<select name="current_status" class="form-select" required>
					<option value="">-- Select Current Status --</option>
					<option value="Calling">Calling</option>
					<option value="Pre Trial Conference">Pre Trial Conference</option>
					<option value="Trial">Trial</option>
					<option value="Inquiry">Inquiry</option>
					<option value="Order">Order</option>
					<option value="Judgement">Judgement</option>
					<option value="Post Judgement Calling">Post Judgement Calling</option>
					<option value="Laid By">Laid By</option>
					<option value="Motion">Motion</option>
					<option value="Dismissed">Dismissed</option>
					<option value="Completed - Judgement Delivered">Completed - Judgement Delivered</option>
					<option value="Appeal">Appeal</option>
					<option value="Completed - Order Made">Completed - Order Made</option>
				</select>
			</div>
			<div class="col-md-6">
				<label>Next Status</label>
				<select name="next_status" class="form-select" required>
					<option value="">-- Select Next Status --</option>
					<option value="Calling">Calling</option>
					<option value="Pre Trial Conference">Pre Trial Conference</option>
					<option value="Trial">Trial</option>
					<option value="Inquiry">Inquiry</option>
					<option value="Order">Order</option>
					<option value="Judgement">Judgement</option>
					<option value="Post Judgement Calling">Post Judgement Calling</option>
					<option value="Laid By">Laid By</option>
					<option value="Motion">Motion</option>
					<option value="Dismissed">Dismissed</option>
					<option value="Completed - Judgement Delivered">Completed - Judgement Delivered</option>
					<option value="Appeal">Appeal</option>
					<option value="Completed - Order Made">Completed - Order Made</option>
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
				<label>Mark as Taken?</label>
				<select name="is_taken" class="form-select" required>
					<option selected hidden value="<?= $row['is_taken'] ?>"><?= $row['is_taken'] == 1 ? 'Yes' : 'No' ?></option>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</div>
		</div>
		<div class="mt-3">
			<button type="submit" name="btn_update" class="btn btn-primary">Save Activity</button>
			<a href="index.php?pg=dailycaseactivities.php" class="btn btn-secondary">Cancel</a>
		</div>
	</form>
</div>
<?php endif; ?>
<?php
	if ($isEdaybook) {
	
	    $sql = "
	        SELECT * FROM dailycaseactivities
	        ORDER BY activity_date DESC, activity_id DESC
	    ";
	    $result = $conn->query($sql);
	
	    $activitiesByDate = [];
	
	    while ($row = $result->fetch_assoc()) {
	        $date = $row['activity_date'];
	        $activitiesByDate[$date][] = $row;
	    }
	    ?>
<div class="container mt-4">
	<?php
		if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04' || $systemUsertype == 'R05'){
		?>
	<button onclick="window.print()" class="btn btn-outline-secondary d-print-none float-end mb-3">
	🖨️ Print / Save as PDF
	</button>
	<?php
		}
		?>
	<h2 class="text-primary mb-4">📘 Daily Case Journal (e-Daybook)</h2>
	<?php if (!empty($activitiesByDate)): ?>
	<?php foreach ($activitiesByDate as $date => $activities): ?>
	<div class="border border-dark-subtle rounded-3 shadow-sm mb-4 bg-white">
		<div class="bg-dark text-white px-3 py-2 rounded-top">
			<h5 class="mb-0">📅<b> <?= date("l, d M Y", strtotime($date)) ?></h5>
		</div>
		<div class="p-3">
			<?php foreach ($activities as $row): ?>
			<div class="mb-3 p-3 border-start border-4 border-primary bg-light rounded">
				<?php $realCase = $helper->getCaseData($row['case_name']); ?>
				<h6 class="text-primary">Case: <?= Security::sanitize($realCase['case_name']) ?></h6>
				<p><strong>Summary:</strong> <?= nl2br(Security::sanitize($row['summary'])) ?></p>
				<p><strong>Current Status:</strong> <?= Security::sanitize($row['current_status']) ?></p>
				<p><strong>Next Date:</strong> <?= Security::sanitize($row['next_date']) ?></p>
				<p><strong>Next Status:</strong> <?= Security::sanitize($row['next_status']) ?></p>
				<p>
					<strong>Is Taken :</strong> 
					<span class="badge <?= $row['is_taken'] ? 'bg-success' : 'bg-danger' ?>">
					<?= $row['is_taken'] ? 'Yes' : 'No' ?>
					</span>
				</p>
				<!-- ✅ Edit Button Form -->
				<?php
					if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04' || $systemUsertype == 'R05'){
					?>
				<form method="POST" action="index.php?pg=dailycaseactivities.php&option=edit" class="d-inline">
					<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
					<input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_name']) ?>">
					<input type="hidden" name="activity_date" value="<?= Security::sanitize($row['activity_date']) ?>">
					<input type="hidden" name="activity_id" value="<?= Security::sanitize($row['activity_id']) ?>">
					<button type="submit" name="btn_edit" class="btn btn-sm btn-warning">✏️ Edit </button>
				</form>
				<?php
					}
					?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endforeach; ?>
	<?php else: ?>
	<div class="alert alert-info">No case activities recorded yet.</div>
	<?php endif; ?>
	<a href="index.php?pg=dailycaseactivities.php" class="btn btn-secondary mt-3">← Back</a>
</div>
<?php exit; } ?>


<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


<!-- Hide Next Date if case is Completed/ Closed -->
<script>
	$(document).ready(function () {
	    // Function to handle the show/hide logic for the Next Date field
	    function toggleNextDateField() {
	        var nextStatus = $('select[name="next_status"]').val();
	        if (nextStatus === 'Completed/ Closed') {
	            $('#next-date-container').hide();  // Hide the Next Date field
	        } else {
	            $('#next-date-container').show();  // Show the Next Date field
	        }
	    }
	
	    // Initial check when the page loads
	    toggleNextDateField();
	
	    // Event listener for when the Next Status field changes
	    $('select[name="next_status"]').on('change', function () {
	        toggleNextDateField();  // Recheck on change
	    });
	});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const suggestButton = document.getElementById('suggest_trial_date');
    const dateInput = document.getElementById('next_date');

    if (!suggestButton || !dateInput) {
        console.error("❌ Required elements not found (button or input missing).");
        return;
    }

    suggestButton.addEventListener('click', function () {
        console.log("📡 Requesting suggested trial date...");

        fetch('action/get_trial_date.ajax.php')
            .then(response => {
                console.log("🔄 Response status:", response.status);
                const contentType = response.headers.get("Content-Type") || "";
                console.log("🧾 Content-Type:", contentType);

                if (!response.ok) {
                    throw new Error(`Server returned status ${response.status}`);
                }

                if (!contentType.includes("application/json")) {
                    throw new Error("Response is not JSON.");
                }

                return response.json();
            })
            .then(data => {
                console.log("📦 Received data:", data);

                if (data.date) {
                    dateInput.value = data.date;
                    alert("✅ Suggested date filled: " + data.date);
                } else {
                    alert("⚠️ No date received from server.");
                }
            })
            .catch(error => {
                alert("⚠️ Could not fetch suggested date.");
                console.error("🔥 Error fetching suggested date:", error);
            });
    });
});
</script>


