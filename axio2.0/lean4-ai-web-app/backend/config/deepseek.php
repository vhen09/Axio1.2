<?php
// config/deepseek.php
return [
    'deepseek_api_url' => 'https://api.deepseek.com/v1/chat/completions',
    
    // ========================================
    // 🔑 API KEY FROM ENVIRONMENT VARIABLES
    // ========================================
    // DO NOT hardcode API keys in this file!
    // Set via environment variables instead:
    // 1. Go to Render Dashboard
    // 2. Environment → Add Variable
    // 3. Name: DEEPSEEK_API_KEY
    // 4. Value: sk-xxxxxxxxxxxxxxxxxxxxx
    
    'deepseek_api_key' => getenv('DEEPSEEK_API_KEY') ?: '',
    
    'timeout' => 60,
    'connect_timeout' => 10,
    'model' => 'deepseek-chat',
    'max_tokens' => 900,
    'temperature' => 0.6,
    'cache_ttl' => 0,
    'retry_attempts' => 2,
    'retry_delay_ms' => 1200,
    'fallback_on_timeout' => false,
];