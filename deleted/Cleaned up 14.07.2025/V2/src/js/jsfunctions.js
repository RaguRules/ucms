/**
 * JavaScript Functions for Courts Management System
 * 
 * This file contains common JavaScript functions used throughout the application.
 * 
 * @version 2.0
 * @author Courts Management System
 */

// Common JavaScript functions for the Courts Management System
const jsFunctions = {
    /**
     * Initialize date pickers
     */
    initDatePickers: function() {
        const datePickers = document.querySelectorAll('.date-picker');
        if (datePickers.length > 0) {
            datePickers.forEach(picker => {
                picker.type = 'date'; // HTML5 date picker as fallback
            });
        }
    },

    /**
     * Initialize form validation
     */
    initFormValidation: function() {
        const forms = document.querySelectorAll('form[data-validate="true"]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        this.showFieldError(field, 'This field is required');
                    } else {
                        this.clearFieldError(field);
                    }
                });
                
                // Email validation
                const emailFields = form.querySelectorAll('input[type="email"]');
                emailFields.forEach(field => {
                    if (field.value.trim() && !this.isValidEmail(field.value)) {
                        isValid = false;
                        this.showFieldError(field, 'Please enter a valid email address');
                    }
                });
                
                // Phone validation
                const phoneFields = form.querySelectorAll('input[data-validate="phone"]');
                phoneFields.forEach(field => {
                    if (field.value.trim() && !this.isValidPhone(field.value)) {
                        isValid = false;
                        this.showFieldError(field, 'Please enter a valid phone number');
                    }
                });
                
                // NIC validation
                const nicFields = form.querySelectorAll('input[data-validate="nic"]');
                nicFields.forEach(field => {
                    if (field.value.trim() && !this.isValidNIC(field.value)) {
                        isValid = false;
                        this.showFieldError(field, 'Please enter a valid NIC number');
                    }
                });
                
                if (!isValid) {
                    event.preventDefault();
                }
            }.bind(this));
        });
    },
    
    /**
     * Show field error message
     * 
     * @param {HTMLElement} field The form field
     * @param {string} message Error message
     */
    showFieldError: function(field, message) {
        // Clear any existing error
        this.clearFieldError(field);
        
        // Add error class to field
        field.classList.add('border-red-500');
        
        // Create error message element
        const errorElement = document.createElement('p');
        errorElement.className = 'text-red-500 text-xs mt-1';
        errorElement.textContent = message;
        
        // Insert error message after field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    },
    
    /**
     * Clear field error message
     * 
     * @param {HTMLElement} field The form field
     */
    clearFieldError: function(field) {
        // Remove error class
        field.classList.remove('border-red-500');
        
        // Remove error message if exists
        const nextSibling = field.nextSibling;
        if (nextSibling && nextSibling.tagName === 'P' && nextSibling.classList.contains('text-red-500')) {
            nextSibling.parentNode.removeChild(nextSibling);
        }
    },
    
    /**
     * Validate email format
     * 
     * @param {string} email Email to validate
     * @return {boolean} True if valid, false otherwise
     */
    isValidEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    /**
     * Validate phone number format
     * 
     * @param {string} phone Phone number to validate
     * @return {boolean} True if valid, false otherwise
     */
    isValidPhone: function(phone) {
        const re = /^[0-9]{10}$/;
        return re.test(phone);
    },
    
    /**
     * Validate NIC number format
     * 
     * @param {string} nic NIC number to validate
     * @return {boolean} True if valid, false otherwise
     */
    isValidNIC: function(nic) {
        // Validate both old (9 digits + V/X) and new (12 digits) NIC formats
        const re = /^([0-9]{9}[vVxX]|[0-9]{12})$/;
        return re.test(nic);
    },
    
    /**
     * Initialize password strength meter
     */
    initPasswordStrength: function() {
        const passwordFields = document.querySelectorAll('input[data-password-strength]');
        
        passwordFields.forEach(field => {
            const strengthMeter = document.createElement('div');
            strengthMeter.className = 'w-full h-2 mt-1 rounded-full bg-gray-200 overflow-hidden';
            
            const strengthBar = document.createElement('div');
            strengthBar.className = 'h-full bg-red-500 transition-all duration-300';
            strengthBar.style.width = '0%';
            
            const strengthText = document.createElement('p');
            strengthText.className = 'text-xs mt-1 text-gray-600';
            strengthText.textContent = 'Password strength: None';
            
            strengthMeter.appendChild(strengthBar);
            
            field.parentNode.insertBefore(strengthMeter, field.nextSibling);
            field.parentNode.insertBefore(strengthText, strengthMeter.nextSibling);
            
            field.addEventListener('input', function() {
                const strength = this.checkPasswordStrength(field.value);
                
                // Update strength bar
                strengthBar.style.width = strength.percent + '%';
                
                // Update bar color
                strengthBar.className = 'h-full transition-all duration-300';
                if (strength.score === 0) {
                    strengthBar.classList.add('bg-red-500');
                } else if (strength.score === 1) {
                    strengthBar.classList.add('bg-orange-500');
                } else if (strength.score === 2) {
                    strengthBar.classList.add('bg-yellow-500');
                } else if (strength.score === 3) {
                    strengthBar.classList.add('bg-blue-500');
                } else {
                    strengthBar.classList.add('bg-green-500');
                }
                
                // Update text
                strengthText.textContent = 'Password strength: ' + strength.label;
            }.bind(this));
        });
    },
    
    /**
     * Check password strength
     * 
     * @param {string} password Password to check
     * @return {object} Strength object with score, percent and label
     */
    checkPasswordStrength: function(password) {
        let score = 0;
        
        // Length check
        if (password.length > 0) score += 1;
        if (password.length >= 8) score += 1;
        
        // Complexity checks
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) score += 1;
        if (password.match(/\d/)) score += 1;
        if (password.match(/[^a-zA-Z\d]/)) score += 1;
        
        // Calculate percentage
        const percent = (score / 5) * 100;
        
        // Get label
        let label = 'None';
        if (score === 1) label = 'Very Weak';
        else if (score === 2) label = 'Weak';
        else if (score === 3) label = 'Medium';
        else if (score === 4) label = 'Strong';
        else if (score === 5) label = 'Very Strong';
        
        return {
            score: score,
            percent: percent,
            label: label
        };
    },
    
    /**
     * Initialize all JavaScript functionality
     */
    init: function() {
        this.initDatePickers();
        this.initFormValidation();
        this.initPasswordStrength();
        
        // Add more initializations as needed
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    jsFunctions.init();
});
