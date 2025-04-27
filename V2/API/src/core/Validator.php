<?php
/**
 * Validator Class for Courts Management System API
 * 
 * This class handles input validation for API requests.
 * 
 * @version 1.0
 */

namespace Courts\Core;

class Validator {
    /**
     * @var array Validation errors
     */
    private array $errors = [];
    
    /**
     * @var array Data to validate
     */
    private array $data = [];
    
    /**
     * @var array Validation rules
     */
    private array $rules = [];
    
    /**
     * Constructor
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     */
    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    /**
     * Validate data against rules
     * 
     * @return bool True if validation passes, false otherwise
     */
    public function validate(): bool {
        $this->errors = [];
        
        foreach ($this->rules as $field => $rules) {
            // Skip validation if field is not required and not present
            if (!$this->isRequired($rules) && !isset($this->data[$field])) {
                continue;
            }
            
            // Get field value
            $value = $this->data[$field] ?? null;
            
            // Apply validation rules
            $this->applyRules($field, $value, $rules);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Check if a field is required
     * 
     * @param string $rules Validation rules
     * @return bool
     */
    private function isRequired(string $rules): bool {
        return strpos($rules, 'required') !== false;
    }
    
    /**
     * Apply validation rules to a field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rules Validation rules
     * @return void
     */
    private function applyRules(string $field, $value, string $rules): void {
        $rulesList = explode('|', $rules);
        
        foreach ($rulesList as $rule) {
            // Check if rule has parameters
            if (strpos($rule, ':') !== false) {
                [$ruleName, $ruleParams] = explode(':', $rule, 2);
                $params = explode(',', $ruleParams);
            } else {
                $ruleName = $rule;
                $params = [];
            }
            
            // Apply rule
            $methodName = 'validate' . ucfirst($ruleName);
            
            if (method_exists($this, $methodName)) {
                $this->$methodName($field, $value, $params);
            }
        }
    }
    
    /**
     * Validate required field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateRequired(string $field, $value, array $params): void {
        if ($value === null || $value === '') {
            $this->addError($field, 'The ' . $field . ' field is required.');
        }
    }
    
    /**
     * Validate email format
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateEmail(string $field, $value, array $params): void {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'The ' . $field . ' must be a valid email address.');
        }
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateMin(string $field, $value, array $params): void {
        $min = (int) $params[0];
        
        if ($value !== null && $value !== '' && strlen($value) < $min) {
            $this->addError($field, 'The ' . $field . ' must be at least ' . $min . ' characters.');
        }
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateMax(string $field, $value, array $params): void {
        $max = (int) $params[0];
        
        if ($value !== null && $value !== '' && strlen($value) > $max) {
            $this->addError($field, 'The ' . $field . ' may not be greater than ' . $max . ' characters.');
        }
    }
    
    /**
     * Validate numeric value
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateNumeric(string $field, $value, array $params): void {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, 'The ' . $field . ' must be a number.');
        }
    }
    
    /**
     * Validate integer value
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateInteger(string $field, $value, array $params): void {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'The ' . $field . ' must be an integer.');
        }
    }
    
    /**
     * Validate date format
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateDate(string $field, $value, array $params): void {
        $format = $params[0] ?? 'Y-m-d';
        
        if ($value !== null && $value !== '') {
            $date = \DateTime::createFromFormat($format, $value);
            
            if (!$date || $date->format($format) !== $value) {
                $this->addError($field, 'The ' . $field . ' is not a valid date.');
            }
        }
    }
    
    /**
     * Validate value in list
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateIn(string $field, $value, array $params): void {
        if ($value !== null && $value !== '' && !in_array($value, $params)) {
            $this->addError($field, 'The selected ' . $field . ' is invalid.');
        }
    }
    
    /**
     * Validate confirmed field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $params Rule parameters
     * @return void
     */
    private function validateConfirmed(string $field, $value, array $params): void {
        $confirmation = $this->data[$field . '_confirmation'] ?? null;
        
        if ($value !== null && $value !== '' && $value !== $confirmation) {
            $this->addError($field, 'The ' . $field . ' confirmation does not match.');
        }
    }
    
    /**
     * Add a validation error
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return void
     */
    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
}
