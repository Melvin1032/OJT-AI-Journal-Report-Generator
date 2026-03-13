/**
 * Print Report Functions for OJT Journal Report Generator
 * Handles Download Report modal, Word/PDF download, and printing
 */

// DOM Elements for Download Report
const downloadReportBtn = document.getElementById('downloadReportBtn');
const downloadReportModal = document.getElementById('downloadReportModal');
const downloadReportContent = document.getElementById('downloadReportContent');
const closeDownloadBtn = document.getElementById('closeDownloadBtn');
const printDownloadBtn = document.getElementById('printDownloadBtn');
const downloadWordBtn = document.getElementById('downloadWordBtn');
const downloadPdfBtn = document.getElementById('downloadPdfBtn');

// DOM Elements for AI Report
const aiReportBtn = document.getElementById('aiReportBtn');
const aiReportModal = document.getElementById('aiReportModal');
const aiReportContent = document.getElementById('aiReportContent');
const closeAIReportBtn = document.getElementById('closeAIReportBtn');
const aiPrintBtn = document.getElementById('aiPrintBtn');
const aiDownloadWordBtn = document.getElementById('aiDownloadWordBtn');
const aiDownloadPdfBtn = document.getElementById('aiDownloadPdfBtn');

// State
let downloadReportCache = null;
let aiReportCache = null;

/**
 * Initialize Download Report event listeners
 */
function initializeDownloadReport() {
    // Download Report button
    downloadReportBtn.addEventListener('click', handleGenerateDownloadReport);

    // Close Download button
    closeDownloadBtn.addEventListener('click', () => {
        downloadReportModal.classList.remove('show');
    });

    // Print Download button
    printDownloadBtn.addEventListener('click', handlePrintDownloadReport);

    // Download Word button
    downloadWordBtn.addEventListener('click', handleDownloadWord);

    // Download PDF button
    downloadPdfBtn.addEventListener('click', handleDownloadPdf);

    // Close modal on overlay click
    downloadReportModal.addEventListener('click', (e) => {
        if (e.target.classList.contains('download-report-overlay')) {
            downloadReportModal.classList.remove('show');
        }
    });

    // AI Report button
    aiReportBtn.addEventListener('click', handleGenerateAIReport);

    // Close AI Report button
    closeAIReportBtn.addEventListener('click', () => {
        aiReportModal.classList.remove('show');
    });

    // AI Print button
    aiPrintBtn.addEventListener('click', handlePrintAIReport);

    // AI Download Word button
    aiDownloadWordBtn.addEventListener('click', handleDownloadAIWord);

    // AI Download PDF button
    aiDownloadPdfBtn.addEventListener('click', handleDownloadAIPdf);

    // Close AI modal on overlay click
    aiReportModal.addEventListener('click', (e) => {
        if (e.target.classList.contains('download-report-overlay')) {
            aiReportModal.classList.remove('show');
        }
    });
}

/**
 * Handle Generate Download Report (non-AI, just entries)
 */
async function handleGenerateDownloadReport() {
    downloadReportModal.classList.add('show');
    downloadReportContent.innerHTML = `
        <div style="text-align: center; padding: 3rem;">
            <div class="btn-loader" style="display: inline-block; margin-bottom: 1rem;"></div>
            <h3>Generating your OJT Report...</h3>
            <p>Loading your OJT entries and photos</p>
        </div>
    `;

    try {
        const response = await fetch('process.php?action=generateDownloadReport');
        const result = await response.json();

        if (result.success) {
            downloadReportCache = result.report;
            displayDownloadReport(result.report);
        } else {
            downloadReportContent.innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <p style="color: var(--error-color);">${result.error || 'Failed to generate report'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Download Report error:', error);
        downloadReportContent.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <p style="color: var(--error-color);">Error: ${error.message}</p>
            </div>
        `;
    }
}

/**
 * Display Download Report (non-AI, just entries following ISPSC format)
 */
function displayDownloadReport(report) {
    const { entries, start_date, end_date, total_days, student_name } = report;

    downloadReportContent.innerHTML = `
        <div class="download-report">
            <!-- Cover Page (No Header/Footer) -->
            <div class="report-page report-cover-page">
                <div class="report-cover-content">
                    <h1 class="report-cover-college">ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1>
                    <h2 class="report-cover-campus">Candon Campus</h2>
                    <div class="report-cover-spacer-large"></div>
                    <h1 class="report-cover-title">OJT REPORT</h1>
                    <p class="report-cover-company">(Name of Company/office assigned)</p>
                    <div class="report-cover-spacer-large"></div>
                    <p class="report-cover-name">${student_name}</p>
                    <p class="report-cover-program">Bachelor of Science in Information Technology</p>
                    <p class="report-cover-sy">S.Y. 2025 - 2026</p>
                </div>
            </div>

            <!-- Table of Contents -->
            <div class="report-page report-content-page">
                <div class="report-header">
                    <div class="report-logos">
                        <span class="logo-box">ISPSC</span>
                        <span class="logo-box">BSIT</span>
                    </div>
                    <div class="report-header-text">
                        <strong>Ilocos Sur Polytechnic State College</strong>
                        Candon Campus
                    </div>
                </div>
                <div class="report-toc">
                    <h2 class="report-toc-title">TABLE OF CONTENTS</h2>
                    <div class="report-toc-chapter">
                        <h3>Chapter I Company Profile</h3>
                        <ul>
                            <li>Introduction ............................................................................................ 1</li>
                            <li>Duration and Time ................................................................................... 2</li>
                            <li>Purpose/Role to the Company .................................................................. 3</li>
                        </ul>
                    </div>
                    <div class="report-toc-chapter">
                        <h3>Chapter II Immersion Documentation</h3>
                        <ul>
                            <li>Background of the Action Plan ............................................................... 4</li>
                            <li>Program of Activities – Per Day .............................................................. 5</li>
                            <li>Evaluation of Result (4th Year Only) ....................................................... 8</li>
                        </ul>
                    </div>
                    <div class="report-toc-chapter">
                        <h3>Chapter III Conclusion and Recommendation</h3>
                        <ul>
                            <li>Conclusion .............................................................................................. 9</li>
                            <li>Recommendation .................................................................................... 10</li>
                        </ul>
                    </div>
                    <div class="report-toc-chapter">
                        <h3>Appendix</h3>
                        <ul>
                            <li>Endorsement Letter ................................................................................ 11</li>
                            <li>Screen of the Project .............................................................................. 12</li>
                            <li>Certificate ................................................................................................ 13</li>
                            <li>Daily Time and Record (DTR) (4th Year Only) ........................................... 14</li>
                            <li>Photo Documentation .............................................................................. 15</li>
                        </ul>
                    </div>
                </div>
                <div class="report-footer">
                    Bachelor of Science in Information Technology
                </div>
            </div>

            <!-- Chapter I -->
            <div class="report-page report-content-page">
                <div class="report-header">
                    <div class="report-logos">
                        <span class="logo-box">ISPSC</span>
                        <span class="logo-box">BSIT</span>
                    </div>
                    <div class="report-header-text">
                        <strong>Ilocos Sur Polytechnic State College</strong>
                        Candon Campus
                    </div>
                </div>
                <div class="report-chapter">
                    <h2 class="report-chapter-title">CHAPTER I: COMPANY PROFILE</h2>
                    
                    <h3 class="report-section-title">Introduction</h3>
                    <div class="report-placeholder">
                        <p><em>[Write the introduction of the company here. Include company name, location, nature of business, and background.]</em></p>
                    </div>

                    <h3 class="report-section-title">Duration and Time</h3>
                    <div class="report-placeholder">
                        <p><em>Start Date: ${start_date}</em></p>
                        <p><em>End Date: ${end_date}</em></p>
                        <p><em>Daily Hours: [Specify your daily OJT hours, e.g., 8:00 AM - 5:00 PM]</em></p>
                    </div>

                    <h3 class="report-section-title">Purpose/Role to the Company</h3>
                    <div class="report-placeholder">
                        <p><em>[Describe your specific role and what you aimed to achieve during the OJT]</em></p>
                    </div>
                </div>
                <div class="report-footer">
                    Bachelor of Science in Information Technology
                </div>
            </div>

            <!-- Chapter II -->
            <div class="report-page report-content-page">
                <div class="report-header">
                    <div class="report-logos">
                        <span class="logo-box">ISPSC</span>
                        <span class="logo-box">BSIT</span>
                    </div>
                    <div class="report-header-text">
                        <strong>Ilocos Sur Polytechnic State College</strong>
                        Candon Campus
                    </div>
                </div>
                <div class="report-chapter">
                    <h2 class="report-chapter-title">CHAPTER II: IMMERSION DOCUMENTATION</h2>
                    
                    <h3 class="report-section-title">Background of the Action Plan</h3>
                    <div class="report-placeholder">
                        <p><em>[Describe the plan you created before starting the immersion]</em></p>
                    </div>

                    <h3 class="report-section-title">Program of Activities – Per Day</h3>
                    <table class="report-activities-table">
                        <thead>
                            <tr>
                                <th>Day/Date</th>
                                <th>Activity</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${entries.map((entry, index) => `
                                <tr>
                                    <td>Day ${index + 1}<br>${new Date(entry.entry_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                                    <td>${escapeHtml(entry.title)}</td>
                                    <td>${escapeHtml(entry.ai_enhanced_description || entry.user_description || 'No description')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <h3 class="report-section-title">Evaluation of Result</h3>
                    <div class="report-placeholder">
                        <p><em>(Include this section ONLY if you are a 4th Year Student)</em></p>
                        <p><em>[Evaluate the outcomes of your immersion]</em></p>
                    </div>
                </div>
                <div class="report-footer">
                    Bachelor of Science in Information Technology
                </div>
            </div>

            <!-- Chapter III -->
            <div class="report-page report-content-page">
                <div class="report-header">
                    <div class="report-logos">
                        <span class="logo-box">ISPSC</span>
                        <span class="logo-box">BSIT</span>
                    </div>
                    <div class="report-header-text">
                        <strong>Ilocos Sur Polytechnic State College</strong>
                        Candon Campus
                    </div>
                </div>
                <div class="report-chapter">
                    <h2 class="report-chapter-title">CHAPTER III: CONCLUSION AND RECOMMENDATION</h2>
                    
                    <h3 class="report-section-title">Conclusion</h3>
                    <div class="report-placeholder">
                        <p><em>[Summarize your overall experience and learnings]</em></p>
                    </div>

                    <h3 class="report-section-title">Recommendation</h3>
                    <div class="report-placeholder">
                        <p><em>[Provide suggestions for future OJT students, the company, or the school]</em></p>
                    </div>
                </div>
                <div class="report-footer">
                    Bachelor of Science in Information Technology
                </div>
            </div>

            <!-- Appendix -->
            <div class="report-page report-content-page">
                <div class="report-header">
                    <div class="report-logos">
                        <span class="logo-box">ISPSC</span>
                        <span class="logo-box">BSIT</span>
                    </div>
                    <div class="report-header-text">
                        <strong>Ilocos Sur Polytechnic State College</strong>
                        Candon Campus
                    </div>
                </div>
                <div class="report-chapter">
                    <h2 class="report-chapter-title">APPENDIX</h2>
                    
                    <h3 class="report-section-title">Endorsement Letter</h3>
                    <div class="report-placeholder">
                        <p><em>[Insert scanned copy or text of the endorsement letter]</em></p>
                    </div>

                    <h3 class="report-section-title">Screen of the Project</h3>
                    <div class="report-placeholder">
                        <p><em>[Insert screenshots of projects worked on]</em></p>
                    </div>

                    <h3 class="report-section-title">Certificate</h3>
                    <div class="report-placeholder">
                        <p><em>[Insert Completion Certificate]</em></p>
                    </div>

                    <h3 class="report-section-title">Daily Time and Record (DTR)</h3>
                    <div class="report-placeholder">
                        <p><em>(Include this section ONLY if you are a 4th Year Student)</em></p>
                        <p><em>[Insert signed DTR forms]</em></p>
                    </div>

                    <h3 class="report-section-title">Photo Documentation</h3>
                    <div class="report-photo-appendix">
                        ${(() => {
                            const photoEntries = entries.filter(e => e.images && e.images.length > 0);
                            return photoEntries.map((entry, idx) => {
                                if (idx % 2 === 0) {
                                    const nextEntry = photoEntries[idx + 1];
                                    return `
                                        <div class="report-photo-row">
                                            <div class="report-photo-item">
                                                <img src="${entry.images[0]}" alt="${escapeHtml(entry.title)}" />
                                                <p class="report-photo-caption">Figure: ${escapeHtml(entry.title)}</p>
                                                <p class="report-photo-date">${new Date(entry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                            </div>
                                            ${nextEntry ? `
                                            <div class="report-photo-item">
                                                <img src="${nextEntry.images[0]}" alt="${escapeHtml(nextEntry.title)}" />
                                                <p class="report-photo-caption">Figure: ${escapeHtml(nextEntry.title)}</p>
                                                <p class="report-photo-date">${new Date(nextEntry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                            </div>
                                            ` : '<div class="report-photo-item"></div>'}
                                        </div>
                                    `;
                                }
                                return '';
                            }).join('');
                        })()}
                    </div>
                </div>
                <div class="report-footer">
                    Bachelor of Science in Information Technology
                </div>
            </div>
        </div>
    `;
}

/**
 * Print Download Report
 */
function handlePrintDownloadReport() {
    const printContent = document.getElementById('printReportContent');

    if (!downloadReportCache) {
        showStatus('No report data available', 'error');
        return;
    }

    const { entries, start_date, end_date, student_name } = downloadReportCache;

    printContent.innerHTML = `
        <!-- Cover Page -->
        <div class="print-page print-cover-page">
            <div class="print-cover-content">
                <h1 class="print-cover-college">ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1>
                <h2 class="print-cover-campus">Candon Campus</h2>
                <div class="print-cover-spacer-large"></div>
                <h1 class="print-cover-title">OJT REPORT</h1>
                <p class="print-cover-company">(Name of Company/office assigned)</p>
                <div class="print-cover-spacer-large"></div>
                <p class="print-cover-name">${student_name}</p>
                <p class="print-cover-program">Bachelor of Science in Information Technology</p>
                <p class="print-cover-sy">S.Y. 2025 - 2026</p>
            </div>
        </div>

        <!-- Table of Contents -->
        <div class="print-page print-content-page">
            <div class="print-header">
                <div class="print-logos">
                    <span class="print-logo-box">ISPSC</span>
                    <span class="print-logo-box">BSIT</span>
                </div>
                <div class="print-header-text">
                    <strong>Ilocos Sur Polytechnic State College</strong>
                    Candon Campus
                </div>
            </div>
            <div class="print-toc">
                <h2 class="print-toc-title">TABLE OF CONTENTS</h2>
                <div class="print-toc-chapter">
                    <h3>Chapter I Company Profile</h3>
                    <ul>
                        <li>Introduction</li>
                        <li>Duration and time</li>
                        <li>Purpose/Role to the company</li>
                    </ul>
                </div>
                <div class="print-toc-chapter">
                    <h3>Chapter II Immersion Documentation</h3>
                    <ul>
                        <li>Background of the action plan</li>
                        <li>Program of Activities – per day</li>
                        <li>Evaluation of Result (4th year only)</li>
                    </ul>
                </div>
                <div class="print-toc-chapter">
                    <h3>Chapter III Conclusion and Recommendation</h3>
                    <ul>
                        <li>Conclusion</li>
                        <li>Recommendation</li>
                    </ul>
                </div>
                <div class="print-toc-chapter">
                    <h3>Appendix</h3>
                    <ul>
                        <li>Endorsement Letter</li>
                        <li>Screen of the Project</li>
                        <li>Certificate</li>
                        <li>Daily Time and Record DTR (4th year only)</li>
                        <li>Photo Documentation</li>
                    </ul>
                </div>
            </div>
            <div class="print-footer">
                Bachelor of Science in Information Technology
            </div>
        </div>

        <!-- Chapter I -->
        <div class="print-page print-content-page">
            <div class="print-header">
                <div class="print-logos">
                    <span class="print-logo-box">ISPSC</span>
                    <span class="print-logo-box">BSIT</span>
                </div>
                <div class="print-header-text">
                    <strong>Ilocos Sur Polytechnic State College</strong>
                    Candon Campus
                </div>
            </div>
            <h2 class="print-chapter-title">CHAPTER I: COMPANY PROFILE</h2>
            
            <h3 class="print-section-title">Introduction</h3>
            <div class="print-placeholder">
                <p><em>[Write the introduction of the company here]</em></p>
            </div>

            <h3 class="print-section-title">Duration and Time</h3>
            <div class="print-placeholder">
                <p><em>Start Date: ${start_date}</em></p>
                <p><em>End Date: ${end_date}</em></p>
                <p><em>Daily Hours: [Specify your daily OJT hours]</em></p>
            </div>

            <h3 class="print-section-title">Purpose/Role to the Company</h3>
            <div class="print-placeholder">
                <p><em>[Describe your specific role and objectives]</em></p>
            </div>
        </div>
        <div class="print-footer">
            Bachelor of Science in Information Technology
        </div>
    </div>

        <!-- Chapter II -->
        <div class="print-page print-content-page">
            <div class="print-header">
                <div class="print-logos">
                    <span class="print-logo-box">ISPSC</span>
                    <span class="print-logo-box">BSIT</span>
                </div>
                <div class="print-header-text">
                    <strong>Ilocos Sur Polytechnic State College</strong>
                    Candon Campus
                </div>
            </div>
            <h2 class="print-chapter-title">CHAPTER II: IMMERSION DOCUMENTATION</h2>

            <h3 class="print-section-title">Background of the Action Plan</h3>
            <div class="print-placeholder">
                <p><em>[Describe the plan you created before starting the immersion]</em></p>
            </div>

            <h3 class="print-section-title">Program of Activities – Per Day</h3>
            <table class="print-activities-table">
                <thead>
                    <tr>
                        <th>Day/Date</th>
                        <th>Activity</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    ${entries.map((entry, index) => `
                        <tr>
                            <td>Day ${index + 1}<br>${new Date(entry.entry_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                            <td>${escapeHtml(entry.title)}</td>
                            <td>${escapeHtml(entry.user_description || 'No description')}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>

            <h3 class="print-section-title">Evaluation of Result</h3>
            <div class="print-placeholder">
                <p><em>(4th Year Only)</em></p>
            </div>
        </div>
        <div class="print-footer">
            Bachelor of Science in Information Technology
        </div>
    </div>

        <!-- Chapter III -->
        <div class="print-page print-content-page">
            <div class="print-header">
                <div class="print-logos">
                    <span class="print-logo-box">ISPSC</span>
                    <span class="print-logo-box">BSIT</span>
                </div>
                <div class="print-header-text">
                    <strong>Ilocos Sur Polytechnic State College</strong>
                    Candon Campus
                </div>
            </div>
            <h2 class="print-chapter-title">CHAPTER III: CONCLUSION AND RECOMMENDATION</h2>

            <h3 class="print-section-title">Conclusion</h3>
            <div class="print-placeholder">
                <p><em>[Summarize your overall experience and learnings]</em></p>
            </div>

            <h3 class="print-section-title">Recommendation</h3>
            <div class="print-placeholder">
                <p><em>[Provide suggestions for future OJT students, company, school]</em></p>
            </div>
        </div>
        <div class="print-footer">
            Bachelor of Science in Information Technology
        </div>
    </div>

        <!-- Appendix -->
        <div class="print-page print-content-page">
            <div class="print-header">
                <div class="print-logos">
                    <span class="print-logo-box">ISPSC</span>
                    <span class="print-logo-box">BSIT</span>
                </div>
                <div class="print-header-text">
                    <strong>Ilocos Sur Polytechnic State College</strong>
                    Candon Campus
                </div>
            </div>
            <h2 class="print-chapter-title">APPENDIX</h2>

            <h3 class="print-section-title">Endorsement Letter</h3>
            <div class="print-placeholder">
                <p><em>[Insert scanned copy]</em></p>
            </div>

            <h3 class="print-section-title">Screen of the Project</h3>
            <div class="print-placeholder">
                <p><em>[Insert screenshots]</em></p>
            </div>

            <h3 class="print-section-title">Certificate</h3>
            <div class="print-placeholder">
                <p><em>[Insert Completion Certificate]</em></p>
            </div>

            <h3 class="print-section-title">Daily Time and Record (DTR)</h3>
            <div class="print-placeholder">
                <p><em>(4th Year Only)</em></p>
            </div>

            <h3 class="print-section-title">Photo Documentation</h3>
            <div class="print-photo-appendix">
                ${(() => {
                    const photoEntries = entries.filter(e => e.images && e.images.length > 0);
                    return photoEntries.map((entry, idx) => {
                        if (idx % 2 === 0) {
                            const nextEntry = photoEntries[idx + 1];
                            return `
                                <div class="print-photo-row">
                                    <div class="print-photo-item">
                                        <img src="${entry.images[0]}" alt="${escapeHtml(entry.title)}" />
                                        <p class="print-photo-caption">Figure: ${escapeHtml(entry.title)}</p>
                                        <p class="print-photo-date">${new Date(entry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                    </div>
                                    ${nextEntry ? `
                                    <div class="print-photo-item">
                                        <img src="${nextEntry.images[0]}" alt="${escapeHtml(nextEntry.title)}" />
                                        <p class="print-photo-caption">Figure: ${escapeHtml(nextEntry.title)}</p>
                                        <p class="print-photo-date">${new Date(nextEntry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                    </div>
                                    ` : '<div class="print-photo-item"></div>'}
                                </div>
                            `;
                        }
                        return '';
                    }).join('');
                })()}
            </div>
        </div>
        <div class="print-footer">
            Bachelor of Science in Information Technology
        </div>
    </div>
    `;

    // Give browser time to render content before printing
    setTimeout(() => {
        window.print();
    }, 100);
}

/**
 * Download as Word document
 */
async function handleDownloadWord() {
    if (!downloadReportCache) {
        showStatus('No report data available', 'error');
        return;
    }

    const { entries, start_date, end_date, student_name } = downloadReportCache;

    // Convert images to base64
    const imageCache = {};
    const imagePromises = [];
    
    entries.forEach(entry => {
        if (entry.images && entry.images.length > 0) {
            entry.images.forEach(imgSrc => {
                if (!imageCache[imgSrc]) {
                    imagePromises.push(
                        fetch(imgSrc)
                            .then(res => res.blob())
                            .then(blob => {
                                return new Promise((resolve) => {
                                    const reader = new FileReader();
                                    reader.onloadend = () => {
                                        imageCache[imgSrc] = reader.result;
                                        resolve(reader.result);
                                    };
                                    reader.readAsDataURL(blob);
                                });
                            })
                            .catch(err => {
                                console.error('Error loading image:', imgSrc, err);
                                imageCache[imgSrc] = '';
                            })
                    );
                }
            });
        }
    });

    // Wait for all images to load
    await Promise.all(imagePromises);

    // Create Word document HTML
    const wordHtml = `
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <style>
                @page { size: letter; margin: 1in 1in 1in 1.5in; }
                body { font-family: Arial; font-size: 12pt; line-height: 1.5; }
                .cover-page { text-align: center; page-break-after: always; }
                .cover-college { font-size: 16pt; font-weight: bold; }
                .cover-campus { font-size: 14pt; font-weight: bold; margin-bottom: 50px; }
                .cover-title { font-size: 18pt; font-weight: bold; }
                .cover-name { font-size: 12pt; text-transform: uppercase; font-weight: bold; }
                h2 { font-size: 14pt; font-weight: bold; text-align: center; margin-top: 30px; }
                h3 { font-size: 12pt; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 11pt; }
                th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; }
                th { background: #f0f0f0; font-weight: bold; }
                .photo-grid { display: table; width: 100%; margin-top: 20px; }
                .photo-row { display: table-row; }
                .photo-item { display: table-cell; width: 50%; padding: 10px; text-align: center; page-break-inside: avoid; }
                .photo-item img { width: 80%; max-width: 200px; max-height: 150px; object-fit: contain; border: 1px solid #999; }
                .photo-caption { text-align: center; font-weight: bold; font-size: 10pt; margin: 8px 0 4px 0; }
                .photo-date { text-align: center; font-size: 9pt; font-style: italic; color: #666; }
                p { margin: 8px 0; }
            </style>
        </head>
        <body>
            <div class="cover-page">
                <h1 class="cover-college">ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1>
                <h2 class="cover-campus">Candon Campus</h2>
                <div style="height: 50px;"></div>
                <h1 class="cover-title">OJT REPORT</h1>
                <p>(Name of Company/office assigned)</p>
                <div style="height: 50px;"></div>
                <p class="cover-name">${student_name}</p>
                <p>Bachelor of Science in Information Technology</p>
                <p>S.Y. 2025 - 2026</p>
            </div>

            <h2>Chapter I: Company Profile</h2>
            <h3>Introduction</h3>
            <p><em>[Write the introduction of the company here]</em></p>
            <h3>Duration and Time</h3>
            <p>Start Date: ${start_date}</p>
            <p>End Date: ${end_date}</p>
            <p><em>Daily Hours: [Specify your daily OJT hours]</em></p>
            <h3>Purpose/Role to the Company</h3>
            <p><em>[Describe your specific role and objectives]</em></p>

            <h2>Chapter II: Immersion Documentation</h2>
            <h3>Background of the Action Plan</h3>
            <p><em>[Describe the plan you created before starting the immersion]</em></p>
            <h3>Program of Activities – Per Day</h3>
            <table>
                <thead>
                    <tr><th>Day/Date</th><th>Activity</th><th>Remarks</th></tr>
                </thead>
                <tbody>
                    ${entries.map((entry, index) => {
                        const date = new Date(entry.entry_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        return `
                        <tr>
                            <td>Day ${index + 1}<br>${date}</td>
                            <td>${escapeHtml(entry.title)}</td>
                            <td>${escapeHtml(entry.ai_enhanced_description || entry.user_description || 'No description')}</td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>

            <h2>Chapter III: Conclusion and Recommendation</h2>
            <h3>Conclusion</h3>
            <p><em>[Summarize your overall experience and learnings]</em></p>
            <h3>Recommendation</h3>
            <p><em>[Provide suggestions for future OJT students, company, school]</em></p>

            <h2>Appendix: Photo Documentation</h2>
            <div class="photo-grid">
                ${(() => {
                    const photoEntries = entries.filter(e => e.images && e.images.length > 0);
                    return photoEntries.map((entry, idx) => {
                        if (idx % 2 === 0) {
                            const nextEntry = photoEntries[idx + 1];
                            return `
                                <div class="photo-row">
                                    <div class="photo-item">
                                        <img src="${imageCache[entry.images[0]] || ''}" alt="${escapeHtml(entry.title)}" />
                                        <p class="photo-caption">Figure: ${escapeHtml(entry.title)}</p>
                                        <p class="photo-date">${new Date(entry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                    </div>
                                    ${nextEntry ? `
                                    <div class="photo-item">
                                        <img src="${imageCache[nextEntry.images[0]] || ''}" alt="${escapeHtml(nextEntry.title)}" />
                                        <p class="photo-caption">Figure: ${escapeHtml(nextEntry.title)}</p>
                                        <p class="photo-date">${new Date(nextEntry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                    </div>
                                    ` : '<div class="photo-item"></div>'}
                                </div>
                            `;
                        }
                        return '';
                    }).join('');
                })()}
            </div>
        </body>
        </html>
    `;

    const blob = new Blob(['\ufeff', wordHtml], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `OJT_Report_${student_name.replace(/\s+/g, '_')}.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

/**
 * Download as PDF using browser print
 */
function handleDownloadPdf() {
    if (!downloadReportCache) {
        showStatus('No report data available', 'error');
        return;
    }

    // Trigger print and user can select "Save as PDF"
    handlePrintDownloadReport();
}

/**
 * Handle Generate AI Report
 */
async function handleGenerateAIReport() {
    aiReportModal.classList.add('show');
    aiReportContent.innerHTML = `
        <div style="text-align: center; padding: 3rem;">
            <div class="btn-loader" style="display: inline-block; margin-bottom: 1rem;"></div>
            <h3>Generating AI-Powered OJT Report...</h3>
            <p>AI is analyzing your entries and generating:</p>
            <ul style="text-align: left; max-width: 400px; margin: 1rem auto;">
                <li>Chapter I: Company Profile (Introduction, Purpose)</li>
                <li>Chapter II: Immersion Documentation (Background, Activities)</li>
                <li>Chapter III: Conclusion & Recommendations</li>
            </ul>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem;">This may take 1-2 minutes</p>
        </div>
    `;

    try {
        const response = await fetch('process.php?action=generateISPSCReport');
        const result = await response.json();

        if (result.success) {
            aiReportCache = result.report;
            displayAIReport(result.report);
        } else {
            aiReportContent.innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <p style="color: var(--error-color);">${result.error || 'Failed to generate AI report'}</p>
                    <p style="font-size: 0.85rem; margin-top: 0.5rem;">Add more detailed OJT entries for better results.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('AI Report error:', error);
        aiReportContent.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <p style="color: var(--error-color);">Error: ${error.message}</p>
            </div>
        `;
    }
}

/**
 * Display AI Report
 */
function displayAIReport(report) {
    const { chapter1, chapter2, chapter3, entries, start_date, end_date, total_days } = report;

    aiReportContent.innerHTML = `
        <div class="download-report">
            <!-- Cover Page -->
            <div class="report-page report-cover-page">
                <div class="report-cover-content">
                    <h1 class="report-cover-college">ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1>
                    <h2 class="report-cover-campus">Candon Campus</h2>
                    <div class="report-cover-spacer-large"></div>
                    <h1 class="report-cover-title">OJT REPORT</h1>
                    <p class="report-cover-company">(Name of Company/office assigned)</p>
                    <div class="report-cover-spacer-large"></div>
                    <p class="report-cover-name">JUAN DELA CRUZ</p>
                    <p class="report-cover-program">Bachelor of Science in Information Technology</p>
                    <p class="report-cover-sy">S.Y. 2025 - 2026</p>
                </div>
            </div>

            <!-- Chapter I -->
            <div class="report-page">
                <h2 class="report-chapter-title">Chapter I: Company Profile</h2>
                <div class="ai-generated-content">${markdownToHtml(chapter1)}</div>
            </div>

            <!-- Chapter II -->
            <div class="report-page">
                <h2 class="report-chapter-title">Chapter II: Immersion Documentation</h2>
                <div class="ai-generated-content">${markdownToHtml(chapter2)}</div>
            </div>

            <!-- Chapter III -->
            <div class="report-page">
                <h2 class="report-chapter-title">Chapter III: Conclusion and Recommendation</h2>
                <div class="ai-generated-content">${markdownToHtml(chapter3)}</div>
            </div>

            <!-- Appendix: Photo Documentation -->
            <div class="report-page">
                <h2 class="report-chapter-title">Appendix: Photo Documentation</h2>
                <div class="report-photo-appendix">
                    ${(() => {
                        const photoEntries = entries.filter(e => e.images && e.images.length > 0);
                        return photoEntries.map((entry, idx) => {
                            if (idx % 2 === 0) {
                                const nextEntry = photoEntries[idx + 1];
                                return `
                                    <div class="report-photo-row">
                                        <div class="report-photo-item">
                                            <img src="${entry.images[0]}" alt="${escapeHtml(entry.title)}" />
                                            <p class="report-photo-caption">Figure: ${escapeHtml(entry.title)}</p>
                                            <p class="report-photo-date">${new Date(entry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                        </div>
                                        ${nextEntry ? `
                                        <div class="report-photo-item">
                                            <img src="${nextEntry.images[0]}" alt="${escapeHtml(nextEntry.title)}" />
                                            <p class="report-photo-caption">Figure: ${escapeHtml(nextEntry.title)}</p>
                                            <p class="report-photo-date">${new Date(nextEntry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                        </div>
                                        ` : '<div class="report-photo-item"></div>'}
                                    </div>
                                `;
                            }
                            return '';
                        }).join('');
                    })()}
                </div>
            </div>
        </div>
    `;
}

/**
 * Simple markdown to HTML converter
 */
function markdownToHtml(markdown) {
    let html = markdown;

    // Convert headers
    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');

    // Convert bold
    html = html.replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>');

    // Convert italic
    html = html.replace(/\*(.*?)\*/gim, '<em>$1</em>');

    // Convert line breaks to paragraphs
    html = html.replace(/\n\n/gim, '</p><p>');
    html = '<p>' + html + '</p>';

    // Convert tables
    html = html.replace(/\|(.+)\|\n\|([-:\s|]+)\|\n((?:\|.+\|\n?)+)/gim, function(match, header, separator, rows) {
        const headers = header.split('|').map(h => `<th>${h.trim()}</th>`).join('');
        const bodyRows = rows.trim().split('\n').map(row => {
            const cells = row.split('|').filter((_, i, arr) => i !== 0 && i !== arr.length - 1);
            return '<tr>' + cells.map(cell => `<td>${cell.trim()}</td>`).join('') + '</tr>';
        }).join('');
        return `<table class="report-activities-table"><thead><tr>${headers}</tr></thead><tbody>${bodyRows}</tbody></table>`;
    });

    return html;
}

/**
 * Print AI Report
 */
function handlePrintAIReport() {
    if (!aiReportCache) {
        showStatus('No AI report data available', 'error');
        return;
    }

    const { chapter1, chapter2, chapter3, entries, start_date, end_date, total_days } = aiReportCache;
    const printContent = document.getElementById('printReportContent');

    printContent.innerHTML = `
        <!-- Cover Page -->
        <div class="print-page print-cover-page">
            <div class="print-cover-content">
                <h1 class="print-cover-college">ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1>
                <h2 class="print-cover-campus">Candon Campus</h2>
                <div class="print-cover-spacer-large"></div>
                <h1 class="print-cover-title">OJT REPORT</h1>
                <p class="print-cover-company">(Name of Company/office assigned)</p>
                <div class="print-cover-spacer-large"></div>
                <p class="print-cover-name">JUAN DELA CRUZ</p>
                <p class="print-cover-program">Bachelor of Science in Information Technology</p>
                <p class="print-cover-sy">S.Y. 2025 - 2026</p>
            </div>
        </div>

        <!-- Chapter I -->
        <div class="print-page">
            <h2 class="print-chapter-title">Chapter I: Company Profile</h2>
            <div class="print-generated-content">${markdownToHtml(chapter1)}</div>
        </div>

        <!-- Chapter II -->
        <div class="print-page">
            <h2 class="print-chapter-title">Chapter II: Immersion Documentation</h2>
            <div class="print-generated-content">${markdownToHtml(chapter2)}</div>
        </div>

        <!-- Chapter III -->
        <div class="print-page">
            <h2 class="print-chapter-title">Chapter III: Conclusion and Recommendation</h2>
            <div class="print-generated-content">${markdownToHtml(chapter3)}</div>
        </div>

        <!-- Appendix: Photo Documentation -->
        <div class="print-page">
            <h2 class="print-chapter-title">Appendix: Photo Documentation</h2>
            <div class="print-photo-appendix">
                ${(() => {
                    const photoEntries = entries.filter(e => e.images && e.images.length > 0);
                    return photoEntries.map((entry, idx) => {
                        if (idx % 2 === 0) {
                            const nextEntry = photoEntries[idx + 1];
                            return `
                                <div class="print-photo-row">
                                    <div class="print-photo-item">
                                        <img src="${entry.images[0]}" alt="${escapeHtml(entry.title)}" />
                                        <p class="print-photo-caption">Figure: ${escapeHtml(entry.title)}</p>
                                        <p class="print-photo-date">${new Date(entry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                    </div>
                                    ${nextEntry ? `
                                    <div class="print-photo-item">
                                        <img src="${nextEntry.images[0]}" alt="${escapeHtml(nextEntry.title)}" />
                                        <p class="print-photo-caption">Figure: ${escapeHtml(nextEntry.title)}</p>
                                        <p class="print-photo-date">${new Date(nextEntry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                    </div>
                                    ` : '<div class="print-photo-item"></div>'}
                                </div>
                            `;
                        }
                        return '';
                    }).join('');
                })()}
            </div>
        </div>
    `;

    setTimeout(() => {
        window.print();
    }, 100);
}

/**
 * Download AI Report as Word
 */
function handleDownloadAIWord() {
    if (!aiReportCache) {
        showStatus('No AI report data available', 'error');
        return;
    }

    const { chapter1, chapter2, chapter3, entries, start_date, end_date, student_name } = aiReportCache;

    const wordHtml = `
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <style>
                @page { size: letter; margin: 1in 1in 1in 1.5in; }
                body { font-family: Arial; font-size: 12pt; line-height: 1.5; }
                .cover-page { text-align: center; page-break-after: always; }
                h2 { font-size: 14pt; font-weight: bold; text-align: center; margin-top: 30px; }
                h3 { font-size: 12pt; font-weight: bold; margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
                th { background: #f0f0f0; }
            </style>
        </head>
        <body>
            <div class="cover-page">
                <h1 class="cover-college">ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1>
                <h2 class="cover-campus">Candon Campus</h2>
                <div style="height: 50px;"></div>
                <h1 class="cover-title">OJT REPORT</h1>
                <p>(Name of Company/office assigned)</p>
                <div style="height: 50px;"></div>
                <p class="cover-name">JUAN DELA CRUZ</p>
                <p>Bachelor of Science in Information Technology</p>
                <p>S.Y. 2025 - 2026</p>
            </div>
            <h2>Chapter I: Company Profile</h2>
            ${markdownToHtml(chapter1)}
            <h2>Chapter II: Immersion Documentation</h2>
            ${markdownToHtml(chapter2)}
            <h2>Chapter III: Conclusion and Recommendation</h2>
            ${markdownToHtml(chapter3)}
        </body>
        </html>
    `;

    const blob = new Blob(['\ufeff', wordHtml], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `OJT_AI_Report.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

/**
 * Download AI Report as PDF
 */
function handleDownloadAIPdf() {
    if (!aiReportCache) {
        showStatus('No AI report data available', 'error');
        return;
    }
    handlePrintAIReport();
}
