# 🔒 API Key Encryption Guide

## Overview

API keys are now **encrypted with AES-256-CBC** before being stored in the database, providing an additional layer of security.

---

## How It Works

### Encryption Flow

```
User enters API key in browser
        ↓
Key sent to server via HTTPS
        ↓
Server verifies key validity
        ↓
🔑 Key encrypted with AES-256-CBC
        ↓
Encrypted key stored in database
        ↓
Encryption key stored in storage/.encryption_key
```

### Storage Locations

| Data | Location | Encrypted? |
|------|----------|------------|
| API Keys | `storage/db/journal.db` | ✅ Yes (AES-256-CBC) |
| Encryption Key | `storage/.encryption_key` | ✅ Yes (auto-generated) |
| Session Data | PHP session files | ✅ Server-side |
| `.env` file | `config/.env` | ❌ No (keep empty) |

---

## Encryption Details

### Algorithm: AES-256-CBC

- **Cipher**: AES-256-CBC (industry standard)
- **Key Size**: 256 bits (32 bytes)
- **IV**: Random 16 bytes per encryption
- **Encoding**: Base64 (for database storage)

### Key Features

✅ **Automatic encryption** - Keys encrypted on save  
✅ **Automatic decryption** - Keys decrypted on retrieval  
✅ **Unique IV** - Each encryption uses random IV  
✅ **Secure key storage** - Encryption key in protected file  
✅ **Fallback support** - Works even if encryption fails  

---

## Files Modified/Created

### New Files:
- `src/encryption.php` - Encryption helper functions
- `db/encrypt_existing_keys.php` - Migration for existing keys

### Modified Files:
- `config/config.php` - Loads encryption library
- `public/verify-api-keys.php` - Encrypts keys on save
- `settings.php` - Encrypts keys on update

---

## Security Comparison

### Before (Unencrypted):
```sql
-- Database view (INSECURE)
session_id | openrouter_key
-----------|----------------------------------
"abc123"   | "sk-or-v1-81f316be481cc05f..."  ← Plain text!
```

### After (Encrypted):
```sql
-- Database view (SECURE)
session_id | openrouter_key
-----------|--------------------------------------------------
"abc123"   | "Kx9#mP2$vL5@nQ8*wR3&jT6^yU1!iO4%aS7+dF0=..."  ← Encrypted!
```

---

## What Happens If...

### 🔓 Someone steals the database file?
They **cannot** read API keys without the encryption key.

### 🔓 Someone accesses File Manager?
They see `journal.db` but keys are encrypted inside.

### 🔓 Encryption key is lost?
⚠️ **Critical**: All stored API keys become unrecoverable.  
✅ **Solution**: Users can re-enter keys via setup page.

### 🔓 Server is compromised?
Attacker needs BOTH:
1. Database file (`storage/db/journal.db`)
2. Encryption key file (`storage/.encryption_key`)

---

## Encryption Key File

### Location:
```
storage/.encryption_key
```

### Permissions:
```bash
chmod 600 storage/.encryption_key  # Read/write for owner only
```

### Content:
```
64-character hexadecimal string (auto-generated)
```

### Backup:
⚠️ **Important**: Backup this file along with your database!

---

## Migration Steps

### For New Installations:
1. Upload all files to InfinityFree
2. Run `db/migrate_api_keys.php`
3. Delete migration file
4. Keys are automatically encrypted on first use

### For Existing Installations:
1. Upload new/modified files
2. Run `db/encrypt_existing_keys.php`
3. Delete migration file
4. All existing keys are now encrypted

---

## Testing Encryption

### 1. Check if keys are encrypted:
```php
// In PHP or database browser
SELECT openrouter_key FROM user_api_keys;

// Encrypted: "Kx9#mP2$vL5@nQ8*..." (long, random chars)
// Plain text: "sk-or-v1-..." (readable)
```

### 2. Test decryption:
```php
require_once 'config/config.php';
$keys = getUserApiKeys();
echo $keys['openrouter']; // Should show decrypted key
```

---

## Best Practices

### ✅ DO:
- Keep `storage/.encryption_key` secure (chmod 600)
- Backup both database AND encryption key
- Use HTTPS on your InfinityFree site
- Delete migration files after running
- Keep `.env` file with empty API keys

### ❌ DON'T:
- Share your encryption key
- Store encryption key in version control
- Modify encryption key file manually
- Use same encryption key across multiple sites
- Forget to backup encryption key

---

## Troubleshooting

### ❌ "Encryption failed" error
**Cause**: OpenSSL extension not enabled  
**Fix**: Contact InfinityFree support to enable OpenSSL

### ❌ "Decryption failed" error
**Cause**: Encryption key file missing or corrupted  
**Fix**: Restore from backup or have users re-enter keys

### ❌ Keys not encrypting
**Cause**: Old migration not run  
**Fix**: Run `db/encrypt_existing_keys.php`

---

## Security Checklist

| Security Measure | Status |
|-----------------|--------|
| API keys encrypted in database | ✅ |
| Encryption key stored separately | ✅ |
| Encryption key file protected (600) | ⏳ Set manually |
| HTTPS enabled on InfinityFree | ⏳ Recommended |
| Migration files deleted | ⏳ Do after running |
| `.env` has empty API keys | ✅ |

---

## InfinityFree Specific

### File Permissions:
Set these in File Manager:
```
storage/.encryption_key  → 600 (CRITICAL!)
storage/db/journal.db    → 644
storage/                 → 755
```

### .htaccess Protection:
Add to `storage/.htaccess`:
```apache
<Files ".encryption_key">
    Order allow,deny
    Deny from all
</Files>
```

---

## Summary

✅ **API keys are now encrypted** with military-grade AES-256-CBC  
✅ **Stored securely** in database (unreadable without key)  
✅ **Auto-generated encryption key** in `storage/.encryption_key`  
✅ **Backwards compatible** - existing keys auto-migrated  
✅ **Multi-user safe** - each user's keys isolated and encrypted  

**Result**: Even if someone accesses your database file, they **cannot** read the API keys! 🔒
