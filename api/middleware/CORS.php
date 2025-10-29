<?php
/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing
 */

namespace App\Middleware;

class CORS
{
    /**
     * Handle CORS headers
     *
     * @return void
     */
    public static function handle(): void
    {
        $config = require __DIR__ . '/../../config/app.php';
        $cors = $config['cors'];

        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $origin = $_SERVER['HTTP_ORIGIN'];

            if (in_array('*', $cors['allowed_origins']) || in_array($origin, $cors['allowed_origins'])) {
                header("Access-Control-Allow-Origin: $origin");
            }
        } else {
            header("Access-Control-Allow-Origin: *");
        }

        // Access-Control headers
        header("Access-Control-Allow-Methods: " . implode(', ', $cors['allowed_methods']));
        header("Access-Control-Allow-Headers: " . implode(', ', $cors['allowed_headers']));
        header("Access-Control-Max-Age: {$cors['max_age']}");

        if ($cors['supports_credentials']) {
            header("Access-Control-Allow-Credentials: true");
        }

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
