<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Document your On-the-Job Training journey with AI-powered enhancements">
    <meta name="csrf-token" content="<?php require_once '../config/config.php'; echo generateCSRFToken(); ?>">
    <title>OJT Journal Report Generator</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/print-styles.css">
    <link rel="stylesheet" href="../assets/css/enhancements.css">
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
        </header>

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
                    <button class="btn btn-outline" id="aiReportBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                        ✨ AI Report
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

            <!-- AI Report Modal -->
            <div class="download-report-modal" id="aiReportModal">
                <div class="download-report-overlay"></div>
                <div class="download-report-container">
                    <div class="download-report-header">
                        <h2>✨ AI-Generated OJT Report</h2>
                        <div class="download-report-actions">
                            <button class="btn btn-outline" id="aiDownloadWordBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                Download Word
                            </button>
                            <button class="btn btn-outline" id="aiDownloadPdfBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                Download PDF
                            </button>
                            <button class="btn btn-primary" id="aiPrintBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 6 2 18 2 18 9"/>
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                    <rect x="6" y="14" width="12" height="8"/>
                                </svg>
                                Print
                            </button>
                            <button class="btn btn-secondary" id="closeAIReportBtn">&times; Close</button>
                        </div>
                    </div>
                    <div class="download-report-content" id="aiReportContent">
                        <!-- AI-generated report content will be loaded here -->
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

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/print-report.js"></script>
</body>
</html>
