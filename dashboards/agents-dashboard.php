<?php
// Start session for dashboard features - MUST be before any HTML output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Agents Dashboard - OJT Journal Report Generator</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Dashboard Layout */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .dashboard-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .dashboard-header p {
            margin: 0 0 1rem 0;
            opacity: 0.95;
            font-size: 1rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-3px);
        }

        /* Stats Grid */
        .agent-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .quick-actions h3 {
            margin: 0 0 1rem 0;
            color: var(--text-primary);
            font-size: 1.2rem;
            font-weight: 600;
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
        }

        .quick-action-btn {
            background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-secondary));
            border: 2px solid var(--border-color);
            padding: 1.25rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            font-weight: 500;
            color: var(--text-primary);
        }

        .quick-action-btn:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Agents Grid */
        .agents-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .agent-card-large {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 1.75rem;
            border: 2px solid var(--border-color);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .agent-card-large:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .agent-card-large.featured {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--bg-secondary), rgba(79, 70, 229, 0.08));
            box-shadow: var(--shadow-md);
        }

        .agent-card-large.featured::before {
            content: '⭐ RECOMMENDED';
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            font-size: 0.7rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .agent-icon-large {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .agent-card-large h3 {
            margin: 0 0 0.75rem 0;
            color: var(--text-primary);
            font-size: 1.4rem;
            font-weight: 600;
        }

        .agent-card-large p {
            color: var(--text-secondary);
            margin: 0 0 1.25rem 0;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .agent-features {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem 0;
        }

        .agent-features li {
            padding: 0.5rem 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .agent-features li::before {
            content: '✓';
            color: var(--success-color);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .agent-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .agent-actions .btn {
            flex: 1;
            min-width: 120px;
        }

        /* Result Panel */
        .agent-result-panel {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-top: 2rem;
            border: 2px solid var(--border-color);
            display: none;
            box-shadow: var(--shadow-lg);
        }

        .agent-result-panel.active {
            display: block;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .result-header h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.5rem;
        }

        .result-content {
            line-height: 1.8;
            color: var(--text-primary);
        }

        .loading-panel {
            text-align: center;
            padding: 3rem 1rem;
        }

        .spinner-large {
            width: 60px;
            height: 60px;
            border: 5px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .status-text {
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .loading-substatus {
            color: var(--text-muted);
            font-size: 0.9rem;
            animation: pulse 1.5s ease infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Result Content */
        .result-content {
            line-height: 1.8;
            color: var(--text-primary);
            max-height: 600px;
            overflow-y: auto;
            padding-right: 1rem;
        }

        .result-content h4 {
            color: var(--text-primary);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
        }

        .result-content pre {
            background: var(--bg-tertiary);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            overflow-x: auto;
            font-size: 0.85rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .result-content .notification {
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .dashboard-header {
                padding: 1.5rem;
            }

            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            .agents-overview {
                grid-template-columns: 1fr;
            }

            .agent-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>🤖 AI Agents Dashboard</h1>
            <p>Advanced AI-powered tools for intelligent OJT report generation</p>
            <a href="../index.php" class="back-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Journal
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="agent-stats">
            <div class="stat-card">
                <div class="stat-number" id="totalEntries">0</div>
                <div class="stat-label">Journal Entries</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="agentsAvailable">4</div>
                <div class="stat-label">AI Agents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="tasksCompleted">0</div>
                <div class="stat-label">Tasks Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="avgQuality">--</div>
                <div class="stat-label">Avg Quality Score</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>⚡ Quick Actions</h3>
            <div class="quick-actions-grid">
                <div class="quick-action-btn" onclick="quickRun('narrative')">
                    📝<br>Generate Narrative
                </div>
                <div class="quick-action-btn" onclick="quickRun('analysis')">
                    📊<br>Analyze Entries
                </div>
                <div class="quick-action-btn" onclick="quickRun('quality')">
                    ✅<br>Check Quality
                </div>
                <div class="quick-action-btn" onclick="quickRun('portfolio')">
                    📜<br>Full Report
                </div>
            </div>
        </div>

        <!-- Agents Grid -->
        <div class="agents-overview">
            <!-- Narrative Agent -->
            <div class="agent-card-large" data-agent="narrative">
                <div class="agent-icon-large">📝</div>
                <h3>Narrative Agent</h3>
                <p>Generates intelligent weekly narrative reports with theme analysis and automatic quality checks.</p>
                <ul class="agent-features">
                    <li>Theme identification</li>
                    <li>Skills extraction</li>
                    <li>Multi-paragraph narratives</li>
                    <li>Auto-quality check</li>
                </ul>
                <div class="agent-actions">
                    <button class="btn btn-primary" onclick="runAgent('narrative')">Run Agent</button>
                    <button class="btn btn-outline" onclick="showAgentInfo('narrative')">Learn More</button>
                </div>
            </div>

            <!-- Analysis Agent -->
            <div class="agent-card-large" data-agent="analysis">
                <div class="agent-icon-large">📊</div>
                <h3>Analysis Agent</h3>
                <p>Performs deep analysis of your OJT entries to identify skills, progress patterns, and insights.</p>
                <ul class="agent-features">
                    <li>Skills gap analysis</li>
                    <li>Progress tracking</li>
                    <li>Pattern recognition</li>
                    <li>Executive summaries</li>
                </ul>
                <div class="agent-actions">
                    <button class="btn btn-primary" onclick="runAgent('analysis')">Run Agent</button>
                    <button class="btn btn-outline" onclick="showAgentInfo('analysis')">Learn More</button>
                </div>
            </div>

            <!-- Quality Agent -->
            <div class="agent-card-large" data-agent="quality">
                <div class="agent-icon-large">✅</div>
                <h3>Quality Agent</h3>
                <p>Reviews and validates your entries with detailed feedback and improvement suggestions.</p>
                <ul class="agent-features">
                    <li>Quality scoring (A-F)</li>
                    <li>Grammar & style check</li>
                    <li>Improvement suggestions</li>
                    <li>Completeness verification</li>
                </ul>
                <div class="agent-actions">
                    <button class="btn btn-primary" onclick="runAgent('quality')">Run Agent</button>
                    <button class="btn btn-outline" onclick="showAgentInfo('quality')">Learn More</button>
                </div>
            </div>

            <!-- Portfolio Agent (Featured) -->
            <div class="agent-card-large featured" data-agent="portfolio">
                <div class="agent-icon-large">📜</div>
                <h3>Portfolio Agent</h3>
                <p>Generates complete OJT internship reports with all chapters - ready for submission!</p>
                <ul class="agent-features">
                    <li>Chapter I: Company Profile</li>
                    <li>Chapter II: Activities</li>
                    <li>Chapter III: Conclusion</li>
                    <li>Download ready</li>
                </ul>
                <div class="agent-actions">
                    <button class="btn btn-primary" onclick="runAgent('portfolio')">Generate Report</button>
                    <button class="btn btn-outline" onclick="showAgentInfo('portfolio')">Learn More</button>
                </div>
            </div>
        </div>

        <!-- Result Panel -->
        <div class="agent-result-panel" id="resultPanel">
            <div class="result-header">
                <h2 id="resultTitle">Agent Result</h2>
                <button class="btn btn-sm btn-outline" onclick="closeResultPanel()">×</button>
            </div>
            <div id="resultContent">
                <div class="loading-panel" id="loadingPanel">
                    <div class="spinner-large"></div>
                    <p class="status-text" id="statusText">AI Agent is working...</p>
                    <p class="loading-substatus" id="subStatus">This may take 30-60 seconds</p>
                </div>
                <div class="result-content" id="actualResult" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/chatbot.js"></script>
    <script>
        // Dashboard-specific JavaScript
        let currentAgentRequest = null;

        // Load stats on page load
        document.addEventListener('DOMContentLoaded', async () => {
            await loadDashboardStats();
        });

        // Load dashboard statistics
        async function loadDashboardStats() {
            try {
                const response = await fetch('../src/process.php?action=getWeekly');
                const data = await response.json();
                
                if (data.entries) {
                    document.getElementById('totalEntries').textContent = data.entries.length;
                }

                const completed = localStorage.getItem('agentTasksCompleted') || '0';
                document.getElementById('tasksCompleted').textContent = completed;

                const avgQuality = localStorage.getItem('agentAvgQuality');
                if (avgQuality) {
                    document.getElementById('avgQuality').textContent = avgQuality + '%';
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        }

        function quickRun(agentType) {
            runAgent(agentType);
        }

        async function runAgent(agentType) {
            const resultPanel = document.getElementById('resultPanel');
            const loadingPanel = document.getElementById('loadingPanel');
            const actualResult = document.getElementById('actualResult');
            const statusText = document.getElementById('statusText');
            const subStatus = document.getElementById('subStatus');
            const resultTitle = document.getElementById('resultTitle');

            // Show result panel
            resultPanel.classList.add('active');
            resultPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Reset and show loading
            loadingPanel.style.display = 'block';
            actualResult.style.display = 'none';
            
            // Set title
            const agentNames = {
                narrative: '📝 Narrative Agent',
                analysis: '📊 Analysis Agent',
                quality: '✅ Quality Agent',
                portfolio: '📜 Portfolio Agent'
            };
            resultTitle.textContent = agentNames[agentType] || 'AI Agent';

            try {
                // Abort previous request
                if (currentAgentRequest) {
                    currentAgentRequest.abort();
                }

                const controller = new AbortController();
                currentAgentRequest = controller;

                // Update status
                updateStatus(agentType, statusText, subStatus);

                // Get CSRF token
                const csrfToken = await getCSRFToken();

                // Prepare request
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('type', 'default');

                // API endpoints
                const endpoints = {
                    narrative: '../src/process.php?action=agent/narrative',
                    analysis: '../src/process.php?action=agent/analysis',
                    quality: '../src/process.php?action=agent/quality',
                    portfolio: '../src/process.php?action=agent/portfolio'
                };

                // Send request
                const response = await fetch(endpoints[agentType], {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                });

                const result = await response.json();

                // Hide loading, show result
                loadingPanel.style.display = 'none';
                actualResult.style.display = 'block';

                if (result.success) {
                    displayResult(result, agentType);
                    showNotification('Agent completed successfully!', 'success');
                    
                    // Update stats
                    incrementTasksCompleted();
                } else {
                    actualResult.innerHTML = `<div class="notification error" style="padding: 1rem; background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error-color); border-radius: var(--border-radius); color: var(--error-color);">❌ ${result.error || 'Agent failed'}</div>`;
                    if (result.debug) {
                        console.log('Debug info:', result.debug);
                    }
                    showNotification('Agent failed: ' + (result.error || 'Unknown error'), 'error');
                }

            } catch (error) {
                loadingPanel.style.display = 'none';
                actualResult.style.display = 'block';
                
                if (error.name === 'AbortError') {
                    actualResult.innerHTML = '<div class="notification error" style="padding: 1rem;">Request cancelled</div>';
                } else {
                    console.error('Agent error:', error);
                    actualResult.innerHTML = `<div class="notification error" style="padding: 1rem;">❌ ${error.message || 'Unknown error'}</div>`;
                    showNotification('Error: ' + error.message, 'error');
                }
            } finally {
                currentAgentRequest = null;
            }
        }

        function updateStatus(agentType, statusText, subStatus) {
            const messages = {
                narrative: { status: 'Generating narrative...', sub: 'Analyzing entries and identifying themes' },
                analysis: { status: 'Analyzing entries...', sub: 'Extracting skills and patterns' },
                quality: { status: 'Checking quality...', sub: 'Reviewing content and scoring' },
                portfolio: { status: 'Generating portfolio...', sub: 'Creating chapters and compiling report (may take 1-2 minutes)' }
            };
            const msg = messages[agentType] || { status: 'Processing...', sub: 'Please wait' };
            statusText.textContent = msg.status;
            subStatus.textContent = msg.sub;
        }

        function displayResult(result, agentType) {
            const actualResult = document.getElementById('actualResult');
            let html = '';

            switch (agentType) {
                case 'narrative':
                    html = `<div class="notification success" style="padding: 1rem; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success-color); border-radius: var(--border-radius); color: var(--success-color); margin-bottom: 1.5rem;">✓ Narrative Generated Successfully</div>`;
                    if (result.narrative) {
                        html += `<div style="line-height: 1.8; white-space: pre-wrap;">${formatMarkdown(result.narrative)}</div>`;
                    }
                    if (result.themes && result.themes.themes) {
                        html += `<h4 style="margin-top: 1.5rem;">🎯 Themes Identified</h4>`;
                        html += `<div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">`;
                        result.themes.themes.forEach(theme => {
                            html += `<span style="background: var(--primary-color); color: white; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.85rem;">${escapeHtml(theme)}</span>`;
                        });
                        html += `</div>`;
                    }
                    if (result.entry_count) {
                        html += `<p style="margin-top: 1rem; color: var(--text-secondary); font-size: 0.9rem;">📊 Generated from ${result.entry_count} entries</p>`;
                    }
                    break;

                case 'analysis':
                    html = `<div class="notification success" style="padding: 1rem; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success-color); border-radius: var(--border-radius); color: var(--success-color); margin-bottom: 1.5rem;">✓ Analysis Complete</div>`;
                    if (result.result) {
                        if (result.result.summary) {
                            html += `<h4>📊 Executive Summary</h4>`;
                            html += `<div style="white-space: pre-wrap; line-height: 1.8;">${formatMarkdown(result.result.summary)}</div>`;
                        }
                        if (result.result.skills && result.result.skills.technical_skills) {
                            html += `<h4 style="margin-top: 1.5rem;">🛠️ Technical Skills Detected</h4><ul style="line-height: 1.8;">`;
                            result.result.skills.technical_skills.forEach(skill => {
                                html += `<li><strong>${escapeHtml(skill.name)}</strong>${skill.evidence ? ' - ' + escapeHtml(skill.evidence) : ''}</li>`;
                            });
                            html += `</ul>`;
                        }
                        if (result.result.progress) {
                            html += `<h4 style="margin-top: 1.5rem;">📈 Progress</h4>`;
                            html += `<div style="white-space: pre-wrap; line-height: 1.8;">${formatMarkdown(result.result.progress.progress_summary || '')}</div>`;
                        }
                    }
                    break;

                case 'quality':
                    html = `<div class="notification success" style="padding: 1rem; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success-color); border-radius: var(--border-radius); color: var(--success-color); margin-bottom: 1.5rem;">✓ Quality Check Complete</div>`;
                    if (result.result) {
                        if (result.result.average_score !== undefined) {
                            const score = result.result.average_score;
                            const grade = result.result.average_grade || 'N/A';
                            const scoreColor = score >= 80 ? 'var(--success-color)' : score >= 60 ? 'var(--warning-color)' : 'var(--error-color)';
                            html += `<div style="text-align: center; padding: 1.5rem; background: var(--bg-tertiary); border-radius: var(--border-radius); margin-bottom: 1.5rem;">`;
                            html += `<div style="font-size: 3rem; font-weight: 700; color: ${scoreColor};">${score}%</div>`;
                            html += `<div style="font-size: 1.5rem; color: var(--text-secondary);">Grade: ${grade}</div>`;
                            html += `</div>`;
                        }
                        if (result.result.overall_assessment) {
                            html += `<h4>📋 Assessment</h4>`;
                            html += `<div style="white-space: pre-wrap; line-height: 1.8;">${formatMarkdown(result.result.overall_assessment)}</div>`;
                        }
                        if (result.result.problematic_entries && result.result.problematic_entries.length > 0) {
                            html += `<h4 style="margin-top: 1.5rem;">⚠️ Entries Needing Improvement (${result.result.problem_count || result.result.problematic_entries.length})</h4>`;
                            html += `<div style="display: flex; flex-direction: column; gap: 0.75rem;">`;
                            result.result.problematic_entries.forEach((entry, idx) => {
                                html += `<div style="background: var(--bg-tertiary); padding: 1rem; border-radius: var(--border-radius-sm); border-left: 3px solid var(--warning-color);">`;
                                html += `<strong>${escapeHtml(entry.title)}</strong> <span style="color: var(--error-color);">(${entry.score}%)</span>`;
                                if (entry.main_issues && entry.main_issues.length > 0) {
                                    html += `<ul style="margin-top: 0.5rem; margin-bottom: 0;">`;
                                    entry.main_issues.forEach(issue => {
                                        html += `<li style="font-size: 0.9rem;">${escapeHtml(issue)}</li>`;
                                    });
                                    html += `</ul>`;
                                }
                                html += `</div>`;
                            });
                            html += `</div>`;
                        }
                    }
                    break;

                case 'portfolio':
                    html = `<div class="notification success" style="padding: 1rem; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success-color); border-radius: var(--border-radius); color: var(--success-color); margin-bottom: 1.5rem;">✓ Portfolio Generated Successfully</div>`;
                    if (result.quality_score) {
                        html += `<div style="text-align: center; padding: 1rem; background: var(--bg-tertiary); border-radius: var(--border-radius); margin-bottom: 1.5rem;">`;
                        html += `<div style="font-size: 2rem; font-weight: 700; color: var(--success-color);">${result.quality_score}%</div>`;
                        html += `<div style="font-size: 0.9rem; color: var(--text-secondary);">Quality Score</div>`;
                        html += `</div>`;
                    }
                    if (result.portfolio) {
                        html += `<h4>📄 Report Preview</h4>`;
                        html += `<div style="white-space: pre-wrap; line-height: 1.8; background: var(--bg-tertiary); padding: 1rem; border-radius: var(--border-radius-sm); max-height: 400px; overflow-y: auto;">${formatMarkdown(result.portfolio)}</div>`;
                        html += `<div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">`;
                        html += `<button class="btn btn-primary" onclick="downloadPortfolio()" style="flex: 1; min-width: 150px;">📥 Download Report</button>`;
                        html += `<button class="btn btn-outline" onclick="printPortfolio()" style="flex: 1; min-width: 150px;">🖨️ Print Report</button>`;
                        html += `</div>`;
                        window.currentPortfolio = result.portfolio;
                    }
                    if (result.steps_completed) {
                        html += `<p style="margin-top: 1rem; color: var(--text-secondary); font-size: 0.9rem;">⚙️ Steps completed: ${result.steps_completed}</p>`;
                    }
                    break;
            }

            actualResult.innerHTML = html;
        }

        function closeResultPanel() {
            document.getElementById('resultPanel').classList.remove('active');
            if (currentAgentRequest) {
                currentAgentRequest.abort();
                currentAgentRequest = null;
            }
        }

        function showAgentInfo(agentType) {
            const info = {
                narrative: 'The Narrative Agent analyzes your journal entries to identify themes, extract skills, and generate coherent weekly narrative reports.',
                analysis: 'The Analysis Agent performs deep analysis including skills extraction, progress tracking, pattern recognition, and executive summaries.',
                quality: 'The Quality Agent reviews your entries for completeness, grammar, style, and provides scoring (A-F) with improvement suggestions.',
                portfolio: 'The Portfolio Agent generates complete 3-chapter OJT reports including Company Profile, Activities, and Conclusion & Recommendations.'
            };
            alert(info[agentType] || 'AI Agent for OJT report generation');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        function incrementTasksCompleted() {
            const current = parseInt(localStorage.getItem('agentTasksCompleted') || '0');
            localStorage.setItem('agentTasksCompleted', (current + 1).toString());
            document.getElementById('tasksCompleted').textContent = current + 1;
        }

        function downloadPortfolio() {
            const content = window.currentPortfolio;
            if (!content) {
                showNotification('No portfolio to download', 'error');
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
            showNotification('Portfolio downloaded!', 'success');
        }

        function printPortfolio() {
            const content = window.currentPortfolio;
            if (!content) {
                showNotification('No portfolio to print', 'error');
                return;
            }
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`<!DOCTYPE html><html><head><title>OJT Report</title><style>body{font-family:Arial,sans-serif;padding:40px;line-height:1.6;}@media print{body{padding:0;}}</style></head><body><div style="white-space:pre-wrap;">${escapeHtml(content)}</div></body></html>`);
            printWindow.document.close();
            printWindow.print();
            showNotification('Print dialog opened', 'success');
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatMarkdown(text) {
            if (!text) return '';
            let html = escapeHtml(text);
            html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
            html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
            html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\n/g, '<br>');
            return html;
        }

        async function getCSRFToken() {
            try {
                const response = await fetch('../src/process.php?action=getCSRFToken');
                const data = await response.json();
                return data.csrf_token || '';
            } catch (error) {
                console.error('CSRF error:', error);
                return '';
            }
        }
    </script>

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
</body>
</html>
