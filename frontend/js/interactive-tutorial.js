/**
 * AXIO Interactive Tutorial System
 * Comprehensive onboarding for new users
 * Covers: Proof writing, LaTeX, Verification, Scoring
 */

class InteractiveTutorial {
  constructor(options = {}) {
    this.currentStep = 0;
    this.completed = false;
    this.completedLessons = new Set();
    this.userPreferences = {
      autoStartTutorial: true,
      showHints: true,
      darkMode: false,
      userLevel: 'beginner' // beginner, intermediate, advanced
    };

    this.lessons = this.initializeLessons();
    this.init();
  }

  init() {
    this.loadProgress();
    this.setupEventListeners();
    this.checkAutoStart();
  }

  /**
   * Define all tutorial lessons
   */
  initializeLessons() {
    return [
      {
        id: 'welcome',
        title: 'Welcome to AXIO',
        difficulty: 'beginner',
        duration: '2 min',
        steps: [
          {
            title: 'What is AXIO?',
            content: 'AXIO is an intelligent proof verification system that helps you learn and practice mathematical proofs.',
            action: 'Read the introduction',
            hint: 'Click "Next" to continue'
          },
          {
            title: 'How AXIO Works',
            content: '1. Enter a theorem statement\n2. Write your proof step-by-step\n3. AXIO verifies each step\n4. Get scored with detailed feedback',
            action: 'Understanding the workflow',
            hint: 'Different proof types require different approaches'
          }
        ]
      },

      {
        id: 'latex_basics',
        title: 'LaTeX for Math',
        difficulty: 'beginner',
        duration: '5 min',
        prerequisite: 'welcome',
        steps: [
          {
            title: 'Why LaTeX?',
            content: 'LaTeX ensures your mathematical notation is precise and unambiguous. It\'s the standard in mathematics.',
            action: 'View LaTeX examples',
            hint: 'Common: \\alpha, \\sum, \\int, \\forall'
          },
          {
            title: 'Basic LaTeX Syntax',
            content: 'Greek letters: \\alpha, \\beta, \\gamma\nOperators: \\sum, \\prod, \\int\nSuperscripts: x^2\nSubscripts: x_i',
            example: 'For all (\\forall) x in the interval, the function f(x) is continuous.',
            action: 'Practice typing LaTeX',
            hint: 'Use the Equations button to insert symbols'
          },
          {
            title: 'Fractions and Roots',
            content: 'Fractions: \\frac{numerator}{denominator}\nRoots: \\sqrt{x} or \\sqrt[3]{x}',
            example: '\\frac{1}{2} and \\sqrt{2}',
            action: 'Type examples',
            hint: 'Click the Equations button to see symbol categories'
          }
        ]
      },

      {
        id: 'proof_types',
        title: 'Reading Proof Problems',
        difficulty: 'beginner',
        duration: '5 min',
        prerequisite: 'welcome',
        steps: [
          {
            title: 'Understanding the Statement',
            content: 'Every proof starts with a theorem statement. Read it carefully:\n- Identify what you\"re given (assumptions)\n- Identify what you need to prove (conclusion)',
            example: 'Theorem: If n is even, then n² is even.\nAssumption: n is even\nConclusion: n² is even',
            action: 'Analyze theorem statements',
            hint: 'Always jot down assumptions and conclusions first'
          },
          {
            title: 'Proof Strategies',
            content: 'Different theorems need different approaches:\n• Direct: Assume A, derive B\n• Contradiction: Assume not B, show contradiction\n• Induction: Base case + inductive step',
            action: 'Choose a strategy',
            hint: 'The statement hints at the best approach'
          }
        ]
      },

      {
        id: 'step_by_step',
        title: 'Writing Your First Proof',
        difficulty: 'beginner',
        duration: '8 min',
        prerequisite: ['welcome', 'latex_basics'],
        steps: [
          {
            title: 'Theorem Input',
            content: 'Enter your theorem in the "Input Theorem Statement" box. Use LaTeX for precision.',
            example: 'Prove: For all integers n, if n is even, then n² is even.',
            action: 'Input a theorem',
            hint: 'Click "Equations" to insert mathematical symbols'
          },
          {
            title: 'Proof Steps',
            content: 'Click "+ Add Step" to create proof steps. Each step must:\n1. State a fact or derivation\n2. Justify it (reference previous step, definition, theorem)',
            example: 'Step 1: Let n be an even integer.\nBecause: By definition of "even"',
            action: 'Add your first step',
            hint: 'Justification is crucial for verification'
          },
          {
            title: 'Building the Chain',
            content: 'Each step should logically follow from the previous one. AXIO will verify the logical chain.',
            example: 'Step 1 → Step 2 → Step 3 → Conclusion',
            action: 'Complete a simple proof',
            hint: 'Aim for 3-5 clear steps for a short proof'
          },
          {
            title: 'Verification',
            content: 'Click "Complete Proof" to submit. The verification panel will show which steps are valid and which need work.',
            example: '✓ Step 1 valid\n✓ Step 2 valid\n✗ Step 3 needs justification',
            action: 'Submit your proof',
            hint: 'Check the "Verification Panel" on the right'
          }
        ]
      },

      {
        id: 'symbols_library',
        title: 'Math Symbols Reference',
        difficulty: 'beginner',
        duration: '3 min',
        steps: [
          {
            title: 'Access the Symbols Library',
            content: 'Navigate to "Σ Math Symbols" from the sidebar. This complete reference helps you find LaTeX codes for any symbol.',
            action: 'Visit symbols library',
            hint: 'Bookmark this page for quick reference'
          },
          {
            title: 'Search and Copy',
            content: 'Search by symbol name, LaTeX code, or visual appearance. Click "Copy" to copy the LaTeX code to your clipboard.',
            example: 'Search "integral" → find \\int → copy → paste',
            action: 'Search and copy a symbol',
            hint: 'Filter by category for faster navigation'
          }
        ]
      },

      {
        id: 'scoring_system',
        title: 'Understanding Your Score',
        difficulty: 'beginner',
        duration: '4 min',
        prerequisite: 'step_by_step',
        steps: [
          {
            title: 'Score Breakdown',
            content: 'Your proof is scored on 5 dimensions:\n• Logical Correctness (35%)\n• Completeness (25%)\n• Clarity (20%)\n• Rigor (15%)\n• Efficiency (5%)',
            action: 'Review scoring matrix',
            hint: 'Each dimension has subcriteria'
          },
          {
            title: 'Reading Your Feedback',
            content: 'After submission, you see:\n• Overall grade (A-F)\n• Point breakdown per category\n• Specific improvement areas\n• Tips for better proofs',
            action: 'Understand feedback',
            hint: 'Use feedback to improve your next proof'
          }
        ]
      },

      {
        id: 'advanced_proofs',
        title: 'Advanced Proof Techniques',
        difficulty: 'intermediate',
        duration: '10 min',
        prerequisite: 'step_by_step',
        steps: [
          {
            title: 'Proof by Contradiction',
            content: 'Assume the negation of what you want to prove, then derive a contradiction.\nStructure:\n1. Assume NOT(conclusion)\n2. Derive a contradiction\n3. Therefore, conclusion must be true',
            example: 'To prove √2 is irrational:\n1. Assume √2 = p/q (rational)\n2. Derive that p and q must both be even\n3. Contradiction with coprimality',
            action: 'Try a proof by contradiction',
            hint: 'Clearly state your assumption first'
          },
          {
            title: 'Proof by Induction',
            content: 'Prove statements about all natural numbers.\nStructure:\n1. Base case: Prove for n=0 or n=1\n2. Inductive step: Assume true for n=k, prove for n=k+1',
            example: 'Prove: 1+2+...+n = n(n+1)/2\nBase: 1 = 1(2)/2 ✓\nStep: If true for k, then true for k+1',
            action: 'Write an induction proof',
            hint: 'The inductive hypothesis is key'
          },
          {
            title: 'Proof by Cases',
            content: 'When a condition has multiple cases, prove each separately.\nStructure:\n1. Identify all cases\n2. Prove conclusion for each case\n3. Conclude for all cases',
            example: 'For any integer n: n² ≡ 0 or 1 (mod 4)\nCase 1: n even → n² ≡ 0 (mod 4)\nCase 2: n odd → n² ≡ 1 (mod 4)',
            action: 'Write a multi-case proof',
            hint: 'Ensure you cover all cases'
          }
        ]
      },

      {
        id: 'different_domains',
        title: 'Different Math Domains',
        difficulty: 'intermediate',
        duration: '6 min',
        steps: [
          {
            title: 'Beyond Real Analysis',
            content: 'AXIO supports proofs in:  • Algebra • Calculus • Discrete Math • Geometry • Linear Algebra • Logic • Probability',
            action: 'Explore different domains',
            hint: 'Each domain has specific proof patterns'
          },
          {
            title: 'Domain-Specific Tips',
            content: 'When you submit, AXIO detects your domain and provides:\n• Domain-specific notation help\n• Relevant theorem suggestions\n• Domain-appropriate proof structures',
            action: 'Submit in different domains',
            hint: 'Try algebra, geometry, and discrete math'
          }
        ]
      },

      {
        id: 'full_proof_mode',
        title: 'Full Proof Mode',
        difficulty: 'advanced',
        duration: '5 min',
        steps: [
          {
            title: 'Alternative: Write Naturally',
            content: 'Instead of step-by-step, you can write your proof in paragraph form (natural language or full LaTeX).',
            action: 'Try full proof mode',
            hint: 'Some people find this more intuitive'
          },
          {
            title: 'How It Works',
            content: 'AXIO uses AI to:\n1. Parse your natural language proof\n2. Convert to formal steps\n3. Verify logical structure\n4. Provide the same detailed feedback',
            example: 'Write: "Since n is even, n = 2k. So n² = 4k², which is even."\nAXIO converts to structured steps and verifies.',
            action: 'Write a natural language proof',
            hint: 'Be as clear as possible for best results'
          }
        ]
      }
    ];
  }

  /**
   * Setup event listeners
   */
  setupEventListeners() {
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('tutorial-btn-next')) {
        this.nextStep();
      }
      if (e.target.classList.contains('tutorial-btn-prev')) {
        this.previousStep();
      }
      if (e.target.classList.contains('tutorial-icon')) {
        this.showTutorial();
      }
      if (e.target.classList.contains('tutorial-close')) {
        this.closeTutorial();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.closeTutorial();
      if (e.key === 'ArrowRight') this.nextStep();
      if (e.key === 'ArrowLeft') this.previousStep();
    });
  }

  /**
   * Check if tutorial should auto-start
   */
  checkAutoStart() {
    const hasSeenTutorial = localStorage.getItem('axio_tutorial_completed');
    if (!hasSeenTutorial && this.userPreferences.autoStartTutorial) {
      // Show welcome indicator
      // this.showWelcomePrompt();
    }
  }

  /**
   * Show welcome prompt
   */
  showWelcomePrompt() {
    const prompt = document.createElement('div');
    prompt.className = 'tutorial-welcome-prompt';
    prompt.innerHTML = `
      <div class="prompt-content">
        <div class="prompt-icon">👋</div>
        <h3>New to AXIO?</h3>
        <p>Take our interactive tutorial to learn how to write and verify proofs.</p>
        <div class="prompt-buttons">
          <button class="btn btn-primary tutorial-start-now">Start Tutorial</button>
          <button class="btn tutorial-skip">Skip for Now</button>
        </div>
      </div>
    `;

    document.body.appendChild(prompt);

    prompt.querySelector('.tutorial-start-now').addEventListener('click', () => {
      prompt.remove();
      this.showTutorial();
    });

    prompt.querySelector('.tutorial-skip').addEventListener('click', () => {
      prompt.remove();
    });
  }

  /**
   * Show tutorial modal
   */
  showTutorial() {
    const modal = this.createTutorialModal();
    document.body.appendChild(modal);

    // Position behind other elements initially
    setTimeout(() => modal.classList.add('show'), 10);

    // Show first lesson
    this.showLesson('welcome');
  }

  /**
   * Create tutorial modal
   */
  createTutorialModal() {
    const modal = document.createElement('div');
    modal.className = 'tutorial-modal';
    modal.innerHTML = `
      <div class="tutorial-container">
        <div class="tutorial-close">✕</div>
        
        <div class="tutorial-sidebar">
          <div class="tutorial-logo">AXIO Tutorials</div>
          <div class="tutorial-lessons-nav"></div>
          <div class="tutorial-progress">
            <div class="progress-label">Overall Progress</div>
            <div class="progress-bar">
              <div class="progress-fill"></div>
            </div>
            <div class="progress-text">0/8 lessons</div>
          </div>
        </div>

        <div class="tutorial-main">
          <div class="tutorial-breadcrumb">
            <span class="breadcrumb-lesson"></span>
            <span class="breadcrumb-step"></span>
          </div>

          <div class="tutorial-content">
            <h2 class="tutorial-title"></h2>
            <div class="tutorial-body"></div>
            
            <div class="tutorial-example" style="display: none;">
              <strong>Example:</strong>
              <pre class="example-code"></pre>
            </div>

            <div class="tutorial-navigation">
              <button class="btn tutorial-btn-prev" style="display: none;">← Previous</button>
              <div class="tutorial-step-indicator"></div>
              <button class="btn btn-primary tutorial-btn-next">Next →</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Add styles
    this.addTutorialStyles();

    return modal;
  }

  /**
   * Show a specific lesson
   */
  showLesson(lessonId) {
    const lesson = this.lessons.find(l => l.id === lessonId);
    if (!lesson) return;

    const modal = document.querySelector('.tutorial-modal');
    if (!modal) return;

    // Update sidebar navigation
    this.updateLessonNav();

    // Show first step
    this.currentStep = 0;
    this.showStep(lesson, 0);
  }

  /**
   * Show a specific step
   */
  showStep(lesson, stepIndex) {
    const step = lesson.steps[stepIndex];
    const modal = document.querySelector('.tutorial-modal');
    
    if (!modal || !step) return;

    const title = modal.querySelector('.tutorial-title');
    const body = modal.querySelector('.tutorial-body');
    const example = modal.querySelector('.tutorial-example');
    const exampleCode = modal.querySelector('.example-code');
    const indicator = modal.querySelector('.tutorial-step-indicator');
    const nextBtn = modal.querySelector('.tutorial-btn-next');
    const prevBtn = modal.querySelector('.tutorial-btn-prev');
    const breadcrumb = modal.querySelector('.breadcrumb-step');

    // Update content
    title.textContent = step.title;
    body.innerHTML = `<p>${step.content.replace(/\n/g, '</p><p>')}</p>`;

    // Show example if present
    if (step.example) {
      example.style.display = 'block';
      exampleCode.textContent = step.example;
      // Try to render LaTeX if KaTeX available
      if (window.renderMathInElement) {
        window.renderMathInElement(exampleCode, { delimiters: [[{ left: '$', right: '$', display: false }]] });
      }
    } else {
      example.style.display = 'none';
    }

    // Update navigation
    indicator.textContent = `Step ${stepIndex + 1} of ${lesson.steps.length}`;
    breadcrumb.textContent = lesson.title;

    // Update buttons
    prevBtn.style.display = stepIndex > 0 ? 'block' : 'none';
    nextBtn.textContent = stepIndex === lesson.steps.length - 1 ? 'Complete →' : 'Next →';

    // Button handlers
    nextBtn.onclick = () => {
      if (stepIndex === lesson.steps.length - 1) {
        this.completeLesson(lesson.id);
      } else {
        this.showStep(lesson, stepIndex + 1);
      }
    };

    prevBtn.onclick = () => {
      if (stepIndex > 0) {
        this.showStep(lesson, stepIndex - 1);
      }
    };
  }

  /**
   * Complete a lesson
   */
  completeLesson(lessonId) {
    this.completedLessons.add(lessonId);
    localStorage.setItem('axio_tutorial_completed_' + lessonId, 'true');

    showSuccess(`Great! Lesson "${lessonId}" completed!`);

    // Find next lesson
    const nextLesson = this.lessons.find((l, idx) => {
      const currentIdx = this.lessons.findIndex(ll => ll.id === lessonId);
      return idx > currentIdx;
    });

    if (nextLesson) {
      this.showLesson(nextLesson.id);
    } else {
      this.closeTutorial();
      localStorage.setItem('axio_tutorial_completed', 'true');
      showSuccess('Tutorial completed! You\'re ready to start proving.');
    }
  }

  /**
   * Close tutorial
   */
  closeTutorial() {
    const modal = document.querySelector('.tutorial-modal');
    if (modal) {
      modal.classList.remove('show');
      setTimeout(() => modal.remove(), 300);
    }
  }

  /**
   * Load progress from storage
   */
  loadProgress() {
    Object.keys(localStorage).forEach(key => {
      if (key.startsWith('axio_tutorial_completed_')) {
        const lessonId = key.replace('axio_tutorial_completed_', '');
        this.completedLessons.add(lessonId);
      }
    });
  }

  /**
   * Update lesson navigation
   */
  updateLessonNav() {
    const nav = document.querySelector('.tutorial-lessons-nav');
    if (!nav) return;

    nav.innerHTML = '';
    this.lessons.forEach(lesson => {
      const isCompleted = this.completedLessons.has(lesson.id);
      const item = document.createElement('div');
      item.className = `tutorial-lesson-item ${isCompleted ? 'completed' : ''}`;
      item.innerHTML = `
        <span class="lesson-title">${lesson.title}</span>
        <span class="lesson-time">${lesson.duration}</span>
      `;
      item.addEventListener('click', () => this.showLesson(lesson.id));
      nav.appendChild(item);
    });
  }

  /**
   * Add tutorial CSS styles
   */
  addTutorialStyles() {
    if (document.getElementById('tutorial-styles')) return;

    const style = document.createElement('style');
    style.id = 'tutorial-styles';
    style.textContent = `
      .tutorial-modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s;
      }

      .tutorial-modal.show {
        opacity: 1;
      }

      .tutorial-container {
        display: flex;
        background: white;
        border-radius: 12px;
        max-width: 900px;
        max-height: 80vh;
        margin: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
      }

      .tutorial-close {
        position: absolute;
        top: 14px;
        right: 14px;
        cursor: pointer;
        font-size: 1.5em;
        color: #6b7280;
        z-index: 10000;
      }

      .tutorial-sidebar {
        width: 250px;
        background: #f3f4f6;
        padding: 20px;
        overflow-y: auto;
        border-right: 1px solid #e5e7eb;
      }

      .tutorial-logo {
        font-weight: 700;
        margin-bottom: 20px;
        color: #1e3a8a;
      }

      .tutorial-lessons-nav {
        margin-bottom: 20px;
      }

      .tutorial-lesson-item {
        padding: 10px;
        margin-bottom: 8px;
        border-radius: 6px;
        cursor: pointer;
        background: white;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
      }

      .tutorial-lesson-item:hover {
        background: #e5e7eb;
      }

      .tutorial-lesson-item.completed {
        background: #d1fae5;
        border-color: #6ee7b7;
      }

      .tutorial-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 30px;
        overflow-y: auto;
      }

      .tutorial-title {
        margin: 0 0 20px 0;
        color: #1f2937;
      }

      .tutorial-body {
        flex: 1;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 20px;
      }

      .tutorial-example {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 16px;
        font-size: 0.9em;
      }

      .example-code {
        margin: 8px 0 0 0;
        padding: 8px;
        background: white;
        border-radius: 4px;
        overflow-x: auto;
        font-family: monospace;
      }

      .tutorial-navigation {
        display: flex;
        gap: 12px;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
      }

      .tutorial-step-indicator {
        color: #6b7280;
        font-size: 0.9em;
      }

      .tutorial-welcome-prompt {
        display: none !important;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        z-index: 9998;
        text-align: center;
        animation: slideUp 0.3s ease-out;
      }

      .prompt-icon {
        font-size: 3em;
        margin-bottom: 16px;
      }

      .prompt-content h3 {
        margin: 16px 0 8px 0;
        color: #1f2937;
      }

      .prompt-buttons {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        justify-content: center;
      }

      /* Dark mode */
      .theme-dark .tutorial-modal {
        background: rgba(0, 0, 0, 0.8);
      }

      .theme-dark .tutorial-container {
        background: #1f2937;
        color: #f3f4f6;
      }

      .theme-dark .tutorial-sidebar {
        background: #374151;
        border-right-color: #3f3f46;
      }

      .theme-dark .tutorial-lesson-item {
        background: #1f2937;
        border-color: #3f3f46;
        color: #f3f4f6;
      }

      .theme-dark .tutorial-title,
      .theme-dark .tutorial-body {
        color: inherit;
      }

      @keyframes slideUp {
        from { transform: translate(-50%, -40%); opacity: 0; }
        to { transform: translate(-50%, -50%); opacity: 1; }
      }
    `;

    document.head.appendChild(style);
  }

  /**
   * Navigate steps
   */
  nextStep() {
    const lesson = this.lessons[0]; // Simplified
    this.showStep(lesson, this.currentStep + 1);
    this.currentStep++;
  }

  previousStep() {
    const lesson = this.lessons[0];
    if (this.currentStep > 0) {
      this.showStep(lesson, this.currentStep - 1);
      this.currentStep--;
    }
  }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
  window.tutorialSystem = new InteractiveTutorial();
  
  // Add help button to topbar if available
  const topbar = document.querySelector('.topbar .actions');
  if (topbar) {
    const helpBtn = document.createElement('button');
    helpBtn.className = 'btn tutorial-icon';
    helpBtn.title = 'Start Tutorial';
    helpBtn.innerHTML = '❓ Help';
    topbar.appendChild(helpBtn);
  }
});

// Export
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { InteractiveTutorial };
}
