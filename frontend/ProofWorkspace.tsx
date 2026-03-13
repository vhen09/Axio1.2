import { AnimatePresence, motion } from "framer-motion";
import { Button } from "./button";
import { Textarea } from "./textarea";
import { Plus, Minus, FilePlus, FolderOpen, CheckCircle2, AlertTriangle, Clock, Award } from "lucide-react";
import { useNavigate } from "react-router-dom";
import { useAppState } from "./AppContext";
import LaTeXPreview from "./LaTeXPreview";
import ProofStepEditor from "./ProofStepEditor";
import VerificationPanel from "./VerificationPanel";
import EquationKeyboard from "./EquationKeyboard";
import { toast } from "sonner";

const ProofWorkspace = () => {
  const navigate = useNavigate();
  const {
    theorem, setTheorem, theoremMode, setTheoremMode,
    steps, addStep, removeStep, updateStep, updateStepMode,
    verifyStep, fontSize, setFontSize, resetWorkspace, saveCurrentProof,
    canAddStep, canCompleteProof,
  } = useAppState();

  const handleInsertTheoremSymbol = (symbol: string) => {
    setTheorem(theorem + " " + symbol);
  };

  const handleSave = () => {
    if (!theorem.trim()) {
      toast.error("Enter a theorem before saving.");
      return;
    }
    saveCurrentProof();
    toast.success("Proof saved!");
  };

  const handleComplete = () => {
    if (!canCompleteProof) return;
    saveCurrentProof();
    toast.success("🎉 Proof completed and saved!");
  };

  return (
    <div className="animate-fade-in">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
          <h1 className="text-2xl md:text-3xl font-bold text-primary font-serif">AI Proof Tutor</h1>
          <p className="text-muted-foreground text-sm mt-1">Unified Proof Workspace</p>
        </div>
        <div className="flex items-center gap-2 flex-wrap">
          <Button variant="outline" size="sm" onClick={() => navigate("/proofs")} className="gap-1.5">
            <FolderOpen className="h-4 w-4" /> View My Proofs
          </Button>
          <Button size="sm" onClick={resetWorkspace} className="gap-1.5 bg-accent text-accent-foreground hover:bg-accent/90">
            <FilePlus className="h-4 w-4" /> New Proof
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left: Main workspace (2/3) */}
        <div className="lg:col-span-2 space-y-6">
          {/* Section 1: Theorem Input */}
          <section className="glass-card p-5 space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="font-semibold text-primary text-lg">Input Theorem or Statement</h2>
              <div className="flex items-center gap-2">
                {/* Mode toggle */}
                <div className="flex rounded-lg border border-border overflow-hidden text-xs">
                  <button
                    onClick={() => setTheoremMode("latex")}
                    className={`px-3 py-1.5 font-medium transition-colors ${
                      theoremMode === "latex"
                        ? "bg-primary text-primary-foreground"
                        : "bg-card text-muted-foreground hover:bg-muted"
                    }`}
                  >
                    LaTeX
                  </button>
                  <button
                    onClick={() => setTheoremMode("natural")}
                    className={`px-3 py-1.5 font-medium transition-colors ${
                      theoremMode === "natural"
                        ? "bg-primary text-primary-foreground"
                        : "bg-card text-muted-foreground hover:bg-muted"
                    }`}
                  >
                    Natural Language
                  </button>
                </div>
                {/* Equation keyboard */}
                <EquationKeyboard onInsert={handleInsertTheoremSymbol} />
                {/* Font size */}
                <div className="flex items-center gap-1">
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => setFontSize((size) => Math.max(12, size - 2))}
                    className="text-xs font-mono h-7 w-7 p-0"
                  >
                    A−
                  </Button>
                  <span className="text-xs text-muted-foreground font-mono w-8 text-center">{fontSize}</span>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => setFontSize((size) => Math.min(28, size + 2))}
                    className="text-xs font-mono h-7 w-7 p-0"
                  >
                    A+
                  </Button>
                </div>
              </div>
            </div>
            <Textarea
              value={theorem}
              onChange={(event) => setTheorem(event.target.value)}
              placeholder={
                theoremMode === "latex"
                  ? "Enter LaTeX theorem, e.g. \\forall \\epsilon > 0, \\exists \\delta > 0 …"
                  : "Enter in natural language, e.g. 'For all epsilon greater than zero…'"
              }
              className="min-h-[100px] font-mono resize-none transition-all duration-200 focus:border-primary focus:ring-primary"
              style={{ fontSize: `${fontSize}px` }}
            />
            <div>
              <p className="text-xs text-muted-foreground mb-1.5 font-medium">Live Preview</p>
              <LaTeXPreview content={theorem} />
            </div>
          </section>

          {/* Section 2: Steps */}
          <section className="space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="font-semibold text-primary text-lg">Step-by-Step Proof Builder</h2>
              <div className="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={addStep}
                  disabled={!canAddStep}
                  className={`gap-1.5 transition-all ${
                    canAddStep
                      ? "border-accent text-accent hover:bg-accent hover:text-accent-foreground"
                      : "opacity-40 cursor-not-allowed"
                  }`}
                >
                  <Plus className="h-4 w-4" /> Add Step
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => { if (steps.length > 1) removeStep(steps[steps.length - 1].id); }}
                  disabled={steps.length <= 1}
                  className="gap-1.5"
                >
                  <Minus className="h-4 w-4" /> Remove
                </Button>
              </div>
            </div>

            <AnimatePresence mode="popLayout">
              {steps.map((step, i) => (
                <ProofStepEditor
                  key={step.id}
                  step={step}
                  index={i}
                  fontSize={fontSize}
                  onUpdate={updateStep}
                  onUpdateMode={updateStepMode}
                  onVerify={verifyStep}
                  onRemove={removeStep}
                  canRemove={steps.length > 1}
                />
              ))}
            </AnimatePresence>
          </section>

          {/* Complete Proof */}
          <div className="flex justify-end gap-3">
            <Button onClick={handleSave} variant="outline" className="px-6">
              Save Draft
            </Button>
            <Button
              onClick={handleComplete}
              disabled={!canCompleteProof}
              className={`px-6 gap-2 transition-all ${
                canCompleteProof
                  ? "bg-accent text-accent-foreground hover:bg-accent/85 shadow-md"
                  : "bg-muted text-muted-foreground opacity-40 cursor-not-allowed"
              }`}
            >
              <Award className="h-4 w-4" />
              Complete Proof
            </Button>
          </div>
        </div>

        {/* Right: Verification Panel (1/3) */}
        <div className="lg:col-span-1">
          <VerificationPanel />
        </div>
      </div>
    </div>
  );
};

export default ProofWorkspace;
