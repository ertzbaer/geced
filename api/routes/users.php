<?php
/**
 * User Management Routes
 * CRUD operations for users (Superadmin only)
 */

use App\Utils\Router;
use App\Utils\Response;
use App\Utils\Database;
use App\Utils\Validator;
use App\Middleware\Auth;

/**
 * GET /api/users
 * Get all users (Superadmin only)
 */
$router->get('/users', function () {
    Auth::requireSuperadmin();

    $db = Database::getInstance();
    $users = $db->query(
        "SELECT id, username, email, role, status, created_at, last_login FROM users ORDER BY created_at DESC"
    );

    Response::success(['users' => $users]);
});

/**
 * GET /api/users/me
 * Get current user profile
 */
$router->get('/users/me', function () {
    $user = Auth::authenticate();

    // Get user preferences
    $db = Database::getInstance();
    $prefs = $db->query(
        "SELECT * FROM user_preferences WHERE user_id = ? LIMIT 1",
        [$user['id']]
    );

    $userProfile = $user;
    $userProfile['preferences'] = $prefs[0] ?? null;

    Response::success(['user' => $userProfile]);
});

/**
 * GET /api/users/:id
 * Get single user (Superadmin only)
 */
$router->get('/users/:id', function ($id) {
    Auth::requireSuperadmin();

    $db = Database::getInstance();
    $users = $db->query(
        "SELECT id, username, email, role, status, created_at, last_login FROM users WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($users)) {
        Response::notFound('User not found');
    }

    Response::success(['user' => $users[0]]);
});

/**
 * POST /api/users
 * Create new user (Superadmin only)
 */
$router->post('/users', function () {
    Auth::requireSuperadmin();

    $data = Router::getJsonBody();
    $validator = new Validator();

    // Validate input
    if (!$validator->required($data['username'] ?? '', 'username') ||
        !$validator->required($data['email'] ?? '', 'email') ||
        !$validator->email($data['email'] ?? '', 'email') ||
        !$validator->required($data['password'] ?? '', 'password') ||
        !$validator->password($data['password'] ?? '', 'password') ||
        !$validator->required($data['role'] ?? '', 'role') ||
        !$validator->inArray($data['role'] ?? '', ['superadmin', 'admin', 'agent'], 'role')) {
        Response::validationError($validator->getErrors());
    }

    $db = Database::getInstance();

    // Check if email exists
    $existing = $db->query(
        "SELECT id FROM users WHERE email = ? LIMIT 1",
        [Validator::sanitizeEmail($data['email'])]
    );

    if (!empty($existing)) {
        Response::error('Email already exists', 409);
    }

    // Check if username exists
    $existing = $db->query(
        "SELECT id FROM users WHERE username = ? LIMIT 1",
        [Validator::sanitizeString($data['username'])]
    );

    if (!empty($existing)) {
        Response::error('Username already exists', 409);
    }

    // Create user
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    $status = $data['status'] ?? 'active';

    $userId = $db->execute(
        "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)",
        [
            Validator::sanitizeString($data['username']),
            Validator::sanitizeEmail($data['email']),
            $hashedPassword,
            $data['role'],
            $status
        ]
    );

    // Create default preferences
    $db->execute(
        "INSERT INTO user_preferences (user_id) VALUES (?)",
        [$userId]
    );

    // Get created user
    $users = $db->query(
        "SELECT id, username, email, role, status, created_at FROM users WHERE id = ? LIMIT 1",
        [$userId]
    );

    Response::success(['user' => $users[0]], 'User created successfully', 201);
});

/**
 * PUT /api/users/:id
 * Update user (Superadmin only)
 */
$router->put('/users/:id', function ($id) {
    Auth::requireSuperadmin();

    $data = Router::getJsonBody();
    $validator = new Validator();
    $db = Database::getInstance();

    // Check if user exists
    $existing = $db->query(
        "SELECT * FROM users WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('User not found');
    }

    $updateFields = [];
    $params = [];

    // Validate and prepare update fields
    if (isset($data['username'])) {
        $validator->required($data['username'], 'username');
        $updateFields[] = "username = ?";
        $params[] = Validator::sanitizeString($data['username']);
    }

    if (isset($data['email'])) {
        $validator->required($data['email'], 'email');
        $validator->email($data['email'], 'email');
        $updateFields[] = "email = ?";
        $params[] = Validator::sanitizeEmail($data['email']);
    }

    if (isset($data['role'])) {
        $validator->inArray($data['role'], ['superadmin', 'admin', 'agent'], 'role');
        $updateFields[] = "role = ?";
        $params[] = $data['role'];
    }

    if (isset($data['status'])) {
        $validator->inArray($data['status'], ['active', 'inactive', 'locked'], 'status');
        $updateFields[] = "status = ?";
        $params[] = $data['status'];
    }

    if ($validator->hasErrors()) {
        Response::validationError($validator->getErrors());
    }

    if (empty($updateFields)) {
        Response::error('No fields to update', 400);
    }

    // Update user
    $params[] = $id;
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $db->execute($sql, $params);

    // Get updated user
    $users = $db->query(
        "SELECT id, username, email, role, status, created_at FROM users WHERE id = ? LIMIT 1",
        [$id]
    );

    Response::success(['user' => $users[0]], 'User updated successfully');
});

/**
 * PATCH /api/users/me
 * Update own profile
 */
$router->patch('/users/me', function () {
    $user = Auth::authenticate();
    $data = Router::getJsonBody();
    $validator = new Validator();
    $db = Database::getInstance();

    $updateFields = [];
    $params = [];

    // Only allow updating own username and email
    if (isset($data['username'])) {
        $validator->required($data['username'], 'username');
        $updateFields[] = "username = ?";
        $params[] = Validator::sanitizeString($data['username']);
    }

    if (isset($data['email'])) {
        $validator->required($data['email'], 'email');
        $validator->email($data['email'], 'email');
        $updateFields[] = "email = ?";
        $params[] = Validator::sanitizeEmail($data['email']);
    }

    if ($validator->hasErrors()) {
        Response::validationError($validator->getErrors());
    }

    if (empty($updateFields)) {
        Response::error('No fields to update', 400);
    }

    // Update user
    $params[] = $user['id'];
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $db->execute($sql, $params);

    // Get updated user
    $users = $db->query(
        "SELECT id, username, email, role, status FROM users WHERE id = ? LIMIT 1",
        [$user['id']]
    );

    Response::success(['user' => $users[0]], 'Profile updated successfully');
});

/**
 * PATCH /api/users/me/preferences
 * Update user preferences
 */
$router->patch('/users/me/preferences', function () {
    $user = Auth::authenticate();
    $data = Router::getJsonBody();
    $db = Database::getInstance();

    $updateFields = [];
    $params = [];

    if (isset($data['notifications_enabled'])) {
        $updateFields[] = "notifications_enabled = ?";
        $params[] = $data['notifications_enabled'] ? 1 : 0;
    }

    if (isset($data['email_notifications'])) {
        $updateFields[] = "email_notifications = ?";
        $params[] = $data['email_notifications'] ? 1 : 0;
    }

    if (empty($updateFields)) {
        Response::error('No preferences to update', 400);
    }

    // Update or insert preferences
    $params[] = $user['id'];
    $sql = "INSERT INTO user_preferences (user_id, " .
           implode(', ', array_map(fn($f) => explode(' = ', $f)[0], $updateFields)) .
           ") VALUES (?, " . str_repeat('?, ', count($updateFields) - 1) . "?) " .
           "ON DUPLICATE KEY UPDATE " . implode(', ', $updateFields);

    $paramsForInsert = array_merge([$user['id']], array_slice($params, 0, -1));
    $finalParams = array_merge($paramsForInsert, $params);

    // Simpler approach
    $db->execute(
        "DELETE FROM user_preferences WHERE user_id = ?",
        [$user['id']]
    );

    $db->execute(
        "INSERT INTO user_preferences (user_id, notifications_enabled, email_notifications) VALUES (?, ?, ?)",
        [
            $user['id'],
            $data['notifications_enabled'] ?? true,
            $data['email_notifications'] ?? true
        ]
    );

    $prefs = $db->query(
        "SELECT * FROM user_preferences WHERE user_id = ? LIMIT 1",
        [$user['id']]
    );

    Response::success(['preferences' => $prefs[0]], 'Preferences updated successfully');
});

/**
 * DELETE /api/users/:id
 * Delete user (Superadmin only)
 */
$router->delete('/users/:id', function ($id) {
    Auth::requireSuperadmin();
    $currentUser = Auth::getCurrentUser();

    // Prevent deleting self
    if ($id == $currentUser['id']) {
        Response::error('Cannot delete your own account', 400);
    }

    $db = Database::getInstance();

    // Check if user exists
    $existing = $db->query(
        "SELECT id FROM users WHERE id = ? LIMIT 1",
        [$id]
    );

    if (empty($existing)) {
        Response::notFound('User not found');
    }

    // Delete user (cascade will handle related records)
    $db->execute("DELETE FROM users WHERE id = ?", [$id]);

    Response::success(null, 'User deleted successfully');
});
