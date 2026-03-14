<?php
/**
 * AI Chatbot
 * 
 * Intelligent chatbot assistant for OJT Journal Report Generator
 * Provides help, guidance, and answers to student questions
 */

require_once __DIR__ . '/../config/config.php';

class AIChatbot {
    private array $conversationHistory = [];
    private string $systemPrompt = '';
    
    public function __construct() {
        $this->initializeSystemPrompt();
    }
    
    /**
     * Initialize system prompt with OJT knowledge
     */
    private function initializeSystemPrompt(): void {
        $this->systemPrompt = <<<PROMPT
You are a helpful AI assistant for the OJT (On-the-Job Training) Journal Report Generator application.

YOUR ROLE:
- Help students with their OJT journal entries
- Answer questions about OJT requirements and best practices
- Provide guidance on writing effective journal entries
- Assist with report generation and formatting
- Offer tips for maximizing OJT experience

KNOWLEDGE BASE:

1. JOURNAL ENTRY BEST PRACTICES:
- Write entries daily or weekly
- Include specific tasks and activities
- Describe skills learned or applied
- Mention challenges and how you overcame them
- Reflect on what you learned
- Use professional language
- Aim for 50-200 words per entry
- Include titles that summarize the activity

2. OJT REPORT STRUCTURE:
Chapter I - Company Profile:
- Company background and history
- Vision, mission, and values
- Organizational structure
- Your role and department

Chapter II - Internship Activities:
- Overview of tasks and responsibilities
- Detailed description of major activities
- Skills applied and developed
- Challenges faced and solutions

Chapter III - Conclusion & Recommendations:
- Summary of overall experience
- Key learnings and insights
- Impact on professional development
- Recommendations for future interns

3. COMMON QUESTIONS:
- "How do I write a good journal entry?" → Be specific, include tasks, skills, and learnings
- "What should I include?" → Activities, skills learned, challenges, reflections
- "How long should entries be?" → 50-200 words, quality over quantity
- "Can I use AI?" → Yes, use AI to enhance descriptions, not replace your input
- "How often should I write?" → Daily or weekly, consistency is key
- "What if I missed a day?" → Combine multiple days, be honest about the timeline

4. TIPS FOR SUCCESS:
- Document activities as they happen
- Take photos (with permission) for reference
- Ask questions and seek feedback
- Network with professionals
- Set learning goals
- Reflect on progress regularly

5. TECHNICAL HELP:
- Image upload: Supports JPG, PNG, GIF, WebP (max 5MB)
- AI enhancement: Automatically improves descriptions
- Report generation: Creates formatted ISPSC-style reports
- Dashboard: Access AI Agents from the dashboard

TONE & STYLE:
- Friendly and encouraging
- Professional but approachable
- Clear and concise
- Supportive of student learning

RESPONSE FORMAT:
- Keep responses concise (100-200 words)
- Use bullet points for clarity when needed
- Include examples when helpful
- End with encouragement or next steps

LIMITATIONS:
- You cannot access or modify user data directly
- You cannot perform actions like creating entries
- You provide guidance, users must take actions themselves
PROMPT;
    }
    
    /**
     * Process user message and generate response
     */
    public function chat(string $message, array $context = []): array {
        try {
            // Build conversation history
            $this->conversationHistory[] = [
                'role' => 'user',
                'content' => $message,
                'timestamp' => time()
            ];
            
            // Keep only last 10 messages to avoid token limits
            if (count($this->conversationHistory) > 10) {
                array_shift($this->conversationHistory);
            }
            
            // Build messages for API
            $messages = [
                ['role' => 'system', 'content' => $this->systemPrompt]
            ];
            
            // Add conversation history
            foreach ($this->conversationHistory as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }
            
            // Call AI API
            $response = $this->callAI($messages);
            
            // Add assistant response to history
            $this->conversationHistory[] = [
                'role' => 'assistant',
                'content' => $response,
                'timestamp' => time()
            ];
            
            return [
                'success' => true,
                'message' => $response,
                'conversation_id' => $this->getConversationId(),
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            Logger::error('Chatbot error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => "I'm sorry, I'm having trouble responding right now. Please try again.",
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Call AI API for chat response
     */
    private function callAI(array $messages): string {
        // Use Groq for fast responses (if available)
        if (!empty(GROQ_API_KEY)) {
            try {
                return $this->callGroq($messages);
            } catch (Exception $e) {
                // Fallback to other APIs
            }
        }
        
        // Use Gemini (if available)
        if (!empty(GEMINI_API_KEY)) {
            try {
                return $this->callGemini($messages);
            } catch (Exception $e) {
                // Fallback to OpenRouter
            }
        }
        
        // Final fallback to OpenRouter
        return $this->callOpenRouter($messages);
    }
    
    /**
     * Call Groq API
     */
    private function callGroq(array $messages): string {
        $requestData = [
            'model' => GROQ_MODEL,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500
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
     * Call Gemini API
     */
    private function callGemini(array $messages): string {
        $url = GEMINI_API_ENDPOINT . "/gemini-2.0-flash-exp:generateContent?key=" . GEMINI_API_KEY;
        
        // Convert messages to Gemini format
        $lastMessage = end($messages);
        $prompt = $lastMessage['content'] ?? '';
        
        $requestData = [
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 500
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
     * Call OpenRouter API
     */
    private function callOpenRouter(array $messages): string {
        $requestData = [
            'model' => QWEN_TEXT_MODEL,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500
        ];
        
        $ch = curl_init(QWEN_API_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . QWEN_API_KEY,
                'HTTP-Referer: http://localhost:8000',
                'X-Title: OJT Journal Chatbot'
            ],
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || $response === false) {
            throw new Exception("OpenRouter API error: HTTP {$httpCode}");
        }
        
        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * Get conversation ID (session-based)
     */
    private function getConversationId(): string {
        if (!isset($_SESSION['chatbot_conversation_id'])) {
            $_SESSION['chatbot_conversation_id'] = uniqid('chat_');
        }
        return $_SESSION['chatbot_conversation_id'];
    }
    
    /**
     * Clear conversation history
     */
    public function clearHistory(): void {
        $this->conversationHistory = [];
    }
    
    /**
     * Get conversation history
     */
    public function getHistory(): array {
        return $this->conversationHistory;
    }
}
