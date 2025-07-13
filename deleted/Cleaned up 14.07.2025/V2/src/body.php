<?php
/**
 * Main Body Content for Courts Management System
 * 
 * This file provides the main content for the home page of the Courts Management System.
 * 
 * @version 2.0
 * @author Courts Management System
 */
?>

<!-- Hero Section -->
<div class="bg-court-blue-dark text-white">
    <div class="container mx-auto px-4 py-16 md:py-24">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Kilinochchi Courts Management System</h1>
                <p class="text-lg mb-6">A modern, efficient system for managing court cases, schedules, and documents.</p>
                <div class="flex flex-wrap gap-4">
                    <a href="index.php?pg=about.php" class="bg-white text-court-blue-dark hover:bg-gray-100 px-6 py-3 rounded-md font-semibold transition-colors">
                        Learn More
                    </a>
                    <?php if (!isset($_SESSION["LOGIN_USERTYPE"]) || $_SESSION["LOGIN_USERTYPE"] == "GUEST"): ?>
                    <a href="index.php?pg=register.php" class="bg-transparent hover:bg-white hover:text-court-blue-dark border border-white text-white px-6 py-3 rounded-md font-semibold transition-colors">
                        Register
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="md:w-1/2">
                <img src="assets/img/courts-hero.jpg" alt="Kilinochchi Courts" class="rounded-lg shadow-xl max-w-full h-auto">
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Key Features</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gray-50 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="w-12 h-12 bg-court-blue rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-gavel text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Case Management</h3>
                <p class="text-gray-600">Efficiently manage court cases with comprehensive tracking, scheduling, and document management.</p>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="w-12 h-12 bg-court-blue rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-calendar-alt text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Scheduling</h3>
                <p class="text-gray-600">Streamlined court scheduling with automated notifications and conflict detection.</p>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="w-12 h-12 bg-court-blue rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Staff Management</h3>
                <p class="text-gray-600">Comprehensive staff management with role-based access control and performance tracking.</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Our Impact</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-4xl font-bold text-court-blue mb-2">5,000+</div>
                <div class="text-gray-600">Cases Managed</div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-4xl font-bold text-court-blue mb-2">98%</div>
                <div class="text-gray-600">On-time Hearings</div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-4xl font-bold text-court-blue mb-2">50+</div>
                <div class="text-gray-600">Staff Members</div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-4xl font-bold text-court-blue mb-2">30%</div>
                <div class="text-gray-600">Faster Resolution</div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">What People Say</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-court-blue-light rounded-full flex items-center justify-center mr-4">
                        <span class="text-white font-bold">JD</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">Hon. Judge Dharmalingam</h4>
                        <p class="text-gray-600 text-sm">District Court</p>
                    </div>
                </div>
                <p class="text-gray-700 italic">"The Courts Management System has revolutionized how we handle cases. The scheduling and document management features have significantly improved our efficiency."</p>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-court-blue-light rounded-full flex items-center justify-center mr-4">
                        <span class="text-white font-bold">RS</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">Rajini Sivakumar</h4>
                        <p class="text-gray-600 text-sm">Court Registrar</p>
                    </div>
                </div>
                <p class="text-gray-700 italic">"Managing staff schedules and case assignments has never been easier. The system provides all the tools we need to run the court efficiently."</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="py-16 bg-court-blue text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to Get Started?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Join the Kilinochchi Courts Management System today and experience the benefits of a modern, efficient court management solution.</p>
        
        <?php if (!isset($_SESSION["LOGIN_USERTYPE"]) || $_SESSION["LOGIN_USERTYPE"] == "GUEST"): ?>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="index.php?pg=register.php" class="bg-white text-court-blue hover:bg-gray-100 px-8 py-3 rounded-md font-semibold transition-colors">
                Register Now
            </a>
            <a href="index.php?pg=login.php" class="bg-transparent hover:bg-white hover:text-court-blue border border-white text-white px-8 py-3 rounded-md font-semibold transition-colors">
                Login
            </a>
        </div>
        <?php else: ?>
        <a href="index.php?pg=dashboard.php" class="bg-white text-court-blue hover:bg-gray-100 px-8 py-3 rounded-md font-semibold transition-colors">
            Go to Dashboard
        </a>
        <?php endif; ?>
    </div>
</div>
