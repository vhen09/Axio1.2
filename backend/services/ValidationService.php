<?php

class ValidationService {
    /**
     * Validate mathematical input
     * Basic validation for mathematical expressions
     */
    public function validate($input) {
        // Check if input is not empty
        if (empty(trim($input))) {
            return false;
        }
        
        // Check minimum length
        if (strlen(trim($input)) < 3) {
            return false;
        }
        
        // Input is valid
        return true;
    }
    
    /**
     * Sanitize input to prevent XSS
     */
    public function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}