<?php
	if($systemUsertype == 'GUEST'){
	    echo "<script>location.href='index.php?pg=404.php';</script>";
	}
	
	// Toggle Warrant
	if (isset($_POST['toggle_warrant']) && isset($_POST['case_id'])) {
	    $caseId = $_POST['case_id'];
	    $current = (int) $_POST['current_value'];
	    $newValue = $current ? 0 : 1;
	
	    $stmt = $conn->prepare("UPDATE cases SET is_warrant = ? WHERE case_id = ?");
	    $stmt->bind_param("is", $newValue, $caseId);
	    $stmt->execute();
	
	    echo "<script>location.href='index.php?pg=warrant.php&option=view';</script>";
	    exit;
	}
	
	// Search logic
	$searchTerm = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : null;
	if ($searchTerm) {
	    $stmt = $conn->prepare("SELECT * FROM cases WHERE case_name LIKE ?");
	    $stmt->bind_param("s", $searchTerm);
	    $stmt->execute();
	    $result = $stmt->get_result();
	} else {
	    $stmt = $conn->prepare("SELECT * FROM cases WHERE is_warrant = 1");
	    $stmt->execute();
	    $result = $stmt->get_result();
	}
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): 
                $plaintiffData = $helper->getPartyData($row['plaintiff']);
                $plaintiff = $plaintiffData ? $plaintiffData['first_name'] . ' ' . $plaintiffData['last_name'] : 'Unknown';
                $defendantData = $helper->getPartyData($row['defendant']);
                $defendant = $defendantData ? $defendantData['first_name'] . ' ' . $defendantData['last_name'] : 'Unknown';
                ?>
            <td><?= Security::sanitize($row['case_name']) ?></td>
            <td><?= Security::sanitize($plaintiff) ?></td>
            <td><?= Security::sanitize($defendant) ?></td>
            <td><?= Security::sanitize($row['status']) ?></td>
            <td>
                <span class="badge <?= $row['is_warrant'] ? 'bg-danger' : 'bg-success' ?>">
                <?= $row['is_warrant'] ? 'Yes' : 'No' ?>
                </span>
            </td>
            <td><?= Security::sanitize($row['next_date']) ?></td>
            <td>
                <?php
                    if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04') {
                    ?>
                <form method="POST" action="index.php?pg=warrant.php&option=view" onsubmit="return confirm('Toggle warrant status for this case?')">
                    <input type="hidden" name="case_id" value="<?= $row['case_id'] ?>">
                    <input type="hidden" name="current_value" value="<?= $row['is_warrant'] ?>">
                    <button type="submit" name="toggle_warrant" class="btn btn-sm <?= $row['is_warrant'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                    <?= $row['is_warrant'] ? 'Remove Warrant' : 'Add Warrant' ?>
                    </button>
                </form>
                <?php
                    }
                    ?>
            </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
	<hr>
	<?php
		if ($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04'|| $systemUsertype == 'R05') {
	?>

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
		<div class="col-md-12">
			<button type="submit" name="toggle_warrant" class="btn btn-primary">Add Warrant</button>
		</div>
	</form>

	<?php
		}
	?>
</div>