# 📁 Project Structure

This document explains the organized folder structure of the OJT AI Journal Report Generator.

---

## 🗂️ Directory Structure

```
OJT-AI-Journal-Report-Generator/
│
├── 📂 config/                    # ⚙️ Configuration Files
│   ├── config.php                # Main configuration (database, uploads, API)
│   └── .env.example              # Environment variables template
│
├── 📂 src/                       # 🔧 Source Code (Backend)
│   ├── security.php              # CSRF, validation, rate limiting, file upload security
│   ├── logger.php                # Logging system (app, error, access logs)
│   └── process.php               # Request handler (CRUD operations, AI calls)
│
├── 📂 public/                    # 🌐 Public-Facing Files
│   ├── index.php                 # Main application entry point
│   ├── api-test.php              # API testing page
│   ├── test.php                  # General testing utilities
│   └── migrate.php               # Database migration script
│
├── 📂 assets/                    # 🎨 Frontend Assets
│   ├── 📂 css/
│   │   ├── style.css             # Main styles
│   │   ├── enhancements.css      # New feature styles (bulk actions, skeleton loaders)
│   │   └── print-styles.css      # Print-specific styles
│   ├── 📂 js/
│   │   ├── script.js             # Main frontend JavaScript
│   │   ├── utils.js              # Utility functions (toast, compression, lazy load)
│   │   └── print-report.js       # Print functionality
│   └── 📂 images/                # Static images (if needed)
│
├── 📂 storage/                   # 💾 Writable Storage (Git-Ignored)
│   ├── 📂 uploads/               # User-uploaded images
│   ├── 📂 cache/                 # Cache files (rate limits, API responses)
│   ├── 📂 logs/                  # Application logs
│   │   ├── app.log               # General application logs
│   │   ├── error.log             # Error logs only
│   │   └── access.log            # Access logs
│   └── 📂 db/                    # Database
│       └── journal.db            # SQLite database
│
├── 📂 docs/                      # 📚 Documentation
│   ├── README.md                 # Project overview
│   ├── SETUP.md                  # Installation and setup guide
│   ├── IMPROVEMENTS.md           # Analysis and recommendations
│   ├── IMPLEMENTATION_COMPLETE.md # Implementation summary
│   ├── GIT-CHEATSHEET.md         # Git workflow reference
│   ├── PROJECT_STRUCTURE.md      # This file
│   ├── OJT_Report.md             # OJT report template
│   └── ojt-report-format.*       # Report format templates
│
├── 📂 .github/                   # 🔗 GitHub Configuration
│   └── CONTRIBUTING.md           # Contribution guidelines
│
├── .env                          # 🔐 Environment Variables (DO NOT COMMIT)
├── .gitignore                    # 🚫 Git Ignore Rules
├── start.ps1                     # 🚀 PowerShell Launch Script
└── stop.ps1                      # ⏹️ PowerShell Stop Script
```

---

## 📋 File Descriptions

### **config/**
| File | Purpose |
|------|---------|
| `config.php` | Loads environment variables, defines constants, database connection |
| `.env.example` | Template for `.env` file (safe to share) |

### **src/**
| File | Purpose |
|------|---------|
| `security.php` | CSRF protection, input validation, rate limiting, file upload security |
| `logger.php` | Comprehensive logging system with auto-rotation |
| `process.php` | Main backend handler for all API requests |

### **public/**
| File | Purpose |
|------|---------|
| `index.php` | Main application interface |
| `api-test.php` | Test API connectivity and configuration |
| `test.php` | General testing utilities |
| `migrate.php` | Database migration and setup |

### **assets/css/**
| File | Purpose |
|------|---------|
| `style.css` | Main application styles, theme support |
| `enhancements.css` | Bulk actions, skeleton loaders, mobile improvements |
| `print-styles.css` | Print-optimized styles |

### **assets/js/**
| File | Purpose |
|------|---------|
| `script.js` | Main frontend logic, form handling |
| `utils.js` | Toast notifications, image compression, lazy loading |
| `print-report.js` | Print functionality for reports |

### **storage/** (Git-Ignored)
| Directory | Purpose |
|-----------|---------|
| `uploads/` | User-uploaded images (organized by entry) |
| `cache/` | Rate limit data, API response cache |
| `logs/` | Application, error, and access logs |
| `db/` | SQLite database file |

### **docs/**
| File | Purpose |
|------|---------|
| `README.md` | Project overview and features |
| `SETUP.md` | Installation, configuration, troubleshooting |
| `IMPROVEMENTS.md` | Detailed analysis and recommendations |
| `IMPLEMENTATION_COMPLETE.md` | Summary of implemented features |
| `GIT-CHEATSHEET.md` | Git commands reference |
| `PROJECT_STRUCTURE.md` | This file - folder structure guide |

---

## 🔐 Security Notes

### Protected Files (Not Committed to Git)
- `.env` - Contains API keys and secrets
- `storage/uploads/*` - User data
- `storage/cache/*` - Cache files
- `storage/logs/*` - Log files
- `storage/db/*.db` - Database files

### Why This Structure is Secure
1. **Separation of Concerns** - Config, source, and public files separated
2. **Protected Storage** - User data in non-public directory
3. **Environment Variables** - Secrets in `.env` (git-ignored)
4. **Clean Public Root** - Only necessary files accessible via web

---

## 🚀 Usage Examples

### Starting the Application
```powershell
# From project root
.\start.ps1
```

### Access Points
- **Main App:** `http://localhost:8000/public/`
- **API Test:** `http://localhost:8000/public/api-test.php`
- **Test Page:** `http://localhost:8000/public/test.php`

### Adding New Features
1. **Backend Logic** → Add to `src/`
2. **Frontend Components** → Add to `assets/`
3. **Configuration** → Add to `config/`
4. **Documentation** → Add to `docs/`

---

## 📝 Path References

### In PHP Files
```php
// From public/index.php
require_once '../config/config.php';
require_once '../src/security.php';

// Asset paths in HTML
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>
```

### In Configuration
```php
// Database path
define('DB_PATH', __DIR__ . '/../storage/db/journal.db');

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../storage/uploads/');

// Log files
private static $logFile = __DIR__ . '/../storage/logs/app.log';
```

---

## 🎯 Benefits of This Structure

| Benefit | Description |
|---------|-------------|
| ✅ **Clarity** | Easy to find any file |
| ✅ **Security** | Sensitive files protected |
| ✅ **Scalability** | Easy to add new features |
| ✅ **Maintainability** | Clear separation of concerns |
| ✅ **Professional** | Follows industry standards |
| ✅ **Testability** | Easy to add tests later |

---

## 🔄 Migration Notes

**Previous Structure (Old)**
```
Root/
  ├── config.php
  ├── process.php
  ├── index.php
  ├── style.css
  ├── script.js
  ├── db/
  ├── uploads/
  └── logs/
```

**New Structure (Current)**
```
Root/
  ├── config/config.php
  ├── src/process.php
  ├── public/index.php
  ├── assets/css/style.css
  ├── assets/js/script.js
  └── storage/db/, storage/uploads/, storage/logs/
```

**Key Changes:**
- All backend code moved to `src/`
- Public files moved to `public/`
- Assets organized in `assets/css/` and `assets/js/`
- Writable data in `storage/`
- Documentation in `docs/`

---

## 📚 Best Practices Followed

1. **PSR-1 (PHP Standards)** - One class per file
2. **PSR-4 (Autoloading)** - Logical file organization
3. **12-Factor App** - Config in environment variables
4. **MVC Pattern** - Separation of concerns
5. **Security First** - Protected storage, git-ignored sensitive files

---

**Last Updated:** March 13, 2026  
**Version:** 2.0 (Reorganized Structure)
