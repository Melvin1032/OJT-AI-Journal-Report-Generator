<?php
/**
 * Verify API Keys Endpoint
 * 
 * This endpoint verifies the provided API keys by making test requests to each service.
 * Keys are stored in the database for the current session user.
 */

session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Verify CSRF token
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($csrfToken) || !validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$keys = $input['keys'] ?? [];

if (empty($keys['openrouter']) || empty($keys['gemini']) || empty($keys['groq'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All API keys are required']);
    exit;
}

$results = [];
$allValid = true;

// Verify OpenRouter API Key
$results['openrouter'] = verifyOpenRouterKey($keys['openrouter']);
if (!$results['openrouter']['valid']) {
    $allValid = false;
}

// Verify Google Gemini API Key
$results['gemini'] = verifyGeminiKey($keys['gemini']);
if (!$results['gemini']['valid']) {
    $allValid = false;
}

// Verify Groq API Key
$results['groq'] = verifyGroqKey($keys['groq']);
if (!$results['groq']['valid']) {
    $allValid = false;
}

// If all keys are valid, store them in session and database
if ($allValid) {
    // Store in session for quick access
    $_SESSION['api_keys_configured'] = true;
    $_SESSION['api_keys'] = [
        'openrouter' => $keys['openrouter'],
        'gemini' => $keys['gemini'],
        'groq' => $keys['groq']
    ];
    
    // Store in database for persistence (user-specific)
    storeApiKeys($keys);
}

echo json_encode([
    'success' => $allValid,
    'results' => $results
]);

/**
 * Verify OpenRouter API Key
 */
function verifyOpenRouterKey($apiKey) {
    $ch = curl_init('https://openrouter.ai/api/v1/models');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: http://localhost:8000',
            'X-Title: OJT Journal Generator'
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return ['valid' => false, 'message' => 'Connection error: ' . $error];
    }
    
    if ($httpCode === 200) {
        return ['valid' => true, 'message' => 'Valid API key'];
    }
    
    $data = json_decode($response, true);
    $errorMsg = $data['error']['message'] ?? 'Invalid API key';
    
    return ['valid' => false, 'message' => $errorMsg];
}

/**
 * Verify Google Gemini API Key
 */
function verifyGeminiKey($apiKey) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return ['valid' => false, 'message' => 'Connection error: ' . $error];
    }
    
    if ($httpCode === 200) {
        return ['valid' => true, 'message' => 'Valid API key'];
    }
    
    $data = json_decode($response, true);
    $errorMsg = $data['error']['message'] ?? 'Invalid API key';
    
    return ['valid' => false, 'message' => $errorMsg];
}

/**
 * Verify Groq API Key
 */
function verifyGroqKey($apiKey) {
    $ch = curl_init('https://api.groq.com/openai/v1/models');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return ['valid' => false, 'message' => 'Connection error: ' . $error];
    }
    
    if ($httpCode === 200) {
        return ['valid' => true, 'message' => 'Valid API key'];
    }
    
    $data = json_decode($response, true);
    $errorMsg = $data['error']['message'] ?? 'Invalid API key';
    
    return ['valid' => false, 'message' => $errorMsg];
}

/**
 * Store API keys in database for current user
 * Uses user_id for authenticated users, session_id for guests
 */
function storeApiKeys($keys) {
    try {
        $pdo = getDbConnection();

        // Create user_api_keys table if not exists (with both user_id and session_id support)
        $createTable = "CREATE TABLE IF NOT EXISTS user_api_keys (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT NULL,
            session_id TEXT DEFAULT NULL,
            openrouter_key TEXT NOT NULL,
            gemini_key TEXT NOT NULL,
            groq_key TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        $pdo->exec($createTable);

        // Create indexes for faster lookups
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_api_keys_user_id ON user_api_keys(user_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_api_keys_session_id ON user_api_keys(session_id)");

        // Determine whether to use user_id or session_id
        $userId = getCurrentUserId();
        $sessionId = session_id();

        // Encrypt API keys before storing
        $encryptedKeys = [
            'openrouter' => encryptApiKey($keys['openrouter']),
            'gemini' => encryptApiKey($keys['gemini']),
            'groq' => encryptApiKey($keys['groq'])
        ];

        // Check if keys already exist for this user
        if ($userId) {
            // Use user_id for authenticated users
            $checkStmt = $pdo->prepare("SELECT id FROM user_api_keys WHERE user_id = ?");
            $checkStmt->execute([$userId]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                $updateStmt = $pdo->prepare("
                    UPDATE user_api_keys
                    SET openrouter_key = ?, gemini_key = ?, groq_key = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ?
                ");
                $updateStmt->execute([
                    $encryptedKeys['openrouter'],
                    $encryptedKeys['gemini'],
                    $encryptedKeys['groq'],
                    $userId
                ]);
            } else {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_api_keys (user_id, openrouter_key, gemini_key, groq_key)
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $userId,
                    $encryptedKeys['openrouter'],
                    $encryptedKeys['gemini'],
                    $encryptedKeys['groq']
                ]);
            }
        } else {
            // Use session_id for guests (backward compatibility)
            $checkStmt = $pdo->prepare("SELECT id FROM user_api_keys WHERE session_id = ?");
            $checkStmt->execute([$sessionId]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                $updateStmt = $pdo->prepare("
                    UPDATE user_api_keys
                    SET openrouter_key = ?, gemini_key = ?, groq_key = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE session_id = ?
                ");
                $updateStmt->execute([
                    $encryptedKeys['openrouter'],
                    $encryptedKeys['gemini'],
                    $encryptedKeys['groq'],
                    $sessionId
                ]);
            } else {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_api_keys (session_id, openrouter_key, gemini_key, groq_key)
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $sessionId,
                    $encryptedKeys['openrouter'],
                    $encryptedKeys['gemini'],
                    $encryptedKeys['groq']
                ]);
            }
        }

        return true;
    } catch (PDOException $e) {
        error_log('Failed to store API keys: ' . $e->getMessage());
        return false;
    }
}
?>
