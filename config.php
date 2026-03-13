<?php
/**
 * Configuration file for Weekly Journal Report Generator
 *
 * API credentials are loaded from .env file.
 */

// Load environment variables from .env file
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key) && !array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Alibaba Qwen API Configuration (OpenRouter)
define('QWEN_API_KEY', getenv('QWEN_API_KEY') ?: $_ENV['QWEN_API_KEY'] ?? '');
define('QWEN_API_ENDPOINT', getenv('QWEN_API_ENDPOINT') ?: $_ENV['QWEN_API_ENDPOINT'] ?? 'https://openrouter.ai/api/v1/chat/completions');

// Primary AI Models
define('QWEN_VISION_MODEL', getenv('QWEN_VISION_MODEL') ?: $_ENV['QWEN_VISION_MODEL'] ?? 'qwen/qwen-2-vl-7b-instruct');
define('QWEN_TEXT_MODEL', getenv('QWEN_TEXT_MODEL') ?: $_ENV['QWEN_TEXT_MODEL'] ?? 'qwen/qwen-2.5-72b-instruct');

// Fallback AI Models (used when primary models fail)
define('FALLBACK_VISION_MODEL', getenv('FALLBACK_VISION_MODEL') ?: $_ENV['FALLBACK_VISION_MODEL'] ?? 'google/gemini-2.0-flash-exp:free');
define('FALLBACK_TEXT_MODEL', getenv('FALLBACK_TEXT_MODEL') ?: $_ENV['FALLBACK_TEXT_MODEL'] ?? 'google/gemini-2.0-flash-exp:free');

// AI Model Configuration
define('AI_MAX_RETRIES', (int)(getenv('AI_MAX_RETRIES') ?: $_ENV['AI_MAX_RETRIES'] ?? 1));
define('AI_TIMEOUT', (int)(getenv('AI_TIMEOUT') ?: $_ENV['AI_TIMEOUT'] ?? 30));

// Database Configuration
define('DB_PATH', getenv('DB_PATH') ?: $_ENV['DB_PATH'] ?? __DIR__ . '/db/journal.db');

// Upload Configuration
define('UPLOAD_DIR', getenv('UPLOAD_DIR') ?: $_ENV['UPLOAD_DIR'] ?? __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', (int)(getenv('MAX_FILE_SIZE') ?: $_ENV['MAX_FILE_SIZE'] ?? 5 * 1024 * 1024)); // 5MB max
define('ALLOWED_TYPES', explode(',', getenv('ALLOWED_TYPES') ?: $_ENV['ALLOWED_TYPES'] ?? 'image/jpeg,image/png,image/gif,image/webp'));

/**
 * Get database connection
 * @return PDO SQLite database connection
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Initialize database if not exists
            initializeDatabase($pdo);
            
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Initialize database tables
 * @param PDO $pdo Database connection
 */
function initializeDatabase($pdo) {
    // Create OJT entries table
    $sql = "CREATE TABLE IF NOT EXISTS ojt_entries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        user_description TEXT,
        entry_date DATE NOT NULL,
        ai_enhanced_description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);

    // Create entry images table
    $sql = "CREATE TABLE IF NOT EXISTS entry_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entry_id INTEGER NOT NULL,
        image_path TEXT NOT NULL,
        image_order INTEGER DEFAULT 0,
        ai_description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (entry_id) REFERENCES ojt_entries(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);

    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_entry_date ON ojt_entries(entry_date)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_entry_images_entry_id ON entry_images(entry_id)");
    
    // Keep old table for backward compatibility (optional)
    $pdo->exec("CREATE TABLE IF NOT EXISTS journal_entries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        image_path TEXT NOT NULL,
        ai_description TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

/**
 * Validate API key is set
 * @return bool
 */
function isApiKeyConfigured() {
    return strpos(QWEN_API_KEY, 'sk-or-') === 0 && strlen(QWEN_API_KEY) > 20;
}

/**
 * Call AI API with fallback support
 * Tries primary model first, then falls back to alternative model if it fails
 * 
 * @param array $requestData The request payload for the API
 * @param string $primaryModel The primary model to use
 * @param string $fallbackModel The fallback model if primary fails
 * @param int $timeout Request timeout in seconds
 * @return array Result with 'success' boolean and 'content' or 'error'
 */
function callAIWithFallback($requestData, $primaryModel, $fallbackModel, $timeout = 30) {
    $models = [$primaryModel];
    if ($primaryModel !== $fallbackModel) {
        $models[] = $fallbackModel;
    }
    
    foreach ($models as $modelIndex => $model) {
        $isFallback = $modelIndex > 0;
        $requestData['model'] = $model;
        
        $ch = curl_init(QWEN_API_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . QWEN_API_KEY,
                'HTTP-Referer: http://localhost:8000',
                'X-Title: OJT Journal Generator'
            ],
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_TIMEOUT => $timeout
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Check for curl errors
        if ($response === false) {
            error_log("AI API Error ({$model}): curl error - " . $curlError);
            continue; // Try fallback
        }
        
        // Check HTTP status
        if ($httpCode !== 200) {
            $result = json_decode($response, true);
            $errorMsg = $result['message'] ?? $result['error']['message'] ?? 'Unknown API error';
            error_log("AI API Error ({$model}): HTTP {$httpCode} - " . $errorMsg);
            continue; // Try fallback
        }
        
        // Parse response
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'content' => trim($result['choices'][0]['message']['content']),
                'model' => $model,
                'used_fallback' => $isFallback
            ];
        }
        
        error_log("AI API Error ({$model}): Unexpected response format");
    }
    
    // All models failed
    return [
        'success' => false,
        'error' => 'All AI models failed to generate a response',
        'models_tried' => $models
    ];
}

/**
 * Simple AI call function (backward compatibility)
 * Uses fallback mechanism internally
 */
function callAIAPI($prompt, $systemMessage, $model = null) {
    $model = $model ?? QWEN_TEXT_MODEL;
    $fallback = ($model === QWEN_TEXT_MODEL) ? FALLBACK_TEXT_MODEL : FALLBACK_VISION_MODEL;
    
    $requestData = [
        'messages' => [
            ['role' => 'user', 'content' => $systemMessage . "\n\n" . $prompt]
        ],
        'max_tokens' => 500,
        'temperature' => 0.5
    ];
    
    $result = callAIWithFallback($requestData, $model, $fallback, AI_TIMEOUT);
    
    if ($result['success']) {
        return $result['content'];
    }
    
    return 'Content generation unavailable. Please try again.';
}
?>
