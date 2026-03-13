<?php
/**
 * Test script to verify configuration
 */
require_once 'config.php';

echo "<h2>Configuration Test</h2>";

// Test 1: API Key
echo "<h3>1. API Key Configuration</h3>";
echo "<p>API Key starts with: " . substr(QWEN_API_KEY, 0, 10) . "...</p>";
echo "<p>API Key configured: " . (isApiKeyConfigured() ? 'YES ✓' : 'NO ✗') . "</p>";
echo "<p>Endpoint: " . QWEN_API_ENDPOINT . "</p>";
echo "<p>Model: " . QWEN_MODEL . "</p>";

// Test 2: Database
echo "<h3>2. Database</h3>";
echo "<p>DB Path: " . DB_PATH . "</p>";
try {
    $pdo = getDbConnection();
    echo "<p>Database connection: SUCCESS ✓</p>";
    
    // Check if table exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='journal_entries'");
    if ($stmt->fetch()) {
        echo "<p>Table 'journal_entries': EXISTS ✓</p>";
    } else {
        echo "<p>Table 'journal_entries': NOT FOUND ✗</p>";
    }
} catch (Exception $e) {
    echo "<p>Database connection: FAILED ✗ - " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: Upload Directory
echo "<h3>3. Upload Directory</h3>";
echo "<p>Upload Dir: " . UPLOAD_DIR . "</p>";
if (is_dir(UPLOAD_DIR)) {
    echo "<p>Directory exists: YES ✓</p>";
    if (is_writable(UPLOAD_DIR)) {
        echo "<p>Directory writable: YES ✓</p>";
    } else {
        echo "<p>Directory writable: NO ✗</p>";
    }
} else {
    echo "<p>Directory exists: NO ✗</p>";
}

// Test 4: PHP Extensions
echo "<h3>4. PHP Extensions</h3>";
$extensions = ['pdo', 'pdo_sqlite', 'curl', 'json', 'mbstring'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>{$ext}: " . ($loaded ? 'LOADED ✓' : 'NOT LOADED ✗') . "</p>";
}

// Test 5: cURL Test
echo "<h3>5. cURL Test</h3>";
if (function_exists('curl_version')) {
    $cv = curl_version();
    echo "<p>cURL Version: " . $cv['version'] . "</p>";
} else {
    echo "<p>cURL: NOT AVAILABLE ✗</p>";
}

echo "<hr>";
echo "<p><strong>If all tests pass, your setup is ready!</strong></p>";
echo "<p><a href='index.php'>Go to Application</a></p>";
?>
