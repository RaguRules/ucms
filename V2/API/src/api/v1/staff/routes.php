<?php
/**
 * Staff Management Routes for Courts Management System API
 * 
 * This file defines the routes for staff management API endpoints.
 * 
 * @version 1.0
 */

use Courts\Api\V1\Staff\StaffController;
use Courts\Core\Router;
use Courts\Middleware\AuthMiddleware;
use Courts\Middleware\RoleMiddleware;

// Get router instance
$router = new Router();
$router->setBasePath('/api/v1');

// Staff management routes
$router->get('/staff', [StaffController::class, 'index'], [AuthMiddleware::class]);
$router->get('/staff/departments', [StaffController::class, 'departments'], [AuthMiddleware::class]);
$router->get('/staff/positions', [StaffController::class, 'positions'], [AuthMiddleware::class]);
$router->get('/staff/statuses', [StaffController::class, 'statuses'], [AuthMiddleware::class]);
$router->get('/staff/{id}', [StaffController::class, 'show'], [AuthMiddleware::class]);
$router->get('/staff/{id}/assignments', [StaffController::class, 'assignments'], [AuthMiddleware::class]);
$router->post('/staff', [StaffController::class, 'store'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->put('/staff/{id}', [StaffController::class, 'update'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->delete('/staff/{id}', [StaffController::class, 'destroy'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->post('/staff/{id}/assignments', [StaffController::class, 'assignToCase'], [AuthMiddleware::class, new RoleMiddleware(['admin', 'manager'])]);
$router->delete('/staff/{id}/assignments/{case_id}', [StaffController::class, 'removeFromCase'], [AuthMiddleware::class, new RoleMiddleware(['admin', 'manager'])]);

return $router;
