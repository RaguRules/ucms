<?php
$helper = new Helper($conn);
$security = new Security();
$staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);
$today = date('Y-m-d');

// Add Motion
if (isset($_POST['add_motion'])) {
    $caseId = $_POST['case_id'];
    $purpose = trim($_POST['purpose']);
    $motionDate = $_POST['motion_date'];

    $motionId = $helper->generateNextMotionID();
    $stmt = $conn->prepare("INSERT INTO motions (motion_id, case_id, purpose, motion_date, staff_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $motionId, $caseId, $purpose, $motionDate, $staffId);
    $stmt->execute();

    // Log activity (optional)
    $activityId = $helper->generateNextActivityID();
    $summary = 'Motion filed: ' . $purpose;
    $stmt2 = $conn->prepare("INSERT INTO dailycaseactivities (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id) VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?)");
    $currentStatus = 'Motion';
    $nextStatus = 'Calling';
    $isTaken = 1;
    $stmt2->bind_param("sssssisi", $activityId, $caseId, $summary, $currentStatus, $nextStatus, $isTaken, $motionDate, $staffId);
    $stmt2->execute();

    echo "<script>alert('Motion filed successfully.'); location.href='index.php?pg=motion.php';</script>";
    exit;
}

$cases = $conn->query("SELECT case_id, case_name FROM cases");
$motions = $conn->query("SELECT m.*, c.case_name FROM motions m JOIN cases c ON m.case_id = c.case_id ORDER BY m.motion_date DESC");
?>

<div class="container py-4">
    <h3 class="text-primary">📥 File New Motion</h3>
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
        <div class="col-md-6">
            <label class="form-label">Motion Date</label>
            <input type="date" name="motion_date" class="form-control" value="<?= $today ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Purpose of Motion</label>
            <textarea name="purpose" class="form-control" rows="2" required></textarea>
        </div>
        <div class="col-12">
            <button type="submit" name="add_motion" class="btn btn-success">File Motion</button>
        </div>
    </form>

    <hr class="my-4">
    <h4 class="text-secondary">📋 Filed Motions</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Case Name</th>
                    <th>Purpose</th>
                    <th>Motion Date</th>
                    <th>Filed By</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($m = $motions->fetch_assoc()): ?>
                    <tr>
                        <td><?= Security::sanitize($m['case_name']) ?></td>
                        <td><?= Security::sanitize($m['purpose']) ?></td>
                        <td><?= Security::sanitize($m['motion_date']) ?></td>
                        <td><?= Security::sanitize($m['staff_id']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
