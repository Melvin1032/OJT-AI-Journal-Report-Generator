# 📄 Report Preview Dark Mode Fix

## Problem
When viewing the **OJT Report Preview** (📜 Download Report), switching to dark mode made the text white, but the background stayed white, making the text invisible.

## User Expectation
- ✅ Report preview should **ALWAYS** have **white background** with **black text**
- ✅ Even when the rest of the app is in dark mode
- ✅ Report preview simulates printed paper (always light mode)

---

## Root Cause
The report preview was using CSS variables (`var(--text-primary)`) which changed in dark mode, but the background remained white.

---

## Solution

### Files Modified:
1. ✅ `assets/css/style.css`

### Changes Made:

#### 1. Download Report Preview
**Line:** ~1277

**Before:**
```css
.download-report {
    color: var(--text-primary); /* Changes in dark mode ❌ */
    background: white;
}
```

**After:**
```css
/* Download Report Styles (Preview) - ALWAYS LIGHT MODE */
.download-report {
    font-family: Arial, sans-serif;
    font-size: 12pt;
    line-height: 1.5;
    color: #000 !important; /* Always black text ✅ */
    background: #fff !important; /* Always white background ✅ */
}

.report-page {
    min-height: 11in;
    padding: 1in 1in 1in 1.5in;
    margin-bottom: 2rem;
    background: white !important; /* Always white ✅ */
}
```

---

#### 2. ISPSC Report Preview
**Line:** ~1007

**Before:**
```css
.ispcc-report {
    color: var(--text-primary); /* Changes in dark mode ❌ */
    background: white;
}
```

**After:**
```css
/* ISPSC Report Styles - ALWAYS LIGHT MODE */
.ispcc-report {
    font-family: Arial, sans-serif;
    font-size: 12pt;
    line-height: 1.5;
    color: #000 !important; /* Always black text ✅ */
    background: #fff !important; /* Always white background ✅ */
}

.ispcc-page {
    min-height: 11in;
    padding: 1in;
    margin-bottom: 2rem;
    background: white !important; /* Always white ✅ */
}
```

---

#### 3. Dark Mode Override Rules
**Line:** ~59

Added special rules to force light mode for reports even when dark mode is active:

```css
/* Prevent dark mode from affecting download report preview */
[data-theme="dark"] .download-report,
[data-theme="dark"] .download-report * {
    color: #000 !important;
    background: #fff !important;
}

[data-theme="dark"] .report-page,
[data-theme="dark"] .report-page * {
    background: #fff !important;
    color: #000 !important;
}

/* Prevent dark mode from affecting ISPSC report preview */
[data-theme="dark"] .ispcc-report,
[data-theme="dark"] .ispcc-report * {
    color: #000 !important;
    background: #fff !important;
}

[data-theme="dark"] .ispcc-page,
[data-theme="dark"] .ispcc-page * {
    background: #fff !important;
    color: #000 !important;
}
```

---

## Why Use `!important`?

The `!important` flag is used because:
1. **Override dark mode styles** - Ensures report stays light
2. **Prevent CSS variable inheritance** - Forces specific colors
3. **Guarantee consistency** - Works regardless of theme

This is a **deliberate design choice** because:
- 📄 Reports simulate printed paper (always light)
- 📄 Users expect black text on white background
- 📄 Preview should match the final printed/downloaded output

---

## Test It

### Steps:
1. **Open the app** (localhost)
2. **Click "Download Report"** button
3. **View the report preview**
4. **Toggle dark mode** (sun/moon icon)

### Expected Result:

#### App UI (Dark Mode):
- ✅ Dark background
- ✅ Light text
- ✅ Everything adapts

#### Report Preview (ALWAYS Light Mode):
- ✅ **White background** (doesn't change)
- ✅ **Black text** (doesn't change)
- ✅ Stays light even in dark mode

---

## What's Fixed

| Component | Before | After |
|-----------|--------|-------|
| **Download Report Preview** | Text invisible (white on white) | ✅ Black text on white |
| **ISPSC Report Preview** | Text invisible (white on white) | ✅ Black text on white |
| **Report Pages** | Text invisible (white on white) | ✅ Black text on white |
| **Report Content** | Text invisible (white on white) | ✅ Black text on white |

---

## Design Rationale

### Why Keep Report Preview in Light Mode?

1. **Realism** - Simulates printed paper
2. **Consistency** - Matches downloaded/printed output
3. **Readability** - Black on white is standard for documents
4. **Professional** - Academic reports are traditionally light
5. **User Expectation** - Users expect documents to look like paper

### App UI vs Report Preview

```
┌─────────────────────────────────────────┐
│ App UI (Dark Mode Enabled)              │
│ ┌─────────────────────────────────────┐ │
│ │ Dark Background                     │ │
│ │ Light Text                          │ │
│ │                                     │ │
│ │ ┌─────────────────────────────────┐ │ │
│ │ │ Report Preview (Light Mode)     │ │ │
│ │ │ ┌─────────────────────────────┐ │ │ │
│ │ │ │ White Background (Always)   │ │ │ │
│ │ │ │ Black Text (Always)         │ │ │ │
│ │ │ │                             │ │ │ │
│ │ │ │ OJT REPORT                  │ │ │ │
│ │ │ │ Chapter I...                │ │ │ │
│ │ │ └─────────────────────────────┘ │ │ │
│ │ └─────────────────────────────────┘ │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

---

## Files Modified Summary

| File | Changes | Lines |
|------|---------|-------|
| `assets/css/style.css` | Download report always light | ~1277 |
| `assets/css/style.css` | ISPSC report always light | ~1007 |
| `assets/css/style.css` | Dark mode override rules | ~59 |

**Total:** 3 sections modified, ~30 lines added/changed

---

## ✅ Fix Complete

**Report previews now ALWAYS stay in light mode with black text on white background, even when dark mode is enabled!** 📄✨

### Test Checklist:
- [x] Download Report button opens preview
- [x] Preview has white background
- [x] Preview has black text
- [x] Toggle dark mode
- [x] Preview STAYS white with black text
- [x] Rest of app changes to dark mode
- [x] Preview remains light (as designed)

**Report preview now correctly simulates printed paper!** 🎉
