<nav class="top-navbar">
    <div class="container-fluid">
        <!-- Left side - Page title -->
        <div class="page-title">
            <h4><?php echo ucfirst($page); ?></h4>
        </div>
        
        <!-- Right side - User actions -->
        <div class="user-actions">
            <!-- Search -->
            <div class="search-box">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="search">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search..." name="query">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn notification-btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                    <span class="badge rounded-pill bg-danger"><?php echo $notificationCount; ?></span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                    <li class="dropdown-header">
                        <h6>Notifications</h6>
                        <?php if ($notificationCount > 0): ?>
                        <span class="badge rounded-pill bg-primary"><?php echo $notificationCount; ?> New</span>
                        <?php endif; ?>
                    </li>
                    
                    <li><hr class="dropdown-divider"></li>
                    
                    <?php if ($notificationCount > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                        <li>
                            <a class="dropdown-item notification-item" href="#">
                                <div class="notification-content">
                                    <div class="icon">
                                        <?php 
                                        $icon = 'info-circle';
                                        if (strpos($notification['type'], 'case') !== false) {
                                            $icon = 'folder';
                                        } elseif (strpos($notification['type'], 'warrant') !== false) {
                                            $icon = 'search';
                                        } elseif (strpos($notification['type'], 'judgement') !== false) {
                                            $icon = 'gavel';
                                        }
                                        ?>
                                        <i class="fas fa-<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="details">
                                        <p><?php echo $notification['message']; ?></p>
                                        <small class="text-muted">
                                            <?php echo timeAgo($notification['created_at'] ?? date('Y-m-d H:i:s')); ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><div class="dropdown-item">No new notifications</div></li>
                    <?php endif; ?>
                    
                    <li><hr class="dropdown-divider"></li>
                    
                    <li>
                        <a class="dropdown-item text-center" href="index.php?page=notifications">
                            Show all notifications
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Theme Toggle -->
            <button class="btn theme-toggle-btn" id="theme-toggle">
                <i class="fas fa-moon"></i>
            </button>
            
            <!-- User Profile -->
            <div class="dropdown">
                <button class="btn user-dropdown-btn" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $profileImage; ?>" alt="User Avatar" class="user-avatar-small">
                    <span class="d-none d-md-inline"><?php echo $userDetails['first_name']; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <div class="dropdown-item user-info">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $profileImage; ?>" alt="User Avatar" class="user-avatar-medium">
                                <div>
                                    <h6 class="mb-0"><?php echo $userDetails['first_name'] . ' ' . $userDetails['last_name']; ?></h6>
                                    <small class="text-muted"><?php echo getRoleName($userRole); ?></small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-user me-2"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="index.php?page=profile&action=password"><i class="fas fa-key me-2"></i> Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
