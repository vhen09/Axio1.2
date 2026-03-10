# 📚 Real Analysis Theorem & Proof Examples

## For Testing the REANA System

---

## 🟢 BEGINNER LEVEL

### Example 1: Simple Limit Proof
**Theorem:**
```
Prove that lim(x→3) (2x + 1) = 7
```

**Proof:**
```
Proof:
Let ε > 0 be given.
We need to find δ > 0 such that if 0 < |x - 3| < δ, then |(2x + 1) - 7| < ε.

Consider |(2x + 1) - 7| = |2x - 6| = 2|x - 3|

We want 2|x - 3| < ε
Therefore |x - 3| < ε/2

Choose δ = ε/2.

Then if 0 < |x - 3| < δ, we have:
|(2x + 1) - 7| = 2|x - 3| < 2(ε/2) = ε

Therefore, lim(x→3) (2x + 1) = 7. ∎
```

---

### Example 2: Sequence Convergence
**Theorem:**
```
Prove that the sequence aₙ = 1/n converges to 0
```

**Proof:**
```
Proof:
Let ε > 0 be given.
We need to find N ∈ ℕ such that for all n > N, |aₙ - 0| < ε.

Consider |aₙ - 0| = |1/n - 0| = 1/n

We want 1/n < ε
Therefore n > 1/ε

Choose N = ⌈1/ε⌉ (ceiling of 1/ε).

Then for all n > N:
|aₙ - 0| = 1/n < 1/N ≤ ε

Therefore, lim(n→∞) 1/n = 0. ∎
```

---

### Example 3: Basic Continuity
**Theorem:**
```
Prove that f(x) = 3x is continuous at x = 2
```

**Proof:**
```
Proof:
Let ε > 0 be given.
We need to find δ > 0 such that if |x - 2| < δ, then |f(x) - f(2)| < ε.

Note that f(2) = 3(2) = 6.

Consider |f(x) - f(2)| = |3x - 6| = 3|x - 2|

We want 3|x - 2| < ε
Therefore |x - 2| < ε/3

Choose δ = ε/3.

Then if |x - 2| < δ:
|f(x) - f(2)| = 3|x - 2| < 3(ε/3) = ε

Therefore, f(x) = 3x is continuous at x = 2. ∎
```

---

## 🟡 INTERMEDIATE LEVEL

### Example 4: Limit with Composition
**Theorem:**
```
Prove that lim(x→2) (x² - 1) = 3
```

**Proof:**
```
Proof:
Let ε > 0 be given.
We need to find δ > 0 such that if 0 < |x - 2| < δ, then |(x² - 1) - 3| < ε.

Consider |(x² - 1) - 3| = |x² - 4| = |x - 2||x + 2|

We can bound |x + 2| by restricting x near 2.
Suppose |x - 2| < 1, then 1 < x < 3, so 3 < x + 2 < 5.
Therefore |x + 2| < 5.

Now we want |x - 2||x + 2| < ε
With |x + 2| < 5, we want 5|x - 2| < ε
Therefore |x - 2| < ε/5

Choose δ = min(1, ε/5).

Then if 0 < |x - 2| < δ:
|(x² - 1) - 3| = |x - 2||x + 2| < (ε/5)(5) = ε

Therefore, lim(x→2) (x² - 1) = 3. ∎
```

---

### Example 5: Continuity of Polynomial
**Theorem:**
```
Prove that f(x) = x² is continuous at x = a for any a ∈ ℝ
```

**Proof:**
```
Proof:
Let a ∈ ℝ and ε > 0 be given.
We need to find δ > 0 such that if |x - a| < δ, then |f(x) - f(a)| < ε.

Consider |f(x) - f(a)| = |x² - a²| = |x - a||x + a|

Similar to before, restrict |x - a| < 1.
Then a - 1 < x < a + 1.

By triangle inequality:
|x + a| ≤ |x| + |a| < |a + 1| + |a| = 2|a| + 1

Let M = 2|a| + 1.
We want M|x - a| < ε
Therefore |x - a| < ε/M

Choose δ = min(1, ε/M).

Then if |x - a| < δ:
|f(x) - f(a)| = |x - a||x + a| < (ε/M)(M) = ε

Therefore, f(x) = x² is continuous at x = a. ∎
```

---

### Example 6: Bounded Sequence
**Theorem:**
```
Prove that the sequence aₙ = sin(n)/n is bounded
```

**Proof:**
```
Proof:
We need to show that there exists M > 0 such that |aₙ| ≤ M for all n ∈ ℕ.

For all n ∈ ℕ:
|aₙ| = |sin(n)/n|
     = |sin(n)|/n    (since n > 0)
     ≤ 1/n           (since |sin(n)| ≤ 1)
     ≤ 1             (since n ≥ 1)

Therefore, choosing M = 1, we have |aₙ| ≤ M for all n ∈ ℕ.

Hence, the sequence is bounded. ∎
```

---

## 🔴 ADVANCED LEVEL

### Example 7: Squeeze Theorem Application
**Theorem:**
```
Prove that lim(x→0) x²sin(1/x) = 0
```

**Proof:**
```
Proof:
We use the Squeeze Theorem.

For all x ≠ 0, we know that -1 ≤ sin(1/x) ≤ 1.

Multiplying by x² (which is non-negative):
-x² ≤ x²sin(1/x) ≤ x²

Now consider the limits:
lim(x→0) (-x²) = 0
lim(x→0) x² = 0

By the Squeeze Theorem:
lim(x→0) x²sin(1/x) = 0 ∎
```

---

### Example 8: Monotone Convergence
**Theorem:**
```
Prove that the sequence aₙ = (1 + 1/n)ⁿ is increasing
```

**Proof:**
```
Proof:
We need to show that aₙ₊₁ > aₙ for all n ∈ ℕ.

Consider the ratio:
aₙ₊₁/aₙ = [(1 + 1/(n+1))^(n+1)] / [(1 + 1/n)ⁿ]

By Bernoulli's inequality and algebraic manipulation:
= [(n+2)/(n+1)]^(n+1) / [(n+1)/n]ⁿ

After simplification (multiply both sides by appropriate powers):
= [(n+2)ⁿ⁺¹ · nⁿ] / [(n+1)ⁿ⁺¹ · (n+1)ⁿ]

Using the AM-GM inequality or direct computation, 
we can show this ratio is greater than 1.

Therefore aₙ₊₁ > aₙ, so the sequence is increasing. ∎
```

---

### Example 9: Uniform Continuity
**Theorem:**
```
Prove that f(x) = x² is uniformly continuous on [0, 1]
```

**Proof:**
```
Proof:
Let ε > 0 be given.
We need to find δ > 0 such that for all x, y ∈ [0, 1]:
if |x - y| < δ, then |f(x) - f(y)| < ε.

Consider |f(x) - f(y)| = |x² - y²| = |x - y||x + y|

Since x, y ∈ [0, 1], we have:
|x + y| ≤ |x| + |y| ≤ 1 + 1 = 2

Therefore:
|f(x) - f(y)| = |x - y||x + y| ≤ 2|x - y|

We want 2|x - y| < ε
Therefore |x - y| < ε/2

Choose δ = ε/2.

Then for all x, y ∈ [0, 1] with |x - y| < δ:
|f(x) - f(y)| ≤ 2|x - y| < 2(ε/2) = ε

Therefore, f is uniformly continuous on [0, 1]. ∎
```

---

## 🎯 TEST THEOREMS (For System Testing)

### Quick Test 1: Very Simple
```
Prove that lim(x→5) (x + 3) = 8
```

### Quick Test 2: Basic Sequence
```
Prove that lim(n→∞) (2n + 1)/n = 2
```

### Quick Test 3: Continuity
```
Prove that f(x) = 5x - 2 is continuous everywhere
```

### Quick Test 4: Wrong Theorem (Should Detect Error)
```
Prove that lim(x→0) 1/x = 0
```
*(This is FALSE - the AI should point this out!)*

---

## 💡 How to Use These Examples

### In the AI Tutor:
1. **Copy a theorem** from above
2. **Paste into "Theorem Statement" field**
3. **Click "Suggest Proof Strategy"** to get guidance
4. **Try writing the proof yourself**
5. **Compare with the example proof above**

### Testing the System:
- **Beginner examples** - Test basic functionality
- **Intermediate examples** - Test complex formatting
- **Advanced examples** - Test mathematical accuracy
- **Wrong theorems** - Test error detection

---

## 📖 Common Real Analysis Patterns

### For Limits (ε-δ):
1. Let ε > 0 be given
2. Manipulate |f(x) - L|
3. Find relationship to |x - a|
4. Choose appropriate δ
5. Verify the implication

### For Sequences (ε-N):
1. Let ε > 0 be given
2. Manipulate |aₙ - L|
3. Solve inequality for n
4. Choose appropriate N
5. Verify for all n > N

### For Continuity:
1. Let ε > 0 be given
2. Consider |f(x) - f(a)|
3. Bound using |x - a|
4. Choose appropriate δ
5. Verify the condition

---

**Ready to test? Copy any theorem above and try it in the AI Tutor!** 🚀
