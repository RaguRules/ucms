<?php
/**
 * Authentication Routes for Courts Management System API
 * 
 * This file defines the routes for authentication-related endpoints.
 * 
 * @version 1.0
 */

use Courts\Api\V1\Auth\AuthController;
use Courts\Core\Router;
use Courts\Middleware\AuthMiddleware;

// Get router instance
$router = new Router();
$router->setBasePath('/api/v1');

// Authentication routes
$router->post('/auth/register', [AuthController::class, 'register']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/profile', [AuthController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/auth/password/reset-request', [AuthController::class, 'requestPasswordReset']);
$router->post('/auth/password/reset', [AuthController::class, 'resetPassword']);
$router->post('/auth/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

return $router;
