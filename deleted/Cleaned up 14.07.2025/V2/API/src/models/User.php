<?php
/**
 * User Model Class for Courts Management System API
 * 
 * This class represents a user in the court system.
 * 
 * @version 1.0
 */

namespace Courts\Models;

use Courts\Core\Database;

class User {
    /**
     * @var int User ID
     */
    public int $id;
    
    /**
     * @var string Username
     */
    public string $username;
    
    /**
     * @var string Email
     */
    public string $email;
    
    /**
     * @var string Full name
     */
    public string $full_name;
    
    /**
     * @var string Role
     */
    public string $role;
    
    /**
     * @var string Status
     */
    public string $status;
    
    /**
     * @var string|null Last login timestamp
     */
    public ?string $last_login;
    
    /**
     * @var string Created at timestamp
     */
    public string $created_at;
    
    /**
     * @var string|null Updated at timestamp
     */
    public ?string $updated_at;
    
    /**
     * @var Database Database instance
     */
    private Database $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all users with optional filtering
     * 
     * @param array $filters Filters to apply
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getAll(array $filters = [], int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $where[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['role'])) {
            $where[] = "role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        // Build WHERE clause
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
        $countResult = $this->db->getRecord($countSql, $params);
        $total = $countResult ? (int) $countResult['total'] : 0;
        
        // Get users
        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at, updated_at 
                FROM users {$whereClause} 
                ORDER BY username 
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $users = $this->db->getRecords($sql, $params);
        
        // Calculate pagination info
        $totalPages = ceil($total / $limit);
        
        return [
            'data' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ]
        ];
    }
    
    /**
     * Get a user by ID
     * 
     * @param int $id User ID
     * @return array|false
     */
    public function getById(int $id) {
        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at, updated_at 
                FROM users 
                WHERE id = ? 
                LIMIT 1";
        return $this->db->getRecord($sql, [$id]);
    }
    
    /**
     * Get a user by username
     * 
     * @param string $username Username
     * @return array|false
     */
    public function getByUsername(string $username) {
        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at, updated_at 
                FROM users 
                WHERE username = ? 
                LIMIT 1";
        return $this->db->getRecord($sql, [$username]);
    }
    
    /**
     * Get a user by email
     * 
     * @param string $email Email
     * @return array|false
     */
    public function getByEmail(string $email) {
        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at, updated_at 
                FROM users 
                WHERE email = ? 
                LIMIT 1";
        return $this->db->getRecord($sql, [$email]);
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int|false
     */
    public function create(array $data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Add timestamps and default status
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'active';
        
        return $this->db->insert('users', $data);
    }
    
    /**
     * Update a user
     * 
     * @param int $id User ID
     * @param array $data User data
     * @return int|false
     */
    public function update(int $id, array $data) {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Add updated timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('users', $data, 'id = ?', [$id]);
    }
    
    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return int|false
     */
    public function delete(int $id) {
        return $this->db->delete('users', 'id = ?', [$id]);
    }
    
    /**
     * Update user status
     * 
     * @param int $id User ID
     * @param string $status New status
     * @return int|false
     */
    public function updateStatus(int $id, string $status) {
        return $this->db->update('users', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
    }
    
    /**
     * Update last login timestamp
     * 
     * @param int $id User ID
     * @return int|false
     */
    public function updateLastLogin(int $id) {
        return $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
    }
    
    /**
     * Check if username exists
     * 
     * @param string $username Username
     * @param int|null $excludeId User ID to exclude
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->getRecord($sql, $params);
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email
     * @param int|null $excludeId User ID to exclude
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->getRecord($sql, $params);
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get user roles
     * 
     * @return array
     */
    public function getRoles(): array {
        return [
            'user',
            'staff',
            'manager',
            'admin'
        ];
    }
    
    /**
     * Get user statuses
     * 
     * @return array
     */
    public function getStatuses(): array {
        return [
            'active',
            'inactive',
            'suspended',
            'locked'
        ];
    }
}
