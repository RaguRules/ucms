<?php
$helper = new Helper($conn);
$security = new Security();
$staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);
$today = date('Y-m-d');

// Add Motion
if (isset($_POST['add_motion'])) {
    $caseId = $_POST['case_id'];
    $motionType = $_POST['motion_type'];
    $filedDate = $_POST['filed_date'];
    $filedBy = $_POST['filed_by'];

    $motionId = $helper->generateNextMotionID();
    $stmt = $conn->prepare("INSERT INTO motions (motion_id, case_id, filed_by, type, filed_date, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("sssss", $motionId, $caseId, $filedBy, $motionType, $filedDate);
    $stmt->execute();

    // Log to dailycaseactivities
    $activityId = $helper->generateNextActivityID();
    $summary = 'Motion Filed: ' . $motionType;
    $currentStatus = 'Motion';
    $nextStatus = 'Calling';
    $isTaken = 1;
    $stmt2 = $conn->prepare("INSERT INTO dailycaseactivities (activity_id, case_name, summary, next_date, current_status, next_status, is_taken, activity_date, staff_id) VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("sssssisi", $activityId, $caseId, $summary, $currentStatus, $nextStatus, $isTaken, $filedDate, $staffId);
    $stmt2->execute();

    echo "<script>alert('Motion filed successfully.'); location.href='index.php?pg=motion.php';</script>";
    exit;
}

// Update motion status
if (isset($_POST['update_status'], $_POST['motion_id'], $_POST['new_status'])) {
    $motionId = $_POST['motion_id'];
    $newStatus = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE motions SET status = ? WHERE motion_id = ?");
    $stmt->bind_param("ss", $newStatus, $motionId);
    $stmt->execute();
    echo "<script>alert('Motion status updated.'); location.href='index.php?pg=motion.php';</script>";
    exit;
}

$cases = $conn->query("SELECT case_id, case_name FROM cases");
$lawyers = $conn->query("SELECT lawyer_id, CONCAT(first_name, ' ', last_name) AS full_name FROM lawyer");
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = $searchQuery ? "WHERE c.case_name LIKE '%" . $conn->real_escape_string($searchQuery) . "%'" : '';
$motions = $conn->query("SELECT m.*, c.case_name, CONCAT(l.first_name, ' ', l.last_name) AS lawyer_name FROM motions m JOIN cases c ON m.case_id = c.case_id JOIN lawyer l ON m.filed_by = l.lawyer_id $where ORDER BY m.filed_date DESC");
?>

<div class="container py-4">
    <h3 class="text-primary">📥 File New Motion</h3>
    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Select Case</label>
            <select name="case_id" class="form-select" required>
                <option value="">-- Choose Case --</option>
                <?php while ($c = $cases->fetch_assoc()): ?>
                    <option value="<?= Security::sanitize($c['case_id']) ?>"><?= Security::sanitize($c['case_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Filed By (Lawyer)</label>
            <select name="filed_by" class="form-select" required>
                <option value="">-- Choose Lawyer --</option>
                <?php while ($l = $lawyers->fetch_assoc()): ?>
                    <option value="<?= Security::sanitize($l['lawyer_id']) ?>"><?= Security::sanitize($l['full_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Motion Type</label>
            <input type="text" name="motion_type" class="form-control" placeholder="e.g., Call Early, Postpone" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Filed Date</label>
            <input type="date" name="filed_date" class="form-control" value="<?= $today ?>" required>
        </div>
        <div class="col-12">
            <button type="submit" name="add_motion" class="btn btn-success">File Motion</button>
        </div>
    </form>

    <hr class="my-4">
    <h4 class="text-secondary">📋 Filed Motions</h4>
    <form method="GET" class="mb-3 row g-2">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by Case Name" value="<?= Security::sanitize($searchQuery) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary">Search</button>
            <a href="index.php?pg=motion.php" class="btn btn-outline-secondary">Clear</a>
        </div>
    </form>

    <form method="POST" action="lib/rep/report6.php" target="_blank" class="mb-3">
        <button type="submit" class="btn btn-dark">🖨️ Print Motions</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Case Name</th>
                    <th>Motion Type</th>
                    <th>Filed By</th>
                    <th>Filed Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($m = $motions->fetch_assoc()): ?>
                    <tr>
                        <td><?= Security::sanitize($m['case_name']) ?></td>
                        <td><?= Security::sanitize($m['type']) ?></td>
                        <td><?= Security::sanitize($m['lawyer_name']) ?></td>
                        <td><?= Security::sanitize($m['filed_date']) ?></td>
                        <td><?= Security::sanitize($m['status']) ?></td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="motion_id" value="<?= Security::sanitize($m['motion_id']) ?>">
                                <select name="new_status" class="form-select form-select-sm me-2" required>
                                    <option value="">-- Set Status --</option>
                                    <option value="Pending" <?= $m['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Approved" <?= $m['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="Rejected" <?= $m['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-outline-success">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
