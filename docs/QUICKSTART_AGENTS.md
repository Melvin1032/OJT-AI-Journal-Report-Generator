# AI Agents - Quick Start Guide

## 🚀 Setup (5 minutes)

### Step 1: Get Free API Keys

#### Option A: Groq (Recommended - Fastest)
1. Go to https://console.groq.com
2. Sign up (free)
3. Create API key
4. Copy the key

#### Option B: Google Gemini (Fallback)
1. Go to https://aistudio.google.com/app/apikey
2. Sign in with Google
3. Click "Get API Key"
4. Copy the key

### Step 2: Configure Your `.env` File

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Edit `.env` and add your API key:
```env
# Groq (Recommended)
GROQ_API_KEY=gsk_your_actual_key_here

# Google Gemini (Optional fallback)
GEMINI_API_KEY=your_actual_key_here
```

### Step 3: Start Your Server

```bash
# If using the provided PowerShell script
.\start.ps1

# Or manually start PHP server
php -S localhost:8000
```

### Step 4: Open the Application

Go to: `http://localhost:8000`

---

## 🎯 Using AI Agents

### Method 1: Via UI (Easiest)

1. **Scroll to "AI Agents" section** on the main page
2. **Choose an agent:**
   - 📝 **Narrative Agent** - Weekly reports
   - 📊 **Analysis Agent** - Skills & progress analysis
   - ✅ **Quality Agent** - Check & improve entries
   - 📜 **Portfolio Agent** - Complete OJT report (Recommended!)

3. **Click "Run Agent"**
4. **Wait for results** (10-30 seconds)
5. **View/copy the output**

### Method 2: Via JavaScript

```javascript
// Generate weekly narrative
runAgent('narrative', { type: 'weekly' });

// Analyze your skills
runAgent('analysis', { type: 'skills' });

// Check entry quality
runAgent('quality', { type: 'all_entries' });

// Generate complete report
runAgent('portfolio');
```

### Method 3: Via API

```bash
# Narrative Agent
curl -X POST "http://localhost:8000/src/process.php?action=agent/narrative" \
  -d "type=weekly" \
  -d "csrf_token=YOUR_TOKEN"

# Portfolio Agent
curl -X POST "http://localhost:8000/src/process.php?action=agent/portfolio" \
  -d "csrf_token=YOUR_TOKEN"
```

---

## 📋 Agent Quick Reference

| Agent | Best For | Time | Output |
|-------|----------|------|--------|
| **Portfolio** | Final submission | 30s | Complete 3-chapter report |
| **Narrative** | Weekly reports | 10s | 2-3 paragraph summary |
| **Analysis** | Understanding progress | 15s | Skills & insights |
| **Quality** | Before submission | 15s | Score & improvements |

---

## 💡 Example Workflows

### Workflow 1: Weekly Journal Entry

1. Add your journal entries for the week
2. Run **Narrative Agent** → Get weekly summary
3. Copy narrative to your report

```javascript
runAgent('narrative', { type: 'weekly' });
```

### Workflow 2: Mid-Term Check

1. Run **Analysis Agent** → See skills developed
2. Run **Quality Agent** → Check entry quality
3. Fix any issues identified

```javascript
runAgent('analysis', { type: 'comprehensive' });
runAgent('quality', { type: 'all_entries' });
```

### Workflow 3: Final Report Submission

1. Ensure you have 10+ journal entries
2. Run **Portfolio Agent** → Get complete report
3. Review and download

```javascript
runAgent('portfolio');
```

---

## 🔧 Troubleshooting

### "All AI models failed"

**Fix:** Check your API key in `.env`
```env
GROQ_API_KEY=gsk_correct_key_here
```

### "Rate limit exceeded"

**Fix:** Wait 60 seconds, then try again
- Free tier: 60 requests/minute
- Reduce `AGENT_MAX_STEPS` in config if needed

### "No entries found"

**Fix:** Add journal entries first
1. Fill out the journal entry form
2. Add at least 1 entry
3. Run agent again

### Agent takes too long

**Fix:** 
1. Use Groq API (fastest)
2. Check internet connection
3. Reduce entries or use date range

---

## 📊 What Makes a Good Agent Result?

### ✅ Good Input = Good Output

**Before running agents:**
- ✅ Add detailed journal entries (50+ words)
- ✅ Include titles for each entry
- ✅ Add images (agents can analyze them)
- ✅ Have 5+ entries for best results

**Example good entry:**
```
Title: Developed User Authentication System
Description: Implemented login system using PHP sessions and 
password_hash(). Learned about security best practices like 
SQL injection prevention and CSRF protection. Fixed issues 
with session timeout and implemented remember me functionality.
```

**Example bad entry:**
```
Title: Did stuff
Description: Worked on login
```

---

## 🎓 Tips for Best Results

### 1. Use Portfolio Agent for Final Reports
```javascript
// Generates complete 3-chapter report
runAgent('portfolio');
```

### 2. Chain Agents for Quality
```javascript
// 1. Analyze first
runAgent('analysis');

// 2. Fix issues
runAgent('quality', { type: 'improve' });

// 3. Generate report
runAgent('portfolio');
```

### 3. Save Important Results
- Agent results are not saved automatically
- Copy important outputs to your report
- Download portfolio results immediately

### 4. Use Specific Types
```javascript
// For technical focus
runAgent('narrative', { type: 'technical_focus' });

// For growth reflection
runAgent('narrative', { type: 'growth_focus' });
```

---

## 📖 Next Steps

- Read full documentation: `docs/AI_AGENTS.md`
- View source code: `src/agents/`
- Report issues: https://github.com/Melvin1032/OJT-AI-Journal-Report-Generator/issues

---

## 🆘 Need Help?

1. Check `docs/AI_AGENTS.md` for detailed docs
2. Review `.env.example` for configuration
3. Check browser console for JavaScript errors
4. Check server logs for PHP errors

**Common issues:**
- Missing API key → Check `.env`
- CSRF errors → Refresh page
- Timeout → Use Groq API
- Empty results → Add more entries

---

**Happy Journaling! 📝✨**
