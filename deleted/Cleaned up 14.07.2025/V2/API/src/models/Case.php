<?php
/**
 * Case Model Class for Courts Management System API
 * 
 * This class represents a case in the court system.
 * 
 * @version 1.0
 */

namespace Courts\Models;

use Courts\Core\Database;

class Case {
    /**
     * @var int Case ID
     */
    public int $id;
    
    /**
     * @var string Case number
     */
    public string $case_number;
    
    /**
     * @var string Case title
     */
    public string $title;
    
    /**
     * @var string Case description
     */
    public string $description;
    
    /**
     * @var string Case type
     */
    public string $type;
    
    /**
     * @var string Case status
     */
    public string $status;
    
    /**
     * @var string Filing date
     */
    public string $filing_date;
    
    /**
     * @var string Plaintiff
     */
    public string $plaintiff;
    
    /**
     * @var string Defendant
     */
    public string $defendant;
    
    /**
     * @var int|null Judge ID
     */
    public ?int $judge_id;
    
    /**
     * @var string|null Priority
     */
    public ?string $priority;
    
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
     * Get all cases with optional filtering
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
            $where[] = "(case_number LIKE ? OR title LIKE ? OR plaintiff LIKE ? OR defendant LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $where[] = "type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "filing_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "filing_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['judge_id'])) {
            $where[] = "judge_id = ?";
            $params[] = $filters['judge_id'];
        }
        
        // Build WHERE clause
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM cases {$whereClause}";
        $countResult = $this->db->getRecord($countSql, $params);
        $total = $countResult ? (int) $countResult['total'] : 0;
        
        // Get cases
        $sql = "SELECT * FROM cases {$whereClause} ORDER BY filing_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $cases = $this->db->getRecords($sql, $params);
        
        // Calculate pagination info
        $totalPages = ceil($total / $limit);
        
        return [
            'data' => $cases,
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
     * Get a case by ID
     * 
     * @param int $id Case ID
     * @return array|false
     */
    public function getById(int $id) {
        $sql = "SELECT * FROM cases WHERE id = ? LIMIT 1";
        return $this->db->getRecord($sql, [$id]);
    }
    
    /**
     * Create a new case
     * 
     * @param array $data Case data
     * @return int|false
     */
    public function create(array $data) {
        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('cases', $data);
    }
    
    /**
     * Update a case
     * 
     * @param int $id Case ID
     * @param array $data Case data
     * @return int|false
     */
    public function update(int $id, array $data) {
        // Add updated timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('cases', $data, 'id = ?', [$id]);
    }
    
    /**
     * Delete a case
     * 
     * @param int $id Case ID
     * @return int|false
     */
    public function delete(int $id) {
        return $this->db->delete('cases', 'id = ?', [$id]);
    }
    
    /**
     * Update case status
     * 
     * @param int $id Case ID
     * @param string $status New status
     * @param string|null $notes Status change notes
     * @return bool
     */
    public function updateStatus(int $id, string $status, ?string $notes = null): bool {
        try {
            $this->db->beginTransaction();
            
            // Update case status
            $updated = $this->db->update('cases', [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            if (!$updated) {
                $this->db->rollback();
                return false;
            }
            
            // Add to case history
            $historyData = [
                'case_id' => $id,
                'status' => $status,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $historyId = $this->db->insert('case_history', $historyData);
            
            if (!$historyId) {
                $this->db->rollback();
                return false;
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Get case history
     * 
     * @param int $id Case ID
     * @return array
     */
    public function getHistory(int $id): array {
        $sql = "SELECT * FROM case_history WHERE case_id = ? ORDER BY created_at DESC";
        return $this->db->getRecords($sql, [$id]);
    }
    
    /**
     * Get case types
     * 
     * @return array
     */
    public function getTypes(): array {
        return [
            'civil',
            'criminal',
            'family',
            'probate',
            'juvenile',
            'traffic',
            'small_claims',
            'administrative'
        ];
    }
    
    /**
     * Get case statuses
     * 
     * @return array
     */
    public function getStatuses(): array {
        return [
            'pending',
            'active',
            'scheduled',
            'continued',
            'dismissed',
            'closed',
            'appealed',
            'archived'
        ];
    }
}
