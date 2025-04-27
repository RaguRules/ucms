<?php
/**
 * Footer Component for Courts Management System
 * 
 * This file provides the footer section for the Courts Management System.
 * 
 * @version 2.0
 * @author Courts Management System
 */
?>

<!-- Footer -->
<footer class="bg-gray-800 text-white mt-auto">
    <!-- Main Footer -->
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <h3 class="text-lg font-semibold mb-4">About Us</h3>
                <p class="text-gray-400 mb-4">
                    The Kilinochchi Courts Management System provides a modern, efficient solution for managing court cases, schedules, and documents.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="text-gray-400 hover:text-white transition-colors">Home</a>
                    </li>
                    <li>
                        <a href="index.php?pg=about.php" class="text-gray-400 hover:text-white transition-colors">About</a>
                    </li>
                    <li>
                        <a href="index.php?pg=services.php" class="text-gray-400 hover:text-white transition-colors">Services</a>
                    </li>
                    <li>
                        <a href="index.php?pg=contact.php" class="text-gray-400 hover:text-white transition-colors">Contact</a>
                    </li>
                </ul>
            </div>
            
            <!-- Services -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Services</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="index.php?pg=case-management.php" class="text-gray-400 hover:text-white transition-colors">Case Management</a>
                    </li>
                    <li>
                        <a href="index.php?pg=scheduling.php" class="text-gray-400 hover:text-white transition-colors">Court Scheduling</a>
                    </li>
                    <li>
                        <a href="index.php?pg=document-management.php" class="text-gray-400 hover:text-white transition-colors">Document Management</a>
                    </li>
                    <li>
                        <a href="index.php?pg=reporting.php" class="text-gray-400 hover:text-white transition-colors">Reporting & Analytics</a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                <ul class="space-y-2 text-gray-400">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-3"></i>
                        <span>A9 Road, Kilinochchi, Northern Province, Sri Lanka</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-phone-alt mr-3"></i>
                        <span>+94 21 228 5678</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-envelope mr-3"></i>
                        <span>info@kilinochchi-courts.gov.lk</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Copyright -->
    <div class="bg-gray-900 py-4">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm mb-4 md:mb-0">
                    &copy; <?php echo date('Y'); ?> Kilinochchi Courts Management System. All rights reserved.
                </div>
                <div class="text-gray-400 text-sm">
                    <a href="index.php?pg=privacy-policy.php" class="hover:text-white transition-colors">Privacy Policy</a>
                    <span class="mx-2">|</span>
                    <a href="index.php?pg=terms-of-service.php" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>
