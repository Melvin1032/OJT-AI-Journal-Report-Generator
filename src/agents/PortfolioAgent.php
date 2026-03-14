<?php
/**
 * Portfolio Agent
 * 
 * Orchestrates complete OJT portfolio/report generation:
 * - Multi-step workflow planning
 * - Content gathering and synthesis
 * - Chapter generation
 * - Quality assurance
 * - Final compilation
 */

require_once __DIR__ . '/BaseAgent.php';
require_once __DIR__ . '/NarrativeAgent.php';
require_once __DIR__ . '/AnalysisAgent.php';
require_once __DIR__ . '/QualityAgent.php';
require_once __DIR__ . '/../tools/ToolRegistry.php';

class PortfolioAgent extends BaseAgent {
    private ToolRegistry $toolRegistry;
    private ?NarrativeAgent $narrativeAgent = null;
    private ?AnalysisAgent $analysisAgent = null;
    private ?QualityAgent $qualityAgent = null;
    
    public function __construct() {
        $this->toolRegistry = ToolRegistry::getInstance();
        $this->registerDefaultTools();
        $this->initializeSubAgents();
    }
    
    /**
     * Initialize sub-agents
     */
    private function initializeSubAgents(): void {
        $this->narrativeAgent = new NarrativeAgent();
        $this->analysisAgent = new AnalysisAgent();
        $this->qualityAgent = new QualityAgent();
    }
    
    /**
     * Execute the portfolio agent
     */
    public function execute(string $goal, array $context = []): array {
        $this->log("Starting Portfolio Agent", ['goal' => $goal]);
        
        try {
            // Step 1: Create execution plan
            $this->log("Creating execution plan...");
            $plan = $this->createPortfolioPlan($goal, $context);
            
            // Step 2: Execute plan steps
            $results = [];
            foreach ($plan as $stepNum => $step) {
                $this->log("Executing step {$stepNum}: {$step['name']}");
                $results[$step['name']] = $this->executeStep($step, $context, $results);
                
                // Check for step failure
                if (isset($results[$step['name']]['success']) && !$results[$step['name']]['success']) {
                    $this->log("Step {$stepNum} failed, attempting recovery...");
                    $results[$step['name']] = $this->recoverFromFailure($step, $results);
                }
            }
            
            // Step 3: Compile final portfolio
            $this->log("Compiling final portfolio...");
            $portfolio = $this->compilePortfolio($results, $context);
            
            // Step 4: Final quality check
            $this->log("Performing final quality check...");
            $qualityResult = $this->qualityAgent->execute("Check report quality", ['report' => $portfolio]);
            
            $this->log("Portfolio generation completed");
            
            return [
                'success' => true,
                'portfolio' => $portfolio,
                'quality_score' => $qualityResult['result']['report_quality']['overall_score'] ?? null,
                'steps_completed' => count($plan),
                'execution_log' => $this->executionLog
            ];
            
        } catch (Exception $e) {
            $this->log("Error during portfolio generation", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'partial_results' => $this->memory
            ];
        }
    }
    
    /**
     * Register default tools
     */
    private function registerDefaultTools(): void {
        $tools = ['fetchEntries', 'getStudentInfo', 'getDateRange', 'generateNarrative'];
        
        foreach ($tools as $toolName) {
            $tool = $this->toolRegistry->get($toolName);
            if ($tool) {
                $this->registerTool($toolName, $tool);
            }
        }
    }
    
    /**
     * Create portfolio generation plan
     */
    private function createPortfolioPlan(string $goal, array $context): array {
        // Default plan for ISPSC-style report
        return [
            [
                'name' => 'gather_info',
                'description' => 'Gather student info and entries',
                'dependencies' => []
            ],
            [
                'name' => 'analyze_entries',
                'description' => 'Analyze entries for skills and patterns',
                'dependencies' => ['gather_info']
            ],
            [
                'name' => 'generate_chapter1',
                'description' => 'Generate Chapter I - Company Profile',
                'dependencies' => ['gather_info']
            ],
            [
                'name' => 'generate_chapter2',
                'description' => 'Generate Chapter II - Activities',
                'dependencies' => ['gather_info', 'analyze_entries']
            ],
            [
                'name' => 'generate_chapter3',
                'description' => 'Generate Chapter III - Conclusion & Recommendations',
                'dependencies' => ['analyze_entries']
            ],
            [
                'name' => 'compile_report',
                'description' => 'Compile all chapters into final report',
                'dependencies' => ['generate_chapter1', 'generate_chapter2', 'generate_chapter3']
            ]
        ];
    }
    
    /**
     * Execute a single step
     */
    private function executeStep(array $step, array $context, array $previousResults): array {
        switch ($step['name']) {
            case 'gather_info':
                return $this->gatherInfo($context);
                
            case 'analyze_entries':
                return $this->analyzeEntries($previousResults['gather_info'] ?? []);
                
            case 'generate_chapter1':
                return $this->generateChapter1($previousResults['gather_info'] ?? []);
                
            case 'generate_chapter2':
                return $this->generateChapter2(
                    $previousResults['gather_info'] ?? [],
                    $previousResults['analyze_entries'] ?? []
                );
                
            case 'generate_chapter3':
                return $this->generateChapter3(
                    $previousResults['gather_info'] ?? [],
                    $previousResults['analyze_entries'] ?? []
                );
                
            case 'compile_report':
                return $this->compileReport($previousResults);
                
            default:
                return ['success' => false, 'error' => "Unknown step: {$step['name']}"];
        }
    }
    
    /**
     * Gather student info and entries
     */
    private function gatherInfo(array $context): array {
        $studentInfo = $this->executeTool('getStudentInfo');
        $entries = $this->executeTool('fetchEntries', ['limit' => 100]);
        
        // Get date range
        $dateRange = null;
        if (!empty($entries)) {
            $dates = array_column($entries, 'entry_date');
            $dateRange = [
                'start' => min($dates),
                'end' => max($dates),
                'total_days' => (strtotime(max($dates)) - strtotime(min($dates))) / 86400 + 1
            ];
        }
        
        return [
            'success' => true,
            'student_info' => $studentInfo,
            'entries' => $entries,
            'entry_count' => count($entries),
            'date_range' => $dateRange
        ];
    }
    
    /**
     * Analyze entries using AnalysisAgent
     */
    private function analyzeEntries(array $gatheredData): array {
        $entries = $gatheredData['entries'] ?? [];
        
        if (empty($entries)) {
            return ['success' => false, 'error' => 'No entries to analyze'];
        }
        
        $result = $this->analysisAgent->execute("Comprehensive analysis of OJT entries", [
            'entries' => $entries
        ]);
        
        return $result;
    }
    
    /**
     * Generate Chapter I - Company Profile
     */
    private function generateChapter1(array $gatheredData): array {
        $studentInfo = $gatheredData['student_info'] ?? null;
        $entries = $gatheredData['entries'] ?? [];
        
        $companyName = $studentInfo['company_name'] ?? 'the company';
        $position = $studentInfo['position'] ?? 'intern';
        
        // Build context from entries
        $entriesContext = $this->buildEntriesContext($entries);
        
        $prompt = "Write Chapter I - Company Profile for an OJT report:\n\n";
        
        if ($studentInfo) {
            $prompt .= "Student Info:\n";
            $prompt .= "- Name: {$studentInfo['name']}\n";
            $prompt .= "- Company: {$companyName}\n";
            $prompt .= "- Position: {$position}\n";
            if (!empty($studentInfo['company_address'])) {
                $prompt .= "- Address: {$studentInfo['company_address']}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "OJT Entries Context:\n{$entriesContext}\n\n";
        
        $prompt .= "Write the following sections:\n";
        $prompt .= "1.1 Company Background - Describe the company, its industry, and main business\n";
        $prompt .= "1.2 Company Vision and Mission - If known, otherwise create generic professional versions\n";
        $prompt .= "1.3 Organizational Structure - Describe the department structure\n";
        $prompt .= "1.4 Intern's Role - Describe where the intern fits in the organization\n\n";
        $prompt .= "Use formal academic tone. Total: 300-400 words.";
        
        $chapter1 = $this->callAI($prompt, "Write professional OJT company profile chapter.");
        
        return [
            'success' => true,
            'chapter' => 'Chapter I',
            'title' => 'Company Profile',
            'content' => $chapter1
        ];
    }
    
    /**
     * Generate Chapter II - Activities
     */
    private function generateChapter2(array $gatheredData, array $analysisData): array {
        $entries = $gatheredData['entries'] ?? [];
        $studentInfo = $gatheredData['student_info'] ?? null;
        
        if (empty($entries)) {
            return ['success' => false, 'error' => 'No entries for activities chapter'];
        }
        
        // Build activities table
        $activitiesTable = $this->buildActivitiesTable($entries);
        
        // Get analysis insights
        $skills = $analysisData['result']['skills'] ?? null;
        $narrative = $analysisData['result']['summary'] ?? null;
        
        $prompt = "Write Chapter II - Internship Activities for an OJT report:\n\n";
        
        $prompt .= "ACTIVITIES TABLE:\n{$activitiesTable}\n\n";
        
        if ($skills) {
            $prompt .= "SKILLS ANALYSIS:\n" . json_encode($skills) . "\n\n";
        }
        
        $prompt .= "Write the following sections:\n";
        $prompt .= "2.1 Overview of Activities - Summarize the types of work done\n";
        $prompt .= "2.2 Detailed Activities - Describe major tasks and responsibilities\n";
        $prompt .= "2.3 Skills Applied - Discuss technical and soft skills used\n";
        $prompt .= "2.4 Challenges and Solutions - Describe problems faced and how they were solved\n\n";
        
        if ($studentInfo) {
            $prompt .= "Intern: {$studentInfo['name']} at {$studentInfo['company_name']}\n\n";
        }
        
        $prompt .= "Use formal academic tone. Include specific examples from entries. Total: 400-500 words.";
        
        $chapter2 = $this->callAI($prompt, "Write professional OJT activities chapter.");
        
        return [
            'success' => true,
            'chapter' => 'Chapter II',
            'title' => 'Internship Activities',
            'content' => $chapter2,
            'activities_table' => $activitiesTable
        ];
    }
    
    /**
     * Generate Chapter III - Conclusion & Recommendations
     */
    private function generateChapter3(array $gatheredData, array $analysisData): array {
        $entries = $gatheredData['entries'] ?? [];
        $studentInfo = $gatheredData['student_info'] ?? null;
        
        // Get analysis results
        $progress = $analysisData['result']['progress'] ?? null;
        $skills = $analysisData['result']['skills'] ?? null;
        
        $entriesContext = $this->buildEntriesContext($entries);
        
        $prompt = "Write Chapter III - Conclusion and Recommendations for an OJT report:\n\n";
        
        $prompt .= "OJT Experience Context:\n{$entriesContext}\n\n";
        
        if ($progress) {
            $prompt .= "Progress Analysis:\n" . json_encode($progress) . "\n\n";
        }
        
        if ($skills) {
            $prompt .= "Skills Developed:\n" . json_encode($skills) . "\n\n";
        }
        
        $prompt .= "Write the following sections:\n";
        $prompt .= "3.1 Conclusion\n";
        $prompt .= "   - Summary of overall OJT experience\n";
        $prompt .= "   - Key learnings and takeaways\n";
        $prompt .= "   - Impact on professional development\n";
        $prompt .= "   - Connection to academic studies\n\n";
        
        $prompt .= "3.2 Recommendations\n";
        $prompt .= "   - For future interns (at least 2)\n";
        $prompt .= "   - For the company (at least 1)\n";
        $prompt .= "   - For the school's OJT program (at least 1)\n\n";
        
        if ($studentInfo) {
            $prompt .= "Student: {$studentInfo['name']}\n";
        }
        
        $prompt .= "Use formal academic tone. Be specific and insightful. Total: 300-400 words.";
        
        $chapter3 = $this->callAI($prompt, "Write professional OJT conclusion and recommendations.");
        
        return [
            'success' => true,
            'chapter' => 'Chapter III',
            'title' => 'Conclusion and Recommendations',
            'content' => $chapter3
        ];
    }
    
    /**
     * Compile all chapters into final report
     */
    private function compileReport(array $results): array {
        $chapters = [];
        $fullReport = "";
        
        // Compile chapters
        foreach (['generate_chapter1', 'generate_chapter2', 'generate_chapter3'] as $chapterKey) {
            if (isset($results[$chapterKey]) && $results[$chapterKey]['success']) {
                $chapters[] = $results[$chapterKey];
            }
        }
        
        // Build full report
        $fullReport .= "# OJT INTERNSHIP REPORT\n\n";
        
        if (isset($results['gather_info']['student_info'])) {
            $info = $results['gather_info']['student_info'];
            $fullReport .= "**Student:** {$info['name']}\n";
            $fullReport .= "**Company:** {$info['company_name']}\n";
            $fullReport .= "**Position:** {$info['position']}\n";
            if (!empty($info['date_from']) && !empty($info['date_to'])) {
                $fullReport .= "**Period:** " . date('M d, Y', strtotime($info['date_from'])) . " - " . date('M d, Y', strtotime($info['date_to'])) . "\n";
            }
            $fullReport .= "\n---\n\n";
        }
        
        foreach ($chapters as $chapter) {
            $fullReport .= "## {$chapter['title']}\n\n";
            $fullReport .= $chapter['content'] . "\n\n";
            $fullReport .= "---\n\n";
        }
        
        // Add entry summary
        if (isset($results['gather_info']['entries'])) {
            $entries = $results['gather_info']['entries'];
            $fullReport .= "## Appendix: Journal Entries Summary\n\n";
            $fullReport .= "Total Entries: " . count($entries) . "\n\n";
            
            foreach ($entries as $entry) {
                $fullReport .= "### {$entry['title']}\n";
                $fullReport .= "**Date:** " . date('M d, Y', strtotime($entry['entry_date'])) . "\n";
                $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description';
                $fullReport .= "**Description:** {$desc}\n\n";
            }
        }
        
        return [
            'success' => true,
            'chapters' => $chapters,
            'full_report' => $fullReport,
            'chapter_count' => count($chapters)
        ];
    }
    
    /**
     * Compile final portfolio
     */
    private function compilePortfolio(array $results, array $context): string {
        $compileResult = $this->compileReport($results);
        return $compileResult['full_report'] ?? 'Portfolio compilation failed';
    }
    
    /**
     * Recover from step failure
     */
    private function recoverFromFailure(array $step, array $previousResults): array {
        $this->log("Attempting recovery for step: {$step['name']}");
        
        // Try to use cached/result from memory
        $cachedResult = $this->recall("step_{$step['name']}");
        if ($cachedResult) {
            $this->log("Recovered from cache");
            return $cachedResult;
        }
        
        // Try simplified version
        switch ($step['name']) {
            case 'generate_chapter1':
                return [
                    'success' => true,
                    'chapter' => 'Chapter I',
                    'title' => 'Company Profile',
                    'content' => "[Company Profile - Auto-generated content unavailable]"
                ];
                
            case 'generate_chapter2':
                return [
                    'success' => true,
                    'chapter' => 'Chapter II',
                    'title' => 'Internship Activities',
                    'content' => "[Activities - See journal entries for details]"
                ];
                
            case 'generate_chapter3':
                return [
                    'success' => true,
                    'chapter' => 'Chapter III',
                    'title' => 'Conclusion and Recommendations',
                    'content' => "[Conclusion - OJT experience completed successfully]"
                ];
        }
        
        return ['success' => false, 'error' => 'Recovery failed'];
    }
    
    /**
     * Build entries context string
     */
    private function buildEntriesContext(array $entries): string {
        if (empty($entries)) {
            return 'No entries available';
        }
        
        $contexts = [];
        foreach ($entries as $entry) {
            $date = date('M d, Y', strtotime($entry['entry_date']));
            $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description';
            $contexts[] = "[{$date}] {$entry['title']}: " . substr($desc, 0, 150);
        }
        
        return implode("\n", $contexts);
    }
    
    /**
     * Build activities table
     */
    private function buildActivitiesTable(array $entries): string {
        if (empty($entries)) {
            return 'No activities';
        }
        
        $table = "| Date | Activity | Description |\n";
        $table .= "|------|----------|-------------|\n";
        
        foreach (array_slice($entries, 0, 20) as $entry) {
            $date = date('M d', strtotime($entry['entry_date']));
            $title = $entry['title'];
            $desc = substr($entry['ai_enhanced_description'] ?: $entry['user_description'] ?: '', 0, 100);
            $table .= "| {$date} | {$title} | {$desc} |\n";
        }
        
        if (count($entries) > 20) {
            $table .= "| ... | " . (count($entries) - 20) . " more entries | ... |\n";
        }
        
        return $table;
    }
}
