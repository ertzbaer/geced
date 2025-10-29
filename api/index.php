<?php
/**
 * API Entry Point
 * Lead Management System v2.0
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Middleware\CORS;
use App\Utils\Router;
use App\Utils\Response;

// Set timezone
date_default_timezone_set('Europe/Berlin');

// Handle CORS
CORS::handle();

// Create router
$router = new Router();

// =====================================================
// Authentication Routes
// =====================================================
require __DIR__ . '/routes/auth.php';

// =====================================================
// User Routes
// =====================================================
require __DIR__ . '/routes/users.php';

// =====================================================
// Lead Routes
// =====================================================
require __DIR__ . '/routes/leads.php';

// =====================================================
// Campaign Routes
// =====================================================
require __DIR__ . '/routes/campaigns.php';

// =====================================================
// Call Routes
// =====================================================
require __DIR__ . '/routes/calls.php';

// =====================================================
// Dashboard/Stats Routes
// =====================================================
require __DIR__ . '/routes/dashboard.php';

// Global error handler
set_exception_handler(function ($e) {
    error_log("Uncaught Exception: " . $e->getMessage());
    Response::serverError(
        $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : 'Internal server error'
    );
});

// Dispatch the request
$router->dispatch();
