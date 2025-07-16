<?php
	if ($systemUsertype != 'R01' && $systemUsertype != 'R02' ) {
	  echo "<script>location.href='index.php?pg=404.php';</script>";
	}
	
	$staffId = $helper->getId($_SESSION['LOGIN_USERNAME'], $_SESSION['LOGIN_USERTYPE']);
	
	
	if (isset($_POST['add_note'])) {
	  $caseId = $_POST['case_id'] ?? '';
	  $title = Security::sanitize($_POST['title'] ?? '');
	  $details = Security::sanitize($_POST['details'] ?? '');
	
	
	  if ($caseId && $title && $details) {
	      $noteId = $helper->generateNextNotesID();
	      $stmt = $conn->prepare("INSERT INTO notes (note_id, case_id, title, details, created_date, updated_date, is_deleted, staff_id) VALUES (?, ?, ?, ?, NOW(), NOW(), 0, ?)");
	      $stmt->bind_param("sssss", $noteId, $caseId, $title, $details, $staffId);
	      $stmt->execute();
	      $stmt->close();
	      echo "<script>alert('Note added successfully.'); location.href='index.php?pg=notes.php';</script>";
	      exit;
	  } else {
	      $error = "Please fill all fields.";
	  }
	}
	
	
	if (isset($_POST['update_note'])) {
	  $noteId = Security::sanitize($_POST['note_id']) ?? '';
	  $caseId = Security::sanitize($_POST['case_id']) ?? '';
	  $title = Security::sanitize($_POST['title']) ?? '';
	  $details = Security::sanitize($_POST['details'])?? '';
	
	  if ($noteId && $caseId && $title && $details) {
	      $stmt = $conn->prepare("UPDATE notes SET case_id=?, title=?, details=?, updated_date=NOW() WHERE note_id=? AND is_deleted=0");
	      $stmt->bind_param("ssss", $caseId, $title, $details, $noteId);
	      $stmt->execute();
	      $stmt->close();
	      echo "<script>alert('Note updated successfully.'); location.href='index.php?pg=notes.php';</script>";
	      exit;
	  } else {
	      $error = "Please fill all fields for update.";
	  }
	}
	
	
	if (isset($_GET['delete_note_id'])) {
	  $deleteNoteId = $_GET['delete_note_id'];
	  $stmt = $conn->prepare("UPDATE notes SET is_deleted=1 WHERE note_id=?");
	  $stmt->bind_param("s", $deleteNoteId);
	  $stmt->execute();
	  $stmt->close();
	  echo "<script>alert('Note deleted.'); location.href='index.php?pg=notes.php';</script>";
	  exit;
	}
	
	// --- Filters ---
	$searchQuery = trim($_GET['search'] ?? '');
	$caseFilter = $_GET['case_id'] ?? '';
	$fromDate = $_GET['from_date'] ?? '';
	$toDate = $_GET['to_date'] ?? '';
	$page = max(1, intval($_GET['page'] ?? 1));
	$perPage = 10;
	$offset = ($page - 1) * $perPage;
	
	$where = ['n.is_deleted=0'];
	if ($searchQuery) {
	  $safeSearch = $conn->real_escape_string($searchQuery);
	  $where[] = "(n.title LIKE '%$safeSearch%' OR n.details LIKE '%$safeSearch%')";
	}
	if ($caseFilter) {
	  $safeCase = $conn->real_escape_string($caseFilter);
	  $where[] = "n.case_id = '$safeCase'";
	}
	if ($fromDate && $toDate) {
	  $safeFrom = $conn->real_escape_string($fromDate);
	  $safeTo = $conn->real_escape_string($toDate);
	  $where[] = "DATE(n.created_date) BETWEEN '$safeFrom' AND '$safeTo'";
	}
	$whereSql = 'WHERE ' . implode(' AND ', $where);
	
	// --- Count for pagination ---
	$totalCount = $conn->query("SELECT COUNT(*) AS total FROM notes n $whereSql")->fetch_assoc()['total'];
	$totalPages = ceil($totalCount / $perPage);
	
	// --- Load Notes ---
	$sql = "SELECT n.note_id, n.case_id, n.title, n.details, n.created_date, n.updated_date, c.case_name 
	      FROM notes n
	      JOIN cases c ON n.case_id = c.case_id
	      $whereSql
	      ORDER BY n.updated_date DESC
	      LIMIT $offset, $perPage";
	$notes = $conn->query($sql);
	
	// --- Load Cases ---
	$cases = $conn->query("SELECT case_id, case_name FROM cases ORDER BY case_name");
	
	// --- Edit Form Load ---
	$editNoteId = $_GET['edit_note_id'] ?? '';
	$editNote = null;
	if ($editNoteId) {
	  $stmt = $conn->prepare("SELECT * FROM notes WHERE note_id=? AND is_deleted=0");
	  $stmt->bind_param("s", $editNoteId);
	  $stmt->execute();
	  $result = $stmt->get_result();
	  $editNote = $result->fetch_assoc();
	  $stmt->close();
	}
	?>
<div class="container py-4">
	<h3>📝 Judicial Notes (Case Observations)</h3>
	<!-- Filter Form -->
	<form method="GET" class="row g-3 mb-4">
		<input type="hidden" name="pg" value="notes.php">
		<div class="col-md-3">
			<label>Case</label>
			<select name="case_id" class="form-select">
				<option value="">-- All Cases --</option>
				<?php foreach ($cases as $c): ?>
				<option value="<?= Security::sanitize($c['case_id']) ?>" <?= ($caseFilter == $c['case_id']) ? 'selected' : '' ?>>
					<?= Security::sanitize($c['case_name']) ?>
				</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-3">
			<label>From Date</label>
			<input type="date" name="from_date" class="form-control" value="<?= Security::sanitize($fromDate) ?>">
		</div>
		<div class="col-md-3">
			<label>To Date</label>
			<input type="date" name="to_date" class="form-control" value="<?= Security::sanitize($toDate) ?>">
		</div>
		<div class="col-md-3">
			<label>Search</label>
			<input type="text" name="search" class="form-control" placeholder="Search title/details..." value="<?= Security::sanitize($searchQuery) ?>">
		</div>
		<div class="col-12 d-flex gap-2">
			<button type="submit" class="btn btn-primary">Filter</button>
			<a href="index.php?pg=notes.php" class="btn btn-outline-secondary">Clear</a>
		</div>
	</form>
	<!-- Notes Table -->
	<div class="table-responsive mb-4">
		<table class="table table-bordered table-striped">
			<thead class="table-light">
				<tr>
					<th>Case</th>
					<th>Title</th>
					<th>Details</th>
					<th>Created</th>
					<th>Updated</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($notes->num_rows > 0): ?>
				<?php while ($n = $notes->fetch_assoc()): ?>
				<tr>
					<td><?= Security::sanitize($n['case_name']) ?></td>
					<td><?= Security::sanitize($n['title']) ?></td>
					<td style="white-space: pre-wrap;"><?= Security::sanitize($n['details']) ?></td>
					<td><?= Security::sanitize($n['created_date']) ?></td>
					<td><?= Security::sanitize($n['updated_date']) ?></td>
					<td>
						<a href="index.php?pg=notes.php&edit_note_id=<?= urlencode($n['note_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
						<a href="index.php?pg=notes.php&delete_note_id=<?= urlencode($n['note_id']) ?>" onclick="return confirm('Delete this note?')" class="btn btn-sm btn-danger">Delete</a>
					</td>
				</tr>
				<?php endwhile; ?>
				<?php else: ?>
				<tr>
					<td colspan="6" class="text-center">No notes found.</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<!-- Pagination -->
	<?php if ($totalPages > 1): ?>
	<nav>
		<ul class="pagination">
			<?php for ($i = 1; $i <= $totalPages; $i++): 
				$queryParams = $_GET;
				$queryParams['page'] = $i;
				$url = 'index.php?' . http_build_query($queryParams);
				?>
			<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
				<a class="page-link" href="<?= $url ?>"><?= $i ?></a>
			</li>
			<?php endfor; ?>
		</ul>
	</nav>
	<?php endif; ?>
	<!-- Add/Edit Note Form -->
	<div class="card mt-5 p-4">
		<h4><?= $editNote ? '✏️ Edit Note' : '➕ Add New Note' ?></h4>
		<form method="POST" class="row g-3">
			<input type="hidden" name="note_id" value="<?= $editNote ? Security::sanitize($editNote['note_id']) : '' ?>">
			<div class="col-md-6">
				<label>Case</label>
				<select name="case_id" class="form-select" required>
					<option value="">-- Choose Case --</option>
					<?php
						$cases->data_seek(0);
						while ($c = $cases->fetch_assoc()):
						?>
					<option value="<?= Security::sanitize($c['case_id']) ?>" <?= ($editNote && $editNote['case_id'] == $c['case_id']) ? 'selected' : '' ?>>
						<?= Security::sanitize($c['case_name']) ?>
					</option>
					<?php endwhile; ?>
				</select>
			</div>
			<div class="col-md-6">
				<label>Title</label>
				<input type="text" name="title" class="form-control" required value="<?= $editNote ? Security::sanitize($editNote['title']) : '' ?>">
			</div>
			<div class="col-12">
				<label>Details</label>
				<textarea name="details" class="form-control" rows="5" required><?= $editNote ? Security::sanitize($editNote['details']) : '' ?></textarea>
			</div>
			<div class="col-12">
				<?php if ($editNote): ?>
				<button type="submit" name="update_note" class="btn btn-success">Update</button>
				<a href="index.php?pg=notes.php" class="btn btn-secondary ms-2">Cancel</a>
				<?php else: ?>
				<button type="submit" name="add_note" class="btn btn-primary">Add Note</button>
				<?php endif; ?>
			</div>
		</form>
	</div>
</div>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>