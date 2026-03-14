<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - OJT Journal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite;
        }

        body::before { width: 400px; height: 400px; top: -100px; right: -100px; }
        body::after { width: 300px; height: 300px; bottom: -50px; left: -50px; animation-delay: -5s; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }

        .left-panel {
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
        }

        .system-name {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .system-tagline {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .developer-info {
            margin-top: auto;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .developer-info p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }

        .developer-info a {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: opacity 0.3s;
        }

        .developer-info a:hover { opacity: 0.8; }

        .right-panel {
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-header h1 {
            color: #1a202c;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #718096;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f7fafc;
            color: #2d3748;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            padding: 0.25rem;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.4s;
            border-radius: 10px;
        }

        .password-strength-bar.weak { width: 33%; background: #fc8181; }
        .password-strength-bar.medium { width: 66%; background: #f6ad55; }
        .password-strength-bar.strong { width: 100%; background: #68d391; }

        .password-hint {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1.25rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .submit-btn.loading .btn-text { display: none; }
        .submit-btn.loading .spinner { display: block; }

        .error-message, .success-message {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: none;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #fc8181;
        }

        .success-message {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #68d391;
        }

        .error-message.show, .success-message.show { display: block; }

        .auth-links {
            text-align: center;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid #e2e8f0;
        }

        .auth-links p {
            color: #718096;
            font-size: 0.9rem;
        }

        .auth-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-links a:hover { text-decoration: underline; }

        .theme-toggle {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 20;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(30deg);
        }

        [data-theme="dark"] .auth-card { background: rgba(30, 30, 46, 0.95); }
        [data-theme="dark"] .auth-header h1, [data-theme="dark"] .form-group label { color: #f7fafc; }
        [data-theme="dark"] .auth-header p, [data-theme="dark"] .password-hint, [data-theme="dark"] .auth-links p { color: #a0aec0; }
        [data-theme="dark"] .input-wrapper input { background: #2d3748; color: #f7fafc; border-color: #4a5568; }
        [data-theme="dark"] .input-wrapper input:focus { background: #1a202c; }
        [data-theme="dark"] .auth-links { border-top-color: #4a5568; }
        [data-theme="dark"] .error-message { background: #742a2a; color: #feb2b2; border-color: #c53030; }
        [data-theme="dark"] .success-message { background: #22543d; color: #9ae6b4; border-color: #48bb78; }

        @media (max-width: 900px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; }
            .auth-card { padding: 2rem 1.5rem; }
            .theme-toggle {
                width: 40px;
                height: 40px;
                top: 1rem;
                right: 1rem;
            }
            .theme-toggle svg { width: 18px; height: 18px; }
            .system-name { font-size: 2rem; }
            .system-tagline { font-size: 1rem; }
            .form-row { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            body { padding: 0; }
            .auth-card {
                border-radius: 0;
                padding: 1.5rem 1rem;
                margin: 1rem;
                max-width: calc(100% - 2rem);
            }
            .auth-header h1 { font-size: 1.5rem; }
            .form-group label { font-size: 0.85rem; }
            .input-wrapper input { font-size: 16px; padding: 0.75rem 0.75rem 0.75rem 2.5rem; }
            .submit-btn { padding: 0.875rem; font-size: 0.95rem; }
            .theme-toggle {
                width: 36px;
                height: 36px;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
            <circle cx="12" cy="12" r="5"/>
            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
        <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22" style="display:none;">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
    </button>

    <div class="left-panel">
        <div>
            <h1 class="system-name">📔 OJT Journal Report Generator</h1>
            <p class="system-tagline">Document your On-the-Job Training journey with AI-powered assistance</p>
            
            <div class="developer-info">
                <p>Developed by</p>
                <a href="https://github.com/Melvin1032" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
                    </svg>
                    John Melvin R. Macabeo
                </a>
                <p style="margin-top: 1rem; opacity: 0.7; font-size: 0.85rem;">
                    <a href="https://github.com/Melvin1032/OJT-AI-Journal-Report-Generator" target="_blank" style="color: white; opacity: 0.9;">
                        View on GitHub →
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Start documenting your journey</p>
            </div>

            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>

            <form id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" required placeholder="Username" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" required placeholder="Email">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" required placeholder="Password" minlength="8">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <p class="password-hint">Minimum 8 characters</p>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirmPassword" name="confirm_password" required placeholder="Confirm password" minlength="8">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text">Create Account</span>
                    <span class="spinner"></span>
                </button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>

    <script>
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

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthBar.className = 'password-strength-bar';
            if (strength <= 2) strengthBar.classList.add('weak');
            else if (strength <= 4) strengthBar.classList.add('medium');
            else strengthBar.classList.add('strong');
        });

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            errorMessage.className = 'error-message';
            successMessage.className = 'success-message';

            const formData = {
                username: document.getElementById('username').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirmPassword').value
            };

            if (!formData.username || !formData.email || !formData.password || !formData.confirm_password) {
                errorMessage.textContent = 'Please fill in all fields';
                errorMessage.className = 'error-message show';
                return;
            }

            if (formData.password !== formData.confirm_password) {
                errorMessage.textContent = 'Passwords do not match';
                errorMessage.className = 'error-message show';
                return;
            }

            if (formData.password.length < 8) {
                errorMessage.textContent = 'Password must be at least 8 characters';
                errorMessage.className = 'error-message show';
                return;
            }

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            try {
                const response = await fetch('public/register.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    successMessage.textContent = 'Account created! Redirecting to login...';
                    successMessage.className = 'success-message show';
                    setTimeout(() => window.location.href = 'login.php?registered=1', 1500);
                } else {
                    errorMessage.textContent = data.error || 'Registration failed';
                    errorMessage.className = 'error-message show';
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            } catch (error) {
                errorMessage.textContent = 'Network error. Please try again.';
                errorMessage.className = 'error-message show';
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
