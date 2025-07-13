<?php
/**
 * Integration Test for Courts Management System
 * 
 * This file tests the integration between the improved index.php and the modernized staff.php and db.php files.
 * 
 * @version 2.0
 * @author Courts Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'src/db.php';
require_once 'src/security.php';

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

// Test file inclusion
function testFileInclusion() {
    $requiredFiles = [
        'index.php',
        'src/db.php',
        'src/security.php',
        'src/menu.php',
        'src/jsfunctions.php',
        'src/body.php',
        'src/footer.php',
        'src/js/jsfunctions.js',
        'src/staff.php'
    ];
    
    $results = [];
    $allExist = true;
    
    foreach ($requiredFiles as $file) {
        $exists = file_exists($file);
        $results[$file] = $exists;
        
        if (!$exists) {
            $allExist = false;
        }
    }
    
    if ($allExist) {
        return [
            'status' => 'success',
            'message' => 'All required files exist',
            'details' => $results
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Some required files are missing',
            'details' => $results
        ];
    }
}

// Test path resolution
function testPathResolution() {
    $testPaths = [
        'staff.php' => 'src/staff.php',
        'db.php' => 'src/db.php',
        'security.php' => 'src/security.php',
        'nonexistent.php' => false
    ];
    
    $results = [];
    $allResolved = true;
    
    foreach ($testPaths as $path => $expected) {
        // Simulate the path resolution logic from index.php
        $page = str_replace(['../', '..\\', '/', '\\'], '', $path);
        $resolved = false;
        
        if (file_exists($page)) {
            $resolved = $page;
        } else {
            $srcPage = "src/" . $page;
            if (file_exists($srcPage)) {
                $resolved = $srcPage;
            }
        }
        
        $success = ($resolved === $expected);
        $results[$path] = [
            'expected' => $expected,
            'resolved' => $resolved,
            'success' => $success
        ];
        
        if (!$success) {
            $allResolved = false;
        }
    }
    
    if ($allResolved) {
        return [
            'status' => 'success',
            'message' => 'Path resolution is working correctly',
            'details' => $results
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Path resolution has issues',
            'details' => $results
        ];
    }
}

// Test security integration
function testSecurityIntegration() {
    try {
        // Test CSRF token generation
        $token = Security::generateCSRFToken();
        $tokenValid = !empty($token);
        
        // Test password hashing
        $password = 'TestPassword123!';
        $hash = Security::hashPassword($password);
        $passwordValid = Security::verifyPassword($password, $hash);
        
        // Test input validation
        $emailValidation = Security::validateInput('test@example.com', 'email');
        $emailValidationCorrect = ($emailValidation['status'] === true);
        
        if ($tokenValid && $passwordValid && $emailValidationCorrect) {
            return [
                'status' => 'success',
                'message' => 'Security integration is working correctly',
                'details' => [
                    'csrf_token' => $tokenValid,
                    'password_hashing' => $passwordValid,
                    'input_validation' => $emailValidationCorrect
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Security integration has issues',
                'details' => [
                    'csrf_token' => $tokenValid,
                    'password_hashing' => $passwordValid,
                    'input_validation' => $emailValidationCorrect
                ]
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Security integration test failed: ' . $e->getMessage()
        ];
    }
}

// Run tests
$testResults['database_connection'] = testDatabaseConnection();
$testResults['file_inclusion'] = testFileInclusion();
$testResults['path_resolution'] = testPathResolution();
$testResults['security_integration'] = testSecurityIntegration();

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
    <title>Courts Management System - Integration Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-xl font-bold">Courts Management System - Integration Test</h1>
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
                            <h2 class="text-lg font-semibold text-gray-900">Overall Integration Status</h2>
                            <p class="text-sm text-gray-500">
                                <?php echo ($overallStatus === 'success') ? 'All integration tests passed successfully' : 'Some integration tests failed'; ?>
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
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Home
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
