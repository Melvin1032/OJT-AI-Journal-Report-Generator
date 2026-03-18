<?php
/**
 * Narrative Agent
 * 
 * Generates intelligent narrative reports by:
 * - Analyzing journal entries
 * - Identifying themes and patterns
 * - Creating coherent narratives
 * - Adapting tone based on context
 */

require_once __DIR__ . '/BaseAgent.php';
require_once __DIR__ . '/../tools/ToolRegistry.php';

class NarrativeAgent extends BaseAgent {
    private ToolRegistry $toolRegistry;
    
    public function __construct() {
        $this->toolRegistry = ToolRegistry::getInstance();
        $this->registerDefaultTools();
    }
    
    /**
     * Execute the narrative generation agent
     */
    public function execute(string $goal, array $context = []): array {
        $this->log("Starting Narrative Agent", ['goal' => $goal, 'context' => $context]);
        
        try {
            // Step 1: Gather context
            $this->log("Gathering context...");
            $gatheredData = $this->gatherContext($context);
            
            // Step 2: Analyze entries for themes
            $this->log("Analyzing entries for themes...");
            $themes = $this->identifyThemes($gatheredData['entries']);
            
            // Step 3: Determine narrative type
            $narrativeType = $this->determineNarrativeType($goal, $themes);
            
            // Step 4: Generate narrative
            $this->log("Generating {$narrativeType} narrative...");
            $narrative = $this->generateNarrative($gatheredData, $themes, $narrativeType);
            
            // Step 5: Quality check
            $this->log("Performing quality check...");
            $qualityResult = $this->qualityCheck($narrative, $goal);
            
            if (!$qualityResult['passed'] && $qualityResult['needs_revision']) {
                $this->log("Revising narrative based on quality check...");
                $narrative = $this->reviseNarrative($narrative, $qualityResult['feedback']);
            }
            
            $this->log("Narrative generation completed successfully");
            
            return [
                'success' => true,
                'narrative' => $narrative,
                'themes' => $themes,
                'narrative_type' => $narrativeType,
                'entry_count' => count($gatheredData['entries']),
                'quality_score' => $qualityResult['score'],
                'steps_taken' => count($this->executionLog)
            ];
            
        } catch (Exception $e) {
            $this->log("Error during narrative generation", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Register default tools for this agent
     */
    private function registerDefaultTools(): void {
        $tools = ['fetchEntries', 'getDateRange', 'getStudentInfo', 'generateNarrative', 'summarizeEntries'];
        
        foreach ($tools as $toolName) {
            $tool = $this->toolRegistry->get($toolName);
            if ($tool) {
                $this->registerTool($toolName, $tool);
            }
        }
    }
    
    /**
     * Gather context data
     */
    private function gatherContext(array $context): array {
        $data = [
            'entries' => [],
            'student_info' => null,
            'date_range' => null
        ];
        
        // Get entries
        if (isset($context['date_range'])) {
            $data['entries'] = $this->executeTool('getDateRange', $context['date_range']);
            $data['date_range'] = $context['date_range'];
        } elseif (isset($context['entries'])) {
            $data['entries'] = $context['entries'];
        } else {
            $data['entries'] = $this->executeTool('fetchEntries', ['limit' => 50]);
        }
        
        // Get student info
        $data['student_info'] = $this->executeTool('getStudentInfo');
        
        $this->remember('gathered_data', $data);
        return $data;
    }
    
    /**
     * Identify themes from entries
     */
    private function identifyThemes(array $entries): array {
        if (empty($entries)) {
            return ['themes' => [], 'skills' => [], 'challenges' => []];
        }
        
        $entriesText = '';
        foreach ($entries as $entry) {
            $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: '';
            $entriesText .= "{$entry['title']}: {$desc}\n";
        }
        
        $prompt = "Analyze these OJT journal entries and identify:\n\n{$entriesText}\n\n";
        $prompt .= "Return a JSON object with:\n";
        $prompt .= "- themes: array of main themes (e.g., 'Learning', 'Problem-solving', 'Teamwork')\n";
        $prompt .= "- skills: array of skills developed\n";
        $prompt .= "- challenges: array of challenges faced\n";
        $prompt .= "- progression: description of how the intern progressed\n\n";
        $prompt .= "Return ONLY valid JSON.";
        
        $response = $this->callAI($prompt, "Analyze OJT entries and extract themes. Return only JSON.");
        
        // Extract JSON
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->remember('themes', $parsed);
                return $parsed;
            }
        }
        
        return [
            'themes' => ['Professional Development'],
            'skills' => ['Technical Skills'],
            'challenges' => ['Learning Curve'],
            'progression' => 'Showed improvement over time'
        ];
    }
    
    /**
     * Determine the type of narrative to generate
     */
    private function determineNarrativeType(string $goal, array $themes): string {
        $goalLower = strtolower($goal);
        
        if (str_contains($goalLower, 'weekly')) {
            return 'weekly_summary';
        } elseif (str_contains($goalLower, 'final') || str_contains($goalLower, 'conclusion')) {
            return 'final_report';
        } elseif (str_contains($goalLower, 'reflection')) {
            return 'reflective';
        } elseif (str_contains($goalLower, 'technical')) {
            return 'technical_focus';
        } elseif (str_contains($goalLower, 'growth') || str_contains($goalLower, 'development')) {
            return 'growth_focus';
        }
        
        return 'standard';
    }
    
    /**
     * Generate the narrative
     */
    private function generateNarrative(array $data, array $themes, string $type): string {
        $entries = $data['entries'];
        $studentInfo = $data['student_info'];

        // Build detailed entries context with day-by-day structure
        $entriesContext = [];
        $totalDays = count($entries);
        
        foreach ($entries as $index => $entry) {
            $date = date('l, F j, Y', strtotime($entry['entry_date']));
            $dayNumber = $index + 1;
            $fullDesc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description';
            $entriesContext[] = "[Day {$dayNumber}] {$date} - {$entry['title']}\nDetailed Account: {$fullDesc}";
        }

        // Get date range
        $startDate = date('F j, Y', strtotime($entries[0]['entry_date']));
        $endDate = date('F j, Y', strtotime(end($entries)['entry_date']));

        // Build comprehensive prompt based on narrative type
        $prompt = $this->buildNarrativePrompt($type, $entriesContext, $themes, $studentInfo);

        $systemMessage = $this->getSystemMessageForType($type);

        $narrative = $this->callAI($prompt, $systemMessage);

        $this->remember('generated_narrative', $narrative);
        return $narrative;
    }

    /**
     * Build prompt based on narrative type
     */
    private function buildNarrativePrompt(string $type, array $entries, array $themes, ?array $studentInfo): string {
        $prompt = "";

        // Add student context if available
        if ($studentInfo) {
            $prompt .= "Student: {$studentInfo['name']} at {$studentInfo['company_name']}\n";
            $prompt .= "Role: {$studentInfo['position']}\n\n";
        }

        // Add entries
        $prompt .= "JOURNAL ENTRIES:\n" . implode("\n", $entries) . "\n\n";

        // Add themes
        $prompt .= "IDENTIFIED THEMES:\n";
        $prompt .= "- Themes: " . implode(', ', $themes['themes'] ?? []) . "\n";
        $prompt .= "- Skills: " . implode(', ', $themes['skills'] ?? []) . "\n\n";

        // Type-specific instructions
        switch ($type) {
            case 'weekly_summary':
                $prompt .= "Write a WEEKLY SUMMARY narrative:\n";
                $prompt .= "- Paragraph 1: Overview of activities this week\n";
                $prompt .= "- Paragraph 2: Skills developed and progress made\n";
                $prompt .= "- Paragraph 3: Challenges encountered and how they were addressed\n";
                $prompt .= "Keep it professional, 150-200 words.\n";
                break;

            case 'final_report':
                $prompt .= "Write a FINAL REPORT narrative:\n";
                $prompt .= "- Summarize the entire OJT experience\n";
                $prompt .= "- Highlight major accomplishments and learning outcomes\n";
                $prompt .= "- Discuss overall growth and future applications\n";
                $prompt .= "Professional tone, 250-300 words.\n";
                break;

            case 'reflective':
                $prompt .= "Write a REFLECTIVE narrative:\n";
                $prompt .= "- Personal insights and learnings\n";
                $prompt .= "- How experiences changed perspectives\n";
                $prompt .= "- Connection between theory and practice\n";
                $prompt .= "Thoughtful and introspective tone, 200-250 words.\n";
                break;

            case 'technical_focus':
                $prompt .= "Write a TECHNICAL narrative:\n";
                $prompt .= "- Focus on technical skills and technologies used\n";
                $prompt .= "- Specific tasks and implementations\n";
                $prompt .= "- Technical challenges and solutions\n";
                $prompt .= "Detailed and technical tone, 200-250 words.\n";
                break;

            case 'growth_focus':
                $prompt .= "Write a GROWTH-FOCUSED narrative:\n";
                $prompt .= "- Personal and professional development\n";
                $prompt .= "- Skills progression over time\n";
                $prompt .= "- Confidence and competence growth\n";
                $prompt .= "Encouraging and positive tone, 200-250 words.\n";
                break;

            default:
                $prompt .= "Write a STANDARD narrative:\n";
                $prompt .= "- Summarize activities and experiences\n";
                $prompt .= "- Highlight key learnings\n";
                $prompt .= "Professional tone, 150-200 words.\n";
        }

        return $prompt;
    }

    /**
     * Get system message for narrative type
     */
    private function getSystemMessageForType(string $type): string {
        $messages = [
            'weekly_summary' => 'You are a professional report writer. Create concise weekly summaries.',
            'final_report' => 'You are a professional report writer. Create comprehensive final reports.',
            'reflective' => 'You are a reflective writing coach. Help students articulate their learnings.',
            'technical_focus' => 'You are a technical writer. Focus on technical details and implementations.',
            'growth_focus' => 'You are a career development coach. Highlight growth and development.',
            'standard' => 'You are a professional report writer. Create clear, well-structured narratives.'
        ];

        return $messages[$type] ?? $messages['standard'];
    }

    /**
     * Quality check for generated narrative
     */
    private function qualityCheck(string $narrative, string $goal): array {
        $prompt = "Evaluate this OJT narrative for quality:\n\n{$narrative}\n\n";
        $prompt .= "Check for:\n";
        $prompt .= "1. Clarity and coherence\n";
        $prompt .= "2. Professional tone\n";
        $prompt .= "3. Specific details (not generic)\n";
        $prompt .= "4. Proper length (100-300 words)\n";
        $prompt .= "5. Addresses the goal: {$goal}\n\n";
        $prompt .= "Return JSON with:\n";
        $prompt .= "- passed: boolean (true if quality is acceptable)\n";
        $prompt .= "- score: number 1-10\n";
        $prompt .= "- feedback: string with improvement suggestions if needed\n";
        $prompt .= "- needs_revision: boolean\n\n";
        $prompt .= "Return ONLY valid JSON.";

        $response = $this->callAI($prompt, "You are a quality reviewer. Return only JSON.");

        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }

        return [
            'passed' => true,
            'score' => 7,
            'feedback' => '',
            'needs_revision' => false
        ];
    }

    /**
     * Revise narrative based on feedback
     */
    private function reviseNarrative(string $narrative, string $feedback): string {
        $prompt = "Revise this OJT narrative based on the feedback:\n\n";
        $prompt .= "ORIGINAL:\n{$narrative}\n\n";
        $prompt .= "FEEDBACK:\n{$feedback}\n\n";
        $prompt .= "Provide an improved version that addresses the feedback.";

        return $this->callAI($prompt, "You are an editor. Improve the narrative based on feedback.");
    }
}
