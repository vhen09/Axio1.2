// Math Symbol Library Component for Dashboard
// Provides a quick-reference panel for LaTeX symbols

class SymbolLibrary {
    constructor(containerId = 'symbol-library') {
        this.containerId = containerId;
        this.symbols = this.getSymbols();
        this.currentCategory = 'common';
        this.init();
    }

    getSymbols() {
        return {
            common: [
                { name: '+', latex: '+', category: 'Addition' },
                { name: '−', latex: '-', category: 'Subtraction' },
                { name: '×', latex: '\\times', category: 'Multiplication' },
                { name: '÷', latex: '\\frac{a}{b}', category: 'Division' },
                { name: '√', latex: '\\sqrt{x}', category: 'Root' },
                { name: '^', latex: 'x^n', category: 'Power' },
                { name: '=', latex: '=', category: 'Equals' },
                { name: '≠', latex: '\\neq', category: 'Not Equal' },
                { name: '<', latex: '<', category: 'Less than' },
                { name: '>', latex: '>', category: 'Greater than' },
                { name: '≤', latex: '\\leq', category: 'Less or equal' },
                { name: '≥', latex: '\\geq', category: 'Greater or equal' }
            ],
            calculus: [
                { name: '∫', latex: '\\int', category: 'Integral' },
                { name: '∂', latex: '\\partial', category: 'Partial derivative' },
                { name: '∑', latex: '\\sum', category: 'Sum' },
                { name: '∏', latex: '\\prod', category: 'Product' },
                { name: 'lim', latex: '\\lim', category: 'Limit' },
                { name: '∞', latex: '\\infty', category: 'Infinity' },
                { name: '∇', latex: '\\nabla', category: 'Gradient' }
            ],
            logic: [
                { name: '∀', latex: '\\forall', category: 'For all' },
                { name: '∃', latex: '\\exists', category: 'There exists' },
                { name: '∈', latex: '\\in', category: 'Element of' },
                { name: '∉', latex: '\\notin', category: 'Not element' },
                { name: '⊂', latex: '\\subset', category: 'Subset' },
                { name: '⊆', latex: '\\subseteq', category: 'Subset equal' },
                { name: '∪', latex: '\\cup', category: 'Union' },
                { name: '∩', latex: '\\cap', category: 'Intersection' }
            ],
            greek: [
                { name: 'α', latex: '\\alpha', category: 'Alpha' },
                { name: 'β', latex: '\\beta', category: 'Beta' },
                { name: 'γ', latex: '\\gamma', category: 'Gamma' },
                { name: 'δ', latex: '\\delta', category: 'Delta' },
                { name: 'θ', latex: '\\theta', category: 'Theta' },
                { name: 'λ', latex: '\\lambda', category: 'Lambda' },
                { name: 'π', latex: '\\pi', category: 'Pi' },
                { name: 'σ', latex: '\\sigma', category: 'Sigma' }
            ]
        };
    }

    init() {
        this.render();
    }

    render() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        let html = `
            <div class="symbol-library-panel">
                <div class="symbol-library-header">
                    <h3>📐 Math Symbols</h3>
                    <button class="symbol-library-toggle" title="Collapse">−</button>
                </div>
                <div class="symbol-library-content">
                    <div class="symbol-library-tabs">
                        <button class="symbol-tab active" data-category="common">Common</button>
                        <button class="symbol-tab" data-category="calculus">Calculus</button>
                        <button class="symbol-tab" data-category="logic">Logic</button>
                        <button class="symbol-tab" data-category="greek">Greek</button>
                    </div>
                    <div class="symbol-library-grid">`;

        Object.entries(this.symbols).forEach(([category, symbols]) => {
            html += `<div class="symbol-category-group" data-category="${category}" style="display: ${category === 'common' ? 'grid' : 'none'};">`;
            
            symbols.forEach(sym => {
                html += `
                    <button class="symbol-quick-btn" data-latex="${sym.latex}" title="${sym.category}">
                        <span class="symbol-display">${sym.name}</span>
                    </button>
                `;
            });

            html += `</div>`;
        });

        html += `
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = html;
        this.attachEventListeners();
    }

    attachEventListeners() {
        const container = document.getElementById(this.containerId);

        // Tab switching
        container.querySelectorAll('.symbol-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                container.querySelectorAll('.symbol-tab').forEach(t => t.classList.remove('active'));
                container.querySelectorAll('.symbol-category-group').forEach(g => g.style.display = 'none');
                
                e.target.classList.add('active');
                const category = e.target.dataset.category;
                const group = container.querySelector(`.symbol-category-group[data-category="${category}"]`);
                if (group) group.style.display = 'grid';
            });
        });

        // Symbol copy buttons
        container.querySelectorAll('.symbol-quick-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const latex = btn.dataset.latex;
                navigator.clipboard.writeText(latex);
                
                // Visual feedback
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="symbol-display">✓</span>';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 1000);

                // Also try to paste into active textarea/input
                this.pasteToEditor(latex);
            });
        });

        // Collapse toggle
        const toggle = container.querySelector('.symbol-library-toggle');
        const content = container.querySelector('.symbol-library-content');
        if (toggle && content) {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const isHidden = content.style.display === 'none';
                content.style.display = isHidden ? 'block' : 'none';
                toggle.textContent = isHidden ? '−' : '+';
            });
        }
    }

    pasteToEditor(latex) {
        // Try to find active editor and insert symbol
        const activeElement = document.activeElement;
        if (activeElement && (activeElement.tagName === 'TEXTAREA' || (activeElement.tagName === 'INPUT' && activeElement.type === 'text'))) {
            const start = activeElement.selectionStart;
            const end = activeElement.selectionEnd;
            const text = activeElement.value;
            
            activeElement.value = text.substring(0, start) + latex + text.substring(end);
            activeElement.selectionStart = activeElement.selectionEnd = start + latex.length;
            activeElement.focus();
            
            // Trigger input event
            activeElement.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
}

// CSS for Symbol Library (to be added to dashboard CSS)
const symbolLibraryCSS = `
.symbol-library-panel {
    background: white;
    border: 1px solid rgba(30, 58, 138, 0.2);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.08);
}

.symbol-library-header {
    background: linear-gradient(90deg, #0f766e 0%, #115e59 100%);
    color: white;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.symbol-library-header h3 {
    margin: 0;
    font-size: 1.1em;
}

.symbol-library-toggle {
    background: rgba(255,255,255,0.2);
    border: 0;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.symbol-library-toggle:hover {
    background: rgba(255,255,255,0.35);
}

.symbol-library-content {
    padding: 16px;
}

.symbol-library-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    border-bottom: 2px solid rgba(30, 58, 138, 0.1);
    padding-bottom: 8px;
}

.symbol-tab {
    padding: 6px 12px;
    border: 0;
    background: transparent;
    color: #4b5563;
    cursor: pointer;
    font-weight: 600;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.symbol-tab:hover {
    color: #1e3a8a;
}

.symbol-tab.active {
    color: #0f766e;
    border-bottom-color: #0f766e;
}

.symbol-library-grid {
    position: relative;
}

.symbol-category-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(36px, 1fr));
    gap: 8px;
}

.symbol-quick-btn {
    aspect-ratio: 1;
    border: 1px solid rgba(30, 58, 138, 0.16);
    background: #f9fafb;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1.2em;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    color: #1e3a8a;
}

.symbol-quick-btn:hover {
    background: #0f766e;
    color: white;
    border-color: #0f766e;
    transform: scale(1.1);
}

.symbol-quick-btn:active {
    transform: scale(0.95);
}

/* Dark Mode */
.theme-dark .symbol-library-panel {
    background: #0b1220;
    border-color: rgba(148, 163, 184, 0.35);
}

.theme-dark .symbol-quick-btn {
    background: #1f2937;
    border-color: rgba(148, 163, 184, 0.35);
    color: #7dd3fc;
}

.theme-dark .symbol-quick-btn:hover {
    background: #0f766e;
    color: white;
}

.theme-dark .symbol-tab {
    color: #cbd5e1;
}

.theme-dark .symbol-tab.active {
    color: #14b8a6;
    border-bottom-color: #14b8a6;
}

.theme-dark .symbol-library-tabs {
    border-bottom-color: rgba(148, 163, 184, 0.2);
}
`;

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SymbolLibrary, symbolLibraryCSS };
}
