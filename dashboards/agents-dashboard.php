<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Agents Dashboard - OJT Journal Report Generator</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Dashboard Specific Styles */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

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
        }

        .dashboard-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 1rem;
            transition: var(--transition);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .agents-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .agent-card-large {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 2rem;
            border: 2px solid var(--border-color);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .agent-card-large:hover {
            border-color: var(--primary-color);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .agent-card-large.featured {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--bg-secondary), rgba(79, 70, 229, 0.05));
        }

        .agent-card-large.featured::before {
            content: '⭐ RECOMMENDED';
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .agent-icon-large {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .agent-card-large h3 {
            margin: 0 0 0.75rem 0;
            color: var(--text-primary);
            font-size: 1.5rem;
        }

        .agent-card-large p {
            color: var(--text-secondary);
            margin: 0 0 1.5rem 0;
            line-height: 1.6;
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
            gap: 0.5rem;
        }

        .agent-features li::before {
            content: '✓';
            color: var(--success-color);
            font-weight: bold;
        }

        .agent-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .agent-result-panel {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-top: 2rem;
            border: 1px solid var(--border-color);
            display: none;
        }

        .agent-result-panel.active {
            display: block;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .result-content {
            line-height: 1.8;
            color: var(--text-primary);
        }

        .result-content h1,
        .result-content h2,
        .result-content h3 {
            color: var(--text-primary);
            margin-top: 1.5rem;
        }

        .result-content pre {
            background: var(--bg-tertiary);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            overflow-x: auto;
            font-size: 0.9rem;
        }

        .loading-panel {
            text-align: center;
            padding: 3rem;
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
        }

        .agent-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-tertiary);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .quick-actions {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
        }

        .quick-actions h3 {
            margin: 0 0 1rem 0;
            color: var(--text-primary);
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .quick-action-btn {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .quick-action-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background: var(--success-color);
        }

        .notification.error {
            background: var(--error-color);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
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
                    <button class="btn btn-primary" onclick="runAgent('narrative')">
                        Run Agent
                    </button>
                    <button class="btn btn-outline" onclick="showAgentInfo('narrative')">
                        Learn More
                    </button>
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
                    <button class="btn btn-primary" onclick="runAgent('analysis')">
                        Run Agent
                    </button>
                    <button class="btn btn-outline" onclick="showAgentInfo('analysis')">
                        Learn More
                    </button>
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
                    <button class="btn btn-primary" onclick="runAgent('quality')">
                        Run Agent
                    </button>
                    <button class="btn btn-outline" onclick="showAgentInfo('quality')">
                        Learn More
                    </button>
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
                    <button class="btn btn-primary" onclick="runAgent('portfolio')">
                        Generate Report
                    </button>
                    <button class="btn btn-outline" onclick="showAgentInfo('portfolio')">
                        Learn More
                    </button>
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
                    <p style="color: var(--text-muted); margin-top: 0.5rem;" id="subStatus">This may take 30-60 seconds</p>
                </div>
                <div class="result-content" id="actualResult"></div>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/agents.js"></script>
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
                // Get entry count
                const response = await fetch('../src/process.php?action=getWeekly');
                const data = await response.json();
                
                if (data.entries) {
                    document.getElementById('totalEntries').textContent = data.entries.length;
                }

                // Load completed tasks from localStorage
                const completed = localStorage.getItem('agentTasksCompleted') || '0';
                document.getElementById('tasksCompleted').textContent = completed;

                // Load average quality if available
                const avgQuality = localStorage.getItem('agentAvgQuality');
                if (avgQuality) {
                    document.getElementById('avgQuality').textContent = avgQuality + '%';
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        }

        // Quick run functions
        function quickRun(agentType) {
            runAgent(agentType);
        }

        // Run agent (overrides agents.js version for dashboard)
        async function runAgent(agentType) {
            const resultPanel = document.getElementById('resultPanel');
            const loadingPanel = document.getElementById('loadingPanel');
            const actualResult = document.getElementById('actualResult');
            const statusText = document.getElementById('statusText');
            const subStatus = document.getElementById('subStatus');
            const resultTitle = document.getElementById('resultTitle');

            // Show result panel
            resultPanel.classList.add('active');
            resultPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Reset
            loadingPanel.style.display = 'block';
            actualResult.innerHTML = '';
            
            // Set title
            const agentNames = {
                narrative: '📝 Narrative Agent',
                analysis: '📊 Analysis Agent',
                quality: '✅ Quality Agent',
                portfolio: '📜 Portfolio Agent'
            };
            resultTitle.textContent = agentNames[agentType] || 'AI Agent';

            // Get CSRF token
            const csrfToken = await getCSRFToken();

            // Prepare request
            const requestData = new FormData();
            requestData.append('csrf_token', csrfToken);
            requestData.append('type', 'default');

            try {
                // Abort previous request
                if (currentAgentRequest) {
                    currentAgentRequest.abort();
                }

                const controller = new AbortController();
                currentAgentRequest = controller;

                // Update status based on agent type
                updateStatus(agentType, statusText, subStatus);

                const endpoints = {
                    narrative: '../src/process.php?action=agent/narrative',
                    analysis: '../src/process.php?action=agent/analysis',
                    quality: '../src/process.php?action=agent/quality',
                    portfolio: '../src/process.php?action=agent/portfolio'
                };

                const response = await fetch(endpoints[agentType], {
                    method: 'POST',
                    body: requestData,
                    signal: controller.signal
                });

                const result = await response.json();

                loadingPanel.style.display = 'none';

                if (result.success) {
                    displayResult(result, agentType);
                    showNotification('Agent completed successfully!', 'success');
                    
                    // Update stats
                    incrementTasksCompleted();
                } else {
                    actualResult.innerHTML = `<div class="notification error">❌ ${result.error || 'Agent failed'}</div>`;
                    showNotification('Agent failed: ' + (result.error || 'Unknown error'), 'error');
                }

            } catch (error) {
                loadingPanel.style.display = 'none';
                
                if (error.name === 'AbortError') {
                    actualResult.innerHTML = '<div class="notification error">Request cancelled</div>';
                } else {
                    actualResult.innerHTML = `<div class="notification error">❌ ${error.message}</div>`;
                    showNotification('Error: ' + error.message, 'error');
                }
            } finally {
                currentAgentRequest = null;
            }
        }

        // Update status message based on agent
        function updateStatus(agentType, statusText, subStatus) {
            const messages = {
                narrative: {
                    status: 'Generating narrative...',
                    sub: 'Analyzing entries and identifying themes'
                },
                analysis: {
                    status: 'Analyzing entries...',
                    sub: 'Extracting skills and patterns'
                },
                quality: {
                    status: 'Checking quality...',
                    sub: 'Reviewing content and scoring'
                },
                portfolio: {
                    status: 'Generating portfolio...',
                    sub: 'Creating chapters and compiling report (may take 1-2 minutes)'
                }
            };

            const msg = messages[agentType] || { status: 'Processing...', sub: 'Please wait' };
            statusText.textContent = msg.status;
            subStatus.textContent = msg.sub;
        }

        // Display result
        function displayResult(result, agentType) {
            const actualResult = document.getElementById('actualResult');
            
            let html = '';

            switch (agentType) {
                case 'narrative':
                    html = `<div class="notification success">✓ Narrative Generated</div>`;
                    html += `<div style="line-height: 1.8;">${formatMarkdown(result.narrative || '')}</div>`;
                    if (result.themes) {
                        html += `<h4>Themes Identified</h4><p>${(result.themes.themes || []).join(', ')}</p>`;
                    }
                    break;

                case 'analysis':
                    html = `<div class="notification success">✓ Analysis Complete</div>`;
                    if (result.result) {
                        html += `<pre>${JSON.stringify(result.result, null, 2)}</pre>`;
                    }
                    break;

                case 'quality':
                    html = `<div class="notification success">✓ Quality Check Complete</div>`;
                    if (result.result) {
                        if (result.result.average_score) {
                            html += `<h3>Quality Score: ${result.result.average_score}% (${result.result.average_grade || 'N/A'})</h3>`;
                        }
                        if (result.result.overall_assessment) {
                            html += `<p>${formatMarkdown(result.result.overall_assessment)}</p>`;
                        }
                    }
                    break;

                case 'portfolio':
                    html = `<div class="notification success">✓ Portfolio Generated</div>`;
                    if (result.portfolio) {
                        html += `<div style="white-space: pre-wrap;">${formatMarkdown(result.portfolio)}</div>`;
                        html += `<div style="margin-top: 1rem; display: flex; gap: 1rem;">`;
                        html += `<button class="btn btn-primary" onclick="downloadPortfolio()">📥 Download</button>`;
                        html += `<button class="btn btn-outline" onclick="printPortfolio()">🖨️ Print</button>`;
                        html += `</div>`;
                        window.currentPortfolio = result.portfolio;
                    }
                    break;
            }

            actualResult.innerHTML = html;
        }

        // Close result panel
        function closeResultPanel() {
            document.getElementById('resultPanel').classList.remove('active');
            if (currentAgentRequest) {
                currentAgentRequest.abort();
                currentAgentRequest = null;
            }
        }

        // Show agent info
        function showAgentInfo(agentType) {
            const info = {
                narrative: 'The Narrative Agent analyzes your journal entries to identify themes, extract skills, and generate coherent weekly narrative reports. It includes automatic quality checking and revision.',
                analysis: 'The Analysis Agent performs deep analysis including skills extraction (technical & soft), progress tracking over time, pattern recognition, and generates executive summaries.',
                quality: 'The Quality Agent reviews your entries for completeness, grammar, style, and provides scoring (A-F) with specific improvement suggestions.',
                portfolio: 'The Portfolio Agent is an orchestrator that generates complete 3-chapter OJT reports including Company Profile, Activities, and Conclusion & Recommendations.'
            };

            alert(info[agentType] || 'AI Agent for OJT report generation');
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Increment tasks completed
        function incrementTasksCompleted() {
            const current = parseInt(localStorage.getItem('agentTasksCompleted') || '0');
            localStorage.setItem('agentTasksCompleted', (current + 1).toString());
            document.getElementById('tasksCompleted').textContent = current + 1;
        }

        // Download portfolio
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

        // Print portfolio
        function printPortfolio() {
            const content = window.currentPortfolio;
            if (!content) {
                showNotification('No portfolio to print', 'error');
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
                        h1, h2, h3 { color: #333; margin-top: 1.5rem; }
                        @media print { body { padding: 0; } }
                    </style>
                </head>
                <body>
                    <div style="white-space: pre-wrap;">${escapeHtml(content)}</div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            showNotification('Print dialog opened', 'success');
        }

        // Escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
