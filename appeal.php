<?php

	if($systemUsertype == 'GUEST'){
	    echo "<script>location.href='index.php?pg=404.php';</script>";
	}

// Mark case as appeal
if (isset($_POST['toggle_appeal']) && isset($_POST['case_name'])) {

    if ($systemUsertype != 'R01' && $systemUsertype != 'R03' && $systemUsertype != 'R04' && $systemUsertype != 'R05') {
		die("Unauthorised access.");
	}

    $caseName = $_POST['case_name'];
    $today = date('Y-m-d');

    $activityId = $helper->generateNextActivityID();
    $staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);

    $stmt = $conn->prepare("INSERT INTO dailycaseactivities (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $summary = 'Case moved to appeal';
    $nextDate = $today;
    $currentStatus = 'Judgement';
    $nextStatus = 'Appeal';
    $isTaken = 1;
    $stmt->bind_param("ssssssisi", $activityId, $caseName, $summary, $nextDate, $currentStatus, $nextStatus, $isTaken, $today, $staffId);
    $stmt->execute();

    // Insert into appeals table
    $appealId = $helper->generateNextAppealID();
    $stmt2 = $conn->prepare("SELECT plaintiff, defendant FROM cases WHERE case_id = ?");
    $stmt2->bind_param("s", $caseName);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $case = $result->fetch_assoc();
    $appellant = $case['plaintiff'];
    $respondent = $case['defendant'];

    $stmt3 = $conn->prepare("INSERT INTO appeals (appeal_id, case_id, appellant_party_id, respondent_party_id, appeal_date, appeal_status) VALUES (?, ?, ?, ?, ?, ?)");
    $appealStatus = 'Pending';
    $stmt3->bind_param("ssssss", $appealId, $caseName, $appellant, $respondent, $today, $appealStatus);
    $stmt3->execute();

    echo "<script>location.href='index.php?pg=appeal.php&option=view';</script>";
    exit;
}

// Update appeal follow-up status
if (isset($_POST['update_appeal']) && isset($_POST['case_name']) && isset($_POST['appeal_status'])) {
	if ($systemUsertype != 'R01' && $systemUsertype != 'R03' && $systemUsertype != 'R04' && $systemUsertype != 'R05') {
		die("Unauthorised access.");
	}
    $caseName = $_POST['case_name'];
    $today = date('Y-m-d');
    $appealStatus = $_POST['appeal_status'];
    $nextDate = !empty($_POST['next_date']) ? $_POST['next_date'] : NULL;
    $currentStatus = 'Appeal';
    $isTaken = 1;
    $staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);

    // Determine nextStatus from outcome
    switch ($appealStatus) {
        case 'Appeal Dismissed':
            $nextStatus = 'Dismissed';
            break;
        case 'Refixed for Trial':
            $nextStatus = 'Trial';
            break;
        case 'Refixed for Pre Trial Conference':
            $nextStatus = 'Pre Trial Conference';
            break;
        case 'Refixed for Calling':
            $nextStatus = 'Calling';
            break;
        case 'Refixed for Order':
            $nextStatus = 'Order';
            break;
        default:
            $nextStatus = $appealStatus;
    }

    $summary = 'Appeal outcome: ' . $appealStatus;

    // Safely update only the most recent appeal record for this case
    $stmt = $conn->prepare("UPDATE dailycaseactivities 
        SET summary = ?, next_date = ?, current_status = ?, next_status = ?, is_taken = ?, activity_date = ?, staff_id = ? 
        WHERE case_name = ? 
        AND activity_id = (
            SELECT activity_id FROM (
                SELECT activity_id FROM dailycaseactivities 
                WHERE case_name = ? AND next_status = 'Appeal' 
                ORDER BY activity_date DESC LIMIT 1
            ) AS recent_id
        )");

    $stmt->bind_param("ssssissss", $summary, $nextDate, $currentStatus, $nextStatus, $isTaken, $today, $staffId, $caseName, $caseName);
    $stmt->execute();

    // Remove from appeals table if appeal is resolved
    if ($nextStatus !== 'Appeal') {
        $deleteAppeal = $conn->prepare("DELETE FROM appeals WHERE case_id = ?");
        $deleteAppeal->bind_param("s", $caseName);
        $deleteAppeal->execute();
    }

    echo "<script>location.href='index.php?pg=appeal.php&option=view';</script>";
    exit;
}

// Handle search filter
$search = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : null;

// Fetch appeal cases
$sql = "
SELECT d.*, c.case_name AS proper_case_name FROM dailycaseactivities d
INNER JOIN (
    SELECT case_name, MAX(activity_date) AS latest_activity
    FROM dailycaseactivities
    GROUP BY case_name
) latest ON d.case_name = latest.case_name AND d.activity_date = latest.latest_activity
INNER JOIN cases c ON d.case_name = c.case_id
WHERE d.next_status = 'Appeal'
";
if ($search) {
    $sql .= " AND c.case_name LIKE '" . $conn->real_escape_string($_GET['search']) . "%'";
}
$sql .= " ORDER BY d.activity_date DESC";
$result = $conn->query($sql);

// Fetch cases where judgement is completed (to be added to appeal)
$judgementCases = $conn->query("
    SELECT d.case_name, MAX(d.activity_date) as latest_date
    FROM dailycaseactivities d
    WHERE d.next_status = 'Completed - Judgement Delivered' 
       OR d.next_status = 'Completed - Order Made' 
       OR d.next_status = 'Dismissed' 
       OR d.next_status = 'Order'
    GROUP BY d.case_name
");

?>

<div class="container py-4">
    <h3 class="mb-4 text-primary">📜 Cases Appealed</h3>

    <form method="GET" action="index.php" class="row g-3 mb-3">
        <input type="hidden" name="pg" value="appeal.php">
        <input type="hidden" name="option" value="view">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search case name..." value="<?= Security::sanitize($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary">Search</button>
        </div>
        <div class="col-md-2">
            <a href="index.php?pg=appeal.php&option=view" class="btn btn-outline-secondary">Clear</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-warning">
                <tr>
                    <th>Case Name</th>
                    <th>Summary</th>
                    <th>Appealed On</th>
						<?php
							if($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04') {
						?>
                    <th>Action</th>
					<?php
						}
					?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= Security::sanitize($row['proper_case_name']) ?></td>
                        <td><?= Security::sanitize($row['summary']) ?></td>
                        <td><?= Security::sanitize($row['activity_date']) ?></td>
							<?php
								if($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04') {
							?>
                        <td>
                            <form method="POST" action="index.php?pg=appeal.php&option=view" class="row g-2">
                                <input type="hidden" name="case_name" value="<?= Security::sanitize($row['case_name']) ?>">
                                <div class="col-md-12">
                                    <select name="appeal_status" class="form-select" required>
                                        <option value="">-- Appeal Court Decision --</option>
                                        <option value="Appeal Dismissed">Appeal Dismissed</option>
                                        <option value="Refixed for Calling">Refixed for Calling</option>
                                        <option value="Refixed for Pre Trial Conference">Refixed for Pre Trial Conference</option>
                                        <option value="Refixed for Trial">Refixed for Trial</option>
                                        <option value="Refixed for Order">Refixed for Order</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <input type="date" name="next_date" class="form-control" placeholder="Next Date (if applicable)">
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" name="update_appeal" class="btn btn-sm btn-outline-primary mt-2">Update</button>
                                </div>
                            </form>
                        </td>
						<?php
							}
						?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <hr>
	<?php
		if($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04') {
	?>
    <h4 class="text-success mt-4">➕ Add Case to Appeal</h4>
    <form method="POST" action="index.php?pg=appeal.php&option=view" class="row g-3">
        <div class="col-md-6">
            <label for="case_name" class="form-label">Select Judgement Case</label>
            <select name="case_name" class="form-select" required>
                <option value="">-- Select a Case --</option>
                <?php while ($r = $judgementCases->fetch_assoc()): 
                    $realCaseName = $helper->getCaseData($r['case_name']);
                    ?>
                    <option value="<?= Security::sanitize($r['case_name']) ?>"><?= $realCaseName['case_name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-12">
            <button type="submit" name="toggle_appeal" class="btn btn-primary">Mark as Appealed</button>
        </div>
    </form>
	<?php
		}
	?>
</div>
