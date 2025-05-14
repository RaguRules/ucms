<?php

	// Flash message system
	$message = $_SESSION['message'] ?? null;
	unset($_SESSION['message']);
	
	// Handle POST requests
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	    // Validate CSRF token
	    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	        die("Invalid CSRF token");
	    }
	
	    // Add court logic
	    if (isset($_POST['court_name'])) {
	        $court_name = Security::sanitize($_POST['court_name']);
	        $courtId = $helper->generateNextCourtID($conn); // Always generate server-side

	        // Check for existing court_id (defensive)
	        $stmt = $conn->prepare("SELECT 1 FROM courts WHERE court_id = ?");
	        $stmt->bind_param("s", $courtId);
	        $stmt->execute();
	        $stmt->store_result();
	
	        if ($stmt->num_rows > 0) {
	            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Court ID already exists!'];
	        } else {
	            $insert = $conn->prepare("INSERT INTO courts (court_id, court_name, court_status) VALUES (?, ?, 1)");
	            $insert->bind_param("ss", $courtId, $court_name);
	            if ($insert->execute()) {
	                $_SESSION['message'] = ['type' => 'success', 'text' => 'Court added successfully.'];
	            } else {
	                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Database error: ' . $conn->error];
	            }
	        }
			echo "<script> location.href='index.php?pg=courts.php'; </script>";
	        exit;
	    }

        if (isset($_POST["btn_rename"])) {
            // Sanitize inputs
            $txtCourtId = Security::sanitize($_POST["court_id"]);
            $txtCourtName = Security::sanitize($_POST["new_court_name"]);

            // Check for CSRF Tokens
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Invalid CSRF token.");
            }

            // Begin transaction
            $conn->begin_transaction();

            try {
                $stmtUpdate = $conn->prepare("UPDATE courts SET court_name=? WHERE court_id=?");

                $stmtUpdate->bind_param(
                    "ss",
                    $txtCourtName,
                    $txtCourtId
                );
                $stmtUpdate->execute();

                $conn->commit();

                echo '<script>alert("Successfully updated a Court.");</script>';
                echo "<script>location.href='index.php?pg=courts.php&option=view';</script>";
                exit;
            } catch (Exception $e) {
                $conn->rollback();

                Security::logError($e->getMessage());
                echo '<script>alert("An error occurred while updating. Please try again.");</script>';
            }
        }
	
	    // Toggle status logic
	    if (isset($_POST['toggle_status'], $_POST['court_id'])) {
	        $courtId = $_POST['court_id'];

	        $stmt = $conn->prepare("SELECT court_status FROM courts WHERE court_id = ?");
	        $stmt->bind_param("s", $courtId);
	        $stmt->execute();
	        $result = $stmt->get_result();
	
	        if ($row = $result->fetch_assoc()) {
	            $newStatus = ($row['court_status'] == 1) ? 0 : 1;
	            $update = $conn->prepare("UPDATE courts SET court_status = ? WHERE court_id = ?");
	            $update->bind_param("is", $newStatus, $courtId);
	            $update->execute();
	            $_SESSION['message'] = ['type' => 'info', 'text' => 'Status updated.'];
	        }

			echo "<script> location.href='index.php?pg=courts.php'; </script>";
	        exit;
	    }
	}
	
	// Fetch courts
	$result = $conn->query("SELECT * FROM courts ORDER BY court_id ASC");
	
	// Generate next ID
	$nextCourtId = $helper->generateNextCourtID($conn);
	?>

	
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Available Courts in the Complex</title>
	</head>
	<body>
		<div class="container mt-5">
			<!-- Show flash message -->
			<?php if ($message): ?>
			<div class="alert alert-<?= Security::sanitize($message['type']) ?>">
				<?= Security::sanitize($message['text']) ?>
			</div>
			<?php endif; ?>

			<!-- Add Court Button -->
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourtModal">
			Add Court
			</button>

			<!-- Courts Table -->
			<table class="table mt-3">
				<thead>
					<tr>
						<th>Court ID</th>
						<th>Court Name</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($row = $result->fetch_assoc()): ?>
					<tr>
						<td><?= Security::sanitize($row['court_id']) ?></td>
						<td><?= Security::sanitize($row['court_name']) ?></td>
						<td><?= $row['court_status'] ? 'active' : 'inactive' ?></td>
						<td>
							<form method="POST" action="index.php?pg=courts.php" style="display:inline;">
								<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
								<input type="hidden" name="toggle_status" value="1">
								<input type="hidden" name="court_id" value="<?= Security::sanitize($row['court_id']) ?>">
								<?php if ($row['court_status']): ?>
								<!-- Deactivate Button - Red -->
								<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to deactivate this court?');">
								Deactivate
								</button>
								<?php else: ?>
								<!-- Activate Button - Green -->
								<button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to activate this court?');">
								Activate
								</button>
								<?php endif; ?>
							</form>
                           <?php if (!in_array($row['court_id'], ['C01', 'C02', 'C03', 'C04'])): ?>
                                <button 
                                    type="button" 
                                    class="btn btn-info btn-sm text-white rename-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#renameModal"
                                    data-court-id="<?= Security::sanitize($row['court_id']) ?>">
                                    <i class="fas fa-edit"></i> Rename
                                </button>
                            <?php endif; ?>
						</td>
					</tr>
					<?php endwhile; ?>
				</tbody>
			</table>

			<!-- Add Court Modal -->
			<div class="modal fade" id="addCourtModal" tabindex="-1" aria-labelledby="addCourtModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<form method="POST" action="index.php?pg=courts.php" class="modal-content">
						<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
						<div class="modal-header">
							<h5 class="modal-title" id="addCourtModalLabel">Add New Court</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="court_id" class="form-label">Court ID</label>
								<input type="text" value="<?= Security::sanitize($nextCourtId) ?>"
									class="form-control" name="court_id_display" readonly disabled>
								<!-- Hidden, generated server-side -->
							</div>
							<div class="mb-3">
								<label for="court_name" class="form-label">Court Name</label>
								<input type="text" class="form-control" name="court_name" required>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-success">Add Court</button>
						</div>
					</form>
				</div>
			</div>
		</div>

        <!-- Rename Court Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="index.php?pg=courts.php&option=rename">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="renameModalLabel">Rename Court ID</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="court_id" id="CourtId">
                    <div class="mb-3">
                        <label for="newCourtName" class="form-label">New Court Name</label>
                        <input type="text" name="new_court_name" id="newCourtName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button name="btn_rename" id="btn_rename" value='1' type="submit" class="btn btn-success">Rename Court</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const renameButtons = document.querySelectorAll('.rename-btn');
    const courtIdInput = document.getElementById('CourtId');

    renameButtons.forEach(button => {
        button.addEventListener('click', function () {
            const courtId = this.getAttribute('data-court-id');
            courtIdInput.value = courtId;
        });
    });
});
</script>

	</body>
</html>