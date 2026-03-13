import re

# Read the file
with open(r'frontend\js\axio-app.js', 'r', encoding='utf-8') as f:
    content = f.read()

# Update 1: Fix the parsing pattern to include Proof Completion Assessment
if "Proof Completion Assessment" not in content:
    content = content.replace(
        "const headingPattern = /(Status|Input Summary|Justification|What(?:'|')s Wrong \/ Missing|Improvement|Hint|Next Step Guide)\\s*:?[ \\t]*\\n?/gi;",
        "const headingPattern = /(Status|Input Summary|Justification|What(?:'|')s Wrong \/ Missing|Improvement|Proof Completion Assessment|Hint|Next Step Guide|Next Step Guidance)\\s*:?[ \\t]*\\n?/gi;"
    )

# Update 2: Add proofCompletion field parsing
if "proofCompletion:" not in content:
    content = content.replace(
        "improvement: sections['improvement'] || '',\n        hint: sections['hint'] || '',\n        nextStepSuggestion: sections['next step guide'] || ''",
        "improvement: sections['improvement'] || '',\n        proofCompletion: sections['proof completion assessment'] || '',\n        hint: sections['hint'] || '',\n        nextStepSuggestion: sections['next step guide'] || sections['next step guidance'] || ''"
    )

# Update 3: Fix nextStepSuggestion in combinedAiText to replace with proofCompletion when available
# Logic: If proof is complete and correct, don't show "next step"
# We'll add this to the toStructuredAiResult function

# Find the return statement in toStructuredAiResult and add proofCompletion handling
old_return = """      return {
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
      };"""

new_return = """      // Check if proof is complete and correct
      const isProofComplete = parsed.proofCompletion && /PROOF IS COMPLETE|proof.*complete/i.test(parsed.proofCompletion);
      
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
        proofCompletion: parsed.proofCompletion || '',
        isProofComplete: isProofComplete,
        hint: parsed.hint || (statusMeta.cls === 'correct' ? fallbackResult.hint : fallbackResult.hint),
        nextStepSuggestion: isProofComplete && finalCls === 'correct'
          ? 'No further steps needed - the proof is complete.'
          : (forcedMismatch
            ? (parsed.nextStepSuggestion || `Return to the theorem target and derive a line directly connected to: ${summarizeTheoremGoal(theoremText || '')}`)
            : (parsed.nextStepSuggestion || fallbackResult.nextStepSuggestion)),
        _source: 'ai',
        _rawStepText: rawStepText,
        verifiedStepNumber: fallbackResult.verifiedStepNumber || null
      };"""

if old_return in content:
    content = content.replace(old_return, new_return)

# Write back
with open(r'frontend\js\axio-app.js', 'w', encoding='utf-8') as f:
    f.write(content)

print("✓ Updated axio-app.js parsing")
