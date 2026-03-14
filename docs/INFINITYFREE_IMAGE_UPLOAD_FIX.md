# 🔧 InfinityFree Image Upload Fix

## Problem
InfinityFree injects their own script (`evmAsk.js`) which causes:
1. `Uncaught TypeError: Cannot redefine property: ethereum`
2. Content Security Policy (CSP) violations
3. Image uploads fail silently

## Root Cause
InfinityFree's free hosting injects advertising/tracking scripts that conflict with modern web applications.

---

## Solutions

### Solution 1: Add CSP Meta Tag (Recommended)

Add this to **every PHP page** in the `<head>` section:

```php
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.googleapis.com https://*.gstatic.com; style-src 'self' 'unsafe-inline' https://*.googleapis.com; img-src 'self' data: blob: https:; font-src 'self' https://*.gstatic.com; connect-src 'self' https://*.googleapis.com https://api.groq.com https://openrouter.ai https://generativelanguage.googleapis.com;">
```

### Solution 2: Update .htaccess (Server-Level CSP)

Add to `.htaccess` in root directory:

```apache
<IfModule mod_headers.c>
    Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.googleapis.com https://*.gstatic.com; img-src 'self' data: blob: https:; connect-src 'self' https://*.googleapis.com https://api.groq.com https://openrouter.ai https://generativelanguage.googleapis.com"
</IfModule>
```

### Solution 3: Handle InfinityFree's 403 Error Page

InfinityFree shows custom 403 pages which inject additional scripts. Create a custom error page:

**File:** `.htaccess`
```apache
ErrorDocument 403 /errors/403.php
```

**File:** `errors/403.php`
```php
<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        p { color: #7f8c8d; }
    </style>
</head>
<body>
    <h1>403 - Forbidden</h1>
    <p>You don't have permission to access this resource.</p>
    <a href="index.php">Go to Home</a>
</body>
</html>
```

---

## Image Upload Specific Fixes

### 1. Increase PHP Limits

Create `.user.ini` in root directory:

```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

### 2. Fix File Permissions

In InfinityFree File Manager, set permissions:

```
storage/              → 755
storage/uploads/      → 755
storage/db/           → 755
uploads/              → 755
```

### 3. Update Upload Path

Make sure `config/.env` uses absolute paths:

```env
UPLOAD_DIR=htdocs/storage/uploads/
```

---

## Quick Fix for All Pages

Add this helper function to `config/config.php`:

```php
/**
 * Output CSP meta tag
 */
function outputCSPMetaTag() {
    $csp = "default-src 'self'; "
         . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.googleapis.com https://*.gstatic.com; "
         . "style-src 'self' 'unsafe-inline' https://*.googleapis.com; "
         . "img-src 'self' data: blob: https:; "
         . "font-src 'self' https://*.gstatic.com; "
         . "connect-src 'self' https://*.googleapis.com https://api.groq.com https://openrouter.ai https://generativelanguage.googleapis.com";
    
    echo '<meta http-equiv="Content-Security-Policy" content="' . htmlspecialchars($csp) . '">' . "\n";
}
```

Then in each page's `<head>`:

```php
<head>
    <?php outputCSPMetaTag(); ?>
    <!-- other head elements -->
</head>
```

---

## Testing Upload on InfinityFree

### Step 1: Check PHP Configuration

Create `info.php`:
```php
<?php phpinfo(); ?>
```

Visit: `https://your-site.infinityfreeapp.com/info.php`

Check:
- `upload_max_filesize` → Should be 10M or higher
- `post_max_size` → Should be 12M or higher
- `max_execution_time` → Should be 300 or higher

### Step 2: Test Simple Upload

Create `test-upload.php`:
```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $uploadDir = __DIR__ . '/storage/uploads/';
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmpName = $file['tmp_name'];
        $name = basename($file['name']);
        $size = $file['size'];
        
        if (move_uploaded_file($tmpName, $uploadDir . $name)) {
            echo "✓ Upload successful!<br>";
            echo "File: {$name}<br>";
            echo "Size: {$size} bytes<br>";
            echo '<img src="storage/uploads/' . htmlspecialchars($name) . '" alt="Uploaded" style="max-width: 300px;">';
        } else {
            echo "✗ Failed to move file<br>";
            echo "Error code: " . $file['error'];
        }
    } else {
        echo "✗ Upload error: " . $file['error'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Upload</title>
</head>
<body>
    <h1>Test Image Upload</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
```

### Step 3: Check Error Logs

In InfinityFree File Manager:
- Check `htdocs/logs/error.log`
- Check `htdocs/storage/logs/` (if exists)

---

## Common InfinityFree Issues

### Issue 1: "403 Forbidden" on Upload
**Cause:** File permissions or mod_security  
**Fix:** 
```bash
chmod 755 storage/uploads/
```

### Issue 2: File Too Large
**Cause:** PHP limits too low  
**Fix:** Create `.user.ini` with higher limits

### Issue 3: Upload Times Out
**Cause:** Execution time too short  
**Fix:** Increase `max_execution_time` in `.user.ini`

### Issue 4: evmAsk.js Error
**Cause:** InfinityFree injection  
**Fix:** Add CSP meta tag (Solution 1)

### Issue 5: File Not Saving to Database
**Cause:** Database lock or permissions  
**Fix:** 
```bash
chmod 755 storage/db/
chmod 644 storage/db/journal.db
```

---

## Recommended InfinityFree Settings

### File Structure:
```
htdocs/
├── .htaccess          ← CSP headers
├── .user.ini          ← PHP limits
├── config/
│   └── .env           ← Absolute paths
├── storage/
│   ├── uploads/       ← 755 permissions
│   └── db/            ← 755 permissions
└── errors/
    └── 403.php        ← Custom error page
```

### .htaccess (Root):
```apache
# Enable CSP
<IfModule mod_headers.c>
    Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self' data: blob: https:; connect-src 'self' https://*"
</IfModule>

# Custom error pages
ErrorDocument 403 /errors/403.php
ErrorDocument 404 /errors/404.php
ErrorDocument 500 /errors/500.php

# Protect sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files ".encryption_key">
    Order allow,deny
    Deny from all
</Files>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### .user.ini:
```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
file_uploads = On
max_file_uploads = 10
```

---

## ✅ Fix Checklist

- [ ] Add CSP meta tag to all pages
- [ ] Create `.user.ini` with proper limits
- [ ] Set correct file permissions (755)
- [ ] Create custom error pages
- [ ] Test simple upload (`test-upload.php`)
- [ ] Check error logs
- [ ] Verify database is writable
- [ ] Test full upload flow in app

---

## Alternative: Use External Image Hosting

If InfinityFree upload issues persist, consider:
- **Cloudinary** (free tier)
- **Imgur API** (free)
- **Amazon S3** (free tier)

This bypasses InfinityFree's file upload limitations entirely.

---

## Final Notes

InfinityFree's free hosting has limitations:
- Injected scripts (evmAsk.js)
- Strict file size limits
- Slow upload speeds
- Occasional 403 errors

**For production, consider upgrading to:**
- InfinityFree Premium ($6.99/month)
- Cheap shared hosting ($2-5/month)
- VPS hosting ($5-10/month)

These remove the injected scripts and provide better upload support.

---

**Test the fix and let me know which solution works best!** 🚀
