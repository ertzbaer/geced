<?php
/**
 * JWT Authentication Configuration
 * Lead Management System v2.0
 */

return [
    // JWT Secret Key - CHANGE THIS IN PRODUCTION!
    'secret_key' => getenv('JWT_SECRET') ?: 'your-super-secret-jwt-key-change-this-in-production-2025',

    // JWT Algorithm
    'algorithm' => 'HS256',

    // Access Token Lifetime (15 minutes)
    'access_token_lifetime' => 15 * 60, // 900 seconds

    // Refresh Token Lifetime (7 days)
    'refresh_token_lifetime' => 7 * 24 * 60 * 60, // 604800 seconds

    // Token Issuer
    'issuer' => getenv('JWT_ISSUER') ?: 'lead-management-system',

    // Token Audience
    'audience' => getenv('JWT_AUDIENCE') ?: 'lead-management-api',
];
