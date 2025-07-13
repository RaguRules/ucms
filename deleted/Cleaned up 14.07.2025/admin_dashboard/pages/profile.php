<?php
// Profile page for all user roles
include_once('includes/rbac.php');

// Check if user has permission to view this page
if (!checkPermission('profile', 'view')) {
    header("Location: index.php");
    exit;
}

// Get user details
$userDetails = getUserDetails($conn, $userId, $userRole);

// Handle profile update
$successMessage = '';
$errorMessage = '';

if (isset($_POST['update_profile']) && checkPermission('profile', 'edit')) {
    $firstName = sanitizeInput($conn, $_POST['first_name']);
    $lastName = sanitizeInput($conn, $_POST['last_name']);
    $mobile = sanitizeInput($conn, $_POST['mobile']);
    $email = sanitizeInput($conn, $_POST['email']);
    $address = sanitizeInput($conn, $_POST['address']);
    
    // Determine table and ID field based on role
    $table = '';
    $idField = '';
    
    switch ($userRole) {
        case ROLE_ADMIN:
        case ROLE_JUDGE:
        case ROLE_REGISTRAR:
        case ROLE_INTERPRETER:
        case ROLE_STAFF:
            $table = 'staff';
            $idField = 'staff_id';
            break;
        case ROLE_LAWYER:
            $table = 'lawyer';
            $idField = 'lawyer_id';
            break;
        case ROLE_POLICE:
            $table = 'police';
            $idField = 'police_id';
            break;
    }
    
    // Update profile
    $sql = "UPDATE $table SET 
            first_name = '$firstName',
            last_name = '$lastName',
            mobile = '$mobile',
            email = '$email',
            address = '$address'
            WHERE $idField = '$userId'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $successMessage = 'Profile updated successfully.';
        
        // Update session variables
        $_SESSION['FIRST_NAME'] = $firstName;
        $_SESSION['LAST_NAME'] = $lastName;
        
        // Refresh user details
        $userDetails = getUserDetails($conn, $userId, $userRole);
    } else {
        $errorMessage = 'Error updating profile: ' . mysqli_error($conn);
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = 'All password fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'New password and confirm password do not match.';
    } else {
        // Get current password from database
        $sql = "SELECT password FROM login WHERE username = '{$_SESSION['USERNAME']}'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $storedPassword = $row['password'];
            
            // Verify current password (in production, use password_verify)
            if ($currentPassword == $storedPassword) {
                // Hash new password (in production, use password_hash)
                $hashedPassword = $newPassword;
                
                // Update password
                $sql = "UPDATE login SET password = '$hashedPassword' WHERE username = '{$_SESSION['USERNAME']}'";
                $result = mysqli_query($conn, $sql);
                
                if ($result) {
                    $successMessage = 'Password changed successfully.';
                } else {
                    $errorMessage = 'Error changing password: ' . mysqli_error($conn);
                }
            } else {
                $errorMessage = 'Current password is incorrect.';
            }
        } else {
            $errorMessage = 'Error retrieving user information.';
        }
    }
}

// Get profile image
$profileImage = $userDetails['image_path'] ?? 'assets/img/default-avatar.png';

// Determine action (profile or password)
$action = isset($_GET['action']) ? $_GET['action'] : 'profile';
?>

<div class="profile-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="page-title">My Profile</h1>
            </div>
            <div class="col text-end">
                <?php if ($action == 'profile'): ?>
                <a href="index.php?page=profile&action=password" class="btn btn-outline-primary">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <?php else: ?>
                <a href="index.php?page=profile" class="btn btn-outline-primary">
                    <i class="fas fa-user"></i> View Profile
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
    
    <?php if ($action == 'profile'): ?>
    <!-- Profile Information -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-3 text-center">
                    <div class="profile-image-container">
                        <img src="<?php echo $profileImage; ?>" alt="Profile Image" class="img-fluid rounded-circle profile-image">
                        <?php if (checkPermission('profile', 'edit')): ?>
                        <div class="profile-image-overlay">
                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#changePhotoModal">
                                <i class="fas fa-camera"></i> Change Photo
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <h4 class="mt-3"><?php echo $userDetails['first_name'] . ' ' . $userDetails['last_name']; ?></h4>
                    <p class="text-muted"><?php echo getRoleName($userRole); ?></p>
                </div>
                
                <!-- Profile Details Form -->
                <div class="col-md-9">
                    <form method="POST" action="index.php?page=profile">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $userDetails['first_name']; ?>" <?php echo checkPermission('profile', 'edit') ? '' : 'readonly'; ?>>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $userDetails['last_name']; ?>" <?php echo checkPermission('profile', 'edit') ? '' : 'readonly'; ?>>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $userDetails['email']; ?>" <?php echo checkPermission('profile', 'edit') ? '' : 'readonly'; ?>>
                            </div>
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo $userDetails['mobile']; ?>" <?php echo checkPermission('profile', 'edit') ? '' : 'readonly'; ?>>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" <?php echo checkPermission('profile', 'edit') ? '' : 'readonly'; ?>><?php echo $userDetails['address']; ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nic_number" class="form-label">NIC Number</label>
                                <input type="text" class="form-control" id="nic_number" value="<?php echo $userDetails['nic_number']; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="joined_date" class="form-label">Joined Date</label>
                                <input type="text" class="form-control" id="joined_date" value="<?php echo formatDate($userDetails['joined_date']); ?>" readonly>
                            </div>
                        </div>
                        
                        <?php if (in_array($userRole, [ROLE_ADMIN, ROLE_JUDGE, ROLE_REGISTRAR, ROLE_INTERPRETER, ROLE_STAFF])): ?>
                        <div class="mb-3">
                            <label for="court_id" class="form-label">Court</label>
                            <input type="text" class="form-control" id="court_id" value="<?php echo getCourtName($userDetails['court_id']); ?>" readonly>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($userRole == ROLE_LAWYER): ?>
                        <div class="mb-3">
                            <label for="enrolment_number" class="form-label">Enrolment Number</label>
                            <input type="text" class="form-control" id="enrolment_number" value="<?php echo $userDetails['enrolment_number']; ?>" readonly>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($userRole == ROLE_POLICE): ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="badge_number" class="form-label">Badge Number</label>
                                <input type="text" class="form-control" id="badge_number" value="<?php echo $userDetails['badge_number']; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="station" class="form-label">Police Station</label>
                                <input type="text" class="form-control" id="station" value="<?php echo $userDetails['station']; ?>" readonly>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (checkPermission('profile', 'edit')): ?>
                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Change Password Form -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Change Password</h5>
            <form method="POST" action="index.php?page=profile&action=password">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <div class="form-text">Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="text-end">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Change Photo Modal -->
<div class="modal fade" id="changePhotoModal" tabindex="-1" aria-labelledby="changePhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePhotoModalLabel">Change Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="index.php?page=profile" enctype="multipart/form-data" id="photoForm">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Select New Photo</label>
                        <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*" required>
                    </div>
                    <div class="text-center mt-3">
                        <img id="photo_preview" class="img-fluid rounded-circle profile-preview d-none" alt="Preview">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="photoForm" name="update_photo" class="btn btn-primary">Upload Photo</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview image before upload
    document.getElementById('profile_photo').addEventListener('change', function(e) {
        const preview = document.getElementById('photo_preview');
        const file = e.target.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        
        if (file) {
            reader.readAsDataURL(file);
        }
    });
</script>
