<?php
// Auto-initialize database on app startup
@require_once __DIR__ . '/backend/config/auto-setup.php';

header('Location: frontend/pages/welcome.html', true, 302);
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REANA - Real Analysis Education with AI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0A1929 0%, #1A2332 50%, #0D1F33 100%);
            min-height: 100vh;
            color: #E8EAF6;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #2D3F54 0%, #1A2332 100%);
            border-bottom: 3px solid #FFD700;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.8em;
            font-weight: bold;
            background: linear-gradient(135deg, #FFD700 0%, #FFF4CC 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header-nav a {
            color: #E8EAF6;
            text-decoration: none;
            margin-left: 30px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .header-nav a:hover {
            background: rgba(255, 215, 0, 0.1);
            color: #FFD700;
        }
        
        /* Hero Section */
        .hero {
            text-align: center;
            padding: 80px 20px 60px;
            max-width: 900px;
            margin: 0 auto;
        }
        .hero h1 {
            font-size: 3em;
            background: linear-gradient(135deg, #FFD700 0%, #FFF4CC 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        .hero .tagline {
            font-size: 1.3em;
            color: #FFD700;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .hero .description {
            font-size: 1.1em;
            color: #B8C5D6;
            line-height: 1.8;
            margin-bottom: 40px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #FFD700 0%, #DAA520 100%);
            color: #0A1929;
            padding: 18px 45px;
            font-size: 1.2em;
            font-weight: bold;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
            border: none;
            cursor: pointer;
        }
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 215, 0, 0.4);
        }
        
        /* Features Section */
        .features {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .section-title {
            text-align: center;
            font-size: 2.2em;
            color: #FFD700;
            margin-bottom: 50px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }
        .feature-card {
            background: linear-gradient(135deg, #2D3F54 0%, #1A2332 100%);
            border: 2px solid #FFD700;
            border-radius: 15px;
            padding: 35px;
            text-align: center;
            transition: all 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.2);
            border-color: #FFF4CC;
        }
        .feature-icon {
            font-size: 3.5em;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            color: #FFD700;
            font-size: 1.4em;
            margin-bottom: 15px;
        }
        .feature-card p {
            color: #B8C5D6;
            line-height: 1.7;
        }
        
        /* Quick Access Section */
        .quick-access {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        .access-card {
            background: linear-gradient(135deg, #0A1929 0%, #1A2332 100%);
            border: 2px solid #FFD700;
            border-radius: 12px;
            padding: 25px;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            display: block;
        }
        .access-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
            background: linear-gradient(135deg, #1A2332 0%, #2D3F54 100%);
        }
        .access-card .icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }
        .access-card h3 {
            color: #FFD700;
            margin-bottom: 10px;
        }
        .access-card p {
            color: #B8C5D6;
            font-size: 0.95em;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 40px 20px;
            margin-top: 80px;
            border-top: 2px solid rgba(255, 215, 0, 0.3);
            color: #B8C5D6;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">📐 REANA</div>
            <nav class="header-nav">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="frontend/pages/welcome.html">Get Started</a>
            </nav>
        </div>
    </div>
    
    <!-- Hero Section -->
    <div class="hero">
        <h1>Real Analysis Education with AI</h1>
        <p class="tagline">Master Mathematical Proofs with AI-Powered Guidance</p>
        <p class="description">
            REANA is an intelligent tutoring system that helps students learn Real Analysis 
            through interactive proof construction, step-by-step guidance, and instant feedback. 
            Perfect for undergraduate mathematics students preparing for exams and thesis defense.
        </p>
        <a href="frontend/pages/welcome.html" class="cta-button">🚀 Start Learning Now</a>
    </div>
    
    <!-- Features Section -->
    <div class="features" id="features">
        <h2 class="section-title">Why Choose REANA?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>AI-Powered Tutor</h3>
                <p>Get personalized guidance from DeepSeek AI trained in Real Analysis. Receive detailed explanations, proof strategies, and step-by-step assistance.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Interactive Proofs</h3>
                <p>Write proofs step-by-step with real-time verification. Use mathematical symbols easily with keyboard shortcuts.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>29 Theorems Library</h3>
                <p>Access a curated collection of essential Real Analysis theorems covering limits, continuity, sequences, and series.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✓</div>
                <h3>Instant Feedback</h3>
                <p>Get immediate verification of your proof steps. Learn from mistakes with constructive feedback and hints.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Track Progress</h3>
                <p>Monitor your learning journey with submission history and scores. See your improvement over time.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💾</div>
                <h3>Auto-Save</h3>
                <p>Never lose your work. All theorems and proof steps are automatically saved and restored on refresh.</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Access -->
    <div class="quick-access">
        <h2 class="section-title" id="how-it-works">Get Started in 3 Easy Steps</h2>
        <div class="access-grid">
            <a href="frontend/pages/theorems.html" class="access-card">
                <div class="icon">📚</div>
                <h3>1. Browse Theorems</h3>
                <p>Explore 29 Real Analysis theorems organized by topic</p>
            </a>
            <a href="frontend/pages/tutor.html" class="access-card">
                <div class="icon">🤖</div>
                <h3>2. Get AI Guidance</h3>
                <p>Enter a theorem and receive AI-powered proof strategies</p>
            </a>
            <a href="frontend/pages/submissions.html" class="access-card">
                <div class="icon">✅</div>
                <h3>3. Submit & Review</h3>
                <p>Submit your proofs and track your progress</p>
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>REANA</strong> - Real Analysis Education with AI</p>
        <p style="margin-top: 10px; font-size: 0.9em;">Thesis Project 2026 | Powered by DeepSeek AI</p>
    </div>
</body>
</html>
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            animation: float 6s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.08) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            animation: float 8s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        .container {
            background: linear-gradient(135deg, #2D3F54 0%, #1A2332 100%);
            border: 3px solid #FFD700;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 40px rgba(255, 215, 0, 0.2);
            max-width: 800px;
            width: 90%;
            position: relative;
            z-index: 1;
        }
        h1 {
            background: linear-gradient(135deg, #FFD700 0%, #FFF4CC 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
            text-shadow: 0 0 30px rgba(255, 215, 0, 0.4);
        }
        .subtitle {
            text-align: center;
            color: #E8EAF6;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .status {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2) 0%, rgba(255, 215, 0, 0.1) 100%);
            border: 2px solid #FFD700;
            color: #FFD700;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: linear-gradient(135deg, #0A1929 0%, #1A2332 100%);
            border: 2px solid #FFD700;
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.2), transparent);
            transition: left 0.5s;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4), 0 0 30px rgba(255, 215, 0, 0.3);
            border-color: #FFF4CC;
        }
        .card:hover::before {
            left: 100%;
        }
        .card-icon {
            font-size: 3em;
            margin-bottom: 10px;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.5));
        }
        .card-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
            color: #FFD700;
        }
        .card-desc {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .info h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .info ul {
            list-style: none;
            padding-left: 0;
        }
        .info li {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .info li:last-child {
            border-bottom: none;
        }
        .server-info {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 REANA System</h1>
        <p class="subtitle">Real Analysis Theorem Library with AI-Powered Proof Assistant</p>
        
        <div class="status">
            ✅ System is Running Successfully!
        </div>

        <div class="server-info">
            <strong>📡 Server Status:</strong> PHP Development Server on Port 8080<br>
            <strong>💾 Database:</strong> <span id="dbStatus">Checking...</span><br>
            <strong>📚 Theorems Loaded:</strong> <span id="theoremCount">Loading...</span>
        </div>

        <div class="cards">
            <a href="frontend/pages/theorems.html" class="card">
                <div class="card-icon">📚</div>
                <div class="card-title">Theorem Browser</div>
                <div class="card-desc">Browse 29+ Real Analysis theorems</div>
            </a>

            <a href="frontend/pages/tutor.html" class="card">
                <div class="card-icon">🤖</div>
                <div class="card-title">AI Proof Assistant</div>
                <div class="card-desc">Get AI help with proofs</div>
            </a>

            <a href="frontend/pages/dashboard.html" class="card">
                <div class="card-icon">📊</div>
                <div class="card-title">Dashboard</div>
                <div class="card-desc">View your progress</div>
            </a>

            <a href="test-system.html" class="card">
                <div class="card-icon">🧪</div>
                <div class="card-title">System Test</div>
                <div class="card-desc">Test all features</div>
            </a>
        </div>

        <div class="info">
            <h3>📋 Quick Start Guide</h3>
            <ul>
                <li><strong>Step 1:</strong> Click "Theorem Browser" to see all theorems</li>
                <li><strong>Step 2:</strong> Search or filter by category/difficulty</li>
                <li><strong>Step 3:</strong> Click "Start Proving" on any theorem</li>
                <li><strong>Step 4:</strong> Write your proof in natural language (Tagalog or English)</li>
                <li><strong>Step 5:</strong> System converts to Lean and verifies automatically</li>
            </ul>
        </div>

        <div class="info">
            <h3>🔌 API Endpoints</h3>
            <ul>
                <li><a href="backend/api/theorems.php?action=get_categories" target="_blank">Get Categories</a></li>
                <li><a href="backend/api/theorems.php?action=get_theorems" target="_blank">Get All Theorems</a></li>
                <li><a href="backend/api/theorems.php?action=get_theorem_statistics" target="_blank">Get Statistics</a></li>
            </ul>
        </div>
    </div>

    <script>
        // Check database status
        fetch('backend/api/theorems.php?action=get_categories')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('dbStatus').innerHTML = '<span style="color:green">✅ Connected</span>';
                    document.getElementById('theoremCount').textContent = data.categories.reduce((sum, cat) => sum + parseInt(cat.theorem_count || 0), 0);
                } else {
                    document.getElementById('dbStatus').innerHTML = '<span style="color:red">❌ Error</span>';
                    document.getElementById('theoremCount').textContent = 'N/A';
                }
            })
            .catch(err => {
                document.getElementById('dbStatus').innerHTML = '<span style="color:red">❌ Connection Error</span>';
                document.getElementById('theoremCount').textContent = 'N/A';
            });
    </script>
</body>
</html>
