<?php
/**
 * Security Utilities Class
 * 
 * This class provides security-related utilities for the Courts Management System,
 * including CSRF protection, input validation, and secure file uploads.
 * 
 * @version 1.0
 * @author Courts Management System
 */
class Security {
    /**
     * Generate a CSRF token and store it in the session
     * 
     * @return string CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool True if token is valid, false otherwise
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
    
    /**
     * Sanitize and validate input data
     * 
     * @param string $data Input data
     * @param string $type Type of validation (string, email, int, etc.)
     * @return array Status and value/message
     */
    public static function validateInput($data, $type = 'string') {
        $data = trim($data);
        
        // Check if required field is empty
        if (empty($data)) {
            return ["status" => false, "message" => "This field is required"];
        }
        
        // Validate based on type
        switch ($type) {
            case 'email':
                if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                    return ["status" => false, "message" => "Invalid email format"];
                }
                break;
                
            case 'mobile':
                if (!preg_match('/^[0-9]{10}$/', $data)) {
                    return ["status" => false, "message" => "Mobile number must be 10 digits"];
                }
                break;
                
            case 'nic':
                // Validate both old (9 digits + V/X) and new (12 digits) NIC formats
                if (!preg_match('/^([0-9]{9}[vVxX]|[0-9]{12})$/', $data)) {
                    return ["status" => false, "message" => "Invalid NIC format"];
                }
                break;
                
            case 'name':
                if (!preg_match('/^[a-zA-Z\s]+$/', $data)) {
                    return ["status" => false, "message" => "Only letters and spaces allowed"];
                }
                break;
                
            case 'int':
                if (!filter_var($data, FILTER_VALIDATE_INT)) {
                    return ["status" => false, "message" => "Only numbers allowed"];
                }
                break;
        }
        
        // Sanitize output to prevent XSS
        $sanitized = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return ["status" => true, "value" => $sanitized];
    }
    
    /**
     * Secure file upload for images
     * 
     * @param string $fileInputName Name of the file input field
     * @return array Upload result with success status, filename or error
     */
    public static function secureImageUpload($fileInputName) {
        // Check if file was uploaded
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            return ["success" => false, "error" => "File upload failed or no file selected"];
        }
        
        $file = $_FILES[$fileInputName];
        
        // Define allowed file types and max size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            return ["success" => false, "error" => "File size exceeds the 2MB limit"];
        }
        
        // Validate file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->file($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            return ["success" => false, "error" => "Invalid file type. Only JPEG, PNG, and GIF are allowed"];
        }
        
        // Generate a secure filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        
        // Set upload directory
        $uploadDir = __DIR__ . '/uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $newFilename;
        
        // Move the file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ["success" => false, "error" => "Failed to move uploaded file"];
        }
        
        return ["success" => true, "filename" => $newFilename];
    }
    
    /**
     * Hash password securely
     * 
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Password to verify
     * @param string $hash Stored hash
     * @return bool True if password is correct, false otherwise
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
