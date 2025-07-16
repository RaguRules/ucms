<?php

	if ($systemUsertype != 'R01'){
		echo "<script>location.href='index.php?pg=404.php';</script>";
	}

	// Flash message system
	$message = $_SESSION['message'] ?? null;
	unset($_SESSION['message']);
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	        die("Invalid CSRF token");
	    }
	
	    if (isset($_POST['role_name'])) {
	        $role_name = Security::sanitize($_POST['role_name']);
	        $roleId = $helper->generateNextRoleID($conn); // Always generate server-side
	
	        $stmt = $conn->prepare("SELECT 1 FROM roles WHERE role_id = ?");
	        $stmt->bind_param("s", $roleId);
	        $stmt->execute();
	        $stmt->store_result();
	
	        if ($stmt->num_rows > 0) {
	            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Role ID already exists!'];
	        } else {
	            $insert = $conn->prepare("INSERT INTO roles (role_id, role_name, role_status) VALUES (?, ?, 1)");
	            $insert->bind_param("ss", $roleId, $role_name);
	            if ($insert->execute()) {
	                $_SESSION['message'] = ['type' => 'success', 'text' => 'Role added successfully.'];
	            } else {
	                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Database error: ' . $conn->error];
	            }
	        }
			echo "<script> location.href='index.php?pg=roles.php'; </script>";
	        exit;
	    }

		if (isset($_POST["btn_rename"])) {
            $txtRoleId = Security::sanitize($_POST["role_id"]);
            $txtRoleName = Security::sanitize($_POST["new_role_name"]);

            $conn->begin_transaction();

            try {
                $stmtUpdate = $conn->prepare("UPDATE roles SET role_name=? WHERE role_id=?");

                $stmtUpdate->bind_param(
                    "ss",
                    $txtRoleName,
                    $txtRoleId
                );
                $stmtUpdate->execute();

                $conn->commit();

                echo '<script>alert("Successfully updated a Role.");</script>';
                echo "<script>location.href='index.php?pg=roles.php&option=view';</script>";
                exit;
            } catch (Exception $e) {
                $conn->rollback();

                Security::logError($e->getMessage());
                echo '<script>alert("An error occurred while updating. Please try again.");</script>';
            }
        }
	
	    if (isset($_POST['toggle_status'], $_POST['role_id'])) {
	        $roleId = $_POST['role_id'];
	
			// Prevent deactivation of R01 (Admin)
			if ($roleId === 'R01') {
				$_SESSION['message'] = ['type' => 'warning', 'text' => 'Administrator role cannot be deactivated.'];
				echo "<script> location.href='index.php?pg=roles.php'; </script>";
				exit;
			}
	
	        $stmt = $conn->prepare("SELECT role_status FROM roles WHERE role_id = ?");
	        $stmt->bind_param("s", $roleId);
	        $stmt->execute();
	        $result = $stmt->get_result();
	
	        if ($row = $result->fetch_assoc()) {
	            $newStatus = ($row['role_status'] == 1) ? 0 : 1;
	            $update = $conn->prepare("UPDATE roles SET role_status = ? WHERE role_id = ?");
	            $update->bind_param("is", $newStatus, $roleId);
	            $update->execute();
	            $_SESSION['message'] = ['type' => 'info', 'text' => 'Status updated.'];
	        }
	
			echo "<script> location.href='index.php?pg=roles.php'; </script>";
	        exit;
	    }
	}	
	
	// Fetch roles
	$result = $conn->query("SELECT * FROM roles ORDER BY role_id ASC");
	
	$nextRoleId = $helper->generateNextRoleID($conn);
	?>

	
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Roles</title>
	</head>
	<body>
		<div class="container mt-5">
			<!-- Show flash message -->
			<?php if ($message): ?>
			<div class="alert alert-<?= Security::sanitize($message['type']) ?>">
				<?= Security::sanitize($message['text']) ?>
			</div>
			<?php endif; ?>

			<!-- Add Role Button -->
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
			Add Role
			</button>

			<!-- Roles Table -->
			<table class="table mt-3">
				<thead>
					<tr>
						<th>Role ID</th>
						<th>Role Name</th>
						<th>Status</th>
						<?php
						if ($systemUsertype == 'R01') {
						?>
						<th>Actions</th>
						<?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php while ($row = $result->fetch_assoc()): ?>
					<tr>
						<td><?= Security::sanitize($row['role_id']) ?></td>
						<td><?= Security::sanitize($row['role_name']) ?></td>
						<td><?= $row['role_status'] ? 'active' : 'inactive' ?></td>
						
						<td>
							<?php if ($row['role_id'] !== 'R01'): ?>
							<form method="POST" action="index.php?pg=roles.php" style="display:inline;">
								<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
								<input type="hidden" name="toggle_status" value="1">
								<input type="hidden" name="role_id" value="<?= Security::sanitize($row['role_id']) ?>">
								<?php if ($row['role_status']): ?>
								<!-- Deactivate Button - Red -->
								<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to deactivate this role?');">
								Deactivate
								</button>
								<?php else: ?>
								<!-- Activate Button - Green -->
								<button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to activate this role?');">
								Activate
								</button>
								<?php endif; ?>
							</form>
							<?php endif; ?>
							<?php if (!in_array($row['role_id'], ['R01', 'R02', 'R03', 'R04', 'R05', 'R06', 'R07'])): ?>
                                <button 
                                    type="button" 
                                    class="btn btn-info btn-sm text-white rename-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#renameModal"
                                    data-role-id="<?= Security::sanitize($row['role_id']) ?>">
                                    <i class="fas fa-edit"></i> Rename
                                </button>
                            <?php endif; ?>
						</td>
					</tr>
					<?php endwhile; ?>
				</tbody>
			</table>

			<!-- Add Role Modal -->
			<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<form method="POST" action="index.php?pg=roles.php" class="modal-content">
						<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
						<div class="modal-header">
							<h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="role_id" class="form-label">Role ID</label>
								<input type="text" value="<?= Security::sanitize($nextRoleId) ?>"
									class="form-control" name="role_id_display" readonly disabled>
							</div>
							<div class="mb-3">
								<label for="role_name" class="form-label">Role Name</label>
								<input type="text" class="form-control" name="role_name" required>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-success">Add Role</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		
 <!-- Rename Role Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="index.php?pg=roles.php&option=rename">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="renameModalLabel">Rename Role ID</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="role_id" id="RoleId">
                    <div class="mb-3">
                        <label for="newRoleName" class="form-label">New Role Name</label>
                        <input type="text" name="new_role_name" id="newRoleName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button name="btn_rename" id="btn_rename" value='1' type="submit" class="btn btn-success">Rename Role</button>
                </div>
            </div>
        </form>
    </div>
</div>

	<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		const renameButtons = document.querySelectorAll('.rename-btn');
		const roleIdInput = document.getElementById('RoleId');

		renameButtons.forEach(button => {
			button.addEventListener('click', function () {
				const roleId = this.getAttribute('data-role-id');
				roleIdInput.value = roleId;
			});
		});
	});
</script>

</body>
</html>

