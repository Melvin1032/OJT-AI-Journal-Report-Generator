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
        $prompt = $this->buildComprehensiveNarrativePrompt($type, $entriesContext, $themes, $studentInfo, $totalDays, $startDate, $endDate);

        $systemMessage = $this->getEnhancedSystemMessageForType($type);

        $narrative = $this->callAI($prompt, $systemMessage, ['max_tokens' => 4000]);

        $this->remember('generated_narrative', $narrative);
        return $narrative;
    }

    /**
     * Build comprehensive prompt for detailed narrative generation
     */
    private function buildComprehensiveNarrativePrompt(string $type, array $entries, array $themes, ?array $studentInfo, int $totalDays, string $startDate, string $endDate): string {
        $prompt = "";

        // Add student context if available
        if ($studentInfo) {
            $prompt .= "INTERN CONTEXT:\n";
            $prompt .= "Student: {$studentInfo['name']}\n";
            $prompt .= "Company: {$studentInfo['company_name']}\n";
            $prompt .= "Role: {$studentInfo['position']}\n\n";
        }

        // Add OJT period info
        $prompt .= "OJT PERIOD: {$totalDays} days ({$startDate} to {$endDate})\n\n";

        // Add journal entries
        $prompt .= "JOURNAL ENTRIES:\n" . implode("\n\n", $entries) . "\n\n";

        // Add identified themes
        $prompt .= "IDENTIFIED THEMES:\n";
        $prompt .= "- Main Themes: " . implode(', ', $themes['themes'] ?? []) . "\n";
        $prompt .= "- Skills Developed: " . implode(', ', $themes['skills'] ?? []) . "\n";
        $prompt .= "- Challenges Faced: " . implode(', ', $themes['challenges'] ?? []) . "\n\n";

        // Comprehensive report structure
        $prompt .= "TASK: Generate a comprehensive, professionally documented OJT Narrative Report.\n\n";
        $prompt .= "REQUIRED REPORT STRUCTURE:\n\n";
        
        $prompt .= "### 1. EXECUTIVE SUMMARY (1 substantial paragraph, 100-150 words)\n";
        $prompt .= "Provide a high-level overview of the entire OJT experience including:\n";
        $prompt .= "- The organization and duration of immersion\n";
        $prompt .= "- Primary focus areas and main responsibilities\n";
        $prompt .= "- Key accomplishments and their significance\n";
        $prompt .= "- Overall impact on professional development and career readiness\n\n";

        $prompt .= "### 2. WEEKLY PROGRESSION ANALYSIS (2-3 paragraphs per week)\n";
        $prompt .= "Organize the OJT experience into weeks (assume 5 working days per week).\n";
        $prompt .= "For EACH week, provide detailed coverage:\n";
        $prompt .= "- **Week Number and Date Range** (e.g., \"Week 1: January 6-10, 2025\")\n";
        $prompt .= "- **Primary Activities**: Main tasks, projects, and responsibilities undertaken\n";
        $prompt .= "- **Skills Development**: Specific technical and soft skills acquired or enhanced\n";
        $prompt .= "- **Challenges and Solutions**: Obstacles encountered and problem-solving approaches\n";
        $prompt .= "- **Key Achievements**: Notable accomplishments, completed milestones, or recognition\n";
        $prompt .= "- **Learning Insights**: Important realizations or connections to academic knowledge\n\n";

        $prompt .= "### 3. DAY-BY-DAY DETAILED ACCOUNT (100-150 words per day)\n";
        $prompt .= "For EACH day in the journal, write a comprehensive paragraph covering:\n";
        $prompt .= "- **Specific Tasks**: Detailed description of activities performed and methodologies used\n";
        $prompt .= "- **Technical Application**: Tools, technologies, frameworks, or systems utilized\n";
        $prompt .= "- **Learning Outcomes**: New knowledge gained, skills practiced, or concepts understood\n";
        $prompt .= "- **Professional Growth**: How this day contributed to overall development\n";
        $prompt .= "- **Continuity**: Reference to previous days' work to show progression and building complexity\n";
        $prompt .= "Write in chronological order with smooth transitional phrases between days.\n\n";

        $prompt .= "### 4. SKILLS AND COMPETENCIES FRAMEWORK (analytical breakdown)\n";
        $prompt .= "Categorize and analyze all skills developed during the OJT:\n";
        $prompt .= "- **Technical Skills**: Programming languages, software tools, frameworks, methodologies\n";
        $prompt .= "  • For each skill, specify the proficiency level achieved and provide concrete examples\n";
        $prompt .= "- **Soft Skills**: Communication, teamwork, time management, adaptability, leadership\n";
        $prompt .= "  • Illustrate with specific situations where these skills were demonstrated\n";
        $prompt .= "- **Professional Competencies**: Work ethic, problem-solving, critical thinking, industry awareness\n";
        $prompt .= "  • Explain how these were developed through real-world experience\n";
        $prompt .= "Show progression from initial skill level to competent application.\n\n";

        $prompt .= "### 5. CHALLENGES, PROBLEM-SOLVING, AND LESSONS LEARNED (reflective analysis)\n";
        $prompt .= "Identify and analyze significant challenges faced:\n";
        $prompt .= "- **Challenge Description**: What made it difficult? (technical complexity, time pressure, knowledge gaps)\n";
        $prompt .= "- **Problem-Solving Approach**: Steps taken to address the challenge\n";
        $prompt .= "- **Resources Utilized**: Documentation, mentors, online resources, collaboration\n";
        $prompt .= "- **Resolution**: How was it ultimately resolved?\n";
        $prompt .= "- **Lessons Learned**: What insights were gained? How did it contribute to growth?\n\n";

        $prompt .= "### 6. CONCLUSION AND FUTURE APPLICATION (1-2 substantial paragraphs)\n";
        $prompt .= "Provide a comprehensive conclusion:\n";
        $prompt .= "- **Overall Experience Summary**: Holistic view of the OJT journey\n";
        $prompt .= "- **Career Impact**: How this experience shapes career goals and direction\n";
        $prompt .= "- **Future Application**: How will these learnings be applied in future endeavors?\n";
        $prompt .= "- **Professional Transformation**: Reflection on growth from start to finish\n";
        $prompt .= "- **Recommendations**: Advice for future OJT students or improvements to the program\n\n";

        $prompt .= "WRITING STANDARDS AND GUIDELINES:\n";
        $prompt .= "✓ **Tone**: Formal, professional, academic writing suitable for institutional documentation\n";
        $prompt .= "✓ **Perspective**: Third person past tense (e.g., 'The intern developed...', 'Tasks were completed...')\n";
        $prompt .= "✓ **Language**: Rich vocabulary, varied sentence structures, industry-appropriate terminology\n";
        $prompt .= "✓ **Specificity**: Use concrete details, specific technologies, actual project names from the entries\n";
        $prompt .= "✓ **Flow**: Smooth transitions between sections, paragraphs, and individual days\n";
        $prompt .= "✓ **Depth**: Provide thorough analysis, not just surface-level descriptions\n";
        $prompt .= "✓ **Evidence**: Support claims with specific examples from the journal entries\n";
        $prompt .= "✓ **Avoid**: Generic statements, repetitive phrases, starting with 'Today' or 'This week'\n";
        $prompt .= "✓ **Length**: Target 1500-2500 words for comprehensive documentation\n";
        $prompt .= "✓ **Formatting**: Use clear section headings, proper paragraph breaks, and professional formatting\n\n";

        // Type-specific emphasis
        $prompt .= $this->getTypeSpecificInstructions($type);

        return $prompt;
    }

    /**
     * Get type-specific instructions for narrative
     */
    private function getTypeSpecificInstructions(string $type): string {
        switch ($type) {
            case 'weekly_summary':
                return "EMPHASIS: Focus on weekly progression and cumulative learning. Highlight how each week built upon the previous one.\n\n";
            
            case 'final_report':
                return "EMPHASIS: Provide comprehensive coverage suitable for final submission. Include all major projects, achievements, and transformative moments. This is the definitive account of the OJT experience.\n\n";
            
            case 'reflective':
                return "EMPHASIS: Emphasize personal insights, internal growth, and perspective changes. Include deeper reflection on how experiences challenged assumptions and shaped professional identity.\n\n";
            
            case 'technical_focus':
                return "EMPHASIS: Prioritize technical depth. Detail specific technologies, architectures, algorithms, and implementations. Include technical challenges and their solutions. Use appropriate technical terminology.\n\n";
            
            case 'growth_focus':
                return "EMPHASIS: Highlight the transformation arc. Show clear before/after comparisons of skills and confidence. Emphasize moments of breakthrough learning and increasing independence.\n\n";
            
            default:
                return "EMPHASIS: Provide balanced coverage of all aspects: activities, learning, challenges, and growth. Maintain comprehensive documentation standards.\n\n";
        }
    }

    /**
     * Get enhanced system message for narrative type
     */
    private function getEnhancedSystemMessageForType(string $type): string {
        $messages = [
            'weekly_summary' => 'You are a professional academic writer specializing in OJT documentation. Create detailed weekly progress reports with comprehensive day-by-day coverage. Use formal tone and ensure thorough documentation.',
            'final_report' => 'You are an expert technical writer creating definitive OJT final reports. Produce comprehensive, publication-quality documentation suitable for academic submission. Include all major achievements and detailed analysis.',
            'reflective' => 'You are a reflective writing coach and career counselor. Help students articulate deep learnings and personal transformation. Balance professional tone with meaningful introspection.',
            'technical_focus' => 'You are a senior technical writer with industry expertise. Create detailed technical documentation that demonstrates deep understanding of technologies and methodologies used.',
            'growth_focus' => 'You are a career development specialist. Highlight transformation, skill progression, and increasing professional maturity. Emphasize growth milestones.',
            'standard' => 'You are a professional academic documentation specialist. Create comprehensive, well-structured OJT narrative reports with detailed coverage of all activities, learnings, and professional development.'
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
