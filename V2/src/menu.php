<?php
/**
 * Menu Component for Courts Management System
 * 
 * This file provides the navigation menu for the Courts Management System,
 * with different options based on user role.
 * 
 * @version 2.0
 * @author Courts Management System
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user type from session
$system_usertype = $_SESSION["LOGIN_USERTYPE"] ?? "GUEST";
$system_username = $_SESSION["LOGIN_USERNAME"] ?? "";

/**
 * Generate menu items based on user role
 * 
 * @param string $userType User role/type
 * @return array Menu items
 */
function getMenuItems($userType) {
    $menuItems = [];
    
    // Common menu items for all users
    $menuItems[] = [
        'title' => 'Home',
        'url' => 'index.php',
        'icon' => 'fas fa-home',
        'active' => !isset($_GET['pg'])
    ];
    
    // Menu items based on user role
    switch ($userType) {
        case 'ADMIN':
            $menuItems[] = [
                'title' => 'Dashboard',
                'url' => 'index.php?pg=dashboard.php',
                'icon' => 'fas fa-tachometer-alt',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'dashboard.php'
            ];
            $menuItems[] = [
                'title' => 'Staff Management',
                'url' => 'index.php?pg=staff.php&option=view',
                'icon' => 'fas fa-users',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'staff.php'
            ];
            $menuItems[] = [
                'title' => 'Case Management',
                'url' => 'index.php?pg=case.php&option=view',
                'icon' => 'fas fa-gavel',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'case.php'
            ];
            $menuItems[] = [
                'title' => 'Reports',
                'url' => 'index.php?pg=reports.php',
                'icon' => 'fas fa-chart-bar',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'reports.php'
            ];
            $menuItems[] = [
                'title' => 'Settings',
                'url' => 'index.php?pg=settings.php',
                'icon' => 'fas fa-cog',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'settings.php'
            ];
            break;
            
        case 'JUDGE':
            $menuItems[] = [
                'title' => 'Dashboard',
                'url' => 'index.php?pg=dashboard.php',
                'icon' => 'fas fa-tachometer-alt',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'dashboard.php'
            ];
            $menuItems[] = [
                'title' => 'My Cases',
                'url' => 'index.php?pg=case.php&option=view',
                'icon' => 'fas fa-gavel',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'case.php'
            ];
            $menuItems[] = [
                'title' => 'Calendar',
                'url' => 'index.php?pg=calendar.php',
                'icon' => 'fas fa-calendar-alt',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'calendar.php'
            ];
            break;
            
        case 'STAFF':
            $menuItems[] = [
                'title' => 'Dashboard',
                'url' => 'index.php?pg=dashboard.php',
                'icon' => 'fas fa-tachometer-alt',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'dashboard.php'
            ];
            $menuItems[] = [
                'title' => 'Case Records',
                'url' => 'index.php?pg=case.php&option=view',
                'icon' => 'fas fa-folder-open',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'case.php'
            ];
            $menuItems[] = [
                'title' => 'Schedule',
                'url' => 'index.php?pg=schedule.php',
                'icon' => 'fas fa-calendar-alt',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'schedule.php'
            ];
            break;
            
        default: // GUEST
            $menuItems[] = [
                'title' => 'About',
                'url' => 'index.php?pg=about.php',
                'icon' => 'fas fa-info-circle',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'about.php'
            ];
            $menuItems[] = [
                'title' => 'Contact',
                'url' => 'index.php?pg=contact.php',
                'icon' => 'fas fa-envelope',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'contact.php'
            ];
            $menuItems[] = [
                'title' => 'Login',
                'url' => 'index.php?pg=login.php',
                'icon' => 'fas fa-sign-in-alt',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'login.php'
            ];
            $menuItems[] = [
                'title' => 'Register',
                'url' => 'index.php?pg=register.php',
                'icon' => 'fas fa-user-plus',
                'active' => isset($_GET['pg']) && $_GET['pg'] == 'register.php'
            ];
            break;
    }
    
    return $menuItems;
}

// Get menu items based on user type
$menuItems = getMenuItems($system_usertype);
?>

<!-- Navigation Menu -->
<nav class="bg-court-blue shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="index.php" class="text-white font-bold text-xl">
                        Kilinochchi Courts
                    </a>
                </div>
                
                <!-- Primary Navigation Menu -->
                <div class="hidden md:ml-6 md:flex md:items-center md:space-x-4">
                    <?php foreach ($menuItems as $item): ?>
                    <a href="<?php echo $item['url']; ?>" class="<?php echo $item['active'] ? 'bg-court-blue-dark text-white' : 'text-white hover:bg-court-blue-dark hover:text-white'; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        <i class="<?php echo $item['icon']; ?> mr-1"></i>
                        <?php echo $item['title']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- User Menu -->
            <div class="hidden md:flex md:items-center">
                <?php if ($system_usertype !== "GUEST"): ?>
                <div class="ml-3 relative">
                    <div>
                        <button type="button" class="dropdown-toggle flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-court-blue focus:ring-white" data-target="user-menu">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-court-blue-dark flex items-center justify-center text-white">
                                <?php echo substr($system_username, 0, 1); ?>
                            </div>
                        </button>
                    </div>
                    
                    <div id="user-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-10">
                        <a href="index.php?pg=profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-1"></i> Profile
                        </a>
                        <a href="index.php?pg=settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog mr-1"></i> Settings
                        </a>
                        <a href="index.php?pg=logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile menu button -->
            <div class="flex items-center md:hidden">
                <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-white hover:text-white hover:bg-court-blue-dark focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <?php foreach ($menuItems as $item): ?>
            <a href="<?php echo $item['url']; ?>" class="<?php echo $item['active'] ? 'bg-court-blue-dark text-white' : 'text-white hover:bg-court-blue-dark hover:text-white'; ?> block px-3 py-2 rounded-md text-base font-medium">
                <i class="<?php echo $item['icon']; ?> mr-1"></i>
                <?php echo $item['title']; ?>
            </a>
            <?php endforeach; ?>
            
            <?php if ($system_usertype !== "GUEST"): ?>
            <div class="pt-4 pb-3 border-t border-court-blue-dark">
                <div class="flex items-center px-5">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-court-blue-dark flex items-center justify-center text-white">
                            <?php echo substr($system_username, 0, 1); ?>
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-white"><?php echo $system_username; ?></div>
                        <div class="text-sm font-medium text-court-blue-light"><?php echo $system_usertype; ?></div>
                    </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                    <a href="index.php?pg=profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-court-blue-dark">
                        <i class="fas fa-user mr-1"></i> Profile
                    </a>
                    <a href="index.php?pg=settings.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-court-blue-dark">
                        <i class="fas fa-cog mr-1"></i> Settings
                    </a>
                    <a href="index.php?pg=logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-court-blue-dark">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(dropdown => {
                const targetId = dropdown.getAttribute('data-target');
                const target = document.getElementById(targetId);
                
                if (target && !dropdown.contains(event.target) && !target.contains(event.target)) {
                    target.classList.add('hidden');
                }
            });
        });
    });
</script>
