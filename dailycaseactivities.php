<?php

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

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	       die("Invalid CSRF token.");
	   }
    
    // Insert activity
    $stmt = $conn->prepare("INSERT INTO dailycaseactivities 
        (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $activityId, $caseName, $summary, $nextDate, $currentStatus, $nextStatus, $isTaken, $activityDate, $staffId);
    $stmt->execute();



    if ($stmt->affected_rows > 0) {
        // Update case details
        $update = $conn->prepare("UPDATE cases SET status=?, is_warrant=?, next_date=?, for_what=?, staff_id=? WHERE case_name=?");
        $isWarrant = isset($_POST['is_warrant']) ? 1 : 0;
        $forWhat = $_POST['next_status'];
        $update->bind_param("sissis", $nextStatus, $isWarrant, $nextDate, $forWhat, $staffId, $caseName);
        $update->execute();

        // Now trigger notifications based on status
        $caseData = $helper->getCaseData($caseName);
        $caseId = $caseData['case_id'] ?? null;

        if ($caseId) {
            // Judgement notification
            if (in_array($currentStatus, ['Judgement']) || in_array($nextStatus, ['Judgement'])) {
                $helper->triggerJudgementNotification($caseId);
            }

            // Order notification
            if (in_array($currentStatus, ['Order']) || in_array($nextStatus, ['Order'])) {
                $helper->triggerOrderNotification($caseId);
            }

            // Next Date Changed notification
            if (!empty($nextDate) && !in_array($nextStatus, ['Order', 'Judgement'])) {
                $helper->triggerNextDateUpdated($caseId);
            }

            
            // header("Location: dailycaseactivities.php");
            echo "<script>location.href='index.php?pg=dailycaseactivities.php';</script>";
            exit;
        }
    }
}


// Handle Edit
if (isset($_POST['btn_update']) && isset($_POST['activity_id'])) {
    $summary = Security::sanitize($_POST['summary']);
    $nextDate = $_POST['next_date'];
    $currentStatus = Security::sanitize($_POST['current_status']);
    $nextStatus = Security::sanitize($_POST['next_status']);
    $isTaken = Security::sanitize($_POST['is_taken']);

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
    // header("Location: dailycaseactivities.php");
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

    <h4 class="text-primary">📅 Pending Hearings (Due Today or Missed)</h4>

    <?php if (!empty($casesToShow)): ?>
        <div class="table-responsive">
            <!-- <a href="index.php?pg=dailycaseactivities.php&view=e-daybook" class="btn btn-outline-secondary d-print-none float-end mb-3">
                📘 View e-DayBook
            </a> -->
            <table class="table table-bordered table-hover shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Case Name</th>
                        <th>Due Hearing Date</th>
                        <th>Actions</th>
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
    <?php endif; ?>
</div>



<!-- ADD FORM (Displayed only when btn_add_form is clicked) -->
<?php if (isset($_POST['btn_add_form'], $_POST['case_name'], $_POST['activity_date'])): ?>
    <div class="container py-4">
        <h4>Add Activity for <b><?= Security::sanitize($realCase['case_name']) ?> </b> on <i><?= Security::sanitize($_POST['activity_date']) ?></i></h4>
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
<?php if (isset($_POST['btn_edit'], $_POST['activity_id'], $_POST['case_id'])): ?>
    <?php
    $staffId = $helper->getId($systemUsertype, $systemUsername);
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
                        <option selected value="<?php echo $row['current_status'] ?>"><?php echo $row['current_status'] ?></option>
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
                        <option selected value="<?php echo $row['next_status'] ?>"><?php echo $row['next_status'] ?></option>
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
        <button onclick="window.print()" class="btn btn-outline-secondary d-print-none float-end mb-3">
            🖨️ Print / Save as PDF
        </button>

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
                                <form method="POST" action="index.php?pg=dailycaseactivities.php&option=edit" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_name']) ?>">
                                    <input type="hidden" name="activity_date" value="<?= Security::sanitize($row['activity_date']) ?>">
                                    <input type="hidden" name="activity_id" value="<?= Security::sanitize($row['activity_id']) ?>">
                                    <button type="submit" name="btn_edit" class="btn btn-sm btn-warning">✏️ Edit </button>
                                </form>
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