<?php
// Cases management page for all roles with appropriate permissions
include_once('includes/rbac.php');

// Check if user has permission to view this page
if (!checkPermission('cases', 'view')) {
    header("Location: index.php");
    exit;
}

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

// Check if user has permission for the action
if (!checkPermission('cases', $action)) {
    $action = 'view'; // Default to view if no permission
}

// Get case ID if provided
$caseId = isset($_GET['id']) ? sanitizeInput($conn, $_GET['id']) : '';

// Process form submissions
$successMessage = '';
$errorMessage = '';

// Handle case addition
if (isset($_POST['add_case']) && checkPermission('cases', 'add')) {
    // Generate new case ID
    $sql = "SELECT MAX(SUBSTRING(case_id, 2)) as max_id FROM cases";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $nextId = 'C' . str_pad(($row['max_id'] + 1), 4, '0', STR_PAD_LEFT);
    
    // Get form data
    $caseName = sanitizeInput($conn, $_POST['case_name']);
    $plaintiff = sanitizeInput($conn, $_POST['plaintiff']);
    $defendant = sanitizeInput($conn, $_POST['defendant']);
    $plaintiffLawyer = sanitizeInput($conn, $_POST['plaintiff_lawyer']);
    $defendantLawyer = sanitizeInput($conn, $_POST['defendant_lawyer']);
    $registeredDate = sanitizeInput($conn, $_POST['registered_date']);
    $nature = sanitizeInput($conn, $_POST['nature']);
    $status = sanitizeInput($conn, $_POST['status']);
    $isWarrant = isset($_POST['is_warrant']) ? 1 : 0;
    $nextDate = sanitizeInput($conn, $_POST['next_date']);
    $forWhat = sanitizeInput($conn, $_POST['for_what']);
    $staffId = sanitizeInput($conn, $_POST['staff_id']);
    $courtName = sanitizeInput($conn, $_POST['court_name']);
    
    // Insert case record
    $sql = "INSERT INTO cases (case_id, case_name, plaintiff, defendant, plaintiff_lawyer, defendant_lawyer, registered_date, is_active, nature, status, is_warrant, next_date, for_what, staff_id, court_name) 
            VALUES ('$nextId', '$caseName', '$plaintiff', '$defendant', '$plaintiffLawyer', '$defendantLawyer', '$registeredDate', 1, '$nature', '$status', $isWarrant, '$nextDate', '$forWhat', '$staffId', '$courtName')";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $successMessage = 'Case added successfully.';
        // Redirect to view page
        header("Location: index.php?page=cases&action=view");
        exit;
    } else {
        $errorMessage = 'Error adding case record: ' . mysqli_error($conn);
    }
}

// Handle case update
if (isset($_POST['update_case']) && checkPermission('cases', 'edit')) {
    // Get form data
    $caseId = sanitizeInput($conn, $_POST['case_id']);
    $caseName = sanitizeInput($conn, $_POST['case_name']);
    $plaintiff = sanitizeInput($conn, $_POST['plaintiff']);
    $defendant = sanitizeInput($conn, $_POST['defendant']);
    $plaintiffLawyer = sanitizeInput($conn, $_POST['plaintiff_lawyer']);
    $defendantLawyer = sanitizeInput($conn, $_POST['defendant_lawyer']);
    $nature = sanitizeInput($conn, $_POST['nature']);
    $status = sanitizeInput($conn, $_POST['status']);
    $isWarrant = isset($_POST['is_warrant']) ? 1 : 0;
    $nextDate = sanitizeInput($conn, $_POST['next_date']);
    $forWhat = sanitizeInput($conn, $_POST['for_what']);
    $staffId = sanitizeInput($conn, $_POST['staff_id']);
    $courtName = sanitizeInput($conn, $_POST['court_name']);
    
    // Update case record
    $sql = "UPDATE cases SET 
            case_name = '$caseName',
            plaintiff = '$plaintiff',
            defendant = '$defendant',
            plaintiff_lawyer = '$plaintiffLawyer',
            defendant_lawyer = '$defendantLawyer',
            nature = '$nature',
            status = '$status',
            is_warrant = $isWarrant,
            next_date = '$nextDate',
            for_what = '$forWhat',
            staff_id = '$staffId',
            court_name = '$courtName'
            WHERE case_id = '$caseId'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $successMessage = 'Case updated successfully.';
        // Redirect to view page
        header("Location: index.php?page=cases&action=view");
        exit;
    } else {
        $errorMessage = 'Error updating case record: ' . mysqli_error($conn);
    }
}

// Handle case deletion
if (isset($_POST['delete_case']) && checkPermission('cases', 'delete')) {
    $caseId = sanitizeInput($conn, $_POST['case_id']);
    
    // Update case record (soft delete)
    $sql = "UPDATE cases SET is_active = 0 WHERE case_id = '$caseId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $successMessage = 'Case deleted successfully.';
        // Redirect to view page
        header("Location: index.php?page=cases&action=view");
        exit;
    } else {
        $errorMessage = 'Error deleting case record: ' . mysqli_error($conn);
    }
}

// Get case data for edit
$caseData = [];
if ($action == 'edit' && !empty($caseId)) {
    $sql = "SELECT * FROM cases WHERE case_id = '$caseId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $caseData = mysqli_fetch_assoc($result);
    } else {
        $errorMessage = 'Case not found.';
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

// Get staff for dropdown
$staffMembers = [];
$sql = "SELECT staff_id, first_name, last_name FROM staff WHERE is_active = 1";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $staffMembers[] = $row;
    }
}

// Get lawyers for dropdown
$lawyers = [];
$sql = "SELECT lawyer_id, first_name, last_name FROM lawyer WHERE is_active = 1";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lawyers[] = $row;
    }
}

// Filter cases based on user role
$caseFilter = '';
if ($userRole == ROLE_JUDGE) {
    $caseFilter = "WHERE staff_id = '$userId' AND is_active = 1";
} elseif ($userRole == ROLE_LAWYER) {
    $caseFilter = "WHERE (plaintiff_lawyer = '$userId' OR defendant_lawyer = '$userId') AND is_active = 1";
} else {
    $caseFilter = "WHERE is_active = 1";
}
?>

<div class="cases-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="page-title">Case Management</h1>
            </div>
            <div class="col text-end">
                <?php if ($action == 'view' && checkPermission('cases', 'add')): ?>
                <a href="index.php?page=cases&action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Register New Case
                </a>
                <?php elseif ($action != 'view'): ?>
                <a href="index.php?page=cases&action=view" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Cases List
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
    <!-- Cases List -->
    <div class="card">
        <div class="card-body">
            <!-- Filter Controls -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Dismissed">Dismissed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="courtFilter">
                        <option value="">All Courts</option>
                        <?php foreach ($courts as $court): ?>
                        <option value="<?php echo $court['court_name']; ?>"><?php echo $court['court_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search cases...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" id="resetFilters">Reset Filters</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="casesTable">
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Case Name</th>
                            <th>Plaintiff</th>
                            <th>Defendant</th>
                            <th>Status</th>
                            <th>Next Date</th>
                            <th>Court</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM cases $caseFilter ORDER BY case_id";
                        $result = mysqli_query($conn, $sql);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $row['case_id']; ?></td>
                            <td><?php echo $row['case_name']; ?></td>
                            <td><?php echo $row['plaintiff']; ?></td>
                            <td><?php echo $row['defendant']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    if ($row['status'] == 'Pending') echo 'warning';
                                    elseif ($row['status'] == 'In Progress') echo 'primary';
                                    elseif ($row['status'] == 'Completed') echo 'success';
                                    elseif ($row['status'] == 'Dismissed') echo 'danger';
                                    else echo 'secondary';
                                ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($row['next_date']); ?></td>
                            <td><?php echo $row['court_name']; ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="index.php?page=cases&action=view_details&id=<?php echo $row['case_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    <?php if (checkPermission('cases', 'edit')): ?>
                                    <a href="index.php?page=cases&action=edit&id=<?php echo $row['case_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (checkPermission('cases', 'delete')): ?>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['case_id']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $row['case_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $row['case_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $row['case_id']; ?>">Confirm Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete case <strong><?php echo $row['case_name']; ?></strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="index.php?page=cases&action=view">
                                                        <input type="hidden" name="case_id" value="<?php echo $row['case_id']; ?>">
                                                        <button type="submit" name="delete_case" class="btn btn-danger">Delete</button>
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
                            <td colspan="8" class="text-center">No cases found.</td>
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
    <!-- Add/Edit Case Form -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo ($action == 'add') ? 'Register New Case' : 'Edit Case'; ?></h5>
            
            <form method="POST" action="index.php?page=cases&action=<?php echo $action; ?><?php echo ($action == 'edit') ? '&id=' . $caseId : ''; ?>">
                <?php if ($action == 'edit'): ?>
                <input type="hidden" name="case_id" value="<?php echo $caseData['case_id']; ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="case_name" class="form-label">Case Name</label>
                        <input type="text" class="form-control" id="case_name" name="case_name" value="<?php echo ($action == 'edit') ? $caseData['case_name'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nature" class="form-label">Nature of Case</label>
                        <select class="form-select" id="nature" name="nature" required>
                            <option value="">Select Nature</option>
                            <option value="Civil" <?php echo ($action == 'edit' && $caseData['nature'] == 'Civil') ? 'selected' : ''; ?>>Civil</option>
                            <option value="Criminal" <?php echo ($action == 'edit' && $caseData['nature'] == 'Criminal') ? 'selected' : ''; ?>>Criminal</option>
                            <option value="Family" <?php echo ($action == 'edit' && $caseData['nature'] == 'Family') ? 'selected' : ''; ?>>Family</option>
                            <option value="Commercial" <?php echo ($action == 'edit' && $caseData['nature'] == 'Commercial') ? 'selected' : ''; ?>>Commercial</option>
                            <option value="Other" <?php echo ($action == 'edit' && $caseData['nature'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="plaintiff" class="form-label">Plaintiff</label>
                        <input type="text" class="form-control" id="plaintiff" name="plaintiff" value="<?php echo ($action == 'edit') ? $caseData['plaintiff'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="defendant" class="form-label">Defendant</label>
                        <input type="text" class="form-control" id="defendant" name="defendant" value="<?php echo ($action == 'edit') ? $caseData['defendant'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="plaintiff_lawyer" class="form-label">Plaintiff's Lawyer</label>
                        <select class="form-select" id="plaintiff_lawyer" name="plaintiff_lawyer">
                            <option value="">Select Lawyer</option>
                            <?php foreach ($lawyers as $lawyer): ?>
                            <option value="<?php echo $lawyer['lawyer_id']; ?>" <?php echo ($action == 'edit' && $caseData['plaintiff_lawyer'] == $lawyer['lawyer_id']) ? 'selected' : ''; ?>>
                                <?php echo $lawyer['first_name'] . ' ' . $lawyer['last_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="defendant_lawyer" class="form-label">Defendant's Lawyer</label>
                        <select class="form-select" id="defendant_lawyer" name="defendant_lawyer">
                            <option value="">Select Lawyer</option>
                            <?php foreach ($lawyers as $lawyer): ?>
                            <option value="<?php echo $lawyer['lawyer_id']; ?>" <?php echo ($action == 'edit' && $caseData['defendant_lawyer'] == $lawyer['lawyer_id']) ? 'selected' : ''; ?>>
                                <?php echo $lawyer['first_name'] . ' ' . $lawyer['last_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="court_name" class="form-label">Court</label>
                        <select class="form-select" id="court_name" name="court_name" required>
                            <option value="">Select Court</option>
                            <?php foreach ($courts as $court): ?>
                            <option value="<?php echo $court['court_name']; ?>" <?php echo ($action == 'edit' && $caseData['court_name'] == $court['court_name']) ? 'selected' : ''; ?>>
                                <?php echo $court['court_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="staff_id" class="form-label">Assigned Judge/Staff</label>
                        <select class="form-select" id="staff_id" name="staff_id" required>
                            <option value="">Select Staff</option>
                            <?php foreach ($staffMembers as $staff): ?>
                            <option value="<?php echo $staff['staff_id']; ?>" <?php echo ($action == 'edit' && $caseData['staff_id'] == $staff['staff_id']) ? 'selected' : ''; ?>>
                                <?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="Pending" <?php echo ($action == 'edit' && $caseData['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Progress" <?php echo ($action == 'edit' && $caseData['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo ($action == 'edit' && $caseData['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="Dismissed" <?php echo ($action == 'edit' && $caseData['status'] == 'Dismissed') ? 'selected' : ''; ?>>Dismissed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="registered_date" class="form-label">Registered Date</label>
                        <input type="date" class="form-control" id="registered_date" name="registered_date" value="<?php echo ($action == 'edit') ? $caseData['registered_date'] : date('Y-m-d'); ?>" <?php echo ($action == 'edit') ? 'readonly' : ''; ?> required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="next_date" class="form-label">Next Hearing Date</label>
                        <input type="date" class="form-control" id="next_date" name="next_date" value="<?php echo ($action == 'edit') ? $caseData['next_date'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="for_what" class="form-label">Purpose of Next Hearing</label>
                        <input type="text" class="form-control" id="for_what" name="for_what" value="<?php echo ($action == 'edit') ? $caseData['for_what'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_warrant" name="is_warrant" <?php echo ($action == 'edit' && $caseData['is_warrant'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_warrant">Warrant Required</label>
                </div>
                
                <div class="text-end">
                    <button type="submit" name="<?php echo ($action == 'add') ? 'add_case' : 'update_case'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo ($action == 'add') ? 'Register Case' : 'Update Case'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php elseif ($action == 'view_details'): ?>
    <!-- Case Details View -->
    <?php
    $sql = "SELECT * FROM cases WHERE case_id = '$caseId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $caseData = mysqli_fetch_assoc($result);
    ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Case Details</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Case ID</th>
                            <td><?php echo $caseData['case_id']; ?></td>
                        </tr>
                        <tr>
                            <th>Case Name</th>
                            <td><?php echo $caseData['case_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Nature</th>
                            <td><?php echo $caseData['nature']; ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?php 
                                    if ($caseData['status'] == 'Pending') echo 'warning';
                                    elseif ($caseData['status'] == 'In Progress') echo 'primary';
                                    elseif ($caseData['status'] == 'Completed') echo 'success';
                                    elseif ($caseData['status'] == 'Dismissed') echo 'danger';
                                    else echo 'secondary';
                                ?>">
                                    <?php echo $caseData['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Registered Date</th>
                            <td><?php echo formatDate($caseData['registered_date']); ?></td>
                        </tr>
                        <tr>
                            <th>Court</th>
                            <td><?php echo $caseData['court_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Warrant Required</th>
                            <td><?php echo ($caseData['is_warrant'] == 1) ? 'Yes' : 'No'; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Plaintiff</th>
                            <td><?php echo $caseData['plaintiff']; ?></td>
                        </tr>
                        <tr>
                            <th>Defendant</th>
                            <td><?php echo $caseData['defendant']; ?></td>
                        </tr>
                        <tr>
                            <th>Plaintiff's Lawyer</th>
                            <td>
                                <?php
                                if (!empty($caseData['plaintiff_lawyer'])) {
                                    $sql = "SELECT first_name, last_name FROM lawyer WHERE lawyer_id = '{$caseData['plaintiff_lawyer']}'";
                                    $lawyerResult = mysqli_query($conn, $sql);
                                    if ($lawyerResult && $lawyerRow = mysqli_fetch_assoc($lawyerResult)) {
                                        echo $lawyerRow['first_name'] . ' ' . $lawyerRow['last_name'];
                                    } else {
                                        echo 'N/A';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Defendant's Lawyer</th>
                            <td>
                                <?php
                                if (!empty($caseData['defendant_lawyer'])) {
                                    $sql = "SELECT first_name, last_name FROM lawyer WHERE lawyer_id = '{$caseData['defendant_lawyer']}'";
                                    $lawyerResult = mysqli_query($conn, $sql);
                                    if ($lawyerResult && $lawyerRow = mysqli_fetch_assoc($lawyerResult)) {
                                        echo $lawyerRow['first_name'] . ' ' . $lawyerRow['last_name'];
                                    } else {
                                        echo 'N/A';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Assigned Judge/Staff</th>
                            <td>
                                <?php
                                if (!empty($caseData['staff_id'])) {
                                    $sql = "SELECT first_name, last_name FROM staff WHERE staff_id = '{$caseData['staff_id']}'";
                                    $staffResult = mysqli_query($conn, $sql);
                                    if ($staffResult && $staffRow = mysqli_fetch_assoc($staffResult)) {
                                        echo $staffRow['first_name'] . ' ' . $staffRow['last_name'];
                                    } else {
                                        echo 'N/A';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Next Hearing Date</th>
                            <td><?php echo formatDate($caseData['next_date']); ?></td>
                        </tr>
                        <tr>
                            <th>Purpose of Next Hearing</th>
                            <td><?php echo $caseData['for_what']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Related Records Tabs -->
            <div class="mt-4">
                <ul class="nav nav-tabs" id="caseDetailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="hearings-tab" data-bs-toggle="tab" data-bs-target="#hearings" type="button" role="tab" aria-controls="hearings" aria-selected="true">Hearings</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="judgements-tab" data-bs-toggle="tab" data-bs-target="#judgements" type="button" role="tab" aria-controls="judgements" aria-selected="false">Judgements</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">Orders</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="warrants-tab" data-bs-toggle="tab" data-bs-target="#warrants" type="button" role="tab" aria-controls="warrants" aria-selected="false">Warrants</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-controls="notes" aria-selected="false">Notes</button>
                    </li>
                </ul>
                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="caseDetailsTabsContent">
                    <div class="tab-pane fade show active" id="hearings" role="tabpanel" aria-labelledby="hearings-tab">
                        <h6>Hearing History</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Summary</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM dailycaseactivities WHERE case_name = '{$caseData['case_name']}' ORDER BY activity_date DESC";
                                    $activitiesResult = mysqli_query($conn, $sql);
                                    
                                    if ($activitiesResult && mysqli_num_rows($activitiesResult) > 0) {
                                        while ($activity = mysqli_fetch_assoc($activitiesResult)) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($activity['activity_date']); ?></td>
                                        <td><?php echo $activity['summary']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($activity['is_taken'] == 1) ? 'success' : 'warning'; ?>">
                                                <?php echo ($activity['is_taken'] == 1) ? 'Completed' : 'Pending'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No hearing records found.</td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="judgements" role="tabpanel" aria-labelledby="judgements-tab">
                        <h6>Judgements</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Contested</th>
                                        <th>Judge</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT j.*, s.first_name, s.last_name FROM judgements j 
                                            LEFT JOIN staff s ON j.staff_id = s.staff_id 
                                            WHERE j.case_id = '$caseId' ORDER BY j.given_on DESC";
                                    $judgementsResult = mysqli_query($conn, $sql);
                                    
                                    if ($judgementsResult && mysqli_num_rows($judgementsResult) > 0) {
                                        while ($judgement = mysqli_fetch_assoc($judgementsResult)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $judgement['jud_id']; ?></td>
                                        <td><?php echo formatDate($judgement['given_on']); ?></td>
                                        <td><?php echo ($judgement['is_contested'] == 1) ? 'Yes' : 'No'; ?></td>
                                        <td><?php echo $judgement['first_name'] . ' ' . $judgement['last_name']; ?></td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No judgements found.</td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                        <h6>Orders</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Calculated</th>
                                        <th>Judge</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT o.*, s.first_name, s.last_name FROM orders o 
                                            LEFT JOIN staff s ON o.staff_id = s.staff_id 
                                            WHERE o.case_id = '$caseId' ORDER BY o.given_on DESC";
                                    $ordersResult = mysqli_query($conn, $sql);
                                    
                                    if ($ordersResult && mysqli_num_rows($ordersResult) > 0) {
                                        while ($order = mysqli_fetch_assoc($ordersResult)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $order['order_id']; ?></td>
                                        <td><?php echo formatDate($order['given_on']); ?></td>
                                        <td><?php echo ($order['is_calculated'] == 1) ? 'Yes' : 'No'; ?></td>
                                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No orders found.</td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="warrants" role="tabpanel" aria-labelledby="warrants-tab">
                        <h6>Warrants</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Issue Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM warrants WHERE case_id = '$caseId' ORDER BY issue_date DESC";
                                    $warrantsResult = mysqli_query($conn, $sql);
                                    
                                    if ($warrantsResult && mysqli_num_rows($warrantsResult) > 0) {
                                        while ($warrant = mysqli_fetch_assoc($warrantsResult)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $warrant['warrant_id']; ?></td>
                                        <td><?php echo $warrant['warrant_type']; ?></td>
                                        <td><?php echo formatDate($warrant['issue_date']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                if ($warrant['status'] == 'Active') echo 'danger';
                                                elseif ($warrant['status'] == 'Executed') echo 'success';
                                                elseif ($warrant['status'] == 'Cancelled') echo 'secondary';
                                                else echo 'warning';
                                            ?>">
                                                <?php echo $warrant['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No warrants found.</td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                        <h6>Case Notes</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT n.*, r.role_name FROM notes n 
                                            LEFT JOIN roles r ON n.role_id = r.role_id 
                                            WHERE n.case_id = '$caseId' AND n.is_deleted = 0 ORDER BY n.updated_date DESC";
                                    $notesResult = mysqli_query($conn, $sql);
                                    
                                    if ($notesResult && mysqli_num_rows($notesResult) > 0) {
                                        while ($note = mysqli_fetch_assoc($notesResult)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $note['title']; ?></td>
                                        <td><?php echo formatDate($note['created_date']); ?></td>
                                        <td><?php echo formatDate($note['updated_date']); ?></td>
                                        <td><?php echo $note['role_name']; ?></td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No notes found.</td>
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
        </div>
    </div>
    <?php
    } else {
        echo '<div class="alert alert-danger">Case not found.</div>';
    }
    ?>
    <?php endif; ?>
</div>

<script>
    // Filter functionality for cases table
    document.addEventListener('DOMContentLoaded', function() {
        const statusFilter = document.getElementById('statusFilter');
        const courtFilter = document.getElementById('courtFilter');
        const searchInput = document.getElementById('searchInput');
        const resetButton = document.getElementById('resetFilters');
        const table = document.getElementById('casesTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        function applyFilters() {
            const statusValue = statusFilter.value.toLowerCase();
            const courtValue = courtFilter.value.toLowerCase();
            const searchValue = searchInput.value.toLowerCase();
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const statusCell = row.cells[4].textContent.toLowerCase();
                const courtCell = row.cells[6].textContent.toLowerCase();
                const rowText = row.textContent.toLowerCase();
                
                const statusMatch = statusValue === '' || statusCell.includes(statusValue);
                const courtMatch = courtValue === '' || courtCell.includes(courtValue);
                const searchMatch = searchValue === '' || rowText.includes(searchValue);
                
                if (statusMatch && courtMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        if (statusFilter && courtFilter && searchInput && resetButton) {
            statusFilter.addEventListener('change', applyFilters);
            courtFilter.addEventListener('change', applyFilters);
            searchInput.addEventListener('keyup', applyFilters);
            
            resetButton.addEventListener('click', function() {
                statusFilter.value = '';
                courtFilter.value = '';
                searchInput.value = '';
                applyFilters();
            });
        }
    });
</script>
