<?php
// Integration file to connect the new admin dashboard with the existing system
include_once('config/database.php');
include_once('includes/auth.php');
include_once('includes/rbac.php');
include_once('includes/functions.php');

/**
 * Map existing user roles to new RBAC roles
 * @param string $existingRole Role from the existing system
 * @return string New RBAC role ID
 */
function mapExistingRoleToRBAC($existingRole) {
    $roleMap = [
        'Administrator' => ROLE_ADMIN,
        'Judge' => ROLE_JUDGE,
        'Registrar' => ROLE_REGISTRAR,
        'Interpreter' => ROLE_INTERPRETER,
        'Staff' => ROLE_STAFF,
        'Lawyer' => ROLE_LAWYER,
        'Police' => ROLE_POLICE
    ];
    
    return $roleMap[$existingRole] ?? ROLE_STAFF; // Default to Staff if role not found
}

/**
 * Get user details from the existing system
 * @param mysqli $conn Database connection
 * @param string $username Username to check
 * @return array|bool User details or false if not found
 */
function getExistingUserDetails($conn, $username) {
    // Check staff table
    $sql = "SELECT * FROM staff WHERE email = '$username' AND is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user['user_type'] = 'staff';
        return $user;
    }
    
    // Check lawyer table
    $sql = "SELECT * FROM lawyer WHERE email = '$username' AND is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user['user_type'] = 'lawyer';
        return $user;
    }
    
    // Check police table
    $sql = "SELECT * FROM police WHERE email = '$username' AND is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user['user_type'] = 'police';
        return $user;
    }
    
    return false;
}

/**
 * Sync user data between existing system and new dashboard
 * @param mysqli $conn Database connection
 */
function syncUserData($conn) {
    // Get all active users from login table
    $sql = "SELECT * FROM login WHERE status = 'Active'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($user = mysqli_fetch_assoc($result)) {
            $username = $user['username'];
            $roleId = $user['role_id'];
            
            // Get user details from appropriate table
            $userDetails = getExistingUserDetails($conn, $username);
            
            if ($userDetails) {
                // Update role_id if needed
                $mappedRole = mapExistingRoleToRBAC($roleId);
                
                if ($userDetails['role_id'] != $mappedRole) {
                    $table = $userDetails['user_type'];
                    $idField = $table . '_id';
                    $id = $userDetails[$idField];
                    
                    $sql = "UPDATE $table SET role_id = '$mappedRole' WHERE $idField = '$id'";
                    mysqli_query($conn, $sql);
                    
                    // Also update login table
                    $sql = "UPDATE login SET role_id = '$mappedRole' WHERE username = '$username'";
                    mysqli_query($conn, $sql);
                }
            }
        }
    }
}

/**
 * Import existing cases into the dashboard format if needed
 * @param mysqli $conn Database connection
 */
function syncCaseData($conn) {
    // Check if cases need to be synced
    $sql = "SELECT COUNT(*) as count FROM cases";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    // If cases already exist, no need to sync
    if ($row['count'] > 0) {
        return;
    }
    
    // Import cases from existing system if needed
    // This is a placeholder - actual implementation would depend on the existing system's structure
    // $sql = "INSERT INTO cases (case_id, case_name, ...) SELECT id, name, ... FROM old_cases";
    // mysqli_query($conn, $sql);
}

/**
 * Redirect to appropriate dashboard page based on user role
 * @param string $roleId User role ID
 * @return string Dashboard page URL
 */
function getDashboardRedirect($roleId) {
    switch ($roleId) {
        case ROLE_ADMIN:
            return 'index.php?page=dashboard';
        case ROLE_JUDGE:
            return 'index.php?page=dashboard';
        case ROLE_REGISTRAR:
            return 'index.php?page=dashboard';
        case ROLE_INTERPRETER:
            return 'index.php?page=dashboard';
        case ROLE_STAFF:
            return 'index.php?page=dashboard';
        case ROLE_LAWYER:
            return 'index.php?page=dashboard';
        case ROLE_POLICE:
            return 'index.php?page=dashboard';
        default:
            return 'index.php?page=dashboard';
    }
}

/**
 * Check if user has access to a specific page
 * @param string $page Page to check
 * @param string $roleId User role ID
 * @return bool True if user has access, false otherwise
 */
function canAccessPage($page, $roleId) {
    // Map pages to resources
    $pageResourceMap = [
        'dashboard' => 'dashboard',
        'profile' => 'profile',
        'staff' => 'staff',
        'cases' => 'cases',
        'appeals' => 'appeals',
        'judgements' => 'judgements',
        'warrants' => 'warrants',
        'orders' => 'orders',
        'parties' => 'parties',
        'dailycaseactivities' => 'dailycaseactivities',
        'notifications' => 'notifications',
        'fines' => 'fines',
        'notes' => 'notes',
        'lawyers' => 'lawyers',
        'police' => 'police',
        'approve' => 'approve',
        'reports' => 'reports',
        'settings' => 'settings'
    ];
    
    $resource = $pageResourceMap[$page] ?? $page;
    
    return checkPermission($resource, 'view');
}

/**
 * Get dashboard statistics based on user role
 * @param mysqli $conn Database connection
 * @param string $roleId User role ID
 * @param string $userId User ID
 * @return array Dashboard statistics
 */
function getDashboardStats($conn, $roleId, $userId) {
    $stats = [
        'total_cases' => 0,
        'active_cases' => 0,
        'pending_judgements' => 0,
        'warrants_issued' => 0,
        'total_staff' => 0
    ];
    
    // Total cases
    $sql = "SELECT COUNT(*) as count FROM cases WHERE is_active = 1";
    if ($roleId == ROLE_JUDGE) {
        $sql .= " AND staff_id = '$userId'";
    } elseif ($roleId == ROLE_LAWYER) {
        $sql .= " AND (plaintiff_lawyer = '$userId' OR defendant_lawyer = '$userId')";
    }
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['total_cases'] = $row['count'];
    }
    
    // Active cases
    $sql = "SELECT COUNT(*) as count FROM cases WHERE is_active = 1 AND status IN ('Pending', 'In Progress')";
    if ($roleId == ROLE_JUDGE) {
        $sql .= " AND staff_id = '$userId'";
    } elseif ($roleId == ROLE_LAWYER) {
        $sql .= " AND (plaintiff_lawyer = '$userId' OR defendant_lawyer = '$userId')";
    }
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['active_cases'] = $row['count'];
    }
    
    // Pending judgements
    $sql = "SELECT COUNT(*) as count FROM cases WHERE is_active = 1 AND status = 'In Progress'";
    if ($roleId == ROLE_JUDGE) {
        $sql .= " AND staff_id = '$userId'";
    }
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['pending_judgements'] = $row['count'];
    }
    
    // Warrants issued
    $sql = "SELECT COUNT(*) as count FROM warrants WHERE status = 'Active'";
    if ($roleId == ROLE_POLICE) {
        $sql .= " AND police_id = '$userId'";
    }
    
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['warrants_issued'] = $row['count'];
    }
    
    // Total staff (admin only)
    if ($roleId == ROLE_ADMIN) {
        $sql = "SELECT COUNT(*) as count FROM staff WHERE is_active = 1";
        $result = mysqli_query($conn, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $stats['total_staff'] = $row['count'];
        }
    }
    
    return $stats;
}

/**
 * Get user details based on role and ID
 * @param mysqli $conn Database connection
 * @param string $userId User ID
 * @param string $roleId User role ID
 * @return array User details
 */
function getUserDetails($conn, $userId, $roleId) {
    $userDetails = [];
    
    // Determine table based on role
    $table = '';
    $idField = '';
    
    if (in_array($roleId, [ROLE_ADMIN, ROLE_JUDGE, ROLE_REGISTRAR, ROLE_INTERPRETER, ROLE_STAFF])) {
        $table = 'staff';
        $idField = 'staff_id';
    } elseif ($roleId == ROLE_LAWYER) {
        $table = 'lawyer';
        $idField = 'lawyer_id';
    } elseif ($roleId == ROLE_POLICE) {
        $table = 'police';
        $idField = 'police_id';
    }
    
    // Get user details
    if (!empty($table)) {
        $sql = "SELECT * FROM $table WHERE $idField = '$userId'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $userDetails = mysqli_fetch_assoc($result);
        }
    }
    
    return $userDetails;
}

/**
 * Get court name from court ID
 * @param string $courtId Court ID
 * @return string Court name
 */
function getCourtName($courtId) {
    global $conn;
    
    $sql = "SELECT court_name FROM courts WHERE court_id = '$courtId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['court_name'];
    }
    
    return 'Unknown Court';
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    return date('d M Y', $timestamp);
}

/**
 * Truncate text to specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @return string Truncated text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}

/**
 * Sanitize input to prevent SQL injection
 * @param mysqli $conn Database connection
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}

// Run sync functions when needed
// syncUserData($conn);
// syncCaseData($conn);
