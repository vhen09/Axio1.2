# REANA User Guide
## Real Analysis Education with AI

### 📖 Table of Contents
1. [Getting Started](#getting-started)
2. [Using the AI Tutor](#using-the-ai-tutor)
3. [Keyboard Shortcuts](#keyboard-shortcuts)
4. [Tips for Success](#tips-for-success)
5. [Troubleshooting](#troubleshooting)

---

## 🚀 Getting Started

### What is REANA?
REANA (Real Analysis Education with AI) is an intelligent tutoring system that helps you master mathematical proofs through:
- **AI-powered guidance** from DeepSeek AI
- **Step-by-step proof construction**
- **Instant verification and feedback**
- **29 curated Real Analysis theorems**

### First Steps
1. **Browse Theorems**: Start at the [Theorem Library](frontend/pages/theorems.html) to explore available theorems
2. **Open AI Tutor**: Click the 🤖 AI Proof Assistant in the sidebar
3. **Enter a Theorem**: Type or paste a theorem you want to prove
4. **Get Strategy**: Click "💡 Suggest Proof Strategy" for AI guidance

---

## 🤖 Using the AI Tutor

### Workflow
```
1. Enter Theorem → 2. Get Strategy → 3. Write Proof → 4. Verify Steps
```

### Features

#### 1. **Proof Strategy**
- Click "💡 Suggest Proof Strategy"
- AI analyzes your theorem and suggests:
  - Best proof technique (direct, contradiction, induction, ε-δ)
  - Key definitions needed
  - Step-by-step outline
  - Common pitfalls to avoid

#### 2. **Step-by-Step Guidance**
- Click "→ Guide Next Step"
- AI provides:
  - What to write next
  - Why this step is needed
  - Justification and references
  - Detailed work showing

#### 3. **Proof Verification**
- Write your proof in the "Step" boxes
- Click "✓ Verify" on each step
- AI checks:
  - Logical correctness
  - Mathematical rigor
  - Completeness
  - Provides constructive feedback

#### 4. **Get Hints**
- Stuck? Click "❓ I'm Stuck - Get Hint"
- AI asks guiding questions
- Suggests things to consider
- Helps you discover the solution

---

## ⌨️ Keyboard Shortcuts

### Mathematical Symbols
Press `Alt + key` to insert symbols:

#### Greek Letters
- `Alt+a` = α (alpha)
- `Alt+b` = β (beta)
- `Alt+g` = γ (gamma)
- `Alt+d` = δ (delta)
- `Alt+e` = ε (epsilon)
- `Alt+t` = θ (theta)
- `Alt+l` = λ (lambda)
- `Alt+m` = μ (mu)
- `Alt+p` = π (pi)
- `Alt+s` = σ (sigma)

#### Logic Operators
- `Alt+Shift+A` = ∀ (for all)
- `Alt+Shift+E` = ∃ (exists)
- `Alt+i` = ∈ (element of)
- `Alt+Shift+I` = ∉ (not element of)
- `Alt+r` = → (arrow)
- `Alt+Shift+R` = ⇒ (implies)

#### Math Symbols
- `Alt+8` = ∞ (infinity)
- `Alt+,` = ≤ (less than or equal)
- `Alt+.` = ≥ (greater than or equal)
- `Alt+/` = ≠ (not equal)
- `Alt+q` = √ (square root)
- `Alt+w` = ∑ (summation)

#### Number Sets
- `Alt+Shift+N` = ℕ (natural numbers)
- `Alt+Shift+Z` = ℤ (integers)
- `Alt+Shift+Q` = ℚ (rationals)
- `Alt+Shift+R` = ℝ (reals)
- `Alt+0` = ∅ (empty set)

**Pro Tip**: Hover over any symbol button to see its keyboard shortcut!

---

## 💡 Tips for Success

### 1. **Be Specific**
- State your theorem clearly and completely
- Include all conditions and assumptions
- Use proper mathematical notation

### 2. **Start Simple**
- Begin with easier theorems to learn the system
- Example: "Prove that lim(x→2) (3x+1) = 7"
- Build up to complex proofs

### 3. **Use the AI Strategically**
- Ask for strategy BEFORE writing your proof
- Use step-by-step guidance when unsure
- Verify each step as you go
- Request hints instead of full solutions

### 4. **Practice Mathematical Writing**
- Use proper notation: ∀, ∃, ∈, ε, δ
- State "Given" and "To Prove" clearly
- Justify each step
- Write "Therefore" before conclusions

### 5. **Learn from Feedback**
- Read AI feedback carefully
- Understand WHY a step is correct/incorrect
- Apply lessons to future proofs

### 6. **Auto-Save Feature**
- Your work saves automatically
- Safe to refresh or close tab
- Resume anytime without losing progress

---

## 🔧 Troubleshooting

### AI Response is Taking Too Long
- **Wait Time**: Detailed proofs take 20-60 seconds
- **Complex Theorems**: Break into simpler parts
- **Network**: Check internet connection
- **Timeout**: If >60 seconds, try refreshing

### Symbols Not Inserting
- **Click First**: Click in a text area before using shortcuts
- **Alt Key**: Make sure you're pressing Alt, not Ctrl
- **Buttons Work**: You can always click symbol buttons

### Work Not Saving
- **Auto-Save**: Saves happen automatically on typing
- **Browser**: Ensure localStorage is enabled
- **Private Mode**: May not save in incognito

### AI Responses Unclear
- **Be Specific**: Provide more context in your theorem
- **Rephrase**: Try stating the theorem differently
- **Ask Follow-up**: Use "Get Hint" for clarification

### Mathematical Symbols Not Displaying
- **Font Support**: Your browser needs Unicode support
- **Update Browser**: Use latest version of Chrome/Firefox/Edge
- **Should See**: α, β, ∀, ∃, ∈, ε, δ, →, ⇒, ≤, ≥, ∞

---

## 📚 Example Theorems to Try

### Beginner (🟢)
1. "Prove that lim(x→3) (2x+1) = 7"
2. "Prove that the sum of two even numbers is even"
3. "Prove that if x² = 4, then x = 2 or x = -2"

### Intermediate (🟡)
1. "Prove that lim(x→0) (sin x)/x = 1"
2. "Prove that every convergent sequence is bounded"
3. "Prove the squeeze theorem for sequences"

### Advanced (🔴)
1. "Prove that every Cauchy sequence in ℝ converges"
2. "Prove the Bolzano-Weierstrass theorem"
3. "Prove that a continuous function on [a,b] is uniformly continuous"

---

## 🎓 For Thesis Defense

### Key Features to Demonstrate
1. **AI Integration**: Show DeepSeek API generating detailed proofs
2. **Symbol Input**: Demonstrate keyboard shortcuts (Alt+key)
3. **Auto-Save**: Refresh page to show persistence
4. **Validation**: Show proof step verification with feedback
5. **User Experience**: Navigate between pages smoothly

### Talking Points
- "REANA uses state-of-the-art DeepSeek AI for Real Analysis education"
- "Students can practice proofs with instant, detailed feedback"
- "29 carefully selected theorems covering core topics"
- "Keyboard shortcuts enable efficient mathematical symbol entry"
- "Auto-save prevents work loss, improving user experience"
- "System promotes active learning over passive consumption"

### Common Questions
**Q: Why Real Analysis?**
A: It's fundamental for advanced mathematics, requires rigorous proofs, and students often struggle with the abstraction.

**Q: How does AI help?**
A: Provides personalized guidance, identifies errors, suggests strategies, and gives hints without solving everything.

**Q: What makes this different?**
A: Combines modern AI with pedagogical best practices - helps students LEARN, not just get answers.

---

## 📞 Support

For issues or questions:
- Check this guide first
- Review the [System Documentation](SYSTEM_STATUS_REPORT.md)
- Test with example theorems
- Verify API configuration in `backend/config/deepseek.php`

---

**Good luck with your mathematical journey!** 🎓✨
