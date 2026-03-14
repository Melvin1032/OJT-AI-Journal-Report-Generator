<?php
/**
 * API Test Script
 */
require_once 'config.php';

echo "<h2>OJT Journal API Test</h2>";

// Test 1: Database connection
echo "<h3>1. Database Test</h3>";
try {
    $pdo = getDbConnection();
    echo "<p>✓ Database connected</p>";
    
    // Check tables
    $tables = ['ojt_entries', 'entry_images'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
        $count = $stmt->fetch()['count'];
        echo "<p>✓ Table '{$table}': {$count} records</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: API Configuration
echo "<h3>2. API Configuration</h3>";
$userKeys = getUserApiKeys();
$hasKeys = !empty($userKeys['openrouter']) || !empty($userKeys['groq']) || !empty($userKeys['gemini']);
echo "<p>API Keys: " . ($hasKeys ? '✓ Configured (from session/database)' : '✗ Not configured') . "</p>";
if ($userKeys) {
    echo "<p><strong>Your configured APIs:</strong></p>";
    echo "<ul>";
    echo "<li>OpenRouter: " . (!empty($userKeys['openrouter']) ? '✓' : '✗') . "</li>";
    echo "<li>Groq: " . (!empty($userKeys['groq']) ? '✓' : '✗') . "</li>";
    echo "<li>Gemini: " . (!empty($userKeys['gemini']) ? '✓' : '✗') . "</li>";
    echo "</ul>";
}
echo "<p><strong>Primary Models:</strong></p>";
echo "<ul>";
echo "<li>Vision Model: " . htmlspecialchars(QWEN_VISION_MODEL) . "</li>";
echo "<li>Text Model: " . htmlspecialchars(QWEN_TEXT_MODEL) . "</li>";
echo "</ul>";
echo "<p><strong>Fallback Models:</strong></p>";
echo "<ul>";
echo "<li>Vision Fallback: " . htmlspecialchars(FALLBACK_VISION_MODEL) . "</li>";
echo "<li>Text Fallback: " . htmlspecialchars(FALLBACK_TEXT_MODEL) . "</li>";
echo "</ul>";

// Test 3: Test API call with user's key
echo "<h3>3. API Connection Test</h3>";
if (!empty($userKeys['openrouter'])) {
    $ch = curl_init(QWEN_API_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $userKeys['openrouter']
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => QWEN_TEXT_MODEL,
            'messages' => [
                ['role' => 'user', 'content' => 'Say hello']
            ],
            'max_tokens' => 10
        ]),
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "<p>✓ API connection successful (HTTP {$httpCode})</p>";
    } else {
        echo "<p>✗ API connection failed (HTTP {$httpCode})</p>";
        $result = json_decode($response, true);
        if (isset($result['error'])) {
            echo "<p>Error: " . htmlspecialchars($result['error']['message'] ?? $result['message']) . "</p>";
        }
    }
} else {
    echo "<p>⚠ No OpenRouter API key configured. Please enter your API keys in Settings.</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Go to Application</a></p>";
?>
