/**
 * AI Chatbot Widget
 * Floating chat interface for OJT Journal
 */

// Chatbot state
const ChatbotWidget = {
    isOpen: false,
    isTyping: false,
    conversationId: null,
    
    // Initialize widget
    init() {
        this.createWidget();
        this.attachEvents();
        this.loadHistory();
    },
    
    // Create chatbot HTML
    createWidget() {
        const widgetHTML = `
            <!-- Chatbot Toggle Button -->
            <button id="chatbotToggle" class="chatbot-toggle" aria-label="Open chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span class="chatbot-badge">!</span>
            </button>
            
            <!-- Chatbot Window -->
            <div id="chatbotWindow" class="chatbot-window">
                <div class="chatbot-header">
                    <div class="chatbot-title">
                        <span class="chatbot-icon">🤖</span>
                        <div>
                            <h4>OJT Assistant</h4>
                            <p class="chatbot-status">Online</p>
                        </div>
                    </div>
                    <div class="chatbot-actions">
                        <button id="chatbotClear" class="chatbot-action-btn" title="Clear conversation">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                        <button id="chatbotClose" class="chatbot-action-btn" title="Close chat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div id="chatbotMessages" class="chatbot-messages">
                    <div class="chatbot-welcome">
                        <div class="welcome-icon">👋</div>
                        <h4>Welcome! I'm your OJT Assistant</h4>
                        <p>I can help you with:</p>
                        <ul class="welcome-features">
                            <li>✍️ Writing journal entries</li>
                            <li>📝 Report formatting</li>
                            <li>💡 OJT tips and best practices</li>
                            <li>❓ Answering your questions</li>
                        </ul>
                        <p class="welcome-try">Try asking:</p>
                        <div class="suggestion-chips">
                            <button class="chip" data-message="How do I write a good journal entry?">How to write entries?</button>
                            <button class="chip" data-message="What should I include in my report?">Report format?</button>
                            <button class="chip" data-message="Tips for OJT success">OJT tips</button>
                        </div>
                    </div>
                </div>
                
                <div class="chatbot-typing" id="chatbotTyping">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
                
                <div class="chatbot-input-container">
                    <input 
                        type="text" 
                        id="chatbotInput" 
                        class="chatbot-input" 
                        placeholder="Ask me anything..."
                        maxlength="500"
                    />
                    <button id="chatbotSend" class="chatbot-send" aria-label="Send message">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
    },
    
    // Attach event listeners
    attachEvents() {
        // Toggle button
        document.getElementById('chatbotToggle').addEventListener('click', () => this.toggle());
        
        // Close button
        document.getElementById('chatbotClose').addEventListener('click', () => this.close());
        
        // Clear button
        document.getElementById('chatbotClear').addEventListener('click', () => this.clear());
        
        // Send button
        document.getElementById('chatbotSend').addEventListener('click', () => this.sendMessage());
        
        // Enter key
        document.getElementById('chatbotInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
        
        // Suggestion chips
        document.querySelectorAll('.chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const message = chip.dataset.message;
                document.getElementById('chatbotInput').value = message;
                this.sendMessage();
            });
        });
    },
    
    // Toggle chat visibility
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    },
    
    // Open chat
    open() {
        const window = document.getElementById('chatbotWindow');
        const toggle = document.getElementById('chatbotToggle');
        
        window.classList.add('open');
        toggle.classList.add('hidden');
        this.isOpen = true;
        
        // Focus input
        setTimeout(() => {
            document.getElementById('chatbotInput').focus();
        }, 300);
        
        // Scroll to bottom
        this.scrollToBottom();
    },
    
    // Close chat
    close() {
        const window = document.getElementById('chatbotWindow');
        const toggle = document.getElementById('chatbotToggle');
        
        window.classList.remove('open');
        toggle.classList.remove('hidden');
        this.isOpen = false;
    },
    
    // Send message
    async sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        
        if (!message || this.isTyping) return;
        
        // Add user message
        this.addMessage(message, 'user');
        
        // Clear input
        input.value = '';
        
        // Show typing indicator
        this.showTyping();
        
        try {
            // Get CSRF token
            const csrfToken = await this.getCSRFToken();
            
            // Send to server
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('message', message);
            
            const response = await fetch('src/process.php?action=chatbot/send', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            // Hide typing
            this.hideTyping();
            
            if (result.success) {
                this.addMessage(result.message, 'assistant');
                this.conversationId = result.conversation_id;
            } else {
                this.addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
            }
            
        } catch (error) {
            console.error('Chatbot error:', error);
            this.hideTyping();
            this.addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
        }
    },
    
    // Add message to chat
    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbotMessages');
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = text;
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeDiv);
        
        messagesContainer.appendChild(messageDiv);
        
        // Scroll to bottom
        this.scrollToBottom();
    },
    
    // Show typing indicator
    showTyping() {
        this.isTyping = true;
        document.getElementById('chatbotTyping').classList.add('show');
        this.scrollToBottom();
    },
    
    // Hide typing indicator
    hideTyping() {
        this.isTyping = false;
        document.getElementById('chatbotTyping').classList.remove('show');
    },
    
    // Clear conversation
    async clear() {
        if (!confirm('Clear conversation history?')) return;
        
        try {
            const csrfToken = await this.getCSRFToken();
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            
            await fetch('src/process.php?action=chatbot/clear', {
                method: 'POST',
                body: formData
            });
            
            // Clear UI
            const messagesContainer = document.getElementById('chatbotMessages');
            messagesContainer.innerHTML = `
                <div class="chatbot-welcome">
                    <div class="welcome-icon">👋</div>
                    <h4>Conversation Cleared</h4>
                    <p>How can I help you now?</p>
                </div>
            `;
            
            this.conversationId = null;
            
        } catch (error) {
            console.error('Clear error:', error);
        }
    },
    
    // Load conversation history
    async loadHistory() {
        try {
            const response = await fetch('src/process.php?action=chatbot/history');
            const result = await response.json();
            
            if (result.success && result.history && result.history.length > 0) {
                this.conversationId = result.conversation_id;
                
                // Clear welcome message
                const messagesContainer = document.getElementById('chatbotMessages');
                messagesContainer.innerHTML = '';
                
                // Add history
                result.history.forEach(msg => {
                    this.addMessage(msg.content, msg.role);
                });
                
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Load history error:', error);
        }
    },
    
    // Scroll to bottom
    scrollToBottom() {
        const messagesContainer = document.getElementById('chatbotMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    },
    
    // Get CSRF token
    async getCSRFToken() {
        try {
            const response = await fetch('src/process.php?action=getCSRFToken');
            const data = await response.json();
            return data.csrf_token || '';
        } catch (error) {
            console.error('CSRF error:', error);
            return '';
        }
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    ChatbotWidget.init();
});
