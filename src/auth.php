<?php
/**
 * Authentication Helper Functions
 * 
 * Provides authentication checks and user-related utilities
 */

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null Username or null if not logged in
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Check if current user is a guest
 * @return bool
 */
function isGuest() {
    return isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Redirect to index if already logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /index.php');
        exit;
    }
}

/**
 * Get user data by ID
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log('Error getting user: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get current user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    $userId = getCurrentUserId();
    if (!$userId) {
        return null;
    }
    return getUserById($userId);
}

/**
 * Update user's API keys in database
 * @param int $userId User ID
 * @param array $keys API keys array
 * @return bool Success status
 */
function updateUserApiKeys($userId, $keys) {
    try {
        $pdo = getDbConnection();
        
        // Encrypt API keys
        $encryptedKeys = [];
        foreach ($keys as $service => $key) {
            $encryptedKeys[$service] = encryptApiKey($key);
        }
        
        // Check if user has existing keys
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_api_keys WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            // Update existing keys
            $stmt = $pdo->prepare("
                UPDATE user_api_keys 
                SET openrouter_key = ?, gemini_key = ?, groq_key = ?, updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?
            ");
            $stmt->execute([
                $encryptedKeys['openrouter'] ?? null,
                $encryptedKeys['gemini'] ?? null,
                $encryptedKeys['groq'] ?? null,
                $userId
            ]);
        } else {
            // Insert new keys
            $stmt = $pdo->prepare("
                INSERT INTO user_api_keys (user_id, openrouter_key, gemini_key, groq_key)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $encryptedKeys['openrouter'] ?? null,
                $encryptedKeys['gemini'] ?? null,
                $encryptedKeys['groq'] ?? null
            ]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Error updating API keys: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user's API keys from database
 * @param int $userId User ID
 * @return array|null Array of API keys or null if not found
 */
function getUserApiKeysFromDb($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT openrouter_key, gemini_key, groq_key FROM user_api_keys WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        if ($result) {
            return [
                'openrouter' => getApiKey($result['openrouter_key']),
                'gemini' => getApiKey($result['gemini_key']),
                'groq' => getApiKey($result['groq_key'])
            ];
        }
        
        return null;
    } catch (PDOException $e) {
        error_log('Error getting API keys: ' . $e->getMessage());
        return null;
    }
}

/**
 * Delete user's API keys from database
 * @param int $userId User ID
 * @return bool Success status
 */
function deleteUserApiKeysByUserId($userId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM user_api_keys WHERE user_id = ?");
        $stmt->execute([$userId]);
        return true;
    } catch (PDOException $e) {
        error_log('Error deleting API keys: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get OJT entries for current user
 * @param int $limit Number of entries to fetch
 * @param int $offset Offset for pagination
 * @return array Array of entries
 */
function getUserOjtEntries($limit = 50, $offset = 0) {
    try {
        $pdo = getDbConnection();
        $userId = getCurrentUserId();
        
        if (!$userId) {
            return [];
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM ojt_entries 
            WHERE user_id = ? 
            ORDER BY entry_date DESC, created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting OJT entries: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get OJT entry by ID for current user
 * @param int $entryId Entry ID
 * @return array|null Entry data or null if not found
 */
function getUserOjtEntry($entryId) {
    try {
        $pdo = getDbConnection();
        $userId = getCurrentUserId();
        
        if (!$userId) {
            return null;
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM ojt_entries 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$entryId, $userId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log('Error getting OJT entry: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get entry images for current user
 * @param int $entryId Entry ID
 * @return array Array of images
 */
function getUserEntryImages($entryId) {
    try {
        $pdo = getDbConnection();
        $userId = getCurrentUserId();
        
        if (!$userId) {
            return [];
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM entry_images 
            WHERE entry_id = ? AND user_id = ?
            ORDER BY image_order ASC
        ");
        $stmt->execute([$entryId, $userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting entry images: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get student info for current user
 * @return array|null Student info or null if not found
 */
function getUserStudentInfo() {
    try {
        $pdo = getDbConnection();
        $userId = getCurrentUserId();
        
        if (!$userId) {
            return null;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM student_info WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log('Error getting student info: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get count of entries for current user
 * @return int Entry count
 */
function getUserEntryCount() {
    try {
        $pdo = getDbConnection();
        $userId = getCurrentUserId();
        
        if (!$userId) {
            return 0;
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ojt_entries WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Error getting entry count: ' . $e->getMessage());
        return 0;
    }
}
?>
