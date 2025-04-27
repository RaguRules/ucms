<?php
/**
 * Case Management Routes for Courts Management System API
 * 
 * This file defines the routes for case management API endpoints.
 * 
 * @version 1.0
 */

use Courts\Api\V1\Cases\CaseController;
use Courts\Core\Router;
use Courts\Middleware\AuthMiddleware;
use Courts\Middleware\RoleMiddleware;

// Get router instance
$router = new Router();
$router->setBasePath('/api/v1');

// Case management routes
$router->get('/cases', [CaseController::class, 'index'], [AuthMiddleware::class]);
$router->get('/cases/types', [CaseController::class, 'types'], [AuthMiddleware::class]);
$router->get('/cases/statuses', [CaseController::class, 'statuses'], [AuthMiddleware::class]);
$router->get('/cases/{id}', [CaseController::class, 'show'], [AuthMiddleware::class]);
$router->get('/cases/{id}/history', [CaseController::class, 'history'], [AuthMiddleware::class]);
$router->post('/cases', [CaseController::class, 'store'], [AuthMiddleware::class, new RoleMiddleware(['admin', 'staff'])]);
$router->put('/cases/{id}', [CaseController::class, 'update'], [AuthMiddleware::class, new RoleMiddleware(['admin', 'staff'])]);
$router->delete('/cases/{id}', [CaseController::class, 'destroy'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->put('/cases/{id}/status', [CaseController::class, 'updateStatus'], [AuthMiddleware::class, new RoleMiddleware(['admin', 'staff'])]);

return $router;
