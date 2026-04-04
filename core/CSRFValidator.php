<?php
/**
 * CSRF Token Validator
 * Validates all POST, PUT, DELETE, PATCH requests
 */
class CSRFValidator {
    
    /**
     * Validate CSRF token from POST request
     * @throws Exception if token is invalid
     */
    public static function validate() {
        // Only validate state-changing requests
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return true;
        }

        // Get token from POST/request
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token) {
            self::logSecurityEvent('CSRF_MISSING', 'No CSRF token provided');
            http_response_code(403);
            die('⛔ Session expired. Please refresh and try again.');
        }

        // Compare with session token
        $sessionToken = $_SESSION['_csrf_token'] ?? null;
        if (!$sessionToken || !hash_equals($token, $sessionToken)) {
            self::logSecurityEvent('CSRF_INVALID', 'CSRF token mismatch or invalid');
            http_response_code(403);
            die('⛔ Invalid security token. Request rejected.');
        }

        return true;
    }

    /**
     * Log security events for audit trail
     */
    private static function logSecurityEvent($eventType, $details) {
        $logMessage = sprintf(
            "[%s] Event: %s | IP: %s | User: %s | Details: %s\n",
            date('Y-m-d H:i:s'),
            $eventType,
            $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            $_SESSION['user_id'] ?? 'GUEST',
            $details
        );
        
        error_log($logMessage, 3, 'logs/security.log');
    }
}
?>
