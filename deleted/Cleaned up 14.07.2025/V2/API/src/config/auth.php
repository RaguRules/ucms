<?php
/**
 * Auth Configuration for Courts Management System API
 * 
 * This file contains the authentication configuration.
 * 
 * @version 1.0
 */

return [
    // JWT configuration
    'secret_key' => 'your_secret_key_here', // Change this to a secure random string in production
    'token_expiration' => 3600, // 1 hour in seconds
    'token_issuer' => 'courts_management_api',
    'token_audience' => 'courts_management_client',
    
    // Password configuration
    'password_min_length' => 8,
    
    // Login attempts
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutes in seconds
];
