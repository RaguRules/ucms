<?php
/**
 * Router Class for Courts Management System API
 * 
 * This class handles routing of API requests to appropriate controllers.
 * 
 * @version 1.0
 */

namespace Courts\Core;

use Courts\Core\Request;
use Courts\Core\Response;

class Router {
    /**
     * @var array Routes configuration
     */
    private array $routes = [];
    
    /**
     * @var array Middleware to apply to routes
     */
    private array $middleware = [];
    
    /**
     * @var string Base path for API
     */
    private string $basePath = '/api/v1';
    
    /**
     * Add a GET route
     * 
     * @param string $path Route path
     * @param callable|array $handler Route handler
     * @param array $middleware Middleware to apply
     * @return Router
     */
    public function get(string $path, $handler, array $middleware = []): Router {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    /**
     * Add a POST route
     * 
     * @param string $path Route path
     * @param callable|array $handler Route handler
     * @param array $middleware Middleware to apply
     * @return Router
     */
    public function post(string $path, $handler, array $middleware = []): Router {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    /**
     * Add a PUT route
     * 
     * @param string $path Route path
     * @param callable|array $handler Route handler
     * @param array $middleware Middleware to apply
     * @return Router
     */
    public function put(string $path, $handler, array $middleware = []): Router {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    /**
     * Add a DELETE route
     * 
     * @param string $path Route path
     * @param callable|array $handler Route handler
     * @param array $middleware Middleware to apply
     * @return Router
     */
    public function delete(string $path, $handler, array $middleware = []): Router {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    /**
     * Add a route for any HTTP method
     * 
     * @param string $path Route path
     * @param callable|array $handler Route handler
     * @param array $middleware Middleware to apply
     * @return Router
     */
    public function any(string $path, $handler, array $middleware = []): Router {
        return $this->addRoute('ANY', $path, $handler, $middleware);
    }
    
    /**
     * Add middleware to be applied to all routes
     * 
     * @param string|callable $middleware Middleware to apply
     * @return Router
     */
    public function middleware($middleware): Router {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * Set base path for API
     * 
     * @param string $path Base path
     * @return Router
     */
    public function setBasePath(string $path): Router {
        $this->basePath = $path;
        return $this;
    }
    
    /**
     * Handle the current request
     * 
     * @param Request $request Request object
     * @return void
     */
    public function handle(Request $request): void {
        $method = $request->getMethod();
        $path = $request->getPath();
        
        // Remove base path from request path
        if (strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
        
        // Default to / if path is empty
        if (empty($path)) {
            $path = '/';
        }
        
        // Find matching route
        $route = $this->findRoute($method, $path);
        
        if ($route) {
            // Extract route parameters
            $params = $this->extractParams($route['pattern'], $path);
            $request->setParams($params);
            
            // Apply global middleware
            foreach ($this->middleware as $middleware) {
                $this->applyMiddleware($middleware, $request);
            }
            
            // Apply route-specific middleware
            foreach ($route['middleware'] as $middleware) {
                $this->applyMiddleware($middleware, $request);
            }
            
            // Call route handler
            $this->callHandler($route['handler'], $request);
        } else {
            // Route not found
            Response::notFound('Route not found: ' . $method . ' ' . $path);
        }
    }
    
    /**
     * Add a route
     * 
     * @param string $method HTTP method
     * @param string $path Route path
     * @param callable|array $handler Route handler
     * @param array $middleware Middleware to apply
     * @return Router
     */
    private function addRoute(string $method, string $path, $handler, array $middleware = []): Router {
        // Convert path to regex pattern
        $pattern = $this->pathToPattern($path);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware
        ];
        
        return $this;
    }
    
    /**
     * Find a route matching the request method and path
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @return array|null Matching route or null if not found
     */
    private function findRoute(string $method, string $path): ?array {
        foreach ($this->routes as $route) {
            if (($route['method'] === $method || $route['method'] === 'ANY') && 
                preg_match($route['pattern'], $path)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Convert a path to a regex pattern
     * 
     * @param string $path Route path
     * @return string Regex pattern
     */
    private function pathToPattern(string $path): string {
        // Replace route parameters with regex patterns
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        
        // Add start and end anchors
        $pattern = '/^' . $pattern . '$/';
        
        return $pattern;
    }
    
    /**
     * Extract parameters from a path
     * 
     * @param string $pattern Route pattern
     * @param string $path Request path
     * @return array Extracted parameters
     */
    private function extractParams(string $pattern, string $path): array {
        $params = [];
        
        if (preg_match($pattern, $path, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
        }
        
        return $params;
    }
    
    /**
     * Apply middleware to a request
     * 
     * @param string|callable $middleware Middleware to apply
     * @param Request $request Request object
     * @return void
     */
    private function applyMiddleware($middleware, Request $request): void {
        if (is_callable($middleware)) {
            $middleware($request);
        } else if (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            $instance->handle($request);
        }
    }
    
    /**
     * Call a route handler
     * 
     * @param callable|array $handler Route handler
     * @param Request $request Request object
     * @return void
     */
    private function callHandler($handler, Request $request): void {
        if (is_callable($handler)) {
            $handler($request);
        } else if (is_array($handler) && count($handler) === 2) {
            [$controller, $method] = $handler;
            
            if (is_string($controller) && class_exists($controller)) {
                $instance = new $controller();
                
                if (method_exists($instance, $method)) {
                    $instance->$method($request);
                    return;
                }
            }
            
            Response::serverError('Invalid route handler');
        } else {
            Response::serverError('Invalid route handler');
        }
    }
}
