<?php
// General utility functions for the dashboard

/**
 * Get user details based on ID and role
 * @param mysqli $conn Database connection
 * @param string $userId User ID
 * @param string $roleId Role ID
 * @return array User details
 */
function getUserDetails($conn, $userId, $roleId) {
    $userId = sanitizeInput($conn, $userId);
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
            return [];
    }
    
    // Get user data
    $sql = "SELECT * FROM $table WHERE $idField = '$userId' AND is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return [];
}

/**
 * Get notifications for a user
 * @param mysqli $conn Database connection
 * @param string $userId User ID
 * @return array Notifications
 */
function getNotifications($conn, $userId) {
    $userId = sanitizeInput($conn, $userId);
    
    $sql = "SELECT * FROM notifications WHERE receiver_id = '$userId' AND status != 'read' ORDER BY notification_id DESC LIMIT 10";
    $result = mysqli_query($conn, $sql);
    
    $notifications = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }
    
    return $notifications;
}

/**
 * Get messages for a user (placeholder - actual implementation would depend on messaging system)
 * @param mysqli $conn Database connection
 * @param string $userId User ID
 * @return array Messages
 */
function getMessages($conn, $userId) {
    // This is a placeholder. In a real system, you would query a messages table
    return [];
}

/**
 * Get user theme preference
 * @param string $userId User ID
 * @return string Theme class (light-mode or dark-mode)
 */
function getUserTheme($userId) {
    // This is a placeholder. In a real system, you would get this from user preferences
    // For now, default to light mode
    return 'light-mode';
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date) || $date == '0000-00-00') {
        return 'N/A';
    }
    
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Get court name from court ID
 * @param string $courtId Court ID
 * @return string Court name
 */
function getCourtName($courtId) {
    switch ($courtId) {
        case 'C01':
            return "Magistrate's Court";
        case 'C02':
            return "District Court";
        case 'C03':
            return "High Court";
        default:
            return "Unknown";
    }
}

/**
 * Generate dashboard statistics based on user role
 * @param mysqli $conn Database connection
 * @param string $roleId User role ID
 * @param string $userId User ID
 * @return array Statistics
 */
function getDashboardStats($conn, $roleId, $userId) {
    $stats = [
        'total_cases' => 0,
        'active_cases' => 0,
        'pending_judgements' => 0,
        'warrants_issued' => 0
    ];
    
    // Total cases
    $sql = "SELECT COUNT(*) as count FROM cases";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['total_cases'] = $row['count'];
    }
    
    // Active cases
    $sql = "SELECT COUNT(*) as count FROM cases WHERE is_active = 1";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['active_cases'] = $row['count'];
    }
    
    // Pending judgements
    $sql = "SELECT COUNT(*) as count FROM judgements";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['pending_judgements'] = $row['count'];
    }
    
    // Warrants issued
    $sql = "SELECT COUNT(*) as count FROM warrants";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['warrants_issued'] = $row['count'];
    }
    
    // Role-specific stats
    switch ($roleId) {
        case 'R01': // Administrator
            // Add admin-specific stats
            $sql = "SELECT COUNT(*) as count FROM staff WHERE is_active = 1";
            $result = mysqli_query($conn, $sql);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $stats['total_staff'] = $row['count'];
            }
            
            $sql = "SELECT COUNT(*) as count FROM registration WHERE status = 'Pending'";
            $result = mysqli_query($conn, $sql);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $stats['pending_registrations'] = $row['count'];
            }
            break;
            
        case 'R02': // Judge
            // Add judge-specific stats
            $sql = "SELECT COUNT(*) as count FROM cases WHERE staff_id = '$userId'";
            $result = mysqli_query($conn, $sql);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $stats['assigned_cases'] = $row['count'];
            }
            break;
            
        case 'R06': // Lawyer
            // Add lawyer-specific stats
            $sql = "SELECT COUNT(*) as count FROM cases WHERE plaintiff_lawyer = '$userId' OR defendant_lawyer = '$userId'";
            $result = mysqli_query($conn, $sql);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $stats['my_cases'] = $row['count'];
            }
            break;
            
        case 'R07': // Police
            // Add police-specific stats
            $sql = "SELECT COUNT(*) as count FROM warrants WHERE status = 'Active'";
            $result = mysqli_query($conn, $sql);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $stats['active_warrants'] = $row['count'];
            }
            break;
    }
    
    return $stats;
}

/**
 * Generate a unique ID for various entities
 * @param string $prefix Prefix for the ID
 * @param int $length Length of the numeric part
 * @return string Generated ID
 */
function generateUniqueId($prefix, $length = 4) {
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    $uniqueNumber = substr($timestamp . $random, -$length);
    return $prefix . str_pad($uniqueNumber, $length, '0', STR_PAD_LEFT);
}

/**
 * Check if a string contains a substring
 * @param string $haystack The string to search in
 * @param string $needle The substring to search for
 * @return bool True if substring is found, false otherwise
 */
function containsString($haystack, $needle) {
    return strpos($haystack, $needle) !== false;
}

/**
 * Truncate text to a specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append String to append if truncated
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $append;
}

/**
 * Convert a date to a relative time string (e.g., "2 hours ago")
 * @param string $date Date string
 * @return string Relative time
 */
function timeAgo($date) {
    if (empty($date)) {
        return 'N/A';
    }
    
    $timestamp = strtotime($date);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'Just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($date);
    }
}
?>
