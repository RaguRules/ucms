<?php


// Define pagination parameters
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch case data with pagination
$sql = "SELECT * FROM cases ORDER BY registered_date DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total case count for pagination
$totalSql = "SELECT COUNT(*) AS total FROM cases";
$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();
$totalCases = $totalRow['total'];
$totalPages = ceil($totalCases / $limit);


if (isset($_POST["btn_full_view"]) && isset($_POST["case_id"])): ?>
<!-- Modal HTML -->
<div class="modal fade show" id="caseDetailModal" tabindex="-1" aria-labelledby="caseDetailModalLabel" style="display:block;" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="background-color: #C0C0C0;">
      <div class="modal-header">
        <h5 class="modal-title" id="caseDetailModalLabel">Case Details</h5>
        <a href="index.php?pg=cases.php" class="btn-close"></a>
      </div>
      <div class="modal-body">
        <div id="case-detail-content">
        <?php
            $caseId = Security::sanitize($_POST['case_id']);

            // Fetch case details
            $stmt = $conn->prepare("SELECT * FROM cases WHERE case_id = ?");
            $stmt->bind_param("s", $caseId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($case = $result->fetch_assoc()) {
                $caseName = $case['case_name'];
                ?>
                <div class="case-header"><?= htmlspecialchars($caseName) ?></div>
                <div class="case-row">
                  <div class="case-column">
                    <p><b>Plaintiff:</b> <?= htmlspecialchars($case['plaintiff']) ?></p>
                    <p><b>Defendant:</b> <?= htmlspecialchars($case['defendant']) ?></p>
                    <p><b>Plaintiff Lawyer:</b> <?= htmlspecialchars($case['plaintiff_lawyer']) ?></p>
                    <p><b>Defendant Lawyer:</b> <?= htmlspecialchars($case['defendant_lawyer']) ?></p>
                  </div>
                  <div class="case-column">
                    <p><b>Nature:</b> <?= htmlspecialchars($case['nature']) ?></p>
                    <p><b>Status:</b> <?= htmlspecialchars($case['status']) ?></p>
                    <p><b>Warrant:</b> <?= $case['is_warrant'] ? "<span class='badge bg-danger'>Warrant Issued</span>" : "No Warrant" ?></p>
                    <p><b>Registered Date:</b> <?= htmlspecialchars($case['registered_date']) ?></p>
                  </div>
                </div>

                <div class="activities-section mt-3">
                    <h5>Daily Case Activities</h5>
                    <?php
                    $stmt2 = $conn->prepare("SELECT * FROM dailycaseactivities WHERE case_name = ? ORDER BY activity_date DESC");
                    $stmt2->bind_param("s", $caseName);
                    $stmt2->execute();
                    $activities = $stmt2->get_result();

                    if ($activities->num_rows > 0) {
                        while ($activity = $activities->fetch_assoc()) {
                            ?>
                            <div class="card mb-2 p-3 shadow-sm">
                                <h6><?= htmlspecialchars($activity['activity_date']) ?></h6>
                                <p><strong>Summary:</strong> <?= htmlspecialchars($activity['summary']) ?></p>
                                <p><strong>Next Date:</strong> <?= htmlspecialchars($activity['next_date']) ?></p>
                                <p><strong>Next Steps:</strong> <?= htmlspecialchars($activity['next_status']) ?></p>
                            </div>
                        <?php }
                    } else {
                        echo "<p>No activities available.</p>";
                    }

                    $stmt2->close();
                } else {
                    echo "<p>Case not found.</p>";
                }

                $stmt->close();
                ?>
        </div>
      </div>
      <div class="modal-footer">
        <a href="index.php?pg=cases.php" class="btn btn-secondary">Close</a>
      </div>
    </div>
  </div>
</div>

<!-- Auto trigger modal script -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const modalEl = document.getElementById('caseDetailModal');
    if (modalEl) {
      var myModal = new bootstrap.Modal(modalEl);
      myModal.show();
    }
  });
</script>
<?php endif;
?>










<div class="container py-5">
    <h3 class="mb-4">Case Management</h3>
    <div class="table-responsive">
        <table id="casesTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Case Name</th>
                    <th>Plaintiff</th>
                    <th>Defendant</th>
                    <th>Lawyers</th>
                    <th>Registered Date</th>
                    <th>Status</th>
                    <th>Warrant</th>
                    <th>Next Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                            <?php
                            $serial = ($page - 1) * $limit + 1;
                            while ($row = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= $serial++ ?></td>
            
                                    <td><?= Security::sanitize($row['case_name']) ?></td>
                                    <td><?= Security::sanitize($row['plaintiff']) ?></td>
                                    <td><?= Security::sanitize($row['defendant']) ?></td>
                                    <td><?= Security::sanitize($row['plaintiff_lawyer']) ?> / <?= Security::sanitize($row['defendant_lawyer']) ?></td>
                                    <td><?= Security::sanitize($row['registered_date']) ?></td>
                                    <td>
                                        <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $row['is_warrant'] ? 'bg-danger' : 'bg-secondary' ?>">
                                            <?= $row['is_warrant'] ? 'Issued' : 'Not Issued' ?>
                                        </span>
                                    </td>
                                    <td><?= Security::sanitize($row['next_date']) ?></td>
                                    <td>
                                        <a href="edit_case.php?id=<?= Security::sanitize($row['case_id']) ?>" class="btn btn-warning btn-sm">
                                            Edit
                                        </a>
                                        <form method="POST" action="index.php?pg=cases.php&option=fullview" class="d-inline">
                                            <input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_id']) ?>">
                                            <button type="submit" name="btn_full_view" class="btn btn-sm text-white" style="background-color: #6f42c1;">
                                                Full View
                                            </button>
                                        </form>



                                        <form method="POST" action="#" class="d-inline">
                                            <input type="hidden" name="case_id" value="<?= Security::sanitize($row['case_id']) ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this case?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('casesTable');
    const headers = table.querySelectorAll('th');
    const rows = table.querySelectorAll('tbody tr');

    // Sorting functionality
    headers.forEach((header, index) => {
        header.addEventListener('click', () => {
            const isAscending = header.classList.contains('asc');
            header.classList.toggle('asc', !isAscending);
            header.classList.toggle('desc', isAscending);

            const sortedRows = Array.from(rows).sort((rowA, rowB) => {
                const cellA = rowA.cells[index].innerText.trim();
                const cellB = rowB.cells[index].innerText.trim();

                return isAscending
                    ? cellA.localeCompare(cellB)
                    : cellB.localeCompare(cellA);
            });

            sortedRows.forEach(row => table.querySelector('tbody').appendChild(row));
        });
    });

    // Filtering functionality
    const filterInput = document.createElement('input');
    filterInput.type = 'text';
    filterInput.placeholder = 'Search cases...';
    filterInput.classList.add('form-control', 'mb-3');
    table.parentElement.insertBefore(filterInput, table);

    filterInput.addEventListener('input', () => {
        const query = filterInput.value.toLowerCase();
        rows.forEach(row => {
            const cells = Array.from(row.cells);
            const isMatch = cells.some(cell =>
                cell.innerText.toLowerCase().includes(query)
            );
            row.style.display = isMatch ? '' : 'none';
        });
    });
});
</script>