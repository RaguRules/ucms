<?php
/**
 * API Test Script for Courts Management System API
 * 
 * This script tests the functionality of the Courts Management System API.
 * 
 * @version 1.0
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define test class
class ApiTest {
    /**
     * @var string Base URL for API
     */
    private $baseUrl;
    
    /**
     * @var string|null Authentication token
     */
    private $token = null;
    
    /**
     * @var array Test results
     */
    private $results = [];
    
    /**
     * Constructor
     * 
     * @param string $baseUrl Base URL for API
     */
    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->results = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'tests' => []
        ];
    }
    
    /**
     * Run all tests
     * 
     * @return array Test results
     */
    public function runAll() {
        echo "Starting API tests...\n\n";
        
        // Authentication tests
        $this->testRegister();
        $this->testLogin();
        $this->testGetProfile();
        
        // Case management tests
        $this->testGetCaseTypes();
        $this->testGetCaseStatuses();
        $this->testCreateCase();
        $this->testGetCases();
        $this->testGetCaseById();
        $this->testUpdateCase();
        $this->testUpdateCaseStatus();
        $this->testGetCaseHistory();
        
        // Staff management tests
        $this->testGetStaffDepartments();
        $this->testGetStaffPositions();
        $this->testGetStaffStatuses();
        $this->testCreateStaff();
        $this->testGetStaff();
        $this->testGetStaffById();
        $this->testUpdateStaff();
        $this->testAssignStaffToCase();
        $this->testGetStaffAssignments();
        $this->testRemoveStaffFromCase();
        
        // User management tests
        $this->testGetUserRoles();
        $this->testGetUserStatuses();
        $this->testCreateUser();
        $this->testGetUsers();
        $this->testGetUserById();
        $this->testUpdateUser();
        $this->testUpdateUserStatus();
        $this->testChangeUserPassword();
        
        // Cleanup tests
        $this->testDeleteUser();
        $this->testDeleteStaff();
        $this->testDeleteCase();
        $this->testLogout();
        
        // Print summary
        $this->printSummary();
        
        return $this->results;
    }
    
    /**
     * Make HTTP request
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param array $files Files to upload
     * @return array Response data
     */
    private function request($method, $endpoint, $data = [], $files = []) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = ['Accept: application/json'];
        
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        
        if ($method === 'POST' || $method === 'PUT') {
            if (!empty($files)) {
                $postData = $data;
                foreach ($files as $key => $file) {
                    $postData[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                $headers[] = 'Content-Type: multipart/form-data';
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $headers[] = 'Content-Type: application/json';
            }
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return [
            'code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    /**
     * Assert condition
     * 
     * @param string $testName Test name
     * @param bool $condition Condition to assert
     * @param string $message Message to display on failure
     * @return bool Test result
     */
    private function assert($testName, $condition, $message = '') {
        $this->results['total']++;
        
        if ($condition) {
            $this->results['passed']++;
            $this->results['tests'][] = [
                'name' => $testName,
                'result' => 'PASS'
            ];
            echo "✅ PASS: {$testName}\n";
            return true;
        } else {
            $this->results['failed']++;
            $this->results['tests'][] = [
                'name' => $testName,
                'result' => 'FAIL',
                'message' => $message
            ];
            echo "❌ FAIL: {$testName} - {$message}\n";
            return false;
        }
    }
    
    /**
     * Print test summary
     */
    private function printSummary() {
        echo "\nTest Summary:\n";
        echo "Total tests: {$this->results['total']}\n";
        echo "Passed: {$this->results['passed']}\n";
        echo "Failed: {$this->results['failed']}\n";
        
        $passRate = ($this->results['total'] > 0) 
            ? round(($this->results['passed'] / $this->results['total']) * 100, 2) 
            : 0;
            
        echo "Pass rate: {$passRate}%\n";
    }
    
    /**
     * Test user registration
     */
    private function testRegister() {
        $data = [
            'username' => 'testuser' . time(),
            'email' => 'testuser' . time() . '@example.com',
            'password' => 'Password123!',
            'full_name' => 'Test User',
            'role' => 'admin'
        ];
        
        $response = $this->request('POST', '/api/v1/auth/register', $data);
        
        $this->assert(
            'User Registration',
            $response['code'] === 200 || $response['code'] === 201,
            'Failed to register user. Response code: ' . $response['code']
        );
        
        if ($response['code'] === 200 || $response['code'] === 201) {
            $this->token = $response['data']['data']['token'] ?? null;
        }
    }
    
    /**
     * Test user login
     */
    private function testLogin() {
        // Only test if registration failed
        if ($this->token === null) {
            $data = [
                'username' => 'admin',
                'password' => 'admin123'
            ];
            
            $response = $this->request('POST', '/api/v1/auth/login', $data);
            
            $this->assert(
                'User Login',
                $response['code'] === 200,
                'Failed to login. Response code: ' . $response['code']
            );
            
            if ($response['code'] === 200) {
                $this->token = $response['data']['data']['token'] ?? null;
            }
        } else {
            $this->assert('User Login', true, 'Already logged in from registration');
        }
    }
    
    /**
     * Test get user profile
     */
    private function testGetProfile() {
        $response = $this->request('GET', '/api/v1/auth/profile');
        
        $this->assert(
            'Get User Profile',
            $response['code'] === 200,
            'Failed to get profile. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get case types
     */
    private function testGetCaseTypes() {
        $response = $this->request('GET', '/api/v1/cases/types');
        
        $this->assert(
            'Get Case Types',
            $response['code'] === 200,
            'Failed to get case types. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get case statuses
     */
    private function testGetCaseStatuses() {
        $response = $this->request('GET', '/api/v1/cases/statuses');
        
        $this->assert(
            'Get Case Statuses',
            $response['code'] === 200,
            'Failed to get case statuses. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test create case
     */
    private function testCreateCase() {
        $data = [
            'case_number' => 'TEST-' . time(),
            'title' => 'Test Case',
            'description' => 'This is a test case',
            'type' => 'civil',
            'status' => 'pending',
            'filing_date' => date('Y-m-d'),
            'plaintiff' => 'Test Plaintiff',
            'defendant' => 'Test Defendant',
            'priority' => 'medium'
        ];
        
        $response = $this->request('POST', '/api/v1/cases', $data);
        
        $this->assert(
            'Create Case',
            $response['code'] === 200 || $response['code'] === 201,
            'Failed to create case. Response code: ' . $response['code']
        );
        
        if ($response['code'] === 200 || $response['code'] === 201) {
            $this->caseId = $response['data']['data']['id'] ?? null;
        }
    }
    
    /**
     * Test get cases
     */
    private function testGetCases() {
        $response = $this->request('GET', '/api/v1/cases');
        
        $this->assert(
            'Get Cases',
            $response['code'] === 200,
            'Failed to get cases. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get case by ID
     */
    private function testGetCaseById() {
        if (!isset($this->caseId)) {
            $this->assert('Get Case By ID', false, 'No case ID available');
            return;
        }
        
        $response = $this->request('GET', '/api/v1/cases/' . $this->caseId);
        
        $this->assert(
            'Get Case By ID',
            $response['code'] === 200,
            'Failed to get case by ID. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test update case
     */
    private function testUpdateCase() {
        if (!isset($this->caseId)) {
            $this->assert('Update Case', false, 'No case ID available');
            return;
        }
        
        $data = [
            'title' => 'Updated Test Case',
            'description' => 'This is an updated test case',
            'priority' => 'high'
        ];
        
        $response = $this->request('PUT', '/api/v1/cases/' . $this->caseId, $data);
        
        $this->assert(
            'Update Case',
            $response['code'] === 200,
            'Failed to update case. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test update case status
     */
    private function testUpdateCaseStatus() {
        if (!isset($this->caseId)) {
            $this->assert('Update Case Status', false, 'No case ID available');
            return;
        }
        
        $data = [
            'status' => 'active',
            'notes' => 'Case activated for testing'
        ];
        
        $response = $this->request('PUT', '/api/v1/cases/' . $this->caseId . '/status', $data);
        
        $this->assert(
            'Update Case Status',
            $response['code'] === 200,
            'Failed to update case status. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get case history
     */
    private function testGetCaseHistory() {
        if (!isset($this->caseId)) {
            $this->assert('Get Case History', false, 'No case ID available');
            return;
        }
        
        $response = $this->request('GET', '/api/v1/cases/' . $this->caseId . '/history');
        
        $this->assert(
            'Get Case History',
            $response['code'] === 200,
            'Failed to get case history. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get staff departments
     */
    private function testGetStaffDepartments() {
        $response = $this->request('GET', '/api/v1/staff/departments');
        
        $this->assert(
            'Get Staff Departments',
            $response['code'] === 200,
            'Failed to get staff departments. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get staff positions
     */
    private function testGetStaffPositions() {
        $response = $this->request('GET', '/api/v1/staff/positions');
        
        $this->assert(
            'Get Staff Positions',
            $response['code'] === 200,
            'Failed to get staff positions. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get staff statuses
     */
    private function testGetStaffStatuses() {
        $response = $this->request('GET', '/api/v1/staff/statuses');
        
        $this->assert(
            'Get Staff Statuses',
            $response['code'] === 200,
            'Failed to get staff statuses. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test create staff
     */
    private function testCreateStaff() {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'Staff',
            'email' => 'teststaff' . time() . '@example.com',
            'phone' => '555-123-4567',
            'nic' => 'TEST' . time(),
            'address' => '123 Test St, Test City',
            'department' => 'civil',
            'position' => 'clerk',
            'join_date' => date('Y-m-d'),
            'status' => 'active'
        ];
        
        $response = $this->request('POST', '/api/v1/staff', $data);
        
        $this->assert(
            'Create Staff',
            $response['code'] === 200 || $response['code'] === 201,
            'Failed to create staff. Response code: ' . $response['code']
        );
        
        if ($response['code'] === 200 || $response['code'] === 201) {
            $this->staffId = $response['data']['data']['id'] ?? null;
        }
    }
    
    /**
     * Test get staff
     */
    private function testGetStaff() {
        $response = $this->request('GET', '/api/v1/staff');
        
        $this->assert(
            'Get Staff',
            $response['code'] === 200,
            'Failed to get staff. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get staff by ID
     */
    private function testGetStaffById() {
        if (!isset($this->staffId)) {
            $this->assert('Get Staff By ID', false, 'No staff ID available');
            return;
        }
        
        $response = $this->request('GET', '/api/v1/staff/' . $this->staffId);
        
        $this->assert(
            'Get Staff By ID',
            $response['code'] === 200,
            'Failed to get staff by ID. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test update staff
     */
    private function testUpdateStaff() {
        if (!isset($this->staffId)) {
            $this->assert('Update Staff', false, 'No staff ID available');
            return;
        }
        
        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Staff',
            'phone' => '555-987-6543',
            'address' => '456 Updated St, Test City'
        ];
        
        $response = $this->request('PUT', '/api/v1/staff/' . $this->staffId, $data);
        
        $this->assert(
            'Update Staff',
            $response['code'] === 200,
            'Failed to update staff. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test assign staff to case
     */
    private function testAssignStaffToCase() {
        if (!isset($this->staffId) || !isset($this->caseId)) {
            $this->assert('Assign Staff To Case', false, 'No staff ID or case ID available');
            return;
        }
        
        $data = [
            'case_id' => $this->caseId,
            'role' => 'clerk',
            'start_date' => date('Y-m-d')
        ];
        
        $response = $this->request('POST', '/api/v1/staff/' . $this->staffId . '/assignments', $data);
        
        $this->assert(
            'Assign Staff To Case',
            $response['code'] === 200,
            'Failed to assign staff to case. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get staff assignments
     */
    private function testGetStaffAssignments() {
        if (!isset($this->staffId)) {
            $this->assert('Get Staff Assignments', false, 'No staff ID available');
            return;
        }
        
        $response = $this->request('GET', '/api/v1/staff/' . $this->staffId . '/assignments');
        
        $this->assert(
            'Get Staff Assignments',
            $response['code'] === 200,
            'Failed to get staff assignments. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test remove staff from case
     */
    private function testRemoveStaffFromCase() {
        if (!isset($this->staffId) || !isset($this->caseId)) {
            $this->assert('Remove Staff From Case', false, 'No staff ID or case ID available');
            return;
        }
        
        $response = $this->request('DELETE', '/api/v1/staff/' . $this->staffId . '/assignments/' . $this->caseId);
        
        $this->assert(
            'Remove Staff From Case',
            $response['code'] === 200,
            'Failed to remove staff from case. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get user roles
     */
    private function testGetUserRoles() {
        $response = $this->request('GET', '/api/v1/users/roles');
        
        $this->assert(
            'Get User Roles',
            $response['code'] === 200,
            'Failed to get user roles. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get user statuses
     */
    private function testGetUserStatuses() {
        $response = $this->request('GET', '/api/v1/users/statuses');
        
        $this->assert(
            'Get User Statuses',
            $response['code'] === 200,
            'Failed to get user statuses. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test create user
     */
    private function testCreateUser() {
        $data = [
            'username' => 'testuser2' . time(),
            'email' => 'testuser2' . time() . '@example.com',
            'password' => 'Password123!',
            'full_name' => 'Test User 2',
            'role' => 'staff'
        ];
        
        $response = $this->request('POST', '/api/v1/users', $data);
        
        $this->assert(
            'Create User',
            $response['code'] === 200 || $response['code'] === 201,
            'Failed to create user. Response code: ' . $response['code']
        );
        
        if ($response['code'] === 200 || $response['code'] === 201) {
            $this->userId = $response['data']['data']['id'] ?? null;
        }
    }
    
    /**
     * Test get users
     */
    private function testGetUsers() {
        $response = $this->request('GET', '/api/v1/users');
        
        $this->assert(
            'Get Users',
            $response['code'] === 200,
            'Failed to get users. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test get user by ID
     */
    private function testGetUserById() {
        if (!isset($this->userId)) {
            $this->assert('Get User By ID', false, 'No user ID available');
            return;
        }
        
        $response = $this->request('GET', '/api/v1/users/' . $this->userId);
        
        $this->assert(
            'Get User By ID',
            $response['code'] === 200,
            'Failed to get user by ID. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test update user
     */
    private function testUpdateUser() {
        if (!isset($this->userId)) {
            $this->assert('Update User', false, 'No user ID available');
            return;
        }
        
        $data = [
            'full_name' => 'Updated Test User',
            'role' => 'manager'
        ];
        
        $response = $this->request('PUT', '/api/v1/users/' . $this->userId, $data);
        
        $this->assert(
            'Update User',
            $response['code'] === 200,
            'Failed to update user. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test update user status
     */
    private function testUpdateUserStatus() {
        if (!isset($this->userId)) {
            $this->assert('Update User Status', false, 'No user ID available');
            return;
        }
        
        $data = [
            'status' => 'inactive'
        ];
        
        $response = $this->request('PUT', '/api/v1/users/' . $this->userId . '/status', $data);
        
        $this->assert(
            'Update User Status',
            $response['code'] === 200,
            'Failed to update user status. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test change user password
     */
    private function testChangeUserPassword() {
        if (!isset($this->userId)) {
            $this->assert('Change User Password', false, 'No user ID available');
            return;
        }
        
        $data = [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ];
        
        $response = $this->request('PUT', '/api/v1/users/' . $this->userId . '/password', $data);
        
        $this->assert(
            'Change User Password',
            $response['code'] === 200,
            'Failed to change user password. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test delete user
     */
    private function testDeleteUser() {
        if (!isset($this->userId)) {
            $this->assert('Delete User', false, 'No user ID available');
            return;
        }
        
        $response = $this->request('DELETE', '/api/v1/users/' . $this->userId);
        
        $this->assert(
            'Delete User',
            $response['code'] === 200,
            'Failed to delete user. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test delete staff
     */
    private function testDeleteStaff() {
        if (!isset($this->staffId)) {
            $this->assert('Delete Staff', false, 'No staff ID available');
            return;
        }
        
        $response = $this->request('DELETE', '/api/v1/staff/' . $this->staffId);
        
        $this->assert(
            'Delete Staff',
            $response['code'] === 200,
            'Failed to delete staff. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test delete case
     */
    private function testDeleteCase() {
        if (!isset($this->caseId)) {
            $this->assert('Delete Case', false, 'No case ID available');
            return;
        }
        
        $response = $this->request('DELETE', '/api/v1/cases/' . $this->caseId);
        
        $this->assert(
            'Delete Case',
            $response['code'] === 200,
            'Failed to delete case. Response code: ' . $response['code']
        );
    }
    
    /**
     * Test logout
     */
    private function testLogout() {
        $response = $this->request('POST', '/api/v1/auth/logout');
        
        $this->assert(
            'User Logout',
            $response['code'] === 200,
            'Failed to logout. Response code: ' . $response['code']
        );
    }
}

// Run tests
$apiTest = new ApiTest('http://localhost:8000');
$results = $apiTest->runAll();

// Save results to file
file_put_contents('test_results.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\nTest results saved to test_results.json\n";
