<?php
/**
 * Security Helper Functions
 * Provides CSRF protection, input validation, rate limiting, and file upload security
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF Token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF Token as JSON
 * @return string JSON response with token
 */
function getCSRFTokenJSON() {
    return json_encode(['csrf_token' => generateCSRFToken()]);
}

/**
 * Validate CSRF Token from Request
 * @return void
 * @throws Exception If token is invalid
 */
function requireCSRFValidation() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check header first (for AJAX requests)
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        
        if (empty($csrfToken)) {
            throw new Exception('CSRF token missing', 403);
        }
        
        if (!validateCSRFToken($csrfToken)) {
            throw new Exception('Invalid CSRF token', 403);
        }
    }
}

/**
 * Sanitize Input Data
 * @param string $data Input data
 * @param string $type Type of data (string, email, date, text, int, float)
 * @param int $maxLength Maximum length for strings
 * @return mixed Sanitized data or false if invalid
 */
function sanitizeInput($data, $type = 'string', $maxLength = 500) {
    if (!isset($data) || $data === '') {
        return $type === 'string' ? '' : false;
    }
    
    // Remove whitespace and slashes
    $data = trim($data);
    $data = stripslashes($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL);
            
        case 'date':
            $date = DateTime::createFromFormat('Y-m-d', $data);
            return ($date && $date->format('Y-m-d') === $data) ? $data : false;
            
        case 'datetime':
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $data);
            return ($date && $date->format('Y-m-d H:i:s') === $data) ? $data : false;
            
        case 'int':
        case 'integer':
            return filter_var($data, FILTER_VALIDATE_INT) !== false ? (int)$data : false;
            
        case 'float':
        case 'double':
            return filter_var($data, FILTER_VALIDATE_FLOAT) !== false ? (float)$data : false;
            
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL);
            
        case 'text':
            // Sanitize HTML but allow basic formatting
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            return substr($data, 0, $maxLength);
            
        case 'html':
            // Strip dangerous tags but allow basic HTML
            $allowedTags = '<p><br><strong><em><ul><ol><li>';
            $data = strip_tags($data, $allowedTags);
            return substr($data, 0, $maxLength);
            
        case 'filename':
            // Only allow alphanumeric, dash, underscore, and dot
            $data = preg_replace('/[^a-zA-Z0-9._-]/', '', $data);
            return substr($data, 0, 100);
            
        case 'string':
        default:
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            return substr($data, 0, $maxLength);
    }
}

/**
 * Validate Required Fields
 * @param array $fields Array of field names to check in $_POST
 * @return array Array of missing fields
 */
function validateRequiredFields($fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (empty($_POST[$field]) && $_POST[$field] !== '0') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Rate Limiting Check
 * @param string $action Action identifier
 * @param int $limit Maximum requests allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if within limit, false if exceeded
 */
function checkRateLimit($action, $limit = 10, $timeWindow = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit:" . md5("{$ip}:{$action}");
    
    // Use file-based storage for rate limiting data
    $cacheDir = __DIR__ . '/cache/rate_limits';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/' . md5($key) . '.json';
    $now = time();
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data && is_array($data)) {
            if ($now - $data['timestamp'] < $timeWindow) {
                if ($data['count'] >= $limit) {
                    return false;
                }
                $data['count']++;
            } else {
                $data = ['timestamp' => $now, 'count' => 1];
            }
        } else {
            $data = ['timestamp' => $now, 'count' => 1];
        }
    } else {
        $data = ['timestamp' => $now, 'count' => 1];
    }
    
    file_put_contents($cacheFile, json_encode($data));
    return true;
}

/**
 * Get Rate Limit Info
 * @param string $action Action identifier
 * @return array Rate limit information
 */
function getRateLimitInfo($action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit:" . md5("{$ip}:{$action}");
    $cacheFile = __DIR__ . '/cache/rate_limits/' . md5($key) . '.json';
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data && is_array($data)) {
            return [
                'remaining' => max(0, 10 - $data['count']),
                'reset' => $data['timestamp'] + 60,
                'limit' => 10
            ];
        }
    }
    
    return ['remaining' => 10, 'reset' => time() + 60, 'limit' => 10];
}

/**
 * Validate Image Upload
 * @param array $file $_FILES array element
 * @return array Validation result with 'valid' boolean and additional info
 */
function validateImageUpload($file) {
    // Check for upload errors
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temporary directory missing',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension blocked the upload'
        ];
        
        $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        return [
            'valid' => false,
            'error' => $errorMessages[$errorCode] ?? 'Unknown upload error'
        ];
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['valid' => false, 'error' => 'File size exceeds 5MB limit'];
    }
    
    // Check MIME type using finfo
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP allowed'];
        }
    } else {
        // Fallback to extension check
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Invalid file extension'];
        }
    }
    
    // Check if it's a valid image
    $dimensions = getimagesize($file['tmp_name']);
    if (!$dimensions) {
        return ['valid' => false, 'error' => 'File is not a valid image'];
    }
    
    // Check dimensions (max 4096x4096)
    if ($dimensions[0] > 4096 || $dimensions[1] > 4096) {
        return ['valid' => false, 'error' => 'Image dimensions exceed 4096x4096'];
    }
    
    // Minimum dimensions
    if ($dimensions[0] < 100 || $dimensions[1] < 100) {
        return ['valid' => false, 'error' => 'Image too small (minimum 100x100)'];
    }
    
    // Generate secure filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $secureName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
    
    return [
        'valid' => true,
        'secure_name' => $secureName,
        'mime_type' => $dimensions['mime'],
        'width' => $dimensions[0],
        'height' => $dimensions[1],
        'size' => $file['size']
    ];
}

/**
 * Move Uploaded File Securely
 * @param array $file $_FILES array element
 * @param string $destination Destination directory
 * @param string $secureName Secure filename
 * @return array Result with success status and path
 */
function moveUploadedFileSecurely($file, $destination, $secureName) {
    // Ensure destination directory exists
    if (!is_dir($destination)) {
        if (!mkdir($destination, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
    }
    
    $targetPath = rtrim($destination, '/') . DIRECTORY_SEPARATOR . $secureName;
    
    // Use move_uploaded_file for security
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'path' => $targetPath,
            'url' => str_replace('\\', '/', $targetPath)
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to save uploaded file'];
}

/**
 * Check if Request is AJAX
 * @return bool
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Require AJAX Request
 * @return void
 * @throws Exception
 */
function requireAjax() {
    if (!isAjaxRequest()) {
        throw new Exception('Invalid request method', 400);
    }
}

/**
 * Set Security Headers
 * @return void
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (adjust as needed)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;");
}

/**
 * Hash Password
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify Password
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate Secure Random Token
 * @param int $length Token length in bytes
 * @return string Hex token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Clean Rate Limit Cache (remove old entries)
 * @param int $olderThan Remove entries older than this many seconds
 * @return int Number of files removed
 */
function cleanRateLimitCache($olderThan = 86400) {
    $cacheDir = __DIR__ . '/cache/rate_limits';
    
    if (!is_dir($cacheDir)) {
        return 0;
    }
    
    $removed = 0;
    $now = time();
    $files = glob($cacheDir . '/*.json');
    
    foreach ($files as $file) {
        if (file_exists($file) && (filemtime($file) < ($now - $olderThan))) {
            unlink($file);
            $removed++;
        }
    }
    
    return $removed;
}

// Automatically set security headers for all requests
setSecurityHeaders();
