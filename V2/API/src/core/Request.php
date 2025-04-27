<?php
/**
 * Request Class for Courts Management System API
 * 
 * This class handles API request parsing and validation.
 * 
 * @version 1.0
 */

namespace Courts\Core;

class Request {
    /**
     * @var array Request data
     */
    private array $data = [];
    
    /**
     * @var array Request headers
     */
    private array $headers = [];
    
    /**
     * @var string Request method
     */
    private string $method;
    
    /**
     * @var string Request path
     */
    private string $path;
    
    /**
     * @var array URL parameters
     */
    private array $params = [];
    
    /**
     * @var array Query parameters
     */
    private array $query = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->headers = $this->getRequestHeaders();
        $this->query = $_GET ?? [];
        
        // Parse request body based on content type
        $this->parseRequestBody();
    }
    
    /**
     * Get request method
     * 
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }
    
    /**
     * Get request path
     * 
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }
    
    /**
     * Get all request data
     * 
     * @return array
     */
    public function all(): array {
        return array_merge($this->data, $this->query);
    }
    
    /**
     * Get a specific request value
     * 
     * @param string $key Key to get
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        if (isset($this->query[$key])) {
            return $this->query[$key];
        }
        
        return $default;
    }
    
    /**
     * Check if request has a specific key
     * 
     * @param string $key Key to check
     * @return bool
     */
    public function has(string $key): bool {
        return isset($this->data[$key]) || isset($this->query[$key]);
    }
    
    /**
     * Get all request headers
     * 
     * @return array
     */
    public function headers(): array {
        return $this->headers;
    }
    
    /**
     * Get a specific header
     * 
     * @param string $key Header name
     * @param mixed $default Default value if header doesn't exist
     * @return mixed
     */
    public function header(string $key, $default = null) {
        $key = strtolower($key);
        
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
        
        return $default;
    }
    
    /**
     * Get URL parameters
     * 
     * @return array
     */
    public function params(): array {
        return $this->params;
    }
    
    /**
     * Set URL parameters
     * 
     * @param array $params URL parameters
     * @return void
     */
    public function setParams(array $params): void {
        $this->params = $params;
    }
    
    /**
     * Get a specific URL parameter
     * 
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed
     */
    public function param(string $key, $default = null) {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        
        return $default;
    }
    
    /**
     * Get query parameters
     * 
     * @return array
     */
    public function query(): array {
        return $this->query;
    }
    
    /**
     * Get bearer token from Authorization header
     * 
     * @return string|null
     */
    public function bearerToken(): ?string {
        $header = $this->header('Authorization');
        
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    public function isAjax(): bool {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * Check if request expects JSON
     * 
     * @return bool
     */
    public function expectsJson(): bool {
        return $this->isAjax() || 
               strpos($this->header('Accept', ''), 'application/json') !== false;
    }
    
    /**
     * Parse request body based on content type
     * 
     * @return void
     */
    private function parseRequestBody(): void {
        if (in_array($this->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $contentType = $this->header('Content-Type', '');
            
            if (strpos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $this->data = json_decode($json, true) ?? [];
            } else {
                $this->data = $_POST ?? [];
                
                // Handle file uploads
                if (!empty($_FILES)) {
                    foreach ($_FILES as $key => $file) {
                        $this->data[$key] = $file;
                    }
                }
            }
        }
    }
    
    /**
     * Get all request headers
     * 
     * @return array
     */
    private function getRequestHeaders(): array {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) === 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }
        
        // Convert headers to lowercase for easier access
        $result = [];
        foreach ($headers as $key => $value) {
            $result[strtolower($key)] = $value;
        }
        
        return $result;
    }
}
