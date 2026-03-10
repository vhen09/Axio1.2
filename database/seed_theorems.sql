-- Seed REANA Theorem Categories
INSERT INTO theorem_categories (name, description, display_order) VALUES
('Real Numbers', 'Fundamental properties of real numbers and completeness', 1),
('Sequences', 'Convergence, limits, and properties of sequences', 2),
('Series', 'Infinite series, convergence tests, and summation', 3),
('Continuity', 'Continuous functions and their properties', 4),
('Differentiation', 'Derivatives, differentiability, and related theorems', 5),
('Integration', 'Riemann integration and fundamental theorems', 6),
('Function Sequences', 'Sequences and series of functions, uniform convergence', 7),
('Power Series', 'Power series, Taylor series, and radius of convergence', 8),
('Topology', 'Metric spaces, open/closed sets, and compactness', 9);

-- Seed REANA Theorems
-- CHAPTER 1: Real Numbers
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(1, 'Archimedean Property', 
'For any real number x, there exists a natural number n such that n > x',
'theorem archimedean_property (x : ℝ) : ∃ n : ℕ, (n : ℝ) > x',
'Ang Archimedean Property ay nagsasabing para sa kahit anong real number, may mas malaking natural number.',
'beginner',
'["fundamental", "real numbers", "inequality"]',
'[]',
'Consider the set of natural numbers and use the fact that they are unbounded. Try proof by contradiction if direct proof is difficult.'),

(1, 'Completeness Axiom',
'Every non-empty set of real numbers that is bounded above has a supremum (least upper bound)',
'theorem completeness_axiom (S : Set ℝ) (hne : S.Nonempty) (hbdd : BddAbove S) : ∃ s : ℝ, IsLUB S s',
'Ang bawat set ng real numbers na may upper bound ay may pinakamaliit na upper bound (supremum).',
'intermediate',
'["fundamental", "supremum", "completeness"]',
'["Archimedean Property"]',
'Use the definition of supremum: s is the smallest number such that all elements of S are ≤ s.'),

(1, 'Nested Interval Property',
'If {[aₙ, bₙ]} is a sequence of nested closed intervals, then their intersection is non-empty',
'theorem nested_interval_property {a b : ℕ → ℝ} (h_nested : ∀ n, a n ≤ a (n+1) ∧ b (n+1) ≤ b n) (h_closed : ∀ n, a n ≤ b n) : ∃ x : ℝ, ∀ n, a n ≤ x ∧ x ≤ b n',
'Kapag may mga nested intervals na papalaki ang left side at paliliit ang right side, may punto sa gitna na nasa lahat ng intervals.',
'intermediate',
'["intervals", "sequences", "completeness"]',
'["Completeness Axiom", "Monotone Convergence Theorem"]',
'Show that {aₙ} is increasing and bounded above, so it converges. Similarly for {bₙ}. Show they converge to the same limit.'),

(1, 'Density of Rationals',
'Between any two distinct real numbers, there exists a rational number',
'theorem density_of_rationals (x y : ℝ) (h : x < y) : ∃ q : ℚ, x < q ∧ (q : ℝ) < y',
'Sa pagitan ng dalawang magkaibang real numbers, laging may rational number.',
'beginner',
'["rationals", "density", "fundamental"]',
'["Archimedean Property"]',
'Use Archimedean property to find n such that n(y-x) > 1. Then find the smallest integer m with m/n > x.');

-- CHAPTER 2: Sequences
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(2, 'Uniqueness of Limits',
'If a sequence converges, its limit is unique',
'theorem limit_unique {a : ℕ → ℝ} {L₁ L₂ : ℝ} (h₁ : sequence_converges_to a L₁) (h₂ : sequence_converges_to a L₂) : L₁ = L₂',
'Ang isang sequence ay may isang limit lamang. Hindi pwedeng dalawa o higit pa.',
'beginner',
'["sequences", "limits", "uniqueness"]',
'[]',
'Assume L₁ ≠ L₂. Choose ε = |L₁ - L₂|/2 and derive a contradiction using the triangle inequality.'),

(2, 'Squeeze Theorem',
'If aₙ ≤ bₙ ≤ cₙ and lim aₙ = lim cₙ = L, then lim bₙ = L',
'theorem squeeze_theorem {a b c : ℕ → ℝ} {L : ℝ} (h_squeeze : ∀ n, a n ≤ b n ∧ b n ≤ c n) (ha : sequence_converges_to a L) (hc : sequence_converges_to c L) : sequence_converges_to b L',
'Kung ang sequence b ay nakasingit sa gitna ng a at c, at pareho ang limit ng a at c, ang limit ni b ay pareho din.',
'intermediate',
'["sequences", "limits", "comparison"]',
'["Uniqueness of Limits"]',
'Given ε > 0, find N such that both |aₙ - L| < ε and |cₙ - L| < ε. Use the squeeze property to bound |bₙ - L|.'),

(2, 'Monotone Convergence Theorem',
'A bounded monotone sequence converges',
'theorem monotone_convergence {a : ℕ → ℝ} (h_mono : ∀ n, a n ≤ a (n+1)) (h_bdd : ∃ M, ∀ n, a n ≤ M) : ∃ L, sequence_converges_to a L',
'Ang isang sequence na pataaas (o pababa) at may hangganan ay convergent.',
'intermediate',
'["sequences", "monotone", "convergence", "fundamental"]',
'["Completeness Axiom"]',
'Use completeness: the set {aₙ : n ∈ ℕ} has a supremum L. Show this is the limit using the definition.'),

(2, 'Bolzano-Weierstrass Theorem',
'Every bounded sequence has a convergent subsequence',
'theorem bolzano_weierstrass {a : ℕ → ℝ} (h_bdd : ∃ M, ∀ n, |a n| ≤ M) : ∃ (φ : ℕ → ℕ) (L : ℝ), StrictMono φ ∧ sequence_converges_to (a ∘ φ) L',
'Bawat bounded sequence ay may subsequence na convergent.',
'advanced',
'["sequences", "subsequences", "compactness"]',
'["Monotone Convergence Theorem", "Nested Interval Property"]',
'Use nested intervals or show there exists an accumulation point. Construct subsequence approaching this point.'),

(2, 'Cauchy Criterion',
'A sequence converges if and only if it is Cauchy',
'theorem cauchy_criterion (a : ℕ → ℝ) : (∃ L, sequence_converges_to a L) ↔ (∀ ε > 0, ∃ N, ∀ m n, m ≥ N → n ≥ N → |a m - a n| < ε)',
'Ang sequence ay convergent kung at kung ang mga terms nito ay nagiging closer sa isa\'t isa.',
'advanced',
'["sequences", "convergence", "cauchy", "fundamental"]',
'["Bolzano-Weierstrass Theorem", "Completeness Axiom"]',
'Forward: If convergent, use triangle inequality. Backward: Show Cauchy sequence is bounded, use BW theorem to get convergent subsequence.');

-- CHAPTER 3: Series
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(3, 'Geometric Series',
'The series ∑rⁿ converges to 1/(1-r) if |r| < 1',
'theorem geometric_series {r : ℝ} (h : |r| < 1) : series_converges (fun n => r^n) (1 / (1 - r))',
'Ang geometric series na may ratio na mas maliit sa 1 ay convergent.',
'beginner',
'["series", "geometric", "convergence"]',
'["Uniqueness of Limits"]',
'Find the formula for partial sums: Sₙ = (1 - rⁿ⁺¹)/(1 - r). Show rⁿ → 0 as n → ∞.'),

(3, 'Comparison Test',
'If 0 ≤ aₙ ≤ bₙ and ∑bₙ converges, then ∑aₙ converges',
'theorem comparison_test {a b : ℕ → ℝ} (h_pos : ∀ n, 0 ≤ a n) (h_comp : ∀ n, a n ≤ b n) (hb : ∃ L, series_converges b L) : ∃ L, series_converges a L',
'Kung mas maliit ang terms ng isang series sa convergent series, convergent din ito.',
'intermediate',
'["series", "comparison", "convergence test"]',
'["Monotone Convergence Theorem"]',
'Show partial sums of ∑aₙ are increasing and bounded above by partial sums of ∑bₙ.'),

(3, 'Ratio Test',
'If lim |aₙ₊₁/aₙ| < 1, then ∑aₙ converges',
'theorem ratio_test {a : ℕ → ℝ} (h : ∃ L < 1, sequence_converges_to (fun n => |a (n+1) / a n|) L) : ∃ S, series_converges a S',
'Kung ang ratio ng consecutive terms ay mas maliit sa 1, ang series ay convergent.',
'intermediate',
'["series", "ratio test", "convergence test"]',
'["Geometric Series", "Comparison Test"]',
'Find r such that L < r < 1. Show |aₙ| ≤ Crⁿ for some C. Compare with geometric series.'),

(3, 'Root Test',
'If lim sup |aₙ|^(1/n) < 1, then ∑aₙ converges',
'theorem root_test {a : ℕ → ℝ} (h : ∃ L < 1, sequence_converges_to (fun n => |a n|^(1/(n:ℝ))) L) : ∃ S, series_converges a S',
'Kung ang n-th root ng |aₙ| ay mas maliit sa 1, ang series ay convergent.',
'intermediate',
'["series", "root test", "convergence test"]',
'["Geometric Series", "Comparison Test"]',
'Similar to ratio test. Find r such that L < r < 1 and show |aₙ| ≤ rⁿ eventually.'),

(3, 'Alternating Series Test',
'If {aₙ} is decreasing and converges to 0, then ∑(-1)ⁿaₙ converges',
'theorem alternating_series_test {a : ℕ → ℝ} (h_pos : ∀ n, a n > 0) (h_decr : ∀ n, a (n+1) ≤ a n) (h_lim : sequence_converges_to a 0) : ∃ L, series_converges (fun n => (-1)^n * a n) L',
'Ang alternating series na may decreasing positive terms na papunta sa 0 ay convergent.',
'intermediate',
'["series", "alternating", "convergence test"]',
'["Monotone Convergence Theorem"]',
'Show odd and even partial sums form monotone bounded sequences. Show they converge to same limit.');

-- CHAPTER 4: Continuity
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(4, 'Intermediate Value Theorem',
'If f is continuous on [a,b] and f(a) < y < f(b), then there exists c ∈ (a,b) such that f(c) = y',
'theorem intermediate_value_theorem {f : ℝ → ℝ} {a b y : ℝ} (hab : a < b) (hcont : ∀ x ∈ Set.Icc a b, continuous_at f x) (hy : f a < y ∧ y < f b) : ∃ c ∈ Set.Ioo a b, f c = y',
'Ang continuous function ay dumadaan sa lahat ng values sa pagitan ng dalawang endpoints.',
'intermediate',
'["continuity", "IVT", "fundamental"]',
'["Completeness Axiom", "Nested Interval Property"]',
'Consider S = {x ∈ [a,b] : f(x) < y}. Show sup(S) = c is the desired value using continuity.'),

(4, 'Extreme Value Theorem',
'A continuous function on a closed bounded interval attains its maximum and minimum',
'theorem extreme_value_theorem {f : ℝ → ℝ} {a b : ℝ} (hab : a ≤ b) (hcont : ∀ x ∈ Set.Icc a b, continuous_at f x) : (∃ c ∈ Set.Icc a b, ∀ x ∈ Set.Icc a b, f x ≤ f c) ∧ (∃ d ∈ Set.Icc a b, ∀ x ∈ Set.Icc a b, f d ≤ f x)',
'Ang continuous function sa closed interval ay may maximum at minimum value.',
'advanced',
'["continuity", "EVT", "compactness"]',
'["Bolzano-Weierstrass Theorem", "Completeness Axiom"]',
'Let M = sup{f(x) : x ∈ [a,b]}. Find sequence xₙ with f(xₙ) → M. Use BW to extract convergent subsequence.'),

(4, 'Heine-Cantor Theorem',
'A continuous function on a compact set is uniformly continuous',
'theorem heine_cantor {f : ℝ → ℝ} {a b : ℝ} (hab : a ≤ b) (hcont : ∀ x ∈ Set.Icc a b, continuous_at f x) : uniformly_continuous_on f (Set.Icc a b)',
'Ang continuous function sa closed bounded interval ay uniformly continuous.',
'advanced',
'["continuity", "uniform continuity", "compactness"]',
'["Extreme Value Theorem", "Bolzano-Weierstrass Theorem"]',
'Proof by contradiction. Assume not uniformly continuous. Construct sequences violating uniform continuity and use compactness.');

-- CHAPTER 5: Differentiation
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(5, 'Rolle\'s Theorem',
'If f is continuous on [a,b], differentiable on (a,b), and f(a) = f(b), then there exists c ∈ (a,b) with f\'(c) = 0',
'theorem rolles_theorem {f : ℝ → ℝ} {a b : ℝ} (hab : a < b) (hcont : ∀ x ∈ Set.Icc a b, continuous_at f x) (hdiff : ∀ x ∈ Set.Ioo a b, ∃ L, has_derivative_at f x L) (heq : f a = f b) : ∃ c ∈ Set.Ioo a b, has_derivative_at f c 0',
'Kung ang function ay may same values sa endpoints, may punto sa gitna na horizontal ang tangent line.',
'intermediate',
'["differentiation", "Rolle", "mean value"]',
'["Extreme Value Theorem"]',
'Use EVT to find max/min. If max/min is interior, derivative is 0. If both at endpoints, f is constant.'),

(5, 'Mean Value Theorem',
'If f is continuous on [a,b] and differentiable on (a,b), then there exists c ∈ (a,b) such that f\'(c) = (f(b) - f(a))/(b - a)',
'theorem mean_value_theorem {f : ℝ → ℝ} {a b : ℝ} (hab : a < b) (hcont : ∀ x ∈ Set.Icc a b, continuous_at f x) (hdiff : ∀ x ∈ Set.Ioo a b, ∃ L, has_derivative_at f x L) : ∃ c ∈ Set.Ioo a b, ∃ L, has_derivative_at f c L ∧ L = (f b - f a) / (b - a)',
'May punto sa interval na ang instantaneous rate of change ay equal sa average rate of change.',
'intermediate',
'["differentiation", "MVT", "fundamental"]',
'["Rolle\'s Theorem"]',
'Apply Rolle\'s theorem to g(x) = f(x) - ((f(b)-f(a))/(b-a))(x-a).'),

(5, 'Chain Rule',
'The derivative of a composition is (f ∘ g)\'(x) = f\'(g(x)) · g\'(x)',
'theorem chain_rule {f g : ℝ → ℝ} {x : ℝ} {L₁ L₂ : ℝ} (hf : has_derivative_at f (g x) L₁) (hg : has_derivative_at g x L₂) : has_derivative_at (f ∘ g) x (L₁ * L₂)',
'Ang derivative ng composition ay product ng derivatives.',
'intermediate',
'["differentiation", "chain rule", "composition"]',
'[]',
'Write difference quotients for both functions. Show the composition difference quotient telescopes properly.');

-- CHAPTER 6: Integration
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(6, 'Fundamental Theorem of Calculus Part 1',
'If F\'(x) = f(x), then ∫ᵃᵇ f = F(b) - F(a)',
'theorem fundamental_theorem_calculus_1 {f F : ℝ → ℝ} {a b : ℝ} (hab : a ≤ b) (hdiff : ∀ x ∈ Set.Ioo a b, has_derivative_at F x (f x)) (hint : riemann_integrable f a b) : ∃ I, (∀ ε > 0, ∃ δ > 0, ∀ P, |upper_sum f P - I| < ε) ∧ I = F b - F a',
'Ang integral ay antiderivative: integral ng derivative ay balik sa original function.',
'advanced',
'["integration", "FTC", "fundamental"]',
'["Mean Value Theorem", "Riemann Integration"]',
'For any partition, use MVT on each subinterval. Show Riemann sum approximates F(b) - F(a).'),

(6, 'Fundamental Theorem of Calculus Part 2',
'If f is continuous, then d/dx ∫ᵃˣ f(t)dt = f(x)',
'theorem fundamental_theorem_calculus_2 {f : ℝ → ℝ} {a : ℝ} (hcont : ∀ x, continuous_at f x) : ∀ x, has_derivative_at (fun t => sorry) x (f x)',
'Ang derivative ng integral function ay balik sa original function.',
'advanced',
'["integration", "FTC", "fundamental"]',
'["Riemann Integration", "Mean Value Theorem"]',
'Use MVT for integrals. Show (1/h)∫[x,x+h] f(t)dt → f(x) as h → 0.'),

(6, 'Mean Value Theorem for Integrals',
'If f is continuous on [a,b], then there exists c ∈ [a,b] such that ∫ᵃᵇ f = f(c)(b-a)',
'theorem mean_value_theorem_integrals {f : ℝ → ℝ} {a b : ℝ} (hab : a < b) (hcont : ∀ x ∈ Set.Icc a b, continuous_at f x) : ∃ c ∈ Set.Icc a b, ∃ I, (∀ ε > 0, ∃ δ > 0, ∀ P, |upper_sum f P - I| < ε) ∧ I = f c * (b - a)',
'May punto na ang value ay equal sa average value ng function.',
'intermediate',
'["integration", "mean value", "average"]',
'["Intermediate Value Theorem", "Extreme Value Theorem"]',
'Let m and M be min and max of f. Then m(b-a) ≤ ∫f ≤ M(b-a). Use IVT.');

-- CHAPTER 7: Function Sequences
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(7, 'Uniform Limit Theorem',
'The uniform limit of continuous functions is continuous',
'theorem uniform_limit_continuous {f : ℕ → ℝ → ℝ} {g : ℝ → ℝ} {S : Set ℝ} (hcont : ∀ n, ∀ x ∈ S, continuous_at (f n) x) (hunif : uniform_convergence f g S) : ∀ x ∈ S, continuous_at g x',
'Ang uniform limit ng continuous functions ay continuous din.',
'advanced',
'["function sequences", "uniform convergence", "continuity"]',
'["Sequences", "Continuity"]',
'Given ε > 0, use uniform convergence to find N, then use continuity of fₙ. Apply triangle inequality three times.'),

(7, 'Weierstrass M-Test',
'If |fₙ(x)| ≤ Mₙ for all x and ∑Mₙ converges, then ∑fₙ converges uniformly',
'theorem weierstrass_m_test {f : ℕ → ℝ → ℝ} {M : ℕ → ℝ} {S : Set ℝ} (hbound : ∀ n, ∀ x ∈ S, |f n x| ≤ M n) (hM : ∃ L, series_converges M L) : ∃ g, uniform_convergence (fun n => fun x => ∑ i in Finset.range n, f i x) g S',
'Kung bounded ang terms ng function series ng convergent numerical series, uniform convergence.',
'advanced',
'["function sequences", "series", "uniform convergence"]',
'["Cauchy Criterion", "Series"]',
'Show partial sums form Cauchy sequence in uniform norm. Use comparison with ∑Mₙ.');

-- CHAPTER 8: Power Series
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(8, 'Power Series Convergence',
'A power series ∑aₙxⁿ converges absolutely for |x| < R and diverges for |x| > R',
'theorem power_series_convergence {a : ℕ → ℝ} {x : ℝ} (R : ℝ := radius_of_convergence a) (hx : |x| < R) : ∃ L, series_converges (fun n => a n * x^n) L',
'Ang power series ay may radius of convergence: convergent sa loob, divergent sa labas.',
'advanced',
'["power series", "radius of convergence", "series"]',
'["Ratio Test", "Root Test"]',
'Use ratio or root test. Show it works for |x| < R and fails for |x| > R.'),

(8, 'Term-by-Term Differentiation',
'A power series can be differentiated term-by-term within its radius of convergence',
'theorem power_series_differentiation {a : ℕ → ℝ} {x : ℝ} (R : ℝ := radius_of_convergence a) (hx : |x| < R) : ∃ f f\', (∀ t, |t| < R → series_converges (fun n => a n * t^n) (f t)) ∧ (∀ t, |t| < R → has_derivative_at f t (f\' t)) ∧ (∀ t, |t| < R → series_converges (fun n => (n:ℝ) * a n * t^(n-1)) (f\' t))',
'Ang power series ay pwedeng i-differentiate term-by-term.',
'expert',
'["power series", "differentiation", "term-by-term"]',
'["Power Series Convergence", "Uniform Convergence", "Chain Rule"]',
'Show the differentiated series has same radius. Use uniform convergence on compact subsets.');

-- CHAPTER 9: Topology
INSERT INTO theorems (category_id, name, statement, lean_code, natural_language_description, difficulty_level, tags, prerequisites, proof_hints) VALUES
(9, 'Heine-Borel Theorem',
'A subset of ℝ is compact if and only if it is closed and bounded',
'theorem heine_borel (S : Set ℝ) : is_compact S ↔ (is_closed S ∧ ∃ M, ∀ x ∈ S, |x| ≤ M)',
'Ang set sa real numbers ay compact kung at kung ito ay closed at bounded.',
'expert',
'["topology", "compactness", "fundamental"]',
'["Bolzano-Weierstrass Theorem", "Completeness"]',
'Forward: Show compact sets are closed and bounded. Backward: Use BW theorem and sequential compactness.'),

(9, 'Baire Category Theorem',
'A complete metric space is not the countable union of nowhere dense sets',
'theorem baire_category_theorem : ∀ (F : ℕ → Set ℝ), (∀ n, is_closed (F n)) → (∀ n, ∀ U, is_open U → U ≠ ∅ → ∃ V, is_open V ∧ V ⊆ U ∧ V ∩ F n = ∅) → (⋃ n, F n) ≠ Set.univ',
'Ang complete metric space ay hindi pwedeng union ng countably many nowhere dense sets.',
'expert',
'["topology", "completeness", "category"]',
'["Completeness", "Nested Interval Property"]',
'Construct nested closed balls avoiding each Fₙ. Use completeness to show intersection is nonempty.');
