<?php
/**
 * Analysis Agent
 * 
 * Performs deep analysis of OJT entries:
 * - Skills gap analysis
 * - Progress tracking
 * - Pattern recognition
 * - Insight generation
 */

require_once __DIR__ . '/BaseAgent.php';
require_once __DIR__ . '/../tools/ToolRegistry.php';

class AnalysisAgent extends BaseAgent {
    private ToolRegistry $toolRegistry;
    
    public function __construct() {
        $this->toolRegistry = ToolRegistry::getInstance();
        $this->registerDefaultTools();
    }
    
    /**
     * Execute the analysis agent
     */
    public function execute(string $goal, array $context = []): array {
        $this->log("Starting Analysis Agent", ['goal' => $goal]);
        
        try {
            // Step 1: Gather data
            $entries = $this->gatherEntries($context);
            
            // Step 2: Perform analysis based on goal
            $analysisType = $this->determineAnalysisType($goal);
            
            $this->log("Performing {$analysisType} analysis...");
            
            switch ($analysisType) {
                case 'skills':
                    $result = $this->analyzeSkills($entries);
                    break;
                case 'progress':
                    $result = $this->analyzeProgress($entries);
                    break;
                case 'patterns':
                    $result = $this->analyzePatterns($entries);
                    break;
                case 'gaps':
                    $result = $this->analyzeGaps($entries, $context);
                    break;
                case 'comprehensive':
                default:
                    $result = $this->comprehensiveAnalysis($entries);
                    break;
            }
            
            $this->log("Analysis completed");
            
            return [
                'success' => true,
                'analysis_type' => $analysisType,
                'result' => $result,
                'entry_count' => count($entries),
                'steps_taken' => count($this->executionLog)
            ];
            
        } catch (Exception $e) {
            $this->log("Error during analysis", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Register default tools
     */
    private function registerDefaultTools(): void {
        $tools = ['fetchEntries', 'getDateRange', 'getEntryImages', 'summarizeEntries', 'checkCompleteness'];
        
        foreach ($tools as $toolName) {
            $tool = $this->toolRegistry->get($toolName);
            if ($tool) {
                $this->registerTool($toolName, $tool);
            }
        }
    }
    
    /**
     * Gather entries for analysis
     */
    private function gatherEntries(array $context): array {
        if (isset($context['entries'])) {
            return $context['entries'];
        }
        
        if (isset($context['date_range'])) {
            return $this->executeTool('getDateRange', $context['date_range']);
        }
        
        return $this->executeTool('fetchEntries', ['limit' => 100]);
    }
    
    /**
     * Determine type of analysis to perform
     */
    private function determineAnalysisType(string $goal): string {
        $goalLower = strtolower($goal);
        
        if (str_contains($goalLower, 'skill')) {
            return 'skills';
        } elseif (str_contains($goalLower, 'progress') || str_contains($goalLower, 'improvement')) {
            return 'progress';
        } elseif (str_contains($goalLower, 'pattern') || str_contains($goalLower, 'trend')) {
            return 'patterns';
        } elseif (str_contains($goalLower, 'gap') || str_contains($goalLower, 'missing')) {
            return 'gaps';
        } elseif (str_contains($goalLower, 'comprehensive') || str_contains($goalLower, 'full')) {
            return 'comprehensive';
        }
        
        return 'comprehensive';
    }
    
    /**
     * Analyze skills developed
     */
    private function analyzeSkills(array $entries): array {
        if (empty($entries)) {
            return ['error' => 'No entries to analyze'];
        }
        
        $entriesText = $this->buildEntriesText($entries);
        
        $prompt = "Analyze these OJT entries and extract skills:\n\n{$entriesText}\n\n";
        $prompt .= "Categorize skills into:\n";
        $prompt .= "- technical_skills: programming languages, tools, technologies\n";
        $prompt .= "- soft_skills: communication, teamwork, problem-solving\n";
        $prompt .= "- domain_knowledge: industry-specific knowledge\n\n";
        $prompt .= "For each skill, include:\n";
        $prompt .= "- name: skill name\n";
        $prompt .= "- evidence: brief description of how it was demonstrated\n";
        $prompt .= "- proficiency: beginner/intermediate/advanced (estimated)\n\n";
        $prompt .= "Return ONLY valid JSON array for each category.";
        
        $response = $this->callAI($prompt, "Extract and categorize skills. Return only JSON.");
        
        // Parse response
        $skills = $this->parseSkillsResponse($response);
        
        // Calculate skill frequency
        $skillFrequency = $this->calculateSkillFrequency($entries, $skills);
        
        return [
            'skills' => $skills,
            'skill_frequency' => $skillFrequency,
            'top_skills' => array_slice($skillFrequency, 0, 5, true),
            'total_unique_skills' => count($skillFrequency)
        ];
    }
    
    /**
     * Analyze progress over time
     */
    private function analyzeProgress(array $entries): array {
        if (count($entries) < 2) {
            return ['error' => 'Need at least 2 entries for progress analysis'];
        }
        
        // Sort by date
        usort($entries, fn($a, $b) => strtotime($a['entry_date']) - strtotime($b['entry_date']));
        
        // Split into early and late entries
        $midpoint = (int)(count($entries) / 2);
        $earlyEntries = array_slice($entries, 0, $midpoint);
        $lateEntries = array_slice($entries, $midpoint);
        
        $earlyText = $this->buildEntriesText($earlyEntries);
        $lateText = $this->buildEntriesText($lateEntries);
        
        $prompt = "Compare early and late OJT entries to analyze progress:\n\n";
        $prompt .= "EARLY ENTRIES:\n{$earlyText}\n\n";
        $prompt .= "LATE ENTRIES:\n{$lateText}\n\n";
        $prompt .= "Analyze:\n";
        $prompt .= "1. How has the complexity of tasks changed?\n";
        $prompt .= "2. How has confidence/autonomy evolved?\n";
        $prompt .= "3. What new responsibilities were taken on?\n";
        $prompt .= "4. What improvements in skills are evident?\n\n";
        $prompt .= "Return JSON with:\n";
        $prompt .= "- complexity_change: description\n";
        $prompt .= "- autonomy_change: description\n";
        $prompt .= "- new_responsibilities: array\n";
        $prompt .= "- skill_improvements: array\n";
        $prompt .= "- overall_progress: rating 1-10\n";
        $prompt .= "- progress_summary: brief summary\n\n";
        $prompt .= "Return ONLY valid JSON.";
        
        $response = $this->callAI($prompt, "Analyze progress. Return only JSON.");
        
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
        
        return ['progress_summary' => 'Progress analysis unavailable'];
    }
    
    /**
     * Analyze patterns in entries
     */
    private function analyzePatterns(array $entries): array {
        if (empty($entries)) {
            return ['error' => 'No entries to analyze'];
        }
        
        $entriesText = $this->buildEntriesText($entries);
        
        $prompt = "Identify patterns in these OJT entries:\n\n{$entriesText}\n\n";
        $prompt .= "Look for:\n";
        $prompt .= "- Recurring activities or tasks\n";
        $prompt .= "- Common challenges faced\n";
        $prompt .= "- Types of projects worked on\n";
        $prompt .= "- Work patterns (collaboration vs independent)\n";
        $prompt .= "- Learning patterns\n\n";
        $prompt .= "Return JSON with:\n";
        $prompt .= "- activity_patterns: array of recurring activities\n";
        $prompt .= "- challenge_patterns: array of common challenges\n";
        $prompt .= "- project_types: array of project types\n";
        $prompt .= "- work_style: description of work patterns\n";
        $prompt .= "- learning_trajectory: description of learning pattern\n\n";
        $prompt .= "Return ONLY valid JSON.";
        
        $response = $this->callAI($prompt, "Identify patterns. Return only JSON.");
        
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
        
        return ['patterns' => 'Pattern analysis unavailable'];
    }
    
    /**
     * Analyze gaps in documentation
     */
    private function analyzeGaps(array $entries, array $context = []): array {
        $gaps = [];
        
        // Check for missing information
        foreach ($entries as $entry) {
            $entryGaps = [];
            
            if (empty($entry['title'])) {
                $entryGaps[] = 'Missing title';
            }
            
            $descLength = strlen($entry['user_description'] ?? '') + strlen($entry['ai_enhanced_description'] ?? '');
            if ($descLength < 50) {
                $entryGaps[] = 'Description too brief';
            }
            
            if (!str_contains(strtolower($descLength ?? ''), 'learn')) {
                $entryGaps[] = 'No mention of learning outcomes';
            }
            
            if (!empty($entryGaps)) {
                $gaps[] = [
                    'entry_id' => $entry['id'],
                    'entry_title' => $entry['title'],
                    'gaps' => $entryGaps
                ];
            }
        }
        
        // Get AI suggestions for missing content
        if (!empty($gaps)) {
            $gapsText = json_encode($gaps, JSON_PRETTY_PRINT);
            
            $prompt = "Based on these gaps in OJT documentation, suggest what content should be added:\n\n{$gapsText}\n\n";
            $prompt .= "Provide specific suggestions for each gap.";
            
            $suggestions = $this->callAI($prompt, "Provide helpful suggestions for improving documentation.");
            
            return [
                'gaps' => $gaps,
                'total_gaps' => count($gaps),
                'ai_suggestions' => $suggestions,
                'completeness_score' => max(0, 100 - (count($gaps) * 10))
            ];
        }
        
        return [
            'gaps' => [],
            'total_gaps' => 0,
            'completeness_score' => 100
        ];
    }
    
    /**
     * Comprehensive analysis combining all types
     */
    private function comprehensiveAnalysis(array $entries): array {
        $this->log("Running comprehensive analysis...");
        
        $results = [];
        
        // Skills analysis
        $this->log("Analyzing skills...");
        $results['skills'] = $this->analyzeSkills($entries);
        
        // Progress analysis (if enough entries)
        if (count($entries) >= 2) {
            $this->log("Analyzing progress...");
            $results['progress'] = $this->analyzeProgress($entries);
        }
        
        // Pattern analysis
        $this->log("Analyzing patterns...");
        $results['patterns'] = $this->analyzePatterns($entries);
        
        // Generate executive summary
        $this->log("Generating summary...");
        $results['summary'] = $this->generateExecutiveSummary($results);
        
        return $results;
    }
    
    /**
     * Build text from entries for analysis
     */
    private function buildEntriesText(array $entries): string {
        $texts = [];
        foreach ($entries as $entry) {
            $date = date('M j', strtotime($entry['entry_date']));
            $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description';
            $texts[] = "[{$date}] {$entry['title']}: {$desc}";
        }
        return implode("\n", $texts);
    }
    
    /**
     * Parse skills response from AI
     */
    private function parseSkillsResponse(string $response): array {
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
        
        return [
            'technical_skills' => [],
            'soft_skills' => [],
            'domain_knowledge' => []
        ];
    }
    
    /**
     * Calculate skill frequency from entries
     */
    private function calculateSkillFrequency(array $entries, array $skills): array {
        $frequency = [];
        
        // Count mentions of each skill in entries
        $allSkills = array_merge(
            $skills['technical_skills'] ?? [],
            $skills['soft_skills'] ?? [],
            $skills['domain_knowledge'] ?? []
        );
        
        foreach ($allSkills as $skill) {
            $skillName = strtolower($skill['name'] ?? '');
            if (empty($skillName)) continue;
            
            $count = 0;
            foreach ($entries as $entry) {
                $text = strtolower(($entry['user_description'] ?? '') . ($entry['ai_enhanced_description'] ?? ''));
                if (str_contains($text, $skillName)) {
                    $count++;
                }
            }
            
            $frequency[$skill['name']] = $count;
        }
        
        arsort($frequency);
        return $frequency;
    }
    
    /**
     * Generate executive summary of analysis
     */
    private function generateExecutiveSummary(array $results): string {
        $prompt = "Create an executive summary of this OJT analysis:\n\n";
        
        if (isset($results['skills'])) {
            $prompt .= "SKILLS: " . json_encode($results['skills']) . "\n\n";
        }
        if (isset($results['progress'])) {
            $prompt .= "PROGRESS: " . json_encode($results['progress']) . "\n\n";
        }
        if (isset($results['patterns'])) {
            $prompt .= "PATTERNS: " . json_encode($results['patterns']) . "\n\n";
        }
        
        $prompt .= "Write a concise 3-4 sentence summary highlighting:\n";
        $prompt .= "1. Key skills developed\n";
        $prompt .= "2. Overall progress\n";
        $prompt .= "3. Notable patterns or achievements\n\n";
        $prompt .= "Professional tone, suitable for a report.";
        
        return $this->callAI($prompt, "Write executive summaries. Professional tone.");
    }
}
