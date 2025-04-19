<?php
// Test script to verify the admin dashboard functionality
// This file is used to test various components of the dashboard

// Database connection test
function testDatabaseConnection() {
    global $conn;
    
    if ($conn) {
        echo "Database connection: SUCCESS\n";
    } else {
        echo "Database connection: FAILED\n";
    }
}

// Authentication test
function testAuthentication() {
    // Test login with valid credentials
    $validUsername = "admin@example.com";
    $validPassword = "admin123";
    
    $result = authenticateUser($GLOBALS['conn'], $validUsername, $validPassword);
    
    if (isset($result['success']) && $result['success']) {
        echo "Authentication with valid credentials: SUCCESS\n";
    } else {
        echo "Authentication with valid credentials: FAILED\n";
    }
    
    // Test login with invalid credentials
    $invalidUsername = "invalid@example.com";
    $invalidPassword = "wrongpassword";
    
    $result = authenticateUser($GLOBALS['conn'], $invalidUsername, $invalidPassword);
    
    if (isset($result['success']) && !$result['success']) {
        echo "Authentication with invalid credentials: SUCCESS\n";
    } else {
        echo "Authentication with invalid credentials: FAILED\n";
    }
}

// RBAC test
function testRBAC() {
    // Test admin permissions
    $hasPermission = RBAC::hasPermission(ROLE_ADMIN, 'staff', 'view');
    echo "Admin has permission to view staff: " . ($hasPermission ? "YES" : "NO") . "\n";
    
    // Test judge permissions
    $hasPermission = RBAC::hasPermission(ROLE_JUDGE, 'cases', 'view');
    echo "Judge has permission to view cases: " . ($hasPermission ? "YES" : "NO") . "\n";
    
    // Test lawyer permissions
    $hasPermission = RBAC::hasPermission(ROLE_LAWYER, 'staff', 'view');
    echo "Lawyer has permission to view staff: " . ($hasPermission ? "YES" : "NO") . "\n";
    
    // Test registrar permissions
    $hasPermission = RBAC::hasPermission(ROLE_REGISTRAR, 'cases', 'add');
    echo "Registrar has permission to add cases: " . ($hasPermission ? "YES" : "NO") . "\n";
}

// Integration test
function testIntegration() {
    // Test role mapping
    $adminRole = mapExistingRoleToRBAC('Administrator');
    echo "Mapping 'Administrator' to RBAC role: " . $adminRole . "\n";
    
    // Test dashboard stats
    $stats = getDashboardStats($GLOBALS['conn'], ROLE_ADMIN, 'S0001');
    echo "Dashboard stats for admin: " . print_r($stats, true) . "\n";
}

// Run tests
function runAllTests() {
    echo "=== ADMIN DASHBOARD TEST RESULTS ===\n\n";
    
    echo "--- Database Tests ---\n";
    testDatabaseConnection();
    echo "\n";
    
    echo "--- Authentication Tests ---\n";
    testAuthentication();
    echo "\n";
    
    echo "--- RBAC Tests ---\n";
    testRBAC();
    echo "\n";
    
    echo "--- Integration Tests ---\n";
    testIntegration();
    echo "\n";
    
    echo "=== END OF TESTS ===\n";
}

// Include necessary files
include_once('config/database.php');
include_once('includes/auth.php');
include_once('includes/rbac.php');
include_once('includes/functions.php');
include_once('includes/integration.php');

// Run all tests
runAllTests();
