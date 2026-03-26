# 🧪 AI Testing Guide - Paano I-verify ang DeepSeek AI

## 📋 Step-by-Step Testing Process

### ✅ STEP 1: Check API Connection
```bash
# Test kung connected ang API
curl -X POST https://api.deepseek.com/v1/chat/completions ^
-H "Content-Type: application/json" ^
-H "Authorization: Bearer sk-72e7818157464142a3430d562d3db41d" ^
-d "{\"model\":\"deepseek-chat\",\"messages\":[{\"role\":\"user\",\"content\":\"Say 'API is working'\"}],\"max_tokens\":10}"
```

**Expected Result:**
- Kung gumagana: Makikita mo "API is working" sa response
- Kung may problema: May error message

---

### ✅ STEP 2: Test Tutor API Directly

```bash
# Go to project directory
cd c:\Project\axio2.0\lean4-ai-web-app

# Create test request
echo {"action":"get_assistance","theorem":"Prove that the limit of x^2 as x approaches 2 is 4","currentProof":"","context":{}} > ai_test.json

# Test the API
curl -X POST http://localhost:8080/backend/api/tutor.php ^
-H "Content-Type: application/json" ^
-d @ai_test.json
```

**What to Look For:**
✅ **Real AI Response** - Long, detailed, personalized explanation about the specific theorem
❌ **Demo Mode** - Generic template response with "Step 1, Step 2, Step 3"

---

### ✅ STEP 3: Test via Browser

1. **Open AI Tutor Page**
   - Go to: `http://localhost:8080/frontend/pages/tutor.html`

2. **Enter Test Theorem:**
   ```
   Prove that if f is continuous at x=a, then for every ε > 0, 
   there exists δ > 0 such that |x-a| < δ implies |f(x)-f(a)| < ε
   ```

3. **Click "Get Assistance"**

4. **Check Response Time:**
   - **Real AI**: 3-10 seconds (connecting to DeepSeek)
   - **Demo Mode**: Instant (< 1 second)

---

## 🔍 How to Verify AI Accuracy

### Test Case 1: Simple Limit Proof
**Theorem:** "Prove that lim(x→3) (2x+1) = 7"

**Real AI Should:**
- ✅ Use epsilon-delta definition specifically
- ✅ Show algebraic manipulation: |2x+1-7| = |2x-6| = 2|x-3|
- ✅ Choose δ = ε/2 explicitly
- ✅ Verify: if |x-3| < δ, then |2x+1-7| < ε
- ✅ Personalized to THIS theorem (not generic)

**Demo Mode Shows:**
- ❌ Generic "Step 1: Understand, Step 2: Strategy"
- ❌ No specific calculations
- ❌ Template response

---

### Test Case 2: Continuity Proof
**Theorem:** "Prove that f(x) = x² is continuous at x=2"

**Real AI Should:**
- ✅ State continuity definition at a point
- ✅ Show lim(x→2) x² = 4
- ✅ Use specific delta: δ = min(1, ε/5)
- ✅ Explain the triangle inequality application
- ✅ Work through the specific algebra

**Demo Mode Shows:**
- ❌ Generic proof techniques list
- ❌ No specific to x² or x=2
- ❌ Template format

---

### Test Case 3: Sequence Convergence
**Theorem:** "Prove that the sequence aₙ = 1/n converges to 0"

**Real AI Should:**
- ✅ State convergence definition
- ✅ Given ε > 0, find N specifically
- ✅ Show N = ⌈1/ε⌉ works
- ✅ Verify: for n > N, |1/n - 0| < ε
- ✅ Complete the proof with QED

**Demo Mode Shows:**
- ❌ Generic sequence tips
- ❌ No specific N calculation
- ❌ Template response

---

## 🎯 Accuracy Verification Checklist

### ✅ Real AI Indicators:
- [ ] Response takes 3-10 seconds
- [ ] Specific to YOUR theorem (uses your variables/numbers)
- [ ] Shows actual mathematical calculations
- [ ] Provides epsilon-delta or formal definitions
- [ ] Personalized explanations
- [ ] Cites specific theorems by name
- [ ] Different response each time you ask

### ❌ Demo Mode Indicators:
- [ ] Instant response (< 1 second)
- [ ] Generic "Step 1, Step 2" format
- [ ] No specific calculations
- [ ] Same response for different theorems
- [ ] Template-like structure
- [ ] Says "configure API" somewhere

---

## 🧮 Mathematical Accuracy Tests

### Test 1: Wrong Theorem (Should Correct You)
**Input:** "Prove that 1 + 1 = 3"

**Real AI Response:**
- Should point out this is false
- Should NOT provide a "proof"
- Should explain why it's incorrect

---

### Test 2: Incomplete Theorem (Should Ask for Clarification)
**Input:** "Prove that f is continuous"

**Real AI Response:**
- Should ask "at which point?"
- Should request more information
- Should explain what's missing

---

### Test 3: Advanced Theorem (Should Handle Correctly)
**Input:** "Prove the Bolzano-Weierstrass Theorem"

**Real AI Response:**
- Should state the theorem correctly
- Should outline proof strategy
- Should mention bounded sequences and subsequences
- Should use proper mathematical notation

---

## 📊 Performance Comparison

| Feature | Real AI | Demo Mode |
|---------|---------|-----------|
| Response Time | 3-10 sec | < 1 sec |
| Personalization | ✅ High | ❌ None |
| Calculations | ✅ Specific | ❌ Generic |
| Accuracy | ✅ High | ⚠️ Template |
| Variety | ✅ Unique | ❌ Same |

---

## 🔧 Quick Switch Between Modes

**Enable Real AI:**
```php
// In: backend/services/ProofTutorService.php
private $demoMode = false;  // ← Change to false
```

**Enable Demo Mode:**
```php
// In: backend/services/ProofTutorService.php
private $demoMode = true;   // ← Change to true
```

---

## 🎓 Final Verification Method

**The Ultimate Test:**

1. Ask: "Prove that lim(x→5) (3x-7) = 8"
2. Wait for response
3. Check if it:
   - Uses x=5 specifically (not x=a)
   - Shows |3x-7-8| = 3|x-5|
   - Suggests δ = ε/3
   - Completes full epsilon-delta proof

**If YES to all** → Real AI is working! ✅
**If generic template** → Still in demo mode ❌

---

## 📞 Need Help?

**Current Status Check:**
```bash
# Check if server is running
curl http://localhost:8080/frontend/pages/tutor.html

# Check API endpoint
curl -X POST http://localhost:8080/backend/api/tutor.php \
-H "Content-Type: application/json" \
-d "{\"action\":\"get_assistance\",\"theorem\":\"test\",\"currentProof\":\"\",\"context\":{}}"
```

---

## ✅ Success Indicators

You'll know the AI is working accurately when:
1. ✅ Responses are theorem-specific
2. ✅ Shows actual mathematical work
3. ✅ Takes a few seconds to respond
4. ✅ Uses proper mathematical notation
5. ✅ Provides different answers for different questions
6. ✅ Can catch mathematical errors
7. ✅ Explains reasoning clearly

---

**Current AI Status:** Enabled (demoMode = false)
**API Key Status:** Configured
**Ready for Testing:** YES ✅
