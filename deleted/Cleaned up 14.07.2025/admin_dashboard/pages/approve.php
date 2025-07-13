<?php
// Approve registrations page for Administrator role
include_once('includes/rbac.php');

// Check if user has permission to view this page
if (!checkPermission('approve', 'view')) {
    header("Location: index.php");
    exit;
}

// Process approval/denial
$successMessage = '';
$errorMessage = '';

if (isset($_POST['approve_registration']) && checkPermission('approve', 'approve')) {
    $registrationId = sanitizeInput($conn, $_POST['registration_id']);
    
    // Get registration details
    $sql = "SELECT * FROM registration WHERE registration_id = '$registrationId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $registration = mysqli_fetch_assoc($result);
        
        // Update registration status
        $sql = "UPDATE registration SET status = 'Approved', approved_by = '{$_SESSION['USER_ID']}', approved_date = NOW() WHERE registration_id = '$registrationId'";
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            // Create login account
            $username = $registration['email'];
            $password = $registration['password']; // In production, this should be hashed
            $roleId = $registration['role_id'];
            
            $sql = "INSERT INTO login (username, password, status, role_id) VALUES ('$username', '$password', 'Active', '$roleId')";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                // Create user record in appropriate table
                if (in_array($roleId, [ROLE_ADMIN, ROLE_JUDGE, ROLE_REGISTRAR, ROLE_INTERPRETER, ROLE_STAFF])) {
                    // Generate staff ID
                    $sql = "SELECT MAX(SUBSTRING(staff_id, 2)) as max_id FROM staff";
                    $idResult = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($idResult);
                    $nextId = 'S' . str_pad(($row['max_id'] + 1), 4, '0', STR_PAD_LEFT);
                    
                    // Insert staff record
                    $sql = "INSERT INTO staff (staff_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, court_id, joined_date, role_id, is_active) 
                            VALUES ('$nextId', '{$registration['first_name']}', '{$registration['last_name']}', '{$registration['mobile']}', '{$registration['email']}', '{$registration['address']}', '{$registration['nic_number']}', '{$registration['date_of_birth']}', '{$registration['court_id']}', NOW(), '$roleId', 1)";
                } elseif ($roleId == ROLE_LAWYER) {
                    // Generate lawyer ID
                    $sql = "SELECT MAX(SUBSTRING(lawyer_id, 2)) as max_id FROM lawyer";
                    $idResult = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($idResult);
                    $nextId = 'L' . str_pad(($row['max_id'] + 1), 4, '0', STR_PAD_LEFT);
                    
                    // Insert lawyer record
                    $sql = "INSERT INTO lawyer (lawyer_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, enrolment_number, joined_date, role_id, is_active) 
                            VALUES ('$nextId', '{$registration['first_name']}', '{$registration['last_name']}', '{$registration['mobile']}', '{$registration['email']}', '{$registration['address']}', '{$registration['nic_number']}', '{$registration['date_of_birth']}', '{$registration['enrolment_number']}', NOW(), '$roleId', 1)";
                } elseif ($roleId == ROLE_POLICE) {
                    // Generate police ID
                    $sql = "SELECT MAX(SUBSTRING(police_id, 2)) as max_id FROM police";
                    $idResult = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($idResult);
                    $nextId = 'P' . str_pad(($row['max_id'] + 1), 4, '0', STR_PAD_LEFT);
                    
                    // Insert police record
                    $sql = "INSERT INTO police (police_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, badge_number, station, joined_date, role_id, is_active) 
                            VALUES ('$nextId', '{$registration['first_name']}', '{$registration['last_name']}', '{$registration['mobile']}', '{$registration['email']}', '{$registration['address']}', '{$registration['nic_number']}', '{$registration['date_of_birth']}', '{$registration['badge_number']}', '{$registration['station']}', NOW(), '$roleId', 1)";
                }
                
                $result = mysqli_query($conn, $sql);
                
                if ($result) {
                    $successMessage = 'Registration approved successfully.';
                } else {
                    $errorMessage = 'Error creating user record: ' . mysqli_error($conn);
                }
            } else {
                $errorMessage = 'Error creating login account: ' . mysqli_error($conn);
            }
        } else {
            $errorMessage = 'Error updating registration status: ' . mysqli_error($conn);
        }
    } else {
        $errorMessage = 'Registration not found.';
    }
}

if (isset($_POST['deny_registration']) && checkPermission('approve', 'deny')) {
    $registrationId = sanitizeInput($conn, $_POST['registration_id']);
    $reason = sanitizeInput($conn, $_POST['denial_reason']);
    
    // Update registration status
    $sql = "UPDATE registration SET status = 'Denied', denial_reason = '$reason', approved_by = '{$_SESSION['USER_ID']}', approved_date = NOW() WHERE registration_id = '$registrationId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $successMessage = 'Registration denied successfully.';
    } else {
        $errorMessage = 'Error updating registration status: ' . mysqli_error($conn);
    }
}
?>

<div class="approve-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="page-title">Approve Registrations</h1>
            </div>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $successMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $errorMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Pending Registrations -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Pending Registrations</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT r.*, ro.role_name FROM registration r 
                                LEFT JOIN roles ro ON r.role_id = ro.role_id 
                                WHERE r.status = 'Pending' ORDER BY r.registration_date DESC";
                        $result = mysqli_query($conn, $sql);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $row['registration_id']; ?></td>
                            <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['role_name']; ?></td>
                            <td><?php echo formatDate($row['registration_date']); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['registration_id']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <?php if (checkPermission('approve', 'approve')): ?>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $row['registration_id']; ?>">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if (checkPermission('approve', 'deny')): ?>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#denyModal<?php echo $row['registration_id']; ?>">
                                        <i class="fas fa-times"></i> Deny
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo $row['registration_id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $row['registration_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="viewModalLabel<?php echo $row['registration_id']; ?>">Registration Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>First Name</th>
                                                                <td><?php echo $row['first_name']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Last Name</th>
                                                                <td><?php echo $row['last_name']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Email</th>
                                                                <td><?php echo $row['email']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Mobile</th>
                                                                <td><?php echo $row['mobile']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Address</th>
                                                                <td><?php echo $row['address']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>NIC Number</th>
                                                                <td><?php echo $row['nic_number']; ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Date of Birth</th>
                                                                <td><?php echo formatDate($row['date_of_birth']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Role</th>
                                                                <td><?php echo $row['role_name']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Registration Date</th>
                                                                <td><?php echo formatDate($row['registration_date']); ?></td>
                                                            </tr>
                                                            <?php if (!empty($row['court_id'])): ?>
                                                            <tr>
                                                                <th>Court</th>
                                                                <td><?php echo getCourtName($row['court_id']); ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                            <?php if (!empty($row['enrolment_number'])): ?>
                                                            <tr>
                                                                <th>Enrolment Number</th>
                                                                <td><?php echo $row['enrolment_number']; ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                            <?php if (!empty($row['badge_number'])): ?>
                                                            <tr>
                                                                <th>Badge Number</th>
                                                                <td><?php echo $row['badge_number']; ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                            <?php if (!empty($row['station'])): ?>
                                                            <tr>
                                                                <th>Police Station</th>
                                                                <td><?php echo $row['station']; ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal<?php echo $row['registration_id']; ?>" tabindex="-1" aria-labelledby="approveModalLabel<?php echo $row['registration_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveModalLabel<?php echo $row['registration_id']; ?>">Confirm Approval</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to approve the registration for <strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form method="POST" action="index.php?page=approve">
                                                    <input type="hidden" name="registration_id" value="<?php echo $row['registration_id']; ?>">
                                                    <button type="submit" name="approve_registration" class="btn btn-success">Approve</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Deny Modal -->
                                <div class="modal fade" id="denyModal<?php echo $row['registration_id']; ?>" tabindex="-1" aria-labelledby="denyModalLabel<?php echo $row['registration_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="denyModalLabel<?php echo $row['registration_id']; ?>">Deny Registration</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="index.php?page=approve">
                                                <div class="modal-body">
                                                    <p>Are you sure you want to deny the registration for <strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong>?</p>
                                                    <div class="mb-3">
                                                        <label for="denial_reason" class="form-label">Reason for Denial</label>
                                                        <textarea class="form-control" id="denial_reason" name="denial_reason" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <input type="hidden" name="registration_id" value="<?php echo $row['registration_id']; ?>">
                                                    <button type="submit" name="deny_registration" class="btn btn-danger">Deny</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">No pending registrations found.</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Approvals/Denials -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">Recent Approvals/Denials</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Approved/Denied By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT r.*, ro.role_name, CONCAT(s.first_name, ' ', s.last_name) as approved_by_name 
                                FROM registration r 
                                LEFT JOIN roles ro ON r.role_id = ro.role_id 
                                LEFT JOIN staff s ON r.approved_by = s.staff_id
                                WHERE r.status IN ('Approved', 'Denied') 
                                ORDER BY r.approved_date DESC LIMIT 10";
                        $result = mysqli_query($conn, $sql);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $row['registration_id']; ?></td>
                            <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['role_name']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($row['status'] == 'Approved') ? 'success' : 'danger'; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $row['approved_by_name']; ?></td>
                            <td><?php echo formatDate($row['approved_date']); ?></td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">No recent approvals/denials found.</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
