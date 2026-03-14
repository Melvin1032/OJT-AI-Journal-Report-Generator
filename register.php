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
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        }
        .auth-card {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--border-color);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            color: var(--text-primary);
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        .auth-header p {
            color: var(--text-secondary);
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1.5rem;
        }
        .submit-btn:hover {
            background: var(--primary-hover);
        }
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error-color);
            color: var(--error-color);
            padding: 0.875rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1rem;
            display: none;
        }
        .error-message.show {
            display: block;
        }
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
            padding: 0.875rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1rem;
            display: none;
        }
        .success-message.show {
            display: block;
        }
        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .theme-toggle-wrapper {
            position: fixed;
            top: 1rem;
            right: 1rem;
        }
    </style>
</head>
<body>
    <div class="theme-toggle-wrapper">
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
            <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span style="font-size: 3rem;">📝</span>
                <h1>Create Account</h1>
                <p>Join to document your OJT journey</p>
            </div>

            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>

            <form id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                           placeholder="Choose a username" minlength="3" maxlength="50">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Create a password" minlength="8">
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required
                           placeholder="Confirm your password" minlength="8">
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">Create Account</button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        html.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
        themeToggle.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });

        // Form submission
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

            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            try {
                const response = await fetch('public/register.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    successMessage.textContent = 'Account created! Redirecting...';
                    successMessage.className = 'success-message show';
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    errorMessage.textContent = data.error || 'Registration failed';
                    errorMessage.className = 'error-message show';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Account';
                }
            } catch (error) {
                errorMessage.textContent = 'Network error. Please try again.';
                errorMessage.className = 'error-message show';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        });
    </script>
</body>
</html>
