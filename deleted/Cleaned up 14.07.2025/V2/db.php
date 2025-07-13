<?php
/**
 * Enhanced Database Connection Class
 * 
 * This class provides a secure PDO connection to the database with proper error handling,
 * prepared statements, and configuration for character sets.
 * 
 * @version 1.0
 * @author Courts Management System
 */
class Database {
    private static $instance = null;
    private $conn;
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'courtsmanagement';
    private $charset = 'utf8mb4';
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * Constructor - establishes database connection
     */
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        
        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass, $this->options);
        } catch (PDOException $e) {
            // Log error instead of exposing details
            error_log("Connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed. Please contact administrator.");
        }
    }

    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database Database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     * 
     * @return PDO PDO connection
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Execute a query with prepared statements
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return PDOStatement|false PDOStatement object or false on failure
     */
    public function query($sql, $params = []) {
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
     * Get a single row from the database
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array|false Single row as associative array or false on failure
     */
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return false;
    }

    /**
     * Get multiple rows from the database
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array|false Multiple rows as associative array or false on failure
     */
    public function getRows($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return false;
    }

    /**
     * Insert data into the database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column names and values
     * @return int|false Last insert ID or false on failure
     */
    public function insert($table, $data) {
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
     * Update data in the database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column names and values
     * @param string $where WHERE clause
     * @param array $whereParams Parameters for WHERE clause
     * @return int|false Number of affected rows or false on failure
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_merge(array_values($data), $whereParams));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->conn->rollBack();
    }
}

// Create a global database connection instance
$db = Database::getInstance();
$conn = $db->getConnection();

// For backward compatibility with existing code
// This should be removed in future versions once all code is updated to use the new Database class
function getCourtName($court_id) {
    switch ($court_id) {
        case 'C01':
            return "Magistrate's Court";
        case 'C02':
            return "District Court";
        case 'C03':
            return "High Court";
        default:
            return "Unknown";
    }
}

function getRoleName($role_id) {
    switch ($role_id) {
        case 'R01':
            return "Administrator";
        case 'R02':
            return "Hon. Judge";
        case 'R03':
            return "The Registrar";
        case 'R04':
            return "Interpreter";
        case 'R05':
            return "Common Staff";
        case 'R06':
            return "Lawyer";
        case 'R07':
            return "Police";
        default:
            return "Unknown";
    }
}
