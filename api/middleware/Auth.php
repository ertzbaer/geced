<?php
/**
 * Authentication Middleware
 * Validates JWT tokens and user permissions
 */

namespace App\Middleware;

use App\Utils\JWT;
use App\Utils\Response;
use App\Utils\Database;

class Auth
{
    private static $currentUser = null;

    /**
     * Verify JWT token and authenticate user
     *
     * @return array|null User data if authenticated
     */
    public static function authenticate(): ?array
    {
        $jwt = new JWT();
        $token = JWT::getBearerToken();

        if (!$token) {
            Response::unauthorized('No authentication token provided');
        }

        $decoded = $jwt->validateToken($token);

        if (!$decoded) {
            Response::unauthorized('Invalid or expired token');
        }

        // Get user from database
        $db = Database::getInstance();
        $user = $db->query(
            "SELECT id, username, email, role, status FROM users WHERE id = ? AND status = 'active' LIMIT 1",
            [$decoded['data']['id']]
        );

        if (empty($user)) {
            Response::unauthorized('User not found or inactive');
        }

        self::$currentUser = $user[0];
        return self::$currentUser;
    }

    /**
     * Check if user has required role
     *
     * @param array $allowedRoles
     * @return void
     */
    public static function requireRole(array $allowedRoles): void
    {
        if (self::$currentUser === null) {
            self::authenticate();
        }

        if (!in_array(self::$currentUser['role'], $allowedRoles)) {
            Response::forbidden('You do not have permission to access this resource');
        }
    }

    /**
     * Check if user is superadmin
     *
     * @return void
     */
    public static function requireSuperadmin(): void
    {
        self::requireRole(['superadmin']);
    }

    /**
     * Check if user is admin or superadmin
     *
     * @return void
     */
    public static function requireAdmin(): void
    {
        self::requireRole(['superadmin', 'admin']);
    }

    /**
     * Get current authenticated user
     *
     * @return array|null
     */
    public static function getCurrentUser(): ?array
    {
        return self::$currentUser;
    }

    /**
     * Optional authentication - doesn't fail if no token
     *
     * @return array|null
     */
    public static function optionalAuth(): ?array
    {
        $jwt = new JWT();
        $token = JWT::getBearerToken();

        if (!$token) {
            return null;
        }

        $decoded = $jwt->validateToken($token);

        if (!$decoded) {
            return null;
        }

        $db = Database::getInstance();
        $user = $db->query(
            "SELECT id, username, email, role, status FROM users WHERE id = ? AND status = 'active' LIMIT 1",
            [$decoded['data']['id']]
        );

        if (empty($user)) {
            return null;
        }

        self::$currentUser = $user[0];
        return self::$currentUser;
    }
}
