# 🌐 InfinityFree Hosting Guide

## ⚠️ Important: InfinityFree Limitations

InfinityFree's free hosting has several restrictions that affect this application:

1. **403 Forbidden on direct file access** - They block direct access to files outside `htdocs`
2. **No custom error pages** - Their injected error pages override yours
3. **Limited SQLite support** - Can be slow or blocked
4. **Forced ads/injections** - They may inject scripts into your pages

## ✅ Solution: Use Image Server

I've created a PHP image server (`src/serve-image.php`) that serves images through PHP instead of direct file access. This bypasses InfinityFree's 403 errors.

---

## 📋 Setup Instructions

### 1. Upload Files

**Via FTP (FileZilla recommended):**

```
htdocs/
├── index.php
├── login.php
├── register.php
├── setup.php
├── settings.php
├── .htaccess          ← Upload this!
├── config/
├── src/
│   ├── serve-image.php    ← IMPORTANT: This serves images
│   ├── process.php
│   └── ...
├── assets/
├── db/
│   └── migrate.php    ← Run this first!
├── public/
└── storage/
    ├── db/
    │   └── journal.db
    ├── uploads/       ← Must be writable!
    │   └── .htaccess
    ├── cache/
    └── logs/
```

### 2. Set Permissions

In InfinityFree file manager or via FTP:

```
storage/           → 755
storage/db/        → 755
storage/uploads/   → 755
storage/logs/      → 755
storage/db/journal.db  → 644
```

### 3. Run Migration

Visit: `http://your-domain.infinityfreeapp.com/db/migrate.php`

This will:
- Add `user_id` columns to database
- Add `session_id` column (for compatibility)
- Create necessary indexes

### 4. Register & Login

1. Go to `http://your-domain.infinityfreeapp.com/register.php`
2. Create an account
3. Login
4. You'll be redirected to API Setup
5. Enter your API keys

---

## 🔧 InfinityFree-Specific Fixes

### Image Display (403 Error Fix)

**Problem:** InfinityFree returns 403 when accessing images directly.

**Solution:** Images are now served through `src/serve-image.php`

**How it works:**
```html
<!-- Old (doesn't work on InfinityFree) -->
<img src="storage/uploads/image.jpg">

<!-- New (works everywhere) -->
<img src="src/serve-image.php?file=image.jpg">
```

The JavaScript automatically uses the correct URL format.

### Database Error Fix

**Problem:** Missing `session_id` column in `user_api_keys` table.

**Solution:** Run the migration script:
```
http://your-domain.com/db/migrate.php
```

### CSP Headers

Updated `.htaccess` with proper Content-Security-Policy for InfinityFree:
```apache
img-src 'self' data: blob: https: *;
```

---

## 🐛 Troubleshooting

### "403 Forbidden" on Images

**Check:**
1. File exists in `storage/uploads/`
2. File permissions are 644
3. `.htaccess` in uploads folder exists
4. Image is served via `src/serve-image.php?file=filename.jpg`

**Test image server directly:**
```
http://your-domain.com/src/serve-image.php?file=test.jpg
```

If it shows "Image not found" - file doesn't exist
If it shows 403 - permission issue
If it downloads/shows image - WORKING! ✓

### "Database connection failed"

**Fix:**
1. Check `storage/db/` folder exists
2. Set permissions: `chmod 755 storage/db/`
3. Run migration: `db/migrate.php`

### "General error: 1 no such column: session_id"

**Fix:** Run the migration script immediately:
```
http://your-domain.com/db/migrate.php
```

### Redirect Loop After Login

**Fix:**
1. Clear browser cache
2. Check console for errors
3. Verify API keys are saved in database
4. Run diagnostic: `public/diagnostic.php`

### InfinityFree Injects Ads/Scripts

**Partial Fix:** Add to `.htaccess`:
```apache
Header set Content-Security-Policy "script-src 'self' 'unsafe-inline' ..."
```

Note: InfinityFree may still inject some scripts on free plan.

---

## 📊 Diagnostic Tool

Visit: `http://your-domain.com/public/diagnostic.php`

This checks:
- ✓ PHP version
- ✓ Database connection
- ✓ File permissions
- ✓ API keys
- ✓ Session status
- ✓ Image server

---

## 🚀 Better Alternatives to InfinityFree

If you experience too many issues, consider these free/cheap alternatives:

### Free Options:
1. **Oracle Cloud Always Free** - Full VPS, no restrictions
2. **Google Cloud Run** - Free tier available
3. **Railway.app** - $5 free credit monthly

### Cheap Options ($3-5/month):
1. **Namecheap Shared Hosting** - $3.98/month
2. **Hostinger** - $2.99/month
3. **DigitalOcean Droplet** - $4/month

---

## 🔒 Security Notes for InfinityFree

1. **API Keys are encrypted** before database storage ✓
2. **User data isolated** by `user_id` ✓
3. **CSRF protection** on all forms ✓
4. **Image server validates** file types ✓

**Additional Recommendations:**
- Change database file name to something obscure
- Add IP-based rate limiting
- Monitor logs regularly
- Backup database weekly

---

## 📝 Quick Checklist

- [ ] Upload all files via FTP
- [ ] Set folder permissions (755 for folders, 644 for files)
- [ ] Run `db/migrate.php`
- [ ] Run `public/diagnostic.php` to verify
- [ ] Register new account
- [ ] Configure API keys
- [ ] Test image upload
- [ ] Test image display in entries
- [ ] Test on mobile device

---

## 🆘 Still Having Issues?

1. **Check browser console** (F12) for specific errors
2. **Check storage/logs/** for PHP errors
3. **Run diagnostic tool** for system health
4. **Test image server directly**: `src/serve-image.php?file=your-image.jpg`

---

**Last Updated:** March 15, 2026
**Version:** 2.0.1 (InfinityFree Compatible)
