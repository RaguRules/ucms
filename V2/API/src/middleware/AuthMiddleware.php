<?php
/**
 * AuthMiddleware Class for Courts Management System API
 * 
 * This middleware handles authentication for protected API routes.
 * 
 * @version 1.0
 */

namespace Courts\Middleware;

use Courts\Core\Auth;
use Courts\Core\Request;
use Courts\Core\Response;

class AuthMiddleware {
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
     * Handle the request
     * 
     * @param Request $request Request object
     * @return void
     */
    public function handle(Request $request): void {
        // Get token from request
        $token = $request->bearerToken();
        
        if (!$token) {
            Response::unauthorized('No token provided');
            exit;
        }
        
        // Verify token
        $decoded = $this->auth->verifyToken($token);
        
        if (!$decoded) {
            Response::unauthorized('Invalid or expired token');
            exit;
        }
        
        // Add user to request for later use
        $user = $this->auth->getUserFromToken($token);
        
        if (!$user) {
            Response::unauthorized('User not found');
            exit;
        }
        
        // Store user in request for controllers to access
        $request->user = $user;
    }
}
