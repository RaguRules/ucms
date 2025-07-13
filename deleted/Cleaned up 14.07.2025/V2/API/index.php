<?php
/**
 * API Entry Point for Courts Management System API
 * 
 * This file serves as the main entry point for the API.
 * 
 * @version 1.0
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Initialize request
$request = new \Courts\Core\Request();

// Load routes
$authRoutes = require_once __DIR__ . '/src/api/v1/auth/routes.php';
$caseRoutes = require_once __DIR__ . '/src/api/v1/cases/routes.php';
$staffRoutes = require_once __DIR__ . '/src/api/v1/staff/routes.php';
$userRoutes = require_once __DIR__ . '/src/api/v1/users/routes.php';

// Merge routes
$router = new \Courts\Core\Router();
$router->merge($authRoutes);
$router->merge($caseRoutes);
$router->merge($staffRoutes);
$router->merge($userRoutes);

// Handle request
try {
    $router->dispatch($request);
} catch (\Exception $e) {
    \Courts\Core\Response::serverError($e->getMessage());
}
