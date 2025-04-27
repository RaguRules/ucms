<?php
/**
 * Response Class for Courts Management System API
 * 
 * This class handles API response formatting and sending.
 * 
 * @version 1.0
 */

namespace Courts\Core;

class Response {
    /**
     * Send a JSON response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     * @param array $headers Additional headers
     * @return void
     */
    public static function json($data, int $statusCode = 200, array $headers = []): void {
        // Set status code
        http_response_code($statusCode);
        
        // Set content type
        header('Content-Type: application/json; charset=UTF-8');
        
        // Set additional headers
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
        
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        // Output JSON response
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send a success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     * @return void
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): void {
        self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * Send an error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Validation errors
     * @return void
     */
    public static function error(string $message = 'Error', int $statusCode = 400, array $errors = []): void {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        self::json($response, $statusCode);
    }
    
    /**
     * Send a not found response
     * 
     * @param string $message Not found message
     * @return void
     */
    public static function notFound(string $message = 'Resource not found'): void {
        self::error($message, 404);
    }
    
    /**
     * Send an unauthorized response
     * 
     * @param string $message Unauthorized message
     * @return void
     */
    public static function unauthorized(string $message = 'Unauthorized access'): void {
        self::error($message, 401);
    }
    
    /**
     * Send a forbidden response
     * 
     * @param string $message Forbidden message
     * @return void
     */
    public static function forbidden(string $message = 'Access forbidden'): void {
        self::error($message, 403);
    }
    
    /**
     * Send a validation error response
     * 
     * @param array $errors Validation errors
     * @param string $message Validation error message
     * @return void
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void {
        self::error($message, 422, $errors);
    }
    
    /**
     * Send a server error response
     * 
     * @param string $message Server error message
     * @return void
     */
    public static function serverError(string $message = 'Internal server error'): void {
        self::error($message, 500);
    }
}
