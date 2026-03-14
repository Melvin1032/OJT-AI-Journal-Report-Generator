<?php
session_start();
require_once 'config/config.php';

// Require authentication
requireAuth();

// Redirect to setup if API keys are not configured
if (!isset($_SESSION['api_keys_configured']) || $_SESSION['api_keys_configured'] !== true) {
    // Check if keys exist in database
    if (!hasUserApiKeys()) {
        header('Location: setup.php');
        exit;
    } else {
        // Restore session from database
        $_SESSION['api_keys_configured'] = true;
    }
}

$csrfToken = generateCSRFToken();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Document your On-the-Job Training journey with AI-powered enhancements">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    <title>📔 OJT Journal Report Generator</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="apple-touch-icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/print-styles.css">
    <link rel="stylesheet" href="assets/css/enhancements.css">
</head>
<body>
    <!-- Screen Reader Announcer -->
    <div id="sr-announcer" role="status" aria-live="polite" aria-atomic="true" class="sr-only"></div>

    <div class="container">
        <header class="header">
            <div class="header-content">
                <div>
                    <h1>📔 OJT Journal Report Generator</h1>
                    <p class="subtitle">Document your On-the-Job Training journey</p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <!-- User Info Dropdown -->
                    <div style="position: relative;">
                        <button class="btn btn-outline" id="userMenuBtn" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem;"
                                aria-label="User menu" aria-haspopup="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="opacity: 0.6;">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div id="userMenu" style="position: absolute; right: 0; top: 100%; margin-top: 0.5rem; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--border-radius); box-shadow: var(--shadow-lg); min-width: 200px; display: none; z-index: 1000;">
                            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">
                                <p style="margin: 0; font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></p>
                                <p style="margin: 0.25rem 0 0; font-size: 0.85rem; color: var(--text-secondary);"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
                            </div>
                            <a href="settings.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--text-primary); text-decoration: none; transition: var(--transition);">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                </svg>
                                Settings
                            </a>
                            <button onclick="logout()" style="width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: none; border: none; color: var(--error-color); cursor: pointer; transition: var(--transition); text-align: left;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Logout
                            </button>
                        </div>
                    </div>
                    <a href="dashboards/agents-dashboard.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                        AI Agents Dashboard
                    </a>
                    <a href="settings.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;" title="API Key Settings">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        Settings
                    </a>
                    <button class="theme-toggle" id="themeToggle" title="Toggle dark/light mode" aria-label="Toggle dark mode">
                        <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Student & Company Info Section -->
        <section class="student-info-section">
            <div class="entry-card">
                <h2>👤 Student & Company Information</h2>

                <form id="studentInfoForm" class="ojt-form" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="studentName">Student Name *</label>
                            <input type="text" id="studentName" name="student_name"
                                   placeholder="e.g., Juan Dela Cruz"
                                   required
                                   aria-required="true"
                                   autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="studentRole">Role/Position *</label>
                            <input type="text" id="studentRole" name="student_role"
                                   placeholder="e.g., IT Intern, Web Development Intern"
                                   required
                                   aria-required="true">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="companyName">Company/Office Name *</label>
                            <input type="text" id="companyName" name="company_name"
                                   placeholder="e.g., ABC Corporation"
                                   required
                                   aria-required="true">
                        </div>
                        <div class="form-group">
                            <label for="companyAddress">Company Address</label>
                            <input type="text" id="companyAddress" name="company_address"
                                   placeholder="e.g., Candon City, Ilocos Sur">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="introduction">Introduction</label>
                        <textarea id="introduction" name="introduction" rows="3"
                                  placeholder="Brief introduction about the company and your OJT program..."
                                  aria-describedby="intro-hint"></textarea>
                        <div style="display: flex; justify-content: flex-start; margin-top: 0.5rem;">
                            <button type="button" class="btn btn-primary" id="generateIntroAiBtn" style="padding: 0.4rem 0.875rem; font-size: 0.85rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
                                </svg>
                                Generate with AI
                            </button>
                        </div>
                        <span id="intro-hint" class="form-hint">AI will generate based on your company info</span>
                    </div>

                    <div class="form-group">
                        <label for="purposeRole">Purpose/Role to the Company</label>
                        <textarea id="purposeRole" name="purpose_role" rows="3"
                                  placeholder="Describe your responsibilities and contributions..."
                                  aria-describedby="purpose-hint"></textarea>
                        <div style="display: flex; justify-content: flex-start; margin-top: 0.5rem;">
                            <button type="button" class="btn btn-primary" id="generatePurposeAiBtn" style="padding: 0.4rem 0.875rem; font-size: 0.85rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
                                </svg>
                                Generate with AI
                            </button>
                        </div>
                        <span id="purpose-hint" class="form-hint">AI will generate based on your role and activities</span>
                    </div>

                    <div class="form-group">
                        <label for="conclusion">Conclusion</label>
                        <textarea id="conclusion" name="conclusion" rows="3"
                                  placeholder="Summarize your learnings and growth..."
                                  aria-describedby="conclusion-hint"></textarea>
                        <div style="display: flex; justify-content: flex-start; margin-top: 0.5rem;">
                            <button type="button" class="btn btn-primary" id="generateConclusionAiBtn" style="padding: 0.4rem 0.875rem; font-size: 0.85rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
                                </svg>
                                Generate with AI
                            </button>
                        </div>
                        <span id="conclusion-hint" class="form-hint">AI will generate based on your OJT entries</span>
                    </div>

                    <div class="form-group">
                        <label for="recommendations">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" rows="3"
                                  placeholder="Suggestions for future OJT students, company, and school..."
                                  aria-describedby="recs-hint"></textarea>
                        <div style="display: flex; justify-content: flex-start; margin-top: 0.5rem;">
                            <button type="button" class="btn btn-primary" id="generateRecsAiBtn" style="padding: 0.4rem 0.875rem; font-size: 0.85rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
                                </svg>
                                Generate with AI
                            </button>
                        </div>
                        <span id="recs-hint" class="form-hint">AI will generate based on your OJT entries</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="saveStudentInfoBtn"
                                aria-busy="false">
                            <span class="btn-text">Save Information</span>
                            <span class="btn-loader hidden" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="status-message" id="studentInfoMessage" role="alert" aria-live="polite"></div>
                </form>
            </div>
        </section>

        <!-- OJT Entry Form -->
        <section class="entry-section">
            <div class="entry-card">
                <h2>📝 New OJT Entry</h2>

                <form id="ojtForm" class="ojt-form" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="entryTitle">Entry Title *</label>
                            <input type="text" id="entryTitle" name="title"
                                   placeholder="e.g., Website Development - Day 1"
                                   required
                                   aria-required="true"
                                   aria-describedby="title-hint"
                                   autocomplete="off">
                            <span id="title-hint" class="form-hint">Minimum 3 characters</span>
                        </div>
                        <div class="form-group">
                            <label for="entryDate">Date *</label>
                            <input type="date" id="entryDate" name="entry_date"
                                   required
                                   aria-required="true">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="entryDescription">Your Description</label>
                        <textarea id="entryDescription" name="description" rows="3"
                                  placeholder="Briefly describe what you did today, tasks completed, challenges faced, and skills learned..."
                                  aria-describedby="desc-hint"></textarea>
                        <span id="desc-hint" class="form-hint">AI will enhance this description with analysis from your images</span>
                    </div>

                    <div class="upload-area" id="uploadArea"
                         role="button"
                         tabindex="0"
                         aria-label="Upload images. Press Enter to browse or drag and drop files here"
                         aria-describedby="upload-hint">
                        <input type="file" id="imageInput" accept="image/*" multiple hidden
                               aria-label="Select images">
                        <div class="upload-placeholder">
                            <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p>Drag & drop images here or <span class="browse-link">browse</span></p>
                            <p class="upload-hint">Supports: JPEG, PNG, GIF, WebP (Max 5MB each)</p>
                        </div>
                        <div class="preview-container" id="previewContainer" role="list" aria-label="Image previews"></div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="submitBtn"
                                aria-busy="false">
                            <span class="btn-text">Create Entry</span>
                            <span class="btn-loader hidden" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearBtn">Clear Form</button>
                    </div>

                    <div class="status-message" id="statusMessage" role="alert" aria-live="polite"></div>
                </form>
            </div>
        </section>

        <!-- Weekly Report Section -->
        <section class="report-section">
            <div class="report-header">
                <h2>📊 All OJT Entries</h2>
                <div class="report-actions">
                    <button class="btn btn-outline" id="narrativeBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        Generate Narrative
                    </button>
                    <button class="btn btn-primary" id="downloadReportBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Report
                    </button>
                    <button class="btn btn-outline" id="refreshBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"/>
                            <polyline points="1 20 1 14 7 14"/>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <div class="week-info" id="weekInfo">
                <span class="week-range" id="weekRange">Loading...</span>
                <span class="entry-count" id="entryCount">0 entries</span>
            </div>

            <!-- Narrative Report Section -->
            <div class="narrative-container" id="narrativeContainer">
                <div class="narrative-header">
                    <h3>📝 OJT Narrative Report</h3>
                    <button class="btn btn-sm btn-outline" id="closeNarrativeBtn">&times;</button>
                </div>
                <div class="narrative-content" id="narrativeContent">
                    <!-- AI-generated narrative will appear here -->
                </div>
            </div>

            <!-- Download Report Modal -->
            <div class="download-report-modal" id="downloadReportModal">
                <div class="download-report-overlay"></div>
                <div class="download-report-container">
                    <div class="download-report-header">
                        <h2>📜 OJT Report Preview</h2>
                        <div class="download-report-actions">
                            <button class="btn btn-outline" id="downloadWordBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                Download Word
                            </button>
                            <button class="btn btn-outline" id="downloadPdfBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                Download PDF
                            </button>
                            <button class="btn btn-primary" id="printDownloadBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 6 2 18 2 18 9"/>
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                    <rect x="6" y="14" width="12" height="8"/>
                                </svg>
                                Print
                            </button>
                            <button class="btn btn-secondary" id="closeDownloadBtn">&times; Close</button>
                        </div>
                    </div>
                    <div class="download-report-content" id="downloadReportContent">
                        <!-- Report content will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="confirmation-modal" id="confirmationModal">
                <div class="confirmation-overlay"></div>
                <div class="confirmation-container">
                    <div class="confirmation-icon" id="confirmationIcon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <h3 id="confirmationTitle">Are you sure?</h3>
                    <p id="confirmationMessage">This action cannot be undone.</p>
                    <div class="confirmation-actions" id="confirmationActions">
                        <button class="btn btn-secondary" id="confirmCancelBtn">Cancel</button>
                        <button class="btn btn-danger" id="confirmActionBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div class="report-grid" id="reportGrid">
                <!-- Entries will be loaded here -->
            </div>

            <div class="empty-state" id="emptyState">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <h3>No OJT entries yet</h3>
                <p>Fill out the form above to document your training activities</p>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer style="text-align: center; padding: 2rem 0; color: var(--text-secondary); font-size: 0.9rem; border-top: 1px solid var(--border-color); margin-top: 2rem;">
        <p style="margin-bottom: 0.5rem;">
            <strong>✨ AI-Powered OJT Journal Report Generator</strong>
        </p>
        <p style="margin: 0;">
            Developed by 
            <a href="https://github.com/Melvin1032" target="_blank" rel="noopener noreferrer" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                John Melvin R. Macabeo
            </a>
            <span style="margin: 0 0.5rem;">|</span>
            <a href="https://github.com/Melvin1032/OJT-AI-Journal-Report-Generator" target="_blank" rel="noopener noreferrer" style="color: var(--primary-color); text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 0.25rem;">
                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
                </svg>
                View on GitHub
            </a>
        </p>
    </footer>

    <!-- Print Area for Download Report -->
    <div id="printReportArea" class="print-report-area">
        <div class="print-header-section">
            <div class="ispcc-header">
                <strong>ILOCOS SUR POLYTECHNIC STATE COLLEGE</strong><br>
                Candon Campus
            </div>
            <h1 class="ispcc-title">OJT REPORT</h1>
            <p class="ispcc-program">Bachelor of Science in Information Technology</p>
        </div>
        <div id="printReportContent">
            <!-- Content will be injected here -->
        </div>
    </div>

    <!-- Bulk Action Bar -->
    <div class="bulk-action-bar hidden" id="bulkActionBar" role="region" aria-label="Bulk actions">
        <span class="bulk-action-count" id="bulkActionCount">0 selected</span>
        <div class="bulk-action-buttons">
            <button class="btn btn-danger" id="bulkDeleteBtn" aria-label="Delete selected entries">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
                Delete Selected
            </button>
            <button class="btn btn-outline" id="bulkClearBtn" aria-label="Clear selection">Clear</button>
        </div>
    </div>

    <script src="assets/js/utils.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/print-report.js"></script>
    <script src="assets/js/chatbot.js"></script>
    <script>
        // User menu toggle
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenu = document.getElementById('userMenu');

        if (userMenuBtn && userMenu) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.style.display = userMenu.style.display === 'none' ? 'block' : 'none';
            });

            // Close menu when clicking outside
            document.addEventListener('click', () => {
                userMenu.style.display = 'none';
            });

            // Close menu when clicking inside
            userMenu.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // Logout function
        async function logout() {
            if (!confirm('Are you sure you want to logout?')) {
                return;
            }

            try {
                const response = await fetch('public/logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'login.php';
                } else {
                    alert('Logout failed. Please try again.');
                }
            } catch (error) {
                console.error('Logout error:', error);
                alert('Logout failed. Please try again.');
            }
        }
    </script>
</body>
</html>
