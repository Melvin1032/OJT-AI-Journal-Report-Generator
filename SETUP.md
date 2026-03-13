# OJT AI Journal Report Generator - Setup Guide

A web-based system for generating ISPSC-formatted OJT journal reports with AI-powered enhancements using OpenRouter API.

## 📋 Prerequisites

- **PHP 7.4** or higher
- **SQLite3** extension enabled in PHP
- **cURL** extension enabled in PHP
- **Web Server** (Apache/Nginx) or PHP built-in server
- **OpenRouter API Key** ([Get one here](https://openrouter.ai/))

---

## 🚀 Installation

### 1. Clone or Download the Repository

```bash
git clone https://github.com/your-username/OJT-AI-Journal-Report-Generator.git
cd OJT-AI-Journal-Report-Generator
```

### 2. Create the `.env` File

Copy the example environment file:

```bash
cp .env.example .env
```

### 3. Configure Your API Key

Edit the `.env` file and add your OpenRouter API key:

```env
# Get your API key from https://openrouter.ai/
QWEN_API_KEY=sk-or-your-actual-api-key-here
QWEN_API_ENDPOINT=https://openrouter.ai/api/v1/chat/completions

# Primary AI Models (can be changed to any OpenRouter models)
QWEN_VISION_MODEL=qwen/qwen-2-vl-7b-instruct
QWEN_TEXT_MODEL=qwen/qwen-2.5-72b-instruct

# Fallback AI Models (automatically used if primary fails)
FALLBACK_VISION_MODEL=google/gemini-2.0-flash-exp:free
FALLBACK_TEXT_MODEL=google/gemini-2.0-flash-exp:free

# AI Configuration
AI_MAX_RETRIES=1
AI_TIMEOUT=30

# Database Configuration
DB_PATH=./db/journal.db

# Upload Configuration
UPLOAD_DIR=./uploads/
MAX_FILE_SIZE=5242880
ALLOWED_TYPES=image/jpeg,image/png,image/gif,image/webp
```

### 4. Set Directory Permissions

Ensure the following directories are writable:

```bash
# Windows (run as Administrator if needed)
icacls db /grant Users:(OI)(CI)F
icacls uploads /grant Users:(OI)(CI)F

# Linux/Mac
chmod 755 db uploads
chown www-data:www-data db uploads
```

### 5. Start the Web Server

**Option A: PHP Built-in Server (Development)**

```bash
php -S localhost:8000
```

Then open: http://localhost:8000

**Option B: XAMPP/WAMP**

1. Copy the project folder to `htdocs` (XAMPP) or `www` (WAMP)
2. Start Apache
3. Access via http://localhost/OJT-AI-Journal-Report-Generator

---

## 🔧 Configuration Options

### AI Models

You can customize which AI models to use by editing `.env`:

| Variable | Description | Default |
|----------|-------------|---------|
| `QWEN_VISION_MODEL` | Primary model for image analysis | `qwen/qwen-2-vl-7b-instruct` |
| `QWEN_TEXT_MODEL` | Primary model for text generation | `qwen/qwen-2.5-72b-instruct` |
| `FALLBACK_VISION_MODEL` | Backup model for image analysis | `google/gemini-2.0-flash-exp:free` |
| `FALLBACK_TEXT_MODEL` | Backup model for text generation | `google/gemini-2.0-flash-exp:free` |

**Popular OpenRouter Models:**
- `meta-llama/llama-3-70b-instruct` - Fast, good quality
- `anthropic/claude-3-haiku` - Fast, concise
- `google/gemini-pro-1.5` - Good for long context
- `mistralai/mistral-large` - High quality

### AI Settings

| Variable | Description | Default |
|----------|-------------|---------|
| `AI_MAX_RETRIES` | Number of retry attempts | `1` |
| `AI_TIMEOUT` | Request timeout in seconds | `30` |

### Upload Settings

| Variable | Description | Default |
|----------|-------------|---------|
| `MAX_FILE_SIZE` | Max upload size in bytes | `5242880` (5MB) |
| `ALLOWED_TYPES` | Comma-separated MIME types | `image/jpeg,image/png,image/gif,image/webp` |

---

## 📖 How to Use

### 1. Add OJT Entries

1. Open the application in your browser
2. Click **"Add New Entry"**
3. Fill in:
   - **Title**: Activity name (e.g., "Network Configuration")
   - **Date**: When the activity was performed
   - **Description** (optional): Your notes about the activity
   - **Upload Images**: Add photos of your work
4. Click **Save**

The AI will automatically:
- Analyze uploaded images
- Enhance your description with professional language
- Generate detailed activity descriptions

### 2. View Weekly Report

1. Click **"Weekly Report"** in the navigation
2. View all your entries in a formatted report
3. Click **"Generate Narrative"** to create a weekly summary using AI

### 3. Generate Full OJT Report

1. Click **"Generate OJT Report"** in the navigation
2. The AI will create a complete ISPSC-formatted report with:
   - **Chapter I**: Company Profile & Introduction
   - **Chapter II**: Background & Activities Table
   - **Chapter III**: Conclusion & Recommendations
3. Download as **Word (.docx)** or **PDF**

### 4. Print Reports

- Use the **Print** button in any report view
- Optimized print styles are included
- Save as PDF from the print dialog

---

## 🔍 Testing & Troubleshooting

### Test API Connection

Visit: `http://localhost:8000/api-test.php`

This will show:
- ✅ Database connection status
- ✅ API configuration
- ✅ Primary and fallback models
- ✅ Live API connection test

### Common Issues

**1. "API key not configured" error**

- Check that `.env` file exists
- Verify `QWEN_API_KEY` starts with `sk-or-`
- Ensure no extra spaces in the key

**2. "Database connection failed"**

- Check if `db/` directory exists and is writable
- Verify PHP has SQLite3 extension enabled
- Check `php.ini`: `extension=pdo_sqlite`

**3. Image upload fails**

- Ensure `uploads/` directory exists and is writable
- Check file size doesn't exceed `MAX_FILE_SIZE`
- Verify file type is in `ALLOWED_TYPES`

**4. AI response is slow or times out**

- Increase `AI_TIMEOUT` in `.env` (try 60 seconds)
- Check your internet connection
- Try using different models (some are faster)

**5. Fallback model is being used**

- Check `php_errors.log` or server error log
- Primary model may be unavailable or rate-limited
- Consider switching primary model in `.env`

### Enable Debug Mode

Add to `.env`:

```env
DEBUG_MODE=true
```

Check logs:
- PHP errors: `php_errors.log`
- AI fallback usage: Server error log

---

## 📁 Project Structure

```
OJT-AI-Journal-Report-Generator/
├── .env                 # API credentials (DO NOT COMMIT)
├── .env.example         # Template for .env
├── .gitignore           # Git ignore rules
├── config.php           # Configuration loader
├── index.php            # Main application
├── process.php          # Backend processing & AI functions
├── script.js            # Frontend JavaScript
├── style.css            # Main styles
├── print-styles.css     # Print-specific styles
├── print-report.js      # Print functionality
├── api-test.php         # API testing page
├── SETUP.md            # This file
├── README.md            # Project overview
├── db/
│   └── journal.db       # SQLite database
└── uploads/             # Uploaded images
```

---

## 🔐 Security Notes

### ⚠️ Important

1. **Never commit `.env` file** - It contains your API key
2. **Regenerate API key** if it was ever exposed in git history
3. **Use HTTPS** in production
4. **Restrict access** to the application in production

### Git Setup

The `.gitignore` file is already configured to exclude:
- `.env` (API credentials)
- `db/*.db` (database files)
- `uploads/*` (user uploads)
- `*.log` (log files)

When pushing to GitHub:

```bash
git add .gitignore .env.example
git commit -m "Initial commit"
git push
```

---

## 🆘 Support

### OpenRouter Issues

- **Documentation**: https://openrouter.ai/docs
- **Models**: https://openrouter.ai/models
- **API Keys**: https://openrouter.ai/keys

### Application Issues

1. Check `php_errors.log` for PHP errors
2. Visit `api-test.php` to verify configuration
3. Ensure all prerequisites are met
4. Check browser console for JavaScript errors

---

## 📝 License

This project is provided as-is for educational purposes.

---

## 🎯 Quick Start Checklist

- [ ] Install PHP 7.4+ with SQLite3 and cURL
- [ ] Get OpenRouter API key from https://openrouter.ai/
- [ ] Copy `.env.example` to `.env`
- [ ] Add your API key to `.env`
- [ ] Ensure `db/` and `uploads/` are writable
- [ ] Start web server (`php -S localhost:8000`)
- [ ] Visit http://localhost:8000
- [ ] Test API connection at http://localhost:8000/api-test.php
- [ ] Add your first OJT entry!

---

**Happy OJT Journaling! 📚✨**
