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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Animated background shapes */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite;
        }

        body::before {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
        }

        body::after {
            width: 300px;
            height: 300px;
            bottom: -50px;
            left: -50px;
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(50px, 50px) rotate(90deg); }
            50% { transform: translate(0, 100px) rotate(180deg); }
            75% { transform: translate(-50px, 50px) rotate(270deg); }
        }

        .auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .auth-header h1 {
            color: #1a202c;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #718096;
            font-size: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #f7fafc;
            color: #2d3748;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #f5576c;
            background: white;
            box-shadow: 0 0 0 4px rgba(245, 87, 108, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            pointer-events: none;
            transition: color 0.3s;
        }

        .input-wrapper input:focus + .input-icon {
            color: #f5576c;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #f5576c;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 5px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.4s ease;
            border-radius: 10px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: linear-gradient(90deg, #fc8181, #f56565);
        }

        .password-strength-bar.medium {
            width: 66%;
            background: linear-gradient(90deg, #f6ad55, #ed8936);
        }

        .password-strength-bar.strong {
            width: 100%;
            background: linear-gradient(90deg, #68d391, #48bb78);
        }

        .password-hint {
            font-size: 0.8rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
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

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .submit-btn.loading .btn-text {
            display: none;
        }

        .submit-btn.loading .spinner {
            display: block;
        }

        .error-message, .success-message {
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
            display: none;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-message {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #c53030;
            border: 1px solid #fc8181;
        }

        .success-message {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #22543d;
            border: 1px solid #68d391;
        }

        .error-message.show, .success-message.show {
            display: block;
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .auth-links p {
            color: #718096;
            font-size: 0.95rem;
        }

        .auth-links a {
            color: #f5576c;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .auth-links a:hover {
            color: #f093fb;
            text-decoration: underline;
        }

        .back-home {
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            z-index: 20;
        }

        .back-home:hover {
            color: white;
            transform: translateX(-5px);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            body {
                padding: 1rem;
            }

            .auth-card {
                padding: 2rem 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.75rem;
            }

            .back-home {
                position: static;
                margin-bottom: 1.5rem;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to Home
    </a>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-logo">📝</span>
                <h1>Create Account</h1>
                <p>Join to document your OJT journey</p>
            </div>

            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>

            <form id="registerForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" required
                                   placeholder="Choose username"
                                   autocomplete="username"
                                   minlength="3"
                                   maxlength="50"
                                   pattern="[a-zA-Z0-9_]+">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <p class="password-hint">3-50 characters, letters, numbers, underscores</p>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" required
                                   placeholder="your@email.com"
                                   autocomplete="email">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" required
                               placeholder="Create a strong password"
                               autocomplete="new-password"
                               minlength="8">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
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
                        <input type="password" id="confirmPassword" name="confirm_password" required
                               placeholder="Confirm your password"
                               autocomplete="new-password"
                               minlength="8">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
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
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Password strength checker
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
            if (strength <= 2) {
                strengthBar.classList.add('weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }
        });

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            // Hide previous messages
            errorMessage.className = 'error-message';
            successMessage.className = 'success-message';

            const formData = {
                username: document.getElementById('username').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirmPassword').value
            };

            // Client-side validation
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
                    successMessage.textContent = 'Account created successfully! Redirecting to login...';
                    successMessage.className = 'success-message show';
                    
                    setTimeout(() => {
                        window.location.href = 'login.php?registered=1';
                    }, 1500);
                } else {
                    errorMessage.textContent = data.error || 'Registration failed. Please try again.';
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

        // Add input animation
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                input.parentElement.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>
