<?php
/**
 * Rate Limiting Middleware
 * Prevents brute force attacks and API abuse
 */

namespace App\Middleware;

use App\Utils\Database;
use App\Utils\Response;

class RateLimit
{
    /**
     * Check login rate limit
     *
     * @param string $email
     * @param string $ipAddress
     * @return void
     */
    public static function checkLoginAttempts(string $email, string $ipAddress): void
    {
        $config = require __DIR__ . '/../../config/app.php';
        $maxAttempts = $config['rate_limiting']['login_max_attempts'];
        $lockoutMinutes = $config['rate_limiting']['login_lockout_minutes'];

        $db = Database::getInstance();

        // Clean up old attempts
        $db->execute(
            "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$lockoutMinutes]
        );

        // Count recent failed attempts
        $attempts = $db->query(
            "SELECT COUNT(*) as count FROM login_attempts
             WHERE email = ? AND ip_address = ? AND success = FALSE
             AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$email, $ipAddress, $lockoutMinutes]
        );

        $attemptCount = $attempts[0]['count'] ?? 0;

        if ($attemptCount >= $maxAttempts) {
            Response::rateLimitExceeded(
                "Too many failed login attempts. Please try again in $lockoutMinutes minutes."
            );
        }
    }

    /**
     * Record login attempt
     *
     * @param string $email
     * @param string $ipAddress
     * @param bool $success
     * @return void
     */
    public static function recordLoginAttempt(string $email, string $ipAddress, bool $success): void
    {
        $db = Database::getInstance();

        $db->execute(
            "INSERT INTO login_attempts (email, ip_address, success, attempted_at) VALUES (?, ?, ?, NOW())",
            [$email, $ipAddress, $success ? 1 : 0]
        );
    }

    /**
     * Clear successful login attempts
     *
     * @param string $email
     * @param string $ipAddress
     * @return void
     */
    public static function clearLoginAttempts(string $email, string $ipAddress): void
    {
        $db = Database::getInstance();

        $db->execute(
            "DELETE FROM login_attempts WHERE email = ? AND ip_address = ?",
            [$email, $ipAddress]
        );
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public static function getClientIP(): string
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
