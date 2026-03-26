import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Keyboard, ChevronDown, ChevronUp } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

interface Category {
  name: string;
  symbols: { label: string; value: string; preview?: string }[];
}

const categories: Category[] = [
  {
    name: "Fractions",
    symbols: [
      { label: "a/b", value: "\\frac{a}{b}" },
      { label: "dx/dy", value: "\\frac{dx}{dy}" },
      { label: "∂/∂x", value: "\\frac{\\partial}{\\partial x}" },
      { label: "1/n", value: "\\frac{1}{n}" },
      { label: "a+b/c", value: "\\frac{a+b}{c}" },
      { label: "n!/k!", value: "\\frac{n!}{k!(n-k)!}" },
    ],
  },
  {
    name: "Superscripts & Subscripts",
    symbols: [
      { label: "x²", value: "x^{2}" },
      { label: "xⁿ", value: "x^{n}" },
      { label: "x₁", value: "x_{1}" },
      { label: "xₙ", value: "x_{n}" },
      { label: "aᵢⱼ", value: "a_{i,j}" },
      { label: "x^{n+1}", value: "x^{n+1}" },
      { label: "eˣ", value: "e^{x}" },
      { label: "2ⁿ", value: "2^{n}" },
    ],
  },
  {
    name: "Radicals",
    symbols: [
      { label: "√x", value: "\\sqrt{x}" },
      { label: "∛x", value: "\\sqrt[3]{x}" },
      { label: "ⁿ√x", value: "\\sqrt[n]{x}" },
      { label: "√(a²+b²)", value: "\\sqrt{a^2 + b^2}" },
    ],
  },
  {
    name: "Integrals & Summations",
    symbols: [
      { label: "∫", value: "\\int" },
      { label: "∫ₐᵇ", value: "\\int_{a}^{b}" },
      { label: "∬", value: "\\iint" },
      { label: "∮", value: "\\oint" },
      { label: "∑", value: "\\sum_{i=1}^{n}" },
      { label: "∑∞", value: "\\sum_{n=0}^{\\infty}" },
      { label: "∏", value: "\\prod_{i=1}^{n}" },
      { label: "⋃", value: "\\bigcup_{i=1}^{n}" },
      { label: "⋂", value: "\\bigcap_{i=1}^{n}" },
    ],
  },
  {
    name: "Limits",
    symbols: [
      { label: "lim", value: "\\lim_{x \\to a}" },
      { label: "lim→∞", value: "\\lim_{n \\to \\infty}" },
      { label: "lim→0⁺", value: "\\lim_{x \\to 0^{+}}" },
      { label: "limsup", value: "\\limsup_{n \\to \\infty}" },
      { label: "liminf", value: "\\liminf_{n \\to \\infty}" },
      { label: "sup", value: "\\sup_{x \\in S}" },
      { label: "inf", value: "\\inf_{x \\in S}" },
    ],
  },
  {
    name: "Greek Letters",
    symbols: [
      { label: "α", value: "\\alpha" },
      { label: "β", value: "\\beta" },
      { label: "γ", value: "\\gamma" },
      { label: "Γ", value: "\\Gamma" },
      { label: "δ", value: "\\delta" },
      { label: "Δ", value: "\\Delta" },
      { label: "ε", value: "\\epsilon" },
      { label: "ζ", value: "\\zeta" },
      { label: "η", value: "\\eta" },
      { label: "θ", value: "\\theta" },
      { label: "λ", value: "\\lambda" },
      { label: "Λ", value: "\\Lambda" },
      { label: "μ", value: "\\mu" },
      { label: "ν", value: "\\nu" },
      { label: "π", value: "\\pi" },
      { label: "Π", value: "\\Pi" },
      { label: "ρ", value: "\\rho" },
      { label: "σ", value: "\\sigma" },
      { label: "Σ", value: "\\Sigma" },
      { label: "τ", value: "\\tau" },
      { label: "φ", value: "\\phi" },
      { label: "Φ", value: "\\Phi" },
      { label: "ψ", value: "\\psi" },
      { label: "ω", value: "\\omega" },
      { label: "Ω", value: "\\Omega" },
    ],
  },
  {
    name: "Set Theory",
    symbols: [
      { label: "∈", value: "\\in" },
      { label: "∉", value: "\\notin" },
      { label: "⊂", value: "\\subset" },
      { label: "⊆", value: "\\subseteq" },
      { label: "⊃", value: "\\supset" },
      { label: "⊇", value: "\\supseteq" },
      { label: "∪", value: "\\cup" },
      { label: "∩", value: "\\cap" },
      { label: "∅", value: "\\emptyset" },
      { label: "\\", value: "\\setminus" },
      { label: "ℝ", value: "\\mathbb{R}" },
      { label: "ℤ", value: "\\mathbb{Z}" },
      { label: "ℕ", value: "\\mathbb{N}" },
      { label: "ℚ", value: "\\mathbb{Q}" },
      { label: "ℂ", value: "\\mathbb{C}" },
    ],
  },
  {
    name: "Logical Symbols",
    symbols: [
      { label: "∀", value: "\\forall" },
      { label: "∃", value: "\\exists" },
      { label: "∄", value: "\\nexists" },
      { label: "→", value: "\\rightarrow" },
      { label: "⇒", value: "\\Rightarrow" },
      { label: "⇔", value: "\\Leftrightarrow" },
      { label: "¬", value: "\\neg" },
      { label: "∧", value: "\\land" },
      { label: "∨", value: "\\lor" },
      { label: "⊢", value: "\\vdash" },
      { label: "⊨", value: "\\models" },
      { label: "∴", value: "\\therefore" },
      { label: "∵", value: "\\because" },
    ],
  },
  {
    name: "Relations & Operators",
    symbols: [
      { label: "≠", value: "\\neq" },
      { label: "≤", value: "\\leq" },
      { label: "≥", value: "\\geq" },
      { label: "≈", value: "\\approx" },
      { label: "≡", value: "\\equiv" },
      { label: "∼", value: "\\sim" },
      { label: "≺", value: "\\prec" },
      { label: "≻", value: "\\succ" },
      { label: "±", value: "\\pm" },
      { label: "∓", value: "\\mp" },
      { label: "×", value: "\\times" },
      { label: "÷", value: "\\div" },
      { label: "·", value: "\\cdot" },
      { label: "∞", value: "\\infty" },
      { label: "∂", value: "\\partial" },
      { label: "∇", value: "\\nabla" },
    ],
  },
  {
    name: "Brackets",
    symbols: [
      { label: "(…)", value: "\\left( \\right)" },
      { label: "[…]", value: "\\left[ \\right]" },
      { label: "{…}", value: "\\left\\{ \\right\\}" },
      { label: "⟨…⟩", value: "\\langle \\rangle" },
      { label: "|…|", value: "\\left| \\right|" },
      { label: "‖…‖", value: "\\left\\| \\right\\|" },
      { label: "⌊…⌋", value: "\\lfloor \\rfloor" },
      { label: "⌈…⌉", value: "\\lceil \\rceil" },
    ],
  },
  {
    name: "Real Analysis",
    symbols: [
      { label: "ε-δ", value: "\\forall \\epsilon > 0, \\exists \\delta > 0" },
      { label: "→ as n→∞", value: "\\to \\text{ as } n \\to \\infty" },
      { label: "|x-a|<δ", value: "|x - a| < \\delta" },
      { label: "|f(x)-L|<ε", value: "|f(x) - L| < \\epsilon" },
      { label: "sup S", value: "\\sup S" },
      { label: "inf S", value: "\\inf S" },
      { label: "f: A→B", value: "f: A \\to B" },
      { label: "f∘g", value: "f \\circ g" },
      { label: "f⁻¹", value: "f^{-1}" },
      { label: "{xₙ}", value: "\\{x_n\\}_{n=1}^{\\infty}" },
      { label: "Cauchy", value: "\\forall \\epsilon > 0, \\exists N \\in \\mathbb{N}, \\forall m,n \\geq N: |x_m - x_n| < \\epsilon" },
    ],
  },
  {
    name: "Matrices",
    symbols: [
      { label: "2×2", value: "\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}" },
      { label: "3×3", value: "\\begin{pmatrix} a & b & c \\\\ d & e & f \\\\ g & h & i \\end{pmatrix}" },
      { label: "[2×2]", value: "\\begin{bmatrix} a & b \\\\ c & d \\end{bmatrix}" },
      { label: "det", value: "\\begin{vmatrix} a & b \\\\ c & d \\end{vmatrix}" },
      { label: "cases", value: "\\begin{cases} a & \\text{if } x > 0 \\\\ b & \\text{otherwise} \\end{cases}" },
    ],
  },
];

interface EquationKeyboardProps {
  onInsert: (symbol: string) => void;
}

const EquationKeyboard = ({ onInsert }: EquationKeyboardProps) => {
  const [open, setOpen] = useState(false);
  const [activeCategory, setActiveCategory] = useState(0);

  return (
    <div className="relative">
      <Button
        variant="outline"
        size="sm"
        onClick={() => setOpen(!open)}
        className="gap-1.5 border-border text-foreground hover:bg-muted"
      >
        <Keyboard className="h-4 w-4" />
        <span className="hidden sm:inline text-xs">Equations</span>
        {open ? <ChevronUp className="h-3 w-3" /> : <ChevronDown className="h-3 w-3" />}
      </Button>

      <AnimatePresence>
        {open && (
          <motion.div
            initial={{ opacity: 0, y: -8, scaleY: 0.95 }}
            animate={{ opacity: 1, y: 0, scaleY: 1 }}
            exit={{ opacity: 0, y: -8, scaleY: 0.95 }}
            transition={{ duration: 0.15 }}
            className="absolute top-full left-0 mt-2 z-50 w-[520px] bg-card border border-border rounded-lg shadow-lg overflow-hidden"
          >
            {/* Category tabs */}
            <div className="border-b border-border bg-muted/30 px-2 py-1.5 flex flex-wrap gap-1">
              {categories.map((cat, i) => (
                <button
                  key={cat.name}
                  onClick={() => setActiveCategory(i)}
                  className={`px-2 py-1 rounded text-[11px] font-medium transition-colors ${
                    activeCategory === i
                      ? "bg-primary text-primary-foreground"
                      : "text-muted-foreground hover:bg-muted hover:text-foreground"
                  }`}
                >
                  {cat.name}
                </button>
              ))}
            </div>

            {/* Symbol grid */}
            <div className="p-3">
              <div className="grid grid-cols-5 gap-1.5">
                {categories[activeCategory].symbols.map((s) => (
                  <button
                    key={s.value}
                    onClick={() => {
                      onInsert(s.value);
                    }}
                    title={s.value}
                    className="h-9 px-2 rounded-md border border-border bg-card hover:bg-accent hover:text-accent-foreground transition-colors text-xs font-mono font-medium truncate"
                  >
                    {s.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Footer */}
            <div className="border-t border-border bg-muted/20 px-3 py-1.5 flex justify-between items-center">
              <span className="text-[10px] text-muted-foreground">
                Click to insert • {categories[activeCategory].name}
              </span>
              <button
                onClick={() => setOpen(false)}
                className="text-[10px] text-muted-foreground hover:text-foreground font-medium"
              >
                Close
              </button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default EquationKeyboard;
