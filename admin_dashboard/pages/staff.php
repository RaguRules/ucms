<?php
// Staff management page for Administrator role
include_once('includes/rbac.php');

// Check if user has permission to view this page
if (!checkPermission('staff', 'view')) {
    header("Location: index.php");
    exit;
}

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

// Check if user has permission for the action
if (!checkPermission('staff', $action)) {
    $action = 'view'; // Default to view if no permission
}

// Get staff ID if provided
$staffId = isset($_GET['id']) ? sanitizeInput($conn, $_GET['id']) : '';

// Process form submissions
$successMessage = '';
$errorMessage = '';

// Handle staff addition
if (isset($_POST['add_staff']) && checkPermission('staff', 'add')) {
    // Generate new staff ID
    $sql = "SELECT MAX(SUBSTRING(staff_id, 2)) as max_id FROM staff";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $nextId = 'S' . str_pad(($row['max_id'] + 1), 4, '0', STR_PAD_LEFT);
    
    // Get form data
    $firstName = sanitizeInput($conn, $_POST['first_name']);
    $lastName = sanitizeInput($conn, $_POST['last_name']);
    $mobile = sanitizeInput($conn, $_POST['mobile']);
    $email = sanitizeInput($conn, $_POST['email']);
    $address = sanitizeInput($conn, $_POST['address']);
    $nicNumber = sanitizeInput($conn, $_POST['nic_number']);
    $dateOfBirth = sanitizeInput($conn, $_POST['date_of_birth']);
    $courtId = sanitizeInput($conn, $_POST['court_id']);
    $joinedDate = sanitizeInput($conn, $_POST['joined_date']);
    $roleId = sanitizeInput($conn, $_POST['role_id']);
    $password = sanitizeInput($conn, $_POST['password']);
    
    // Check if email already exists
    $sql = "SELECT * FROM staff WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $errorMessage = 'Email already exists. Please use a different email.';
    } else {
        // Insert staff record
        $sql = "INSERT INTO staff (staff_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, court_id, joined_date, role_id, is_active) 
                VALUES ('$nextId', '$firstName', '$lastName', '$mobile', '$email', '$address', '$nicNumber', '$dateOfBirth', '$courtId', '$joinedDate', '$roleId', 1)";
        
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            // Insert login record
            $hashedPassword = $password; // In production, use password_hash
            
            $sql = "INSERT INTO login (username, password, status, role_id) 
                    VALUES ('$email', '$hashedPassword', 'Active', '$roleId')";
            
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $successMessage = 'Staff member added successfully.';
                // Redirect to view page
                header("Location: index.php?page=staff&action=view");
                exit;
            } else {
                $errorMessage = 'Error adding login record: ' . mysqli_error($conn);
            }
        } else {
            $errorMessage = 'Error adding staff record: ' . mysqli_error($conn);
        }
    }
}

// Handle staff update
if (isset($_POST['update_staff']) && checkPermission('staff', 'edit')) {
    // Get form data
    $staffId = sanitizeInput($conn, $_POST['staff_id']);
    $firstName = sanitizeInput($conn, $_POST['first_name']);
    $lastName = sanitizeInput($conn, $_POST['last_name']);
    $mobile = sanitizeInput($conn, $_POST['mobile']);
    $email = sanitizeInput($conn, $_POST['email']);
    $address = sanitizeInput($conn, $_POST['address']);
    $nicNumber = sanitizeInput($conn, $_POST['nic_number']);
    $dateOfBirth = sanitizeInput($conn, $_POST['date_of_birth']);
    $courtId = sanitizeInput($conn, $_POST['court_id']);
    $roleId = sanitizeInput($conn, $_POST['role_id']);
    
    // Get current email
    $sql = "SELECT email FROM staff WHERE staff_id = '$staffId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $currentEmail = $row['email'];
    
    // Check if email changed and already exists
    if ($email != $currentEmail) {
        $sql = "SELECT * FROM staff WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $errorMessage = 'Email already exists. Please use a different email.';
        }
    }
    
    if (empty($errorMessage)) {
        // Update staff record
        $sql = "UPDATE staff SET 
                first_name = '$firstName',
                last_name = '$lastName',
                mobile = '$mobile',
                email = '$email',
                address = '$address',
                nic_number = '$nicNumber',
                date_of_birth = '$dateOfBirth',
                court_id = '$courtId',
                role_id = '$roleId'
                WHERE staff_id = '$staffId'";
        
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            // Update login record if email changed
            if ($email != $currentEmail) {
                $sql = "UPDATE login SET username = '$email', role_id = '$roleId' WHERE username = '$currentEmail'";
                $result = mysqli_query($conn, $sql);
                
                if (!$result) {
                    $errorMessage = 'Error updating login record: ' . mysqli_error($conn);
                }
            } else {
                // Update role_id in login table
                $sql = "UPDATE login SET role_id = '$roleId' WHERE username = '$email'";
                $result = mysqli_query($conn, $sql);
            }
            
            $successMessage = 'Staff member updated successfully.';
            // Redirect to view page
            header("Location: index.php?page=staff&action=view");
            exit;
        } else {
            $errorMessage = 'Error updating staff record: ' . mysqli_error($conn);
        }
    }
}

// Handle staff deletion
if (isset($_POST['delete_staff']) && checkPermission('staff', 'delete')) {
    $staffId = sanitizeInput($conn, $_POST['staff_id']);
    
    // Get email
    $sql = "SELECT email FROM staff WHERE staff_id = '$staffId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $email = $row['email'];
    
    // Update staff record (soft delete)
    $sql = "UPDATE staff SET is_active = 0 WHERE staff_id = '$staffId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        // Update login record
        $sql = "UPDATE login SET status = 'Deleted' WHERE username = '$email'";
        $result = mysqli_query($conn, $sql);
        
        $successMessage = 'Staff member deleted successfully.';
        // Redirect to view page
        header("Location: index.php?page=staff&action=view");
        exit;
    } else {
        $errorMessage = 'Error deleting staff record: ' . mysqli_error($conn);
    }
}

// Get staff data for edit
$staffData = [];
if ($action == 'edit' && !empty($staffId)) {
    $sql = "SELECT * FROM staff WHERE staff_id = '$staffId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $staffData = mysqli_fetch_assoc($result);
    } else {
        $errorMessage = 'Staff member not found.';
        $action = 'view';
    }
}

// Get courts for dropdown
$courts = [];
$sql = "SELECT * FROM courts";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courts[] = $row;
    }
}

// Get roles for dropdown
$roles = RBAC::getAllRoles();
?>

<div class="staff-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="page-title">Staff Management</h1>
            </div>
            <div class="col text-end">
                <?php if ($action == 'view' && checkPermission('staff', 'add')): ?>
                <a href="index.php?page=staff&action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Staff
                </a>
                <?php elseif ($action != 'view'): ?>
                <a href="index.php?page=staff&action=view" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Staff List
                </a>
                <?php endif; ?>
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
    
    <?php if ($action == 'view'): ?>
    <!-- Staff List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Court</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM staff WHERE is_active = 1 ORDER BY staff_id";
                        $result = mysqli_query($conn, $sql);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $row['staff_id']; ?></td>
                            <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['mobile']; ?></td>
                            <td><?php echo getCourtName($row['court_id']); ?></td>
                            <td><?php echo getRoleName($row['role_id']); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if (checkPermission('staff', 'edit')): ?>
                                    <a href="index.php?page=staff&action=edit&id=<?php echo $row['staff_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (checkPermission('staff', 'delete')): ?>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['staff_id']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $row['staff_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $row['staff_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $row['staff_id']; ?>">Confirm Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete staff member <strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="index.php?page=staff&action=view">
                                                        <input type="hidden" name="staff_id" value="<?php echo $row['staff_id']; ?>">
                                                        <button type="submit" name="delete_staff" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">No staff members found.</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
    <!-- Add/Edit Staff Form -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo ($action == 'add') ? 'Add New Staff' : 'Edit Staff'; ?></h5>
            
            <form method="POST" action="index.php?page=staff&action=<?php echo $action; ?><?php echo ($action == 'edit') ? '&id=' . $staffId : ''; ?>">
                <?php if ($action == 'edit'): ?>
                <input type="hidden" name="staff_id" value="<?php echo $staffData['staff_id']; ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo ($action == 'edit') ? $staffData['first_name'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo ($action == 'edit') ? $staffData['last_name'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo ($action == 'edit') ? $staffData['email'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo ($action == 'edit') ? $staffData['mobile'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo ($action == 'edit') ? $staffData['address'] : ''; ?></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nic_number" class="form-label">NIC Number</label>
                        <input type="text" class="form-control" id="nic_number" name="nic_number" value="<?php echo ($action == 'edit') ? $staffData['nic_number'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo ($action == 'edit') ? $staffData['date_of_birth'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_id" class="form-label">Court</label>
                        <select class="form-select" id="court_id" name="court_id" required>
                            <option value="">Select Court</option>
                            <?php foreach ($courts as $court): ?>
                            <option value="<?php echo $court['court_id']; ?>" <?php echo ($action == 'edit' && $staffData['court_id'] == $court['court_id']) ? 'selected' : ''; ?>>
                                <?php echo $court['court_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="role_id" class="form-label">Role</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo ($action == 'edit' && $staffData['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                <?php echo $role['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <?php if ($action == 'add'): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="joined_date" class="form-label">Joined Date</label>
                        <input type="date" class="form-control" id="joined_date" name="joined_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Initial password will be set to this value. User can change it later.</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="text-end">
                    <button type="submit" name="<?php echo ($action == 'add') ? 'add_staff' : 'update_staff'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo ($action == 'add') ? 'Add Staff' : 'Update Staff'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
