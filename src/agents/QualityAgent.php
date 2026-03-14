<?php
/**
 * Quality Agent
 * 
 * Validates and improves OJT content:
 * - Content quality assessment
 * - Grammar and style checking
 * - Completeness verification
 * - Improvement suggestions
 */

require_once __DIR__ . '/BaseAgent.php';
require_once __DIR__ . '/../tools/ToolRegistry.php';

class QualityAgent extends BaseAgent {
    private ToolRegistry $toolRegistry;
    
    public function __construct() {
        $this->toolRegistry = ToolRegistry::getInstance();
        $this->registerDefaultTools();
    }
    
    /**
     * Execute the quality agent
     */
    public function execute(string $goal, array $context = []): array {
        $this->log("Starting Quality Agent", ['goal' => $goal]);
        
        try {
            // Determine quality check type
            $checkType = $this->determineCheckType($goal);
            
            $this->log("Performing {$checkType} quality check...");
            
            switch ($checkType) {
                case 'single_entry':
                    $result = $this->checkSingleEntry($context);
                    break;
                case 'all_entries':
                    $result = $this->checkAllEntries($context);
                    break;
                case 'report':
                    $result = $this->checkReport($context);
                    break;
                case 'improve':
                    $result = $this->improveEntries($context);
                    break;
                default:
                    $result = $this->comprehensiveQualityCheck($context);
            }
            
            $this->log("Quality check completed");
            
            return [
                'success' => true,
                'check_type' => $checkType,
                'result' => $result,
                'steps_taken' => count($this->executionLog)
            ];
            
        } catch (Exception $e) {
            $this->log("Error during quality check", ['error' => $e->getMessage()]);
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
        $tools = ['fetchEntries', 'checkCompleteness', 'suggestImprovements', 'enhanceDescription'];
        
        foreach ($tools as $toolName) {
            $tool = $this->toolRegistry->get($toolName);
            if ($tool) {
                $this->registerTool($toolName, $tool);
            }
        }
    }
    
    /**
     * Determine type of quality check
     */
    private function determineCheckType(string $goal): string {
        $goalLower = strtolower($goal);
        
        if (str_contains($goalLower, 'improve') || str_contains($goalLower, 'enhance')) {
            return 'improve';
        } elseif (str_contains($goalLower, 'report') || str_contains($goalLower, 'document')) {
            return 'report';
        } elseif (str_contains($goalLower, 'all') || str_contains($goalLower, 'every')) {
            return 'all_entries';
        } elseif (str_contains($goalLower, 'single') || str_contains($goalLower, 'one') || str_contains($goalLower, 'this')) {
            return 'single_entry';
        }
        
        return 'comprehensive';
    }
    
    /**
     * Check a single entry
     */
    private function checkSingleEntry(array $context): array {
        $entry = $context['entry'] ?? null;
        
        if (!$entry) {
            return ['error' => 'No entry provided'];
        }
        
        $criteria = [
            'has_title' => !empty($entry['title']),
            'has_description' => !empty($entry['user_description']) || !empty($entry['ai_enhanced_description']),
            'description_length' => strlen($entry['user_description'] ?? '') >= 30,
            'has_action_words' => $this->hasActionWords($entry['user_description'] ?? ''),
            'has_learning_outcome' => $this->hasLearningOutcome($entry['user_description'] ?? ''),
            'professional_tone' => $this->checkProfessionalTone($entry['user_description'] ?? '')
        ];
        
        $score = (int)(array_sum($criteria) / count($criteria) * 100);
        
        // Get AI feedback
        $feedback = $this->getAIFeedback($entry);
        
        return [
            'entry_id' => $entry['id'] ?? null,
            'entry_title' => $entry['title'] ?? 'Untitled',
            'criteria' => $criteria,
            'score' => $score,
            'grade' => $this->scoreToGrade($score),
            'feedback' => $feedback,
            'suggestions' => $this->generateSuggestions($entry, $criteria)
        ];
    }
    
    /**
     * Check all entries
     */
    private function checkAllEntries(array $context = []): array {
        $entries = $context['entries'] ?? $this->executeTool('fetchEntries', ['limit' => 100]);
        
        if (empty($entries)) {
            return ['error' => 'No entries to check'];
        }
        
        $results = [];
        $totalScore = 0;
        $issues = [];
        
        foreach ($entries as $entry) {
            $check = $this->checkSingleEntry(['entry' => $entry]);
            $results[] = $check;
            $totalScore += $check['score'] ?? 0;
            
            if (($check['score'] ?? 0) < 70) {
                $issues[] = [
                    'entry_id' => $entry['id'],
                    'title' => $entry['title'],
                    'score' => $check['score'],
                    'main_issues' => $this->extractMainIssues($check)
                ];
            }
        }
        
        $avgScore = (int)($totalScore / count($entries));
        
        return [
            'total_entries' => count($entries),
            'average_score' => $avgScore,
            'average_grade' => $this->scoreToGrade($avgScore),
            'entries' => $results,
            'problematic_entries' => $issues,
            'problem_count' => count($issues),
            'overall_assessment' => $this->getOverallAssessment($avgScore, count($issues))
        ];
    }
    
    /**
     * Check a complete report
     */
    private function checkReport(array $context): array {
        $reportContent = $context['report'] ?? '';
        
        if (empty($reportContent)) {
            return ['error' => 'No report content provided'];
        }
        
        $prompt = "Evaluate this OJT report for quality:\n\n{$reportContent}\n\n";
        $prompt .= "Assess:\n";
        $prompt .= "1. Structure and organization\n";
        $prompt .= "2. Clarity and coherence\n";
        $prompt .= "3. Professional tone\n";
        $prompt .= "4. Completeness of information\n";
        $prompt .= "5. Grammar and spelling\n";
        $prompt .= "6. Depth of reflection\n\n";
        $prompt .= "Return JSON with:\n";
        $prompt .= "- overall_score: number 1-100\n";
        $prompt .= "- category_scores: object with scores for each criterion\n";
        $prompt .= "- strengths: array of strengths\n";
        $prompt .= "- weaknesses: array of areas to improve\n";
        $prompt .= "- recommendations: array of specific recommendations\n\n";
        $prompt .= "Return ONLY valid JSON.";
        
        $response = $this->callAI($prompt, "You are a quality reviewer. Return only JSON.");
        
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'report_quality' => $parsed,
                    'grade' => $this->scoreToGrade($parsed['overall_score'] ?? 50)
                ];
            }
        }
        
        return ['report_quality' => 'Quality check unavailable'];
    }
    
    /**
     * Improve entries using AI
     */
    private function improveEntries(array $context): array {
        $entries = $context['entries'] ?? $this->executeTool('fetchEntries', ['limit' => 50]);
        
        if (empty($entries)) {
            return ['error' => 'No entries to improve'];
        }
        
        $improvements = [];
        
        foreach ($entries as $entry) {
            $improved = $this->improveSingleEntry($entry);
            $improvements[] = [
                'entry_id' => $entry['id'],
                'title' => $entry['title'],
                'original' => $entry['user_description'] ?? '',
                'improved' => $improved['description'],
                'changes' => $improved['changes']
            ];
        }
        
        return [
            'improved_count' => count($improvements),
            'improvements' => $improvements
        ];
    }
    
    /**
     * Improve a single entry
     */
    private function improveSingleEntry(array $entry): array {
        $original = $entry['user_description'] ?? '';
        $title = $entry['title'] ?? 'OJT Activity';
        
        if (empty($original)) {
            return ['description' => '', 'changes' => ['No content to improve']];
        }
        
        $prompt = "Improve this OJT journal entry:\n\n";
        $prompt .= "Title: {$title}\n";
        $prompt .= "Description: {$original}\n\n";
        $prompt .= "Improve by:\n";
        $prompt .= "1. Making it more specific and detailed\n";
        $prompt .= "2. Using professional language\n";
        $prompt .= "3. Highlighting skills and learnings\n";
        $prompt .= "4. Adding context and impact\n\n";
        $prompt .= "Return JSON with:\n";
        $prompt .= "- improved_description: the enhanced version\n";
        $prompt .= "- changes: array of what was improved\n\n";
        $prompt .= "Return ONLY valid JSON.";
        
        $response = $this->callAI($prompt, "Improve OJT entries. Return only JSON.");
        
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
        
        // Fallback to simple enhancement
        $enhanced = $this->executeTool('enhanceDescription', [
            'description' => $original,
            'title' => $title
        ]);
        
        return [
            'description' => $enhanced,
            'changes' => ['Enhanced using AI']
        ];
    }
    
    /**
     * Comprehensive quality check
     */
    private function comprehensiveQualityCheck(array $context): array {
        $this->log("Running comprehensive quality check...");
        
        // Check all entries
        $allEntriesCheck = $this->checkAllEntries($context);
        
        // Get improvement suggestions
        $this->log("Getting improvement suggestions...");
        $entries = $context['entries'] ?? $this->executeTool('fetchEntries', ['limit' => 50]);
        $improvementContext = ['entries' => array_filter($entries, fn($e) => ($e['score'] ?? 0) < 70)];
        $improvements = $this->improveEntries($improvementContext);
        
        return [
            'quality_assessment' => $allEntriesCheck,
            'improvements' => $improvements,
            'action_items' => $this->generateActionItems($allEntriesCheck)
        ];
    }
    
    /**
     * Get AI feedback for an entry
     */
    private function getAIFeedback(array $entry): string {
        $description = $entry['user_description'] ?? $entry['ai_enhanced_description'] ?? '';
        $title = $entry['title'] ?? 'Untitled';
        
        if (empty($description)) {
            return 'No content to review';
        }
        
        $prompt = "Provide constructive feedback for this OJT entry:\n\n";
        $prompt .= "Title: {$title}\n";
        $prompt .= "Description: {$description}\n\n";
        $prompt .= "Provide 2-3 specific, actionable suggestions for improvement.\n";
        $prompt .= "Keep feedback encouraging and helpful.";
        
        return $this->callAI($prompt, "Provide helpful, constructive feedback.");
    }
    
    /**
     * Generate suggestions based on criteria
     */
    private function generateSuggestions(array $entry, array $criteria): array {
        $suggestions = [];
        
        if (!$criteria['has_title']) {
            $suggestions[] = 'Add a descriptive title for this entry';
        }
        
        if (!$criteria['has_description']) {
            $suggestions[] = 'Add a description of what you did';
        }
        
        if (!$criteria['description_length']) {
            $suggestions[] = 'Expand your description with more details (aim for at least 30 words)';
        }
        
        if (!$criteria['has_action_words']) {
            $suggestions[] = 'Use action verbs to describe your activities (e.g., "developed", "implemented", "created")';
        }
        
        if (!$criteria['has_learning_outcome']) {
            $suggestions[] = 'Include what you learned from this experience';
        }
        
        if (!$criteria['professional_tone']) {
            $suggestions[] = 'Use more professional language';
        }
        
        return $suggestions;
    }
    
    /**
     * Check if text has action words
     */
    private function hasActionWords(string $text): bool {
        $actionWords = [
            'developed', 'created', 'implemented', 'designed', 'built',
            'analyzed', 'tested', 'fixed', 'improved', 'optimized',
            'learned', 'studied', 'researched', 'documented', 'presented',
            'collaborated', 'assisted', 'participated', 'completed', 'achieved'
        ];
        
        $textLower = strtolower($text);
        
        foreach ($actionWords as $word) {
            if (str_contains($textLower, $word)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if text has learning outcomes
     */
    private function hasLearningOutcome(string $text): bool {
        $learningWords = ['learned', 'understand', 'gained', 'skill', 'knowledge', 'insight', 'realized'];
        $textLower = strtolower($text);
        
        foreach ($learningWords as $word) {
            if (str_contains($textLower, $word)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check professional tone
     */
    private function checkProfessionalTone(string $text): bool {
        // Simple heuristic: check for slang/informal words
        $informalWords = ['stuff', 'things', 'basically', 'like', 'kinda', 'gonna', 'wanna'];
        $textLower = strtolower($text);
        
        foreach ($informalWords as $word) {
            if (str_contains($textLower, $word)) {
                return false;
            }
        }
        
        // Check minimum length for professionalism
        return strlen($text) >= 30;
    }
    
    /**
     * Convert score to grade
     */
    private function scoreToGrade(int $score): string {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }
    
    /**
     * Extract main issues from check result
     */
    private function extractMainIssues(array $check): array {
        $issues = [];
        
        foreach ($check['suggestions'] ?? [] as $suggestion) {
            $issues[] = $suggestion;
        }
        
        return array_slice($issues, 0, 3); // Top 3 issues
    }
    
    /**
     * Get overall assessment
     */
    private function getOverallAssessment(int $avgScore, int $problemCount): string {
        if ($avgScore >= 90 && $problemCount === 0) {
            return 'Excellent! Your entries are well-documented.';
        } elseif ($avgScore >= 80 && $problemCount <= 2) {
            return 'Good work! A few entries could use improvement.';
        } elseif ($avgScore >= 70) {
            return 'Acceptable, but there\'s room for improvement.';
        } else {
            return 'Your entries need significant improvement. Consider adding more details.';
        }
    }
    
    /**
     * Generate action items
     */
    private function generateActionItems(array $assessment): array {
        $actionItems = [];
        
        if (($assessment['problem_count'] ?? 0) > 0) {
            $actionItems[] = "Review and improve {$assessment['problem_count']} problematic entries";
        }
        
        if (($assessment['average_score'] ?? 0) < 70) {
            $actionItems[] = 'Add more detailed descriptions to your entries';
            $actionItems[] = 'Include learning outcomes for each activity';
        }
        
        if (empty($actionItems)) {
            $actionItems[] = 'Continue maintaining good documentation practices';
        }
        
        return $actionItems;
    }
}
