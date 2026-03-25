/**
 * Enhanced Equation Keyboard Component
 * Provides professional equation/symbol picker with proper clickability and styling
 * Matches reference design from Image 2
 */

class EnhancedEquationKeyboard {
  constructor(options = {}) {
    this.options = {
      targetTextarea: null,
      buttonElement: null,
      onInsert: null,
      ...options
    };

    this.keyboard = null;
    this.isOpen = false;
    this.currentCategory = 'fractions';

    // Comprehensive equation database organized by category
    this.categories = {
      fractions: {
        name: 'Fractions',
        icon: '÷',
        items: [
          { label: 'a/b', value: '\\frac{a}{b}' },
          { label: 'd/dy', value: '\\frac{d}{dy}' },
          { label: '2|dx', value: '\\frac{2}{dx}' },
          { label: '1/n', value: '\\frac{1}{n}' },
          { label: 'a+b/c', value: '\\frac{a+b}{c}' },
          { label: 'n|kl', value: '\\frac{n}{kl}' }
        ]
      },
      superscripts: {
        name: 'Superscripts & Subscripts',
        icon: 'ₓ',
        items: [
          { label: 'x²', value: '^{2}' },
          { label: 'xⁿ', value: '^{n}' },
          { label: 'x₁', value: '_{1}' },
          { label: 'xₙ', value: '_{n}' },
          { label: 'aᵢⱼ', value: '_{ij}' },
          { label: 'x^(n+1)', value: '^{n+1}' },
          { label: 'eˣ', value: '^{x}' },
          { label: '2ⁿ', value: '^{n}' }
        ]
      },
      radicals: {
        name: 'Radicals',
        icon: '√',
        items: [
          { label: '√x', value: '\\sqrt{x}' },
          { label: '∛x', value: '\\sqrt[3]{x}' },
          { label: 'ⁿ√x', value: '\\sqrt[n]{x}' },
          { label: '√(a²+b²)', value: '\\sqrt{a^{2}+b^{2}}' }
        ]
      },
      integrals: {
        name: 'Integrals & Summations',
        icon: '∫',
        items: [
          { label: '∫', value: '\\int' },
          { label: '∫ₐᵇ', value: '\\int_{a}^{b}' },
          { label: '∬', value: '\\iint' },
          { label: '∮', value: '\\oint' },
          { label: '∑', value: '\\sum' },
          { label: '∑∞', value: '\\sum_{n=1}^{\\infty}' },
          { label: '∏', value: '\\prod' },
          { label: '⋃', value: '\\bigcup' },
          { label: '⋂', value: '\\bigcap' }
        ]
      },
      limits: {
        name: 'Limits',
        icon: 'lim',
        items: [
          { label: 'lim', value: '\\lim' },
          { label: 'lim→∞', value: '\\lim_{x \\to \\infty}' },
          { label: 'lim→0⁺', value: '\\lim_{x \\to 0^{+}}' },
          { label: 'limsup', value: '\\limsup' },
          { label: 'liminf', value: '\\liminf' },
          { label: 'sup', value: '\\sup' },
          { label: 'inf', value: '\\inf' }
        ]
      },
      greek: {
        name: 'Greek Letters',
        icon: 'α',
        items: [
          { label: 'α', value: '\\alpha' },
          { label: 'β', value: '\\beta' },
          { label: 'γ', value: '\\gamma' },
          { label: 'Γ', value: '\\Gamma' },
          { label: 'δ', value: '\\delta' },
          { label: 'Δ', value: '\\Delta' },
          { label: 'ε', value: '\\epsilon' },
          { label: 'ζ', value: '\\zeta' },
          { label: 'η', value: '\\eta' },
          { label: 'θ', value: '\\theta' },
          { label: 'λ', value: '\\lambda' },
          { label: 'Λ', value: '\\Lambda' },
          { label: 'μ', value: '\\mu' },
          { label: 'ν', value: '\\nu' },
          { label: 'π', value: '\\pi' },
          { label: 'Π', value: '\\Pi' },
          { label: 'ρ', value: '\\rho' },
          { label: 'σ', value: '\\sigma' },
          { label: 'Σ', value: '\\Sigma' },
          { label: 'τ', value: '\\tau' },
          { label: 'φ', value: '\\phi' },
          { label: 'Φ', value: '\\Phi' },
          { label: 'ψ', value: '\\psi' },
          { label: 'ω', value: '\\omega' },
          { label: 'Ω', value: '\\Omega' }
        ]
      },
      settheory: {
        name: 'Set Theory',
        icon: '∈',
        items: [
          { label: '∈', value: '\\in' },
          { label: '∉', value: '\\notin' },
          { label: '⊂', value: '\\subset' },
          { label: '⊆', value: '\\subseteq' },
          { label: '⊃', value: '\\supset' },
          { label: '⊇', value: '\\supseteq' },
          { label: '∪', value: '\\cup' },
          { label: '∩', value: '\\cap' },
          { label: '∅', value: '\\emptyset' },
          { label: '\\', value: '\\setminus' },
          { label: 'ℝ', value: '\\mathbb{R}' },
          { label: 'ℤ', value: '\\mathbb{Z}' },
          { label: 'ℕ', value: '\\mathbb{N}' },
          { label: 'ℚ', value: '\\mathbb{Q}' },
          { label: 'ℂ', value: '\\mathbb{C}' }
        ]
      },
      logical: {
        name: 'Logical Symbols',
        icon: '∧',
        items: [
          { label: '∀', value: '\\forall' },
          { label: '∃', value: '\\exists' },
          { label: '∄', value: '\\nexists' },
          { label: '→', value: '\\rightarrow' },
          { label: '⇒', value: '\\Rightarrow' },
          { label: '⇔', value: '\\Leftrightarrow' },
          { label: '¬', value: '\\neg' },
          { label: '∧', value: '\\land' },
          { label: '∨', value: '\\lor' },
          { label: '⊢', value: '\\vdash' },
          { label: '⊨', value: '\\models' },
          { label: '∴', value: '\\therefore' },
          { label: '∵', value: '\\because' }
        ]
      },
      relations: {
        name: 'Relations & Operators',
        icon: '=',
        items: [
          { label: '≠', value: '\\neq' },
          { label: '≤', value: '\\leq' },
          { label: '≥', value: '\\geq' },
          { label: '≈', value: '\\approx' },
          { label: '≡', value: '\\equiv' },
          { label: '∼', value: '\\sim' },
          { label: '≺', value: '\\prec' },
          { label: '≻', value: '\\succ' },
          { label: '±', value: '\\pm' },
          { label: '∓', value: '\\mp' },
          { label: '×', value: '\\times' },
          { label: '÷', value: '\\div' },
          { label: '·', value: '\\cdot' },
          { label: '∞', value: '\\infty' },
          { label: '∂', value: '\\partial' },
          { label: '∇', value: '\\nabla' }
        ]
      },
      brackets: {
        name: 'Brackets',
        icon: '[]',
        items: [
          { label: '(…)', value: '\\left( \\right)' },
          { label: '[…]', value: '\\left[ \\right]' },
          { label: '{…}', value: '\\left\\{ \\right\\}' },
          { label: '⟨…⟩', value: '\\langle \\rangle' },
          { label: '|…|', value: '\\left| \\right|' },
          { label: '‖…‖', value: '\\left\\| \\right\\|' },
          { label: '⌊…⌋', value: '\\lfloor \\rfloor' },
          { label: '⌈…⌉', value: '\\lceil \\rceil' }
        ]
      },
      analysis: {
        name: 'Real Analysis',
        icon: 'ℝ',
        items: [
          { label: 'ε-δ', value: '\\forall \\epsilon > 0, \\exists \\delta > 0' },
          { label: '→ as n→∞', value: '\\to \\text{ as } n \\to \\infty' },
          { label: '|x-a|<δ', value: '|x - a| < \\delta' },
          { label: '|f(x)-L|<ε', value: '|f(x) - L| < \\epsilon' },
          { label: 'sup S', value: '\\sup S' },
          { label: 'inf S', value: '\\inf S' },
          { label: 'f: A→B', value: 'f: A \\to B' },
          { label: 'f∘g', value: 'f \\circ g' },
          { label: 'f⁻¹', value: 'f^{-1}' },
          { label: '{xₙ}', value: '\\{x_n\\}_{n=1}^{\\infty}' },
          { label: 'Cauchy', value: '\\forall \\epsilon > 0, \\exists N \\in \\mathbb{N}, \\forall m,n \\geq N: |x_m - x_n| < \\epsilon' }
        ]
      },
      matrices: {
        name: 'Matrices',
        icon: '⬚',
        items: [
          { label: '2×2', value: '\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}' },
          { label: '3×3', value: '\\begin{pmatrix} a & b & c \\\\ d & e & f \\\\ g & h & i \\end{pmatrix}' },
          { label: '[2×2]', value: '\\begin{bmatrix} a & b \\\\ c & d \\end{bmatrix}' },
          { label: 'det', value: '\\begin{vmatrix} a & b \\\\ c & d \\end{vmatrix}' },
          { label: 'cases', value: '\\begin{cases} a & \\text{if } x > 0 \\\\ b & \\text{otherwise} \\end{cases}' }
        ]
      }
    };

    this.init();
  }

  init() {
    if (!this.options.targetTextarea || !this.options.buttonElement) {
      console.warn('EnhancedEquationKeyboard: missing targetTextarea or buttonElement');
      return;
    }

    this.createKeyboard();
    this.attachButtonListener();
  }

  createKeyboard() {
    // Store button reference for positioning
    this.buttonElement = this.options.buttonElement;
    
    // Create main container
    const keyboard = document.createElement('div');
    keyboard.className = 'enhanced-equation-keyboard';
    keyboard.id = `keyboard-${Math.random().toString(36).substr(2, 9)}`;

    // Create header
    const header = document.createElement('div');
    header.className = 'kb-header';
    header.innerHTML = `
      <div class="kb-title">
        <span class="kb-icon">⌨</span>
        <span>Equation Keyboard</span>
      </div>
      <button class="kb-close" aria-label="Close keyboard">✕</button>
    `;

    // Create tabs
    const tabsContainer = document.createElement('div');
    tabsContainer.className = 'kb-tabs';

    Object.entries(this.categories).forEach(([key, category]) => {
      const tab = document.createElement('button');
      tab.className = `kb-tab ${key === this.currentCategory ? 'active' : ''}`;
      tab.dataset.category = key;
      tab.textContent = category.name;
      tabsContainer.appendChild(tab);
    });

    // Create grid container
    const gridContainer = document.createElement('div');
    gridContainer.className = 'kb-grid-container';

    // Create initial grid
    const grid = document.createElement('div');
    grid.className = 'kb-grid';
    grid.id = `grid-${keyboard.id}`;
    this.renderGrid(grid, this.currentCategory);
    gridContainer.appendChild(grid);

    // Create footer
    const footer = document.createElement('div');
    footer.className = 'kb-footer';
    footer.innerHTML = `<p class="kb-hint">💡 Click any button to insert the symbol. Use Math mode with $ signs in proofs.</p>`;

    // Assemble keyboard
    keyboard.appendChild(header);
    keyboard.appendChild(tabsContainer);
    keyboard.appendChild(gridContainer);
    keyboard.appendChild(footer);

    // Create backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'equation-keyboard-backdrop';
    backdrop.id = `backdrop-${Math.random().toString(36).substr(2, 9)}`;
    
    // Attach to document
    document.body.appendChild(backdrop);
    this.backdrop = backdrop;
    
    document.body.appendChild(keyboard);
    this.keyboard = keyboard;

    // Attach event listeners
    this.attachKeyboardListeners();
  }

  renderGrid(gridElement, categoryKey) {
    const category = this.categories[categoryKey];
    if (!category) return;

    gridElement.innerHTML = '';
    category.items.forEach(item => {
      const button = document.createElement('button');
      button.className = 'kb-item';
      button.textContent = item.label;
      button.title = item.value;
      button.dataset.value = item.value;
      button.type = 'button';
      gridElement.appendChild(button);
    });
  }

  attachButtonListener() {
    this.options.buttonElement.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.toggle();
    });
  }

  attachKeyboardListeners() {
    if (!this.keyboard) return;

    // Position keyboard centered on screen (modal style)
    const positionKeyboard = () => {
      const keyboardWidth = 500;
      const keyboardHeight = 600;
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;

      // Center both horizontally and vertically
      const left = (viewportWidth - keyboardWidth) / 2;
      const top = (viewportHeight - keyboardHeight) / 2;

      // Apply positioning
      this.keyboard.style.position = 'fixed';
      this.keyboard.style.left = left + 'px';
      this.keyboard.style.top = top + 'px';
      this.keyboard.style.transform = 'none';
      this.keyboard.style.zIndex = '99999'; // Ensure it's always on top
    };

    // Tab switching
    this.keyboard.querySelectorAll('.kb-tab').forEach(tab => {
      tab.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const categoryKey = tab.dataset.category;
        this.switchCategory(categoryKey);
      });
    });

    // Grid item clicks
    this.keyboard.addEventListener('click', (e) => {
      e.stopPropagation();

      // Item click
      if (e.target.classList.contains('kb-item')) {
        const value = e.target.dataset.value;
        this.insertSymbol(value);
        return;
      }

      // Close button
      if (e.target.classList.contains('kb-close')) {
        this.close();
        return;
      }
    });

    // Close on outside click (including backdrop click)
    document.addEventListener('click', (e) => {
      if (this.backdrop && e.target === this.backdrop) {
        this.close();
        return;
      }
      
      if (this.keyboard && !this.keyboard.contains(e.target) && e.target !== this.options.buttonElement) {
        if (this.isOpen) {
          this.close();
        }
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isOpen) {
        this.close();
      }
    });

    // Position on scroll/resize
    window.addEventListener('scroll', () => {
      if (this.isOpen) {
        positionKeyboard();
      }
    }, true);

    window.addEventListener('resize', () => {
      if (this.isOpen) {
        positionKeyboard();
      }
    });

    // Store positioning function for use in open()
    this.positionKeyboard = positionKeyboard;
  }

  switchCategory(categoryKey) {
    if (!this.categories[categoryKey]) return;

    this.currentCategory = categoryKey;

    // Update active tab
    this.keyboard.querySelectorAll('.kb-tab').forEach(tab => {
      tab.classList.toggle('active', tab.dataset.category === categoryKey);
    });

    // Re-render grid
    const grid = this.keyboard.querySelector('.kb-grid');
    this.renderGrid(grid, categoryKey);
  }

  insertSymbol(value) {
    if (!this.options.targetTextarea) return;

    const textarea = this.options.targetTextarea;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const before = textarea.value.substring(0, start);
    const after = textarea.value.substring(end);

    // Add space before if needed
    const spaceBefore = before && !/\s$/.test(before) ? ' ' : '';
    const spaceAfter = after && !/^\s/.test(after) ? ' ' : '';

    textarea.value = before + spaceBefore + value + spaceAfter + after;
    textarea.selectionStart = textarea.selectionEnd = start + spaceBefore.length + value.length;

    // Trigger input event
    textarea.dispatchEvent(new Event('input', { bubbles: true }));

    // Call callback if provided
    if (this.options.onInsert) {
      this.options.onInsert(value);
    }
  }

  toggle() {
    if (this.isOpen) {
      this.close();
    } else {
      this.open();
    }
  }

  open() {
    if (!this.keyboard) return;
    // Position keyboard centered on screen
    if (this.positionKeyboard) {
      this.positionKeyboard();
    }
    // Show backdrop
    if (this.backdrop) {
      this.backdrop.classList.add('open');
    }
    this.keyboard.classList.add('open');
    this.isOpen = true;
    this.keyboard.style.display = 'flex';
  }

  close() {
    if (!this.keyboard) return;
    // Hide backdrop
    if (this.backdrop) {
      this.backdrop.classList.remove('open');
    }
    this.keyboard.classList.remove('open');
    this.isOpen = false;
    this.keyboard.style.display = 'none';
  }

  destroy() {
    if (this.keyboard) {
      this.keyboard.remove();
    }
    if (this.backdrop) {
      this.backdrop.remove();
    }
  }
}

// Helper: Escape HTML
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
