<?php
/**
 * Auth Class for Courts Management System API
 * 
 * This class handles authentication and authorization for the API.
 * 
 * @version 1.0
 */

namespace Courts\Core;

use Courts\Core\Database;
use Courts\Core\Response;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    /**
     * @var array Auth configuration
     */
    private array $config;
    
    /**
     * @var Database Database instance
     */
    private Database $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->config = require_once __DIR__ . '/../config/auth.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate a user with username/email and password
     * 
     * @param string $username Username or email
     * @param string $password Password
     * @return array|false User data or false if authentication fails
     */
    public function authenticate(string $username, string $password) {
        // Check if input is email or username
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'username';
        
        // Get user from database
        $sql = "SELECT * FROM users WHERE {$field} = ? LIMIT 1";
        $user = $this->db->getRecord($sql, [$username]);
        
        if (!$user) {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // Remove password from user data
        unset($user['password']);
        
        return $user;
    }
    
    /**
     * Generate a JWT token for a user
     * 
     * @param array $user User data
     * @return string JWT token
     */
    public function generateToken(array $user): string {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->config['token_expiration'];
        
        $payload = [
            'iss' => $this->config['token_issuer'],
            'aud' => $this->config['token_audience'],
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        
        return JWT::encode($payload, $this->config['secret_key'], 'HS256');
    }
    
    /**
     * Verify a JWT token
     * 
     * @param string $token JWT token
     * @return object|false Decoded token payload or false if verification fails
     */
    public function verifyToken(string $token) {
        try {
            $decoded = JWT::decode($token, new Key($this->config['secret_key'], 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get user from token
     * 
     * @param string $token JWT token
     * @return array|false User data or false if token is invalid
     */
    public function getUserFromToken(string $token) {
        $decoded = $this->verifyToken($token);
        
        if (!$decoded) {
            return false;
        }
        
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $user = $this->db->getRecord($sql, [$decoded->user_id]);
        
        if (!$user) {
            return false;
        }
        
        // Remove password from user data
        unset($user['password']);
        
        return $user;
    }
    
    /**
     * Register a new user
     * 
     * @param array $userData User data
     * @return array|false User data or false if registration fails
     */
    public function register(array $userData) {
        // Check if username or email already exists
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $existingUser = $this->db->getRecord($sql, [$userData['username'], $userData['email']]);
        
        if ($existingUser) {
            return false;
        }
        
        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Set default role if not provided
        if (!isset($userData['role'])) {
            $userData['role'] = 'user';
        }
        
        // Insert user into database
        $userId = $this->db->insert('users', $userData);
        
        if (!$userId) {
            return false;
        }
        
        // Get user data
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $user = $this->db->getRecord($sql, [$userId]);
        
        // Remove password from user data
        unset($user['password']);
        
        return $user;
    }
    
    /**
     * Check if user has a specific role
     * 
     * @param array $user User data
     * @param string|array $roles Role or roles to check
     * @return bool
     */
    public function hasRole(array $user, $roles): bool {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        return in_array($user['role'], $roles);
    }
}
