<?php
/**
 * API Helper Functions
 * 
 * Provides functions to get user-specific API keys and call AI services
 */

/**
 * Get user API key or throw exception if not configured
 * @param string $service Service name ('openrouter', 'gemini', 'groq')
 * @return string API key
 * @throws Exception if API key not configured
 */
function getUserApiKeyOrError($service) {
    $key = getUserApiKey($service);
    
    if (empty($key)) {
        throw new Exception(
            "API key not configured for {$service}. " .
            "Please go to Settings and enter your API keys."
        );
    }
    
    return $key;
}

/**
 * Call AI API with user's API keys
 * This is a wrapper that uses user-specific keys instead of .env keys
 * 
 * @param array $messages Messages array for chat completion
 * @param string|null $model Model to use
 * @param array $options Additional options (temperature, max_tokens, etc.)
 * @return string AI response content
 * @throws Exception if all API calls fail
 */
function callAIWithUserKeys(array $messages, $model = null, array $options = []) {
    $userKeys = getUserApiKeys();
    
    // Check if this is a vision request (has image_url in messages)
    $isVision = false;
    foreach ($messages as $msg) {
        if (isset($msg['content']) && is_array($msg['content'])) {
            foreach ($msg['content'] as $content) {
                if (isset($content['type']) && $content['type'] === 'image_url') {
                    $isVision = true;
                    break 2;
                }
            }
        }
    }
    
    // For vision requests, only try OpenRouter (supports vision)
    if ($isVision) {
        if (!empty($userKeys['openrouter'])) {
            try {
                return callOpenRouterWithKey($messages, $userKeys['openrouter'], $model, $options);
            } catch (Exception $e) {
                error_log('OpenRouter vision failed: ' . $e->getMessage());
                throw new Exception('Image analysis failed: ' . $e->getMessage());
            }
        } else {
            throw new Exception('OpenRouter API key required for image analysis');
        }
    }
    
    // For text requests, try each provider in order
    // Try OpenRouter first (primary)
    if (!empty($userKeys['openrouter'])) {
        try {
            return callOpenRouterWithKey($messages, $userKeys['openrouter'], $model, $options);
        } catch (Exception $e) {
            error_log('OpenRouter failed: ' . $e->getMessage());
            // Continue to try other APIs
        }
    }
    
    // Try Groq (fast)
    if (!empty($userKeys['groq'])) {
        try {
            return callGroqWithKey($messages, $userKeys['groq'], $options);
        } catch (Exception $e) {
            error_log('Groq failed: ' . $e->getMessage());
        }
    }
    
    // Try Gemini (fallback)
    if (!empty($userKeys['gemini'])) {
        try {
            return callGeminiWithKey($messages, $userKeys['gemini'], $options);
        } catch (Exception $e) {
            error_log('Gemini failed: ' . $e->getMessage());
        }
    }
    
    throw new Exception('All AI services failed. Please check your API keys in Settings.');
}

/**
 * Call OpenRouter API with specific API key
 */
function callOpenRouterWithKey(array $messages, string $apiKey, $model = null, array $options = []) {
    $model = $model ?: getenv('QWEN_TEXT_MODEL') ?: 'qwen/qwen-2.5-72b-instruct';
    $endpoint = getenv('QWEN_API_ENDPOINT') ?: 'https://openrouter.ai/api/v1/chat/completions';
    
    $requestData = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => $options['max_tokens'] ?? 1000
    ];
    
    // Only add temperature if not a vision request
    if (!isset($options['is_vision']) && $options['temperature']) {
        $requestData['temperature'] = $options['temperature'];
    }
    
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: http://localhost:8000',
            'X-Title: OJT Journal Generator'
        ],
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        throw new Exception("OpenRouter connection failed: " . $curlError);
    }
    
    if ($httpCode !== 200) {
        $result = json_decode($response, true);
        $errorMsg = $result['message'] ?? $result['error']['message'] ?? 'Unknown API error';
        throw new Exception("OpenRouter API error ({$httpCode}): " . $errorMsg);
    }
    
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? '';
}

/**
 * Call Groq API with specific API key
 */
function callGroqWithKey(array $messages, string $apiKey, array $options = []) {
    $model = getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile';
    $endpoint = getenv('GROQ_API_ENDPOINT') ?: 'https://api.groq.com/openai/v1/chat/completions';
    
    $requestData = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => $options['temperature'] ?? 0.7,
        'max_tokens' => $options['max_tokens'] ?? 1000
    ];
    
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || $response === false) {
        throw new Exception("Groq API error: HTTP {$httpCode}");
    }
    
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? '';
}

/**
 * Call Gemini API with specific API key
 */
function callGeminiWithKey(array $messages, string $apiKey, array $options = []) {
    $model = getenv('GEMINI_MODEL') ?: 'gemini-2.0-flash-exp';
    $endpoint = getenv('GEMINI_API_ENDPOINT') ?: 'https://generativelanguage.googleapis.com/v1beta/models';
    
    // Get last user message
    $lastMessage = end($messages);
    $prompt = $lastMessage['content'] ?? '';
    
    $url = $endpoint . "/{$model}:generateContent?key=" . $apiKey;
    
    $requestData = [
        'contents' => [[
            'parts' => [['text' => $prompt]]
        ]],
        'generationConfig' => [
            'temperature' => $options['temperature'] ?? 0.7,
            'maxOutputTokens' => $options['max_tokens'] ?? 1000
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || $response === false) {
        throw new Exception("Gemini API error: HTTP {$httpCode}");
    }
    
    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
}
?>
