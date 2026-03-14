# 📤 InfinityFree Deployment Guide

## ⚠️ IMPORTANT: API Keys Storage

**Your API keys are NOT stored in `.env` on InfinityFree!**

- ✅ **User API keys** → Stored in `storage/db/journal.db` (per user, isolated by session)
- ❌ **NOT in `.env`** → Keep `.env` empty or with default values only
- 🔒 **Each user** enters their own keys via the setup page

---

## Step 1: Prepare Files for Upload

### A. Clean Your `.env` File

**Option 1: Use the InfinityFree template**
```bash
# Copy the template
cp config/.env.infinityfree config/.env
```

**Option 2: Manually edit `.env`**
Open `config/.env` and **remove all API keys**:
```env
# ❌ REMOVE THIS (your personal keys - replace with actual values)
QWEN_API_KEY=sk-or-v1-YOUR_ACTUAL_KEY_HERE
GROQ_API_KEY=gsk_YOUR_ACTUAL_KEY_HERE
GEMINI_API_KEY=AIzaSy_YOUR_ACTUAL_KEY_HERE

# ✅ KEEP THIS (empty values for production)
QWEN_API_KEY=
GROQ_API_KEY=
GEMINI_API_KEY=
```

### B. Files to Upload

```
✅ Upload to InfinityFree:
├── config/
│   └── .env  ← With EMPTY API keys
├── db/
│   ├── migrate_api_keys.php  ← Run this once
│   └── clear_user_data.php   ← Optional cleanup
├── storage/
│   └── db/
│       └── .gitkeep  ← Create this folder
├── All other PHP/HTML/CSS/JS files
└── .htaccess (if exists)

❌ DO NOT Upload:
├── config/.env  ← Your LOCAL .env with personal keys
├── storage/uploads/*  ← User uploads (create empty on server)
└── storage/db/*.db  ← Database created automatically
```

---

## Step 2: Upload via FTP

1. **Connect to InfinityFree** via FTP (FileZilla, etc.)
2. **Navigate to** `htdocs/` folder
3. **Upload all files** (except `.env` with your personal keys)
4. **Create empty folders**:
   ```
   htdocs/storage/db/
   htdocs/storage/uploads/
   htdocs/storage/cache/
   ```

---

## Step 3: Run Database Migration

After uploading, **run the migration once**:

1. Open browser and go to:
   ```
   https://your-site.infinityfreeapp.com/db/migrate_api_keys.php
   ```

2. You should see:
   ```
   === Database Migration: User API Keys Table ===
   ✓ Table 'user_api_keys' created successfully
   ✓ Index created on session_id
   === Migration Complete ===
   ```

3. **Delete the migration file** (security):
   - In InfinityFree File Manager, delete:
     ```
     htdocs/db/migrate_api_keys.php
     ```

---

## Step 4: Test the Setup Flow

1. **Visit your site**:
   ```
   https://your-site.infinityfreeapp.com
   ```

2. **You should be redirected to**:
   ```
   https://your-site.infinityfreeapp.com/setup.php
   ```

3. **Enter YOUR API keys** via the setup page (not in `.env`!)

4. **Keys are saved to database** - isolated for your session only

---

## Step 5: Multi-User Testing

**Test with different browsers** (Chrome, Firefox, Incognito):

1. **Browser 1 (You)**:
   - Enter your API keys
   - Create OJT entries
   - ✅ Your keys stored with YOUR session_id

2. **Browser 2 (Another user)**:
   - Enter different API keys
   - Create different entries
   - ✅ Their keys stored with THEIR session_id

3. **Result**:
   - ✅ No cross-contamination
   - ✅ Each user has isolated keys
   - ✅ Keys NOT visible in File Manager

---

## 🔒 Security Checklist

| Security Measure | Status |
|-----------------|--------|
| `.env` file has empty API keys | ✅ |
| API keys stored in database (per user) | ✅ |
| Session-based isolation | ✅ |
| CSRF protection on forms | ✅ |
| Migration file deleted after use | ⏳ Do this |
| File permissions set correctly | ⏳ Check below |

---

## File Permissions (InfinityFree)

Set these permissions in File Manager:

```
config/.env           → 644 (read-only)
storage/              → 755
storage/db/           → 755 (writable)
storage/uploads/      → 755 (writable)
storage/cache/        → 755 (writable)
*.php files           → 644
```

---

## Troubleshooting

### ❌ "Database connection failed"
**Fix**: Ensure `storage/db/` folder exists and is writable (755 permissions)

### ❌ "API keys not saving"
**Fix**: Check if `user_api_keys` table exists (run migration again)

### ❌ "Redirect loop to setup.php"
**Fix**: Clear browser cookies/cache, or check if session is working

### ❌ "Other users can see my keys"
**Fix**: This shouldn't happen! Check session configuration in PHP

---

## 📋 Quick Upload Checklist

- [ ] Clean `.env` file (remove personal API keys)
- [ ] Upload all files via FTP
- [ ] Create `storage/db/`, `storage/uploads/` folders
- [ ] Run `db/migrate_api_keys.php` once
- [ ] Delete migration file after running
- [ ] Test setup page flow
- [ ] Test with multiple browsers
- [ ] Set correct file permissions

---

## 🎯 Result

After deployment:
- ✅ Users enter their own API keys via `setup.php`
- ✅ Keys stored in `journal.db` (isolated by session_id)
- ✅ Keys NOT visible in InfinityFree File Manager
- ✅ Multi-user support working correctly
- ✅ Your personal keys stay on YOUR computer only

---

**Need Help?** Check `docs/API_KEY_SETUP_README.md` for more details.
