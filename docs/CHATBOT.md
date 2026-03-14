# AI Chatbot Documentation

## 🤖 Overview

The OJT Journal now includes an **AI-powered chatbot assistant** that helps students with their OJT journey by answering questions, providing guidance, and offering tips.

---

## ✨ Features

### 🎯 What the Chatbot Can Do

1. **Answer OJT Questions**
   - Journal entry best practices
   - Report formatting guidelines
   - OJT requirements and expectations

2. **Provide Writing Guidance**
   - How to write effective entries
   - What to include in descriptions
   - Tips for professional language

3. **Offer Tips & Best Practices**
   - Success strategies
   - Common mistakes to avoid
   - Maximizing learning outcomes

4. **Technical Support**
   - How to use the application
   - Feature explanations
   - Troubleshooting help

---

## 🎨 User Interface

### Floating Chat Button
- **Location**: Bottom-right corner of the screen
- **Appearance**: Circular button with chat icon
- **Badge**: Red notification badge indicates new messages
- **Action**: Click to open/close chat window

### Chat Window
- **Header**: 
  - Bot avatar and name ("OJT Assistant")
  - Online status indicator
  - Clear conversation button
  - Close button

- **Message Area**:
  - Welcome message with suggestions
  - Conversation history
  - Typing indicator when bot is responding

- **Input Area**:
  - Text input field
  - Send button
  - Press Enter to send

### Suggestion Chips
Quick-start questions:
- "How to write entries?"
- "Report format?"
- "OJT tips"

---

## 🚀 Usage

### Opening the Chatbot

1. **Click the floating button** in the bottom-right corner
2. **Chat window opens** with welcome message
3. **Type your question** or click a suggestion chip

### Having a Conversation

1. **Type your question** in the input field
2. **Press Enter** or click the send button
3. **Wait for response** (typing indicator shows)
4. **Read the answer** and ask follow-up questions

### Clearing History

1. **Click the trash icon** in the chat header
2. **Confirm** you want to clear
3. **Conversation resets** to welcome screen

---

## 💬 Example Conversations

### Example 1: Journal Entry Help
```
You: How do I write a good journal entry?

Bot: To write an effective OJT journal entry:

1. **Be Specific**: Describe exact tasks you performed
2. **Include Skills**: Mention what skills you used or learned
3. **Note Challenges**: Document problems and solutions
4. **Reflect**: Add what you learned from the experience
5. **Use Professional Language**: Write formally

Example:
"Developed a user authentication system using PHP sessions. 
Implemented password_hash() for security and learned about 
SQL injection prevention. Fixed session timeout issues."

Aim for 50-200 words per entry. Quality over quantity!
```

### Example 2: Report Format
```
You: What should I include in my report?

Bot: Your OJT report should have 3 chapters:

**Chapter I - Company Profile:**
- Company background
- Vision & mission
- Organizational structure
- Your role

**Chapter II - Activities:**
- Overview of tasks
- Detailed activities
- Skills applied
- Challenges & solutions

**Chapter III - Conclusion:**
- Summary of experience
- Key learnings
- Recommendations

Use the Portfolio Agent to auto-generate this!
```

### Example 3: Tips
```
You: Any tips for OJT success?

Bot: Here are key tips for OJT success:

✅ **Document Daily**: Write entries as they happen
✅ **Ask Questions**: Seek clarification when unsure
✅ **Take Initiative**: Volunteer for new tasks
✅ **Network**: Build professional relationships
✅ **Set Goals**: Define what you want to learn
✅ **Get Feedback**: Ask for regular evaluations
✅ **Be Professional**: Punctual, respectful, eager
✅ **Save Evidence**: Keep work samples (if allowed)

Remember: OJT is your bridge from school to career!
```

---

## 🧠 Knowledge Base

The chatbot is trained on:

### 1. Journal Entry Best Practices
- Writing frequency (daily/weekly)
- Content requirements
- Length guidelines (50-200 words)
- Professional language use
- Reflection techniques

### 2. Report Structure
- Chapter I: Company Profile
- Chapter II: Internship Activities
- Chapter III: Conclusion & Recommendations
- Formatting requirements
- ISPSC style guidelines

### 3. Common Questions
- Entry writing techniques
- Report content requirements
- AI usage guidelines
- Missing entry handling
- Timeline management

### 4. Application Features
- Image upload (JPG, PNG, GIF, WebP, max 5MB)
- AI enhancement
- Report generation
- Agent dashboard
- Download options

### 5. Success Tips
- Documentation strategies
- Professional development
- Networking advice
- Goal setting
- Time management

---

## 🔧 Technical Details

### Architecture

```
User Interface (chatbot.js)
    ↓
API Endpoint (process.php)
    ↓
AIChatbot Class (AIChatbot.php)
    ↓
AI API (Groq → Gemini → OpenRouter)
```

### Files

| File | Purpose |
|------|---------|
| `src/chatbot/AIChatbot.php` | Chatbot logic & AI integration |
| `assets/js/chatbot.js` | Frontend widget |
| `assets/css/style.css` | Chatbot styles |
| `src/process.php` | API endpoints |

### API Endpoints

- `src/process.php?action=chatbot/send` - Send message
- `src/process.php?action=chatbot/clear` - Clear history
- `src/process.php?action=chatbot/history` - Get history

### Conversation Storage

- **Method**: Session-based
- **Duration**: Browser session
- **History**: Last 10 messages
- **ID**: Unique per session

---

## 🎯 Best Practices

### For Users

1. **Ask Specific Questions**
   - ✅ "How long should journal entries be?"
   - ❌ "Tell me about entries"

2. **Use Suggestion Chips**
   - Quick starters for common questions
   - Good for first-time users

3. **Have Conversations**
   - Ask follow-up questions
   - Request clarification if needed

4. **Clear History When Needed**
   - Start fresh for new topics
   - Remove outdated context

### For Developers

1. **Extending Knowledge Base**
   - Edit `AIChatbot.php` system prompt
   - Add new Q&A pairs
   - Update best practices

2. **Customizing Responses**
   - Adjust temperature (currently 0.7)
   - Modify max tokens (currently 500)
   - Change system prompt tone

3. **Adding Features**
   - Quick reply buttons
   - Rich message formatting
   - File attachment support
   - Voice input

---

## 🔮 Future Enhancements

Planned features:

- [ ] **Context Awareness**: Know user's current entries
- [ ] **Personalized Suggestions**: Based on entry history
- [ ] **Voice Input**: Speech-to-text support
- [ ] **Quick Actions**: Insert suggestions into entries
- [ ] **Multi-language**: Support for Filipino/regional languages
- [ ] **Export Chat**: Download conversation as PDF
- [ ] **Feedback System**: Rate helpful responses
- [ ] **Analytics**: Track common questions

---

## 🐛 Troubleshooting

### Chatbot Not Responding

**Problem**: No response after sending message

**Solutions**:
1. Check internet connection
2. Verify API keys in `.env`
3. Check browser console for errors
4. Try clearing conversation

### Typing Indicator Stuck

**Problem**: Typing dots show forever

**Solutions**:
1. Wait 30 seconds for timeout
2. Refresh the page
3. Check server logs
4. Verify API endpoint

### Messages Not Saving

**Problem**: History clears on refresh

**Solutions**:
1. Check session configuration
2. Ensure cookies are enabled
3. Don't use incognito mode
4. Check PHP session settings

---

## 📊 Usage Analytics

Track these metrics (future feature):

- Messages sent per day
- Common questions
- Average response time
- User satisfaction
- Session duration

---

## 🎓 Educational Value

The chatbot helps students:

1. **Learn Faster**: Immediate answers to questions
2. **Write Better**: Guidance on entry quality
3. **Stay Organized**: Reminders about requirements
4. **Reduce Anxiety**: 24/7 support availability
5. **Improve Reflection**: Prompts for deeper thinking

---

## 📝 Maintenance

### Updating Knowledge Base

Edit the system prompt in `src/chatbot/AIChatbot.php`:

```php
private function initializeSystemPrompt(): void {
    $this->systemPrompt = <<<PROMPT
    // Add new knowledge here
    PROMPT;
}
```

### Monitoring Performance

Check server logs for:
- API errors
- Response times
- Failed requests
- Session issues

### User Feedback

Collect feedback via:
- Thumbs up/down on responses
- Feedback form in chat
- Common question analysis
- Support tickets

---

## 📞 Support

For chatbot issues:
1. Check this documentation
2. Review troubleshooting section
3. Check server logs
4. Contact development team

---

**Last Updated**: 2024  
**Version**: 1.0  
**Maintained by**: Development Team
