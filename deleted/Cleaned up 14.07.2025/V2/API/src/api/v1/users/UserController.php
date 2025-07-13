<?php
/**
 * UserController Class for Courts Management System API
 * 
 * This class handles user management API endpoints.
 * 
 * @version 1.0
 */

namespace Courts\Api\V1\Users;

use Courts\Core\Request;
use Courts\Core\Response;
use Courts\Core\Validator;
use Courts\Models\User;

class UserController {
    /**
     * @var User User model instance
     */
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Get all users with filtering and pagination
     * 
     * @param Request $request Request object
     * @return void
     */
    public function index(Request $request): void {
        // Get query parameters
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);
        
        // Build filters
        $filters = [
            'search' => $request->get('search'),
            'role' => $request->get('role'),
            'status' => $request->get('status')
        ];
        
        // Remove empty filters
        $filters = array_filter($filters);
        
        // Get users
        $result = $this->userModel->getAll($filters, $page, $limit);
        
        Response::success($result, 'Users retrieved successfully');
    }
    
    /**
     * Get a user by ID
     * 
     * @param Request $request Request object
     * @return void
     */
    public function show(Request $request): void {
        $id = (int) $request->param('id');
        
        // Get user
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            Response::notFound('User not found');
            return;
        }
        
        Response::success($user, 'User retrieved successfully');
    }
    
    /**
     * Create a new user
     * 
     * @param Request $request Request object
     * @return void
     */
    public function store(Request $request): void {
        // Validate input
        $validator = new Validator($request->all(), [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|min:8|max:100',
            'full_name' => 'required|max:100',
            'role' => 'required|in:user,staff,manager,admin',
            'status' => 'in:active,inactive,suspended,locked'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Check if username or email already exists
        if ($this->userModel->usernameExists($request->get('username'))) {
            Response::error('Username already exists', 409);
            return;
        }
        
        if ($this->userModel->emailExists($request->get('email'))) {
            Response::error('Email already exists', 409);
            return;
        }
        
        // Create user
        $userData = [
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'full_name' => $request->get('full_name'),
            'role' => $request->get('role'),
            'status' => $request->get('status', 'active')
        ];
        
        $userId = $this->userModel->create($userData);
        
        if (!$userId) {
            Response::serverError('Failed to create user');
            return;
        }
        
        // Get created user
        $user = $this->userModel->getById($userId);
        
        Response::success($user, 'User created successfully', 201);
    }
    
    /**
     * Update a user
     * 
     * @param Request $request Request object
     * @return void
     */
    public function update(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if user exists
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            Response::notFound('User not found');
            return;
        }
        
        // Validate input
        $validator = new Validator($request->all(), [
            'username' => 'min:3|max:50',
            'email' => 'email|max:100',
            'password' => 'min:8|max:100',
            'full_name' => 'max:100',
            'role' => 'in:user,staff,manager,admin',
            'status' => 'in:active,inactive,suspended,locked'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Check if username or email already exists
        if ($request->has('username') && 
            $request->get('username') !== $user['username'] && 
            $this->userModel->usernameExists($request->get('username'), $id)) {
            Response::error('Username already exists', 409);
            return;
        }
        
        if ($request->has('email') && 
            $request->get('email') !== $user['email'] && 
            $this->userModel->emailExists($request->get('email'), $id)) {
            Response::error('Email already exists', 409);
            return;
        }
        
        // Update user
        $userData = [];
        
        // Only include fields that are present in the request
        foreach ([
            'username', 'email', 'password', 'full_name', 'role', 'status'
        ] as $field) {
            if ($request->has($field)) {
                $userData[$field] = $request->get($field);
            }
        }
        
        $updated = $this->userModel->update($id, $userData);
        
        if (!$updated) {
            Response::serverError('Failed to update user');
            return;
        }
        
        // Get updated user
        $user = $this->userModel->getById($id);
        
        Response::success($user, 'User updated successfully');
    }
    
    /**
     * Delete a user
     * 
     * @param Request $request Request object
     * @return void
     */
    public function destroy(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if user exists
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            Response::notFound('User not found');
            return;
        }
        
        // Prevent deleting the authenticated user
        if ($id === $request->user['id']) {
            Response::error('Cannot delete your own account', 403);
            return;
        }
        
        // Delete user
        $deleted = $this->userModel->delete($id);
        
        if (!$deleted) {
            Response::serverError('Failed to delete user');
            return;
        }
        
        Response::success(null, 'User deleted successfully');
    }
    
    /**
     * Update user status
     * 
     * @param Request $request Request object
     * @return void
     */
    public function updateStatus(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if user exists
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            Response::notFound('User not found');
            return;
        }
        
        // Validate input
        $validator = new Validator($request->all(), [
            'status' => 'required|in:active,inactive,suspended,locked'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Prevent updating the authenticated user's status
        if ($id === $request->user['id']) {
            Response::error('Cannot update your own status', 403);
            return;
        }
        
        // Update status
        $updated = $this->userModel->updateStatus($id, $request->get('status'));
        
        if (!$updated) {
            Response::serverError('Failed to update user status');
            return;
        }
        
        // Get updated user
        $user = $this->userModel->getById($id);
        
        Response::success($user, 'User status updated successfully');
    }
    
    /**
     * Change user password
     * 
     * @param Request $request Request object
     * @return void
     */
    public function changePassword(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if user exists
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            Response::notFound('User not found');
            return;
        }
        
        // Validate input
        $validator = new Validator($request->all(), [
            'password' => 'required|min:8|max:100',
            'password_confirmation' => 'required|min:8'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Check if passwords match
        if ($request->get('password') !== $request->get('password_confirmation')) {
            Response::validationError(['password_confirmation' => ['Passwords do not match']]);
            return;
        }
        
        // Update password
        $updated = $this->userModel->update($id, [
            'password' => $request->get('password')
        ]);
        
        if (!$updated) {
            Response::serverError('Failed to change password');
            return;
        }
        
        Response::success(null, 'Password changed successfully');
    }
    
    /**
     * Get user roles
     * 
     * @param Request $request Request object
     * @return void
     */
    public function roles(Request $request): void {
        $roles = $this->userModel->getRoles();
        
        Response::success($roles, 'User roles retrieved successfully');
    }
    
    /**
     * Get user statuses
     * 
     * @param Request $request Request object
     * @return void
     */
    public function statuses(Request $request): void {
        $statuses = $this->userModel->getStatuses();
        
        Response::success($statuses, 'User statuses retrieved successfully');
    }
}
