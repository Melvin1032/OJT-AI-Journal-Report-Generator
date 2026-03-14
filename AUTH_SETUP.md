# 🔐 User Authentication System - Setup Guide

## Overview

The OJT Journal Report Generator now includes a complete user authentication system to properly isolate data between different users. Each user will have their own:
- **OJT Entries** - Journal entries are private to each user
- **API Keys** - Encrypted and stored per-user
- **Student Information** - Personal data kept separate

## 🚀 Installation Steps

### Step 1: Run the Database Migration

First, you need to create the users table and update existing tables:

1. Open your browser and navigate to:
   ```
   http://your-domain/db/create_users_table.php
   ```

2. You should see a success message indicating:
   - ✓ Users table created
   - ✓ user_api_keys table updated
   - ✓ ojt_entries table updated with user_id
   - ✓ entry_images table updated with user_id
   - ✓ student_info table updated with user_id
   - ✓ Guest user created

3. **Important:** Delete the migration file after running:
   ```
   Delete: db/create_users_table.php
   ```

### Step 2: Access the Application

After running the migration:

1. Navigate to the main application:
   ```
   http://your-domain/index.php
   ```

2. You will be automatically redirected to the **Login Page**

### Step 3: Create Your Account

1. Click **"Sign Up"** on the login page
2. Fill in the registration form:
   - **Username**: 3-50 characters (letters, numbers, underscores only)
   - **Email**: Valid email address
   - **Password**: Minimum 8 characters
   - **Confirm Password**: Must match

3. Click **"Create Account"**
4. You will be redirected to the login page

### Step 4: Login

1. Enter your **username or email**
2. Enter your **password**
3. Click **"Sign In"**

### Step 5: Guest Access (Optional)

If you want to quickly test the application without creating an account:

1. Click **"Continue as Guest"** on the login page
2. You will be logged in with a guest account
3. **Note:** Guest data is still isolated from other users

### Step 6: Configure API Keys

After logging in:

1. You will be redirected to the **API Key Setup** page (if keys aren't configured)
2. Enter your API keys for:
   - **OpenRouter** (Primary AI)
   - **Google Gemini** (Fallback AI)
   - **Groq** (AI Agents)
3. Click **"Verify Keys & Continue"**
4. Keys are encrypted and stored for your user account only

## 📋 Features

### User Dashboard

After logging in, you'll see:
- **Username** in the top-right corner
- **User Menu** with:
  - Your username and email
  - Settings link
  - Logout button

### Data Isolation

Each user has completely isolated data:
- ✅ **OJT Entries** - Only visible to you
- ✅ **Images** - Uploaded images are private
- ✅ **Student Information** - Your company/school info
- ✅ **API Keys** - Encrypted and stored per-user

### Security Features

- 🔒 **Password Hashing** - Using PHP's `password_hash()` with bcrypt
- 🔒 **CSRF Protection** - All forms include CSRF tokens
- 🔒 **Session Security** - Session regeneration on login
- 🔒 **Encrypted API Keys** - API keys encrypted before storage
- 🔒 **Input Validation** - Server-side validation on all inputs
- 🔒 **Rate Limiting** - Protection against brute force attacks

## 🔄 Migration from Session-Based to User-Based

If you have existing data from the session-based system:

### Option 1: Keep Existing Data (Recommended for Testing)

The system supports both session-based and user-based isolation:
- **Guest users** continue to use session_id
- **Registered users** use user_id
- Existing session-based data remains accessible

### Option 2: Migrate Existing Data

If you want to migrate old session-based data to a user account:

1. Login with your new account
2. The system will automatically associate new entries with your user_id
3. Old session-based entries will remain separate

## 🛠️ Troubleshooting

### "Invalid CSRF Token" Error

**Cause:** Session expired or browser cache issue

**Solution:**
1. Clear browser cache and cookies
2. Refresh the page
3. Try logging in again

### "Database Connection Failed"

**Cause:** Database file permissions or path issue

**Solution:**
1. Check that `storage/db/` folder exists
2. Ensure write permissions on the folder
3. Verify `config/config.php` has correct DB_PATH

### "Username Already Taken"

**Cause:** Username is already registered

**Solution:**
1. Try a different username
2. Or login with your existing account

### "Email Already Registered"

**Cause:** Email is already in use

**Solution:**
1. Use a different email
2. Or login with your existing account and update email in settings

### Can't See My Entries After Login

**Cause:** Entries were created under a different session or user account

**Solution:**
1. Ensure you're logged in with the correct account
2. Check if you created entries as a guest (different isolation)
3. Each user account has separate data

## 📊 Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### User API Keys Table
```sql
CREATE TABLE user_api_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER DEFAULT NULL,
    session_id TEXT DEFAULT NULL,
    openrouter_key TEXT,
    gemini_key TEXT,
    groq_key TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### OJT Entries Table (Updated)
```sql
CREATE TABLE ojt_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    user_description TEXT,
    entry_date DATE NOT NULL,
    ai_enhanced_description TEXT,
    user_id INTEGER DEFAULT NULL,
    session_id TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🔐 Best Practices

1. **Use Strong Passwords** - At least 8 characters with mixed case, numbers, and symbols
2. **Don't Share Accounts** - Each user should have their own account
3. **Logout When Done** - Especially on shared computers
4. **Keep API Keys Private** - Never share your API keys
5. **Regular Backups** - Backup your database regularly

## 📝 Files Added/Modified

### New Files:
- `register.php` - Registration page
- `login.php` - Login page
- `public/register.php` - Registration API
- `public/login.php` - Login API
- `public/logout.php` - Logout API
- `src/auth.php` - Authentication helpers
- `db/create_users_table.php` - Database migration

### Modified Files:
- `config/config.php` - Added auth helpers and updated API key functions
- `index.php` - Added authentication requirement and user menu
- `src/process.php` - Updated to use user_id for data isolation
- `public/verify-api-keys.php` - Updated to store keys by user_id

## 🎯 Next Steps

1. ✅ Run the database migration
2. ✅ Create your user account
3. ✅ Configure API keys
4. ✅ Start documenting your OJT journey!

## 📞 Support

If you encounter any issues:
1. Check the error logs in `storage/logs/`
2. Verify all files are uploaded correctly
3. Ensure proper file permissions
4. Contact support if issues persist

---

**Version:** 2.0.0  
**Last Updated:** March 14, 2026
