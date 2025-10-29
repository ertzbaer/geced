<?php
/**
 * Authentication Routes
 * Handles login, logout, token refresh, and password management
 */

use App\Utils\Router;
use App\Utils\Response;
use App\Utils\Database;
use App\Utils\Validator;
use App\Utils\JWT;
use App\Middleware\Auth;
use App\Middleware\RateLimit;

/**
 * POST /api/auth/login
 * User login
 */
$router->post('/auth/login', function () {
    $data = Router::getJsonBody();
    $validator = new Validator();

    // Validate input
    if (!$validator->required($data['email'] ?? '', 'email') ||
        !$validator->email($data['email'] ?? '', 'email') ||
        !$validator->required($data['password'] ?? '', 'password')) {
        Response::validationError($validator->getErrors());
    }

    $email = Validator::sanitizeEmail($data['email']);
    $password = $data['password'];
    $ipAddress = RateLimit::getClientIP();

    // Check rate limiting
    RateLimit::checkLoginAttempts($email, $ipAddress);

    // Find user
    $db = Database::getInstance();
    $users = $db->query(
        "SELECT * FROM users WHERE email = ? LIMIT 1",
        [$email]
    );

    if (empty($users)) {
        RateLimit::recordLoginAttempt($email, $ipAddress, false);
        Response::error('Invalid credentials', 401);
    }

    $user = $users[0];

    // Verify password
    if (!password_verify($password, $user['password'])) {
        RateLimit::recordLoginAttempt($email, $ipAddress, false);
        Response::error('Invalid credentials', 401);
    }

    // Check user status
    if ($user['status'] !== 'active') {
        Response::error('Account is inactive or locked', 403);
    }

    // Clear failed login attempts
    RateLimit::clearLoginAttempts($email, $ipAddress);

    // Record successful login
    RateLimit::recordLoginAttempt($email, $ipAddress, true);

    // Update last login
    $db->execute(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        [$user['id']]
    );

    // Generate tokens
    $jwt = new JWT();
    $tokenPayload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    $accessToken = $jwt->generateAccessToken($tokenPayload);
    $refreshToken = $jwt->generateRefreshToken($tokenPayload);

    // Store refresh token
    $refreshTokenHash = hash('sha256', $refreshToken);
    $config = require __DIR__ . '/../../config/jwt.php';
    $expiresAt = date('Y-m-d H:i:s', time() + $config['refresh_token_lifetime']);

    $db->execute(
        "INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)",
        [$user['id'], $refreshTokenHash, $expiresAt]
    );

    Response::success([
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'accessToken' => $accessToken,
        'refreshToken' => $refreshToken
    ], 'Login successful');
});

/**
 * POST /api/auth/logout
 * User logout (revokes refresh token)
 */
$router->post('/auth/logout', function () {
    Auth::authenticate();
    $data = Router::getJsonBody();
    $user = Auth::getCurrentUser();

    if (isset($data['refreshToken'])) {
        $refreshTokenHash = hash('sha256', $data['refreshToken']);
        $db = Database::getInstance();

        $db->execute(
            "UPDATE refresh_tokens SET revoked = TRUE WHERE user_id = ? AND token_hash = ?",
            [$user['id'], $refreshTokenHash]
        );
    }

    Response::success(null, 'Logged out successfully');
});

/**
 * POST /api/auth/refresh
 * Refresh access token using refresh token
 */
$router->post('/auth/refresh', function () {
    $data = Router::getJsonBody();

    if (empty($data['refreshToken'])) {
        Response::error('Refresh token is required', 400);
    }

    $jwt = new JWT();
    $decoded = $jwt->validateToken($data['refreshToken']);

    if (!$decoded) {
        Response::error('Invalid or expired refresh token', 401);
    }

    // Check if token is refresh type
    if (!isset($decoded['type']) || $decoded['type'] !== 'refresh') {
        Response::error('Invalid token type', 401);
    }

    // Check if token exists and not revoked
    $refreshTokenHash = hash('sha256', $data['refreshToken']);
    $db = Database::getInstance();

    $tokens = $db->query(
        "SELECT * FROM refresh_tokens WHERE token_hash = ? AND revoked = FALSE AND expires_at > NOW() LIMIT 1",
        [$refreshTokenHash]
    );

    if (empty($tokens)) {
        Response::error('Refresh token not found or revoked', 401);
    }

    // Get user
    $users = $db->query(
        "SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1",
        [$decoded['data']['id']]
    );

    if (empty($users)) {
        Response::error('User not found or inactive', 401);
    }

    $user = $users[0];

    // Generate new access token
    $tokenPayload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    $accessToken = $jwt->generateAccessToken($tokenPayload);

    Response::success([
        'accessToken' => $accessToken
    ], 'Token refreshed successfully');
});

/**
 * GET /api/auth/check
 * Validate current token and get user info
 */
$router->get('/auth/check', function () {
    $user = Auth::authenticate();

    Response::success([
        'user' => $user
    ], 'Token is valid');
});

/**
 * POST /api/auth/forgot-password
 * Request password reset
 */
$router->post('/auth/forgot-password', function () {
    $data = Router::getJsonBody();
    $validator = new Validator();

    if (!$validator->required($data['email'] ?? '', 'email') ||
        !$validator->email($data['email'] ?? '', 'email')) {
        Response::validationError($validator->getErrors());
    }

    $email = Validator::sanitizeEmail($data['email']);
    $db = Database::getInstance();

    // Find user
    $users = $db->query(
        "SELECT id, email FROM users WHERE email = ? AND status = 'active' LIMIT 1",
        [$email]
    );

    // Always return success to prevent email enumeration
    if (empty($users)) {
        Response::success(null, 'If the email exists, a password reset link has been sent');
    }

    $user = $users[0];

    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $resetToken);
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

    // Store reset token
    $db->execute(
        "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)",
        [$user['id'], $tokenHash, $expiresAt]
    );

    // TODO: Send email with reset link
    // For now, just log it (in production, send actual email)
    error_log("Password reset token for {$user['email']}: $resetToken");

    Response::success(null, 'If the email exists, a password reset link has been sent');
});

/**
 * POST /api/auth/reset-password
 * Reset password using token
 */
$router->post('/auth/reset-password', function () {
    $data = Router::getJsonBody();
    $validator = new Validator();

    if (!$validator->required($data['token'] ?? '', 'token') ||
        !$validator->required($data['password'] ?? '', 'password') ||
        !$validator->password($data['password'] ?? '', 'password')) {
        Response::validationError($validator->getErrors());
    }

    $token = $data['token'];
    $newPassword = $data['password'];
    $tokenHash = hash('sha256', $token);

    $db = Database::getInstance();

    // Find valid reset token
    $resets = $db->query(
        "SELECT * FROM password_resets WHERE token_hash = ? AND used = FALSE AND expires_at > NOW() LIMIT 1",
        [$tokenHash]
    );

    if (empty($resets)) {
        Response::error('Invalid or expired reset token', 400);
    }

    $reset = $resets[0];

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $db->beginTransaction();
    try {
        $db->execute(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $reset['user_id']]
        );

        $db->execute(
            "UPDATE password_resets SET used = TRUE WHERE id = ?",
            [$reset['id']]
        );

        $db->commit();
    } catch (\Exception $e) {
        $db->rollback();
        Response::serverError('Failed to reset password');
    }

    Response::success(null, 'Password reset successfully');
});

/**
 * PATCH /api/auth/change-password
 * Change own password (authenticated)
 */
$router->patch('/auth/change-password', function () {
    $user = Auth::authenticate();
    $data = Router::getJsonBody();
    $validator = new Validator();

    if (!$validator->required($data['currentPassword'] ?? '', 'currentPassword') ||
        !$validator->required($data['newPassword'] ?? '', 'newPassword') ||
        !$validator->password($data['newPassword'] ?? '', 'newPassword')) {
        Response::validationError($validator->getErrors());
    }

    $db = Database::getInstance();

    // Get user with password
    $users = $db->query(
        "SELECT password FROM users WHERE id = ? LIMIT 1",
        [$user['id']]
    );

    if (empty($users)) {
        Response::error('User not found', 404);
    }

    // Verify current password
    if (!password_verify($data['currentPassword'], $users[0]['password'])) {
        Response::error('Current password is incorrect', 400);
    }

    // Update password
    $hashedPassword = password_hash($data['newPassword'], PASSWORD_BCRYPT);

    $db->execute(
        "UPDATE users SET password = ? WHERE id = ?",
        [$hashedPassword, $user['id']]
    );

    Response::success(null, 'Password changed successfully');
});
