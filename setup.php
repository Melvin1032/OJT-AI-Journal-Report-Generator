<?php
session_start();

// If already configured, redirect to main app
if (isset($_SESSION['api_keys_configured']) && $_SESSION['api_keys_configured'] === true) {
    header('Location: index.php');
    exit;
}

// Generate CSRF token
require_once 'config/config.php';
$csrfToken = generateCSRFToken();

// Check if user already has API keys in database
if (hasUserApiKeys()) {
    $_SESSION['api_keys_configured'] = true;
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Set up your API keys for OJT Journal Generator">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    <title>🔑 API Key Setup - OJT Journal Report Generator</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="apple-touch-icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .setup-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .setup-card {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
        }

        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .setup-header h1 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .setup-header p {
            color: var(--text-secondary);
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
            flex-wrap: wrap;
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

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .status-badge.pending {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
        }

        .status-badge.verifying {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .status-badge.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .status-badge.error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }

        .verify-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .verify-btn:hover {
            background: var(--primary-hover);
        }

        .verify-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .verify-btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .verify-btn.verifying .spinner {
            display: block;
        }

        .verify-btn.verifying .btn-text {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .verification-results {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            display: none;
        }

        .verification-results.show {
            display: block;
        }

        .verification-results.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
        }

        .verification-results.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error-color);
        }

        .result-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            color: var(--text-primary);
        }

        .result-item svg {
            flex-shrink: 0;
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

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .feature-list li {
            padding: 0.5rem 0;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .feature-list li::before {
            content: "✓";
            color: var(--success-color);
            font-weight: bold;
        }

        .theme-toggle-wrapper {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 100;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .setup-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .setup-card {
                padding: 1.25rem;
                margin-bottom: 1rem;
            }

            .setup-header h1 {
                font-size: 1.25rem;
            }

            .setup-header p {
                font-size: 0.9rem;
            }

            .api-key-section {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .api-key-section h3 {
                font-size: 1rem;
                flex-wrap: wrap;
            }

            .api-key-section .icon {
                font-size: 1.25rem;
            }

            .api-key-section h3 .status-badge {
                width: 100%;
                margin-left: 0;
                margin-top: 0.5rem;
                justify-content: center;
            }

            .api-key-info {
                padding: 0.75rem;
                font-size: 0.85rem;
            }

            .api-key-info p {
                font-size: 0.85rem;
            }

            .api-key-input input {
                font-size: 16px;
                padding: 0.7rem 0.8rem;
            }

            .toggle-visibility {
                right: 0.5rem;
                padding: 0.5rem;
            }

            .verify-btn {
                padding: 0.875rem 1rem;
                font-size: 0.95rem;
            }

            .warning-box {
                padding: 0.75rem;
            }

            .warning-box p {
                font-size: 0.85rem;
            }

            .feature-list li {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .setup-card {
                padding: 1rem;
            }

            .setup-header h1 {
                font-size: 1.1rem;
            }

            .api-key-section h3 {
                font-size: 0.95rem;
            }

            .verify-btn {
                font-size: 0.9rem;
                padding: 0.75rem 0.875rem;
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

    <div class="setup-container">
        <div class="setup-header">
            <h1>🔑 API Key Setup</h1>
            <p>Configure your API keys to use the OJT Journal Generator</p>
        </div>

        <div class="setup-card">
            <h2 style="margin-bottom: 1rem; color: var(--text-primary);">Why API Keys?</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                This application uses AI services to enhance your journal entries. Each API key connects to a different AI provider:
            </p>
            <ul class="feature-list">
                <li><strong>OpenRouter (Qwen)</strong> - Primary AI for text generation and image analysis</li>
                <li><strong>Google Gemini</strong> - Fallback AI for reliable responses</li>
                <li><strong>Groq</strong> - Fast AI processing for AI Agents Dashboard</li>
            </ul>
            <div class="warning-box">
                <p>
                    <strong>🔒 Your keys are stored securely in the database</strong> - they are never shared with other users.
                    Each user has their own isolated API keys.
                </p>
            </div>
        </div>

        <form id="apiKeyForm" class="setup-card">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- OpenRouter API Key -->
            <div class="api-key-section">
                <h3>
                    <span class="icon">🚀</span>
                    OpenRouter API Key (Primary)
                    <span class="status-badge pending" id="openrouter-status">Not verified</span>
                </h3>
                <div class="api-key-info">
                    <p><strong>Used for:</strong> Main AI text generation and image analysis</p>
                    <p><strong>Get your free API key:</strong> 
                        <a href="https://openrouter.ai/keys" target="_blank" rel="noopener noreferrer">
                            openrouter.ai/keys →
                        </a>
                    </p>
                    <p style="margin-bottom: 0;">
                        <small>💡 OpenRouter provides free access to Qwen models. Sign up with Google/GitHub for instant access.</small>
                    </p>
                </div>
                <div class="api-key-input">
                    <label for="openrouterKey">OpenRouter API Key</label>
                    <div class="input-with-toggle">
                        <input type="password" id="openrouterKey" name="openrouter_key" 
                               placeholder="sk-or-v1-..." required 
                               pattern="^sk-or-.+" 
                               title="OpenRouter keys start with 'sk-or-'">
                        <button type="button" class="toggle-visibility" onclick="toggleVisibility('openrouterKey')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Google Gemini API Key -->
            <div class="api-key-section">
                <h3>
                    <span class="icon">💎</span>
                    Google Gemini API Key (Fallback)
                    <span class="status-badge pending" id="gemini-status">Not verified</span>
                </h3>
                <div class="api-key-info">
                    <p><strong>Used for:</strong> Fallback AI when OpenRouter is unavailable</p>
                    <p><strong>Get your free API key:</strong> 
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">
                            aistudio.google.com/app/apikey →
                        </a>
                    </p>
                    <p style="margin-bottom: 0;">
                        <small>💡 Google offers a generous free tier. Sign in with your Google account.</small>
                    </p>
                </div>
                <div class="api-key-input">
                    <label for="geminiKey">Google Gemini API Key</label>
                    <div class="input-with-toggle">
                        <input type="password" id="geminiKey" name="gemini_key" 
                               placeholder="AIzaSy..." required
                               pattern="^AIzaSy.+"
                               title="Gemini keys start with 'AIzaSy'">
                        <button type="button" class="toggle-visibility" onclick="toggleVisibility('geminiKey')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Groq API Key -->
            <div class="api-key-section">
                <h3>
                    <span class="icon">⚡</span>
                    Groq API Key (AI Agents)
                    <span class="status-badge pending" id="groq-status">Not verified</span>
                </h3>
                <div class="api-key-info">
                    <p><strong>Used for:</strong> AI Agents Dashboard for fast processing</p>
                    <p><strong>Get your free API key:</strong> 
                        <a href="https://console.groq.com/keys" target="_blank" rel="noopener noreferrer">
                            console.groq.com/keys →
                        </a>
                    </p>
                    <p style="margin-bottom: 0;">
                        <small>💡 Groq offers free tier with rate limits. Sign up for instant access.</small>
                    </p>
                </div>
                <div class="api-key-input">
                    <label for="groqKey">Groq API Key</label>
                    <div class="input-with-toggle">
                        <input type="password" id="groqKey" name="groq_key" 
                               placeholder="gsk_..." required
                               pattern="^gsk_.+"
                               title="Groq keys start with 'gsk_'">
                        <button type="button" class="toggle-visibility" onclick="toggleVisibility('groqKey')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="verify-btn" id="verifyBtn">
                <span class="spinner"></span>
                <span class="btn-text">Verify Keys & Continue</span>
            </button>

            <div class="verification-results" id="verificationResults">
                <!-- Results will be inserted here -->
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

        // Update status badge
        function updateStatus(service, status, message = '') {
            const badge = document.getElementById(`${service}-status`);
            badge.className = `status-badge ${status}`;
            
            const icons = {
                pending: '○',
                verifying: '⟳',
                success: '✓',
                error: '✕'
            };
            
            const labels = {
                pending: 'Not verified',
                verifying: 'Verifying...',
                success: 'Verified',
                error: message || 'Failed'
            };
            
            badge.textContent = `${icons[status]} ${labels[status]}`;
        }

        // Form submission
        document.getElementById('apiKeyForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const verifyBtn = document.getElementById('verifyBtn');
            const resultsDiv = document.getElementById('verificationResults');
            
            const keys = {
                openrouter: document.getElementById('openrouterKey').value.trim(),
                gemini: document.getElementById('geminiKey').value.trim(),
                groq: document.getElementById('groqKey').value.trim()
            };
            
            // Validate all keys are present
            if (!keys.openrouter || !keys.gemini || !keys.groq) {
                alert('Please fill in all three API keys');
                return;
            }
            
            verifyBtn.classList.add('verifying');
            verifyBtn.disabled = true;
            resultsDiv.className = 'verification-results';
            resultsDiv.innerHTML = '';
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            // Call backend to verify keys
            try {
                const response = await fetch('public/verify-api-keys.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ keys })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update status badges
                    updateStatus('openrouter', 'success');
                    updateStatus('gemini', 'success');
                    updateStatus('groq', 'success');
                    
                    resultsDiv.className = 'verification-results show success';
                    resultsDiv.innerHTML = `
                        <div class="result-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="color: var(--success-color);">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <strong>All API keys verified successfully!</strong>
                        </div>
                        <p style="margin-top: 0.5rem; color: var(--text-secondary);">Redirecting to journal generator...</p>
                    `;
                    
                    // Redirect to main app
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    // Handle partial or full failures
                    const results = data.results || {};
                    
                    if (results.openrouter) {
                        updateStatus('openrouter', results.openrouter.valid ? 'success' : 'error', results.openrouter.message);
                    }
                    if (results.gemini) {
                        updateStatus('gemini', results.gemini.valid ? 'success' : 'error', results.gemini.message);
                    }
                    if (results.groq) {
                        updateStatus('groq', results.groq.valid ? 'success' : 'error', results.groq.message);
                    }
                    
                    const failed = Object.entries(results).filter(([, r]) => !r.valid);
                    
                    resultsDiv.className = 'verification-results show error';
                    resultsDiv.innerHTML = `
                        <div class="result-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="color: var(--error-color);">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <strong>Some keys failed verification:</strong>
                        </div>
                        <ul style="margin: 0.5rem 0 0 1.5rem; color: var(--text-secondary);">
                            ${failed.map(([k, v]) => `<li>${k.toUpperCase()}: ${v.message}</li>`).join('')}
                        </ul>
                        <p style="margin-top: 0.5rem;">Please check your keys and try again.</p>
                    `;
                    
                    verifyBtn.classList.remove('verifying');
                    verifyBtn.disabled = false;
                }
            } catch (error) {
                resultsDiv.className = 'verification-results show error';
                resultsDiv.innerHTML = `
                    <div class="result-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="color: var(--error-color);">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <strong>Connection error:</strong> ${error.message}
                    </div>
                    <p style="margin-top: 0.5rem;">Please check your internet connection and try again.</p>
                `;

                verifyBtn.classList.remove('verifying');
                verifyBtn.disabled = false;
            }
        });
    </script>
    <script src="assets/js/mobile-touch.js"></script>
</body>
</html>
