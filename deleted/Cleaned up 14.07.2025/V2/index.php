<?php
/**
 * Main Index File for Courts Management System
 * 
 * This file serves as the main entry point for the Courts Management System,
 * handling session management and page routing.
 * 
 * @version 2.0
 * @author Courts Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize user type and username
$system_usertype = $_SESSION["LOGIN_USERTYPE"] ?? "GUEST";
$system_username = $_SESSION["LOGIN_USERNAME"] ?? "";

// Include required files
require_once('src/db.php');
require_once('src/security.php');
require_once('src/menu.php');
require_once('src/jsfunctions.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Kilinochchi Courts Management System</title>
    <meta name="description" content="Kilinochchi Courts Management System for efficient case management, scheduling, and document handling.">
    <meta name="keywords" content="Kilinochchi Courts, Case Management, Court System, Legal System">
    
    <!-- Favicon -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'court-blue': {
                            light: '#3fbaeb',
                            DEFAULT: '#0369a1',
                            dark: '#0c87b8',
                        },
                        'court-gray': {
                            light: '#f3f4f6',
                            DEFAULT: '#9ca3af',
                            dark: '#4b5563',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                    }
                },
            },
            plugins: [],
        }
    </script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        /* Custom styles that can't be handled by Tailwind */
        .scroll-top {
            position: fixed;
            visibility: hidden;
            opacity: 0;
            right: 15px;
            bottom: 15px;
            z-index: 99999;
            background: #0369a1;
            width: 40px;
            height: 40px;
            border-radius: 4px;
            transition: all 0.4s;
        }
        
        .scroll-top i {
            font-size: 24px;
            color: #fff;
            line-height: 0;
        }
        
        .scroll-top:hover {
            background: #0c87b8;
        }
        
        .scroll-top.active {
            visibility: visible;
            opacity: 1;
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Main Content -->
    <div class="min-h-screen flex flex-col">
        <?php
        // Include the appropriate page based on the URL parameter
        if (isset($_GET['pg'])) {
            $page = $_GET['pg'];
            // Security check to prevent directory traversal
            $page = str_replace(['../', '..\\', '/', '\\'], '', $page);
            
            // Check if file exists before including
            if (file_exists($page)) {
                include_once($page);
            } else {
                // Fallback to src directory if file not found in root
                $srcPage = "src/" . $page;
                if (file_exists($srcPage)) {
                    include_once($srcPage);
                } else {
                    // Display error if page not found
                    echo '<div class="container mx-auto px-4 py-8">';
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
                    echo '<strong class="font-bold">Error!</strong>';
                    echo '<span class="block sm:inline"> Page not found.</span>';
                    echo '</div>';
                    echo '</div>';
                }
            }
        } else {
            // Include default pages if no specific page is requested
            include_once("src/body.php");
            include_once("src/footer.php");
        }
        ?>
    </div>
    
    <!-- Scroll to Top Button -->
    <a href="#" id="scroll-top" class="scroll-top flex items-center justify-center">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <!-- Scripts -->
    <script src="src/js/jsfunctions.js"></script>
    <script>
        // Scroll to top functionality
        document.addEventListener('DOMContentLoaded', function() {
            const scrollTop = document.getElementById('scroll-top');
            
            if (scrollTop) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 100) {
                        scrollTop.classList.add('active');
                    } else {
                        scrollTop.classList.remove('active');
                    }
                });
                
                scrollTop.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
            
            // Initialize any components that need JavaScript
            initializeComponents();
        });
        
        // Function to initialize components
        function initializeComponents() {
            // Add any component initialization here
            // This replaces the functionality from the various vendor scripts
            
            // Example: Initialize dropdowns
            const dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.getElementById(this.getAttribute('data-target'));
                    if (target) {
                        target.classList.toggle('hidden');
                    }
                });
            });
        }
    </script>
</body>
</html>
