<?php
/**
 * Base Agent Class
 * 
 * Provides the foundation for AI agents with:
 * - Planning and reasoning capabilities
 * - Tool execution framework
 * - Memory and context management
 * - Multi-step task orchestration
 */

abstract class BaseAgent {
    protected array $tools = [];
    protected array $memory = [];
    protected array $executionLog = [];
    protected int $maxSteps = AGENT_MAX_STEPS;
    protected float $temperature = AGENT_TEMPERATURE;
    
    /**
     * Register a tool with the agent
     */
    public function registerTool(string $name, callable $tool): self {
        $this->tools[$name] = $tool;
        return $this;
    }
    
    /**
     * Get available tool names
     */
    public function getAvailableTools(): array {
        return array_keys($this->tools);
    }
    
    /**
     * Execute a tool
     */
    protected function executeTool(string $name, array $params = []) {
        if (!isset($this->tools[$name])) {
            throw new Exception("Tool '{$name}' not found");
        }
        
        $this->log("Executing tool: {$name}", $params);
        $result = call_user_func($this->tools[$name], $params);
        $this->log("Tool '{$name}' completed", ['result' => $result]);
        
        return $result;
    }
    
    /**
     * Store in agent memory
     */
    protected function remember(string $key, $value): void {
        $this->memory[$key] = $value;
    }
    
    /**
     * Recall from agent memory
     */
    protected function recall(string $key, $default = null) {
        return $this->memory[$key] ?? $default;
    }
    
    /**
     * Log agent action
     */
    protected function log(string $message, array $context = []): void {
        $this->executionLog[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'context' => $context
        ];
        Logger::info("Agent: {$message}", $context);
    }
    
    /**
     * Get execution log
     */
    public function getExecutionLog(): array {
        return $this->executionLog;
    }
    
    /**
     * Clear memory and log
     */
    public function reset(): void {
        $this->memory = [];
        $this->executionLog = [];
    }
    
    /**
     * Main agent execution method - to be implemented by subclasses
     */
    abstract public function execute(string $goal, array $context = []): array;
    
    /**
     * Plan steps to achieve goal using AI
     */
    protected function plan(string $goal, array $context = []): array {
        $toolsList = implode(', ', $this->getAvailableTools());
        
        $prompt = "You are a planning agent. Create a step-by-step plan to achieve this goal.\n\n";
        $prompt .= "GOAL: {$goal}\n\n";
        $prompt .= "AVAILABLE TOOLS: {$toolsList}\n\n";
        
        if (!empty($context)) {
            $prompt .= "CONTEXT:\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
        }
        
        $prompt .= "Create a JSON array of steps. Each step should have:\n";
        $prompt .= "- tool: which tool to use\n";
        $prompt .= "- input: what input to pass to the tool\n";
        $prompt .= "- reason: why this step is needed\n\n";
        $prompt .= "Return ONLY the JSON array, no other text.";
        
        $response = $this->callAI($prompt, "You are a planning assistant. Return only valid JSON.");
        
        // Extract JSON from response
        if (preg_match('/\[.*\]/s', $response, $matches)) {
            $plan = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->log("Generated plan", ['steps' => count($plan)]);
                return $plan ?? [];
            }
        }
        
        $this->log("Plan generation failed, using default plan");
        return $this->getDefaultPlan($goal);
    }
    
    /**
     * Get default plan when AI planning fails
     */
    protected function getDefaultPlan(string $goal): array {
        // Simple default plan based on goal keywords
        if (stripos($goal, 'narrative') !== false || stripos($goal, 'report') !== false) {
            return [
                ['tool' => 'fetchEntries', 'input' => [], 'reason' => 'Get journal entries'],
                ['tool' => 'generateNarrative', 'input' => [], 'reason' => 'Generate narrative from entries']
            ];
        }
        
        if (stripos($goal, 'analyze') !== false) {
            return [
                ['tool' => 'fetchEntries', 'input' => [], 'reason' => 'Get entries'],
                ['tool' => 'analyzeEntries', 'input' => [], 'reason' => 'Analyze entries']
            ];
        }
        
        return [
            ['tool' => 'fetchEntries', 'input' => [], 'reason' => 'Get data']
        ];
    }
    
    /**
     * Call AI API for reasoning/planning
     */
    protected function callAI(string $prompt, string $systemMessage = ''): string {
        // Try Groq first (fast and free)
        if (!empty(GROQ_API_KEY)) {
            try {
                return $this->callGroq($prompt, $systemMessage);
            } catch (Exception $e) {
                Logger::warning("Groq failed, falling back to Gemini", ['error' => $e->getMessage()]);
            }
        }
        
        // Fall back to Gemini
        if (!empty(GEMINI_API_KEY)) {
            try {
                return $this->callGemini($prompt, $systemMessage);
            } catch (Exception $e) {
                Logger::warning("Gemini failed, falling back to OpenRouter", ['error' => $e->getMessage()]);
            }
        }
        
        // Final fallback to existing OpenRouter
        return callAIAPI($prompt, $systemMessage, QWEN_TEXT_MODEL);
    }
    
    /**
     * Call Groq API (fast, free)
     */
    protected function callGroq(string $prompt, string $systemMessage = ''): string {
        $messages = [];
        
        if (!empty($systemMessage)) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }
        
        $messages[] = ['role' => 'user', 'content' => $prompt];
        
        $requestData = [
            'model' => GROQ_MODEL,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => 2048
        ];
        
        $ch = curl_init(GROQ_API_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . GROQ_API_KEY
            ],
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || $response === false) {
            throw new Exception("Groq API error: HTTP {$httpCode}");
        }
        
        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * Call Google Gemini API (free tier)
     */
    protected function callGemini(string $prompt, string $systemMessage = ''): string {
        $url = GEMINI_API_ENDPOINT . "/{$this->getModel()}:generateContent?key=" . GEMINI_API_KEY;
        
        $fullPrompt = $prompt;
        if (!empty($systemMessage)) {
            $fullPrompt = "System: {$systemMessage}\n\nUser: {$prompt}";
        }
        
        $requestData = [
            'contents' => [[
                'parts' => [['text' => $fullPrompt]]
            ]],
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => 2048
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || $response === false) {
            throw new Exception("Gemini API error: HTTP {$httpCode}");
        }
        
        $result = json_decode($response, true);
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
    
    /**
     * Get Gemini model name
     */
    protected function getModel(): string {
        return GEMINI_MODEL;
    }
    
    /**
     * Synthesize final result from tool outputs
     */
    protected function synthesize(string $goal, array $results): array {
        $resultsJson = json_encode($results, JSON_PRETTY_PRINT);
        
        $prompt = "Synthesize the following results into a coherent response for this goal:\n\n";
        $prompt .= "GOAL: {$goal}\n\n";
        $prompt .= "RESULTS:\n{$resultsJson}\n\n";
        $prompt .= "Provide a clear, well-structured response. Use markdown formatting where appropriate.";
        
        $response = $this->callAI($prompt, "You are a synthesis assistant. Create clear, well-organized responses.");
        
        return [
            'success' => true,
            'goal' => $goal,
            'result' => $response,
            'raw_data' => $results,
            'steps_taken' => count($this->executionLog)
        ];
    }
}
