/**
 * AXIO Multi-Domain Proof System - Usage Examples
 * 
 * This file demonstrates all the new features and integration patterns
 * for the unified equation component and general mathematics support.
 */

// ============================================================================
// EXAMPLE 1: Initialize Unified Equation Component
// ============================================================================

/**
 * Initialize the equation popover for theorem statement
 * This creates a consistent, inline equation editor just like step buttons
 */
function initializeTheoremEquationComponent() {
  const theoremInput = document.getElementById('theorem-input');
  const btnKeyboard = document.getElementById('btn-keyboard');
  
  if (!theoremInput || !btnKeyboard) {
    console.error('Required elements not found');
    return;
  }

  new EquationComponent({
    targetTextarea: theoremInput,
    buttonElement: btnKeyboard,
    onInsert: (latexValue) => {
      console.log('Symbol inserted:', latexValue);
      // Optional: trigger custom behavior
      updateTheoremPreview(theoremInput.value);
    }
  });
}

// ============================================================================
// EXAMPLE 2: Clean LaTeX Copying from Symbol Library
// ============================================================================

/**
 * Copy LaTeX code cleanly to clipboard
 * This ensures only raw LaTeX is copied, no HTML or formatting
 */
async function copySymbolExample() {
  const latexCode = '\\alpha'; // Raw LaTeX, not α
  const feedbackElement = document.querySelector('.copy-status');
  
  // Method 1: Using built-in utility
  copyLatexToClipboard(latexCode, feedbackElement);
}

/**
 * Example of inserting copied LaTeX into workspace
 */
function pasteLatexIntoWorkspace() {
  // User copies \alpha from library
  // User pastes into theorem input
  const theoremInput = document.getElementById('theorem-input');
  const rawLatex = '\\alpha'; // Clipboard contains this
  
  theoremInput.value += rawLatex;
  theoremInput.dispatchEvent(new Event('input'));
  
  // Result: Rendered as α (not as typed alpha or HTML)
  // Verification: Paste \int as \int, not as rendered ∫
}

// ============================================================================
// EXAMPLE 3: Detect Mathematical Domain
// ============================================================================

/**
 * Automatically detect which math domain a statement belongs to
 */
async function detectDomainExample() {
  const theorems = [
    "Prove that the limit of sin(x)/x as x approaches 0 is 1", // calculus
    "Prove that the sum of the first n natural numbers is n(n+1)/2", // discrete_math
    "Prove that the derivative of x² is 2x", // calculus
    "If A is a symmetric matrix, then all eigenvalues are real", // linear_algebra
    "The sum of angles in a triangle is 180 degrees", // geometry
    "Prove that √2 is irrational", // real_analysis
  ];

  for (const theorem of theorems) {
    const response = await fetch('/backend/api/gm-proof.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'detect_domain',
        statement: theorem
      })
    });

    const result = await response.json();
    console.log(`Theorem: "${theorem}"`);
    console.log(`Domain: ${result.domain}\n`);
  }
}

// ============================================================================
// EXAMPLE 4: Detect Proof Type
// ============================================================================

/**
 * Automatically detect which proof type is being used
 */
async function detectProofTypeExample() {
  const proofs = [
    "Assume x > 0. Then x² = x · x > 0 · 0 = 0. So x² > 0.", // direct
    "Assume √2 is rational. Then √2 = p/q for integers p,q. But 2q² = p², contradiction.", // contradiction
    "Base case: 1 = 1(2)/2 ✓. Inductive step: if true for n, then (n+1)(n+2)/2 = n(n+1)/2 + (n+1) ✓", // induction
    "Case 1: if x > 0, ... Case 2: if x < 0, ...", // cases
  ];

  for (const proof of proofs) {
    const response = await fetch('/backend/api/gm-proof.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'detect_proof_type',
        proof: proof
      })
    });

    const result = await response.json();
    console.log(`Proof Type: ${result.proofType}`);
  }
}

// ============================================================================
// EXAMPLE 5: Get Domain-Specific Guidance
// ============================================================================

/**
 * Fetch domain-specific tips and concepts
 */
async function getDomainGuidanceExample() {
  const response = await fetch('/backend/api/gm-proof.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'get_domain_guidance',
      theorem: 'Prove that the function f(x) = sin(x) is continuous',
      domain: 'real_analysis'
    })
  });

  const result = await response.json();
  console.log('Domain:', result.guidance.domain);
  console.log('Key Concepts:', result.guidance.concepts);
  console.log('Tips:');
  result.guidance.tips.forEach(tip => console.log(`  • ${tip}`));
  console.log('Notation:', result.guidance.notation);

  /* Output example:
   * Domain: Real Analysis
   * Key Concepts: [ 'Limits', 'Continuity', 'Differentiability', ... ]
   * Tips:
   *   • Always use ε-δ notation for limits
   *   • Show all inequality manipulations step-by-step
   *   • Apply Triangle Inequality explicitly when needed
   * Notation: [ 'ε', 'δ', '∀', '∃', 'lim', ... ]
   */
}

// ============================================================================
// EXAMPLE 6: Get Proof Type Recommendations
// ============================================================================

/**
 * Get recommendations on which proof type to use
 */
async function getProofTypeRecommendationsExample() {
  const theorem = 'Prove by induction that 1² + 2² + ... + n² = n(n+1)(2n+1)/6';
  
  const response = await fetch('/backend/api/gm-proof.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'get_proof_type_recommendations',
      statement: theorem
    })
  });

  const result = await response.json();
  
  console.log('Recommended Proof Types:');
  result.recommendations.forEach((rec, index) => {
    console.log(`  ${index + 1}. ${rec.type} - ${rec.reason}`);
  });

  /* Output example:
   * Recommended Proof Types:
   *   1. induction - Statement appears to be about all integers
   *   2. direct - Direct proof is a good starting point
   */
}

// ============================================================================
// EXAMPLE 7: Validate Proof Structure
// ============================================================================

/**
 * Validate that a proof has the correct structure for its type
 */
async function validateProofStructureExample() {
  const proofType = 'induction';
  const proof = `
    Base case: For n=1, we have 1² = 1 = 1(2)(3)/6. ✓
    
    Inductive step: Assume true for n.
    Show: 1² + ... + n² + (n+1)² = (n+1)(n+2)(2n+3)/6
    
    By inductive hypothesis: 1² + ... + n² = n(n+1)(2n+1)/6
    Adding (n+1)²: = n(n+1)(2n+1)/6 + (n+1)²
                  = (n+1)[n(2n+1)/6 + (n+1)]
                  = (n+1)[(n(2n+1) + 6(n+1))/6]
                  = (n+1)[(2n² + 7n + 6)/6]
                  = (n+1)[(n+2)(2n+3)/6]
                  = (n+1)(n+2)(2n+3)/6 ✓
  `;

  const response = await fetch('/backend/api/gm-proof.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'validate_proof_structure',
      proofType: proofType,
      proof: proof
    })
  });

  const result = await response.json();
  
  console.log('Proof Type:', result.validation.proofType);
  console.log('Valid Structure:', result.validation.isValid);
  if (!result.validation.isValid) {
    console.log('Issues Found:');
    result.validation.issues.forEach(issue => console.log(`  • ${issue}`));
  }
}

// ============================================================================
// EXAMPLE 8: Full Proof Analysis
// ============================================================================

/**
 * Get complete analysis of a proof (all features in one call)
 */
async function getFullProofAnalysisExample() {
  const theorem = 'Prove that if x is a real number and x > 0, then x² > 0';
  const proof = `
    Assume x is a real number with x > 0.
    By definition, x² = x · x.
    Since x > 0 and 0 > 0 is false, we have x · x > 0 · anything.
    Therefore x² > 0.
  `;

  const response = await fetch('/backend/api/gm-proof.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'get_full_analysis',
      theorem: theorem,
      proof: proof
    })
  });

  const result = await response.json();
  const analysis = result.analysis;

  console.log('╔════════════════════════════════════════╗');
  console.log('    COMPLETE PROOF ANALYSIS');
  console.log('╚════════════════════════════════════════╝');
  
  console.log(`\nDomain: ${analysis.detectedDomain}`);
  console.log(`Proof Type: ${analysis.detectedProofType}`);
  
  console.log(`\nDomain Guidance:`);
  console.log(`  • Domain: ${analysis.domainGuidance.domain}`);
  console.log(`  • Concepts: ${analysis.domainGuidance.concepts.join(', ')}`);
  console.log(`  • Tips:`);
  analysis.domainGuidance.tips.forEach(tip => {
    console.log(`    - ${tip}`);
  });
  
  console.log(`\nProof Validation:`);
  console.log(`  • Structure Valid: ${analysis.proofValidation.isValid}`);
  if (!analysis.proofValidation.isValid) {
    analysis.proofValidation.issues.forEach(issue => {
      console.log(`    ✗ ${issue}`);
    });
  }
  
  console.log(`\nRecommended Proof Types:`);
  analysis.typeRecommendations.forEach((rec, i) => {
    console.log(`  ${i + 1}. ${rec.type}: ${rec.reason}`);
  });
}

// ============================================================================
// EXAMPLE 9: Integration with Dashboard
// ============================================================================

/**
 * Integrate full analysis into the dashboard workflow
 */
async function dashboardIntegrationExample() {
  // 1. User enters theorem statement
  const theoremInput = document.getElementById('theorem-input');
  const theorem = theoremInput.value;

  // 2. User writes proof steps
  const steps = gatherAllSteps(); // From existing axio-app.js

  // 3. When user clicks "Verify All"
  const response = await fetch('/backend/api/gm-proof.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'get_full_analysis',
      theorem: theorem,
      proof: steps.map(s => s.text).join('\n')
    })
  });

  const result = await response.json();
  
  // 4. Display guidance to student
  displayDomainGuidance(result.analysis.domainGuidance);
  displayProofTypeRecommendation(result.analysis.typeRecommendations[0]);
  highlightStructuralIssues(result.analysis.proofValidation.issues);
}

// ============================================================================
// EXAMPLE 10: Multi-domain Proof Support
// ============================================================================

/**
 * Support for proving theorems in different domains
 * Example: Proving the same statement algebraically, geometrically, etc.
 */
async function multidomainProofExample() {
  const statement = 'Prove that (a+b)(a-b) = a² - b²';
  
  // Proof 1: Algebraic
  const algebraicProof = 'Expand: (a+b)(a-b) = a·a + a·(-b) + b·a + b·(-b) = a² - ab + ab - b² = a² - b²';
  
  // Proof 2: Geometric (difference of squares)
  const geometricProof = 'Consider a square of side a. Remove a square of side b from the corner. The remaining area is a² - b², which can be rearranged as two rectangles of dimensions (a+b) × (a-b).';

  console.log('=== Algebraic Proof ===');
  await analyzeProof(statement, algebraicProof);

  console.log('\n=== Geometric Proof ===');
  await analyzeProof(statement, geometricProof);
}

async function analyzeProof(statement, proof) {
  const response = await fetch('/backend/api/gm-proof.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'get_full_analysis',
      theorem: statement,
      proof: proof
    })
  });

  const result = await response.json();
  console.log(`Domain: ${result.analysis.detectedDomain}`);
  console.log(`Type: ${result.analysis.detectedProofType}`);
}

// ============================================================================
// EXAMPLE 11: Step-by-step Equation Component Usage
// ============================================================================

/**
 * How equations flow through the system
 */
async function equationWorkflowExample() {
  // Step 1: Symbol Library
  console.log('1. User copies \\int from symbols library');
  const copiedLatex = '\\int'; // Clipboard contains this
  
  // Step 2: Paste into theorem
  console.log('2. Paste into theorem statement');
  const theoremInput = document.getElementById('theorem-input');
  theoremInput.value = 'We compute ' + copiedLatex;
  
  // Step 3: Equation button for more symbols
  console.log('3. Click equation button to add bounds');
  // User sees popover, clicks Integrals category
  // Selects ∫ₐᵇ → inserts \\int_{a}^{b}
  
  /* Result:
   * Theorem: "We compute \int_{a}^{b}"
   * Rendered: "We compute ∫ₐᵇ"
   */
}

// ============================================================================
// EXAMPLE 12: Error Handling & Fallbacks
// ============================================================================

/**
 * Graceful degradation when services unavailable
 */
async function errorHandlingExample() {
  try {
    const response = await fetch('/backend/api/gm-proof.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'get_full_analysis',
        theorem: 'Prove something',
        proof: 'Proof here'
      })
    });

    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status}`);
    }

    const result = await response.json();
    
    if (!result.success) {
      console.error('API Error:', result.error);
      // Fallback: Show basic guidance without analysis
      showBasicGuidance();
      return;
    }

    // Success: Show full analysis
    showFullAnalysis(result.analysis);

  } catch (error) {
    console.error('Network or parsing error:', error);
    // Fallback: Use localStorage cached guidance
    showCachedGuidance();
  }
}

// ============================================================================
// Run Examples
// ============================================================================

/**
 * Uncomment to run examples in browser console
 */

// detectDomainExample();
// detectProofTypeExample();
// getDomainGuidanceExample();
// getProofTypeRecommendationsExample();
// validateProofStructureExample();
// getFullProofAnalysisExample();
