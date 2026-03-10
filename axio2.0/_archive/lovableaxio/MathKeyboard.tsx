import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Keyboard } from "lucide-react";

const symbols = [
  { label: "√", value: "\\sqrt{}" },
  { label: "∑", value: "\\sum_{i=1}^{n}" },
  { label: "∫", value: "\\int_{a}^{b}" },
  { label: "∀", value: "\\forall" },
  { label: "∃", value: "\\exists" },
  { label: "→", value: "\\rightarrow" },
  { label: "⇒", value: "\\Rightarrow" },
  { label: "⇔", value: "\\Leftrightarrow" },
  { label: "∈", value: "\\in" },
  { label: "∉", value: "\\notin" },
  { label: "⊂", value: "\\subset" },
  { label: "⊆", value: "\\subseteq" },
  { label: "∪", value: "\\cup" },
  { label: "∩", value: "\\cap" },
  { label: "≠", value: "\\neq" },
  { label: "≤", value: "\\leq" },
  { label: "≥", value: "\\geq" },
  { label: "∞", value: "\\infty" },
  { label: "α", value: "\\alpha" },
  { label: "β", value: "\\beta" },
  { label: "γ", value: "\\gamma" },
  { label: "δ", value: "\\delta" },
  { label: "ε", value: "\\epsilon" },
  { label: "θ", value: "\\theta" },
  { label: "λ", value: "\\lambda" },
  { label: "π", value: "\\pi" },
  { label: "σ", value: "\\sigma" },
  { label: "φ", value: "\\phi" },
  { label: "ω", value: "\\omega" },
  { label: "∂", value: "\\partial" },
  { label: "×", value: "\\times" },
  { label: "÷", value: "\\div" },
  { label: "±", value: "\\pm" },
  { label: "≈", value: "\\approx" },
  { label: "frac", value: "\\frac{}{}" },
  { label: "x²", value: "^{2}" },
];

interface MathKeyboardProps {
  onInsert: (symbol: string) => void;
}

const MathKeyboard = ({ onInsert }: MathKeyboardProps) => {
  const [open, setOpen] = useState(false);

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" size="sm" className="gap-1.5">
          <Keyboard className="h-4 w-4" />
          <span className="hidden sm:inline">Symbols</span>
        </Button>
      </DialogTrigger>
      <DialogContent className="max-w-md">
        <DialogHeader>
          <DialogTitle>Math Symbols</DialogTitle>
        </DialogHeader>
        <div className="grid grid-cols-6 gap-2">
          {symbols.map((s) => (
            <button
              key={s.value}
              onClick={() => { onInsert(s.value); setOpen(false); }}
              className="h-10 rounded-md border border-border bg-muted/50 hover:bg-primary hover:text-primary-foreground transition-colors text-sm font-mono font-medium"
            >
              {s.label}
            </button>
          ))}
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default MathKeyboard;
