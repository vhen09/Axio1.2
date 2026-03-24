/**
 * Proof Mode Controller
 * Manages dynamic switching between Step-by-Step and Full Proof modes
 * Provides UI switching, localStorage persistence, and mode-specific functionality
 */

class ProofModeController {
  constructor() {
    this.stepModeContainer = document.getElementById('step-mode-container');
    this.fullProofContainer = document.getElementById('full-proof-container');
    this.proofModeRadios = document.querySelectorAll('input[name="proof-mode"]');
    this.currentMode = this.loadProofMode();
    this.init();
  }

  /**
   * Initialize the proof mode controller
   */
  init() {
    if (!this.stepModeContainer || !this.fullProofContainer) {
      console.warn('Proof mode containers not found');
      return;
    }

    // Load mode from localStorage or global settings
    this.currentMode = this.loadProofMode();

    // Set up event listeners for radio buttons
    this.proofModeRadios.forEach(radio => {
      radio.addEventListener('change', (e) => {
        this.switchMode(e.target.value);
      });
    });

    // Apply initial mode
    this.applyMode(this.currentMode);

    // Setup equation buttons for both modes
    this.setupEquationHandlers();

    // Setup submission handlers
    this.setupSubmissionHandlers();

    // Add auto-save for full proof mode
    this.setupAutoSave();

    console.log('✓ ProofModeController initialized:', this.currentMode);
  }

  /**
   * Load proof mode from storage
   */
  loadProofMode() {
    // First check localStorage for proof mode
    const stored = localStorage.getItem('proof_mode');
    if (stored) return stored;

    // Fall back to global settings if available
    if (window.globalSettings) {
      const proofMode = window.globalSettings.getSetting('proofMode') || 'step-by-step';
      return proofMode;
    }

    return 'step-by-step'; // Default
  }

  /**
   * Switch between proof modes
   */
  switchMode(mode) {
    if (mode !== 'step-by-step' && mode !== 'full-proof') {
      console.error('Invalid proof mode:', mode);
      return;
    }

    this.currentMode = mode;
    this.applyMode(mode);
    this.saveProofMode(mode);

    console.log('📋 Switched to', mode, 'mode');
  }

  /**
   * Apply the selected mode to the UI
   */
  applyMode(mode) {
    if (mode === 'step-by-step') {
      // Show step-by-step container
      this.stepModeContainer.style.display = '';
      this.fullProofContainer.style.display = 'none';
      this.setRadioValue('step-by-step');
      this.addSmoothTransition();
    } else if (mode === 'full-proof') {
      // Show full proof container
      this.stepModeContainer.style.display = 'none';
      this.fullProofContainer.style.display = '';
      this.setRadioValue('full-proof');
      this.addSmoothTransition();

      // Focus the textarea for better UX
      setTimeout(() => {
        const textarea = document.getElementById('full-proof-input');
        if (textarea) textarea.focus();
      }, 100);
    }
  }

  /**
   * Update radio button state without triggering change event
   */
  setRadioValue(value) {
    this.proofModeRadios.forEach(radio => {
      radio.checked = radio.value === value;
    });
  }

  /**
   * Add smooth transition animation
   */
  addSmoothTransition() {
    const container = this.stepModeContainer.parentElement;
    if (container) {
      container.style.opacity = '0.7';
      setTimeout(() => {
        container.style.opacity = '1';
      }, 50);
    }
  }

  /**
   * Save proof mode to localStorage
   */
  saveProofMode(mode) {
    localStorage.setItem('proof_mode', mode);

    // Also update global settings if available
    if (window.globalSettings) {
      window.globalSettings.updateSetting('proofMode', mode);
    }
  }

  /**
   * Setup equation button handlers for both modes
   */
  setupEquationHandlers() {
    // Step-by-step mode equation button
    const btnStepKeyboard = document.getElementById('btn-keyboard');
    if (btnStepKeyboard) {
      btnStepKeyboard.addEventListener('click', () => {
        this.insertEquationIntoStep();
      });
    }

    // Full proof equation button disabled - handled by EnhancedEquationKeyboard
  }

  /**
   * Insert equation into currently active step (Step-by-Step mode)
   */
  insertEquationIntoStep() {
    // This is handled by the EquationComponent for step mode
    // The existing equation keyboard functionality remains intact
  }

  /**
   * Insert equation into full proof textarea
   */
  insertEquationIntoFullProof() {
    const textarea = document.getElementById('full-proof-input');
    if (!textarea) return;

    // Create a simple LaTeX insertion modal
    const latex = prompt('Enter LaTeX code:\n\nExamples:\n\\alpha, \\beta\n\\frac{a}{b}\n\\sqrt{x}\n\\int_0^1 x dx\n\nOr leave blank to cancel');

    if (latex) {
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const before = textarea.value.substring(0, start);
      const after = textarea.value.substring(end);

      textarea.value = before + latex + after;
      textarea.selectionStart = textarea.selectionEnd = start + latex.length;
      textarea.focus();

      // Trigger input event for auto-save
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }
  }

  /**
   * Setup submission handlers for both modes
   */
  setupSubmissionHandlers() {
    // Step-by-Step mode submit (existing button handling in axio-app.js)
    // This is maintained for backward compatibility

    // Full Proof mode submit
    const btnFullSubmit = document.getElementById('btn-full-submit');
    if (btnFullSubmit) {
      btnFullSubmit.addEventListener('click', () => {
        this.submitFullProof();
      });
    }
  }

  /**
   * Submit full proof for verification
   */
  submitFullProof() {
    const theoremInput = document.getElementById('theorem-input');
    const fullProofInput = document.getElementById('full-proof-input');

    if (!theoremInput.value.trim()) {
      showWarning('⚠️ Please enter a theorem statement first');
      return;
    }

    if (!fullProofInput.value.trim()) {
      showWarning('⚠️ Please enter your proof');
      return;
    }

    const payload = {
      theorem: theoremInput.value,
      proof: fullProofInput.value,
      mode: 'full-proof'
    };

    console.log('📤 Submitting full proof...', payload);

    // Call the backend API
    this.verifyFullProof(payload);
  }

  /**
   * Verify full proof via backend
   */
  verifyFullProof(payload) {
    const apiUrl = `${window.location.protocol}//${window.location.host}/backend/api/proof.php?action=verify-full`;
    fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
      credentials: 'include'
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('✓ Proof verified:', data);
          this.displayVerificationResult(data);
          showSuccess('✓ Proof verified! Score: ' + (data.score || 'N/A'));
        } else {
          console.error('✗ Verification failed:', data);
          showError('✗ Verification failed: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('✗ API Error:', error);
        showError('✗ API Error: ' + error.message);
      });
  }

  /**
   * Display verification results in the verification panel
   */
  displayVerificationResult(data) {
    const statusValue = document.getElementById('verify-status-value');
    const inputSummary = document.getElementById('verify-input-summary');
    const justification = document.getElementById('verify-justification');
    const nextGuide = document.getElementById('verify-next-guide');

    if (statusValue) {
      statusValue.innerHTML = `<strong>${data.valid ? '✓ Valid' : '✗ Invalid'}</strong><br>Score: ${data.score || 'N/A'}`;
    }

    if (inputSummary) {
      inputSummary.textContent = data.summary || 'Full proof submitted for verification.';
    }

    if (justification) {
      justification.textContent = data.feedback || 'Your proof has been analyzed.';
    }

    if (nextGuide) {
      nextGuide.textContent = data.suggestion || 'Continue refining your proof or try another theorem.';
    }

    // Update verification header
    const verifyHeader = document.getElementById('verify-header');
    if (verifyHeader) {
      if (data.valid) {
        verifyHeader.className = 'verify-header-box verify-header-success';
        verifyHeader.innerHTML = '<span class="verify-header-icon">✓</span><span class="verify-header-label">Proof verified successfully!</span>';
      } else {
        verifyHeader.className = 'verify-header-box verify-header-error';
        verifyHeader.innerHTML = '<span class="verify-header-icon">✗</span><span class="verify-header-label">Proof needs revision</span>';
      }
    }
  }

  /**
   * Setup auto-save for full proof mode
   */
  setupAutoSave() {
    const fullProofInput = document.getElementById('full-proof-input');
    if (!fullProofInput) return;

    let autoSaveTimeout;

    fullProofInput.addEventListener('input', () => {
      clearTimeout(autoSaveTimeout);

      autoSaveTimeout = setTimeout(() => {
        this.saveDraft(fullProofInput.value);
      }, 2000); // Auto-save every 2 seconds of inactivity
    });

    // Load draft on init
    const draft = localStorage.getItem('full-proof-draft');
    if (draft) {
      fullProofInput.value = draft;
      console.log('📝 Loaded auto-saved draft');
    }
  }

  /**
   * Save draft to localStorage
   */
  saveDraft(content) {
    try {
      localStorage.setItem('full-proof-draft', content);
      console.log('💾 Draft auto-saved');
    } catch (e) {
      console.error('Error saving draft:', e);
    }
  }

  /**
   * Get current mode
   */
  getMode() {
    return this.currentMode;
  }

  /**
   * Get proof content based on current mode
   */
  getProofContent() {
    if (this.currentMode === 'step-by-step') {
      // Collect all steps
      const stepInputs = document.querySelectorAll('[data-step-content]');
      const steps = Array.from(stepInputs).map(input => input.value);
      return { mode: 'step-by-step', steps };
    } else {
      const fullProofInput = document.getElementById('full-proof-input');
      return { mode: 'full-proof', proof: fullProofInput?.value || '' };
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.proofModeController = new ProofModeController();
});
