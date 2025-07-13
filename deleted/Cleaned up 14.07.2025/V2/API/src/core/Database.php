<?php
/**
 * Core Database Class for Courts Management System API
 * 
 * This class provides a singleton PDO database connection and utility methods
 * for database operations.
 * 
 * @version 1.0
 */

namespace Courts\Core;

use PDO;
use PDOException;

class Database {
    /**
     * @var Database|null Singleton instance
     */
    private static ?Database $instance = null;
    
    /**
     * @var PDO|null Database connection
     */
    private ?PDO $conn = null;
    
    /**
     * @var array Database configuration
     */
    private array $config;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Load database configuration
        $this->config = require_once __DIR__ . '/../config/database.php';
        
        $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $this->conn = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
        } catch (PDOException $e) {
            // Log error instead of exposing details
            error_log("Database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed. Please contact administrator.");
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Get database connection
     * 
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->conn;
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return \PDOStatement|false
     */
    public function query(string $sql, array $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a single record
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|false Single record or false if not found
     */
    public function getRecord(string $sql, array $params = []) {
        $stmt = $this->query($sql, $params);
        
        if ($stmt) {
            return $stmt->fetch();
        }
        
        return false;
    }
    
    /**
     * Get multiple records
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array Array of records
     */
    public function getRecords(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        
        if ($stmt) {
            return $stmt->fetchAll();
        }
        
        return [];
    }
    
    /**
     * Insert a record and return the last insert ID
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int|false Last insert ID or false on failure
     */
    public function insert(string $table, array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a record
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where Where clause
     * @param array $params Parameters for where clause
     * @return int|false Number of affected rows or false on failure
     */
    public function update(string $table, array $data, string $where, array $params = []) {
        $set = [];
        
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        
        $set = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_merge(array_values($data), $params));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Update failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a record
     * 
     * @param string $table Table name
     * @param string $where Where clause
     * @param array $params Parameters for where clause
     * @return int|false Number of affected rows or false on failure
     */
    public function delete(string $table, string $where, array $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Delete failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit(): bool {
        return $this->conn->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback(): bool {
        return $this->conn->rollBack();
    }
}
