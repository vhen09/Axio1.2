<?php
/**
 * AXIO - Real Analysis Theorem Proving System
 * Main routing file for Render deployment
 */

// Set headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Serve static files from frontend
$requested_file = $_SERVER['REQUEST_URI'];

// Remove query string
$requested_file = strtok($requested_file, '?');

// Map static file requests
if (strpos($requested_file, '/frontend/') === 0) {
    $file_path = __DIR__ . $requested_file;
    
    // Security: prevent directory traversal
    $file_path = realpath($file_path);
    if ($file_path && strpos($file_path, __DIR__) === 0 && is_file($file_path)) {
        // Serve the file with appropriate content type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . finfo_file($finfo, $file_path));
        finfo_close($finfo);
        readfile($file_path);
        exit;
    }
}

// Route API requests to backend
if (strpos($requested_file, '/backend/api/') === 0) {
    $api_file = __DIR__ . $requested_file;
    
    if (is_file($api_file)) {
        require $api_file;
        exit;
    }
}

// Default: serve main page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AXIO - AI Proof Tutor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        .logo {
            font-size: 4em;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        a {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .status {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 6px;
            margin-top: 30px;
            text-align: left;
        }
        .status h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .status ul {
            list-style: none;
            padding-left: 0;
        }
        .status li {
            color: #666;
            padding: 5px 0;
            padding-left: 25px;
            position: relative;
        }
        .status li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4caf50;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🤖</div>
        <h1>AXIO</h1>
        <p>Real Analysis Theorem Proving System with AI Tutoring</p>
        
        <div class="buttons">
            <a href="/frontend/pages/theorems.html" class="btn-primary">Browse Theorems</a>
            <a href="/frontend/pages/tutor.html" class="btn-primary">AI Proof Tutor</a>
            <a href="/frontend/pages/dashboard.html" class="btn-secondary">Dashboard</a>
        </div>
        
        <div class="status">
            <h3>System Status</h3>
            <ul>
                <li>Frontend: Online</li>
                <li>Backend API: Ready</li>
                <li>AI Tutoring: Enabled</li>
                <li>Theorem Library: Available</li>
            </ul>
        </div>
    </div>
</body>
</html>
