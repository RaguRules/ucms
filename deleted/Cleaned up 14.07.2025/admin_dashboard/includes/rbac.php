<?php
// Role-based access control system for the Unified Courts Management System

// Define role constants
define('ROLE_ADMIN', 'R01');
define('ROLE_JUDGE', 'R02');
define('ROLE_REGISTRAR', 'R03');
define('ROLE_INTERPRETER', 'R04');
define('ROLE_STAFF', 'R05');
define('ROLE_LAWYER', 'R06');
define('ROLE_POLICE', 'R07');

/**
 * Role-based access control class
 */
class RBAC {
    // Store role permissions
    private static $permissions = [
        // Administrator has access to everything
        ROLE_ADMIN => [
            'dashboard' => ['view'],
            'users' => ['view', 'add', 'edit', 'delete'],
            'staff' => ['view', 'add', 'edit', 'delete'],
            'courts' => ['view', 'add', 'edit', 'delete'],
            'roles' => ['view', 'add', 'edit', 'delete'],
            'settings' => ['view', 'edit'],
            'reports' => ['view', 'generate', 'export'],
            'approve' => ['view', 'approve', 'deny'],
            'cases' => ['view', 'add', 'edit', 'delete'],
            'appeals' => ['view', 'add', 'edit', 'delete'],
            'motions' => ['view', 'add', 'edit', 'delete'],
            'judgements' => ['view', 'add', 'edit', 'delete'],
            'warrants' => ['view', 'add', 'edit', 'delete'],
            'parties' => ['view', 'add', 'edit', 'delete'],
            'dailycaseactivities' => ['view', 'add', 'edit', 'delete'],
            'orders' => ['view', 'add', 'edit', 'delete'],
            'notifications' => ['view', 'add', 'edit', 'delete'],
            'fines' => ['view', 'add', 'edit', 'delete'],
            'notes' => ['view', 'add', 'edit', 'delete'],
            'lawyers' => ['view', 'add', 'edit', 'delete'],
            'police' => ['view', 'add', 'edit', 'delete'],
            'profile' => ['view', 'edit']
        ],
        
        // Judge permissions
        ROLE_JUDGE => [
            'dashboard' => ['view'],
            'cases' => ['view', 'edit'],
            'appeals' => ['view', 'edit'],
            'judgements' => ['view', 'add', 'edit'],
            'warrants' => ['view', 'add', 'edit'],
            'orders' => ['view', 'add', 'edit'],
            'notes' => ['view', 'add', 'edit', 'delete'],
            'profile' => ['view', 'edit']
        ],
        
        // Registrar permissions
        ROLE_REGISTRAR => [
            'dashboard' => ['view'],
            'cases' => ['view', 'add', 'edit'],
            'appeals' => ['view', 'add', 'edit'],
            'motions' => ['view', 'add', 'edit'],
            'parties' => ['view', 'add', 'edit'],
            'dailycaseactivities' => ['view', 'add', 'edit'],
            'notifications' => ['view', 'add', 'edit'],
            'fines' => ['view', 'add', 'edit'],
            'profile' => ['view', 'edit']
        ],
        
        // Interpreter permissions
        ROLE_INTERPRETER => [
            'dashboard' => ['view'],
            'cases' => ['view'],
            'dailycaseactivities' => ['view'],
            'notes' => ['view', 'add', 'edit'],
            'profile' => ['view', 'edit']
        ],
        
        // Common Staff permissions
        ROLE_STAFF => [
            'dashboard' => ['view'],
            'cases' => ['view'],
            'dailycaseactivities' => ['view'],
            'notifications' => ['view'],
            'profile' => ['view', 'edit']
        ],
        
        // Lawyer permissions
        ROLE_LAWYER => [
            'dashboard' => ['view'],
            'cases' => ['view'],
            'motions' => ['view', 'add'],
            'notes' => ['view', 'add'],
            'profile' => ['view', 'edit']
        ],
        
        // Police permissions
        ROLE_POLICE => [
            'dashboard' => ['view'],
            'cases' => ['view'],
            'warrants' => ['view', 'edit'],
            'profile' => ['view', 'edit']
        ]
    ];
    
    /**
     * Check if user has permission to access a resource
     * @param string $roleId User role ID
     * @param string $resource Resource to check access for
     * @param string $action Action to check permission for
     * @return bool True if user has permission, false otherwise
     */
    public static function hasPermission($roleId, $resource, $action = 'view') {
        // Admin has access to everything
        if ($roleId === ROLE_ADMIN) {
            return true;
        }
        
        // Check if role exists in permissions
        if (!isset(self::$permissions[$roleId])) {
            return false;
        }
        
        // Check if resource exists for role
        if (!isset(self::$permissions[$roleId][$resource])) {
            return false;
        }
        
        // Check if action is allowed for resource
        return in_array($action, self::$permissions[$roleId][$resource]);
    }
    
    /**
     * Get all resources a role has access to
     * @param string $roleId User role ID
     * @return array Resources the role has access to
     */
    public static function getAccessibleResources($roleId) {
        if (!isset(self::$permissions[$roleId])) {
            return [];
        }
        
        return array_keys(self::$permissions[$roleId]);
    }
    
    /**
     * Get all actions a role can perform on a resource
     * @param string $roleId User role ID
     * @param string $resource Resource to check
     * @return array Actions the role can perform on the resource
     */
    public static function getAllowedActions($roleId, $resource) {
        if (!isset(self::$permissions[$roleId]) || !isset(self::$permissions[$roleId][$resource])) {
            return [];
        }
        
        return self::$permissions[$roleId][$resource];
    }
    
    /**
     * Check if user has access to a page
     * @param string $roleId User role ID
     * @param string $page Page to check access for
     * @return bool True if user has access, false otherwise
     */
    public static function hasPageAccess($roleId, $page) {
        return self::hasPermission($roleId, $page);
    }
    
    /**
     * Get role name from role ID
     * @param string $roleId Role ID
     * @return string Role name
     */
    public static function getRoleName($roleId) {
        $roleNames = [
            ROLE_ADMIN => 'Administrator',
            ROLE_JUDGE => 'Hon. Judge',
            ROLE_REGISTRAR => 'The Registrar',
            ROLE_INTERPRETER => 'Interpreter',
            ROLE_STAFF => 'Common Staff',
            ROLE_LAWYER => 'Lawyer',
            ROLE_POLICE => 'Police'
        ];
        
        return $roleNames[$roleId] ?? 'Unknown';
    }
    
    /**
     * Get all roles
     * @return array All roles with their IDs and names
     */
    public static function getAllRoles() {
        return [
            ['id' => ROLE_ADMIN, 'name' => 'Administrator'],
            ['id' => ROLE_JUDGE, 'name' => 'Hon. Judge'],
            ['id' => ROLE_REGISTRAR, 'name' => 'The Registrar'],
            ['id' => ROLE_INTERPRETER, 'name' => 'Interpreter'],
            ['id' => ROLE_STAFF, 'name' => 'Common Staff'],
            ['id' => ROLE_LAWYER, 'name' => 'Lawyer'],
            ['id' => ROLE_POLICE, 'name' => 'Police']
        ];
    }
}

/**
 * Check if current user has permission for a resource and action
 * @param string $resource Resource to check
 * @param string $action Action to check
 * @return bool True if user has permission, false otherwise
 */
function checkPermission($resource, $action = 'view') {
    if (!isset($_SESSION['ROLE_ID'])) {
        return false;
    }
    
    return RBAC::hasPermission($_SESSION['ROLE_ID'], $resource, $action);
}

/**
 * Check if user has access to a page
 * @param string $roleId User role ID
 * @param string $page Page to check access for
 * @return bool True if user has access, false otherwise
 */
function hasPageAccess($roleId, $page) {
    return RBAC::hasPageAccess($roleId, $page);
}

/**
 * Get role name from role ID
 * @param string $roleId Role ID
 * @return string Role name
 */
function getRoleName($roleId) {
    return RBAC::getRoleName($roleId);
}
