<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register - OJT Journal</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#9b59b6,#8e44ad,#6c3483);padding:1rem;position:relative;overflow:hidden}
        body::before,body::after{content:'';position:absolute;border-radius:50%;background:rgba(255,255,255,0.1);animation:float 20s infinite}
        body::before{width:300px;height:300px;top:-100px;right:-100px}
        body::after{width:200px;height:200px;bottom:-50px;left:-50px;animation-delay:-5s}
        @keyframes float{0%,100%{transform:translate(0,0) rotate(0deg)}25%{transform:translate(50px,50px) rotate(90deg)}50%{transform:translate(0,100px) rotate(180deg)}75%{transform:translate(-50px,50px) rotate(270deg)}}
        .auth-container{width:100%;max-width:380px;position:relative;z-index:10}
        .auth-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);border-radius:16px;padding:2rem 1.5rem;box-shadow:0 10px 40px rgba(0,0,0,0.2)}
        .auth-header{text-align:center;margin-bottom:1.5rem}
        .auth-logo{font-size:3rem;margin-bottom:0.5rem;display:block}
        .auth-header h1{color:#1a202c;font-size:1.5rem;font-weight:700;margin-bottom:0.25rem}
        .auth-header p{color:#718096;font-size:0.875rem}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem}
        .form-group{margin-bottom:0.875rem}
        .form-group label{display:block;color:#2d3748;font-weight:600;margin-bottom:0.35rem;font-size:0.875rem}
        .input-wrapper{position:relative}
        .input-wrapper input{width:100%;padding:0.75rem 0.75rem 0.75rem 2.5rem;border:2px solid #e2e8f0;border-radius:8px;background:#f7fafc;color:#2d3748;font-size:16px;transition:all 0.2s;-webkit-appearance:none}
        .input-wrapper input:focus{outline:none;border-color:#9b59b6;background:white;box-shadow:0 0 0 3px rgba(155,89,182,0.1)}
        .input-icon{position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:#a0aec0;width:20px;height:20px}
        .password-toggle{position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#a0aec0;cursor:pointer;padding:0.25rem}
        .password-toggle:hover{color:#9b59b6}
        .password-strength{margin-top:0.35rem;height:4px;background:#e2e8f0;border-radius:4px;overflow:hidden}
        .password-strength-bar{height:100%;width:0%;transition:all 0.3s}
        .password-strength-bar.weak{width:33%;background:#fc8181}
        .password-strength-bar.medium{width:66%;background:#f6ad55}
        .password-strength-bar.strong{width:100%;background:#68d391}
        .password-hint{font-size:0.7rem;color:#718096;margin-top:0.25rem}
        .submit-btn{width:100%;padding:0.875rem;background:linear-gradient(135deg,#9b59b6,#8e44ad);color:white;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;transition:all 0.2s;margin-top:1rem;-webkit-appearance:none}
        .submit-btn:hover{transform:translateY(-2px);box-shadow:0 8px 16px rgba(155,89,182,0.3)}
        .submit-btn:active{transform:translateY(0)}
        .submit-btn:disabled{opacity:0.6;cursor:not-allowed;transform:none}
        .error-message,.success-message{padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.875rem;display:none;word-wrap:break-word}
        .error-message{background:#fed7d7;color:#c53030;border:1px solid #fc8181}
        .success-message{background:#c6f6d5;color:#22543d;border:1px solid #68d391}
        .error-message.show,.success-message.show{display:block}
        .auth-links{text-align:center;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #e2e8f0}
        .auth-links p{color:#718096;font-size:0.875rem}
        .auth-links a{color:#9b59b6;text-decoration:none;font-weight:600;transition:color 0.2s}
        .auth-links a:hover{color:#8e44ad;text-decoration:underline}
        .app-name{position:fixed;top:1rem;right:1rem;color:rgba(255,255,255,0.95);font-size:0.75rem;font-weight:600;z-index:20;text-align:right;max-width:150px;line-height:1.3}
        .app-name span{display:block;font-size:0.65rem;opacity:0.85;font-weight:400}
        .back-home{position:fixed;top:1rem;left:1rem;color:rgba(255,255,255,0.95);text-decoration:none;font-size:0.8rem;font-weight:500;display:flex;align-items:center;gap:0.35rem;transition:all 0.2s;z-index:20;background:rgba(0,0,0,0.15);padding:0.4rem 0.65rem;border-radius:8px}
        .back-home:hover{color:white;background:rgba(0,0,0,0.25)}
        .developer-credit{position:fixed;bottom:1rem;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.9);font-size:0.7rem;display:flex;flex-direction:column;align-items:center;gap:0.35rem;z-index:20;background:rgba(0,0,0,0.25);padding:0.5rem 0.75rem;border-radius:12px;backdrop-filter:blur(8px);max-width:90%;text-align:center}
        .developer-credit a{color:inherit;text-decoration:none;display:flex;align-items:center;gap:0.35rem;font-weight:600;font-size:0.75rem}
        .developer-credit a:hover{text-decoration:underline}
        .spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,0.3);border-top-color:white;border-radius:50%;animation:spin 0.6s linear infinite;margin:0 auto}
        @keyframes spin{to{transform:rotate(360deg)}}
        .submit-btn.loading .btn-text{display:none}
        .submit-btn.loading .spinner{display:block}
        @media(max-width:768px){
            .app-name{top:0.75rem;right:0.75rem;font-size:0.7rem;max-width:120px}
            .app-name span{font-size:0.6rem}
            .back-home{top:0.75rem;left:0.75rem;font-size:0.75rem;padding:0.35rem 0.5rem}
            .back-home svg{width:16px;height:16px}
        }
        @media(max-width:480px){
            body{padding:0.75rem;flex-direction:column;justify-content:flex-start;padding-top:3.5rem;padding-bottom:8rem}
            body::before,body::after{display:none}
            .form-row{grid-template-columns:1fr}
            .auth-container{max-width:100%}
            .auth-card{padding:1.5rem 1.1rem;border-radius:12px}
            .auth-logo{font-size:2.5rem}
            .auth-header h1{font-size:1.25rem}
            .auth-header p{font-size:0.8rem}
            .form-group label{font-size:0.8rem}
            .input-wrapper input{padding:0.7rem 0.7rem 0.7rem 2.3rem;font-size:16px}
            .input-icon{left:0.7rem;width:18px;height:18px}
            .password-toggle{right:0.7rem}
            .submit-btn{padding:0.8rem;font-size:0.95rem}
            .app-name{position:absolute;top:auto;bottom:100%;right:0;left:0;text-align:center;margin-bottom:0.75rem;max-width:none}
            .back-home{position:absolute;top:auto;bottom:100%;left:0;font-size:0.75rem;margin-bottom:0.75rem}
            .developer-credit{position:fixed;bottom:0.5rem;left:0.5rem;right:0.5rem;transform:none;flex-direction:row;justify-content:center;padding:0.4rem 0.6rem;font-size:0.65rem}
            .developer-credit a{font-size:0.65rem}
            .developer-credit svg{width:12px;height:12px}
        }
        @media(max-width:360px){
            .auth-card{padding:1.25rem 0.9rem}
            .auth-header h1{font-size:1.15rem}
            .input-wrapper input{padding:0.65rem 0.65rem 0.65rem 2.2rem}
            .submit-btn{padding:0.75rem;font-size:0.9rem}
        }
    </style>
</head>
<body>
    <div class="app-name">📔 OJT Journal<span>Report Generator</span></div>
    <a href="index.php" class="back-home">← Back</a>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-logo">📝</span>
                <h1>Create Account</h1>
                <p>Join to document your OJT</p>
            </div>
            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>
            <form id="registerForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" required placeholder="Username" autocomplete="username" minlength="3" pattern="[a-zA-Z0-9_]+">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <p class="password-hint">Letters, numbers, underscores</p>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" required placeholder="Email" autocomplete="email">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" required placeholder="Create password" autocomplete="new-password" minlength="8">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">👁</button>
                    </div>
                    <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
                    <p class="password-hint">Minimum 8 characters</p>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirmPassword" name="confirm_password" required placeholder="Confirm password" autocomplete="new-password" minlength="8">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')" aria-label="Toggle password visibility">👁</button>
                    </div>
                </div>
                <button type="submit" class="submit-btn" id="submitBtn"><span class="btn-text">Create Account</span><span class="spinner"></span></button>
            </form>
            <div class="auth-links"><p>Already have an account? <a href="login.php">Sign In</a></p></div>
        </div>
    </div>
    <div class="developer-credit">
        <span>OJT Journal by <strong>John Melvin R. Macabeo</strong></span>
        <a href="https://github.com/Melvin1032/OJT-AI-Journal-Report-Generator" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            GitHub
        </a>
    </div>
    <script>
        function togglePassword(id){const i=document.getElementById(id),b=i.parentElement.querySelector('.password-toggle');i.type=i.type==='password'?'text':'password';b.textContent=i.type==='password'?'👁':'🙈';}
        const pInput=document.getElementById('password'),bar=document.getElementById('strengthBar');
        pInput.addEventListener('input',()=>{const p=pInput.value;let s=0;if(p.length>=8)s++;if(p.length>=12)s++;if(/[a-z]/.test(p)&&/[A-Z]/.test(p))s++;if(/\d/.test(p))s++;if(/[^a-zA-Z0-9]/.test(p))s++;bar.className='password-strength-bar';if(s<=2)bar.classList.add('weak');else if(s<=4)bar.classList.add('medium');else bar.classList.add('strong');});
        document.getElementById('registerForm').addEventListener('submit',async(e)=>{
            e.preventDefault();
            const btn=document.getElementById('submitBtn'),err=document.getElementById('errorMessage'),suc=document.getElementById('successMessage');
            err.className='error-message';suc.className='success-message';
            const data={username:document.getElementById('username').value.trim(),email:document.getElementById('email').value.trim(),password:document.getElementById('password').value,confirm_password:document.getElementById('confirmPassword').value};
            if(!data.username||!data.email||!data.password||!data.confirm_password){err.textContent='Please fill in all fields';err.className='error-message show';return;}
            if(data.password!==data.confirm_password){err.textContent='Passwords do not match';err.className='error-message show';return;}
            if(data.password.length<8){err.textContent='Password must be at least 8 characters';err.className='error-message show';return;}
            btn.classList.add('loading');btn.disabled=true;
            try{
                const r=await fetch('public/register.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}),d=await r.json();
                if(d.success){suc.textContent='Account created! Redirecting...';suc.className='success-message show';setTimeout(()=>{window.location.href='login.php?registered=1';},1500);}
                else{err.textContent=d.error||'Registration failed';err.className='error-message show';btn.classList.remove('loading');btn.disabled=false;}
            }catch(err){err.textContent='Network error';err.className='error-message show';btn.classList.remove('loading');btn.disabled=false;}
        });
    </script>
</body>
</html>
