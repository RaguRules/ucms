<?php
/**
 * Staff Model Class for Courts Management System API
 * 
 * This class represents a staff member in the court system.
 * 
 * @version 1.0
 */

namespace Courts\Models;

use Courts\Core\Database;

class Staff {
    /**
     * @var int Staff ID
     */
    public int $id;
    
    /**
     * @var string First name
     */
    public string $first_name;
    
    /**
     * @var string Last name
     */
    public string $last_name;
    
    /**
     * @var string Email
     */
    public string $email;
    
    /**
     * @var string Phone
     */
    public string $phone;
    
    /**
     * @var string NIC (National ID Card)
     */
    public string $nic;
    
    /**
     * @var string Address
     */
    public string $address;
    
    /**
     * @var string Department
     */
    public string $department;
    
    /**
     * @var string Position
     */
    public string $position;
    
    /**
     * @var string Join date
     */
    public string $join_date;
    
    /**
     * @var string|null Photo path
     */
    public ?string $photo;
    
    /**
     * @var string Status
     */
    public string $status;
    
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
     * Get all staff members with optional filtering
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
            $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR nic LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['department'])) {
            $where[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['position'])) {
            $where[] = "position = ?";
            $params[] = $filters['position'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        // Build WHERE clause
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM staff {$whereClause}";
        $countResult = $this->db->getRecord($countSql, $params);
        $total = $countResult ? (int) $countResult['total'] : 0;
        
        // Get staff members
        $sql = "SELECT * FROM staff {$whereClause} ORDER BY last_name, first_name LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $staff = $this->db->getRecords($sql, $params);
        
        // Calculate pagination info
        $totalPages = ceil($total / $limit);
        
        return [
            'data' => $staff,
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
     * Get a staff member by ID
     * 
     * @param int $id Staff ID
     * @return array|false
     */
    public function getById(int $id) {
        $sql = "SELECT * FROM staff WHERE id = ? LIMIT 1";
        return $this->db->getRecord($sql, [$id]);
    }
    
    /**
     * Create a new staff member
     * 
     * @param array $data Staff data
     * @return int|false
     */
    public function create(array $data) {
        // Add timestamps and default status
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'active';
        
        return $this->db->insert('staff', $data);
    }
    
    /**
     * Update a staff member
     * 
     * @param int $id Staff ID
     * @param array $data Staff data
     * @return int|false
     */
    public function update(int $id, array $data) {
        // Add updated timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('staff', $data, 'id = ?', [$id]);
    }
    
    /**
     * Delete a staff member
     * 
     * @param int $id Staff ID
     * @return int|false
     */
    public function delete(int $id) {
        return $this->db->delete('staff', 'id = ?', [$id]);
    }
    
    /**
     * Get staff assignments
     * 
     * @param int $id Staff ID
     * @return array
     */
    public function getAssignments(int $id): array {
        $sql = "SELECT ca.*, c.case_number, c.title, c.status 
                FROM case_assignments ca 
                JOIN cases c ON ca.case_id = c.id 
                WHERE ca.staff_id = ? 
                ORDER BY ca.start_date DESC";
                
        return $this->db->getRecords($sql, [$id]);
    }
    
    /**
     * Assign staff to a case
     * 
     * @param int $staffId Staff ID
     * @param int $caseId Case ID
     * @param string $role Role in the case
     * @param string $startDate Start date
     * @param string|null $endDate End date
     * @return int|false
     */
    public function assignToCase(int $staffId, int $caseId, string $role, string $startDate, ?string $endDate = null) {
        $data = [
            'staff_id' => $staffId,
            'case_id' => $caseId,
            'role' => $role,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('case_assignments', $data);
    }
    
    /**
     * Remove staff from a case
     * 
     * @param int $staffId Staff ID
     * @param int $caseId Case ID
     * @return int|false
     */
    public function removeFromCase(int $staffId, int $caseId) {
        return $this->db->delete('case_assignments', 'staff_id = ? AND case_id = ?', [$staffId, $caseId]);
    }
    
    /**
     * Get departments
     * 
     * @return array
     */
    public function getDepartments(): array {
        return [
            'administration',
            'civil',
            'criminal',
            'family',
            'probate',
            'juvenile',
            'traffic',
            'records',
            'it',
            'security'
        ];
    }
    
    /**
     * Get positions
     * 
     * @return array
     */
    public function getPositions(): array {
        return [
            'judge',
            'clerk',
            'bailiff',
            'court_reporter',
            'administrator',
            'attorney',
            'paralegal',
            'it_specialist',
            'security_officer',
            'records_manager'
        ];
    }
    
    /**
     * Get statuses
     * 
     * @return array
     */
    public function getStatuses(): array {
        return [
            'active',
            'on_leave',
            'suspended',
            'retired',
            'terminated'
        ];
    }
}
