# 🚀 Quick Fix Guide - InfinityFree 403 & Database Errors

## Issues You Reported:
1. ❌ **InfinityFree: 403 Forbidden** - Images not displaying
2. ❌ **Database Error:** `no such column: session_id`
3. ❌ **404 Not Found** on index

---

## ✅ Fixes Applied

### 1. Database Error Fixed
**Error:** `SQLSTATE[HY000]: General error: 1 no such column: session_id`

**Fix:** 
- Updated `config/config.php` to include `session_id` column
- Updated `db/migrate.php` to add missing columns

**Action Required:** 
```
Visit: http://your-domain.com/db/migrate.php
```
Run this ONCE to update your database.

---

### 2. Image 403 Forbidden Fixed
**Error:** InfinityFree blocks direct image access with 403

**Fix:**
- Created `src/serve-image.php` - PHP image server
- Updated `src/process.php` to generate `image_url` for each image
- Updated `assets/js/script.js` to use `image_url` instead of `image_path`
- Updated `.htaccess` to allow image access

**How it works:**
```
Old (blocked): storage/uploads/image.jpg → 403 Forbidden
New (works): src/serve-image.php?file=image.jpg → ✓ Image displays
```

---

### 3. 404 Not Found Fixed
**Cause:** Missing database tables or wrong paths

**Fix:**
- Database schema updated in `config/config.php`
- Migration script adds all required tables

**Action Required:**
```
1. Run: http://your-domain.com/db/migrate.php
2. Then visit: http://your-domain.com/public/diagnostic.php
```

---

## 📋 Step-by-Step Fix (InfinityFree)

### Step 1: Upload Updated Files

Upload these NEW/UPDATED files to your InfinityFree hosting:

```
✓ src/serve-image.php          ← NEW FILE (image server)
✓ db/migrate.php               ← UPDATED (adds session_id)
✓ config/config.php            ← UPDATED (fixed schema)
✓ src/process.php              ← UPDATED (image_url support)
✓ assets/js/script.js          ← UPDATED (uses image_url)
✓ .htaccess                    ← UPDATED (allows images)
✓ storage/uploads/.htaccess    ← Make sure this exists!
```

### Step 2: Run Database Migration

Visit in your browser:
```
http://your-domain.infinityfreeapp.com/db/migrate.php
```

You should see:
```
✓ Added user_id column to ojt_entries table
✓ Added user_id column to entry_images table
✓ Added user_id column to student_info table
✓ Added session_id column to user_api_keys table
✓ Users table ensured
✅ Migration completed successfully!
```

### Step 3: Verify with Diagnostic Tool

Visit:
```
http://your-domain.infinityfreeapp.com/public/diagnostic.php
```

Check that all items show ✓ (green checkmarks).

### Step 4: Test Image Upload

1. Login to your account
2. Create a new OJT entry
3. Upload an image
4. Save the entry

**Expected Result:** Image should display in the entry card.

**If still 403:**
- Check file exists: `storage/uploads/your-image.jpg`
- Test directly: `src/serve-image.php?file=your-image.jpg`
- Check permissions: `storage/uploads/` should be 755

---

## 🔍 Testing Checklist

### On Localhost (Development):
```bash
1. Run migration: localhost:8000/db/migrate.php
2. Check diagnostic: localhost:8000/public/diagnostic.php
3. Register account
4. Login → Setup API Keys
5. Create entry with image
6. Verify image displays
```

### On InfinityFree (Production):
```
1. Upload all updated files via FTP
2. Set permissions: storage/* → 755
3. Run migration: your-domain.com/db/migrate.php
4. Run diagnostic: your-domain.com/public/diagnostic.php
5. Register/login
6. Test image upload
```

---

## 🐛 Common Issues & Solutions

### "Migration failed" or "Table already exists"
**Solution:** Tables already exist, migration detected this. Check if columns were added:
```
Visit: your-domain.com/public/diagnostic.php
Look for: "Table X has user_id column" ✓
```

### "Images still 403"
**Check:**
1. Image uploaded successfully? → Check `storage/uploads/` folder
2. File permissions correct? → Should be 644 for files
3. Using correct URL? → Should be `src/serve-image.php?file=filename.jpg`
4. Test image server directly:
   ```
   http://your-domain.com/src/serve-image.php?file=test.jpg
   ```

### "Database locked" or "Cannot write"
**Fix permissions on InfinityFree:**
```
storage/           → 755
storage/db/        → 755
storage/uploads/   → 755
storage/db/journal.db → 644
```

### "Session errors after login"
**Solution:**
1. Clear browser cache/cookies
2. Re-login
3. Check `public/diagnostic.php` → Session section

---

## 📁 File Structure (InfinityFree)

```
htdocs/  (your root directory)
├── .htaccess                    ← Updated
├── index.php
├── login.php
├── register.php
├── setup.php
├── settings.php
│
├── config/
│   └── config.php              ← Updated
│
├── src/
│   ├── serve-image.php         ← NEW! Image server
│   ├── process.php             ← Updated
│   ├── security.php
│   └── ...
│
├── db/
│   └── migrate.php             ← Updated
│
├── public/
│   ├── diagnostic.php          ← NEW! System checker
│   ├── login.php
│   ├── register.php
│   └── verify-api-keys.php
│
├── assets/
│   ├── js/
│   │   └── script.js           ← Updated
│   └── css/
│
└── storage/
    ├── db/
    │   └── journal.db
    ├── uploads/
    │   ├── .htaccess           ← Make sure this exists!
    │   └── (your images)
    ├── logs/
    └── cache/
```

---

## 🎯 What Changed (Technical)

### Backend Changes:

1. **`config/config.php`**
   - Added `session_id` column to `user_api_keys` table schema
   - Ensured `users` table is created

2. **`db/migrate.php`**
   - Added migration for `session_id` column
   - Added creation of `users` table

3. **`src/process.php`**
   - Modified `getWeeklyReport()` to generate `image_url`
   - Extracts filename and creates image server URL

4. **`src/serve-image.php`** (NEW)
   - Serves images securely through PHP
   - Validates filename, prevents directory traversal
   - Sets correct headers for InfinityFree

### Frontend Changes:

1. **`assets/js/script.js`**
   - Updated `createEntryCard()` to use `img.image_url || img.image_path`
   - Backward compatible with existing images

2. **`.htaccess`**
   - Added CSP header for images: `img-src 'self' data: blob: https: *`
   - Added Directory block for `storage/uploads` with CORS headers

---

## ✅ Verification Commands

### Check Database Schema:
```sql
-- In SQLite browser or via PHP
PRAGMA table_info(user_api_keys);
-- Should show: id, user_id, session_id, openrouter_key, gemini_key, groq_key, ...
```

### Test Image Server:
```bash
# Upload a test image to storage/uploads/
# Then visit:
http://your-domain.com/src/serve-image.php?file=test.jpg
```

### Check File Permissions:
```bash
# On InfinityFree via FTP or file manager
storage/           → drwxr-xr-x (755)
storage/uploads/   → drwxr-xr-x (755)
storage/uploads/*.jpg → -rw-r--r-- (644)
```

---

## 🆘 Still Not Working?

### 1. Check Browser Console (F12)
Look for specific error messages:
- `403 Forbidden` → Permission issue
- `404 Not Found` → File doesn't exist or wrong path
- `CORS error` → .htaccess issue

### 2. Check PHP Errors
```
storage/logs/
```
Look for recent error logs.

### 3. Test Each Component:
```
✓ Database: your-domain.com/public/diagnostic.php
✓ Migration: your-domain.com/db/migrate.php
✓ Image Server: your-domain.com/src/serve-image.php?file=test.jpg
✓ Upload: Create entry with image, check storage/uploads/
```

### 4. InfinityFree Specific
- Free hosting may have downtime
- Check if your account is active
- Some features limited on free plan

---

## 📞 Support Resources

- **Diagnostic Tool:** `public/diagnostic.php`
- **Migration Script:** `db/migrate.php`
- **InfinityFree Guide:** `INFINITYFREE_GUIDE.md`
- **Bug Fixes:** `BUGFIXES.md`
- **Installation:** `INSTALLATION.md`

---

**After running migration, your images should work!** 🎉

If you still see 403 errors:
1. Verify migration completed successfully
2. Check image exists in `storage/uploads/`
3. Test image server directly
4. Check browser console for exact error

**Need help?** Share the exact error message from browser console (F12).
