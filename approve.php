<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Staff Page</title>
	</head>
	<body class="light-mode">
		<?php		
			$sql_read = "SELECT reg_id, first_name, last_name, nic_number, mobile, email, role_id, station, image_path, badge_number, enrolment_number FROM registration";
			$result = mysqli_query($conn, $sql_read);
			
			if ($result && mysqli_num_rows($result) >= 0) {
			
			$lawyers = [];
			$police = [];
			
			while ($row = mysqli_fetch_assoc($result)) {
				if ($row['role_id'] == 'R06') {
					$lawyers[] = $row;
				} elseif ($row['role_id'] == 'R07') {
					$police[] = $row;
				}
			}
			?>
		<div class="container-fluid bg-primary text-white text-center py-3">
		<h1 class="text-center mt-4 mb-5">Pending Requests for Approval</h1>
		<!-- ===== LAWYERS ===== -->
		<div class="container mb-5">
			<h3 class="mb-3 text-primary">Lawyer Requests</h3>
			<?php if (!empty($lawyers)): ?>
			<div class="table-responsive">
				<table class="table table-hover align-middle shadow-sm rounded border">
					<thead class="table-light">
						<tr>
							<th>No</th>
							<th>Photo</th>
							<th>Full Name</th>
							<th>AAL Regd. No</th>
							<th>Station</th>
							<th>NIC</th>
							<th>Mobile</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; foreach ($lawyers as $row): ?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td>
								<img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" alt="Lawyer Image">
							</td>
							<td>
								<strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong><br>
								<small class="text-muted"><?php echo $row['email']; ?></small>
							</td>
							<td><?php echo htmlspecialchars($row['enrolment_number']); ?></td>
							<td><?php echo htmlspecialchars($row['station']); ?></td>
							<td><?php echo htmlspecialchars($row['nic_number']); ?></td>
							<td><?php echo htmlspecialchars($row['mobile']); ?></td>
							<td>
								<div class="d-flex gap-2">
									<button class="btn btn-success btn-sm btn-approve" 
										data-regid="<?php echo htmlspecialchars($row['reg_id']); ?>">
									<i class="fas fa-check-circle"></i> Approve
									</button>
									<button class="btn btn-danger btn-sm btn-deny" 
										data-regid="<?php echo htmlspecialchars($row['reg_id']); ?>">
									<i class="fas fa-times-circle"></i> Deny
									</button>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<div class="alert alert-info text-center mt-3" role="alert">
				<i class="bi bi-info-circle-fill me-2"></i>
				No pending lawyer requests.
			</div>
			<?php endif; ?>
		</div>
		<!-- ===== POLICE ===== -->
		<div class="container mb-5">
			<h3 class="mb-3 text-primary">Police Requests</h3>
			<?php if (!empty($police)): ?>
			<div class="table-responsive">
				<table class="table table-hover align-middle shadow-sm rounded border">
					<thead class="table-light">
						<tr>
							<th>No</th>
							<th>Photo</th>
							<th>Full Name</th>
							<th>Badge No</th>
							<th>Station</th>
							<th>NIC</th>
							<th>Mobile</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; foreach ($police as $row): ?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td>
								<img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" alt="Police Image">
							</td>
							<td>
								<strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong><br>
								<small class="text-muted"><?php echo $row['email']; ?></small>
							</td>
							<td><?php echo htmlspecialchars($row['badge_number']); ?></td>
							<td><?php echo htmlspecialchars($row['station']); ?></td>
							<td><?php echo htmlspecialchars($row['nic_number']); ?></td>
							<td><?php echo htmlspecialchars($row['mobile']); ?></td>
							<td>
								<div class="d-flex gap-2">
									<button class="btn btn-success btn-sm btn-approve" 
										data-regid="<?php echo htmlspecialchars($row['reg_id']); ?>">
									<i class="fas fa-check-circle"></i> Approve
									</button>
									<button class="btn btn-danger btn-sm btn-deny" 
										data-regid="<?php echo htmlspecialchars($row['reg_id']); ?>">
									<i class="fas fa-times-circle"></i> Deny
									</button>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<div class="alert alert-info text-center mt-3" role="alert">
				<i class="bi bi-info-circle-fill me-2"></i>
				No pending police requests.
			</div>
			<?php endif; ?>
		</div>
		<?php
			}else{
				echo "<script> alert('No pending requests'); </script>";
				echo "<script> location.href='index.php'; </script>";
				exit; 
			}
			?>
	</body>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
		   document.querySelectorAll('.btn-approve').forEach(btn => {
		       btn.addEventListener('click', function () {
		           let regId = this.dataset.regid;
		           if (confirm("Are you sure you want to approve this user?")) {
		               handleRequest('approve', regId);
		           }
		       });
		   });
		
		   document.querySelectorAll('.btn-deny').forEach(btn => {
		       btn.addEventListener('click', function () {
		           let regId = this.dataset.regid;
		           if (confirm("Are you sure you want to deny this user?")) {
		               handleRequest('deny', regId);
		           }
		       });
		   });
		
		   function handleRequest(action, regId) {
		       fetch('approve_deny_action.php', {
		           method: 'POST',
		           headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		           body: `action=${action}&reg_id=${regId}`
		       })
		       .then(response => response.json())
		       .then(data => {
		           if (data.success) {
		               alert(data.message);
		               // Optionally remove row from table
		               document.querySelector(`button[data-regid="${regId}"]`).closest('tr').remove();
		           } else {
		               alert('Error: ' + data.message);
		           }
		       });
		   }
		});
	</script>
</html>