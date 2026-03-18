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

// Load API helpers if not already loaded
if (!function_exists('callAIWithUserKeys')) {
    require_once __DIR__ . '/../api_helpers.php';
}

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
    protected function callAI(string $prompt, string $systemMessage = '', array $options = []): string {
        // Build messages array
        $messages = [];
        if (!empty($systemMessage)) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Use user-specific API keys via helper with options
        try {
            return callAIWithUserKeys($messages, '', $options);
        } catch (Exception $e) {
            Logger::error('AI call failed in BaseAgent', ['error' => $e->getMessage()]);
            throw $e;
        }
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
