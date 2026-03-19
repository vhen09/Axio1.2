// ============================================
// Dual Proof Modes System
// Feature 6: Step-by-Step vs Full Proof Submission
// ============================================

/**
 * Proof Mode Manager
 * Manages two proof submission modes:
 * 1. FULL_PROOF - Submit entire proof at once
 * 2. STEP_BY_STEP - Submit and verify each step individually
 */

class ProofModeManager {
    constructor() {
        this.currentMode = this.getStoredMode() || 'FULL_PROOF';
        this.proofSteps = [];
        this.currentStepIndex = 0;
        this.verificationsPerformed = [];
        this.init();
    }

    /**
     * Initialize UI and event listeners
     */
    init() {
        this.createModeSelector();
        this.setupEventListeners();
    }

    /**
     * Create mode selector UI
     */
    createModeSelector() {
        const container = document.querySelector('[data-proof-mode-selector]') || 
                        this.createDefaultSelector();
        
        if (!container.querySelector('.proof-mode-selector')) {
            container.innerHTML = `
                <div class="proof-mode-selector">
                    <div class="mode-toggle">
                        <label class="mode-option">
                            <input type="radio" name="proof-mode" value="FULL_PROOF" 
                                   ${this.currentMode === 'FULL_PROOF' ? 'checked' : ''}>
                            <span class="mode-label">
                                <strong>📄 Full Proof</strong>
                                <small>Submit entire proof at once</small>
                            </span>
                        </label>
                        <label class="mode-option">
                            <input type="radio" name="proof-mode" value="STEP_BY_STEP"
                                   ${this.currentMode === 'STEP_BY_STEP' ? 'checked' : ''}>
                            <span class="mode-label">
                                <strong>👣 Step-by-Step</strong>
                                <small>Verify each step individually</small>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="mode-description" id="mode-description"></div>
            `;
        }
        
        this.updateDescription();
    }

    /**
     * Create default selector if no container exists
     */
    createDefaultSelector() {
        const container = document.createElement('div');
        container.setAttribute('data-proof-mode-selector', '');
        document.querySelector('.card') && 
        document.querySelector('.card').parentNode.insertBefore(container, document.querySelector('.card'));
        return container;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        document.querySelectorAll('input[name="proof-mode"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.switchMode(e.target.value);
            });
        });
    }

    /**
     * Switch proof mode
     */
    switchMode(mode) {
        if (mode !== 'FULL_PROOF' && mode !== 'STEP_BY_STEP') {
            console.error('Invalid mode:', mode);
            return false;
        }

        this.currentMode = mode;
        localStorage.setItem('proof_mode', mode);
        this.updateUI();
        this.updateDescription();
        
        console.log(`Switched to ${mode} mode`);
        return true;
    }

    /**
     * Update description based on current mode
     */
    updateDescription() {
        const descElement = document.getElementById('mode-description');
        if (!descElement) return;

        const descriptions = {
            'FULL_PROOF': `
                <div class="mode-info">
                    <p><strong>Full Proof Mode</strong></p>
                    <p>Submit your complete proof at once. The system will:</p>
                    <ul>
                        <li>✓ Analyze the entire proof for logical flow</li>
                        <li>✓ Check mathematical rigor and correctness</li>
                        <li>✓ Provide comprehensive scoring (0-100 points)</li>
                        <li>✓ Give detailed feedback on strengths and improvements</li>
                    </ul>
                    <p><em>Best for students confident in their proof structure.</em></p>
                </div>
            `,
            'STEP_BY_STEP': `
                <div class="mode-info">
                    <p><strong>Step-by-Step Mode</strong></p>
                    <p>Submit and verify each proof step individually for guidance:</p>
                    <ul>
                        <li>✓ Get immediate feedback on each step</li>
                        <li>✓ Verify logical correctness as you progress</li>
                        <li>✓ Receive hints and next-step guidance</li>
                        <li>✓ Learn from corrections before continuing</li>
                    </ul>
                    <p><em>Best for learning proper proof structure and technique.</em></p>
                </div>
            `
        };

        descElement.innerHTML = descriptions[this.currentMode] || '';
    }

    /**
     * Update UI based on current mode
     */
    updateUI() {
        if (this.currentMode === 'FULL_PROOF') {
            this.setupFullProofMode();
        } else {
            this.setupStepByStepMode();
        }
    }

    /**
     * Setup Full Proof Mode UI
     */
    setupFullProofMode() {
        const container = document.querySelector('[data-proof-mode-content]') || 
                        document.querySelector('.workspace');
        
        // Show full proof textarea
        const textareas = document.querySelectorAll('textarea[id*="proof"]');
        textareas.forEach(ta => ta.style.display = 'block');

        // Update submit button label
        const submitBtn = document.getElementById('btn-complete-proof') || 
                         document.querySelector('[data-submit-proof]');
        if (submitBtn) {
            submitBtn.textContent = '📤 Submit Complete Proof';
            submitBtn.classList.add('full-proof-mode');
        }

        // Hide step controls if present
        const stepControls = document.querySelectorAll('[data-step-control]');
        stepControls.forEach(el => el.style.display = 'none');
    }

    /**
     * Setup Step-by-Step Mode UI
     */
    setupStepByStepMode() {
        const container = document.querySelector('[data-proof-mode-content]') || 
                        document.querySelector('.workspace');
        
        // Show step editor
        const stepsWrap = document.getElementById('steps-wrap') || 
                         document.querySelector('[data-steps]');
        if (stepsWrap) {
            stepsWrap.style.display = 'block';
        }

        // Update submit button label
        const submitBtn = document.getElementById('btn-complete-proof') || 
                         document.querySelector('[data-submit-proof]');
        if (submitBtn) {
            submitBtn.textContent = '👉 Verify Current Step';
            submitBtn.classList.remove('full-proof-mode');
        }

        // Show step controls
        const stepControls = document.querySelectorAll('[data-step-control]');
        stepControls.forEach(el => el.style.display = 'flex');
    }

    /**
     * Submit proof based on current mode
     */
    async submitProof(theorem, proof, metadata = {}) {
        if (this.currentMode === 'FULL_PROOF') {
            return this.submitFullProof(theorem, proof, metadata);
        } else {
            return this.submitStepByStep(theorem, proof, metadata);
        }
    }

    /**
     * Submit full proof at once
     */
    async submitFullProof(theorem, proof, metadata = {}) {
        try {
            const response = await fetch('../../backend/api/scoring.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'score_proof',
                    theorem: theorem,
                    proof: proof,
                    lean_code: metadata.leanCode,
                    submission_id: metadata.submissionId,
                    mode: 'FULL_PROOF'
                })
            });

            const data = await response.json();
            
            return {
                success: data.success,
                mode: 'FULL_PROOF',
                score: data.data?.total_score || 0,
                feedback: data.data?.feedback || '',
                breakdown: data.data?.breakdown || {},
                strengths: data.data?.strengths || [],
                improvements: data.data?.improvements || []
            };
        } catch (error) {
            console.error('Full proof submission error:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Submit step-by-step with verification
     */
    async submitStepByStep(theorem, step, metadata = {}) {
        try {
            const response = await fetch('../../backend/api/proof.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'verify_step',
                    theorem: theorem,
                    current_step: step,
                    previous_steps: metadata.previousSteps || [],
                    step_index: metadata.stepIndex || 0,
                    mode: 'STEP_BY_STEP'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.verificationsPerformed.push({
                    step: step,
                    feedback: data.data?.feedback,
                    timestamp: new Date()
                });
            }

            return {
                success: data.success,
                mode: 'STEP_BY_STEP',
                stepIndex: metadata.stepIndex || 0,
                feedback: data.data?.feedback || '',
                isCorrect: data.data?.is_correct || false,
                suggestions: data.data?.suggestions || [],
                nextStepHint: data.data?.next_step_hint || ''
            };
        } catch (error) {
            console.error('Step-by-step submission error:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Get current mode
     */
    getMode() {
        return this.currentMode;
    }

    /**
     * Get stored mode preference
     */
    getStoredMode() {
        return localStorage.getItem('proof_mode') || 'FULL_PROOF';
    }

    /**
     * Get verification history
     */
    getVerificationHistory() {
        return this.verificationsPerformed;
    }

    /**
     * Reset state
     */
    reset() {
        this.proofSteps = [];
        this.currentStepIndex = 0;
        this.verificationsPerformed = [];
    }
}

// Create global instance
window.ProofModeManager = new ProofModeManager();
