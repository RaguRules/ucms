<?php
if(isset($_SESSION["LOGIN_USERTYPE"])){
	    $systemUsertype = $_SESSION["LOGIN_USERTYPE"];
		$systemUsername = $_SESSION["LOGIN_USERNAME"];
	}else{
		$systemUsertype = "GUEST";
	}
	
	if (empty($_SESSION['csrf_token'])) {
	    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	
	$helper = new Helper($conn);
	$security = new Security();

$staffId = $helper->getId($_SESSION["LOGIN_USERNAME"], $_SESSION["LOGIN_USERTYPE"]);
$today = date('Y-m-d');
$isEdaybook = isset($_GET['view']) && $_GET['view'] === 'e-daybook';


// Handle Add New Activity
if (isset($_POST['btn_add_activity'])) {
    $caseName = Security::sanitize($_POST['case_name']);
    $summary = Security::sanitize($_POST['summary']);
    $nextDate = $_POST['next_date'];
    $activityDate = $_POST['activity_date'];
    $currentStatus = Security::sanitize($_POST['current_status']);
    $nextStatus = Security::sanitize($_POST['next_status']);
    $isTaken = isset($_POST['is_taken']) ? 1 : 0;
    $activityId = $helper->generateNextActivityID();

    // Insert activity
    $stmt = $conn->prepare("INSERT INTO dailycaseactivities 
        (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $activityId, $caseName, $summary, $nextDate, $currentStatus, $nextStatus, $isTaken, $activityDate, $staffId);
    $stmt->execute();

    // Update case details
    $update = $conn->prepare("UPDATE cases SET status=?, is_warrant=?, next_date=?, for_what=?, staff_id=? WHERE case_name=?");
    $isWarrant = isset($_POST['is_warrant']) ? 1 : 0;
    $forWhat = $_POST['next_status'];
    $update->bind_param("sissis", $nextStatus, $isWarrant, $nextDate, $forWhat, $staffId, $caseName);
    $update->execute();

    // header("Location: dailycaseactivities.php");
    echo "<script>location.href='index.php?pg=dailycaseactivities.php';</script>";
    exit;
}

// Handle Full View
if (isset($_POST['btn_full_view']) && isset($_POST['activity_id'])) {
    $activityId = Security::sanitize($_POST['activity_id']);
    $stmt = $conn->prepare("SELECT * FROM dailycaseactivities WHERE activity_id = ?");
    $stmt->bind_param("i", $activityId);
    $stmt->execute();
    $result = $stmt->get_result();
    $activity = $result->fetch_assoc();
    // Display modal here (excluded for brevity)
}

// Handle Edit
if (isset($_POST['btn_edit_activity']) && isset($_POST['activity_id'])) {
    $activityId = $_POST['activity_id'];
    $stmt = $conn->prepare("UPDATE dailycaseactivities SET summary=?, next_date=?, current_status=?, next_status=?, is_taken=? WHERE activity_id=?");
    $stmt->bind_param(
        "ssssii",
        $_POST['summary'],
        $_POST['next_date'],
        $_POST['current_status'],
        $_POST['next_status'],
        $_POST['is_taken'],
        $activityId
    );
    $stmt->execute();
    // header("Location: dailycaseactivities.php");
    echo "<script>location.href='index.php?pg=dailycaseactivities.php';</script>";
    exit;
}

// -----------------------------
// 🔍 View Section Logic
// -----------------------------
$casesToShow = [];
$today = date('Y-m-d');

// Get most recent activity for each case (latest activity_date), and get its next_date
$sql = "
    SELECT 
    d.case_name,
    d.next_date
FROM 
    dailycaseactivities d
INNER JOIN (
    SELECT 
        case_name, MAX(activity_date) AS latest_activity
    FROM 
        dailycaseactivities
    GROUP BY 
        case_name
) latest ON d.case_name = latest.case_name AND d.activity_date = latest.latest_activity

";

$res = $conn->query($sql);
$datesFromActivities = [];

while ($r = $res->fetch_assoc()) {
    if ($r['next_date'] <= $today) {
        $datesFromActivities[$r['case_name']] = $r['next_date'];
    }
}

// Now get from `cases` table where there's no activity yet
$sql2 = "SELECT case_name, next_date FROM cases";
$res2 = $conn->query($sql2);
while ($r = $res2->fetch_assoc()) {
    $caseName = $r['case_name'];
    if (!isset($datesFromActivities[$caseName]) && $r['next_date'] <= $today) {
        $datesFromActivities[$caseName] = $r['next_date'];
    }
}

// Format final list
foreach ($datesFromActivities as $case => $date) {
    $casesToShow[] = [
        'case_name' => $case,
        'next_date' => $date
    ];
}
?>

<!-- VIEW SECTION -->
<div class="container py-4">
    <h4>Pending Hearings (Today or Earlier)</h4>
    <?php if (!empty($casesToShow)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Case Name</th>
                        <th>Next Hearing Date (Due)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($casesToShow as $case): ?>
                        <tr>
                            <?php
                            $realCaseName = $helper->getCaseData($case['case_name']);
                            ?>
                            <td><?= Security::sanitize($realCaseName['case_name']) ?></td>
                            <td><?= Security::sanitize($case['next_date']) ?></td>
                            <td>
                                <form method="POST" action="index.php?pg=dailycaseactivities.php">
                                    <input type="hidden" name="case_name" value="<?= Security::sanitize($case['case_name']) ?>">
                                    <input type="hidden" name="activity_date" value="<?= Security::sanitize($case['next_date']) ?>">
                                    <button type="submit" name="btn_add_form" class="btn btn-success btn-sm">Add Activity</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No cases require activity entry for today or earlier.</p>
    <?php endif; ?>
</div>

<!-- ADD FORM (Displayed only when btn_add_form is clicked) -->
<?php if (isset($_POST['btn_add_form'], $_POST['case_name'], $_POST['activity_date'])): ?>
    <div class="container py-4">
        <h4>Add Activity for <b><?= Security::sanitize($realCaseName['case_name']) ?> </b> on <i><?= Security::sanitize($_POST['activity_date']) ?></i></h4>
        <form method="POST" action="index.php?pg=dailycaseactivities.php">
            <input type="hidden" name="case_name" value="<?= $_POST['case_name'] ?>">
            <input type="hidden" name="activity_date" value="<?= $_POST['activity_date'] ?>">

            <div class="mb-3">
                <label>Summary</label>
                <textarea name="summary" class="form-control" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Next Date</label>
                    <input type="date" name="next_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
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
                        <option value="Appeal">Appeal</option>
                        <option value="Completed/ Closed">Completed/ Closed</option>
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
                        <option value="Appeal">Appeal</option>
                        <option value="Completed/ Closed">Completed/ Closed</option>
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
                        <option value="0">No</option>
                        <option value="1">Yes</option>
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
        <h2 class="text-primary mb-4">📘 Electronic Daily Case Journal (E-Daybook)</h2>

        <?php if (!empty($activitiesByDate)): ?>
            <?php foreach ($activitiesByDate as $date => $activities): ?>
                <div class="border border-dark-subtle rounded-3 shadow-sm mb-4 bg-white">
                    <div class="bg-dark text-white px-3 py-2 rounded-top">
                        <h5 class="mb-0">📅 <?= date("l, d M Y", strtotime($date)) ?></h5>
                    </div>
                    <div class="p-3">
                        <?php foreach ($activities as $row): ?>
                            <div class="mb-3 p-3 border-start border-4 border-primary bg-light rounded">
                                <h6 class="text-primary">Case: <?= htmlspecialchars($row['case_name']) ?></h6>
                                <p><strong>Summary:</strong> <?= nl2br(htmlspecialchars($row['summary'])) ?></p>
                                <p><strong>Current Status:</strong> <?= htmlspecialchars($row['current_status']) ?></p>
                                <p><strong>Next Date:</strong> <?= htmlspecialchars($row['next_date']) ?></p>
                                <p><strong>Next Status:</strong> <?= htmlspecialchars($row['next_status']) ?></p>
                                <p>
                                    <strong>Is Taken:</strong> 
                                    <span class="badge <?= $row['is_taken'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $row['is_taken'] ? 'Yes' : 'No' ?>
                                    </span>
                                </p>
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