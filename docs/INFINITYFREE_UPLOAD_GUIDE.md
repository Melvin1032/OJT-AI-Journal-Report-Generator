# 📤 INFINITYFREE UPLOAD GUIDE

## ⚠️ IMPORTANT: What's Saved Locally

Your data is stored in these files (DO NOT upload these to InfinityFree):

### Local Data Files:
```
❌ db/journal.db              ← Your OJT entries, student info, API keys
❌ storage/.encryption_key     ← API key encryption key
❌ storage/uploads/            ← Your uploaded images
❌ storage/logs/               ← Log files
❌ storage/cache/              ← Cache files
```

These stay on your **localhost only** and won't be uploaded to InfinityFree.

---

## 📦 Files TO UPLOAD to InfinityFree

### ✅ Upload These Folders/Files:

```
htdocs/
├── index.php                 ← Main app
├── setup.php                 ← API key setup (first-time)
├── settings.php              ← API key settings
├── start.ps1                 ← Optional (Windows startup script)
├── stop.ps1                  ← Optional (Windows stop script)
├── .gitignore                ← Git ignore file
│
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── print-styles.css
│   │   └── enhancements.css
│   ├── js/
│   │   ├── script.js
│   │   ├── mobile-touch.js
│   │   └── ... (all JS files)
│   └── images/
│       ├── logo.svg          ← App logo
│       └── favicon.png       ← Browser icon
│
├── config/
│   ├── config.php            ← Main configuration
│   ├── .env                  ← EMPTY API keys (template only)
│   └── .env.example          ← Example template
│
├── src/
│   ├── *.php                 ← All PHP files
│   ├── api_helpers.php       ← API helper functions ⭐ NEW
│   ├── encryption.php        ← Encryption functions ⭐ NEW
│   ├── process.php           ← Entry processing
│   ├── security.php
│   ├── logger.php
│   │
│   ├── chatbot/
│   │   └── AIChatbot.php
│   │
│   ├── agents/
│   │   ├── BaseAgent.php
│   │   └── *.php             ← All agent files
│   │
│   └── tools/
│       └── *.php             ← All tool files
│
├── public/
│   ├── verify-api-keys.php   ← API key verification ⭐ NEW
│   ├── api-test.php          ← API test page
│   └── test.php              ← Test file (optional)
│
├── dashboards/
│   └── agents-dashboard.php  ← AI Agents dashboard
│
├── db/
│   └── clear_user_data.php   ← Cleanup script (optional)
│
├── docs/
│   ├── README.md             ← Main documentation
│   ├── API_KEY_FIX_SUMMARY.md
│   ├── API_KEY_ENCRYPTION.md
│   ├── API_KEY_SETUP_README.md
│   ├── FINAL_API_KEY_FIX.md
│   ├── INFINITYFREE_DEPLOYMENT.md
│   ├── STUDENT_INFO_AI_FIX.md
│   └── INDEX.md              ← Documentation index
│
├── storage/
│   ├── .htaccess             ← Security file ⭐ NEW
│   ├── .gitkeep              ← Keep empty folders
│   ├── db/.gitkeep
│   ├── uploads/.gitkeep
│   ├── cache/.gitkeep
│   └── logs/.gitkeep
│
└── uploads/
    └── .gitkeep              ← Keep empty
```

---

## 🚀 Step-by-Step Upload Instructions

### Step 1: Prepare Files on Your Computer

1. **Create a clean copy** of your project:
   - Copy the entire `OJT-AI-Journal-Report-Generator` folder
   - Rename it to `OJT-InfinityFree-Upload`

2. **Delete these files** from the copy:
   ```
   ❌ db/journal.db
   ❌ storage/.encryption_key
   ❌ storage/uploads/* (all images)
   ❌ storage/logs/*
   ❌ storage/cache/*
   ```

3. **Keep these** (they're empty placeholders):
   ```
   ✅ db/.gitkeep
   ✅ storage/db/.gitkeep
   ✅ storage/uploads/.gitkeep
   ✅ storage/cache/.gitkeep
   ✅ storage/logs/.gitkeep
   ```

---

### Step 2: Upload to InfinityFree via FTP

1. **Download FileZilla** (or use InfinityFree's file manager)
   - https://filezilla-project.org/

2. **Connect to InfinityFree:**
   ```
   Host: ftpupload.net (or your InfinityFree FTP host)
   Username: Your InfinityFree username (e.g., epiz_12345678)
   Password: Your InfinityFree password
   Port: 21
   ```

3. **Navigate to htdocs/ folder:**
   - On InfinityFree, open the `htdocs/` directory
   - Delete any existing files (if this is a fresh install)

4. **Upload all files:**
   - Drag and drop ALL files from your clean copy to `htdocs/`
   - Wait for upload to complete (may take 5-10 minutes)

---

### Step 3: Set File Permissions

In InfinityFree File Manager, set these permissions:

```
config/.env              → 644 (read-only)
storage/                 → 755
storage/db/              → 755 (writable)
storage/uploads/         → 755 (writable)
storage/cache/           → 755 (writable)
storage/logs/            → 755 (writable)
*.php files              → 644
```

---

### Step 4: Create Empty Folders

In InfinityFree File Manager, ensure these folders exist:

```
htdocs/storage/db/
htdocs/storage/uploads/
htdocs/storage/cache/
htdocs/storage/logs/
htdocs/uploads/
```

If they don't exist, create them manually.

---

### Step 5: Test Your InfinityFree Site

1. **Visit your site:**
   ```
   https://your-site.infinityfreeapp.com
   ```

2. **You should be redirected to setup:**
   ```
   https://your-site.infinityfreeapp.com/setup.php
   ```

3. **Enter your API keys:**
   - OpenRouter API Key
   - Google Gemini API Key
   - Groq API Key

4. **Test features:**
   - Create an OJT entry
   - Upload an image
   - Generate AI content
   - Test chatbot

---

## 🔒 Security Notes

### What's Protected:

✅ **API Keys** - Stored encrypted in database per user  
✅ **No .env exposure** - API keys not in config files  
✅ **Session isolation** - Each user has separate keys  
✅ **File protection** - `.htaccess` blocks direct access  

### What You Should Do:

1. **Enable HTTPS** on InfinityFree (free SSL)
2. **Delete test files** after deployment:
   ```
   public/test.php
   public/api-test.php
   db/clear_user_data.php
   ```
3. **Set strong FTP password** on InfinityFree
4. **Don't share your API keys** publicly

---

## 📊 Multi-User Support on InfinityFree

### How It Works:

```
User A visits your site
        ↓
Enters API keys in setup.php
        ↓
Keys encrypted & saved to journal.db
        ↓
Linked to User A's session_id
        ↓
User A creates entries (isolated)

User B visits the SAME site
        ↓
Enters THEIR OWN API keys
        ↓
Keys encrypted & saved to SAME database
        ↓
Linked to User B's DIFFERENT session_id
        ↓
User B creates entries (isolated from User A)

✅ No cross-contamination!
✅ Each user has their own API keys!
✅ Each user sees only their own entries!
```

---

## 🐛 Troubleshooting

### Problem: "Database connection failed"
**Solution:** 
- Ensure `storage/db/` folder exists
- Set permissions to 755
- Check if `journal.db` is created automatically

### Problem: "API keys not saving"
**Solution:**
- Check if `user_api_keys` table exists
- Run this in your browser: `https://your-site.com/db/migrate_api_keys.php`
- Then delete the migration file

### Problem: "500 Internal Server Error"
**Solution:**
- Check `storage/logs/` for error logs
- Ensure all PHP files uploaded correctly
- Verify `.htaccess` is in storage folder

### Problem: "Images not uploading"
**Solution:**
- Ensure `storage/uploads/` folder exists
- Set permissions to 755
- Check file size limits in `.env`

---

## ✅ Upload Checklist

Before uploading to InfinityFree:

- [ ] Created clean copy of project
- [ ] Deleted `db/journal.db` (local database)
- [ ] Deleted `storage/.encryption_key`
- [ ] Deleted all uploaded images
- [ ] Deleted log files
- [ ] Kept `.gitkeep` files in folders
- [ ] Uploaded all files via FTP
- [ ] Set correct file permissions
- [ ] Created empty folders
- [ ] Tested setup page
- [ ] Entered API keys
- [ ] Tested create entry
- [ ] Tested AI generation
- [ ] Tested chatbot

---

## 📝 Summary

### What Stays on Your Computer:
- ❌ `db/journal.db` - Your local OJT entries
- ❌ `storage/.encryption_key` - Your encryption key
- ❌ `storage/uploads/*` - Your uploaded images

### What Goes to InfinityFree:
- ✅ All PHP files
- ✅ All CSS/JS files
- ✅ All documentation
- ✅ Empty folder structure
- ✅ `.env` with EMPTY API keys (template)

### What Users Do on InfinityFree:
- 👤 Each user enters their OWN API keys
- 👤 Keys stored encrypted in shared database
- 👤 Each user sees only their own entries
- 👤 Complete isolation between users

---

**Ready to upload to InfinityFree!** 🚀

Your localhost data stays on your computer. InfinityFree users will have their own isolated data.
