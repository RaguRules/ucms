<?php
/**
 * RoleMiddleware Class for Courts Management System API
 * 
 * This middleware handles role-based access control for protected API routes.
 * 
 * @version 1.0
 */

namespace Courts\Middleware;

use Courts\Core\Auth;
use Courts\Core\Request;
use Courts\Core\Response;

class RoleMiddleware {
    /**
     * @var Auth Auth instance
     */
    private Auth $auth;
    
    /**
     * @var array Allowed roles
     */
    private array $roles;
    
    /**
     * Constructor
     * 
     * @param array|string $roles Allowed roles
     */
    public function __construct($roles) {
        $this->auth = new Auth();
        $this->roles = is_array($roles) ? $roles : [$roles];
    }
    
    /**
     * Handle the request
     * 
     * @param Request $request Request object
     * @return void
     */
    public function handle(Request $request): void {
        // Check if user is set by AuthMiddleware
        if (!isset($request->user)) {
            Response::unauthorized('Authentication required');
            exit;
        }
        
        // Check if user has required role
        if (!$this->auth->hasRole($request->user, $this->roles)) {
            Response::forbidden('You do not have permission to access this resource');
            exit;
        }
    }
}
