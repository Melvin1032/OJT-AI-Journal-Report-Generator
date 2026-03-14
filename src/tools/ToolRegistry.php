<?php
/**
 * Tool Registry
 * 
 * Central registry for all agent tools
 * Tools are reusable functions that agents can call
 */

class ToolRegistry {
    private static ?ToolRegistry $instance = null;
    private array $tools = [];
    
    private function __construct() {
        $this->registerDefaultTools();
    }
    
    public static function getInstance(): ToolRegistry {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Register a tool
     */
    public function register(string $name, callable $tool, string $description = ''): void {
        $this->tools[$name] = [
            'callable' => $tool,
            'description' => $description
        ];
    }
    
    /**
     * Get a tool
     */
    public function get(string $name): ?callable {
        return $this->tools[$name]['callable'] ?? null;
    }
    
    /**
     * Get all tool names
     */
    public function getToolNames(): array {
        return array_keys($this->tools);
    }
    
    /**
     * Get tool description
     */
    public function getDescription(string $name): string {
        return $this->tools[$name]['description'] ?? '';
    }
    
    /**
     * Execute a tool
     */
    public function execute(string $name, array $params = []) {
        $tool = $this->get($name);
        if ($tool === null) {
            throw new Exception("Tool '{$name}' not found");
        }
        return call_user_func($tool, $params);
    }
    
    /**
     * Register all default tools
     */
    private function registerDefaultTools(): void {
        // Database Tools
        $this->register('fetchEntries', 
            [$this, 'fetchEntries'],
            'Fetch OJT journal entries from database');
        
        $this->register('fetchEntryById',
            [$this, 'fetchEntryById'],
            'Fetch a specific OJT entry by ID');
        
        $this->register('getStudentInfo',
            [$this, 'getStudentInfo'],
            'Get stored student information');
        
        $this->register('getEntryImages',
            [$this, 'getEntryImages'],
            'Get images associated with an entry');
        
        // AI Analysis Tools
        $this->register('analyzeImage',
            [$this, 'analyzeImage'],
            'Analyze an image using AI vision');
        
        $this->register('enhanceDescription',
            [$this, 'enhanceDescription'],
            'Enhance a description using AI');
        
        $this->register('generateNarrative',
            [$this, 'generateNarrative'],
            'Generate a narrative report from entries');
        
        $this->register('summarizeEntries',
            [$this, 'summarizeEntries'],
            'Summarize multiple entries into key points');
        
        // Quality Tools
        $this->register('checkCompleteness',
            [$this, 'checkCompleteness'],
            'Check if entries have all required information');
        
        $this->register('suggestImprovements',
            [$this, 'suggestImprovements'],
            'Suggest improvements for entries');
        
        // Utility Tools
        $this->register('getDateRange',
            [$this, 'getDateRange'],
            'Get entries within a date range');
        
        $this->register('countEntries',
            [$this, 'countEntries'],
            'Count total entries');
    }
    
    // ==================== TOOL IMPLEMENTATIONS ====================
    
    /**
     * Fetch OJT journal entries
     */
    public function fetchEntries(array $params = []): array {
        $pdo = getDbConnection();
        $limit = $params['limit'] ?? 100;
        $orderBy = $params['order_by'] ?? 'entry_date ASC';
        
        $stmt = $pdo->prepare("
            SELECT id, title, user_description, ai_enhanced_description, entry_date, created_at
            FROM ojt_entries
            ORDER BY {$orderBy}
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch a specific entry by ID
     */
    public function fetchEntryById(array $params = []): ?array {
        $pdo = getDbConnection();
        $id = $params['id'] ?? null;
        
        if ($id === null) {
            return null;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, title, user_description, ai_enhanced_description, entry_date, created_at
            FROM ojt_entries
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Get student information
     */
    public function getStudentInfo(array $params = []): ?array {
        $pdo = getDbConnection();
        
        try {
            $stmt = $pdo->query("SELECT * FROM student_info LIMIT 1");
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get images for an entry
     */
    public function getEntryImages(array $params = []): array {
        $pdo = getDbConnection();
        $entryId = $params['entry_id'] ?? null;
        
        if ($entryId === null) {
            return [];
        }
        
        $stmt = $pdo->prepare("
            SELECT id, image_path, image_order, ai_description
            FROM entry_images
            WHERE entry_id = :entry_id
            ORDER BY image_order ASC
        ");
        $stmt->bindValue(':entry_id', $entryId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Analyze an image using AI
     */
    public function analyzeImage(array $params = []): string {
        $imagePath = $params['image_path'] ?? null;
        
        if ($imagePath === null || !file_exists($imagePath)) {
            return 'Image not found';
        }
        
        // Use existing analyzeImageWithQwen function
        return analyzeImageWithQwen($imagePath);
    }
    
    /**
     * Enhance a description using AI
     */
    public function enhanceDescription(array $params = []): string {
        $description = $params['description'] ?? '';
        $title = $params['title'] ?? 'OJT Activity';
        $imageContext = $params['image_context'] ?? '';
        
        if (empty($description)) {
            return 'No description provided';
        }
        
        // Use existing enhanceUserDescriptionWithAI function
        return enhanceUserDescriptionWithAI($description, $title, $imageContext);
    }
    
    /**
     * Generate narrative from entries
     */
    public function generateNarrative(array $params = []): string {
        $entries = $params['entries'] ?? [];
        
        if (empty($entries)) {
            return 'No entries to generate narrative from';
        }
        
        // Build context from entries
        $entriesContext = [];
        foreach ($entries as $entry) {
            $date = date('M j', strtotime($entry['entry_date']));
            $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: 'No description';
            $entriesContext[] = "{$date}: {$entry['title']} - " . substr($desc, 0, 150);
        }
        
        $contextText = implode("\n", $entriesContext);
        
        $prompt = "Write a 2-paragraph OJT weekly narrative report:\n";
        $prompt .= "Paragraph 1: Summarize activities and skills developed\n";
        $prompt .= "Paragraph 2: Challenges overcome and professional growth\n\n";
        $prompt .= "Entries:\n{$contextText}\n\n";
        $prompt .= "Professional tone, 100-150 words.";
        
        return callAIAPI($prompt, 'Write OJT narrative report. Professional tone.', QWEN_TEXT_MODEL);
    }
    
    /**
     * Summarize entries into key points
     */
    public function summarizeEntries(array $params = []): array {
        $entries = $params['entries'] ?? [];
        
        if (empty($entries)) {
            return ['summary' => 'No entries to summarize'];
        }
        
        $entriesText = '';
        foreach ($entries as $entry) {
            $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: '';
            $entriesText .= "- {$entry['title']}: {$desc}\n";
        }
        
        $prompt = "Summarize these OJT entries into key points:\n\n{$entriesText}\n\n";
        $prompt .= "Return as a JSON object with:\n";
        $prompt .= "- skills_learned: array of skills\n";
        $prompt .= "- main_activities: array of main activities\n";
        $prompt .= "- challenges: array of challenges faced\n";
        $prompt .= "- achievements: array of accomplishments\n\n";
        $prompt .= "Return ONLY valid JSON.";
        
        $response = callAIAPI($prompt, 'Extract key information. Return only JSON.', QWEN_TEXT_MODEL);
        
        // Extract JSON from response
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
        
        return ['summary' => $response];
    }
    
    /**
     * Check completeness of entries
     */
    public function checkCompleteness(array $params = []): array {
        $entries = $params['entries'] ?? [];
        $results = [];
        
        foreach ($entries as $entry) {
            $issues = [];
            
            if (empty($entry['title'])) {
                $issues[] = 'Missing title';
            }
            
            if (empty($entry['user_description']) && empty($entry['ai_enhanced_description'])) {
                $issues[] = 'Missing description';
            }
            
            if (strlen($entry['user_description'] ?? '') < 20) {
                $issues[] = 'Description too short';
            }
            
            $results[] = [
                'entry_id' => $entry['id'],
                'title' => $entry['title'],
                'complete' => empty($issues),
                'issues' => $issues
            ];
        }
        
        return $results;
    }
    
    /**
     * Suggest improvements for entries
     */
    public function suggestImprovements(array $params = []): array {
        $entries = $params['entries'] ?? [];
        $suggestions = [];
        
        foreach ($entries as $entry) {
            $desc = $entry['ai_enhanced_description'] ?: $entry['user_description'] ?: '';
            
            $prompt = "Review this OJT journal entry and suggest improvements:\n\n";
            $prompt .= "Title: {$entry['title']}\n";
            $prompt .= "Description: {$desc}\n\n";
            $prompt .= "Provide 2-3 specific suggestions to make this entry more detailed and professional.\n";
            $prompt .= "Return as JSON array of suggestions.";
            
            $response = callAIAPI($prompt, 'Provide helpful improvement suggestions.', QWEN_TEXT_MODEL);
            
            // Extract JSON array
            if (preg_match('/\[.*\]/s', $response, $matches)) {
                $parsed = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $suggestions[] = [
                        'entry_id' => $entry['id'],
                        'suggestions' => $parsed
                    ];
                    continue;
                }
            }
            
            $suggestions[] = [
                'entry_id' => $entry['id'],
                'suggestions' => [$response]
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Get entries within a date range
     */
    public function getDateRange(array $params = []): array {
        $pdo = getDbConnection();
        $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $params['end_date'] ?? date('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT id, title, user_description, ai_enhanced_description, entry_date
            FROM ojt_entries
            WHERE entry_date BETWEEN :start AND :end
            ORDER BY entry_date ASC
        ");
        $stmt->bindValue(':start', $startDate);
        $stmt->bindValue(':end', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count total entries
     */
    public function countEntries(array $params = []): int {
        $pdo = getDbConnection();
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ojt_entries");
        $result = $stmt->fetch();
        
        return (int)($result['count'] ?? 0);
    }
}
