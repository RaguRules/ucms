<?php
/**
 * AuthController Class for Courts Management System API
 * 
 * This class handles authentication-related API endpoints.
 * 
 * @version 1.0
 */

namespace Courts\Api\V1\Auth;

use Courts\Core\Auth;
use Courts\Core\Request;
use Courts\Core\Response;
use Courts\Core\Validator;

class AuthController {
    /**
     * @var Auth Auth instance
     */
    private Auth $auth;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->auth = new Auth();
    }
    
    /**
     * Register a new user
     * 
     * @param Request $request Request object
     * @return void
     */
    public function register(Request $request): void {
        // Validate input
        $validator = new Validator($request->all(), [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|min:8|max:100',
            'password_confirmation' => 'required|min:8',
            'full_name' => 'required|max:100',
            'role' => 'required|in:user,staff,admin'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Register user
        $userData = [
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'full_name' => $request->get('full_name'),
            'role' => $request->get('role'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $user = $this->auth->register($userData);
        
        if (!$user) {
            Response::error('Username or email already exists', 409);
            return;
        }
        
        // Generate token
        $token = $this->auth->generateToken($user);
        
        // Return user data with token
        Response::success([
            'user' => $user,
            'token' => $token
        ], 'User registered successfully');
    }
    
    /**
     * Login a user
     * 
     * @param Request $request Request object
     * @return void
     */
    public function login(Request $request): void {
        // Validate input
        $validator = new Validator($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Authenticate user
        $user = $this->auth->authenticate(
            $request->get('username'),
            $request->get('password')
        );
        
        if (!$user) {
            Response::error('Invalid credentials', 401);
            return;
        }
        
        // Generate token
        $token = $this->auth->generateToken($user);
        
        // Return user data with token
        Response::success([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }
    
    /**
     * Get current user profile
     * 
     * @param Request $request Request object
     * @return void
     */
    public function profile(Request $request): void {
        // Get token from request
        $token = $request->bearerToken();
        
        if (!$token) {
            Response::unauthorized('No token provided');
            return;
        }
        
        // Get user from token
        $user = $this->auth->getUserFromToken($token);
        
        if (!$user) {
            Response::unauthorized('Invalid token');
            return;
        }
        
        // Return user data
        Response::success(['user' => $user], 'Profile retrieved successfully');
    }
    
    /**
     * Request password reset
     * 
     * @param Request $request Request object
     * @return void
     */
    public function requestPasswordReset(Request $request): void {
        // Validate input
        $validator = new Validator($request->all(), [
            'email' => 'required|email'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // In a real application, you would:
        // 1. Generate a reset token
        // 2. Store it in the database with an expiration time
        // 3. Send an email to the user with a reset link
        
        // For this example, we'll just return a success message
        Response::success(null, 'Password reset instructions sent to your email');
    }
    
    /**
     * Reset password
     * 
     * @param Request $request Request object
     * @return void
     */
    public function resetPassword(Request $request): void {
        // Validate input
        $validator = new Validator($request->all(), [
            'token' => 'required',
            'password' => 'required|min:8|max:100',
            'password_confirmation' => 'required|min:8'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // In a real application, you would:
        // 1. Verify the reset token
        // 2. Update the user's password
        // 3. Invalidate the token
        
        // For this example, we'll just return a success message
        Response::success(null, 'Password reset successfully');
    }
    
    /**
     * Logout a user
     * 
     * @param Request $request Request object
     * @return void
     */
    public function logout(Request $request): void {
        // In a stateless API using JWT, there's no server-side logout
        // The client should discard the token
        
        // For this example, we'll just return a success message
        Response::success(null, 'Logout successful');
    }
}
