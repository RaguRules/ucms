<?php
/**
 * StaffController Class for Courts Management System API
 * 
 * This class handles staff management API endpoints.
 * 
 * @version 1.0
 */

namespace Courts\Api\V1\Staff;

use Courts\Core\Request;
use Courts\Core\Response;
use Courts\Core\Validator;
use Courts\Models\Staff;

class StaffController {
    /**
     * @var Staff Staff model instance
     */
    private $staffModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->staffModel = new Staff();
    }
    
    /**
     * Get all staff members with filtering and pagination
     * 
     * @param Request $request Request object
     * @return void
     */
    public function index(Request $request): void {
        // Get query parameters
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);
        
        // Build filters
        $filters = [
            'search' => $request->get('search'),
            'department' => $request->get('department'),
            'position' => $request->get('position'),
            'status' => $request->get('status')
        ];
        
        // Remove empty filters
        $filters = array_filter($filters);
        
        // Get staff members
        $result = $this->staffModel->getAll($filters, $page, $limit);
        
        Response::success($result, 'Staff members retrieved successfully');
    }
    
    /**
     * Get a staff member by ID
     * 
     * @param Request $request Request object
     * @return void
     */
    public function show(Request $request): void {
        $id = (int) $request->param('id');
        
        // Get staff member
        $staff = $this->staffModel->getById($id);
        
        if (!$staff) {
            Response::notFound('Staff member not found');
            return;
        }
        
        Response::success($staff, 'Staff member retrieved successfully');
    }
    
    /**
     * Create a new staff member
     * 
     * @param Request $request Request object
     * @return void
     */
    public function store(Request $request): void {
        // Validate input
        $validator = new Validator($request->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|email|max:100',
            'phone' => 'required|max:20',
            'nic' => 'required|max:20',
            'address' => 'required|max:255',
            'department' => 'required|max:50',
            'position' => 'required|max:50',
            'join_date' => 'required|date:Y-m-d',
            'status' => 'in:active,on_leave,suspended,retired,terminated'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Create staff member
        $staffData = [
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'nic' => $request->get('nic'),
            'address' => $request->get('address'),
            'department' => $request->get('department'),
            'position' => $request->get('position'),
            'join_date' => $request->get('join_date'),
            'status' => $request->get('status', 'active')
        ];
        
        // Handle photo upload if present
        if (isset($request->data['photo']) && is_array($request->data['photo']) && $request->data['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $request->data['photo'];
            $photoName = time() . '_' . basename($photo['name']);
            $uploadDir = __DIR__ . '/../../../../public/uploads/staff/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $photoName;
            
            if (move_uploaded_file($photo['tmp_name'], $uploadPath)) {
                $staffData['photo'] = '/uploads/staff/' . $photoName;
            }
        }
        
        $staffId = $this->staffModel->create($staffData);
        
        if (!$staffId) {
            Response::serverError('Failed to create staff member');
            return;
        }
        
        // Get created staff member
        $staff = $this->staffModel->getById($staffId);
        
        Response::success($staff, 'Staff member created successfully', 201);
    }
    
    /**
     * Update a staff member
     * 
     * @param Request $request Request object
     * @return void
     */
    public function update(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if staff member exists
        $staff = $this->staffModel->getById($id);
        
        if (!$staff) {
            Response::notFound('Staff member not found');
            return;
        }
        
        // Validate input
        $validator = new Validator($request->all(), [
            'first_name' => 'max:50',
            'last_name' => 'max:50',
            'email' => 'email|max:100',
            'phone' => 'max:20',
            'nic' => 'max:20',
            'address' => 'max:255',
            'department' => 'max:50',
            'position' => 'max:50',
            'join_date' => 'date:Y-m-d',
            'status' => 'in:active,on_leave,suspended,retired,terminated'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Update staff member
        $staffData = [];
        
        // Only include fields that are present in the request
        foreach ([
            'first_name', 'last_name', 'email', 'phone', 'nic',
            'address', 'department', 'position', 'join_date', 'status'
        ] as $field) {
            if ($request->has($field)) {
                $staffData[$field] = $request->get($field);
            }
        }
        
        // Handle photo upload if present
        if (isset($request->data['photo']) && is_array($request->data['photo']) && $request->data['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $request->data['photo'];
            $photoName = time() . '_' . basename($photo['name']);
            $uploadDir = __DIR__ . '/../../../../public/uploads/staff/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $photoName;
            
            if (move_uploaded_file($photo['tmp_name'], $uploadPath)) {
                $staffData['photo'] = '/uploads/staff/' . $photoName;
                
                // Remove old photo if exists
                if (!empty($staff['photo']) && file_exists(__DIR__ . '/../../../../public' . $staff['photo'])) {
                    unlink(__DIR__ . '/../../../../public' . $staff['photo']);
                }
            }
        }
        
        $updated = $this->staffModel->update($id, $staffData);
        
        if (!$updated) {
            Response::serverError('Failed to update staff member');
            return;
        }
        
        // Get updated staff member
        $staff = $this->staffModel->getById($id);
        
        Response::success($staff, 'Staff member updated successfully');
    }
    
    /**
     * Delete a staff member
     * 
     * @param Request $request Request object
     * @return void
     */
    public function destroy(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if staff member exists
        $staff = $this->staffModel->getById($id);
        
        if (!$staff) {
            Response::notFound('Staff member not found');
            return;
        }
        
        // Delete staff member
        $deleted = $this->staffModel->delete($id);
        
        if (!$deleted) {
            Response::serverError('Failed to delete staff member');
            return;
        }
        
        // Remove photo if exists
        if (!empty($staff['photo']) && file_exists(__DIR__ . '/../../../../public' . $staff['photo'])) {
            unlink(__DIR__ . '/../../../../public' . $staff['photo']);
        }
        
        Response::success(null, 'Staff member deleted successfully');
    }
    
    /**
     * Get staff assignments
     * 
     * @param Request $request Request object
     * @return void
     */
    public function assignments(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if staff member exists
        $staff = $this->staffModel->getById($id);
        
        if (!$staff) {
            Response::notFound('Staff member not found');
            return;
        }
        
        // Get assignments
        $assignments = $this->staffModel->getAssignments($id);
        
        Response::success($assignments, 'Staff assignments retrieved successfully');
    }
    
    /**
     * Assign staff to a case
     * 
     * @param Request $request Request object
     * @return void
     */
    public function assignToCase(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if staff member exists
        $staff = $this->staffModel->getById($id);
        
        if (!$staff) {
            Response::notFound('Staff member not found');
            return;
        }
        
        // Validate input
        $validator = new Validator($request->all(), [
            'case_id' => 'required|integer',
            'role' => 'required|max:50',
            'start_date' => 'required|date:Y-m-d',
            'end_date' => 'date:Y-m-d'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Assign to case
        $assigned = $this->staffModel->assignToCase(
            $id,
            (int) $request->get('case_id'),
            $request->get('role'),
            $request->get('start_date'),
            $request->get('end_date')
        );
        
        if (!$assigned) {
            Response::serverError('Failed to assign staff to case');
            return;
        }
        
        // Get updated assignments
        $assignments = $this->staffModel->getAssignments($id);
        
        Response::success($assignments, 'Staff assigned to case successfully');
    }
    
    /**
     * Remove staff from a case
     * 
     * @param Request $request Request object
     * @return void
     */
    public function removeFromCase(Request $request): void {
        $id = (int) $request->param('id');
        $caseId = (int) $request->param('case_id');
        
        // Check if staff member exists
        $staff = $this->staffModel->getById($id);
        
        if (!$staff) {
            Response::notFound('Staff member not found');
            return;
        }
        
        // Remove from case
        $removed = $this->staffModel->removeFromCase($id, $caseId);
        
        if (!$removed) {
            Response::serverError('Failed to remove staff from case');
            return;
        }
        
        Response::success(null, 'Staff removed from case successfully');
    }
    
    /**
     * Get departments
     * 
     * @param Request $request Request object
     * @return void
     */
    public function departments(Request $request): void {
        $departments = $this->staffModel->getDepartments();
        
        Response::success($departments, 'Departments retrieved successfully');
    }
    
    /**
     * Get positions
     * 
     * @param Request $request Request object
     * @return void
     */
    public function positions(Request $request): void {
        $positions = $this->staffModel->getPositions();
        
        Response::success($positions, 'Positions retrieved successfully');
    }
    
    /**
     * Get statuses
     * 
     * @param Request $request Request object
     * @return void
     */
    public function statuses(Request $request): void {
        $statuses = $this->staffModel->getStatuses();
        
        Response::success($statuses, 'Statuses retrieved successfully');
    }
}
