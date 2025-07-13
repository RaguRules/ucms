<?php
// Main index file for the Unified Courts Management System Admin Dashboard
session_start();
include_once('config/database.php');
include_once('includes/auth.php');
include_once('includes/rbac.php');
include_once('includes/functions.php');
include_once('includes/integration.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Get user information
$userId = $_SESSION['USER_ID'];
$userRole = $_SESSION['ROLE_ID'];
$username = $_SESSION['USERNAME'];
$firstName = $_SESSION['FIRST_NAME'];
$lastName = $_SESSION['LAST_NAME'];
$roleName = getRoleName($userRole);

// Get user details
$userDetails = getUserDetails($conn, $userId, $userRole);

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Check if user has access to the page
if (!canAccessPage($page, $userRole)) {
    $page = 'dashboard'; // Default to dashboard if no access
}

// Define valid pages
$validPages = [
    'dashboard', 'profile', 'staff', 'cases', 'appeals', 'judgements', 
    'warrants', 'orders', 'parties', 'dailycaseactivities', 'notifications', 
    'fines', 'notes', 'lawyers', 'police', 'approve', 'reports', 'settings'
];

// Validate page
if (!in_array($page, $validPages)) {
    $page = 'dashboard';
}

// Page titles
$pageTitles = [
    'dashboard' => 'Dashboard',
    'profile' => 'My Profile',
    'staff' => 'Staff Management',
    'cases' => 'Case Management',
    'appeals' => 'Appeals',
    'judgements' => 'Judgements',
    'warrants' => 'Warrants',
    'orders' => 'Orders',
    'parties' => 'Parties',
    'dailycaseactivities' => 'Daily Case Activities',
    'notifications' => 'Notifications',
    'fines' => 'Fines',
    'notes' => 'Notes',
    'lawyers' => 'Lawyers',
    'police' => 'Police',
    'approve' => 'Approve Registrations',
    'reports' => 'Reports',
    'settings' => 'Settings'
];

// Get page title
$pageTitle = $pageTitles[$page] ?? 'Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Unified Courts Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>

<body class="light-mode">
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include_once('includes/sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <?php include_once('includes/topnav.php'); ?>
            
            <!-- Content Wrapper -->
            <div class="content-wrapper">
                <div class="content-container">
                    <?php
                    // Include page content
                    $pageFile = 'pages/' . $page . '.php';
                    if (file_exists($pageFile)) {
                        include_once($pageFile);
                    } else {
                        echo '<div class="alert alert-danger">Page not found.</div>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include_once('includes/footer.php'); ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
