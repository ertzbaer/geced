<?php
/**
 * Application Configuration
 * Lead Management System v2.0
 */

return [
    // Application Name
    'name' => 'Lead Management System',

    // Application Version
    'version' => '2.0.0',

    // Environment (development, production)
    'environment' => getenv('APP_ENV') ?: 'development',

    // Debug Mode
    'debug' => getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'development',

    // Timezone
    'timezone' => 'Europe/Berlin',

    // API Base URL
    'api_url' => getenv('API_URL') ?: 'http://localhost/api',

    // Frontend Base URL
    'frontend_url' => getenv('FRONTEND_URL') ?: 'http://localhost',

    // CORS Settings
    'cors' => [
        'allowed_origins' => ['*'], // In production, specify exact origins
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => [],
        'max_age' => 3600,
        'supports_credentials' => true,
    ],

    // Rate Limiting
    'rate_limiting' => [
        'login_max_attempts' => 5,
        'login_lockout_minutes' => 15,
        'api_max_requests' => 100,
        'api_window_minutes' => 1,
    ],

    // CSRF Protection
    'csrf' => [
        'enabled' => true,
        'token_lifetime' => 3600, // 1 hour
    ],

    // Password Requirements
    'password' => [
        'min_length' => 6,
        'require_uppercase' => false,
        'require_lowercase' => false,
        'require_numbers' => false,
        'require_special_chars' => false,
    ],

    // File Upload
    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'csv'],
    ],

    // Pagination
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    // Email Configuration (for password reset)
    'email' => [
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@leadmanager.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Lead Management System',
    ],

    // WebSocket Configuration (for live updates)
    'websocket' => [
        'enabled' => true,
        'host' => getenv('WS_HOST') ?: 'localhost',
        'port' => getenv('WS_PORT') ?: '8080',
    ],
];
