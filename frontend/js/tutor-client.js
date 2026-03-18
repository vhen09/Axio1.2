/**
 * AI Tutor API Client
 * Handles communication with the proof tutoring backend
 */

const tutorClient = {
    baseUrl: '../../backend/api/tutor.php',

    async makeRequest(action, data) {
        // Validate input
        if (action === 'suggest_strategy' || action === 'get_assistance') {
            if (!data.theorem || data.theorem.trim().length < 5) {
                return {
                    success: false,
                    error: '⚠️ Please enter a valid theorem (at least 5 characters)'
                };
            }
        }
        
        const timeoutPromise = new Promise((_, reject) => 
            setTimeout(() => reject(new Error('AI is taking longer than expected. Waiting for actual DeepSeek response timed out. Please retry once.')), 90000)
        );
        
        const fetchPromise = fetch(this.baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                ...data
            }),
        });
        
        try {
            const response = await Promise.race([fetchPromise, timeoutPromise]);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Request failed');
            }
            
            return result;
        } catch (error) {
            console.error('Tutor API Error:', error);
            return {
                success: false,
                error: error.message || 'Connection failed. Please check your internet and try again.'
            };
        }
    },
    
    getDemoResponse(action, data) {
        const demoResponses = {
            'get_assistance': {
                success: true,
                assistance: `📚 **Demo Mode - AI Proof Assistant**

**Understanding the theorem: "${data.theorem || 'Your Theorem'}"**

Since the AI service is currently unavailable, here's a structured approach to tackle this proof:

**1. Identify Key Components:**
   - What are the given conditions?
   - What do we need to prove?
   - What theorems or definitions might be relevant?

**2. General Proof Strategy:**
   - Start with definitions
   - Break down into smaller steps
   - Use known theorems when applicable
   - Check edge cases

**3. Common Techniques for Real Analysis:**
   - Direct proof
   - Proof by contradiction
   - Epsilon-delta arguments
   - Mathematical induction

**4. Next Steps:**
   - Write out your assumptions clearly
   - State what needs to be shown
   - Construct the logical flow
   - Verify each step

*Note: This is demo mode. For AI-powered assistance, please configure the DeepSeek API key in backend/config/deepseek.php*`,
                hints: [
                    "Start by clearly stating all given conditions",
                    "Break the proof into manageable logical steps",
                    "Use definitions and previously proven theorems"
                ]
            },
            'step_by_step': {
                success: true,
                step: {
                    number: (data.currentStep || 0) + 1,
                    instruction: "**Demo Mode Step**\n\nIn a real scenario, the AI would provide detailed step-by-step guidance. For now, consider:\n\n1. Review the theorem statement\n2. Identify what you're trying to prove\n3. List your assumptions\n4. Apply relevant definitions",
                    explanation: "This is a demo response. Configure the DeepSeek API for actual AI assistance."
                }
            },
            'verify_proof': {
                success: true,
                isValid: true,
                feedback: "**Demo Mode - Proof Verification**\n\n✓ Your proof structure looks promising!\n\n**General Feedback:**\n- Ensure all steps follow logically\n- Check that all assumptions are stated\n- Verify you've addressed all requirements\n\n*Configure DeepSeek API for detailed AI verification*",
                suggestions: [
                    "Double-check your logical flow",
                    "Ensure all definitions are properly used",
                    "Consider edge cases"
                ]
            },
            'explain_concept': {
                success: true,
                explanation: `**Demo Mode - Concept Explanation**

**Topic: ${data.concept || 'Mathematical Concept'}**

In Real Analysis, this concept is important because it provides the foundation for rigorous mathematical reasoning.

**Key Points:**
- Understand the formal definition
- Practice with examples
- Connect to related concepts
- Apply in different contexts

*For detailed AI-powered explanations, configure the DeepSeek API key*`
            }
        };
        
        return demoResponses[action] || {
            success: false,
            error: 'Demo mode: Unknown action'
        };
    },

    // Get general tutoring assistance
    async getAssistance(theorem, currentProof = '', context = {}) {
        return await this.makeRequest('get_assistance', {
            theorem,
            currentProof,
            context
        });
    },

    // Get step-by-step guidance
    async getStepByStep(theorem, currentStep = 0, previousSteps = []) {
        return await this.makeRequest('step_by_step', {
            theorem,
            currentStep,
            previousSteps
        });
    },

    // Verify a proof step
    async verifyStep(theorem, step, stepNumber, previousSteps = []) {
        return await this.makeRequest('verify_step', {
            theorem,
            step,
            stepNumber,
            previousSteps
        });
    },

    

    // Get proof strategy suggestions
    async suggestStrategy(theorem, theoremType = '') {
        return await this.makeRequest('suggest_strategy', {
            theorem,
            theoremType
        });
    },

    // Check complete proof
    async checkProof(theorem, proof) {
        return await this.makeRequest('check_proof', {
            theorem,
            proof
        });
    },

    // Get hints when stuck
    async getHint(theorem, currentProof, stuckPoint) {
        return await this.makeRequest('get_hint', {
            theorem,
            currentProof,
            stuckPoint
        });
    },

    // Get related theorems
    async suggestRelated(theorem, difficulty = 'similar') {
        return await this.makeRequest('suggest_related', {
            theorem,
            difficulty
        });
    },

    // Continue multi-turn conversation
    async continueConversation(messages) {
        return await this.makeRequest('continue_conversation', {
            messages
        });
    }
};

window.tutorClient = tutorClient;
