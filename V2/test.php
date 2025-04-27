<?php
/**
 * Test script for the Courts Management System
 * 
 * This file tests the database connection, security utilities, and staff management functionality.
 * 
 * @version 1.0
 * @author Courts Management System
 */

// Include required files
require_once 'db.php';
require_once 'security.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize test results
$testResults = [];

// Test database connection
function testDatabaseConnection() {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        if ($conn instanceof PDO) {
            return [
                'status' => 'success',
                'message' => 'Database connection successful using PDO'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: Connection is not a PDO instance'
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Database connection failed: ' . $e->getMessage()
        ];
    }
}

// Test CSRF token generation and verification
function testCSRFProtection() {
    try {
        // Generate token
        $token = Security::generateCSRFToken();
        
        if (empty($token)) {
            return [
                'status' => 'error',
                'message' => 'CSRF token generation failed: Token is empty'
            ];
        }
        
        // Verify token
        $isValid = Security::verifyCSRFToken($token);
        
        if ($isValid) {
            return [
                'status' => 'success',
                'message' => 'CSRF token generation and verification successful'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'CSRF token verification failed'
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'CSRF protection test failed: ' . $e->getMessage()
        ];
    }
}

// Test input validation
function testInputValidation() {
    try {
        $testCases = [
            ['input' => 'test@example.com', 'type' => 'email', 'expected' => true],
            ['input' => 'invalid-email', 'type' => 'email', 'expected' => false],
            ['input' => '1234567890', 'type' => 'mobile', 'expected' => true],
            ['input' => '123456', 'type' => 'mobile', 'expected' => false],
            ['input' => '123456789V', 'type' => 'nic', 'expected' => true],
            ['input' => '123456789', 'type' => 'nic', 'expected' => false],
            ['input' => 'John Doe', 'type' => 'name', 'expected' => true],
            ['input' => 'John123', 'type' => 'name', 'expected' => false],
        ];
        
        $results = [];
        $allPassed = true;
        
        foreach ($testCases as $case) {
            $result = Security::validateInput($case['input'], $case['type']);
            $passed = ($result['status'] === $case['expected']);
            
            if (!$passed) {
                $allPassed = false;
            }
            
            $results[] = [
                'input' => $case['input'],
                'type' => $case['type'],
                'expected' => $case['expected'],
                'actual' => $result['status'],
                'passed' => $passed
            ];
        }
        
        if ($allPassed) {
            return [
                'status' => 'success',
                'message' => 'Input validation tests passed',
                'details' => $results
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Some input validation tests failed',
                'details' => $results
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Input validation test failed: ' . $e->getMessage()
        ];
    }
}

// Test password hashing and verification
function testPasswordSecurity() {
    try {
        $password = 'TestPassword123!';
        
        // Hash password
        $hash = Security::hashPassword($password);
        
        if (empty($hash)) {
            return [
                'status' => 'error',
                'message' => 'Password hashing failed: Hash is empty'
            ];
        }
        
        // Verify password
        $isValid = Security::verifyPassword($password, $hash);
        $isInvalid = Security::verifyPassword('WrongPassword', $hash);
        
        if ($isValid && !$isInvalid) {
            return [
                'status' => 'success',
                'message' => 'Password hashing and verification successful'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Password verification failed: Valid=' . ($isValid ? 'true' : 'false') . ', Invalid=' . ($isInvalid ? 'true' : 'false')
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Password security test failed: ' . $e->getMessage()
        ];
    }
}

// Test database query methods
function testDatabaseQueries() {
    try {
        $db = Database::getInstance();
        
        // Test getRows
        $courts = $db->getRows("SELECT * FROM court");
        
        if ($courts === false) {
            return [
                'status' => 'error',
                'message' => 'Database query test failed: getRows returned false'
            ];
        }
        
        // Test getRow
        $court = $db->getRow("SELECT * FROM court WHERE court_id = ?", ['C01']);
        
        if ($court === false) {
            return [
                'status' => 'error',
                'message' => 'Database query test failed: getRow returned false'
            ];
        }
        
        // Test prepared statements with parameters
        $staffWithRole = $db->getRows("SELECT * FROM staff WHERE role_id = ?", ['R01']);
        
        return [
            'status' => 'success',
            'message' => 'Database query tests successful',
            'details' => [
                'courts_count' => count($courts),
                'court_sample' => $court,
                'staff_with_role_count' => $staffWithRole ? count($staffWithRole) : 0
            ]
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Database query test failed: ' . $e->getMessage()
        ];
    }
}

// Run tests
$testResults['database_connection'] = testDatabaseConnection();
$testResults['csrf_protection'] = testCSRFProtection();
$testResults['input_validation'] = testInputValidation();
$testResults['password_security'] = testPasswordSecurity();
$testResults['database_queries'] = testDatabaseQueries();

// Calculate overall test status
$overallStatus = 'success';
foreach ($testResults as $result) {
    if ($result['status'] === 'error') {
        $overallStatus = 'error';
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courts Management System - Test Results</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-xl font-bold">Courts Management System - Test Results</h1>
            </div>
            
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center">
                        <div class="mr-4">
                            <?php if ($overallStatus === 'success') { ?>
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-500">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </span>
                            <?php } else { ?>
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-100 text-red-500">
                                    <i class="fas fa-times-circle text-xl"></i>
                                </span>
                            <?php } ?>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Overall Test Status</h2>
                            <p class="text-sm text-gray-500">
                                <?php echo ($overallStatus === 'success') ? 'All tests passed successfully' : 'Some tests failed'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <?php foreach ($testResults as $testName => $result) { ?>
                        <div class="border rounded-lg overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                                <h3 class="text-md font-semibold text-gray-700"><?php echo ucwords(str_replace('_', ' ', $testName)); ?></h3>
                                <?php if ($result['status'] === 'success') { ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Success
                                    </span>
                                <?php } else { ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                <?php } ?>
                            </div>
                            <div class="px-4 py-3">
                                <p class="text-sm text-gray-600"><?php echo $result['message']; ?></p>
                                
                                <?php if (isset($result['details']) && is_array($result['details'])) { ?>
                                    <div class="mt-3">
                                        <button class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none" onclick="toggleDetails(this)">
                                            Show Details
                                        </button>
                                        <div class="hidden mt-2 bg-gray-50 p-3 rounded text-xs">
                                            <pre class="whitespace-pre-wrap"><?php echo json_encode($result['details'], JSON_PRETTY_PRINT); ?></pre>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <div class="mt-8">
                    <a href="index.php?pg=staff.php&option=view" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Staff Management
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleDetails(button) {
            const detailsDiv = button.nextElementSibling;
            const isHidden = detailsDiv.classList.contains('hidden');
            
            if (isHidden) {
                detailsDiv.classList.remove('hidden');
                button.textContent = 'Hide Details';
            } else {
                detailsDiv.classList.add('hidden');
                button.textContent = 'Show Details';
            }
        }
    </script>
</body>
</html>
