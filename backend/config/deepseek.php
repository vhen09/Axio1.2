<?php
// config/deepseek.php
return [
    'deepseek_api_url' => 'https://api.deepseek.com/v1/chat/completions',
    
    // ========================================
    // 🔑 PUT YOUR DEEPSEEK API KEY HERE:
    // ========================================
    // 1. Go to: https://platform.deepseek.com/
    // 2. Sign up or login
    // 3. Go to "API Keys" section
    // 4. Create new key
    // 5. Copy the key (starts with sk-) and paste it below between the quotes
    
    'deepseek_api_key' => 'sk-72e7818157464142a3430d562d3db41d',
    
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