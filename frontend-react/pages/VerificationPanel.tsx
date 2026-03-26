import { useAppState, VerificationEntry } from "@/context/AppContext";
import { motion, AnimatePresence } from "framer-motion";
import { CheckCircle2, AlertTriangle, Clock, ShieldCheck, Lightbulb } from "lucide-react";

const VerificationPanel = () => {
  const { verificationHistory, steps, canCompleteProof } = useAppState();

  const activelyVerifying = steps.some((s) => s.verifying);
  const latestEntries = verificationHistory.slice(-10).reverse();

  return (
    <div className="glass-card p-5 space-y-4 sticky top-20">
      <h3 className="font-semibold text-primary text-lg flex items-center gap-2">
        <ShieldCheck className="h-5 w-5" />
        Verification Panel
      </h3>

      {/* Status summary */}
      <div className="grid grid-cols-2 gap-3">
        <div className="rounded-lg bg-muted/50 p-3 text-center">
          <p className="text-2xl font-bold text-accent">
            {steps.filter((s) => s.verified === true).length}
          </p>
          <p className="text-xs text-muted-foreground">Verified</p>
        </div>
        <div className="rounded-lg bg-muted/50 p-3 text-center">
          <p className="text-2xl font-bold text-foreground">
            {steps.length}
          </p>
          <p className="text-xs text-muted-foreground">Total Steps</p>
        </div>
      </div>

      {/* Proof completion status */}
      <div className={`rounded-lg px-4 py-3 text-sm font-medium flex items-center gap-2 ${
        canCompleteProof
          ? "bg-accent/10 text-accent border border-accent/20"
          : "bg-muted text-muted-foreground border border-border"
      }`}>
        {canCompleteProof ? (
          <>
            <CheckCircle2 className="h-4 w-4 flex-shrink-0" />
            All steps verified — ready to complete!
          </>
        ) : (
          <>
            <Clock className="h-4 w-4 flex-shrink-0" />
            Verify all steps to complete proof.
          </>
        )}
      </div>

      {/* Active verification */}
      <AnimatePresence>
        {activelyVerifying && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            className="rounded-lg bg-primary/5 border border-primary/20 px-4 py-3 flex items-center gap-3"
          >
            <div className="h-4 w-4 border-2 border-primary border-t-transparent rounded-full animate-spin-slow" />
            <span className="text-sm font-medium text-primary">Verifying step…</span>
          </motion.div>
        )}
      </AnimatePresence>

      {/* History */}
      <div className="space-y-2">
        <p className="text-xs font-medium text-muted-foreground uppercase tracking-wider">Recent Feedback</p>
        {latestEntries.length === 0 ? (
          <p className="text-sm text-muted-foreground italic">No verifications yet.</p>
        ) : (
          <div className="space-y-2 max-h-[400px] overflow-y-auto">
            <AnimatePresence>
              {latestEntries.map((entry, i) => (
                <motion.div
                  key={entry.timestamp}
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: i * 0.05 }}
                  className={`rounded-lg px-3 py-2.5 text-sm border ${
                    entry.correct
                      ? "bg-accent/5 border-accent/20"
                      : "bg-highlight/5 border-highlight/30"
                  }`}
                >
                  <div className="flex items-start gap-2">
                    {entry.correct ? (
                      <CheckCircle2 className="h-4 w-4 text-accent flex-shrink-0 mt-0.5" />
                    ) : (
                      <AlertTriangle className="h-4 w-4 text-highlight flex-shrink-0 mt-0.5" />
                    )}
                    <div className="min-w-0 space-y-1">
                      <p className="font-medium text-foreground text-xs">Step {entry.stepIndex}</p>
                      <p className="text-muted-foreground text-xs">{entry.message}</p>

                      {/* Explanation for incorrect */}
                      {!entry.correct && entry.explanation && (
                        <p className="text-muted-foreground text-[11px] leading-relaxed">
                          {entry.explanation}
                        </p>
                      )}

                      {/* Hint for incorrect */}
                      {!entry.correct && entry.hint && (
                        <div className="flex items-start gap-1 mt-1">
                          <Lightbulb className="h-3 w-3 text-highlight flex-shrink-0 mt-0.5" />
                          <p className="text-[11px] font-medium text-foreground">{entry.hint}</p>
                        </div>
                      )}

                      {entry.rule && (
                        <span className="inline-block mt-1 text-[10px] font-medium px-2 py-0.5 rounded-full bg-primary/10 text-primary">
                          {entry.rule}
                        </span>
                      )}
                    </div>
                  </div>
                </motion.div>
              ))}
            </AnimatePresence>
          </div>
        )}
      </div>
    </div>
  );
};

export default VerificationPanel;
