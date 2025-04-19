<?php
// Dashboard page for Administrator role
include_once('includes/rbac.php');

// Check if user has permission to view this page
if (!checkPermission('dashboard', 'view')) {
    header("Location: index.php");
    exit;
}

// Get dashboard statistics
$stats = getDashboardStats($conn, $userRole, $userId);

// Get pending registrations for admin
$pendingRegistrations = 0;
if ($userRole == ROLE_ADMIN) {
    $sql = "SELECT COUNT(*) as count FROM registration WHERE status = 'Pending'";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $pendingRegistrations = $row['count'];
    }
}

// Get recent activities
$recentActivities = [];
$sql = "SELECT * FROM dailycaseactivities ORDER BY activity_date DESC LIMIT 5";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivities[] = $row;
    }
}

// Get upcoming cases
$upcomingCases = [];
$sql = "SELECT * FROM cases WHERE next_date >= CURDATE() ORDER BY next_date ASC LIMIT 5";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $upcomingCases[] = $row;
    }
}
?>

<div class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>Welcome, <?php echo $userDetails['first_name']; ?>!</h1>
                <p class="text-muted">
                    <?php echo getRoleName($userRole); ?> Dashboard | 
                    <?php echo date('l, F j, Y'); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-primary" onclick="window.location.href='index.php?page=profile'">
                    <i class="fas fa-user-cog"></i> Manage Profile
                </button>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row stats-cards">
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-folder"></i>
                    </div>
                    <h5 class="stats-title">Total Cases</h5>
                    <h3 class="stats-number"><?php echo number_format($stats['total_cases']); ?></h3>
                    <p class="stats-desc">All registered cases</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="stats-icon bg-success">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h5 class="stats-title">Active Cases</h5>
                    <h3 class="stats-number"><?php echo number_format($stats['active_cases']); ?></h3>
                    <p class="stats-desc">Currently active cases</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h5 class="stats-title">Pending Judgements</h5>
                    <h3 class="stats-number"><?php echo number_format($stats['pending_judgements']); ?></h3>
                    <p class="stats-desc">Awaiting judgement</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-search"></i>
                    </div>
                    <h5 class="stats-title">Warrants Issued</h5>
                    <h3 class="stats-number"><?php echo number_format($stats['warrants_issued']); ?></h3>
                    <p class="stats-desc">Total warrants issued</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($userRole == ROLE_ADMIN): ?>
    <!-- Admin-specific Stats -->
    <div class="row stats-cards">
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h5 class="stats-title">Total Staff</h5>
                    <h3 class="stats-number"><?php echo number_format($stats['total_staff']); ?></h3>
                    <p class="stats-desc">Active staff members</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="stats-icon bg-secondary">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h5 class="stats-title">Pending Registrations</h5>
                    <h3 class="stats-number"><?php echo number_format($pendingRegistrations); ?></h3>
                    <p class="stats-desc">Awaiting approval</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="stats-icon bg-dark">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <h5 class="stats-title">Courts</h5>
                    <h3 class="stats-number">3</h3>
                    <p class="stats-desc">Active courts</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions for Admin -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="index.php?page=staff&action=add" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-user-plus mb-2 d-block fs-4"></i>
                                Add New Staff
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="index.php?page=approve" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-user-check mb-2 d-block fs-4"></i>
                                Approve Registrations
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="index.php?page=cases&action=add" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-folder-plus mb-2 d-block fs-4"></i>
                                Register New Case
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="index.php?page=reports" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-chart-bar mb-2 d-block fs-4"></i>
                                Generate Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Activities and Upcoming Cases -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Activities</h5>
                    <a href="index.php?page=dailycaseactivities" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Case</th>
                                    <th>Summary</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentActivities)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent activities found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentActivities as $activity): ?>
                                    <tr>
                                        <td><?php echo $activity['case_name']; ?></td>
                                        <td><?php echo truncateText($activity['summary'], 30); ?></td>
                                        <td><?php echo formatDate($activity['activity_date']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($activity['is_taken'] == 1) ? 'success' : 'warning'; ?>">
                                                <?php echo ($activity['is_taken'] == 1) ? 'Completed' : 'Pending'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Upcoming Cases</h5>
                    <a href="index.php?page=cases" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Case ID</th>
                                    <th>Case Name</th>
                                    <th>Next Date</th>
                                    <th>Purpose</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcomingCases)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No upcoming cases found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($upcomingCases as $case): ?>
                                    <tr>
                                        <td><?php echo $case['case_id']; ?></td>
                                        <td><?php echo $case['case_name']; ?></td>
                                        <td><?php echo formatDate($case['next_date']); ?></td>
                                        <td><?php echo truncateText($case['for_what'], 30); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
