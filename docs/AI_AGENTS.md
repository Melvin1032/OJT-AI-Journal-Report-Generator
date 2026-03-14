# AI Agents Documentation

## Overview

This OJT Journal Report Generator now includes **AI Agents** - advanced autonomous systems that can plan, reason, and execute multi-step tasks to help you create better reports.

## What's New

### 🤖 AI Agents vs. Regular AI Functions

| Regular AI | AI Agents |
|-----------|-----------|
| Single API call | Multi-step reasoning |
| Fixed output | Plans and decides |
| No tool use | Uses multiple tools |
| No autonomy | Autonomous execution |

## Available Agents

### 1. 📝 Narrative Agent

**Purpose:** Generates intelligent weekly narrative reports with theme analysis.

**Features:**
- Analyzes journal entries for themes and patterns
- Identifies skills developed
- Generates coherent narratives
- Performs quality checks
- Auto-revises based on feedback

**Endpoint:** `src/process.php?action=agent/narrative`

**Usage:**
```javascript
// Via JavaScript
runAgent('narrative', { type: 'weekly' });

// Via API
POST /src/process.php?action=agent/narrative
{
  "type": "weekly",
  "date_range": {
    "start_date": "2024-01-01",
    "end_date": "2024-01-07"
  }
}
```

**Response:**
```json
{
  "success": true,
  "narrative": "This week focused on...",
  "themes": {
    "themes": ["Learning", "Problem-solving"],
    "skills": ["PHP", "Communication"],
    "challenges": ["Debugging"]
  },
  "entry_count": 5,
  "quality_score": 85
}
```

---

### 2. 📊 Analysis Agent

**Purpose:** Performs deep analysis of OJT entries for skills, progress, and patterns.

**Analysis Types:**
- **Skills Analysis** - Extracts technical and soft skills
- **Progress Analysis** - Tracks improvement over time
- **Pattern Recognition** - Identifies recurring themes
- **Gap Analysis** - Finds missing information
- **Comprehensive** - All of the above

**Endpoint:** `src/process.php?action=agent/analysis`

**Usage:**
```javascript
// Comprehensive analysis
runAgent('analysis', { type: 'comprehensive' });

// Skills only
runAgent('analysis', { type: 'skills' });

// Progress tracking
runAgent('analysis', { type: 'progress' });
```

**Response:**
```json
{
  "success": true,
  "analysis_type": "comprehensive",
  "result": {
    "skills": {
      "technical_skills": [
        {"name": "PHP", "evidence": "Developed web app", "proficiency": "intermediate"}
      ],
      "soft_skills": [...],
      "domain_knowledge": [...]
    },
    "progress": {
      "overall_progress": 8,
      "progress_summary": "Showed significant improvement..."
    },
    "summary": "Executive summary..."
  }
}
```

---

### 3. ✅ Quality Agent

**Purpose:** Reviews and suggests improvements for entries.

**Features:**
- Content quality assessment
- Grammar and style checking
- Completeness verification
- Improvement suggestions
- Scoring system (A-F grades)

**Endpoint:** `src/process.php?action=agent/quality`

**Usage:**
```javascript
// Check all entries
runAgent('quality', { type: 'all_entries' });

// Check single entry
runAgent('quality', { type: 'single_entry', entry_id: 5 });

// Improve entries
runAgent('quality', { type: 'improve' });
```

**Response:**
```json
{
  "success": true,
  "check_type": "all_entries",
  "result": {
    "total_entries": 10,
    "average_score": 85,
    "average_grade": "B",
    "problematic_entries": [
      {
        "entry_id": 3,
        "title": "Bug Fixing",
        "score": 60,
        "main_issues": ["Too brief", "No learning outcome"]
      }
    ],
    "overall_assessment": "Good work! A few entries could use improvement."
  }
}
```

---

### 4. 📜 Portfolio Agent (Recommended) ⭐

**Purpose:** Generates complete OJT report with all chapters.

**Features:**
- Multi-step workflow planning
- Content gathering and synthesis
- Chapter generation (I, II, III)
- Quality assurance
- Final compilation

**Endpoint:** `src/process.php?action=agent/portfolio`

**Usage:**
```javascript
// Generate complete portfolio
runAgent('portfolio');
```

**Response:**
```json
{
  "success": true,
  "portfolio": "# OJT INTERNSHIP REPORT\n\n## Chapter I - Company Profile\n...",
  "quality_score": 88,
  "steps_completed": 6
}
```

**Generated Report Structure:**
```
OJT INTERNSHIP REPORT
├── Chapter I - Company Profile
│   ├── Company Background
│   ├── Vision and Mission
│   └── Organizational Structure
├── Chapter II - Internship Activities
│   ├── Overview of Activities
│   ├── Detailed Activities
│   ├── Skills Applied
│   └── Challenges and Solutions
├── Chapter III - Conclusion and Recommendations
│   ├── Conclusion
│   └── Recommendations
└── Appendix: Journal Entries Summary
```

---

## Architecture

### Agent System Components

```
┌─────────────────────────────────────────────────────────┐
│                    BaseAgent                            │
│  - Planning & Reasoning                                 │
│  - Tool Execution                                       │
│  - Memory Management                                    │
│  - AI Communication (Groq, Gemini, OpenRouter)         │
└─────────────────────────────────────────────────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
┌───────▼────────┐ ┌──────▼───────┐ ┌────────▼────────┐
│ NarrativeAgent │ │ AnalysisAgent│ │  QualityAgent   │
└────────────────┘ └──────────────┘ └─────────────────┘
                           │
                  ┌────────▼────────┐
                  │ PortfolioAgent  │
                  │ (Orchestrator)  │
                  └─────────────────┘
```

### Tool System

Agents use **tools** to interact with your data:

| Tool | Description |
|------|-------------|
| `fetchEntries` | Get journal entries from database |
| `getStudentInfo` | Retrieve student information |
| `analyzeImage` | Analyze images with AI vision |
| `enhanceDescription` | Improve descriptions with AI |
| `generateNarrative` | Create narrative from entries |
| `summarizeEntries` | Extract key points |
| `checkCompleteness` | Verify entry completeness |
| `suggestImprovements` | Get improvement suggestions |

---

## API Configuration

### Free AI APIs (Recommended for Agents)

Agents automatically use these free APIs:

1. **Groq** (Primary - Fastest)
   - Currently free during beta
   - Llama 3.3 70B model
   - Get key: https://console.groq.com

2. **Google Gemini** (Fallback)
   - Free tier: 60 req/min, 1500/day
   - Gemini 2.0 Flash
   - Get key: https://aistudio.google.com/app/apikey

3. **OpenRouter** (Final fallback)
   - Your existing configuration
   - Free models available

### Setup

1. Copy `.env.example` to `.env`
2. Add your API keys:
```env
# Groq (Recommended)
GROQ_API_KEY=gsk_your_key_here

# Google Gemini (Fallback)
GEMINI_API_KEY=your_key_here
```

---

## Usage Examples

### Via UI

1. Open the OJT Journal Generator
2. Scroll to "AI Agents" section
3. Click "Run Agent" on desired agent
4. View results in the output panel

### Via JavaScript

```javascript
// Run narrative agent
runAgent('narrative', { type: 'weekly' });

// Run analysis with specific type
runAgent('analysis', { type: 'skills' });

// Run quality check on specific entry
runAgent('quality', { entry_id: 5 });

// Generate complete portfolio
runAgent('portfolio');
```

### Via API

```bash
# Narrative Agent
curl -X POST "http://localhost:8000/src/process.php?action=agent/narrative" \
  -d "type=weekly" \
  -d "csrf_token=your_token"

# Portfolio Agent
curl -X POST "http://localhost:8000/src/process.php?action=agent/portfolio" \
  -d "csrf_token=your_token"
```

---

## Advanced Features

### Agent Planning

Agents create plans before executing:

```php
// BaseAgent creates plan using AI
$plan = $agent->plan("Generate weekly narrative");

// Plan example:
[
  {"tool": "fetchEntries", "reason": "Get journal entries"},
  {"tool": "analyzeThemes", "reason": "Identify themes"},
  {"tool": "generateNarrative", "reason": "Create narrative"},
  {"tool": "qualityCheck", "reason": "Ensure quality"}
]
```

### Memory System

Agents remember context during execution:

```php
$agent->remember('entries', $entries);
$agent->remember('themes', $themes);

// Recall later
$themes = $agent->recall('themes');
```

### Error Recovery

Agents automatically recover from failures:

```php
try {
    $result = $agent->execute($goal);
} catch (Exception $e) {
    // Agent attempts recovery
    $result = $agent->recoverFromFailure($step);
}
```

---

## Best Practices

### 1. Use Portfolio Agent for Complete Reports
```javascript
// Best for final submission
runAgent('portfolio');
```

### 2. Use Analysis Agent for Insights
```javascript
// Understand your progress
runAgent('analysis', { type: 'comprehensive' });
```

### 3. Use Quality Agent Before Submission
```javascript
// Check quality before submitting
runAgent('quality', { type: 'all_entries' });
```

### 4. Chain Agents for Best Results
```javascript
// 1. Analyze entries
runAgent('analysis');

// 2. Fix issues based on analysis
runAgent('quality', { type: 'improve' });

// 3. Generate final report
runAgent('portfolio');
```

---

## Troubleshooting

### Agent Returns Error

**Problem:** "All AI models failed"

**Solution:**
1. Check API keys in `.env`
2. Verify internet connection
3. Try adding fallback API keys

### Slow Response

**Problem:** Agent takes too long

**Solution:**
1. Use Groq API (fastest)
2. Reduce `AGENT_MAX_STEPS` in config
3. Check server logs

### Quality Issues

**Problem:** Generated content is generic

**Solution:**
1. Add more detailed journal entries
2. Include images for analysis
3. Use specific agent types (e.g., `type: 'technical_focus'`)

---

## File Structure

```
src/
├── agents/
│   ├── BaseAgent.php       # Base agent class
│   ├── NarrativeAgent.php  # Narrative generation
│   ├── AnalysisAgent.php   # Entry analysis
│   ├── QualityAgent.php    # Quality checking
│   └── PortfolioAgent.php  # Report orchestration
├── tools/
│   └── ToolRegistry.php    # Tool registry
└── process.php             # Agent endpoints

assets/js/
└── agents.js               # Frontend integration

config/
└── config.php              # Agent configuration
```

---

## Future Enhancements

Planned features:
- [ ] Research Agent (web search for concepts)
- [ ] Study Assistant Agent (explain technical terms)
- [ ] Auto-complete Agent (suggest missing entries)
- [ ] Multi-agent collaboration
- [ ] Custom agent creation UI

---

## Credits

Developed by John Melvin R. Macabeo for the OJT AI Journal Report Generator project.

For issues and contributions: https://github.com/Melvin1032/OJT-AI-Journal-Report-Generator
