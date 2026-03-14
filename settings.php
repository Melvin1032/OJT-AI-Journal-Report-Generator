<?php
session_start();
require_once 'config/config.php';

$csrfToken = generateCSRFToken();
$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($submittedToken)) {
        $errorMessage = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'save') {
            // Save API keys
            $openrouterKey = trim($_POST['openrouter_key'] ?? '');
            $geminiKey = trim($_POST['gemini_key'] ?? '');
            $groqKey = trim($_POST['groq_key'] ?? '');
            
            if (empty($openrouterKey) || empty($geminiKey) || empty($groqKey)) {
                $errorMessage = 'All API keys are required.';
            } else {
                // Verify keys before saving
                $verificationResults = [
                    'openrouter' => verifyOpenRouterKey($openrouterKey),
                    'gemini' => verifyGeminiKey($geminiKey),
                    'groq' => verifyGroqKey($groqKey)
                ];
                
                $allValid = $verificationResults['openrouter']['valid'] && 
                           $verificationResults['gemini']['valid'] && 
                           $verificationResults['groq']['valid'];
                
                if ($allValid) {
                    // Store in session
                    $_SESSION['api_keys_configured'] = true;
                    $_SESSION['api_keys'] = [
                        'openrouter' => $openrouterKey,
                        'gemini' => $geminiKey,
                        'groq' => $groqKey
                    ];
                    
                    // Store in database
                    storeApiKeysInDb($openrouterKey, $geminiKey, $groqKey);
                    
                    $successMessage = 'API keys saved successfully!';
                } else {
                    $errors = [];
                    foreach ($verificationResults as $service => $result) {
                        if (!$result['valid']) {
                            $errors[] = ucfirst($service) . ': ' . $result['message'];
                        }
                    }
                    $errorMessage = 'Invalid API keys: ' . implode(', ', $errors);
                }
            }
        } elseif ($action === 'reset') {
            // Reset/Delete API keys
            deleteUserApiKeys();
            $successMessage = 'API keys have been reset. Please enter new keys.';
        }
    }
}

// Get current keys (masked for display)
$currentKeys = getUserApiKeys();
$maskedKeys = [];
if ($currentKeys) {
    foreach ($currentKeys as $service => $key) {
        if (strlen($key) > 10) {
            $maskedKeys[$service] = substr($key, 0, 6) . '...' . substr($key, -4);
        } else {
            $maskedKeys[$service] = '****';
        }
    }
}

// Check if configured
$isConfigured = isset($_SESSION['api_keys_configured']) && $_SESSION['api_keys_configured'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    <title>⚙️ API Key Settings - OJT Journal Generator</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="apple-touch-icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .settings-card {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
        }

        .settings-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .settings-header h1 {
            color: var(--text-primary);
            margin: 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: var(--bg-primary);
        }

        .status-banner {
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .status-banner.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }

        .status-banner.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error-color);
            color: var(--error-color);
        }

        .status-banner.info {
            background: rgba(79, 70, 229, 0.1);
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .api-key-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--bg-tertiary);
            border-radius: var(--border-radius-sm);
            border: 1px solid var(--border-color);
        }

        .api-key-section h3 {
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .api-key-section .icon {
            font-size: 1.5rem;
        }

        .api-key-info {
            background: var(--bg-secondary);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .api-key-info p {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .api-key-info a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .api-key-info a:hover {
            text-decoration: underline;
        }

        .api-key-input {
            margin-top: 1rem;
        }

        .api-key-input label {
            display: block;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .api-key-input input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: var(--transition);
        }

        .api-key-input input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .input-with-toggle {
            position: relative;
        }

        .toggle-visibility {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.25rem;
        }

        .toggle-visibility:hover {
            color: var(--text-primary);
        }

        .current-key-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--bg-secondary);
            border-radius: var(--border-radius-sm);
            font-family: monospace;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-danger {
            background: var(--error-color);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-primary);
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .saving .spinner {
            display: block;
        }

        .saving .btn-text {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .warning-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid var(--warning-color);
            border-radius: var(--border-radius-sm);
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .warning-box p {
            color: var(--warning-color);
            margin: 0;
            font-size: 0.9rem;
        }

        .theme-toggle-wrapper {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 100;
        }

        @media (max-width: 768px) {
            .settings-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .settings-card {
                padding: 1.25rem;
            }

            .settings-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="theme-toggle-wrapper">
        <button class="theme-toggle" id="themeToggle" title="Toggle dark/light mode" aria-label="Toggle dark mode">
            <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    <div class="settings-container">
        <div class="settings-header">
            <a href="index.php" class="back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to App
            </a>
            <h1>⚙️ API Key Settings</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="status-banner success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="status-banner error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!$isConfigured): ?>
            <div class="status-banner info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                API keys are not configured yet. Please enter your keys below.
            </div>
        <?php else: ?>
            <div class="status-banner success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                API keys are configured and working!
            </div>
        <?php endif; ?>

        <form method="POST" class="settings-card" id="apiKeySettingsForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- OpenRouter API Key -->
            <div class="api-key-section">
                <h3>
                    <span class="icon">🚀</span>
                    OpenRouter API Key (Primary)
                </h3>
                <div class="api-key-info">
                    <p><strong>Used for:</strong> Main AI text generation and image analysis</p>
                    <p><strong>Get your free API key:</strong> 
                        <a href="https://openrouter.ai/keys" target="_blank" rel="noopener noreferrer">
                            openrouter.ai/keys →
                        </a>
                    </p>
                </div>
                <div class="api-key-input">
                    <label for="openrouterKey">OpenRouter API Key</label>
                    <div class="input-with-toggle">
                        <input type="password" id="openrouterKey" name="openrouter_key" 
                               placeholder="sk-or-v1-..." 
                               pattern="^sk-or-.+" 
                               title="OpenRouter keys start with 'sk-or-'">
                        <button type="button" class="toggle-visibility" onclick="toggleVisibility('openrouterKey')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($maskedKeys['openrouter'])): ?>
                        <div class="current-key-badge">
                            Current: <?php echo htmlspecialchars($maskedKeys['openrouter']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Google Gemini API Key -->
            <div class="api-key-section">
                <h3>
                    <span class="icon">💎</span>
                    Google Gemini API Key (Fallback)
                </h3>
                <div class="api-key-info">
                    <p><strong>Used for:</strong> Fallback AI when OpenRouter is unavailable</p>
                    <p><strong>Get your free API key:</strong> 
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">
                            aistudio.google.com/app/apikey →
                        </a>
                    </p>
                </div>
                <div class="api-key-input">
                    <label for="geminiKey">Google Gemini API Key</label>
                    <div class="input-with-toggle">
                        <input type="password" id="geminiKey" name="gemini_key" 
                               placeholder="AIzaSy..."
                               pattern="^AIzaSy.+"
                               title="Gemini keys start with 'AIzaSy'">
                        <button type="button" class="toggle-visibility" onclick="toggleVisibility('geminiKey')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($maskedKeys['gemini'])): ?>
                        <div class="current-key-badge">
                            Current: <?php echo htmlspecialchars($maskedKeys['gemini']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Groq API Key -->
            <div class="api-key-section">
                <h3>
                    <span class="icon">⚡</span>
                    Groq API Key (AI Agents)
                </h3>
                <div class="api-key-info">
                    <p><strong>Used for:</strong> AI Agents Dashboard for fast processing</p>
                    <p><strong>Get your free API key:</strong> 
                        <a href="https://console.groq.com/keys" target="_blank" rel="noopener noreferrer">
                            console.groq.com/keys →
                        </a>
                    </p>
                </div>
                <div class="api-key-input">
                    <label for="groqKey">Groq API Key</label>
                    <div class="input-with-toggle">
                        <input type="password" id="groqKey" name="groq_key" 
                               placeholder="gsk_..."
                               pattern="^gsk_.+"
                               title="Groq keys start with 'gsk_'">
                        <button type="button" class="toggle-visibility" onclick="toggleVisibility('groqKey')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($maskedKeys['groq'])): ?>
                        <div class="current-key-badge">
                            Current: <?php echo htmlspecialchars($maskedKeys['groq']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="action" value="save" class="btn btn-primary" id="saveBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">💾 Save Changes</span>
                </button>
                <button type="submit" name="action" value="reset" class="btn btn-danger" id="resetBtn"
                        onclick="return confirm('Are you sure you want to reset your API keys? You will need to enter new keys to continue using the app.')">
                    🗑️ Reset API Keys
                </button>
            </div>

            <div class="warning-box">
                <p>
                    <strong>🔒 Security Notice:</strong> Your API keys are stored securely in the database and are only accessible by your session.
                    Never share your API keys with anyone.
                </p>
            </div>
        </form>
    </div>

    <script>
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });

        // Toggle password visibility
        function toggleVisibility(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Show saving state
        document.getElementById('apiKeySettingsForm').addEventListener('submit', function(e) {
            const action = this.querySelector('button[name="action"][value="' + e.submitter.value + '"]');
            if (action && action.value === 'save') {
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.classList.add('saving');
            }
        });
    </script>
    <script src="assets/js/mobile-touch.js"></script>
</body>
</html>

<?php
// Helper functions (could also be moved to a separate file)

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
    curl_close($ch);
    
    return ['valid' => $httpCode === 200];
}

function verifyGeminiKey($apiKey) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['valid' => $httpCode === 200];
}

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
    curl_close($ch);
    
    return ['valid' => $httpCode === 200];
}

function storeApiKeysInDb($openrouterKey, $geminiKey, $groqKey) {
    try {
        $pdo = getDbConnection();
        $sessionId = session_id();
        
        // Encrypt API keys before storing
        $encryptedOpenrouter = encryptApiKey($openrouterKey);
        $encryptedGemini = encryptApiKey($geminiKey);
        $encryptedGroq = encryptApiKey($groqKey);

        $checkStmt = $pdo->prepare("SELECT id FROM user_api_keys WHERE session_id = ?");
        $checkStmt->execute([$sessionId]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            $updateStmt = $pdo->prepare("
                UPDATE user_api_keys 
                SET openrouter_key = ?, gemini_key = ?, groq_key = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE session_id = ?
            ");
            $updateStmt->execute([$encryptedOpenrouter, $encryptedGemini, $encryptedGroq, $sessionId]);
        } else {
            $insertStmt = $pdo->prepare("
                INSERT INTO user_api_keys (session_id, openrouter_key, gemini_key, groq_key) 
                VALUES (?, ?, ?, ?)
            ");
            $insertStmt->execute([$sessionId, $encryptedOpenrouter, $encryptedGemini, $encryptedGroq]);
        }

        return true;
    } catch (PDOException $e) {
        error_log('Failed to store API keys: ' . $e->getMessage());
        return false;
    }
}
?>
