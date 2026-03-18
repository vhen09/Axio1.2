(function () {
  const KEYS = {
    state: 'axio.workspace.state',
    proofs: 'axio.proofs',
    settings: 'axio.settings'
  };

  const defaultState = {
    theorem: '',
    theoremFontSize: 16,
    stepFontSize: 15,
    steps: [{ id: uid(), text: '', feedback: '', status: '' }],
    selectedStepId: null,
    verificationHistory: [],
    previewMode: 'latex',
    proofStartedAt: null,
    lastEditedAt: null,
    completedAt: null,
    proofCompleted: false,
    draftSubmissionId: null
  };

  const defaultSettings = { theme: 'light', fontScale: 100, notifications: true };

  const KEYBOARD_CATEGORIES = [
    {
      name: 'Fractions',
      symbols: [
        { label: 'a/b', value: '\\frac{a}{b}' },
        { label: 'dx/dy', value: '\\frac{dx}{dy}' },
        { label: '∂/∂x', value: '\\frac{\\partial}{\\partial x}' },
        { label: '1/n', value: '\\frac{1}{n}' },
        { label: 'a+b/c', value: '\\frac{a+b}{c}' },
        { label: 'n!/k!', value: '\\frac{n!}{k!(n-k)!}' }
      ]
    },
    {
      name: 'Superscripts & Subscripts',
      symbols: [
        { label: 'x²', value: 'x^{2}' },
        { label: 'xⁿ', value: 'x^{n}' },
        { label: 'x₁', value: 'x_{1}' },
        { label: 'xₙ', value: 'x_{n}' },
        { label: 'aᵢⱼ', value: 'a_{i,j}' },
        { label: 'eˣ', value: 'e^{x}' }
      ]
    },
    {
      name: 'Radicals',
      symbols: [
        { label: '√x', value: '\\sqrt{x}' },
        { label: '∛x', value: '\\sqrt[3]{x}' },
        { label: 'ⁿ√x', value: '\\sqrt[n]{x}' },
        { label: '√(a²+b²)', value: '\\sqrt{a^2+b^2}' }
      ]
    },
    {
      name: 'Integrals & Summations',
      symbols: [
        { label: '∫', value: '\\int' },
        { label: '∫ₐᵇ', value: '\\int_{a}^{b}' },
        { label: '∑', value: '\\sum_{i=1}^{n}' },
        { label: '∑∞', value: '\\sum_{n=0}^{\\infty}' },
        { label: '∏', value: '\\prod_{i=1}^{n}' },
        { label: '⋂', value: '\\bigcap_{i=1}^{n}' }
      ]
    },
    {
      name: 'Limits',
      symbols: [
        { label: 'lim', value: '\\lim_{x \\to a}' },
        { label: 'lim→∞', value: '\\lim_{n \\to \\infty}' },
        { label: 'lim→0⁺', value: '\\lim_{x \\to 0^{+}}' },
        { label: 'sup', value: '\\sup_{x \\in S}' },
        { label: 'inf', value: '\\inf_{x \\in S}' }
      ]
    },
    {
      name: 'Greek Letters',
      symbols: [
        { label: 'α', value: '\\alpha' },
        { label: 'β', value: '\\beta' },
        { label: 'γ', value: '\\gamma' },
        { label: 'δ', value: '\\delta' },
        { label: 'ε', value: '\\epsilon' },
        { label: 'θ', value: '\\theta' },
        { label: 'λ', value: '\\lambda' },
        { label: 'π', value: '\\pi' }
      ]
    },
    {
      name: 'Set Theory',
      symbols: [
        { label: '∈', value: '\\in' },
        { label: '∉', value: '\\notin' },
        { label: '⊂', value: '\\subset' },
        { label: '⊆', value: '\\subseteq' },
        { label: '∪', value: '\\cup' },
        { label: '∩', value: '\\cap' },
        { label: 'ℝ', value: '\\mathbb{R}' },
        { label: 'ℕ', value: '\\mathbb{N}' }
      ]
    },
    {
      name: 'Logical Symbols',
      symbols: [
        { label: '∀', value: '\\forall' },
        { label: '∃', value: '\\exists' },
        { label: '→', value: '\\rightarrow' },
        { label: '⇒', value: '\\Rightarrow' },
        { label: '⇔', value: '\\Leftrightarrow' },
        { label: '¬', value: '\\neg' },
        { label: '∧', value: '\\land' },
        { label: '∨', value: '\\lor' }
      ]
    },
    {
      name: 'Relations & Operators',
      symbols: [
        { label: '≠', value: '\\neq' },
        { label: '≤', value: '\\leq' },
        { label: '≥', value: '\\geq' },
        { label: '≈', value: '\\approx' },
        { label: '∞', value: '\\infty' },
        { label: '∂', value: '\\partial' }
      ]
    },
    {
      name: 'Brackets',
      symbols: [
        { label: '(…)', value: '\\left( \\right)' },
        { label: '[…]', value: '\\left[ \\right]' },
        { label: '{…}', value: '\\left\\{ \\right\\}' },
        { label: '|…|', value: '\\left| \\right|' },
        { label: '⌊…⌋', value: '\\lfloor \\rfloor' },
        { label: '⌈…⌉', value: '\\lceil \\rceil' }
      ]
    },
    {
      name: 'Real Analysis',
      symbols: [
        { label: 'ε-δ', value: '\\forall \\epsilon > 0, \\exists \\delta > 0' },
        { label: '|x-a|<δ', value: '|x-a| < \\delta' },
        { label: '|f(x)-L|<ε', value: '|f(x)-L| < \\epsilon' },
        { label: 'sup S', value: '\\sup S' },
        { label: 'f: A→B', value: 'f: A \\to B' }
      ]
    },
    {
      name: 'Matrices',
      symbols: [
        { label: '2×2', value: '\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}' },
        { label: '3×3', value: '\\begin{pmatrix} a & b & c \\\\ d & e & f \\\\ g & h & i \\end{pmatrix}' },
        { label: '[2×2]', value: '\\begin{bmatrix} a & b \\\\ c & d \\end{bmatrix}' },
        { label: 'det', value: '\\begin{vmatrix} a & b \\\\ c & d \\end{vmatrix}' },
        { label: 'cases', value: '\\begin{cases} a & \\text{if } x > 0 \\\\ b & \\text{otherwise} \\end{cases}' }
      ]
    }
  ];

  function uid() { return 'id-' + Math.random().toString(36).slice(2, 10); }
  function getJSON(k, d) { try { return JSON.parse(localStorage.getItem(k) || JSON.stringify(d)); } catch { return d; } }
  function setJSON(k, v) { localStorage.setItem(k, JSON.stringify(v)); }
  function getPreferenceTheme() {
    try {
      const prefs = JSON.parse(localStorage.getItem('reana_preferences') || '{}');
      if (typeof prefs.darkTheme === 'boolean') return prefs.darkTheme ? 'dark' : 'light';
    } catch (_) {
    }
    return null;
  }

  let state = getJSON(KEYS.state, defaultState);
  state = {
    ...defaultState,
    ...(state && typeof state === 'object' ? state : {})
  };
  if (!Array.isArray(state.steps) || !state.steps.length) {
    state.steps = [{ id: uid(), text: '', feedback: '', status: '' }];
  }
  state.steps = state.steps.map((step) => ({
    id: step?.id || uid(),
    text: String(step?.text || ''),
    feedback: String(step?.feedback || ''),
    status: String(step?.status || ''),
    verifyAttempts: Number(step?.verifyAttempts || 0)
  }));
  if (!Array.isArray(state.verificationHistory)) state.verificationHistory = [];
  let proofs = getJSON(KEYS.proofs, []);
  let settings = getJSON(KEYS.settings, defaultSettings);

  function applyTheme() {
    const preferenceTheme = getPreferenceTheme();
    if (preferenceTheme) {
      settings.theme = preferenceTheme;
    }
    document.documentElement.classList.toggle('theme-dark', settings.theme === 'dark');
    document.body.classList.toggle('theme-dark', settings.theme === 'dark');
  }

  function ensureMathBackground() {
    if (!document.querySelector('.math-background')) {
      const background = document.createElement('div');
      background.className = 'math-background';
      background.innerHTML = `
        <span class="math-symbol">∫</span>
        <span class="math-symbol">∂</span>
        <span class="math-symbol">∑</span>
        <span class="math-symbol">∞</span>
        <span class="math-symbol">λ</span>
        <span class="math-symbol">∀</span>
        <span class="math-symbol">∃</span>
        <span class="math-symbol">∈</span>
        <span class="math-symbol">→</span>
        <span class="math-symbol">⇒</span>
        <span class="math-symbol">⇔</span>
        <span class="math-symbol">√</span>
        <span class="math-symbol">∆</span>
        <span class="math-symbol">∇</span>
        <span class="math-symbol">∮</span>
      `;
      document.body.prepend(background);
    }

    if (!document.querySelector('.particles')) {
      const particles = document.createElement('div');
      particles.className = 'particles';
      particles.innerHTML = Array.from({ length: 15 }).map(() => '<span class="particle"></span>').join('');
      document.body.prepend(particles);
    }
  }

  function initShared() {
    applyTheme();
    ensureMathBackground();

    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

    const avatar = document.getElementById('avatar-btn');
    const dropdown = document.getElementById('avatar-menu');
    if (avatar && dropdown) {
      avatar.addEventListener('click', () => {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      });
      document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && e.target !== avatar) dropdown.style.display = 'none';
      });
    }
  }

  function renderMath(el, text) {
    if (!el) return;
    const source = (text || '').trim();
    if (!source) {
      el.textContent = '';
      return;
    }

    const hasDelimiters = /(\$\$[\s\S]*\$\$|\$[^$]+\$|\\\([\s\S]*\\\)|\\\[[\s\S]*\\\])/.test(source);

    try {
      if (!hasDelimiters && window.katex && typeof window.katex.render === 'function') {
        window.katex.render(source, el, { throwOnError: false, displayMode: true });
        return;
      }
    } catch (_) {
    }

    el.textContent = source;
    if (window.renderMathInElement) {
      window.renderMathInElement(el, {
        delimiters: [
          { left: '$$', right: '$$', display: true },
          { left: '$', right: '$', display: false },
          { left: '\\(', right: '\\)', display: false },
          { left: '\\[', right: '\\]', display: true }
        ]
      });
    }
  }

  function latexToNatural(text) {
    const source = (text || '').trim();
    if (!source) return 'Natural language preview will appear here...';

    let output = source;

    output = output.replace(/\$\$([\s\S]*?)\$\$/g, ' $1 ');
    output = output.replace(/\$([^$]+)\$/g, ' $1 ');
    output = output.replace(/\\\(([\s\S]*?)\\\)/g, ' $1 ');
    output = output.replace(/\\\[([\s\S]*?)\\\]/g, ' $1 ');

    output = output.replace(/\\frac\{([^{}]+)\}\{([^{}]+)\}/g, '$1 over $2');
    output = output.replace(/\\sqrt\{([^{}]+)\}/g, 'square root of $1');
    output = output.replace(/\\sqrt\[([^{}]+)\]\{([^{}]+)\}/g, '$1 root of $2');
    output = output.replace(/\\lim_\{([^{}]+)\}/g, 'limit as $1');
    output = output.replace(/\\sum_\{([^{}]+)\}\^\{([^{}]+)\}/g, 'sum from $1 to $2');
    output = output.replace(/\\int_\{([^{}]+)\}\^\{([^{}]+)\}/g, 'integral from $1 to $2');

    const commandMap = {
      '\\to': 'approaches',
      '\\infty': 'infinity',
      '\\forall': 'for all',
      '\\exists': 'there exists',
      '\\in': 'is in',
      '\\subseteq': 'is subset of',
      '\\subset': 'is strict subset of',
      '\\Rightarrow': 'implies',
      '\\rightarrow': 'implies',
      '\\Leftrightarrow': 'if and only if',
      '\\land': 'and',
      '\\lor': 'or',
      '\\neg': 'not',
      '\\leq': 'less than or equal to',
      '\\geq': 'greater than or equal to',
      '\neq': 'not equal to',
      '\cdot': 'times'
    };

    Object.entries(commandMap).forEach(([latex, natural]) => {
      output = output.split(latex).join(` ${natural} `);
    });

    output = output
      .replace(/[{}]/g, '')
      .replace(/\^\{([^}]+)\}/g, ' raised to $1 ')
      .replace(/\^([A-Za-z0-9]+)/g, ' raised to $1 ')
      .replace(/_\{([^}]+)\}/g, ' sub $1 ')
      .replace(/_([A-Za-z0-9]+)/g, ' sub $1 ')
      .replace(/\\[a-zA-Z]+/g, ' ')
      .replace(/\\/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();

    return output || source;
  }

  function saveState() {
    if (state && typeof state === 'object') {
      state.lastEditedAt = new Date().toISOString();
      if (!state.proofStartedAt && ((state.theorem || '').trim() || (state.steps || []).some((s) => (s.text || '').trim()))) {
        state.proofStartedAt = new Date().toISOString();
      }
    }
    setJSON(KEYS.state, state);
  }
  function saveSettings() { setJSON(KEYS.settings, settings); applyTheme(); }
  function saveProofs() { setJSON(KEYS.proofs, proofs); }

  function setupWorkspace() {
    const theoremInput = document.getElementById('theorem-input');
    const theoremPreview = document.getElementById('theorem-preview');
    const btnTheoremKeyboard = document.getElementById('btn-theorem-keyboard');
    const btnAPlus = document.getElementById('btn-a-plus');
    const btnAMinus = document.getElementById('btn-a-minus');
    const stepsWrap = document.getElementById('steps-wrap');
    const addBtn = document.getElementById('btn-add-step');
    const removeBtn = document.getElementById('btn-remove-step');
    const newProofBtn = document.getElementById('btn-new-proof');
    const viewProofsBtn = document.getElementById('btn-view-proofs');
    const completeProofBtn = document.getElementById('btn-complete-proof');
    const savePdfBtn = document.getElementById('btn-save-pdf');
    const togglePreviewModeBtn = document.getElementById('btn-toggle-preview-mode');
    const openVerifyPopupBtn = document.getElementById('btn-open-verify-popup');
    const hideVerifyPopupBtn = document.getElementById('btn-hide-verify-popup');
    const closeVerifyPopupBtn = document.getElementById('btn-close-verify-popup');
    const verifyPopup = document.getElementById('verify-popup');
    const verifyPopupList = document.getElementById('verify-popup-list');
    const verifyCountEl = document.getElementById('verify-count');
    const verifyTotalEl = document.getElementById('verify-total');
    const verifyHeaderEl = document.getElementById('verify-header');
    const verifyHeaderIconEl = document.getElementById('verify-header-icon');
    const verifyHeaderLabelEl = document.getElementById('verify-header-label');
    const verifyCurrentStepEl = document.getElementById('verify-current-step');
    const verifyStatusValueEl = document.getElementById('verify-status-value');
    const verifyInputSummaryEl = document.getElementById('verify-input-summary');
    const verifyJustificationEl = document.getElementById('verify-justification');
    const verifyMissingWrapEl = document.getElementById('verify-missing-wrap');
    const verifyMissingEl = document.getElementById('verify-missing');
    const verifyImprovementWrapEl = document.getElementById('verify-improvement-wrap');
    const verifyImprovementEl = document.getElementById('verify-improvement');
    const verifyHintWrapEl = document.getElementById('verify-hint-wrap');
    const verifyHintEl = document.getElementById('verify-hint');
    const verifyNextGuideEl = document.getElementById('verify-next-guide');
    const nextStepGuideBtn = document.getElementById('btn-next-step-guide');

    let activeTextarea = theoremInput;
    let syncTimer = null;
    let syncInFlight = false;
    let lastVerificationResult = null;

    theoremInput.value = state.theorem || '';
    theoremInput.style.fontSize = state.theoremFontSize + 'px';
    theoremInput.style.transition = 'font-size .2s ease';

    function updateSavePdfButtonState() {
      if (savePdfBtn) savePdfBtn.disabled = !state.proofCompleted;
    }

    function markProofDirty() {
      if (state.proofCompleted) {
        state.proofCompleted = false;
        state.completedAt = null;
      }
      updateSavePdfButtonState();
    }

    function updatePreviewModeButtonLabel() {
      if (!togglePreviewModeBtn) return;
      togglePreviewModeBtn.textContent = state.previewMode === 'natural' ? 'Natural Language Mode' : 'LaTeX Mode';
    }

    function rerenderTheoremPreview() {
      if (state.previewMode === 'natural') {
        theoremPreview.textContent = latexToNatural(theoremInput.value || '');
        theoremPreview.classList.add('preview-natural');
        return;
      }
      theoremPreview.classList.remove('preview-natural');
      renderMath(theoremPreview, theoremInput.value || 'Live preview will appear here...');
    }

    function updateStepPreviewVisibility(card, stepId) {
      const latexPreview = card.querySelector('#preview-' + stepId);
      const naturalPreview = card.querySelector('#preview-natural-' + stepId);
      if (!latexPreview || !naturalPreview) return;

      if (state.previewMode === 'natural') {
        latexPreview.style.display = 'none';
        naturalPreview.style.display = 'block';
      } else {
        latexPreview.style.display = 'block';
        naturalPreview.style.display = 'none';
      }
    }

    function calculateCompletionSeconds() {
      if (!state.proofStartedAt) return 0;
      const started = new Date(state.proofStartedAt).getTime();
      const ended = Date.now();
      if (Number.isNaN(started) || ended <= started) return 0;
      return Math.max(1, Math.round((ended - started) / 1000));
    }

    function collectProofMetrics(steps) {
      const safeSteps = Array.isArray(steps) ? steps : [];
      const verifyHistory = Array.isArray(state.verificationHistory) ? state.verificationHistory : [];

      const incorrectSteps = safeSteps.filter((step) => step.status && step.status !== 'correct').length;
      const hintsUsed = verifyHistory.filter((record) => String(record?.hint || '').trim().length > 0).length;
      const attemptsByStep = {};

      verifyHistory.forEach((record) => {
        const key = String(record.stepId || '');
        if (!key) return;
        attemptsByStep[key] = (attemptsByStep[key] || 0) + 1;
      });

      const attemptValues = Object.values(attemptsByStep);
      const avgVerificationAttemptsPerStep = attemptValues.length
        ? Number((attemptValues.reduce((sum, value) => sum + value, 0) / attemptValues.length).toFixed(2))
        : 0;

      return {
        incorrect_steps: incorrectSteps,
        hints_used: hintsUsed,
        verification_attempts: verifyHistory.length,
        avg_verification_attempts_per_step: avgVerificationAttemptsPerStep,
        completion_seconds: calculateCompletionSeconds(),
        started_at: state.proofStartedAt || new Date().toISOString(),
        last_edited_at: state.lastEditedAt || new Date().toISOString(),
        completed_at: new Date().toISOString()
      };
    }

    function buildInputSummary(rawText) {
      const raw = String(rawText || '').trim();
      const t = raw.toLowerCase();

      if (!raw) {
        return 'Student has not yet provided a complete mathematical step.';
      }

      const parts = [];

      if (/\b(let|define|set|assume)\b/.test(t)) {
        parts.push('introduces definitions or assumptions');
      }

      if (/[=<>≤≥]/.test(raw)) {
        parts.push('states a formal relation between mathematical expressions');
      }

      if (/\btherefore\b|\bhence\b|\bthus\b|\bimplies\b|\bconclude\b|\bqed\b|\bproved\b/.test(t)) {
        parts.push('attempts to derive a conclusion from prior steps');
      }

      let appliedRule = '';
      if (/triangle inequality/.test(t)) appliedRule = 'Triangle Inequality';
      else if (/completeness axiom|supremum|least upper bound/.test(t)) appliedRule = 'Completeness Axiom';
      else if (/cauchy/.test(t)) appliedRule = 'Cauchy Criterion';
      else if (/induction/.test(t)) appliedRule = 'Mathematical Induction';
      else if (/contradiction/.test(t)) appliedRule = 'Proof by Contradiction';
      else if (/\bbounded\b/.test(t)) appliedRule = 'Boundedness Argument';
      else if (/\blimit\b|\\lim/.test(t)) appliedRule = 'Limit Argument';

      if (appliedRule) {
        parts.push(`references ${appliedRule}`);
      }

      if (!parts.length) {
        return 'Student presents a mathematical claim intended to advance the proof, but the logical role is not explicitly identified.';
      }

      if (parts.length === 1) {
        return `Student ${parts[0]}.`;
      }

      const head = parts.slice(0, -1).join(', ');
      const tail = parts[parts.length - 1];
      return `Student ${head}, and ${tail}.`;
    }

    function inferAppliedRule(rawText) {
      const t = String(rawText || '').toLowerCase();
      if (/triangle inequality/.test(t)) return 'Triangle Inequality';
      if (/completeness axiom|supremum|least upper bound/.test(t)) return 'Completeness Axiom';
      if (/cauchy/.test(t)) return 'Cauchy Criterion';
      if (/induction/.test(t)) return 'Mathematical Induction';
      if (/contradiction/.test(t)) return 'Proof by Contradiction';
      if (/\blimit\b|\\lim/.test(t)) return 'Limit Argument';
      if (/\bbounded\b/.test(t)) return 'Boundedness Argument';
      if (/[=<>≤≥]/.test(rawText || '')) return 'Algebraic Transformation';
      return 'Logical Inference';
    }

    function summarizeTheoremGoal(theoremText) {
      const natural = latexToNatural(String(theoremText || '')).replace(/\s+/g, ' ').trim();
      if (!natural) return 'the target theorem statement';
      const words = natural.split(' ').slice(0, 16).join(' ');
      return words.length < natural.length ? `${words}...` : words;
    }

    function extractMeaningfulWords(text) {
      const stopWords = new Set([
        'the', 'a', 'an', 'and', 'or', 'if', 'then', 'is', 'are', 'to', 'of', 'in', 'for', 'with', 'by', 'on',
        'that', 'this', 'we', 'let', 'assume', 'therefore', 'thus', 'hence', 'from', 'as', 'be', 'it'
      ]);

      const cleaned = String(text || '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ');
      return new Set(
        cleaned
          .split(/\s+/)
          .map((word) => word.trim())
          .filter((word) => word.length >= 4 && !stopWords.has(word))
      );
    }

    function extractConceptTags(text) {
      const t = String(text || '')
        .toLowerCase()
        .replace(/\\varepsilon/g, ' epsilon ')
        .replace(/\\epsilon/g, ' epsilon ')
        .replace(/\\delta/g, ' delta ')
        .replace(/\\to/g, ' to ')
        .replace(/\\lim/g, ' lim ');
      const tags = new Set();

      if (/\blimit\b|\\lim|epsilon|delta|converg/.test(t)) tags.add('limit');
      if (/bounded|upper bound|lower bound/.test(t)) tags.add('boundedness');
      if (/supremum|least upper bound|infimum/.test(t)) tags.add('completeness');
      if (/cauchy/.test(t)) tags.add('cauchy');
      if (/triangle inequality/.test(t)) tags.add('triangle-inequality');
      if (/induction/.test(t)) tags.add('induction');
      if (/contradiction/.test(t)) tags.add('contradiction');
      if (/sequence|series/.test(t)) tags.add('sequence-series');
      if (/continuity|continuous/.test(t)) tags.add('continuity');
      if (/derivative|differentiat/.test(t)) tags.add('derivative');
      if (/integral|integrab/.test(t)) tags.add('integral');

      return tags;
    }

    function hasEpsilonDeltaStepShape(stepText) {
      const t = String(stepText || '')
        .toLowerCase()
        .replace(/\\varepsilon/g, ' epsilon ')
        .replace(/\\epsilon/g, ' epsilon ')
        .replace(/\\delta/g, ' delta ');

      const hasEpsilon = /(epsilon|ε)/.test(t);
      const hasDelta = /(delta|δ)/.test(t);
      const hasImplication = /(implies|=>|⇒|\bif\b|then)/.test(t);
      const hasAbsIneq = /\|[^|]+\|\s*[<≤]/.test(t);

      return hasEpsilon && hasDelta && hasImplication && hasAbsIneq;
    }

    function computeTheoremAlignment(stepText, theoremText) {
      const theoremWords = extractMeaningfulWords(latexToNatural(theoremText || ''));
      const stepWords = extractMeaningfulWords(latexToNatural(stepText || ''));
      const theoremConcepts = extractConceptTags(latexToNatural(theoremText || ''));
      const stepConcepts = extractConceptTags(latexToNatural(stepText || ''));

      if ((!theoremWords.size || !stepWords.size) && (!theoremConcepts.size || !stepConcepts.size)) {
        return {
          wordScore: 0,
          conceptScore: 0,
          overall: 0,
          matchedConcepts: []
        };
      }

      let overlap = 0;
      theoremWords.forEach((word) => {
        if (stepWords.has(word)) overlap += 1;
      });

      let conceptOverlap = 0;
      const matchedConcepts = [];
      theoremConcepts.forEach((tag) => {
        if (stepConcepts.has(tag)) {
          conceptOverlap += 1;
          matchedConcepts.push(tag);
        }
      });

      const wordScore = overlap / Math.max(1, Math.min(theoremWords.size || 1, stepWords.size || 1));
      const conceptScore = conceptOverlap / Math.max(1, theoremConcepts.size || 1);
      const overall = (wordScore * 0.6) + (conceptScore * 0.4);

      return {
        wordScore,
        conceptScore,
        overall,
        matchedConcepts
      };
    }

    function buildConciseProofExplanation(rawText, statusType) {
      const rule = inferAppliedRule(rawText);
      if (statusType === 'correct') {
        return `Assessment: The step is valid; ${rule} is applied consistently and the inference follows from the established line of reasoning.`;
      }
      if (statusType === 'incomplete') {
        return `Assessment: The step is directionally relevant, but the use of ${rule} is not yet supported by sufficient intermediate justification.`;
      }
      if (statusType === 'uncertain') {
        return `Assessment: The claim references ${rule}, but ambiguous wording prevents formal verification of the logical implication.`;
      }
      return `Assessment: The intended use of ${rule} is plausible, but the transition from the previous statement remains unproven.`;
    }

    function applyTheoremContext(feedback, rawText, theoremText, statusType) {
      const theoremGoal = summarizeTheoremGoal(theoremText);
      const alignment = computeTheoremAlignment(rawText, theoremText);

      if (!String(theoremText || '').trim()) {
        return feedback;
      }

      if (statusType === 'correct') {
        feedback.justification = `${feedback.justification} This contributes directly toward proving: ${theoremGoal}`;
        feedback.nextStepSuggestion = `Derive the next consequence that narrows the gap to: ${theoremGoal}`;
        return feedback;
      }

      if (alignment.overall < 0.2) {
        feedback.missing = `The step is not yet clearly connected to the theorem goal (${theoremGoal}).`;
        feedback.improvement = 'State explicitly how this line advances the theorem statement.';
      } else if (alignment.matchedConcepts.length > 0) {
        feedback.improvement = `Keep the argument anchored on theorem concepts: ${alignment.matchedConcepts.join(', ')}.`;
      }

      feedback.nextStepSuggestion = `Revise this step so it explicitly advances the target theorem: ${theoremGoal}`;
      return feedback;
    }

    function humanizeConceptTag(tag) {
      const map = {
        'limit': 'limit relation',
        'boundedness': 'boundedness claim',
        'completeness': 'completeness idea',
        'cauchy': 'Cauchy condition',
        'triangle-inequality': 'triangle inequality',
        'induction': 'induction structure',
        'contradiction': 'contradiction setup',
        'sequence-series': 'sequence/series behavior',
        'continuity': 'continuity condition',
        'derivative': 'derivative relation',
        'integral': 'integral relation'
      };
      return map[tag] || 'the theorem goal';
    }

    function buildRandomNextStepHint(result, theoremText, stepText = '') {
      const theoremTags = Array.from(extractConceptTags(latexToNatural(theoremText || '')));
      const stepTags = Array.from(extractConceptTags(latexToNatural(stepText || '')));
      const matched = theoremTags.filter((tag) => stepTags.includes(tag));
      const focusTag = matched[0] || theoremTags[0] || stepTags[0] || '';
      const focus = focusTag ? humanizeConceptTag(focusTag) : 'target expression';
      const rule = inferAppliedRule(stepText || theoremText || 'this step');
      const isCorrect = result?.cls === 'correct';

      const correctHints = [
        `Hint: Add exactly one short intermediate line that keeps focus on the ${focus}.`,
        `Hint: Before writing the next conclusion, state one checkable relation justified by ${rule}.`,
        `Hint: Continue with one small transformation that narrows the gap to the theorem target.`,
        `Hint: Keep the same direction—write one consequence from the current line, then verify it.`
      ];

      const revisionHints = [
        `Hint: Rewrite this step as one verifiable claim connected to the ${focus}.`,
        `Hint: Add a missing reason first (definition/theorem), then state one resulting relation.`,
        `Hint: Simplify to one precise statement that can be justified from the previous line.`,
        `Hint: Start with a smaller sub-goal tied to the theorem, then prove that sub-goal.`
      ];

      const pool = isCorrect ? correctHints : revisionHints;
      const idx = Math.floor(Math.random() * pool.length);
      return pool[idx];
    }

    function verifyLogic(text, theoremText) {
      const raw = String(text || '').trim();
      const t = raw.toLowerCase();
      const theoremRaw = String(theoremText || '').trim();

      const inputSummary = buildInputSummary(raw);
      const hasConnector = /(therefore|hence|thus|since|assume|let|implies|by|\bqed\b|conclude|proved)/.test(t);
      const hasMath = /[=<>≤≥\-+*/^]|\\/.test(raw);
      const hasUncertainLanguage = /(maybe|i think|not sure|idk|i don't know|guess)/.test(t);
      const alignmentInfo = theoremRaw ? computeTheoremAlignment(raw, theoremRaw) : { overall: 1, matchedConcepts: [] };
      const theoremMisaligned = theoremRaw && alignmentInfo.overall < 0.18;

      if (!raw || raw.length < 18) {
        return applyTheoremContext({
          cls: 'warn',
          statusLabel: 'Needs Revision',
          msg: 'Step is incomplete.',
          inputSummary,
          justification: buildConciseProofExplanation(raw, 'incomplete'),
          missing: 'A clear mathematical relation and a named reason are missing.',
          improvement: 'State one explicit relation, then explain why it follows.',
          hint: 'Focus on the core idea that links this step to the previous one.',
          nextStepSuggestion: 'Add one justified intermediate statement before the conclusion.'
        }, raw, theoremText, 'incomplete');
      }

      if (hasUncertainLanguage) {
        return applyTheoremContext({
          cls: 'warn',
          statusLabel: 'Needs Revision',
          msg: 'Step is not mathematically justified.',
          inputSummary,
          justification: buildConciseProofExplanation(raw, 'uncertain'),
          missing: 'A definitive mathematical claim with justification is required.',
          improvement: 'Replace uncertain language with a precise statement and support.',
          hint: 'Use a direct claim that can be checked from prior results.',
          nextStepSuggestion: 'Rewrite this step as a verifiable statement tied to the previous line.'
        }, raw, theoremText, 'uncertain');
      }

      if (theoremMisaligned && hasMath) {
        return applyTheoremContext({
          cls: 'warn',
          statusLabel: 'Needs Revision',
          msg: 'Step is weakly aligned with the theorem.',
          inputSummary,
          justification: 'Assessment: The manipulation may be mathematically sound, but its relevance to the theorem objective is not established.',
          missing: 'An explicit connection from this step to the theorem claim is required.',
          improvement: 'State which part of the theorem this step is advancing.',
          hint: 'Tie the step to a theorem keyword or target quantity.',
          nextStepSuggestion: 'Rewrite this line so the theorem objective is directly referenced.'
        }, raw, theoremText, 'needs-work');
      }

      if (hasConnector && hasMath) {
        return applyTheoremContext({
          cls: 'correct',
          statusLabel: 'Correct',
          msg: 'Correct step.',
          inputSummary,
          justification: buildConciseProofExplanation(raw, 'correct'),
          missing: 'No critical issue detected.',
          improvement: 'Optionally cite the exact previous step for stronger rigor.',
          hint: 'Keep your next step aligned with the same logical direction.',
          nextStepSuggestion: 'Advance by deriving the next relation that moves closer to the target conclusion.'
        }, raw, theoremText, 'correct');
      }

      return applyTheoremContext({
        cls: 'warn',
        statusLabel: 'Needs Revision',
        msg: 'Needs stronger justification.',
        inputSummary,
        justification: buildConciseProofExplanation(raw, 'needs-work'),
        missing: 'The bridge from the previous statement to this claim is unclear.',
        improvement: 'Add a concise reason (definition, theorem, or algebraic step).',
        hint: 'Think about which known property justifies this move.',
        nextStepSuggestion: 'Insert one intermediate justified line before this statement.'
      }, raw, theoremText, 'needs-work');
    }

    function normalizeAiStatus(statusText) {
      const t = String(statusText || '').toLowerCase();
      const firstLine = t.split('\n')[0] || t;

      if (/\bincorrect\b|\bwrong\b|\binvalid\b/.test(firstLine)) {
        return { cls: 'warn', statusLabel: 'Needs Revision', msg: 'Step is incorrect.' };
      }
      if (/\bneeds work\b|\bneeds revision\b/.test(firstLine)) {
        return { cls: 'warn', statusLabel: 'Needs Revision', msg: 'Needs stronger justification.' };
      }
      if (/\bcorrect\b/.test(firstLine)) {
        return { cls: 'correct', statusLabel: 'Correct', msg: 'Correct step.' };
      }
      return { cls: 'warn', statusLabel: 'Needs Revision', msg: 'Needs stronger justification.' };
    }

    function parseAiVerificationSections(aiText) {
      const raw = String(aiText || '').replace(/\r\n/g, '\n').trim();
      if (!raw) return null;

      const headingPattern = /(Status|Input Summary|Justification|What(?:’|')s Wrong \/ Missing|Improvement|Hint|Next Step Guide)\s*:?[ \t]*\n?/gi;
      const sections = {};
      let lastIndex = 0;
      let currentKey = null;
      let match;

      while ((match = headingPattern.exec(raw)) !== null) {
        if (currentKey !== null) {
          const chunk = raw.slice(lastIndex, match.index).trim();
          if (chunk) sections[currentKey] = chunk;
        }

        currentKey = match[1].toLowerCase();
        lastIndex = headingPattern.lastIndex;
      }

      if (currentKey !== null) {
        const chunk = raw.slice(lastIndex).trim();
        if (chunk) sections[currentKey] = chunk;
      }

      const normalized = {
        status: sections['status'] || '',
        inputSummary: sections['input summary'] || '',
        justification: sections['justification'] || '',
        missing: sections["what’s wrong / missing"] || sections["what's wrong / missing"] || '',
        improvement: sections['improvement'] || '',
        hint: sections['hint'] || '',
        nextStepSuggestion: sections['next step guide'] || ''
      };

      return normalized;
    }

    function toStructuredAiResult(aiResponse, fallbackResult, rawStepText, theoremText) {
      const parsed = parseAiVerificationSections(aiResponse);
      if (!parsed) return fallbackResult;

      const statusMeta = normalizeAiStatus(parsed.status || fallbackResult.statusLabel || 'Needs Revision');
      const justification = String(parsed.status || '').replace(/[✅⚠️❌✓]/g, '').trim();

      const alignmentInfo = computeTheoremAlignment(rawStepText, theoremText || '');
      const theoremHasLimit = /\\lim|\blim\b|\bto\b/.test(String(theoremText || '').toLowerCase());
      const epsilonDeltaShape = hasEpsilonDeltaStepShape(rawStepText);
      const theoremMismatchByAlignment = String(theoremText || '').trim()
        && alignmentInfo.overall < 0.12
        && alignmentInfo.conceptScore < 0.01
        && !(theoremHasLimit && epsilonDeltaShape);
      const combinedAiText = [parsed.status, parsed.inputSummary, parsed.missing, parsed.improvement, parsed.nextStepSuggestion]
        .map((item) => String(item || '').toLowerCase())
        .join(' ');
      const theoremMismatchByText = /(does not match|not match|mismatch|not aligned|not connected|off-topic|irrelevant|doesn't align)/.test(combinedAiText);

      const aiStatusCorrect = statusMeta.cls === 'correct';
      const forcedMismatch = theoremMismatchByText || (theoremMismatchByAlignment && !aiStatusCorrect);
      const finalCls = forcedMismatch ? 'warn' : statusMeta.cls;
      const finalStatusLabel = forcedMismatch ? 'Needs Revision' : statusMeta.statusLabel;
      const finalMsg = forcedMismatch ? 'Step does not match theorem statement.' : statusMeta.msg;

      return {
        cls: finalCls,
        statusLabel: finalStatusLabel,
        msg: finalMsg,
        inputSummary: parsed.inputSummary || fallbackResult.inputSummary,
        justification: parsed.justification || justification || fallbackResult.justification,
        missing: forcedMismatch
          ? (parsed.missing || 'The current step is not sufficiently aligned with the theorem statement.')
          : (parsed.missing || (finalCls === 'correct' ? 'No critical issue detected.' : fallbackResult.missing)),
        improvement: forcedMismatch
          ? (parsed.improvement || 'Rewrite the step so it explicitly advances the theorem target expression.')
          : (parsed.improvement || fallbackResult.improvement),
        hint: parsed.hint || (statusMeta.cls === 'correct' ? fallbackResult.hint : fallbackResult.hint),
        nextStepSuggestion: forcedMismatch
          ? (parsed.nextStepSuggestion || `Return to the theorem target and derive a line directly connected to: ${summarizeTheoremGoal(theoremText || '')}`)
          : (parsed.nextStepSuggestion || fallbackResult.nextStepSuggestion),
        _source: 'ai',
        _rawStepText: rawStepText,
        verifiedStepNumber: fallbackResult.verifiedStepNumber || null
      };
    }

    function normalizeVerificationConsistency(result) {
      const normalized = {
        ...result
      };

      const isCorrect = normalized.cls === 'correct';
      const justificationText = String(normalized.justification || '').trim();
      const missingText = String(normalized.missing || '').trim();
      const improvementText = String(normalized.improvement || '').trim();

      const cleanupImprovement = (text) => {
        const value = String(text || '').trim();
        if (!value) return '';
        if (/^needed\.?$/i.test(value)) return '';
        if (/^none\s+needed\.?$/i.test(value)) return '';
        if (/^not\s+needed\.?$/i.test(value)) return '';
        return value;
      };

      if (isCorrect) {
        return normalized;
      }

      const invalidJustification = !justificationText
        || /^(correct|nothing|none|n\/?a|valid|ok)\.?$/i.test(justificationText);
      const cleanedImprovementText = cleanupImprovement(improvementText);
      const invalidImprovement = !cleanedImprovementText
        || /^(nothing|none|n\/?a|ok|no improvement|no changes|not required)\.?$/i.test(cleanedImprovementText);

      if (invalidJustification) {
        if (missingText && !/^(nothing|none|n\/?a)\.?$/i.test(missingText)) {
          normalized.justification = `Issue: ${missingText}`;
        } else if (improvementText && !/^(nothing|none|n\/?a)\.?$/i.test(improvementText)) {
          normalized.justification = `Issue: ${improvementText}`;
        } else {
          normalized.justification = 'Issue: This step is not yet fully justified relative to the theorem and prior proof line.';
        }
      }

      if (!missingText || /^(nothing|none|n\/?a)\.?$/i.test(missingText)) {
        normalized.missing = 'The logical reason connecting this step to the theorem objective is not explicitly established.';
      }

      if (invalidImprovement) {
        normalized.improvement = isCorrect
          ? 'No revision required. You may optionally cite the exact algebraic transformation for full rigor.'
          : 'Improve this step by naming the exact theorem property used and showing one explicit intermediate line that links it to the theorem target.';
      } else {
        normalized.improvement = cleanedImprovementText;
      }

      return normalized;
    }

    async function verifyStepWithAi(stepText, stepIndex, fallbackResult) {
      const theorem = String(state.theorem || theoremInput.value || '').trim();
      if (!theorem) return fallbackResult;

      const previousSteps = (state.steps || [])
        .slice(0, Math.max(0, stepIndex))
        .map((step) => String(step?.text || '').trim())
        .filter((value) => value.length > 0);

      try {
        const response = await fetch('../../backend/api/tutor.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'verify_step',
            theorem,
            step: String(stepText || '').trim(),
            stepNumber: stepIndex + 1,
            previousSteps
          })
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
          return fallbackResult;
        }

        const aiResponseText = String(data.response || data.assistance || '').trim();
        if (!aiResponseText) {
          return fallbackResult;
        }

        return normalizeVerificationConsistency(
          toStructuredAiResult(aiResponseText, fallbackResult, stepText, theorem)
        );
      } catch (_) {
        return normalizeVerificationConsistency(fallbackResult);
      }
    }

    function getSanitizedProofSteps() {
      return (state.steps || [])
        .map((step) => {
          const text = String(step?.text || '').trim();
          return {
            text,
            verified: step?.status === 'correct'
          };
        })
        .filter((step) => step.text.length > 0);
    }

    function computeProofScore(steps) {
      if (!steps.length) return 0;
      const verifiedCount = steps.filter((step) => step.verified).length;
      return Math.round((verifiedCount / steps.length) * 100);
    }

    function getWorkspaceDraftSteps() {
      return (state.steps || [])
        .map((step) => ({
          text: String(step?.text || '').trim(),
          verified: step?.status === 'correct',
          status: String(step?.status || ''),
          verify_attempts: Number(step?.verifyAttempts || 0)
        }))
        .filter((step) => step.text.length > 0);
    }

    function computeLiveScoreFromState() {
      const steps = getWorkspaceDraftSteps();
      if (!steps.length) return 0;
      return computeProofScore(steps);
    }

    async function syncWorkspaceDraft(options = {}) {
      const { silent = true } = options;

      if (syncInFlight) return false;

      const theorem = String(state.theorem || '').trim();
      const steps = getWorkspaceDraftSteps();
      if (!theorem && steps.length === 0) return true;

      const payload = {
        action: 'save_draft_proof',
        submission_id: state.draftSubmissionId || null,
        theorem,
        steps,
        live_score: computeLiveScoreFromState(),
        proof_meta: collectProofMetrics(state.steps || []),
        is_completed: false,
        is_verified: false,
        verification_status: 'pending',
        verification_summary: 'Workspace autosave snapshot.'
      };

      syncInFlight = true;
      try {
        const response = await fetch('../../backend/api/submissions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
          throw new Error(data.error || 'Draft sync failed');
        }

        if (data.submission_id && Number(data.submission_id) > 0 && Number(state.draftSubmissionId || 0) !== Number(data.submission_id)) {
          state.draftSubmissionId = Number(data.submission_id);
          saveState();
        }
        return true;
      } catch (error) {
        if (!silent) {
          alert('Unable to sync proof changes. Please check connection and retry.');
        }
        return false;
      } finally {
        syncInFlight = false;
      }
    }

    function scheduleWorkspaceSync(delayMs = 900) {
      if (syncTimer) clearTimeout(syncTimer);
      syncTimer = setTimeout(() => {
        syncWorkspaceDraft({ silent: true });
      }, delayMs);
    }

    async function completeProof() {
      const theorem = String(state.theorem || '').trim();
      const steps = getSanitizedProofSteps();

      if (theorem.length < 5) {
        alert('Please provide a theorem statement before completing your proof.');
        return;
      }

      if (steps.length < 2) {
        alert('Please add at least 2 proof steps before completing your proof.');
        return;
      }

      const allVerified = steps.every((step) => step.verified);
      if (!allVerified) {
        alert('Please verify all steps first. Each step must show as Correct.');
        return;
      }

      const payload = {
        action: 'save_complete_proof',
        theorem,
        steps,
        proof_meta: collectProofMetrics(state.steps || []),
        draft_submission_id: state.draftSubmissionId || null,
        is_completed: true,
        is_verified: true,
        verification_status: 'success',
        verification_summary: 'Proof completed from workspace.',
        score: computeProofScore(steps)
      };

      if (completeProofBtn) completeProofBtn.disabled = true;

      try {
        const response = await fetch('../../backend/api/submissions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
          throw new Error(data.error || 'Failed to save complete proof.');
        }

        state.proofCompleted = true;
        state.completedAt = new Date().toISOString();
        if (data.submission_id) state.draftSubmissionId = Number(data.submission_id);
        saveState();
        updateSavePdfButtonState();

        // Update localStorage proofs array with the new completed proof
        // This ensures the score appears immediately on the scores page
        const newProof = {
          id: data.submission_id || state.draftSubmissionId || Date.now(),
          theorem: state.theorem || '',
          steps: steps.length,
          accuracy: data.score || 0,
          status: 'completed',
          verified: true,
          completedAt: state.completedAt
        };
        
        // Add or update the proof in the proofs array
        const proofIndex = proofs.findIndex(p => p.id === newProof.id);
        if (proofIndex >= 0) {
          proofs[proofIndex] = newProof;
        } else {
          proofs.unshift(newProof); // Add to beginning
        }
        
        // Save updated proofs array to localStorage
        saveJSON(KEYS.proofs, proofs);

        const result = {
          cls: 'correct',
          msg: `Complete proof saved successfully (Submission #${data.submission_id}, Score ${data.score}%).`
        };
        renderVerifyPanel(result);
        alert('✓ Complete proof saved successfully!');
      } catch (error) {
        const result = {
          cls: 'warn',
          msg: String(error?.message || 'Unable to save complete proof.')
        };
        renderVerifyPanel(result);
        alert('✗ ' + result.msg);
      } finally {
        if (completeProofBtn) completeProofBtn.disabled = false;
      }
    }

    async function saveDraftProof() {
      return syncWorkspaceDraft({ silent: false });
    }

    function generateDetailedConclusion(theorem, steps) {
      const theoremText = String(theorem).toLowerCase();
      const stepsText = steps.map(s => String(s.text).toLowerCase()).join(' ');
      const allText = theoremText + ' ' + stepsText;
      
      let methodKeyword = '';
      let methodPhrase = '';
      
      if (allText.includes('intermediate value') || allText.includes('ivt')) {
        methodKeyword = 'Intermediate Value Theorem';
        methodPhrase = `By the ${methodKeyword}, having established that the function is continuous and changes sign over the given interval, we have proven that`;
      } else if (allText.includes('continuity') || allText.includes('continuous')) {
        methodKeyword = 'Continuity';
        methodPhrase = `By the properties of ${methodKeyword}, having verified the function\'s behavior at critical points, we have demonstrated that`;
      } else if (allText.includes('limit') || allText.includes('convergence') || allText.includes('converges')) {
        methodKeyword = 'Limit Properties';
        methodPhrase = `By the properties of ${methodKeyword}, through sequential analysis and convergence verification, we have established that`;
      } else if (allText.includes('derivative') || allText.includes('differentiable')) {
        methodKeyword = 'Differentiability';
        methodPhrase = `Using ${methodKeyword} and calculus principles, we have shown that`;
      } else if (allText.includes('induction') || allText.includes('mathematical induction')) {
        methodKeyword = 'Mathematical Induction';
        methodPhrase = `By ${methodKeyword}, having verified the base case and the inductive step, we conclude that`;
      } else if (allText.includes('cauchy') || allText.includes('subsequence') || allText.includes('subsequences')) {
        methodKeyword = 'Sequence Properties';
        methodPhrase = `Through analysis of ${methodKeyword}, particularly Cauchy sequences and convergence criteria, we have verified that`;
      } else {
        methodPhrase = `Through careful logical analysis and systematic verification of the proof steps, we have established that`;
      }
      
      return `${methodPhrase} the statement holds as required.`;
    }

    function saveProofAsPdf() {
      const theorem = String(state.theorem || '').trim();
      const steps = getSanitizedProofSteps();

      if (!theorem && steps.length === 0) {
        alert('No proof content available to export.');
        return;
      }

      const escape = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      const theoremNatural = latexToNatural(theorem);
      const htmlSteps = steps.map((step, index) =>
        `<li><strong>Step ${index + 1}:</strong> ${escape(latexToNatural(step.text))}</li>`
      ).join('');
      
      const detailedConclusion = generateDetailedConclusion(theorem, steps);

      const win = window.open('', '_blank');
      if (!win) {
        alert('Please allow popups to export PDF.');
        return;
      }

      win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8" />
          <title>AXIO Complete Proof</title>
          <style>
            body { font-family: Inter, Segoe UI, Arial, sans-serif; padding: 24px; color: #0f172a; }
            h1 { margin: 0 0 8px; color: #0f766e; }
            h2 { margin: 18px 0 8px; color: #0f172a; }
            p, li { line-height: 1.5; }
            ul { padding-left: 20px; }
            .meta { color: #64748b; font-size: 13px; margin-bottom: 16px; }
            .conclusion { margin-top: 24px; padding: 16px; border-left: 4px solid #0f766e; background-color: #f0fdf4; }
          </style>
        </head>
        <body>
          <h1>AXIO Complete Proof</h1>
          <div class="meta">Generated: ${new Date().toLocaleString()}</div>
          <h2>Theorem</h2>
          <p>${escape(theoremNatural || 'N/A')}</p>
          <h2>Proof Steps</h2>
          <ul>${htmlSteps || '<li>No steps provided.</li>'}</ul>
          <div class="conclusion">
            <h2>Conclusion</h2>
            <p>${escape(detailedConclusion)}</p>
            <p style="margin-top: 12px; font-style: italic; font-size: 14px;">This completes the proof of the theorem: <em>${escape(theoremNatural || 'the given theorem')}</em></p>
          </div>
        </body>
        </html>
      `);
      win.document.close();
      win.focus();
      win.print();
    }

    function setFeedback(stepId, result) {
      const step = state.steps.find(s => s.id === stepId);
      if (!step) return;
      step.feedback = result.cls === 'correct' ? '' : `${result.statusLabel}: ${result.justification}`;
      step.status = result.cls;
      step.verifyAttempts = Number(step.verifyAttempts || 0) + 1;
      saveState();
    }

    function renderVerifyPanel(result) {
      updateVerificationPanel(result);
    }

    // Helper function to clean emoji/icons from feedback text
    function cleanFeedbackText(text) {
      if (!text) return '';
      let cleaned = String(text);
      
      // AGGRESSIVE: Remove PROOF COMPLETION ASSESSMENT and everything after it
      cleaned = cleaned.split(/PROOF\s+COMPLETION\s+ASSESSMENT/i)[0];
      
      // Remove common emojis and icons
      cleaned = cleaned
        .replace(/✅|✔️|✓|☑️|✔/g, '')
        .replace(/⚠️|⚡️|❌|✗|❗|⚡/g, '')
        .replace(/[\u{1F300}-\u{1F9FF}]/gu, '') // Remove emoji unicode range
        .replace(/\[PROOF COMPLETION ASSESSMENT:\]/g, '')
        .replace(/\*\*PROOF COMPLETION ASSESSMENT:\*\*/g, '')
        .replace(/\*\*PROOF IS INCOMPLETE.*?(?=\n|$)/gis, '')
        .replace(/\n\*\*/g, '\n') // Remove extra ** 
        .replace(/^(Improvement|Hint|Status|Input Summary|What's Wrong):\s*\n?/gim, '') // Remove section headers
        .replace(/NO FURTHER STEPS|no further steps|proof is complete|PROOF IS COMPLETE/gi, '')
        .trim();
      
      return cleaned;
    }

    // Helper function to truncate and make feedback concise
    function makeConcisFeedback(text, maxSentences = 2) {
      if (!text) return '';
      const cleaned = cleanFeedbackText(text);
      const sentences = cleaned.split(/(?<=[.!?])\s+/);
      return sentences.slice(0, maxSentences).join(' ').trim();
    }

    function updateVerificationPanel(result = null) {
      const total = Array.isArray(state.steps) ? state.steps.length : 0;
      const verified = Array.isArray(state.steps)
        ? state.steps.filter((step) => step.status === 'correct').length
        : 0;

      if (verifyCountEl) verifyCountEl.textContent = String(verified);
      if (verifyTotalEl) verifyTotalEl.textContent = String(total);

      if (!result) {
        if (verifyHeaderEl) {
          verifyHeaderEl.classList.remove('verify-header-correct', 'verify-header-warn');
          verifyHeaderEl.classList.add('verify-header-neutral');
        }
        if (verifyHeaderIconEl) verifyHeaderIconEl.textContent = '•';
        if (verifyHeaderLabelEl) verifyHeaderLabelEl.textContent = 'Awaiting verification';
        if (verifyCurrentStepEl) verifyCurrentStepEl.textContent = 'No step verified yet.';
        if (verifyStatusValueEl) verifyStatusValueEl.textContent = 'No verification submitted.';
        if (verifyInputSummaryEl) verifyInputSummaryEl.textContent = 'The system will summarize the current step after verification.';
        if (verifyJustificationEl) verifyJustificationEl.textContent = 'Provide a step and select Verify to receive structured analysis.';
        if (verifyMissingWrapEl) verifyMissingWrapEl.classList.add('verify-hidden');
        if (verifyImprovementWrapEl) verifyImprovementWrapEl.classList.add('verify-hidden');
        if (verifyHintWrapEl) verifyHintWrapEl.classList.add('verify-hidden');
        const verifyNextStepWrapEl = document.getElementById('verify-next-step-wrap');
        if (verifyNextStepWrapEl) verifyNextStepWrapEl.classList.add('verify-hidden');
        if (verifyNextGuideEl) verifyNextGuideEl.textContent = 'Verify a step to receive the next instructional move.';
        if (nextStepGuideBtn) nextStepGuideBtn.disabled = true;
        return;
      }

      const isCorrect = result.cls === 'correct';
      const statusClass = isCorrect ? 'verify-status-good' : 'verify-status-bad';
      const statusText = isCorrect ? 'Correct' : 'Needs Revision';
      const nextStepHint = buildRandomNextStepHint(result, state.theorem || theoremInput.value || '');

      if (verifyHeaderEl) {
        verifyHeaderEl.classList.remove('verify-header-neutral', 'verify-header-correct', 'verify-header-warn');
        verifyHeaderEl.classList.add(isCorrect ? 'verify-header-correct' : 'verify-header-warn');
      }
      if (verifyHeaderIconEl) verifyHeaderIconEl.textContent = isCorrect ? '✓' : '!';
      if (verifyHeaderLabelEl) verifyHeaderLabelEl.textContent = statusText;
      if (verifyCurrentStepEl) {
        const stepText = result?.verifiedStepNumber ? `Verifying result for Step ${result.verifiedStepNumber}.` : 'Verification result updated.';
        verifyCurrentStepEl.textContent = stepText;
      }

      if (verifyStatusValueEl) {
        verifyStatusValueEl.innerHTML = `<span class="${statusClass}">${statusText}</span>`;
      }
      if (verifyInputSummaryEl) verifyInputSummaryEl.textContent = makeConcisFeedback(result.inputSummary || '', 2);
      if (verifyJustificationEl) verifyJustificationEl.textContent = makeConcisFeedback(result.justification || result.msg || '', 3);

      const hasMissing = !isCorrect && !!String(result.missing || '').trim();
      if (verifyMissingWrapEl) verifyMissingWrapEl.classList.toggle('verify-hidden', !hasMissing);
      if (verifyMissingEl) verifyMissingEl.textContent = makeConcisFeedback(result.missing || '', 2);

      const hasImprovement = !!String(result.improvement || '').trim();
      if (verifyImprovementWrapEl) verifyImprovementWrapEl.classList.toggle('verify-hidden', !hasImprovement);
      
      // Check if proof is complete BEFORE cleaning the text
      const rawImprovementText = String(result.improvement || '').trim();
      const rawProofCompletion = String(result.proofCompletion || '').trim();
      
      // More flexible proof completion detection - remove extra whitespace for matching
      const normalizedImprovementText = rawImprovementText.replace(/\s+/g, ' ');
      const normalizedProofCompletion = rawProofCompletion.replace(/\s+/g, ' ');
      
      // Multiple ways to detect proof completion 
      let isProofComplete = result.isProofComplete === true || 
                            /PROOF[\s]*IS[\s]*COMPLETE|no[\s]*further[\s]*steps|proof[\s]*is[\s]*complete/i.test(normalizedImprovementText) ||
                            /PROOF[\s]*IS[\s]*COMPLETE|proof[\s]*complete/i.test(normalizedProofCompletion);
      
      console.log('=== PROOF COMPLETION CHECK ===', {
        isProofComplete,
        rawText: rawImprovementText.substring(0, 80),
        normalized: normalizedImprovementText.substring(0, 80),
        matches: {
          'PROOF IS COMPLETE': /PROOF[\s]*IS[\s]*COMPLETE/i.test(normalizedImprovementText),
          'no further steps': /no[\s]*further[\s]*steps/i.test(normalizedImprovementText),
          'proof is complete': /proof[\s]*is[\s]*complete/i.test(normalizedImprovementText)
        }
      });
      
      if (verifyImprovementEl && hasImprovement) {
        // Clean up improvement text to be more concise
        let improvedText = rawImprovementText;
        
        // Use helper function to clean and consolidate
        improvedText = makeConcisFeedback(improvedText, 2);
        
        // Trim and show
        verifyImprovementEl.textContent = improvedText.trim();
      }

      const hasHint = !isCorrect && !!String(result.hint || '').trim();
      if (verifyHintWrapEl) verifyHintWrapEl.classList.toggle('verify-hidden', !hasHint);
      if (verifyHintEl) verifyHintEl.textContent = makeConcisFeedback(result.hint || '', 2);

      // CRITICAL: Hide Next Step Guide section if proof is complete
      const verifyNextStepWrapEl = document.getElementById('verify-next-step-wrap');
      console.log('Next Step Wrap Element:', verifyNextStepWrapEl, 'isProofComplete:', isProofComplete);
      
      if (verifyNextStepWrapEl) {
        // Force clear any previous classes first
        verifyNextStepWrapEl.classList.remove('verify-hidden');
        
        // Then set based on completion status
        if (isProofComplete) {
          verifyNextStepWrapEl.classList.add('verify-hidden');
          console.log('✅ HIDING Next Step Guidance section - proof is complete!');
        } else {
          console.log('▶️ SHOWING Next Step Guidance section - proof incomplete');
          if (verifyNextGuideEl) {
            verifyNextGuideEl.textContent = nextStepHint;
          }
        }
      }
      
      // Disable Next Step button if proof is complete
      if (nextStepGuideBtn) {
        if (isProofComplete) {
          nextStepGuideBtn.disabled = true;
          console.log('✅ DISABLED Next Step button - proof is complete!');
        } else {
          nextStepGuideBtn.disabled = !isCorrect;
          console.log('▶️ Next Step button state:', { isCorrect, disabled: nextStepGuideBtn.disabled });
        }
      }
    }

    async function performLogout() {
      try {
        await fetch('../../backend/api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'logout' })
        });
      } catch (error) {
      }
      // Clear all client-side session data
      localStorage.removeItem('axio.workspace.state');
      localStorage.removeItem('reana_profile');
      localStorage.removeItem('reana_preferences');
      localStorage.removeItem('reana_proofs');
      localStorage.removeItem('reana_submissions');
      
      // Remove session tracking variables but keep logout flag
      sessionStorage.removeItem('current_user_id');
      sessionStorage.removeItem('new_user');
      sessionStorage.removeItem('latex_skill_done');
      sessionStorage.removeItem('tutorial_completed');
      
      // Flag landing.html to show login instead of redirecting (MUST be last)
      sessionStorage.setItem('just_logged_out', 'true');
      
      window.location.href = 'welcome.html';
    }

    function setVerifyPopupVisibility(isOpen) {
      if (!verifyPopup) return;
      verifyPopup.classList.toggle('hidden', !isOpen);
      if (openVerifyPopupBtn) openVerifyPopupBtn.style.display = isOpen ? 'none' : 'inline-flex';
      if (hideVerifyPopupBtn) hideVerifyPopupBtn.style.display = isOpen ? 'inline-flex' : 'none';
    }

    function renderVerifyPopupList() {
      if (!verifyPopupList) return;
      const records = Array.isArray(state.verificationHistory) ? state.verificationHistory : [];
      if (!records.length) {
        verifyPopupList.innerHTML = '<div class="list-item">No step verification records yet.</div>';
        return;
      }

      verifyPopupList.innerHTML = records.map((record) => {
        const badge = record.cls === 'correct' ? 'Correct' : 'Needs Revision';
        return `
          <div class="list-item" style="display:block;align-items:normal;">
            <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;">
              <strong>${badge} · Step ${record.stepNumber}</strong>
              <span style="font-size:12px;color:var(--muted);">${escapeHtml(record.time)}</span>
            </div>
            <div style="margin-top:6px;"><strong>Status:</strong> ${escapeHtml(record.statusLabel || '')} — ${escapeHtml(record.msg)}</div>
            <div style="margin-top:6px;"><strong>Input Summary:</strong> ${escapeHtml(record.inputSummary || '')}</div>
            <div style="margin-top:6px;"><strong>Justification:</strong> ${escapeHtml(record.justification || '')}</div>
            <div style="margin-top:6px;"><strong>What\'s Wrong / Missing:</strong> ${escapeHtml(record.missing || '')}</div>
            <div style="margin-top:6px;"><strong>Improvement:</strong> ${escapeHtml(record.improvement || '')}</div>
            <div style="margin-top:6px;"><strong>Hint:</strong> ${escapeHtml(record.hint || '')}</div>
            <div style="margin-top:6px;"><strong>Next Step Suggestion:</strong> ${escapeHtml(record.nextStepSuggestion || '')}</div>
            <div style="margin-top:6px;color:var(--muted);font-size:12px;">${escapeHtml(record.stepText || '')}</div>
          </div>
        `;
      }).join('');
    }

    function addVerificationRecord(stepId, stepIndex, stepText, result) {
      if (!Array.isArray(state.verificationHistory)) state.verificationHistory = [];
      state.verificationHistory.unshift({
        stepId,
        stepNumber: stepIndex + 1,
        stepText: String(stepText || '').trim().slice(0, 220),
        cls: result.cls,
        statusLabel: result.statusLabel || '',
        msg: result.msg,
        inputSummary: result.inputSummary || '',
        justification: result.justification || '',
        missing: result.missing || '',
        improvement: result.improvement || '',
        hint: result.hint || '',
        nextStepSuggestion: result.nextStepSuggestion || '',
        time: new Date().toLocaleTimeString()
      });
      state.verificationHistory = state.verificationHistory.slice(0, 50);
      saveState();
      renderVerifyPopupList();
    }

    function setSelectedStep(stepId) {
      state.selectedStepId = stepId;
      saveState();
      stepsWrap.querySelectorAll('.step-card').forEach((node) => {
        node.classList.toggle('selected', node.dataset.stepId === stepId);
      });
      removeBtn.disabled = !state.selectedStepId || state.steps.length <= 1;
    }

    function createStepCard(step, index) {
      const card = document.createElement('div');
      card.className = 'step-card' + (state.selectedStepId === step.id ? ' selected' : '');
      card.dataset.stepId = step.id;
      const tabs = KEYBOARD_CATEGORIES.map((category, categoryIndex) =>
        `<button class="eq-tab ${categoryIndex === 0 ? 'active' : ''}" data-key-tab="${step.id}" data-tab-index="${categoryIndex}">${escapeHtml(category.name)}</button>`
      ).join('');
      card.innerHTML = `
        <div class="step-head">
          <span class="step-num">Step ${index + 1}</span>
          <div class="step-tools">
            <button class="small-btn" data-open-keyboard="${step.id}">⌨ Equations ▾</button>
            <button class="small-btn" data-verify-step="${step.id}">Verify</button>
            <div class="equation-popover" data-key-popover="${step.id}">
              <div class="eq-tabs">${tabs}</div>
              <div class="eq-grid" data-key-grid="${step.id}"></div>
              <div class="eq-footer">
                <span data-key-label="${step.id}">Click to insert • Fractions</span>
                <button class="eq-close" data-close-keyboard="${step.id}">Close</button>
              </div>
            </div>
          </div>
        </div>
        <textarea class="step-text" data-step-text="${step.id}" rows="4" placeholder="Write proof step..."></textarea>
        <div class="preview" id="preview-${step.id}"></div>
        <div class="preview preview-natural" id="preview-natural-${step.id}"></div>
        <div class="step-feedback ${step.status === 'correct' ? '' : (step.status || '')}" id="feedback-${step.id}">${step.status === 'correct' ? '' : (step.feedback || '')}</div>
      `;

      const ta = card.querySelector('[data-step-text]');
      ta.value = step.text || '';
      ta.style.fontSize = state.stepFontSize + 'px';
      ta.style.transition = 'font-size .2s ease';

      ta.addEventListener('focus', () => {
        activeTextarea = ta;
        setSelectedStep(step.id);
      });
      ta.addEventListener('input', () => {
        step.text = ta.value;
        step.status = '';
        step.feedback = '';
        markProofDirty();
        renderMath(card.querySelector('#preview-' + step.id), step.text || 'Live LaTeX preview...');
        card.querySelector('#preview-natural-' + step.id).textContent = latexToNatural(step.text);
        saveState();
        scheduleWorkspaceSync();
      });

      const popover = card.querySelector('[data-key-popover]');
      const keyLabel = card.querySelector('[data-key-label]');
      const keyGrid = card.querySelector('[data-key-grid]');

      function renderKeyboardGrid(categoryIndex) {
        const category = KEYBOARD_CATEGORIES[categoryIndex] || KEYBOARD_CATEGORIES[0];
        keyGrid.innerHTML = category.symbols.map((symbol, symbolIndex) =>
          `<button class="eq-item" data-key-symbol="${step.id}" data-category-index="${categoryIndex}" data-symbol-index="${symbolIndex}" title="${escapeHtml(symbol.value)}">${escapeHtml(symbol.label)}</button>`
        ).join('');
        keyLabel.textContent = `Click to insert • ${category.name}`;
      }

      renderKeyboardGrid(0);

      card.querySelector('[data-open-keyboard]').addEventListener('click', (event) => {
        event.stopPropagation();
        activeTextarea = ta;
        setSelectedStep(step.id);
        document.querySelectorAll('.equation-popover.open').forEach(panel => {
          if (panel !== popover) panel.classList.remove('open');
        });
        popover.classList.toggle('open');
      });

      popover.addEventListener('click', (event) => {
        const tab = event.target.closest('[data-key-tab]');
        const symbolButton = event.target.closest('[data-key-symbol]');
        const closeButton = event.target.closest('[data-close-keyboard]');

        if (tab) {
          const tabIndex = Number(tab.getAttribute('data-tab-index') || 0);
          popover.querySelectorAll('[data-key-tab]').forEach(btn => btn.classList.remove('active'));
          tab.classList.add('active');
          renderKeyboardGrid(tabIndex);
          return;
        }

        if (symbolButton) {
          const categoryIndex = Number(symbolButton.getAttribute('data-category-index') || 0);
          const symbolIndex = Number(symbolButton.getAttribute('data-symbol-index') || 0);
          const value = KEYBOARD_CATEGORIES[categoryIndex]?.symbols?.[symbolIndex]?.value || '';
          insertAtCursor(activeTextarea, value);
          return;
        }

        if (closeButton) {
          popover.classList.remove('open');
        }
      });

      card.querySelector('[data-verify-step]').addEventListener('click', async () => {
        popover.classList.remove('open');
        const verifyBtn = card.querySelector('[data-verify-step]');
        if (verifyBtn) {
          verifyBtn.disabled = true;
          verifyBtn.textContent = 'Verifying...';
        }

        const localResult = verifyLogic(ta.value, state.theorem || theoremInput.value || '');
        localResult.verifiedStepNumber = index + 1;
        if (verifyCurrentStepEl) verifyCurrentStepEl.textContent = `Verifying Step ${index + 1}...`;
        const result = normalizeVerificationConsistency(await verifyStepWithAi(ta.value, index, localResult));
        result.nextStepSuggestion = buildRandomNextStepHint(result, state.theorem || theoremInput.value || '', ta.value);
        lastVerificationResult = result;

        renderVerifyPanel(result);
        setFeedback(step.id, result);
        addVerificationRecord(step.id, index, ta.value, result);
        const fb = card.querySelector('#feedback-' + step.id);
        if (result.cls === 'correct') {
          fb.className = 'step-feedback';
          fb.textContent = '';
        } else {
          fb.className = 'step-feedback ' + result.cls;
          fb.textContent = `${result.statusLabel}: ${result.msg}`;
        }

        if (verifyBtn) {
          verifyBtn.disabled = false;
          verifyBtn.textContent = 'Verify';
        }
        scheduleWorkspaceSync(120);
      });

      renderMath(card.querySelector('#preview-' + step.id), step.text || 'Live LaTeX preview...');
      card.querySelector('#preview-natural-' + step.id).textContent = latexToNatural(step.text);
      updateStepPreviewVisibility(card, step.id);
      return card;
    }

    function renderSteps() {
      stepsWrap.innerHTML = '';
      state.steps.forEach((s, i) => stepsWrap.appendChild(createStepCard(s, i)));
      removeBtn.disabled = !state.selectedStepId || state.steps.length <= 1;
      updateVerificationPanel();
    }

    theoremInput.addEventListener('focus', () => activeTextarea = theoremInput);
    theoremInput.addEventListener('input', () => {
      state.theorem = theoremInput.value;
      markProofDirty();
      if (!state.proofStartedAt && theoremInput.value.trim()) {
        state.proofStartedAt = new Date().toISOString();
      }
      saveState();
      rerenderTheoremPreview();
      scheduleWorkspaceSync();
    });

    btnAPlus.addEventListener('click', () => {
      state.theoremFontSize = Math.min(30, state.theoremFontSize + 1);
      state.stepFontSize = Math.min(28, state.stepFontSize + 1);
      theoremInput.style.fontSize = state.theoremFontSize + 'px';
      saveState();
      renderSteps();
    });
    btnAMinus.addEventListener('click', () => {
      state.theoremFontSize = Math.max(12, state.theoremFontSize - 1);
      state.stepFontSize = Math.max(12, state.stepFontSize - 1);
      theoremInput.style.fontSize = state.theoremFontSize + 'px';
      saveState();
      renderSteps();
    });

    // Theorem keyboard handler
    if (btnTheoremKeyboard) {
      btnTheoremKeyboard.addEventListener('click', (event) => {
        event.stopPropagation();
        activeTextarea = theoremInput;
        const keyboardModal = document.getElementById('keyboard-modal');
        const symbolsWrap = document.getElementById('symbols-wrap');
        
        if (!keyboardModal || !symbolsWrap) return;
        
        // Close any open step popovers
        document.querySelectorAll('.equation-popover.open').forEach(panel => {
          panel.classList.remove('open');
        });
        
        // Create category tabs
        const tabs = KEYBOARD_CATEGORIES.map((category, idx) =>
          `<button class="eq-tab ${idx === 0 ? 'active' : ''}" data-theorem-category="${idx}">${escapeHtml(category.name)}</button>`
        ).join('');
        
        const modalCard = keyboardModal.querySelector('.modal-card');
        let tabsContainer = modalCard.querySelector('.eq-tabs-theorem');
        
        if (!tabsContainer) {
          tabsContainer = document.createElement('div');
          tabsContainer.className = 'eq-tabs-theorem';
          const modalHead = modalCard.querySelector('div[style*="display:flex"]');
          modalHead.parentNode.insertBefore(tabsContainer, symbolsWrap);
        }
        
        tabsContainer.innerHTML = tabs;
        
        // Render symbols for first category
        let currentCategoryIndex = 0;
        
        function renderSymbols(categoryIndex) {
          const category = KEYBOARD_CATEGORIES[categoryIndex] || KEYBOARD_CATEGORIES[0];
          symbolsWrap.style.display = 'grid';
          symbolsWrap.style.gridTemplateColumns = 'repeat(5, 1fr)';
          symbolsWrap.style.gap = '8px';
          symbolsWrap.innerHTML = category.symbols.map((symbol, symbolIndex) =>
            `<button class="eq-item" data-theorem-symbol="${symbolIndex}" data-category="${categoryIndex}" title="${escapeHtml(symbol.value)}">${escapeHtml(symbol.label)}</button>`
          ).join('');
        }
        
        renderSymbols(0);
        
        // Category tab click handler
        tabsContainer.addEventListener('click', (event) => {
          const tab = event.target.closest('[data-theorem-category]');
          if (tab) {
            const categoryIndex = Number(tab.getAttribute('data-theorem-category') || 0);
            currentCategoryIndex = categoryIndex;
            tabsContainer.querySelectorAll('[data-theorem-category]').forEach(btn => btn.classList.remove('active'));
            tab.classList.add('active');
            renderSymbols(categoryIndex);
          }
        });
        
        // Show modal
        keyboardModal.classList.remove('hidden');
      });
    }

    // Symbol click handler for theorem keyboard
    const symbolsWrap = document.getElementById('symbols-wrap');
    if (symbolsWrap) {
      document.addEventListener('click', (event) => {
        const symbolBtn = event.target.closest('[data-theorem-symbol]');
        if (symbolBtn) {
          const categoryIndex = Number(symbolBtn.getAttribute('data-category') || 0);
          const symbolIndex = Number(symbolBtn.getAttribute('data-theorem-symbol') || 0);
          const value = KEYBOARD_CATEGORIES[categoryIndex]?.symbols?.[symbolIndex]?.value || '';
          insertAtCursor(theoremInput, ' ' + value);
          theoremInput.focus();
          markProofDirty();
          rerenderTheoremPreview();
          saveState();
        }
      });
    }

    // Close keyboard modal handler
    const closeKeyboardBtn = document.getElementById('close-keyboard');
    if (closeKeyboardBtn) {
      closeKeyboardBtn.addEventListener('click', () => {
        const keyboardModal = document.getElementById('keyboard-modal');
        if (keyboardModal) {
          keyboardModal.classList.add('hidden');
        }
      });
    }

    function addNewStep() {
      markProofDirty();
      state.steps.push({ id: uid(), text: '', feedback: '', status: '' });
      state.selectedStepId = state.steps[state.steps.length - 1].id;
      saveState();
      renderSteps();
      const selectedCard = stepsWrap.querySelector(`.step-card[data-step-id="${state.selectedStepId}"]`);
      const selectedTextarea = selectedCard?.querySelector('[data-step-text]');
      selectedTextarea?.focus();
      scheduleWorkspaceSync();
    }

    addBtn.addEventListener('click', addNewStep);
    nextStepGuideBtn?.addEventListener('click', () => {
      if (nextStepGuideBtn.disabled) return;
      if (verifyNextGuideEl) {
        verifyNextGuideEl.textContent = buildRandomNextStepHint(
          lastVerificationResult || { cls: 'correct' },
          state.theorem || theoremInput.value || ''
        );
      }
      addNewStep();
      nextStepGuideBtn.disabled = true;
    });

    togglePreviewModeBtn?.addEventListener('click', () => {
      state.previewMode = state.previewMode === 'natural' ? 'latex' : 'natural';
      saveState();
      rerenderTheoremPreview();
      renderSteps();
      updatePreviewModeButtonLabel();
    });

    removeBtn.addEventListener('click', () => {
      if (!state.selectedStepId) return;
      markProofDirty();
      state.steps = state.steps.filter(s => s.id !== state.selectedStepId);
      state.selectedStepId = state.steps[0]?.id || null;
      if (!state.steps.length) state.steps = [{ id: uid(), text: '', feedback: '', status: '' }];
      saveState();
      renderSteps();
      scheduleWorkspaceSync();
    });

    viewProofsBtn.addEventListener('click', () => window.location.href = 'submissions.html');

    completeProofBtn?.addEventListener('click', completeProof);
    savePdfBtn?.addEventListener('click', saveProofAsPdf);
    openVerifyPopupBtn?.addEventListener('click', () => setVerifyPopupVisibility(true));
    hideVerifyPopupBtn?.addEventListener('click', () => setVerifyPopupVisibility(false));
    closeVerifyPopupBtn?.addEventListener('click', () => setVerifyPopupVisibility(false));

    newProofBtn.addEventListener('click', async () => {
      const hasContent = (state.theorem || '').trim() || state.steps.some(s => (s.text || '').trim());
      if (hasContent) {
        if (!state.proofCompleted) {
          newProofBtn.disabled = true;
          const draftSaved = await saveDraftProof();
          newProofBtn.disabled = false;
          if (!draftSaved) return;
        }

        const archived = {
          id: uid(),
          theorem: state.theorem,
          steps: state.steps,
          updatedAt: new Date().toISOString(),
          accuracy: calcAccuracy(state.steps)
        };
        proofs.unshift(archived);
        saveProofs();
      }
      state = JSON.parse(JSON.stringify(defaultState));
      saveState();
      window.location.reload();
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.step-tools')) {
        document.querySelectorAll('.equation-popover.open').forEach(panel => panel.classList.remove('open'));
      }
    });

    document.getElementById('avatar-profile')?.addEventListener('click', () => {
      window.location.href = 'settings.html';
    });
    document.getElementById('avatar-logout')?.addEventListener('click', performLogout);
    document.getElementById('sidebar-logout')?.addEventListener('click', (event) => {
      event.preventDefault();
      performLogout();
    });

    rerenderTheoremPreview();
    updateSavePdfButtonState();
    updatePreviewModeButtonLabel();
    renderSteps();
    renderVerifyPopupList();
    setVerifyPopupVisibility(false);
    scheduleWorkspaceSync(350);
  }

  function calcAccuracy(steps) {
    if (!steps || !steps.length) return 0;
    const good = steps.filter(s => s.status === 'correct').length;
    return Math.round((good / steps.length) * 100);
  }

  function insertAtCursor(textarea, content) {
    if (!textarea) return;
    const start = textarea.selectionStart || 0;
    const end = textarea.selectionEnd || 0;
    textarea.value = textarea.value.slice(0, start) + content + textarea.value.slice(end);
    textarea.focus();
    const pos = start + content.length;
    textarea.setSelectionRange(pos, pos);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function setupProofsPage() {
    const search = document.getElementById('proof-search');
    const list = document.getElementById('proof-list');

    function render() {
      const q = (search.value || '').toLowerCase();
      const filtered = proofs.filter(p => (p.theorem || '').toLowerCase().includes(q));
      if (!filtered.length) {
        list.innerHTML = '<div class="list-item">No saved proofs yet.</div>';
        return;
      }
      list.innerHTML = filtered.map(p => `
        <div class="list-item">
          <div>
            <strong>${escapeHtml((p.theorem || 'Untitled').slice(0, 90))}</strong>
            <div style="font-size:12px;color:var(--muted)">${new Date(p.updatedAt).toLocaleString()} · Accuracy ${p.accuracy || 0}%</div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn" data-open="${p.id}">Open</button>
            <button class="btn" data-delete="${p.id}">Delete</button>
          </div>
        </div>
      `).join('');

      list.querySelectorAll('[data-open]').forEach(btn => btn.addEventListener('click', () => {
        const proof = proofs.find(x => x.id === btn.getAttribute('data-open'));
        if (!proof) return;
        state = {
          theorem: proof.theorem || '',
          theoremFontSize: 16,
          stepFontSize: 15,
          steps: proof.steps?.length ? proof.steps : [{ id: uid(), text: '', feedback: '', status: '' }],
          selectedStepId: proof.steps?.[0]?.id || null
        };
        saveState();
        window.location.href = 'dashboard.html';
      }));

      list.querySelectorAll('[data-delete]').forEach(btn => btn.addEventListener('click', () => {
        proofs = proofs.filter(x => x.id !== btn.getAttribute('data-delete'));
        saveProofs();
        render();
      }));
    }

    search.addEventListener('input', render);
    render();
  }

  function setupScoresPage() {
    const accEl = document.getElementById('metric-accuracy');
    const doneEl = document.getElementById('metric-done');
    const recentEl = document.getElementById('metric-recent');
    const chart = document.getElementById('activity-chart');

    const total = proofs.length;
    const avgAccuracy = total ? Math.round(proofs.reduce((a, p) => a + (p.accuracy || 0), 0) / total) : 0;
    const recent = proofs.slice(0, 7);

    accEl.textContent = avgAccuracy + '%';
    doneEl.textContent = String(total);
    recentEl.textContent = String(recent.length);

    chart.innerHTML = (recent.length ? recent : [{ accuracy: 0 }]).map(p => `<div class="bar" style="height:${Math.max(10, p.accuracy || 0)}%"></div>`).join('');
  }

  function setupSettingsPage() {
    const themeToggle = document.getElementById('set-theme');
    const notifToggle = document.getElementById('set-notif');
    const fontRange = document.getElementById('set-font');
    const fontValue = document.getElementById('set-font-value');

    themeToggle.checked = settings.theme === 'dark';
    notifToggle.checked = !!settings.notifications;
    fontRange.value = settings.fontScale || 100;
    fontValue.textContent = fontRange.value + '%';

    themeToggle.addEventListener('change', () => {
      settings.theme = themeToggle.checked ? 'dark' : 'light';
      saveSettings();
    });

    notifToggle.addEventListener('change', () => {
      settings.notifications = notifToggle.checked;
      saveSettings();
    });

    fontRange.addEventListener('input', () => {
      settings.fontScale = Number(fontRange.value);
      fontValue.textContent = fontRange.value + '%';
      document.documentElement.style.fontSize = (settings.fontScale / 100) + 'rem';
      saveSettings();
    });
  }

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  document.addEventListener('DOMContentLoaded', async () => {
    const page = document.body.dataset.page;
    // Note: Auth already verified by landing.html - no need to re-check here
    
    initShared();
    if (page === 'workspace') setupWorkspace();
    if (page === 'proofs') setupProofsPage();
    if (page === 'scores') setupScoresPage();
    if (page === 'settings') setupSettingsPage();
  });
})();
