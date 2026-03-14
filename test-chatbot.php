<?php
/**
 * Test Chatbot API Connection
 */
session_start();
require_once 'config/config.php';
require_once 'src/chatbot/AIChatbot.php';

header('Content-Type: text/plain');

echo "=== Chatbot API Test ===\n\n";

// Check API keys
echo "API Keys Status:\n";
echo "- QWEN_API_KEY: " . (defined('QWEN_API_KEY') && !empty(QWEN_API_KEY) ? '✓ SET' : '✗ EMPTY') . "\n";
echo "- GROQ_API_KEY: " . (defined('GROQ_API_KEY') && !empty(GROQ_API_KEY) ? '✓ SET' : '✗ EMPTY') . "\n";
echo "- GEMINI_API_KEY: " . (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY) ? '✓ SET' : '✗ EMPTY') . "\n\n";

// Test chatbot
echo "Testing Chatbot...\n";
$chatbot = new AIChatbot();

$result = $chatbot->chat("Hello, this is a test message.");

echo "\nResult:\n";
echo "- Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "- Message: " . ($result['message'] ?? 'N/A') . "\n";

if (!$result['success']) {
    echo "\nError Details:\n";
    echo "- Error: " . ($result['error'] ?? 'N/A') . "\n";
    if (isset($result['debug'])) {
        echo "- Debug: " . json_encode($result['debug'], JSON_PRETTY_PRINT) . "\n";
    }
}

echo "\n=== Test Complete ===\n";
