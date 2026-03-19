// LaTeX Tutorial Data and Logic

const tutorialData = {
    basic: [
        {
            name: "Addition",
            latex: "+",
            explanation: "Addition operator. Example: a + b",
            example: "a + b = c"
        },
        {
            name: "Subtraction",
            latex: "-",
            explanation: "Subtraction operator. Example: x - y",
            example: "x - y = z"
        },
        {
            name: "Multiplication",
            latex: "\\times",
            explanation: "Cross multiplication. For inline: use \\cdot for better spacing",
            example: "a \\times b"
        },
        {
            name: "Division (Fraction)",
            latex: "\\frac{a}{b}",
            explanation: "Creates a fraction with numerator and denominator",
            example: "\\frac{1}{2}"
        },
        {
            name: "Square Root",
            latex: "\\sqrt{x}",
            explanation: "Square root of x. Use \\sqrt[n]{x} for nth root",
            example: "\\sqrt{9} = 3"
        },
        {
            name: "Power/Exponent",
            latex: "x^{n}",
            explanation: "Raises x to the power n. Use curly braces for multi-character exponents",
            example: "x^{2} + y^{2}"
        },
        {
            name: "Subscript",
            latex: "x_{n}",
            explanation: "Subscript notation for indexed variables",
            example: "x_{1}, x_{2}, \\ldots, x_{n}"
        },
        {
            name: "Dot (Multiplication)",
            latex: "\\cdot",
            explanation: "Centered dot for multiplication, provides better spacing than ×",
            example: "a \\cdot b"
        }
    ],
    relations: [
        {
            name: "Equals",
            latex: "=",
            explanation: "Equality relation",
            example: "a = b"
        },
        {
            name: "Not Equals",
            latex: "\\neq",
            explanation: "Inequality relation",
            example: "a \\neq b"
        },
        {
            name: "Less Than",
            latex: "<",
            explanation: "Less than comparison",
            example: "a < b"
        },
        {
            name: "Greater Than",
            latex: ">",
            explanation: "Greater than comparison",
            example: "a > b"
        },
        {
            name: "Less Than or Equal",
            latex: "\\leq",
            explanation: "Less than or equal to",
            example: "a \\leq b"
        },
        {
            name: "Greater Than or Equal",
            latex: "\\geq",
            explanation: "Greater than or equal to",
            example: "a \\geq b"
        },
        {
            name: "Approximately Equal",
            latex: "\\approx",
            explanation: "Approximately equal to",
            example: "\\pi \\approx 3.14"
        },
        {
            name: "Proportional To",
            latex: "\\propto",
            explanation: "Proportional to relation",
            example: "y \\propto x"
        }
    ],
    logic: [
        {
            name: "For All",
            latex: "\\forall",
            explanation: "Universal quantifier - 'for all' elements",
            example: "\\forall x \\in \\mathbb{R}"
        },
        {
            name: "There Exists",
            latex: "\\exists",
            explanation: "Existential quantifier - 'there exists' element",
            example: "\\exists x \\in \\mathbb{R}"
        },
        {
            name: "Element Of",
            latex: "\\in",
            explanation: "Element membership in a set",
            example: "x \\in S"
        },
        {
            name: "Not Element Of",
            latex: "\\notin",
            explanation: "Not an element of a set",
            example: "x \\notin S"
        },
        {
            name: "Subset",
            latex: "\\subset",
            explanation: "Proper subset of a set",
            example: "A \\subset B"
        },
        {
            name: "Subset or Equal",
            latex: "\\subseteq",
            explanation: "Subset or equal to",
            example: "A \\subseteq B"
        },
        {
            name: "Union",
            latex: "\\cup",
            explanation: "Set union",
            example: "A \\cup B"
        },
        {
            name: "Intersection",
            latex: "\\cap",
            explanation: "Set intersection",
            example: "A \\cap B"
        }
    ],
    calculus: [
        {
            name: "Integral",
            latex: "\\int",
            explanation: "Indefinite integral. Add limits: \\int_a^b f(x)dx",
            example: "\\int_a^b f(x)\\,dx"
        },
        {
            name: "Derivative",
            latex: "\\frac{d}{dx}",
            explanation: "First derivative with respect to x",
            example: "\\frac{d}{dx}f(x)"
        },
        {
            name: "Partial Derivative",
            latex: "\\frac{\\partial}{\\partial x}",
            explanation: "Partial derivative with respect to x",
            example: "\\frac{\\partial f}{\\partial x}"
        },
        {
            name: "Sum",
            latex: "\\sum",
            explanation: "Summation operator. Add limits: \\sum_{i=1}^n",
            example: "\\sum_{i=1}^{n} a_i"
        },
        {
            name: "Product",
            latex: "\\prod",
            explanation: "Product operator with limits",
            example: "\\prod_{i=1}^{n} a_i"
        },
        {
            name: "Limit",
            latex: "\\lim_{x \\to a}",
            explanation: "Limit as x approaches a",
            example: "\\lim_{x \\to 0} \\frac{\\sin x}{x}"
        },
        {
            name: "Infinity",
            latex: "\\infty",
            explanation: "Infinity symbol",
            example: "\\int_0^{\\infty} f(x)\\,dx"
        },
        {
            name: "Nabla/Gradient",
            latex: "\\nabla",
            explanation: "Gradient operator or nabla",
            example: "\\nabla f"
        }
    ],
    greek: [
        { name: "Alpha", latex: "\\alpha", explanation: "Greek letter alpha (α)" },
        { name: "Beta", latex: "\\beta", explanation: "Greek letter beta (β)" },
        { name: "Gamma", latex: "\\gamma", explanation: "Greek letter gamma (γ)" },
        { name: "Delta", latex: "\\delta", explanation: "Greek letter delta (δ)" },
        { name: "Epsilon", latex: "\\epsilon", explanation: "Greek letter epsilon (ε)" },
        { name: "Theta", latex: "\\theta", explanation: "Greek letter theta (θ)" },
        { name: "Lambda", latex: "\\lambda", explanation: "Greek letter lambda (λ)" },
        { name: "Mu", latex: "\\mu", explanation: "Greek letter mu (μ)" },
        { name: "Pi", latex: "\\pi", explanation: "Greek letter pi (π)" },
        { name: "Rho", latex: "\\rho", explanation: "Greek letter rho (ρ)" },
        { name: "Sigma", latex: "\\sigma", explanation: "Greek letter sigma (σ)" },
        { name: "Phi", latex: "\\phi", explanation: "Greek letter phi (φ)" }
    ],
    advanced: [
        {
            name: "Real Numbers Set",
            latex: "\\mathbb{R}",
            explanation: "Set of all real numbers",
            example: "x \\in \\mathbb{R}"
        },
        {
            name: "Natural Numbers Set",
            latex: "\\mathbb{N}",
            explanation: "Set of natural numbers",
            example: "n \\in \\mathbb{N}"
        },
        {
            name: "Integer Numbers Set",
            latex: "\\mathbb{Z}",
            explanation: "Set of integers",
            example: "k \\in \\mathbb{Z}"
        },
        {
            name: "Rational Numbers Set",
            latex: "\\mathbb{Q}",
            explanation: "Set of rational numbers",
            example: "q \\in \\mathbb{Q}"
        },
        {
            name: "Complex Numbers Set",
            latex: "\\mathbb{C}",
            explanation: "Set of complex numbers",
            example: "z \\in \\mathbb{C}"
        },
        {
            name: "Absolute Value",
            latex: "|x|",
            explanation: "Absolute value or magnitude of x",
            example: "|x - a| < \\epsilon"
        },
        {
            name: "Norm",
            latex: "\\|x\\|",
            explanation: "Norm of a vector or element",
            example: "\\|\\mathbf{v}\\|"
        },
        {
            name: "Vector/Bold",
            latex: "\\mathbf{v}",
            explanation: "Bold notation for vectors",
            example: "\\mathbf{v} = (v_1, v_2, v_3)"
        }
    ]
};

let allSymbols = [];

function renderSymbol(symbol) {
    const card = document.createElement('div');
    card.className = 'symbol-card';
    
    const previewDiv = document.createElement('div');
    previewDiv.className = 'symbol-preview';
    
    try {
        // Use KaTeX to render the LaTeX
        katex.render(symbol.latex, previewDiv, { displayMode: false });
    } catch (e) {
        previewDiv.textContent = '✓ Rendered';
    }

    const examplePreviewDiv = document.createElement('div');
    examplePreviewDiv.className = 'example-preview';

    const html = `
        <div class="symbol-name">${symbol.name}</div>
        <div class="symbol-code">${symbol.latex}</div>
        <div class="symbol-explanation">${symbol.explanation}</div>
        <div class="example-section">
            <div class="example-title">Example:</div>
            <div class="example-latex">${symbol.example}</div>
        </div>
        <div class="symbol-buttons">
            <button class="btn-copy" data-latex="${symbol.latex}">📋 Copy LaTeX</button>
        </div>
    `;

    card.innerHTML = html;

    // Replace the preview div
    card.querySelector('.symbol-explanation').insertAdjacentElement('beforebegin', previewDiv);

    // Render example
    const exampleSection = card.querySelector('.example-section');
    try {
        katex.render(symbol.example, examplePreviewDiv, { displayMode: false });
        exampleSection.appendChild(examplePreviewDiv);
    } catch (e) {
        examplePreviewDiv.textContent = 'Example preview';
        exampleSection.appendChild(examplePreviewDiv);
    }

    // Copy button
    const copyBtn = card.querySelector('.btn-copy');
    copyBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const latex = copyBtn.dataset.latex;
        navigator.clipboard.writeText(latex).then(() => {
            copyBtn.textContent = '✓ Copied!';
            copyBtn.classList.add('copied');
            setTimeout(() => {
                copyBtn.textContent = '📋 Copy LaTeX';
                copyBtn.classList.remove('copied');
            }, 2000);
        });
    });

    return card;
}

function initializeTutorial() {
    // Populate all symbols
    Object.values(tutorialData).forEach(category => {
        allSymbols.push(...category);
    });

    // Render initial categories
    Object.entries(tutorialData).forEach(([category, symbols]) => {
        const grid = document.getElementById(`${category}-grid`);
        if (grid) {
            symbols.forEach(symbol => {
                grid.appendChild(renderSymbol(symbol));
            });
        }
    });

    // Category navigation
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.category-section').forEach(s => s.classList.remove('active'));
            
            e.target.classList.add('active');
            const category = e.target.dataset.category;
            document.getElementById(category).classList.add('active');
        });
    });

    // Search functionality
    const searchInput = document.getElementById('symbol-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            
            document.querySelectorAll('.symbol-card').forEach(card => {
                const name = card.querySelector('.symbol-name').textContent.toLowerCase();
                const code = card.querySelector('.symbol-code').textContent.toLowerCase();
                const explanation = card.querySelector('.symbol-explanation').textContent.toLowerCase();
                
                const matches = name.includes(query) || code.includes(query) || explanation.includes(query);
                card.style.display = matches ? 'block' : 'none';
            });
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeTutorial);

// Apply dark theme if saved
document.addEventListener('DOMContentLoaded', () => {
    const prefs = JSON.parse(localStorage.getItem('reana_preferences') || '{}');
    if (prefs.darkTheme === true) {
        document.documentElement.classList.add('theme-dark');
        document.body.classList.add('theme-dark');
    }
});
