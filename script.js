/**
 * OJT Journal Report Generator - Frontend JavaScript
 * Handles form submission, image uploads, and AI-powered enhancements
 */

// DOM Elements
const ojtForm = document.getElementById('ojtForm');
const entryTitle = document.getElementById('entryTitle');
const entryDate = document.getElementById('entryDate');
const entryDescription = document.getElementById('entryDescription');
const uploadArea = document.getElementById('uploadArea');
const imageInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('previewContainer');
const submitBtn = document.getElementById('submitBtn');
const clearBtn = document.getElementById('clearBtn');
const statusMessage = document.getElementById('statusMessage');
const reportGrid = document.getElementById('reportGrid');
const emptyState = document.getElementById('emptyState');
const weekRange = document.getElementById('weekRange');
const entryCount = document.getElementById('entryCount');
const refreshBtn = document.getElementById('refreshBtn');
const narrativeBtn = document.getElementById('narrativeBtn');
const narrativeContainer = document.getElementById('narrativeContainer');
const narrativeContent = document.getElementById('narrativeContent');
const closeNarrativeBtn = document.getElementById('closeNarrativeBtn');
const themeToggle = document.getElementById('themeToggle');

// State
let selectedFiles = [];
let narrativeCache = null;

// Set default date to today
entryDate.valueAsDate = new Date();

// Initialize theme
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
}

// Toggle theme
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initializeTheme();
    initializeEventListeners();
    loadWeeklyReport();
    initializeDownloadReport(); // Initialize download report functions
});

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
    // Form submission
    ojtForm.addEventListener('submit', handleSubmit);

    // Upload area click
    uploadArea.addEventListener('click', (e) => {
        if (e.target !== submitBtn && e.target !== clearBtn && !e.target.closest('.remove-btn')) {
            imageInput.click();
        }
    });

    // File input change
    imageInput.addEventListener('change', handleFileSelect);

    // Drag and drop
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('dragleave', handleDragLeave);
    uploadArea.addEventListener('drop', handleDrop);

    // Clear button
    clearBtn.addEventListener('click', clearForm);

    // Refresh button
    refreshBtn.addEventListener('click', loadWeeklyReport);

    // Narrative button
    narrativeBtn.addEventListener('click', handleGenerateNarrative);

    // Close narrative button
    closeNarrativeBtn.addEventListener('click', () => {
        narrativeContainer.classList.remove('show');
    });

    // Refresh button
    refreshBtn.addEventListener('click', loadWeeklyReport);

    // Theme toggle
    themeToggle.addEventListener('click', toggleTheme);
}

/**
 * Handle form submission
 */
async function handleSubmit(e) {
    e.preventDefault();

    const title = entryTitle.value.trim();
    const description = entryDescription.value.trim();
    const date = entryDate.value;

    // Validate
    if (!title) {
        showStatus('Please enter a title for this entry', 'error');
        return;
    }

    if (!date) {
        showStatus('Please select a date', 'error');
        return;
    }

    if (selectedFiles.length === 0) {
        showStatus('Please upload at least one image', 'error');
        return;
    }

    setLoading(true);
    hideStatus();

    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);
    formData.append('entry_date', date);

    selectedFiles.forEach(file => {
        formData.append('images[]', file);
    });

    try {
        console.log('Submitting form...');
        const response = await fetch('process.php?action=createEntry', {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Response text:', responseText);

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Server returned invalid JSON. Check console for details.');
        }

        if (result.success) {
            showStatus('OJT entry created successfully! AI is enhancing your description.', 'success');
            clearForm();
            narrativeCache = null;
            loadWeeklyReport();
        } else {
            showStatus(result.error || 'Failed to create entry', 'error');
        }
    } catch (error) {
        console.error('Submit error:', error);
        showStatus('Error: ' + error.message, 'error');
    } finally {
        setLoading(false);
    }
}

/**
 * Handle drag over event
 */
function handleDragOver(e) {
    e.preventDefault();
    uploadArea.classList.add('drag-over');
}

/**
 * Handle drag leave event
 */
function handleDragLeave(e) {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
}

/**
 * Handle drop event
 */
function handleDrop(e) {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    processFiles(files);
}

/**
 * Handle file input selection
 */
function handleFileSelect(e) {
    const files = e.target.files;
    processFiles(files);
}

/**
 * Process selected files
 */
function processFiles(files) {
    const validFiles = Array.from(files).filter(file => {
        if (!file.type.startsWith('image/')) {
            showStatus(`${file.name} is not an image file`, 'error');
            return false;
        }
        if (file.size > 5 * 1024 * 1024) {
            showStatus(`${file.name} exceeds 5MB limit`, 'error');
            return false;
        }
        return true;
    });

    selectedFiles = [...selectedFiles, ...validFiles];
    updatePreview();
}

/**
 * Update preview container
 */
function updatePreview() {
    previewContainer.innerHTML = '';

    if (selectedFiles.length === 0) {
        uploadArea.classList.remove('has-files');
        return;
    }

    uploadArea.classList.add('has-files');

    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button class="remove-btn" data-index="${index}">&times;</button>
            `;
            previewContainer.appendChild(previewItem);

            previewItem.querySelector('.remove-btn').addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = parseInt(e.target.dataset.index);
                removeFile(idx);
            });
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Remove a file from selection
 */
function removeFile(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
}

/**
 * Clear form
 */
function clearForm() {
    ojtForm.reset();
    entryDate.valueAsDate = new Date();
    selectedFiles = [];
    imageInput.value = '';
    updatePreview();
    hideStatus();
}

/**
 * Load weekly report
 */
async function loadWeeklyReport() {
    reportGrid.innerHTML = '';
    emptyState.classList.remove('show');
    narrativeContainer.classList.remove('show');

    try {
        const response = await fetch('process.php?action=getWeekly');
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', responseText.substring(0, 500));
            throw new Error('Invalid JSON response from server');
        }

        if (result.success) {
            displayWeeklyReport(result.week);
        } else {
            showStatus(result.error || 'Failed to load weekly report', 'error');
        }
    } catch (error) {
        console.error('Load error:', error);
        showStatus('Error: ' + error.message, 'error');
    }
}

/**
 * Display weekly report entries
 */
function displayWeeklyReport(week) {
    weekRange.textContent = `${week.start} - ${week.end}`;
    entryCount.textContent = `${week.entries.length} entr${week.entries.length !== 1 ? 'ies' : 'y'}`;

    reportGrid.innerHTML = '';

    if (week.entries.length === 0) {
        emptyState.classList.add('show');
        return;
    }

    week.entries.forEach(entry => {
        const card = createEntryCard(entry);
        reportGrid.appendChild(card);
    });
}

/**
 * Create an entry card
 */
function createEntryCard(entry) {
    const card = document.createElement('div');
    card.className = 'ojt-entry-card';

    const entryDate = new Date(entry.entry_date);
    const formattedDate = entryDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Build image gallery
    let galleryHtml = '';
    if (entry.images && entry.images.length > 0) {
        galleryHtml = `<div class="ojt-entry-gallery">
            ${entry.images.map(img => `
                <img src="${img.image_path}" alt="Entry image" data-full="${img.image_path}" title="Click to enlarge">
            `).join('')}
        </div>`;
    }

    // Check if description was AI-enhanced
    const isEnhanced = entry.ai_enhanced_description !== entry.user_description &&
                       entry.ai_enhanced_description !== 'No description available';

    const description = entry.ai_enhanced_description || entry.user_description || 'No description';
    const enhancedClass = isEnhanced ? 'enhanced' : '';
    const currentDescription = entry.ai_enhanced_description || entry.user_description || '';

    card.innerHTML = `
        <div class="ojt-entry-header">
            <h3 class="ojt-entry-title">${escapeHtml(entry.title)}</h3>
            <span class="ojt-entry-date">${formattedDate}</span>
        </div>
        <div class="ojt-entry-body">
            <div class="description-container">
                <p class="ojt-entry-description ${enhancedClass}" id="desc-${entry.id}">${escapeHtml(description)}</p>
                <textarea class="ojt-edit-description" id="edit-desc-${entry.id}" style="display:none;" rows="5">${escapeHtml(currentDescription)}</textarea>
            </div>
            <div class="edit-actions" style="display:none; margin-top: 0.5rem;">
                <button class="btn btn-sm btn-primary" onclick="saveDescription(${entry.id})">Save</button>
                <button class="btn btn-sm btn-secondary" onclick="cancelEdit(${entry.id})">Cancel</button>
            </div>
            ${galleryHtml}
            <div class="ojt-entry-meta">
                <span class="ojt-entry-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                    ${entry.images ? entry.images.length : 0} image${entry.images && entry.images.length !== 1 ? 's' : ''}
                </span>
                <div class="entry-actions">
                    <button class="entry-edit" data-id="${entry.id}" title="Edit description">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                    <button class="entry-delete" data-id="${entry.id}" title="Delete entry">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add delete listener
    card.querySelector('.entry-delete').addEventListener('click', () => {
        deleteEntry(entry.id, card);
    });

    // Add edit listener
    card.querySelector('.entry-edit').addEventListener('click', () => {
        editDescription(entry.id);
    });

    // Add image click listeners for modal
    card.querySelectorAll('.ojt-entry-gallery img').forEach(img => {
        img.addEventListener('click', () => {
            showImageModal(img.dataset.full);
        });
    });

    return card;
}

/**
 * Edit description
 */
function editDescription(id) {
    const descParagraph = document.getElementById(`desc-${id}`);
    const editTextarea = document.getElementById(`edit-desc-${id}`);
    const editActions = descParagraph.parentElement.nextElementSibling;
    
    descParagraph.style.display = 'none';
    editTextarea.style.display = 'block';
    editActions.style.display = 'block';
    editTextarea.focus();
}

/**
 * Save description
 */
async function saveDescription(id) {
    const editTextarea = document.getElementById(`edit-desc-${id}`);
    const newDescription = editTextarea.value.trim();
    
    if (!newDescription) {
        showStatus('Description cannot be empty', 'error');
        return;
    }
    
    try {
        const response = await fetch('process.php?action=updateDescription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id, description: newDescription })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const descParagraph = document.getElementById(`desc-${id}`);
            descParagraph.textContent = newDescription;
            descParagraph.classList.add('enhanced');
            cancelEdit(id);
            showStatus('Description updated successfully!', 'success');
            narrativeCache = null;
        } else {
            showStatus(result.error || 'Failed to update description', 'error');
        }
    } catch (error) {
        showStatus('Network error: ' + error.message, 'error');
    }
}

/**
 * Cancel edit
 */
function cancelEdit(id) {
    const descParagraph = document.getElementById(`desc-${id}`);
    const editTextarea = document.getElementById(`edit-desc-${id}`);
    const editActions = descParagraph.parentElement.nextElementSibling;
    
    descParagraph.style.display = 'block';
    editTextarea.style.display = 'none';
    editActions.style.display = 'none';
}

/**
 * Show image modal
 */
function showImageModal(imageSrc) {
    let modal = document.querySelector('.image-modal');
    
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <button class="close-btn">&times;</button>
            <img src="" alt="Full size image">
        `;
        document.body.appendChild(modal);

        modal.querySelector('.close-btn').addEventListener('click', () => {
            modal.classList.remove('show');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    }

    modal.querySelector('img').src = imageSrc;
    modal.classList.add('show');
}

/**
 * Delete an entry
 */
async function deleteEntry(id, cardElement) {
    if (!confirm('Are you sure you want to delete this entry?')) return;

    try {
        const response = await fetch('process.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (result.success) {
            cardElement.style.opacity = '0';
            cardElement.style.transform = 'scale(0.95)';
            narrativeCache = null;
            setTimeout(() => {
                cardElement.remove();
                const entries = reportGrid.querySelectorAll('.ojt-entry-card');
                entryCount.textContent = `${entries.length} entr${entries.length !== 1 ? 'ies' : 'y'}`;
                
                if (entries.length === 0) {
                    emptyState.classList.add('show');
                }
            }, 200);
        } else {
            showStatus(result.error || 'Delete failed', 'error');
        }
    } catch (error) {
        showStatus('Network error: ' + error.message, 'error');
    }
}

/**
 * Generate narrative report
 */
async function handleGenerateNarrative() {
    narrativeContainer.classList.add('show');
    narrativeContent.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <div class="btn-loader" style="display: inline-block; margin-bottom: 1rem;"></div>
            <p>Generating your weekly narrative report...</p>
            <p style="font-size: 0.85rem; opacity: 0.8;">AI is analyzing your OJT entries</p>
        </div>
    `;

    const currentEntryCount = reportGrid.querySelectorAll('.ojt-entry-card').length;
    if (narrativeCache && narrativeCache.entryCount !== currentEntryCount) {
        narrativeCache = null;
    }

    try {
        const response = await fetch('process.php?action=generateNarrative');
        const result = await response.json();

        if (result.success) {
            narrativeCache = {
                narrative: result.narrative,
                entryCount: currentEntryCount
            };
            displayNarrative(result.narrative);
        } else {
            narrativeContent.innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <p style="color: rgba(255,255,255,0.8);">${result.error || 'Failed to generate narrative'}</p>
                    <p style="font-size: 0.85rem; margin-top: 0.5rem;">Add some OJT entries first, then try again.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Narrative error:', error);
        narrativeContent.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <p>Error: ${error.message}</p>
            </div>
        `;
    }
}

/**
 * Display narrative report
 */
function displayNarrative(narrative) {
    const paragraphs = narrative.split('\n\n').filter(p => p.trim());
    narrativeContent.innerHTML = paragraphs
        .map(p => `<p>${escapeHtml(p)}</p>`)
        .join('');
}

/**
 * Handle print
 */
function handlePrint() {
    document.getElementById('printWeekRange').textContent = weekRange.textContent;
    window.print();
}

/**
 * Show status message
 */
function showStatus(message, type) {
    statusMessage.textContent = message;
    statusMessage.className = `status-message show ${type}`;
    
    if (type === 'success') {
        setTimeout(hideStatus, 5000);
    }
}

/**
 * Hide status message
 */
function hideStatus() {
    statusMessage.className = 'status-message';
}

/**
 * Set loading state
 */
function setLoading(loading) {
    submitBtn.disabled = loading;
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    if (loading) {
        btnText.textContent = 'Creating Entry...';
        btnLoader.classList.remove('hidden');
    } else {
        btnText.textContent = 'Create Entry';
        btnLoader.classList.add('hidden');
    }
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
            <!-- Cover Page -->
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
            <div class="report-page">
                <h2 class="report-toc-title">Table of Content</h2>
                <div class="report-toc">
                    <div class="report-toc-chapter">
                        <h3>Chapter I Company Profile</h3>
                        <ul>
                            <li>Introduction ............................................................................................ 1</li>
                            <li>Duration and time ................................................................................... 2</li>
                            <li>Purpose/Role to the company .................................................................. 3</li>
                        </ul>
                    </div>
                    <div class="report-toc-chapter">
                        <h3>Chapter II Immersion Documentation</h3>
                        <ul>
                            <li>Background of the action plan ............................................................... 4</li>
                            <li>Program of Activities – per day .............................................................. 5</li>
                            <li>Evaluation of Result (4th year only) ....................................................... 8</li>
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
                            <li>Daily Time and Record DTR (4th year only) ........................................... 14</li>
                            <li>Photo Documentation .............................................................................. 15</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Chapter I -->
            <div class="report-page">
                <h2 class="report-chapter-title">Chapter I: Company Profile</h2>
                
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

            <!-- Chapter II -->
            <div class="report-page">
                <h2 class="report-chapter-title">Chapter II: Immersion Documentation</h2>
                
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
                                <td>${escapeHtml(entry.user_description || 'No description')}</td>
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

            <!-- Chapter III -->
            <div class="report-page">
                <h2 class="report-chapter-title">Chapter III: Conclusion and Recommendation</h2>
                
                <h3 class="report-section-title">Conclusion</h3>
                <div class="report-placeholder">
                    <p><em>[Summarize your overall experience and learnings]</em></p>
                </div>

                <h3 class="report-section-title">Recommendation</h3>
                <div class="report-placeholder">
                    <p><em>[Provide suggestions for future OJT students, the company, or the school]</em></p>
                </div>
            </div>

            <!-- Appendix -->
            <div class="report-page">
                <h2 class="report-chapter-title">Appendix</h2>
                
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
                    ${entries.filter(e => e.images && e.images.length > 0).map(entry => `
                        <div class="report-photo-item">
                            <img src="${entry.images[0]}" alt="${escapeHtml(entry.title)}" />
                            <p class="report-photo-caption">Figure: ${escapeHtml(entry.title)}</p>
                            <p class="report-photo-date">${new Date(entry.entry_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}</p>
                        </div>
                    `).join('')}
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
            <div class="print-page">
                <h2 class="print-toc-title">Table of Content</h2>
                <div class="print-toc">
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
            </div>

            <!-- Chapter I -->
            <div class="print-page">
                <h2 class="print-chapter-title">Chapter I: Company Profile</h2>
                
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

            <!-- Chapter II -->
            <div class="print-page">
                <h2 class="print-chapter-title">Chapter II: Immersion Documentation</h2>
                
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

            <!-- Chapter III -->
            <div class="print-page">
                <h2 class="print-chapter-title">Chapter III: Conclusion and Recommendation</h2>
                
                <h3 class="print-section-title">Conclusion</h3>
                <div class="print-placeholder">
                    <p><em>[Summarize your overall experience and learnings]</em></p>
                </div>

                <h3 class="print-section-title">Recommendation</h3>
                <div class="print-placeholder">
                    <p><em>[Provide suggestions for future OJT students, company, school]</em></p>
                </div>
            </div>

            <!-- Appendix -->
            <div class="print-page">
                <h2 class="print-chapter-title">Appendix</h2>
                
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
                    ${entries.map((entry, i) => `
                        <tr>
                            <td style="width: 15%;">Day ${i + 1}<br>${new Date(entry.entry_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                            <td style="width: 35%;">${escapeHtml(entry.title)}</td>
                            <td style="width: 50%;">${escapeHtml(entry.user_description || 'No description')}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>

            <h2>Chapter III: Conclusion and Recommendation</h2>
            <h3>Conclusion</h3>
            <p><em>[Summarize your overall experience and learnings]</em></p>
            <h3>Recommendation</h3>
            <p><em>[Provide suggestions for future OJT students, company, school]</em></p>

            <h2>Appendix: Photo Documentation</h2>
            <div class="photo-grid">
                ${entries.filter(e => e.images && e.images.length > 0).map((entry, idx) => {
                    if (idx % 2 === 0) {
                        const nextEntry = entries.filter(e => e.images && e.images.length > 0)[idx + 1];
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
                }).join('')}
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
