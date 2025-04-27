<?php
/**
 * User Management Routes for Courts Management System API
 * 
 * This file defines the routes for user management API endpoints.
 * 
 * @version 1.0
 */

use Courts\Api\V1\Users\UserController;
use Courts\Core\Router;
use Courts\Middleware\AuthMiddleware;
use Courts\Middleware\RoleMiddleware;

// Get router instance
$router = new Router();
$router->setBasePath('/api/v1');

// User management routes
$router->get('/users', [UserController::class, 'index'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->get('/users/roles', [UserController::class, 'roles'], [AuthMiddleware::class]);
$router->get('/users/statuses', [UserController::class, 'statuses'], [AuthMiddleware::class]);
$router->get('/users/{id}', [UserController::class, 'show'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->post('/users', [UserController::class, 'store'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->put('/users/{id}', [UserController::class, 'update'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->delete('/users/{id}', [UserController::class, 'destroy'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->put('/users/{id}/status', [UserController::class, 'updateStatus'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->put('/users/{id}/password', [UserController::class, 'changePassword'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);

return $router;
