<?php
// Get user profile image
$profileImage = $userDetails['image_path'] ?? 'assets/img/default-avatar.png';
?>

<div class="sidebar">
    <!-- Sidebar Header with Logo -->
    <div class="sidebar-header">
        <div class="logo">
            <a href="index.php">
                <img src="assets/img/sri-lanka-emblem.png" alt="Courts Logo" class="logo-icon">
                <span class="logo-text">UCMS</span>
            </a>
        </div>
        <button id="sidebar-toggle-btn" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <!-- User Profile Section -->
    <div class="user-profile">
        <div class="user-avatar">
            <img src="<?php echo $profileImage; ?>" alt="User Avatar">
        </div>
        <div class="user-info">
            <h5><?php echo $userDetails['first_name'] . ' ' . $userDetails['last_name']; ?></h5>
            <span class="user-role"><?php echo getRoleName($userRole); ?></span>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <!-- Dashboard - All users -->
            <li class="nav-item">
                <a href="index.php?page=dashboard" class="nav-link <?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Case Management Section -->
            <?php if (in_array($userRole, ['R01', 'R02', 'R03', 'R04', 'R05', 'R06', 'R07'])): ?>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-gavel"></i>
                    <span>Case Management</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="index.php?page=cases" class="<?php echo ($page == 'cases') ? 'active' : ''; ?>">
                            <i class="fas fa-folder"></i> Cases
                        </a>
                    </li>
                    
                    <?php if (in_array($userRole, ['R01', 'R02', 'R03'])): ?>
                    <li>
                        <a href="index.php?page=appeals" class="<?php echo ($page == 'appeals') ? 'active' : ''; ?>">
                            <i class="fas fa-balance-scale"></i> Appeals
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R03', 'R06'])): ?>
                    <li>
                        <a href="index.php?page=motions" class="<?php echo ($page == 'motions') ? 'active' : ''; ?>">
                            <i class="fas fa-file-alt"></i> Motions
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R02'])): ?>
                    <li>
                        <a href="index.php?page=judgements" class="<?php echo ($page == 'judgements') ? 'active' : ''; ?>">
                            <i class="fas fa-gavel"></i> Judgements
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R02', 'R07'])): ?>
                    <li>
                        <a href="index.php?page=warrants" class="<?php echo ($page == 'warrants') ? 'active' : ''; ?>">
                            <i class="fas fa-search"></i> Warrants
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R03'])): ?>
                    <li>
                        <a href="index.php?page=parties" class="<?php echo ($page == 'parties') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Parties
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- Court Operations Section -->
            <?php if (in_array($userRole, ['R01', 'R02', 'R03', 'R04', 'R05'])): ?>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-university"></i>
                    <span>Court Operations</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <?php if (in_array($userRole, ['R01', 'R03', 'R04', 'R05'])): ?>
                    <li>
                        <a href="index.php?page=dailycaseactivities" class="<?php echo ($page == 'dailycaseactivities') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-day"></i> Daily Activities
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R02'])): ?>
                    <li>
                        <a href="index.php?page=orders" class="<?php echo ($page == 'orders') ? 'active' : ''; ?>">
                            <i class="fas fa-file-signature"></i> Orders
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R03', 'R05'])): ?>
                    <li>
                        <a href="index.php?page=notifications" class="<?php echo ($page == 'notifications') ? 'active' : ''; ?>">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R03'])): ?>
                    <li>
                        <a href="index.php?page=fines" class="<?php echo ($page == 'fines') ? 'active' : ''; ?>">
                            <i class="fas fa-money-bill-wave"></i> Fines
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($userRole, ['R01', 'R02', 'R04', 'R06'])): ?>
                    <li>
                        <a href="index.php?page=notes" class="<?php echo ($page == 'notes') ? 'active' : ''; ?>">
                            <i class="fas fa-sticky-note"></i> Notes
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- Administration Section - Admin Only -->
            <?php if ($userRole == 'R01'): ?>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-cogs"></i>
                    <span>Administration</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="index.php?page=courts" class="<?php echo ($page == 'courts') ? 'active' : ''; ?>">
                            <i class="fas fa-landmark"></i> Courts
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=staff" class="<?php echo ($page == 'staff') ? 'active' : ''; ?>">
                            <i class="fas fa-user-tie"></i> Staff
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=lawyers" class="<?php echo ($page == 'lawyers') ? 'active' : ''; ?>">
                            <i class="fas fa-user-graduate"></i> Lawyers
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=police" class="<?php echo ($page == 'police') ? 'active' : ''; ?>">
                            <i class="fas fa-user-shield"></i> Police
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=roles" class="<?php echo ($page == 'roles') ? 'active' : ''; ?>">
                            <i class="fas fa-user-tag"></i> Roles
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=approve" class="<?php echo ($page == 'approve') ? 'active' : ''; ?>">
                            <i class="fas fa-user-check"></i> Approve Users
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- Settings & Profile - All Users -->
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-user-cog"></i>
                    <span>Settings</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="index.php?page=profile" class="<?php echo ($page == 'profile') ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <?php if ($userRole == 'R01'): ?>
                    <li>
                        <a href="index.php?page=settings" class="<?php echo ($page == 'settings') ? 'active' : ''; ?>">
                            <i class="fas fa-sliders-h"></i> System Settings
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <p>© 2025 Unified Courts Management System</p>
    </div>
</div>
