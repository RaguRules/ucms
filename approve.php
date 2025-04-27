<?php
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION["LOGIN_USERTYPE"])) {
    $system_usertype = $_SESSION["LOGIN_USERTYPE"];
    $system_username = $_SESSION["LOGIN_USERNAME"];
} else {
    $system_usertype = "GUEST";
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once('lib/db.php'); // Ensure db connection
include_once('lib/security.php'); // Helper functions like logError
include_once('lib/helper.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval</title>
</head>
<body class="light-mode">


<?php
$sql_read = "SELECT reg_id, first_name, last_name, nic_number, mobile, email, role_id, station, image_path, badge_number, enrolment_number, gender FROM registration";
$result = mysqli_query($conn, $sql_read);

if ($result) {
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
<!-- Page Content Starts -->
<div class="container-fluid bg-primary text-white text-center py-3">
<h1 class="text-center mt-4 mb-5">Pending Requests for Approval</h1>

<!-- LAWYERS Table -->
<div class="container mb-5">
    <h3 class="mb-3 text-primary">Lawyer Requests</h3>
    <?php if (!empty($lawyers)): ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle shadow-sm rounded border">
            <thead class="table-light">
                <tr>
                    <th>No</th><th>Photo</th><th>Full Name</th><th>AAL Regd. No</th><th>Station</th><th>NIC</th><th>Mobile</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; foreach ($lawyers as $row): ?>
                <tr>
                    <td><?= $count++; ?></td>
                    <td><img src="<?= sanitize_input($row['image_path']); ?>" class="img-thumbnail rounded-circle" style="width:100px;height:100px;object-fit:cover;" alt="Lawyer Image"></td>
                    <td><strong><?= sanitize_input($row['first_name'] . ' ' . $row['last_name']); ?></strong><br><small class="text-muted"><?= sanitize_input($row['email']); ?></small></td>
                    <td><?= sanitize_input($row['enrolment_number']); ?></td>
                    <td><?= sanitize_input($row['station']); ?></td>
                    <td><?= sanitize_input($row['nic_number']); ?></td>
                    <td><?= sanitize_input($row['mobile']); ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm btn-approve" data-regid="<?= sanitize_input($row['reg_id']); ?>"><i class="fas fa-check-circle"></i> Approve</button>
                            <button class="btn btn-danger btn-sm btn-deny" data-regid="<?= sanitize_input($row['reg_id']); ?>"><i class="fas fa-times-circle"></i> Deny</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="alert alert-info text-center">No pending lawyer requests.</div>
    <?php endif; ?>
</div>

<!-- POLICE Table -->
<div class="container mb-5">
    <h3 class="mb-3 text-primary">Police Requests</h3>
    <?php if (!empty($police)): ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle shadow-sm rounded border">
            <thead class="table-light">
                <tr>
                    <th>No</th><th>Photo</th><th>Full Name</th><th>Badge No</th><th>Station</th><th>NIC</th><th>Mobile</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; foreach ($police as $row): ?>
                <tr>
                    <td><?= $count++; ?></td>
                    <td><img src="<?= sanitize_input($row['image_path']); ?>" class="img-thumbnail rounded-circle" style="width:100px;height:100px;object-fit:cover;" alt="Police Image"></td>
                    <td><strong><?= sanitize_input($row['first_name'] . ' ' . $row['last_name']); ?></strong><br><small class="text-muted"><?= sanitize_input($row['email']); ?></small></td>
                    <td><?= sanitize_input($row['badge_number']); ?></td>
                    <td><?= sanitize_input($row['station']); ?></td>
                    <td><?= sanitize_input($row['nic_number']); ?></td>
                    <td><?= sanitize_input($row['mobile']); ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm btn-approve" data-regid="<?= sanitize_input($row['reg_id']); ?>"><i class="fas fa-check-circle"></i> Approve</button>
                            <button class="btn btn-danger btn-sm btn-deny" data-regid="<?= sanitize_input($row['reg_id']); ?>"><i class="fas fa-times-circle"></i> Deny</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="alert alert-info text-center">No pending police requests.</div>
    <?php endif; ?>
</div>

<?php
} else {
    echo "<script>alert('No pending requests.'); location.href='index.php';</script>";
    exit;
}
?>

<script>
// make CSRF token available within JS
const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function () {
            let regId = this.dataset.regid;
            if (confirm("Approve this user?")) {
                handleRequest('approve', regId);
            }
        });
    });

    document.querySelectorAll('.btn-deny').forEach(btn => {
        btn.addEventListener('click', function () {
            let regId = this.dataset.regid;
            if (confirm("Deny this user?")) {
                handleRequest('deny', regId);
            }
        });
    });

    function handleRequest(action, regId) {
        fetch('action/gatekeeper.ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=${action}&reg_id=${regId}&csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                document.querySelector(`button[data-regid="${regId}"]`).closest('tr').remove();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Unexpected Error: ' + error.message);
        });
    }
});
</script>

</body>
</html>
