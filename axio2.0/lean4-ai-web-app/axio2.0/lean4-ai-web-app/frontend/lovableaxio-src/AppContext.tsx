import React, { createContext, useContext, useState, useCallback } from "react";

export type InputMode = "latex" | "natural";

export interface ProofStep {
  id: string;
  content: string;
  verified: boolean | null;
  feedback: string | null;
  verifying: boolean;
  mode: InputMode;
  locked: boolean; // true = user can edit; false = locked out (previous step not verified)
}

export interface SavedProof {
  id: string;
  theorem: string;
  steps: ProofStep[];
  date: string;
  accuracy: number;
}

interface AppState {
  theorem: string;
  setTheorem: (t: string) => void;
  theoremMode: InputMode;
  setTheoremMode: React.Dispatch<React.SetStateAction<InputMode>>;
  steps: ProofStep[];
  setSteps: React.Dispatch<React.SetStateAction<ProofStep[]>>;
  addStep: () => void;
  removeStep: (id: string) => void;
  updateStep: (id: string, content: string) => void;
  updateStepMode: (id: string, mode: InputMode) => void;
  verifyStep: (id: string) => void;
  canAddStep: boolean;
  canCompleteProof: boolean;
  savedProofs: SavedProof[];
  saveCurrentProof: () => void;
  deleteProof: (id: string) => void;
  loadProof: (proof: SavedProof) => void;
  resetWorkspace: () => void;
  fontSize: number;
  setFontSize: React.Dispatch<React.SetStateAction<number>>;
  darkMode: boolean;
  setDarkMode: React.Dispatch<React.SetStateAction<boolean>>;
  notifications: boolean;
  setNotifications: React.Dispatch<React.SetStateAction<boolean>>;
  verificationHistory: VerificationEntry[];
}

export interface VerificationEntry {
  stepIndex: number;
  correct: boolean;
  message: string;
  explanation?: string;
  hint?: string;
  rule?: string;
  timestamp: number;
}

const AppContext = createContext<AppState | null>(null);

export const useAppState = () => {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error("useAppState must be inside AppProvider");
  return ctx;
};

const makeStep = (locked = false): ProofStep => ({
  id: crypto.randomUUID(),
  content: "",
  verified: null,
  feedback: null,
  verifying: false,
  mode: "latex",
  locked,
});

const nlToLatex = (text: string): string => {
  let result = text;
  result = result.replace(/\bfor all\b/gi, "\\forall");
  result = result.replace(/\bthere exists?\b/gi, "\\exists");
  result = result.replace(/\bepsilon\b/gi, "\\epsilon");
  result = result.replace(/\bdelta\b/gi, "\\delta");
  result = result.replace(/\bimplies\b/gi, "\\Rightarrow");
  result = result.replace(/\bif and only if\b/gi, "\\Leftrightarrow");
  result = result.replace(/\bin\b/gi, "\\in");
  result = result.replace(/\bsubset of\b/gi, "\\subset");
  result = result.replace(/\bnot equal to\b/gi, "\\neq");
  result = result.replace(/\bless than or equal to\b/gi, "\\leq");
  result = result.replace(/\bgreater than or equal to\b/gi, "\\geq");
  result = result.replace(/\binfinity\b/gi, "\\infty");
  result = result.replace(/\bsqrt\(([^)]+)\)/gi, "\\sqrt{$1}");
  return result;
};

interface FeedbackOption {
  correct: boolean;
  msg: string;
  explanation?: string;
  hint?: string;
  rule: string;
}

const feedbackOptions: FeedbackOption[] = [
  {
    correct: true,
    msg: "Correct — logically valid transition.",
    rule: "Modus Ponens",
  },
  {
    correct: true,
    msg: "Well-structured reasoning. Proceed to next step.",
    rule: "Direct Proof",
  },
  {
    correct: true,
    msg: "This step correctly applies the definition.",
    rule: "Definition Application",
  },
  {
    correct: false,
    msg: "This step does not logically follow from the previous assumption.",
    explanation: "The logical connection between this step and the preceding one is unclear. The transition requires an intermediate justification or a reference to a known theorem.",
    hint: "Use the definition of continuity at a point.",
    rule: "Missing Justification",
  },
  {
    correct: false,
    msg: "Logical gap detected in the reasoning chain.",
    explanation: "You have skipped a necessary intermediate step. The conclusion drawn here requires additional support from a lemma or previously established result.",
    hint: "Apply the epsilon-delta definition to establish the bound.",
    rule: "Logical Gap",
  },
  {
    correct: false,
    msg: "Unverified claim — this assertion requires proof.",
    explanation: "The statement made in this step is not self-evident and has not been justified by prior steps. A supporting argument is needed before proceeding.",
    hint: "You need to justify boundedness before invoking completeness.",
    rule: "Unverified Claim",
  },
  {
    correct: false,
    msg: "The transition here lacks sufficient rigor.",
    explanation: "While the general direction is correct, the formal justification is incomplete. Consider being more explicit about which theorem or definition you are applying.",
    hint: "Consider proving this by contradiction.",
    rule: "Insufficient Rigor",
  },
  {
    correct: false,
    msg: "This step requires a convergence argument.",
    explanation: "You are asserting convergence without establishing that the sequence satisfies the necessary conditions. The Cauchy criterion or a monotone convergence argument may be needed.",
    hint: "Show that the sequence is Cauchy.",
    rule: "Missing Convergence Proof",
  },
];

export const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [theorem, setTheorem] = useState("");
  const [theoremMode, setTheoremMode] = useState<InputMode>("latex");
  const [steps, setSteps] = useState<ProofStep[]>([makeStep(false)]);
  const [verificationHistory, setVerificationHistory] = useState<VerificationEntry[]>([]);
  const [savedProofs, setSavedProofs] = useState<SavedProof[]>([
    {
      id: "demo-1",
      theorem: "\\sqrt{2} \\text{ is irrational}",
      steps: [
        { id: "d1", content: "Assume √2 = p/q where p,q are coprime integers", verified: true, feedback: "✅ Correct step", verifying: false, mode: "latex", locked: false },
        { id: "d2", content: "Then 2 = p²/q², so p² = 2q²", verified: true, feedback: "✅ Correct step", verifying: false, mode: "latex", locked: false },
      ],
      date: "2026-02-25",
      accuracy: 95,
    },
    {
      id: "demo-2",
      theorem: "\\sum_{k=1}^{n} k = \\frac{n(n+1)}{2}",
      steps: [
        { id: "d3", content: "Base case: n=1, LHS=1, RHS=1(2)/2=1 ✓", verified: true, feedback: "✅ Correct step", verifying: false, mode: "latex", locked: false },
      ],
      date: "2026-02-20",
      accuracy: 88,
    },
  ]);
  const [fontSize, setFontSize] = useState(16);
  const [darkMode, setDarkMode] = useState(false);
  const [notifications, setNotifications] = useState(true);

  // Step locking: first step always unlocked, subsequent steps locked until previous is verified
  const recalcLocks = (stepsArr: ProofStep[]): ProofStep[] => {
    return stepsArr.map((s, i) => ({
      ...s,
      locked: i === 0 ? false : stepsArr[i - 1].verified !== true,
    }));
  };

  const addStep = useCallback(() => {
    setSteps((prev) => {
      const lastStep = prev[prev.length - 1];
      if (lastStep && lastStep.verified !== true) return prev; // can't add if last not verified
      const newSteps = [...prev, makeStep(true)];
      return recalcLocks(newSteps);
    });
  }, []);

  const removeStep = useCallback((id: string) => {
    setSteps((prev) => {
      if (prev.length <= 1) return prev;
      const filtered = prev.filter((s) => s.id !== id);
      return recalcLocks(filtered);
    });
  }, []);

  const updateStep = useCallback((id: string, content: string) => {
    setSteps((prev) => prev.map((s) => (s.id === id ? { ...s, content } : s)));
  }, []);

  const updateStepMode = useCallback((id: string, mode: InputMode) => {
    setSteps((prev) => prev.map((s) => (s.id === id ? { ...s, mode } : s)));
  }, []);

  const verifyStep = useCallback((id: string) => {
    setSteps((prev) => prev.map((s) => (s.id === id ? { ...s, verifying: true, verified: null, feedback: null } : s)));
    const stepIndex = steps.findIndex((s) => s.id === id);
    setTimeout(() => {
      const fb = feedbackOptions[Math.floor(Math.random() * feedbackOptions.length)];
      setSteps((prev) => {
        const updated = prev.map((s) => (s.id === id ? { ...s, verifying: false, verified: fb.correct, feedback: fb.msg } : s));
        return recalcLocks(updated);
      });
      setVerificationHistory((prev) => [
        ...prev,
        {
          stepIndex: stepIndex + 1,
          correct: fb.correct,
          message: fb.msg,
          explanation: fb.explanation,
          hint: fb.hint,
          rule: fb.rule,
          timestamp: Date.now(),
        },
      ]);
    }, 2000);
  }, [steps]);

  const canAddStep = steps.length === 0 || steps[steps.length - 1].verified === true;
  const canCompleteProof = steps.length > 0 && steps.every((s) => s.verified === true) && theorem.trim().length > 0;

  const saveCurrentProof = useCallback(() => {
    if (!theorem.trim()) return;
    const verifiedSteps = steps.filter((s) => s.verified !== null);
    const correctSteps = steps.filter((s) => s.verified === true);
    const accuracy = verifiedSteps.length > 0 ? Math.round((correctSteps.length / verifiedSteps.length) * 100) : 0;
    const proof: SavedProof = {
      id: crypto.randomUUID(),
      theorem,
      steps: [...steps],
      date: new Date().toISOString().split("T")[0],
      accuracy,
    };
    setSavedProofs((prev) => [proof, ...prev]);
  }, [theorem, steps]);

  const deleteProof = useCallback((id: string) => {
    setSavedProofs((prev) => prev.filter((p) => p.id !== id));
  }, []);

  const loadProof = useCallback((proof: SavedProof) => {
    setTheorem(proof.theorem);
    setSteps(recalcLocks(proof.steps.map((s) => ({ ...s }))));
    setVerificationHistory([]);
  }, []);

  const resetWorkspace = useCallback(() => {
    setTheorem("");
    setSteps([makeStep(false)]);
    setVerificationHistory([]);
  }, []);

  return (
    <AppContext.Provider
      value={{
        theorem, setTheorem, theoremMode, setTheoremMode,
        steps, setSteps, addStep, removeStep, updateStep, updateStepMode,
        verifyStep, canAddStep, canCompleteProof,
        savedProofs, saveCurrentProof, deleteProof, loadProof,
        resetWorkspace, fontSize, setFontSize, darkMode, setDarkMode,
        notifications, setNotifications, verificationHistory,
      }}
    >
      {children}
    </AppContext.Provider>
  );
};
