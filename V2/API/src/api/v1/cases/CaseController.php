<?php
/**
 * CaseController Class for Courts Management System API
 * 
 * This class handles case management API endpoints.
 * 
 * @version 1.0
 */

namespace Courts\Api\V1\Cases;

use Courts\Core\Request;
use Courts\Core\Response;
use Courts\Core\Validator;
use Courts\Models\Case;

class CaseController {
    /**
     * @var Case Case model instance
     */
    private $caseModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->caseModel = new Case();
    }
    
    /**
     * Get all cases with filtering and pagination
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
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'judge_id' => $request->get('judge_id')
        ];
        
        // Remove empty filters
        $filters = array_filter($filters);
        
        // Get cases
        $result = $this->caseModel->getAll($filters, $page, $limit);
        
        Response::success($result, 'Cases retrieved successfully');
    }
    
    /**
     * Get a case by ID
     * 
     * @param Request $request Request object
     * @return void
     */
    public function show(Request $request): void {
        $id = (int) $request->param('id');
        
        // Get case
        $case = $this->caseModel->getById($id);
        
        if (!$case) {
            Response::notFound('Case not found');
            return;
        }
        
        // Get case history
        $history = $this->caseModel->getHistory($id);
        
        // Return case with history
        Response::success([
            'case' => $case,
            'history' => $history
        ], 'Case retrieved successfully');
    }
    
    /**
     * Create a new case
     * 
     * @param Request $request Request object
     * @return void
     */
    public function store(Request $request): void {
        // Validate input
        $validator = new Validator($request->all(), [
            'case_number' => 'required|max:50',
            'title' => 'required|max:255',
            'description' => 'required',
            'type' => 'required|in:civil,criminal,family,probate,juvenile,traffic,small_claims,administrative',
            'status' => 'required|in:pending,active,scheduled,continued,dismissed,closed,appealed,archived',
            'filing_date' => 'required|date:Y-m-d',
            'plaintiff' => 'required|max:255',
            'defendant' => 'required|max:255',
            'judge_id' => 'integer',
            'priority' => 'in:low,medium,high'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Create case
        $caseData = [
            'case_number' => $request->get('case_number'),
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'filing_date' => $request->get('filing_date'),
            'plaintiff' => $request->get('plaintiff'),
            'defendant' => $request->get('defendant'),
            'judge_id' => $request->get('judge_id'),
            'priority' => $request->get('priority')
        ];
        
        $caseId = $this->caseModel->create($caseData);
        
        if (!$caseId) {
            Response::serverError('Failed to create case');
            return;
        }
        
        // Get created case
        $case = $this->caseModel->getById($caseId);
        
        Response::success($case, 'Case created successfully', 201);
    }
    
    /**
     * Update a case
     * 
     * @param Request $request Request object
     * @return void
     */
    public function update(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if case exists
        $case = $this->caseModel->getById($id);
        
        if (!$case) {
            Response::notFound('Case not found');
            return;
        }
        
        // Validate input
        $validator = new Validator($request->all(), [
            'case_number' => 'max:50',
            'title' => 'max:255',
            'type' => 'in:civil,criminal,family,probate,juvenile,traffic,small_claims,administrative',
            'status' => 'in:pending,active,scheduled,continued,dismissed,closed,appealed,archived',
            'filing_date' => 'date:Y-m-d',
            'plaintiff' => 'max:255',
            'defendant' => 'max:255',
            'judge_id' => 'integer',
            'priority' => 'in:low,medium,high'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Update case
        $caseData = [];
        
        // Only include fields that are present in the request
        foreach ([
            'case_number', 'title', 'description', 'type', 'status',
            'filing_date', 'plaintiff', 'defendant', 'judge_id', 'priority'
        ] as $field) {
            if ($request->has($field)) {
                $caseData[$field] = $request->get($field);
            }
        }
        
        $updated = $this->caseModel->update($id, $caseData);
        
        if (!$updated) {
            Response::serverError('Failed to update case');
            return;
        }
        
        // Get updated case
        $case = $this->caseModel->getById($id);
        
        Response::success($case, 'Case updated successfully');
    }
    
    /**
     * Delete a case
     * 
     * @param Request $request Request object
     * @return void
     */
    public function destroy(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if case exists
        $case = $this->caseModel->getById($id);
        
        if (!$case) {
            Response::notFound('Case not found');
            return;
        }
        
        // Delete case
        $deleted = $this->caseModel->delete($id);
        
        if (!$deleted) {
            Response::serverError('Failed to delete case');
            return;
        }
        
        Response::success(null, 'Case deleted successfully');
    }
    
    /**
     * Update case status
     * 
     * @param Request $request Request object
     * @return void
     */
    public function updateStatus(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if case exists
        $case = $this->caseModel->getById($id);
        
        if (!$case) {
            Response::notFound('Case not found');
            return;
        }
        
        // Validate input
        $validator = new Validator($request->all(), [
            'status' => 'required|in:pending,active,scheduled,continued,dismissed,closed,appealed,archived',
            'notes' => 'max:1000'
        ]);
        
        if (!$validator->validate()) {
            Response::validationError($validator->getErrors());
            return;
        }
        
        // Update status
        $updated = $this->caseModel->updateStatus(
            $id,
            $request->get('status'),
            $request->get('notes')
        );
        
        if (!$updated) {
            Response::serverError('Failed to update case status');
            return;
        }
        
        // Get updated case
        $case = $this->caseModel->getById($id);
        
        Response::success($case, 'Case status updated successfully');
    }
    
    /**
     * Get case history
     * 
     * @param Request $request Request object
     * @return void
     */
    public function history(Request $request): void {
        $id = (int) $request->param('id');
        
        // Check if case exists
        $case = $this->caseModel->getById($id);
        
        if (!$case) {
            Response::notFound('Case not found');
            return;
        }
        
        // Get case history
        $history = $this->caseModel->getHistory($id);
        
        Response::success($history, 'Case history retrieved successfully');
    }
    
    /**
     * Get case types
     * 
     * @param Request $request Request object
     * @return void
     */
    public function types(Request $request): void {
        $types = $this->caseModel->getTypes();
        
        Response::success($types, 'Case types retrieved successfully');
    }
    
    /**
     * Get case statuses
     * 
     * @param Request $request Request object
     * @return void
     */
    public function statuses(Request $request): void {
        $statuses = $this->caseModel->getStatuses();
        
        Response::success($statuses, 'Case statuses retrieved successfully');
    }
}
