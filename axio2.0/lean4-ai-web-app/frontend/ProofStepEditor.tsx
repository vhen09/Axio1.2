import { motion, AnimatePresence } from "framer-motion";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { Trash2, CheckCircle2, Loader2, Lock, AlertTriangle, Lightbulb } from "lucide-react";
import LaTeXPreview from "./LaTeXPreview";
import EquationKeyboard from "./EquationKeyboard";
import type { ProofStep as ProofStepType, InputMode } from "@/context/AppContext";

interface ProofStepEditorProps {
  step: ProofStepType;
  index: number;
  fontSize: number;
  onUpdate: (id: string, content: string) => void;
  onUpdateMode: (id: string, mode: InputMode) => void;
  onVerify: (id: string) => void;
  onRemove: (id: string) => void;
  canRemove: boolean;
}

const ProofStepEditor = ({ step, index, fontSize, onUpdate, onUpdateMode, onVerify, onRemove, canRemove }: ProofStepEditorProps) => {
  const handleInsertSymbol = (symbol: string) => {
    onUpdate(step.id, step.content + " " + symbol);
  };

  const isLocked = step.locked;
  const isVerified = step.verified === true;
  const isFailed = step.verified === false;

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.3 }}
      className={`glass-card p-4 space-y-3 transition-all ${
        isLocked ? "opacity-40 pointer-events-none" : ""
      } ${isVerified ? "ring-1 ring-accent/30" : ""} ${isFailed ? "ring-1 ring-highlight/30" : ""}`}
    >
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <h4 className="text-sm font-semibold text-primary">Step {index + 1}</h4>
          {isVerified && <CheckCircle2 className="h-4 w-4 text-accent" />}
          {isLocked && <Lock className="h-3.5 w-3.5 text-muted-foreground" />}
        </div>
        <div className="flex items-center gap-2">
          {/* Mode toggle */}
          <div className="flex rounded-md border border-border overflow-hidden text-[11px]">
            <button
              onClick={() => onUpdateMode(step.id, "latex")}
              className={`px-2 py-1 font-medium transition-colors ${
                step.mode === "latex"
                  ? "bg-primary text-primary-foreground"
                  : "bg-card text-muted-foreground hover:bg-muted"
              }`}
            >
              LaTeX
            </button>
            <button
              onClick={() => onUpdateMode(step.id, "natural")}
              className={`px-2 py-1 font-medium transition-colors ${
                step.mode === "natural"
                  ? "bg-primary text-primary-foreground"
                  : "bg-card text-muted-foreground hover:bg-muted"
              }`}
            >
              Natural
            </button>
          </div>
          <EquationKeyboard onInsert={handleInsertSymbol} />
          <Button
            variant="outline"
            size="sm"
            onClick={() => onVerify(step.id)}
            disabled={step.verifying || !step.content.trim() || isLocked}
            className={`gap-1.5 transition-all ${
              !step.verifying && step.content.trim() && !isLocked
                ? "border-accent text-accent hover:bg-accent hover:text-accent-foreground"
                : ""
            }`}
          >
            {step.verifying ? (
              <Loader2 className="h-4 w-4 animate-spin-slow" />
            ) : (
              <CheckCircle2 className="h-4 w-4" />
            )}
            <span className="hidden sm:inline">Verify</span>
          </Button>
          {canRemove && (
            <Button variant="ghost" size="sm" onClick={() => onRemove(step.id)} className="text-destructive hover:text-destructive">
              <Trash2 className="h-4 w-4" />
            </Button>
          )}
        </div>
      </div>

      {/* Input */}
      <Textarea
        value={step.content}
        onChange={(e) => onUpdate(step.id, e.target.value)}
        placeholder={
          step.mode === "latex"
            ? `Write LaTeX proof for step ${index + 1}…`
            : `Describe step ${index + 1} in plain English…`
        }
        className="min-h-[80px] font-mono resize-none focus:border-primary focus:ring-primary"
        style={{ fontSize: `${fontSize}px` }}
        disabled={isLocked}
      />

      <LaTeXPreview content={step.content} />

      {/* Structured Feedback */}
      <AnimatePresence>
        {step.feedback && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            className={`rounded-lg border overflow-hidden ${
              step.verified
                ? "bg-accent/5 border-accent/20"
                : "bg-highlight/5 border-highlight/30"
            }`}
          >
            {/* Error/Success summary */}
            <div className={`px-4 py-2.5 flex items-start gap-2 ${
              step.verified ? "text-accent" : "text-foreground"
            }`}>
              {step.verified ? (
                <CheckCircle2 className="h-4 w-4 flex-shrink-0 mt-0.5 text-accent" />
              ) : (
                <AlertTriangle className="h-4 w-4 flex-shrink-0 mt-0.5 text-highlight" />
              )}
              <p className="text-sm font-medium">{step.feedback}</p>
            </div>

            {/* Additional details for incorrect steps */}
            {!step.verified && step.feedback && (
              <div className="px-4 pb-3 space-y-2 border-t border-highlight/10 pt-2 ml-6">
                <p className="text-xs text-muted-foreground leading-relaxed">
                  The logical connection requires an intermediate justification or reference to a known theorem.
                </p>
                <div className="flex items-start gap-1.5">
                  <Lightbulb className="h-3.5 w-3.5 text-highlight flex-shrink-0 mt-0.5" />
                  <p className="text-xs font-medium text-foreground">
                    Apply the epsilon-delta definition to establish the required bound.
                  </p>
                </div>
              </div>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
};

export default ProofStepEditor;
