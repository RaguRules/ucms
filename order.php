<?php
$helper = new Helper($conn);
$security = new Security();
$staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);
$today = date('Y-m-d');

// Add Order Entry
if (isset($_POST['add_order'])) {
    $caseId = $_POST['case_id'];
    $isCalculated = isset($_POST['is_calculated']) ? 1 : 0;
    $givenOn = $_POST['given_on'];

    $orderId = $helper->generateNextOrderID();
    $stmt = $conn->prepare("INSERT INTO orders (order_id, case_id, is_calculated, given_on, staff_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $orderId, $caseId, $isCalculated, $givenOn, $staffId);
    $stmt->execute();

    // Insert activity log
    $activityId = $helper->generateNextActivityID();
    $summary = 'Order Issued';
    $currentStatus = 'Order';
    $nextStatus = 'Calling'; // or whatever next action may be
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
    <h3 class="text-primary">➕ Add New Order</h3>
    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Select Case</label>
            <select name="case_id" class="form-select" required>
                <option value="">-- Choose Case --</option>
                <?php while ($c = $cases->fetch_assoc()): ?>
                    <option value="<?= $c['case_id'] ?>"><?= htmlspecialchars($c['case_name']) ?></option>
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
    <h4 class="text-secondary">📄 Orders History</h4>
    <form method="POST" action="lib/rep/report5.php" target="_blank" class="mb-3">
        <button type="submit" name="print_order_report" class="btn btn-outline-dark">🖨️ Print A4 Report</button>
    </form>
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
                <?php while ($r = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['case_name']) ?></td>
                        <td><?= htmlspecialchars($r['plaintiff']) ?></td>
                        <td><?= htmlspecialchars($r['defendant']) ?></td>
                        <td><?= htmlspecialchars($r['given_on']) ?></td>
                        <td><?= $r['is_calculated'] ? 'Yes' : 'No' ?></td>
                        <td><?= htmlspecialchars($r['staff_id']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
