/**
 * AI Agents JavaScript
 * Handles interaction with AI Agent backend
 */

// Agent API endpoints
const AGENT_ENDPOINTS = {
    narrative: 'src/process.php?action=agent/narrative',
    analysis: 'src/process.php?action=agent/analysis',
    quality: 'src/process.php?action=agent/quality',
    portfolio: 'src/process.php?action=agent/portfolio',
    improve: 'src/process.php?action=agent/improve-entry'
};

// Current agent request
let currentAgentRequest = null;

/**
 * Run an AI Agent
 * @param {string} agentType - Type of agent to run
 * @param {object} options - Additional options
 */
async function runAgent(agentType, options = {}) {
    const resultContainer = document.getElementById('agentResultContainer');
    const loading = document.getElementById('agentLoading');
    const output = document.getElementById('agentOutput');
    const status = document.getElementById('agentStatus');
    const title = document.getElementById('agentResultTitle');
    
    // Show result container
    resultContainer.classList.add('active');
    
    // Reset output
    output.innerHTML = '';
    loading.style.display = 'block';
    
    // Set title
    const agentNames = {
        narrative: 'Narrative Agent',
        analysis: 'Analysis Agent',
        quality: 'Quality Agent',
        portfolio: 'Portfolio Agent'
    };
    title.textContent = agentNames[agentType] || 'AI Agent';
    
    // Get CSRF token
    const csrfToken = await getCSRFToken();
    
    // Prepare request data
    const requestData = new FormData();
    requestData.append('csrf_token', csrfToken);
    requestData.append('type', options.type || 'default');
    
    if (options.entry_id) {
        requestData.append('entry_id', options.entry_id);
    }
    
    if (options.date_range) {
        requestData.append('date_range', JSON.stringify(options.date_range));
    }
    
    try {
        // Abort previous request if exists
        if (currentAgentRequest) {
            currentAgentRequest.abort();
        }
        
        // Create new abort controller
        const controller = new AbortController();
        currentAgentRequest = controller;
        
        // Update status
        updateAgentStatus('Starting agent...', status);
        
        const response = await fetch(AGENT_ENDPOINTS[agentType], {
            method: 'POST',
            body: requestData,
            signal: controller.signal
        });
        
        const result = await response.json();
        
        // Hide loading
        loading.style.display = 'none';
        
        if (result.success) {
            displayAgentResult(result, agentType);
            showAgentMessage('Agent completed successfully!', 'success');
        } else {
            displayAgentError(result.error || 'Agent failed');
        }
        
    } catch (error) {
        loading.style.display = 'none';
        
        if (error.name === 'AbortError') {
            console.log('Agent request aborted');
            return;
        }
        
        console.error('Agent error:', error);
        displayAgentError('Agent request failed: ' + error.message);
    } finally {
        currentAgentRequest = null;
    }
}

/**
 * Update agent status message
 */
function updateAgentStatus(message, statusElement) {
    if (statusElement) {
        statusElement.textContent = message;
    }
}

/**
 * Display agent result
 */
function displayAgentResult(result, agentType) {
    const output = document.getElementById('agentOutput');
    
    switch (agentType) {
        case 'narrative':
            displayNarrativeResult(result, output);
            break;
        case 'analysis':
            displayAnalysisResult(result, output);
            break;
        case 'quality':
            displayQualityResult(result, output);
            break;
        case 'portfolio':
            displayPortfolioResult(result, output);
            break;
        default:
            output.innerHTML = `<pre>${JSON.stringify(result, null, 2)}</pre>`;
    }
}

/**
 * Display narrative agent result
 */
function displayNarrativeResult(result, output) {
    let html = '<div class="agent-success">✓ Narrative generated successfully!</div>';
    
    if (result.narrative) {
        html += '<div class="narrative-text">';
        html += formatMarkdown(result.narrative);
        html += '</div>';
    }
    
    if (result.themes && result.themes.themes) {
        html += '<h4>Identified Themes</h4><ul>';
        result.themes.themes.forEach(theme => {
            html += `<li>${escapeHtml(theme)}</li>`;
        });
        html += '</ul>';
    }
    
    if (result.entry_count) {
        html += `<p class="agent-meta">Generated from ${result.entry_count} entries</p>`;
    }
    
    output.innerHTML = html;
}

/**
 * Display analysis agent result
 */
function displayAnalysisResult(result, output) {
    let html = '<div class="agent-success">✓ Analysis completed!</div>';
    
    const analysisResult = result.result || {};
    
    // Skills
    if (analysisResult.skills) {
        html += '<h4>Skills Analysis</h4>';
        
        if (analysisResult.skills.technical_skills) {
            html += '<h5>Technical Skills</h5><ul>';
            analysisResult.skills.technical_skills.forEach(skill => {
                html += `<li><strong>${escapeHtml(skill.name)}</strong> - ${escapeHtml(skill.evidence || '')}</li>`;
            });
            html += '</ul>';
        }
        
        if (analysisResult.skills.soft_skills) {
            html += '<h5>Soft Skills</h5><ul>';
            analysisResult.skills.soft_skills.forEach(skill => {
                html += `<li><strong>${escapeHtml(skill.name)}</strong> - ${escapeHtml(skill.evidence || '')}</li>`;
            });
            html += '</ul>';
        }
    }
    
    // Progress
    if (analysisResult.progress) {
        html += '<h4>Progress Analysis</h4>';
        html += `<p>${formatMarkdown(analysisResult.progress.progress_summary || '')}</p>`;
    }
    
    // Summary
    if (analysisResult.summary) {
        html += '<h4>Executive Summary</h4>';
        html += `<div class="summary-text">${formatMarkdown(analysisResult.summary)}</div>`;
    }
    
    output.innerHTML = html;
}

/**
 * Display quality agent result
 */
function displayQualityResult(result, output) {
    let html = '<div class="agent-success">✓ Quality check completed!</div>';
    
    const qualityResult = result.result || {};
    
    // Single entry check
    if (qualityResult.score !== undefined) {
        html += '<div class="quality-score">';
        html += `<h4>Quality Score: ${qualityResult.score}% (Grade: ${qualityResult.grade || 'N/A'})</h4>`;
        html += '</div>';
        
        if (qualityResult.feedback) {
            html += '<h4>Feedback</h4>';
            html += `<div class="feedback-text">${formatMarkdown(qualityResult.feedback)}</div>`;
        }
        
        if (qualityResult.suggestions && qualityResult.suggestions.length > 0) {
            html += '<h4>Suggestions</h4><ul>';
            qualityResult.suggestions.forEach(suggestion => {
                html += `<li>${escapeHtml(suggestion)}</li>`;
            });
            html += '</ul>';
        }
    }
    
    // All entries check
    if (qualityResult.average_score !== undefined) {
        html += '<div class="quality-summary">';
        html += `<p><strong>Total Entries:</strong> ${qualityResult.total_entries || 0}</p>`;
        html += `<p><strong>Average Score:</strong> ${qualityResult.average_score}% (Grade: ${qualityResult.average_grade || 'N/A'})</p>`;
        
        if (qualityResult.problematic_entries && qualityResult.problematic_entries.length > 0) {
            html += '<h4>Entries Needing Improvement</h4><ul>';
            qualityResult.problematic_entries.forEach(entry => {
                html += `<li><strong>${escapeHtml(entry.title)}</strong> - Score: ${entry.score}%`;
                if (entry.main_issues) {
                    html += '<ul>';
                    entry.main_issues.forEach(issue => {
                        html += `<li>${escapeHtml(issue)}</li>`;
                    });
                    html += '</ul>';
                }
                html += '</li>';
            });
            html += '</ul>';
        }
        
        if (qualityResult.overall_assessment) {
            html += `<p class="overall-assessment">${formatMarkdown(qualityResult.overall_assessment)}</p>`;
        }
        html += '</div>';
    }
    
    output.innerHTML = html;
}

/**
 * Display portfolio agent result
 */
function displayPortfolioResult(result, output) {
    let html = '<div class="agent-success">✓ Portfolio generated successfully!</div>';
    
    if (result.portfolio) {
        html += '<div class="portfolio-content">';
        html += formatMarkdown(result.portfolio);
        html += '</div>';
    }
    
    if (result.quality_score) {
        html += `<p class="quality-meta">Quality Score: ${result.quality_score}%</p>`;
    }
    
    if (result.steps_completed) {
        html += `<p class="steps-meta">Steps completed: ${result.steps_completed}</p>`;
    }
    
    // Add download button
    html += '<div class="portfolio-actions" style="margin-top: 1rem;">';
    html += '<button class="btn btn-primary" onclick="downloadPortfolio()">📥 Download Report</button>';
    html += '<button class="btn btn-outline" onclick="printPortfolio()">🖨️ Print Report</button>';
    html += '</div>';
    
    // Store portfolio for download
    window.currentPortfolio = result.portfolio;
    
    output.innerHTML = html;
}

/**
 * Display agent error
 */
function displayAgentError(message) {
    const output = document.getElementById('agentOutput');
    output.innerHTML = `<div class="agent-error">❌ ${escapeHtml(message)}</div>`;
}

/**
 * Show agent message
 */
function showAgentMessage(message, type = 'info') {
    const output = document.getElementById('agentOutput');
    const className = type === 'success' ? 'agent-success' : 'agent-info';
    
    // Don't hide existing content, just show briefly
    console.log(`[${type.toUpperCase()}] ${message}`);
}

/**
 * Close agent result
 */
function closeAgentResult() {
    document.getElementById('agentResultContainer').classList.remove('active');
    
    // Abort current request if exists
    if (currentAgentRequest) {
        currentAgentRequest.abort();
        currentAgentRequest = null;
    }
}

/**
 * Download portfolio
 */
function downloadPortfolio() {
    const content = window.currentPortfolio;
    if (!content) {
        alert('No portfolio to download');
        return;
    }
    
    const blob = new Blob([content], { type: 'text/markdown' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `OJT-Report-${new Date().toISOString().split('T')[0]}.md`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

/**
 * Print portfolio
 */
function printPortfolio() {
    const content = window.currentPortfolio;
    if (!content) {
        alert('No portfolio to print');
        return;
    }
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>OJT Report</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 40px; line-height: 1.6; }
                h1, h2, h3 { color: #333; }
                pre { white-space: pre-wrap; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <pre>${escapeHtml(content)}</pre>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

/**
 * Get CSRF token
 */
async function getCSRFToken() {
    try {
        const response = await fetch('src/process.php?action=getCSRFToken');
        const data = await response.json();
        return data.csrf_token || '';
    } catch (error) {
        console.error('Failed to get CSRF token:', error);
        return '';
    }
}

/**
 * Format markdown-like text to HTML
 */
function formatMarkdown(text) {
    if (!text) return '';
    
    // Escape HTML first
    let html = escapeHtml(text);
    
    // Headers
    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
    
    // Bold
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // Italic
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // Line breaks
    html = html.replace(/\n/g, '<br>');
    
    return html;
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Export functions for global access
window.runAgent = runAgent;
window.closeAgentResult = closeAgentResult;
window.downloadPortfolio = downloadPortfolio;
window.printPortfolio = printPortfolio;
