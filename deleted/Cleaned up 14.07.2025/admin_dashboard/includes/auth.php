<?php
// Authentication functions

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['USERNAME']) && !empty($_SESSION['USERNAME']);
}

/**
 * Authenticate user with provided credentials
 * @param mysqli $conn Database connection
 * @param string $username Username
 * @param string $password Password
 * @return array|bool User data if authentication successful, false otherwise
 */
function authenticateUser($conn, $username, $password) {
    $username = sanitizeInput($conn, $username);
    
    // Get user from database
    $sql = "SELECT * FROM login WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Check if account is active
        if ($user['status'] !== 'Active') {
            return ['error' => 'Account is not active. Status: ' . $user['status']];
        }
        
        // Check if account is locked due to too many attempts
        if ($user['attempt'] >= 3) {
            return ['error' => 'Account is locked due to too many failed login attempts. Please reset your password.'];
        }
        
        // Verify password (in production, use password_verify with hashed passwords)
        if ($password == $user['password']) {
            // Reset login attempts on successful login
            $sql = "UPDATE login SET attempt = 0 WHERE username = '$username'";
            mysqli_query($conn, $sql);
            
            // Get user details based on role
            $userData = getUserDataByRole($conn, $username, $user['role_id']);
            if ($userData) {
                return [
                    'success' => true,
                    'user_id' => $userData['id'],
                    'username' => $username,
                    'role_id' => $user['role_id'],
                    'role_name' => getRoleName($user['role_id']),
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name']
                ];
            }
            
            return ['error' => 'User data not found.'];
        } else {
            // Increment login attempts
            $attempts = $user['attempt'] + 1;
            $sql = "UPDATE login SET attempt = $attempts WHERE username = '$username'";
            mysqli_query($conn, $sql);
            
            return ['error' => 'Invalid password.'];
        }
    }
    
    return ['error' => 'User not found.'];
}

/**
 * Get user data based on role
 * @param mysqli $conn Database connection
 * @param string $username Username
 * @param string $roleId Role ID
 * @return array|bool User data if found, false otherwise
 */
function getUserDataByRole($conn, $username, $roleId) {
    $username = sanitizeInput($conn, $username);
    $roleId = sanitizeInput($conn, $roleId);
    
    $table = '';
    $idField = '';
    
    // Determine table and ID field based on role
    switch ($roleId) {
        case 'R01': // Administrator
        case 'R02': // Judge
        case 'R03': // Registrar
        case 'R04': // Interpreter
        case 'R05': // Common Staff
            $table = 'staff';
            $idField = 'staff_id';
            break;
        case 'R06': // Lawyer
            $table = 'lawyer';
            $idField = 'lawyer_id';
            break;
        case 'R07': // Police
            $table = 'police';
            $idField = 'police_id';
            break;
        default:
            return false;
    }
    
    // Get user data
    $sql = "SELECT $idField as id, first_name, last_name FROM $table WHERE email = '$username' AND is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

/**
 * Get role name from role ID
 * @param string $roleId Role ID
 * @return string Role name
 */
function getRoleName($roleId) {
    switch ($roleId) {
        case 'R01':
            return 'Administrator';
        case 'R02':
            return 'Hon. Judge';
        case 'R03':
            return 'The Registrar';
        case 'R04':
            return 'Interpreter';
        case 'R05':
            return 'Common Staff';
        case 'R06':
            return 'Lawyer';
        case 'R07':
            return 'Police';
        default:
            return 'Unknown';
    }
}

/**
 * Check if user has access to a specific page
 * @param string $roleId User role ID
 * @param string $page Page to check access for
 * @return bool True if user has access, false otherwise
 */
function hasPageAccess($roleId, $page) {
    // Define page access by role
    $pageAccess = [
        'R01' => ['dashboard', 'users', 'staff', 'courts', 'roles', 'settings', 'reports', 'approve', 'cases', 'appeals', 'motions', 'judgements', 'warrants', 'parties', 'dailycaseactivities', 'orders', 'notifications', 'fines', 'notes', 'lawyers', 'police', 'profile'],
        'R02' => ['dashboard', 'cases', 'appeals', 'judgements', 'warrants', 'orders', 'notes', 'profile'],
        'R03' => ['dashboard', 'cases', 'appeals', 'motions', 'parties', 'dailycaseactivities', 'notifications', 'fines', 'profile'],
        'R04' => ['dashboard', 'cases', 'dailycaseactivities', 'notes', 'profile'],
        'R05' => ['dashboard', 'cases', 'dailycaseactivities', 'notifications', 'profile'],
        'R06' => ['dashboard', 'cases', 'motions', 'notes', 'profile'],
        'R07' => ['dashboard', 'cases', 'warrants', 'profile']
    ];
    
    // Check if role exists in access list
    if (!isset($pageAccess[$roleId])) {
        return false;
    }
    
    // Check if page is in allowed pages for role
    return in_array($page, $pageAccess[$roleId]);
}

/**
 * Log user out
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}
?>
