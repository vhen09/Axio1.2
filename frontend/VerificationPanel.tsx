import { useAppState, VerificationEntry } from "@/context/AppContext";
import { motion, AnimatePresence } from "framer-motion";
import { CheckCircle2, AlertTriangle, Clock, ShieldCheck, Lightbulb, TrendingUp, BookOpen, AlertCircle } from "lucide-react";
import { useState } from "react";

const VerificationPanel = () => {
  const { verificationHistory, steps, canCompleteProof } = useAppState();
  const [expandedFeedback, setExpandedFeedback] = useState<number | null>(null);

  const activelyVerifying = steps.some((s) => s.verifying);
  const latestEntries = verificationHistory.slice(-10).reverse();
  
  // Calculate statistics
  const verifiedCount = steps.filter((s) => s.verified === true).length;
  const totalSteps = steps.length;
  const accuracy = totalSteps > 0 ? Math.round((verifiedCount / totalSteps) * 100) : 0;
  
  // Get incorrect entries for improvement analysis
  const incorrectEntries = verificationHistory.filter(e => !e.correct);
  const errorTypes = incorrectEntries.reduce((acc, entry) => {
    const type = entry.errorType || 'other';
    acc[type] = (acc[type] || 0) + 1;
    return acc;
  }, {} as Record<string, number>);

  const getErrorTypeLabel = (type?: string) => {
    switch(type) {
      case 'logic': return 'Logic Error';
      case 'syntax': return 'Syntax Error';
      case 'definition': return 'Definition Error';
      case 'notation': return 'Notation Error';
      default: return 'Other Error';
    }
  };

  const getErrorTypeColor = (type?: string) => {
    switch(type) {
      case 'logic': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
      case 'syntax': return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
      case 'definition': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
      case 'notation': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
      default: return 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400';
    }
  };

  return (
    <div className="glass-card p-5 space-y-4 sticky top-20">
      <h3 className="font-semibold text-primary text-lg flex items-center gap-2">
        <ShieldCheck className="h-5 w-5" />
        Verification Panel
      </h3>

      {/* Progress Section - Enhanced */}
      <div className="space-y-2">
        <div className="flex justify-between items-center">
          <span className="text-sm font-medium text-foreground">Progress</span>
          <span className="text-sm font-bold text-accent">{accuracy}%</span>
        </div>
        <div className="w-full h-2 bg-muted rounded-full overflow-hidden">
          <motion.div
            initial={{ width: 0 }}
            animate={{ width: `${accuracy}%` }}
            transition={{ duration: 0.5 }}
            className="h-full bg-gradient-to-r from-accent to-accent/80"
          />
        </div>
        <div className="flex justify-between text-[11px] text-muted-foreground">
          <span>{verifiedCount} verified</span>
          <span>{totalSteps} total</span>
        </div>
      </div>

      {/* Status summary - Enhanced */}
      <div className="grid grid-cols-2 gap-3">
        <div className="rounded-lg bg-accent/10 p-3 text-center border border-accent/20">
          <p className="text-2xl font-bold text-accent">
            {verifiedCount}
          </p>
          <p className="text-xs text-muted-foreground">Verified</p>
        </div>
        <div className="rounded-lg bg-muted/50 p-3 text-center">
          <p className="text-2xl font-bold text-foreground">
            {totalSteps}
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
            {totalSteps - verifiedCount} step{totalSteps - verifiedCount !== 1 ? 's' : ''} remaining
          </>
        )}
      </div>

      {/* Error Summary - New */}
      {incorrectEntries.length > 0 && (
        <div className="rounded-lg bg-highlight/8 p-4 border border-highlight/25 space-y-3">
          <div className="flex items-center gap-2">
            <TrendingUp className="h-5 w-5 text-highlight" />
            <p className="text-sm font-bold text-foreground">⚠️ Areas to Improve</p>
          </div>
          <div className="flex flex-wrap gap-2">
            {Object.entries(errorTypes).map(([type, count]) => (
              <span key={type} className={`text-xs font-bold px-3 py-1.5 rounded-full ${getErrorTypeColor(type)}`}>
                {getErrorTypeLabel(type)}: {count}
              </span>
            ))}
          </div>
        </div>
      )}

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

      {/* History - Enhanced */}
      <div className="space-y-2">
        <p className="text-xs font-medium text-muted-foreground uppercase tracking-wider">Detailed Feedback</p>
        {latestEntries.length === 0 ? (
          <p className="text-sm text-muted-foreground italic">No verifications yet.</p>
        ) : (
          <div className="space-y-2 max-h-[500px] overflow-y-auto">
            <AnimatePresence>
              {latestEntries.map((entry, i) => {
                const isExpanded = expandedFeedback === entry.timestamp;
                return (
                  <motion.div
                    key={entry.timestamp}
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.05 }}
                    className={`rounded-lg px-4 py-3 border cursor-pointer transition-all ${
                      entry.correct
                        ? "bg-accent/5 border-accent/20 hover:border-accent/40 hover:bg-accent/8"
                        : "bg-highlight/5 border-highlight/30 hover:border-highlight/50 hover:bg-highlight/8"
                    } ${isExpanded ? 'ring-2 ring-offset-2 ring-offset-background' + (entry.correct ? ' ring-accent/30' : ' ring-highlight/30') : ''}`}
                    onClick={() => setExpandedFeedback(isExpanded ? null : entry.timestamp)}
                  >
                    <div className="flex items-start gap-3">
                      {entry.correct ? (
                        <CheckCircle2 className="h-5 w-5 text-accent flex-shrink-0 mt-0" />
                      ) : (
                        <AlertTriangle className="h-5 w-5 text-highlight flex-shrink-0 mt-0" />
                      )}
                      <div className="min-w-0 flex-1 space-y-1.5">
                        <div className="flex items-center justify-between gap-2 flex-wrap">
                          <p className="font-semibold text-foreground text-sm">Step {entry.stepIndex}</p>
                          {entry.errorType && !entry.correct && (
                            <span className={`text-xs font-semibold px-2.5 py-1 rounded-md ${getErrorTypeColor(entry.errorType)}`}>
                              {getErrorTypeLabel(entry.errorType)}
                            </span>
                          )}
                        </div>
                        <p className="text-foreground text-sm">{entry.message}</p>
                        {!isExpanded && (entry.explanation || entry.hint || entry.improvement || entry.nextStep) && (
                          <p className="text-xs text-muted-foreground italic">Click to see details →</p>
                        )}
                      </div>
                    </div>

                        {/* Expandable detailed feedback */}
                        <AnimatePresence>
                          {isExpanded && (
                            <motion.div
                              initial={{ opacity: 0, height: 0 }}
                              animate={{ opacity: 1, height: 'auto' }}
                              exit={{ opacity: 0, height: 0 }}
                              transition={{ duration: 0.2 }}
                              className="space-y-3 pt-3 mt-3 border-t border-border"
                            >
                              {/* Explanation */}
                              {!entry.correct && entry.explanation && (
                                <div className="space-y-1.5 bg-muted/40 rounded p-3">
                                  <p className="text-xs font-bold text-foreground">❓ Why is this incorrect?</p>
                                  <p className="text-sm text-foreground leading-relaxed">
                                    {entry.explanation}
                                  </p>
                                </div>
                              )}

                              {/* Hint */}
                              {!entry.correct && entry.hint && (
                                <div className="flex gap-3 rounded-lg bg-highlight/15 border border-highlight/30 p-3">
                                  <Lightbulb className="h-5 w-5 text-highlight flex-shrink-0 mt-0" />
                                  <div className="space-y-1.5 flex-1">
                                    <p className="text-xs font-bold text-foreground">💡 Hint</p>
                                    <p className="text-sm text-foreground">{entry.hint}</p>
                                  </div>
                                </div>
                              )}

                              {/* Improvement Suggestion */}
                              {!entry.correct && entry.improvement && (
                                <div className="flex gap-3 rounded-lg bg-primary/15 border border-primary/30 p-3">
                                  <BookOpen className="h-5 w-5 text-primary flex-shrink-0 mt-0" />
                                  <div className="space-y-1.5 flex-1">
                                    <p className="text-xs font-bold text-foreground">📚 Try This Approach</p>
                                    <p className="text-sm text-foreground">{entry.improvement}</p>
                                  </div>
                                </div>
                              )}

                              {/* Next Step */}
                              {!entry.correct && entry.nextStep && (
                                <div className="flex gap-3 rounded-lg bg-accent/15 border border-accent/30 p-3">
                                  <AlertCircle className="h-5 w-5 text-accent flex-shrink-0 mt-0" />
                                  <div className="space-y-1.5 flex-1">
                                    <p className="text-xs font-bold text-foreground">→ Next Action</p>
                                    <p className="text-sm text-foreground">{entry.nextStep}</p>
                                  </div>
                                </div>
                              )}

                              {/* Rule Reference */}
                              {entry.rule && (
                                <div className="pt-2">
                                  <span className="inline-block text-xs font-bold px-3 py-1.5 rounded-lg bg-primary/15 border border-primary/30 text-primary">
                                    📋 Rule: {entry.rule}
                                  </span>
                                </div>
                              )}
                            </motion.div>
                          )}
                        </AnimatePresence>
                      </div>
                    </div>
                  </motion.div>
                );
              })}
            </AnimatePresence>
          </div>
        )}
      </div>

      {/* Learning Resources - New */}
      {incorrectEntries.length > 0 && (
        <div className="rounded-lg bg-blue-50 dark:bg-blue-950/20 p-4 border border-blue-200 dark:border-blue-800 space-y-3">
          <div className="flex items-center gap-3">
            <BookOpen className="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
            <p className="text-sm font-bold text-blue-900 dark:text-blue-200">📖 Learning Tip</p>
          </div>
          <p className="text-sm text-blue-800 dark:text-blue-300 leading-relaxed pl-8">
            Review the concepts behind your errors. Focus on understanding the rules and definitions before attempting more steps.
          </p>
        </div>
      )}
    </div>
  );
};

export default VerificationPanel;
