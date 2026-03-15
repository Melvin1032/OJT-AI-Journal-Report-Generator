# 🔧 Installation & Migration Guide

## Quick Start for New Users

### 1. **Run Database Migration**
Visit: `http://your-domain.com/db/migrate.php`

This will:
- Add `user_id` columns to all tables
- Create necessary indexes
- Ensure proper user isolation

### 2. **Register a New Account**
Visit: `http://your-domain.com/register.php`

- Choose a username and email
- Create a strong password
- Click "Create Account"

### 3. **Login**
Visit: `http://your-domain.com/login.php`

- Enter your credentials
- You'll be redirected to the API Setup page

### 4. **Configure API Keys**
After login, you'll be automatically redirected to `setup.php`

**Required API Keys:**
1. **OpenRouter (Primary)** - Get from [openrouter.ai/keys](https://openrouter.ai/keys)
2. **Google Gemini (Fallback)** - Get from [aistudio.google.com/app/apikey](https://aistudio.google.com/app/apikey)
3. **Groq (AI Agents)** - Get from [console.groq.com/keys](https://console.groq.com/keys)

### 5. **Start Creating Entries!**
Once API keys are verified, you'll be redirected to the dashboard.

---

## Issues Fixed

### ✅ 1. Login Redirect Loop
**Problem:** Users were redirected back to login after successful authentication.

**Fix:**
- Updated `public/login.php` to properly restore API keys from database to session
- Added `api_keys_configured` flag in login response
- Frontend now redirects to `setup.php` if API keys are not configured, or `index.php` if they are

### ✅ 2. User Isolation
**Problem:** Database tables didn't have `user_id` columns for user-specific data.

**Fix:**
- Updated `config/config.php` to create tables with `user_id` columns
- Created migration script `db/migrate.php` for existing databases
- All queries now filter by `user_id` for proper isolation

### ✅ 3. Images Not Displaying
**Problem:** Images were saved without `user_id`, causing them to not load.

**Fix:**
- Database migration adds `user_id` to `entry_images` table
- Image saving code already includes `user_id` (was correct, just needed DB schema update)
- Image retrieval properly filters by `user_id`

### ✅ 4. API Key Configuration for New Users
**Problem:** New users had no clear path to configure API keys.

**Fix:**
- After login, users without API keys are redirected to `setup.php`
- After registration, users must login and then configure API keys
- Clear UI in setup page with links to get free API keys

---

## For Existing Installations

### If you have existing data:

1. **Backup your database first!**
   ```bash
   cp storage/db/journal.db storage/db/journal.backup.db
   ```

2. **Run the migration:**
   Visit: `http://your-domain.com/db/migrate.php`

3. **Update existing entries (optional):**
   If you want to assign existing entries to a user:
   ```sql
   -- In SQLite browser or similar tool
   UPDATE ojt_entries SET user_id = 1 WHERE user_id IS NULL;
   UPDATE entry_images SET user_id = 1 WHERE user_id IS NULL;
   UPDATE student_info SET user_id = 1 WHERE user_id IS NULL;
   ```

---

## For Hosting/Production Deployment

### Server Requirements:
- PHP 7.4 or higher
- SQLite3 extension enabled
- Write permissions for `storage/` directory
- HTTPS recommended for production

### Directory Permissions:
```bash
chmod 755 storage/
chmod 755 storage/db/
chmod 755 storage/uploads/
chmod 644 storage/db/journal.db
```

### .htaccess (Apache):
Already included in `.htaccess` files:
- Redirect all requests to HTTPS
- Protect sensitive files
- Enable CORS for API endpoints

### Environment Configuration:
Create `config/.env` file:
```env
DB_PATH=storage/db/journal.db
UPLOAD_DIR=storage/uploads/
QWEN_API_KEY=sk-or-v1-your-key-here
GEMINI_API_KEY=your-gemini-key
GROQ_API_KEY=your-groq-key
```

---

## Troubleshooting

### "Database connection failed"
- Check that `storage/db/` directory exists and is writable
- Verify SQLite3 extension is enabled in PHP

### "API keys not configured"
- Login to your account
- Go to Settings or visit `setup.php`
- Enter all three API keys
- Click "Verify Keys & Continue"

### "Images not displaying"
1. Run the migration: `db/migrate.php`
2. Check that `storage/uploads/` is writable
3. Verify image paths in browser console

### "Redirect loop after login"
1. Clear browser cache and cookies
2. Check that `public/login.php` returns `api_keys_configured` flag
3. Verify session is being properly started

### "403 Forbidden" on upload
- Check directory permissions: `chmod 755 storage/uploads/`
- Verify `php.ini` allows file uploads:
  ```ini
  file_uploads = On
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

---

## Security Notes

- ✅ API keys are encrypted before storage
- ✅ User data is isolated by `user_id`
- ✅ CSRF protection on all forms
- ✅ Input validation and sanitization
- ✅ Prepared statements prevent SQL injection

---

## Support

If you encounter any issues:
1. Check the browser console for errors
2. Check `storage/logs/` for PHP errors
3. Verify all files have correct permissions
4. Ensure database migration completed successfully

---

**Last Updated:** March 15, 2026
**Version:** 2.0.0 (with user authentication)
